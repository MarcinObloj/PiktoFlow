<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Pictogram;
use App\Models\ClickLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Services\PredictionService;
use App\Services\AnalyticsService;

class ChildController extends Controller
{
    protected PredictionService $predictionService;
    protected AnalyticsService $analyticsService;

    public function __construct(PredictionService $predictionService, AnalyticsService $analyticsService)
    {
        $this->predictionService = $predictionService;
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        $children = auth()->user()->children;
        return Inertia::render('Children/Index', ['children' => $children]);
    }

    public function create()
    {
        return Inertia::render('Children/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:1|max:99',
            'hobbies' => 'nullable|string|max:255',
        ]);

        $child = auth()->user()->children()->create([
            'name' => $request->name,
            'age' => $request->age,
            'hobbies' => $request->hobbies,
            'tts_voice' => $request->tts_voice,
            'tts_rate' => $request->tts_rate ?? 0.9,
            'tts_pitch' => $request->tts_pitch ?? 1.0,
            'tts_volume' => $request->tts_volume ?? 1.0,
        ]);

        $categoriesDefinition = [
            'Podstawowe' => [
                'color' => '#10b981',
                'words' => ['chcę', 'tak', 'nie', 'pomocy', 'stop', 'koniec', 'jeszcze', 'to', 'teraz', 'później', 'gotowe']
            ],
            'Osoby' => [
                'color' => '#3b82f6',
                'words' => ['ja', 'ty', 'mama', 'tata', 'dziecko', 'pani', 'pan', 'babcia', 'dziadek', 'kolega', 'lekarz']
            ],
            'Czynności' => [
                'color' => '#f59e0b',
                'words' => ['iść', 'spać', 'jeść', 'pić', 'bawić się', 'oglądać', 'słuchać', 'czytać', 'myć', 'ubierać', 'siadać', 'stać', 'rysować', 'skakać']
            ],
            'Emocje' => [
                'color' => '#ef4444',
                'words' => ['lubię', 'nie lubię', 'dobrze', 'źle', 'wesoły', 'smutny', 'zły', 'zmęczony', 'boli', 'kocham', 'strach']
            ],
            'Miejsca i Rzeczy' => [
                'color' => '#8b5cf6',
                'words' => ['dom', 'szkoła', 'plac zabaw', 'sklep', 'łóżko', 'zabawka', 'książka', 'telefon', 'toaleta', 'jedzenie', 'picie', 'auto']
            ]
        ];


        $searchOverrides = [
            'ja' => 'mnie',
            'ty' => 'ciebie',
            'mama' => 'matka',
            'tata' => 'ojciec',
            'szczęśliwy' => 'uśmiech',
            'smutny' => 'smutek',
            'zły' => 'gniew',
            'boję się' => 'przestraszony',
            'nie lubię' => 'nie znosić',
            'źle' => 'błąd',
            'jabłko' => 'owoc jabłoni',
            'woda' => 'woda mineralna',
            'banan' => 'owoc banana',
            'picie' => 'napoje',
            'chcę' => 'chcieć',
            'do widzenia' => 'pożegnanie',
            'nie wiem' => 'nie rozumieć',
        ];

        $allWordsToFetch = [];
        foreach ($categoriesDefinition as $catName => $data) {
            foreach ($data['words'] as $word) {
                $allWordsToFetch[] = ucfirst($word);
            }
        }

        // Pobieramy istniejące piktogramy jednym zapytaniem
        $existingPictograms = Pictogram::whereIn('name', $allWordsToFetch)->get()->keyBy('name');

        $allStarterIds = [];

        foreach ($categoriesDefinition as $catName => $data) {
            $category = Category::firstOrCreate(
                ['name' => $catName],
                ['color' => $data['color']]
            );

            foreach ($data['words'] as $word) {
                $wordCapitalized = ucfirst($word);
                
                if ($existingPictograms->has($wordCapitalized)) {
                    $allStarterIds[] = $existingPictograms->get($wordCapitalized)->id;
                } else {
                    $wordLower = mb_strtolower($word);

                    $searchTerm = array_key_exists($wordLower, $searchOverrides)
                        ? $searchOverrides[$wordLower]
                        : $word;

                    $resp = Http::get("https://api.arasaac.org/api/pictograms/pl/search/" . urlencode($searchTerm));

                    if ($resp->successful() && count($resp->json()) > 0) {
                        $picData = $resp->json()[0];
                        $arasaacId = $picData['_id'];

                        $newPic = Pictogram::create([
                            'name' => $wordCapitalized,
                            'category_id' => $category->id,
                            'image_path' => "https://static.arasaac.org/pictograms/{$arasaacId}/{$arasaacId}_300.png",
                            'is_custom' => false
                        ]);
                        $allStarterIds[] = $newPic->id;
                    }
                }
            }
        }

        if (!empty($allStarterIds)) {
            $child->pictograms()->syncWithoutDetaching($allStarterIds);
        }

        if ($request->hobbies) {
            $hobbiesArray = array_map('trim', explode(',', $request->hobbies));
            $hobbyCategory = Category::firstOrCreate(['name' => 'Zainteresowania'], ['color' => '#ec4899']);
            $attachedHobbyIds = [];

            foreach ($hobbiesArray as $hobby) {
                if (empty($hobby)) continue;

                $hobbyLower = mb_strtolower($hobby);
                $searchTerm = array_key_exists($hobbyLower, $searchOverrides) ? $searchOverrides[$hobbyLower] : $hobby;

                $response = Http::get("https://api.arasaac.org/api/pictograms/pl/search/" . urlencode($searchTerm));

                if ($response->successful() && count($response->json()) > 0) {
                    $picData = $response->json()[0];
                    $arasaacId = $picData['_id'];

                    $pictogram = Pictogram::firstOrCreate(
                        ['name' => ucfirst($hobby)],
                        [
                            'category_id' => $hobbyCategory->id,
                            'image_path' => "https://static.arasaac.org/pictograms/{$arasaacId}/{$arasaacId}_300.png",
                            'is_custom' => false
                        ]
                    );
                    $attachedHobbyIds[] = $pictogram->id;
                }
            }
            $child->pictograms()->syncWithoutDetaching($attachedHobbyIds);
        }

        return redirect()->route('children.index');
    }

    public function destroy(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        if ($child->avatar_path) {
            $oldPath = str_replace('/storage/', '', $child->avatar_path);
            Storage::disk('public')->delete($oldPath);
        }

        $child->delete();

        return redirect()->back();
    }

    public function board(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $pictograms = $child->pictograms()
            ->with('category')
            ->withPivot('position')
            ->orderByPivot('position', 'asc')
            ->get();

        $grouped = $pictograms->groupBy('category_id');

        $categories = $grouped->map(function ($items) {
            $category = $items->first()->category;
            return [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'pictograms' => $items->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'image_path' => $p->image_path,
                        'audio_path' => $p->audio_path,
                        'position' => $p->pivot->position,
                    ];
                })
            ];
        })->values();

        return Inertia::render('Children/Board', [
            'child' => $child,
            'categories' => $categories
        ]);
    }

    public function reorder(Request $request, Child $child)
    {
        $ids = $request->input('ids');

        foreach ($ids as $index => $id) {
            $child->pictograms()->updateExistingPivot($id, [
                'position' => $index
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function manage(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->with(['pictograms' => function ($query) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', auth()->id());
            }])->get();

        $activePictogramIds = $child->pictograms()->pluck('pictograms.id')->toArray();

        return Inertia::render('Children/Manage', [
            'child' => $child,
            'categories' => $categories,
            'activePictogramIds' => $activePictogramIds
        ]);
    }

    public function updateWords(Request $request, Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'pictogram_ids' => 'array',
        ]);

        $child->pictograms()->sync($request->pictogram_ids);

        return redirect()->route('children.index');
    }

    public function logClick(Request $request, Child $child)
    {
        $request->validate([
            'pictogram_id' => 'required|exists:pictograms,id',
        ]);

        ClickLog::create([
            'child_id' => $child->id,
            'pictogram_id' => $request->pictogram_id,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function logSentence(Request $request, Child $child)
    {
        $request->validate([
            'pictogram_ids' => 'required|array',
        ]);

        $child->sentenceLogs()->create([
            'pictogram_ids' => $request->pictogram_ids,
            'length' => count($request->pictogram_ids),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function predict(Request $request, Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $lastId = $request->query('last_pictogram_id');
        $excludeIds = $request->query('exclude_ids', []);
        
        // Convert to array of ints if passed as comma separated string
        if (is_string($excludeIds)) {
            $excludeIds = array_map('intval', explode(',', $excludeIds));
        }

        $pictograms = $this->predictionService->predictNextPictograms($child, $lastId ? (int) $lastId : null, $excludeIds);

        return response()->json($pictograms);
    }

    public function statistics()
    {
        $children = auth()->user()->children;
        
        $statistics = $this->analyticsService->generateStatistics($children);

        return Inertia::render('Statistics/Index', [
            'statistics' => $statistics
        ]);
    }


    public function update(Request $request, Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'is_cvi_mode' => 'required|boolean',
            'tts_voice' => 'nullable|string|max:255',
            'tts_rate' => 'nullable|numeric|min:0.1|max:2',
            'tts_pitch' => 'nullable|numeric|min:0|max:2',
            'tts_volume' => 'nullable|numeric|min:0|max:1',
        ]);

        $data = [
            'name' => $request->name,
            'is_cvi_mode' => $request->is_cvi_mode,
            'tts_voice' => $request->tts_voice,
            'tts_rate' => $request->tts_rate,
            'tts_pitch' => $request->tts_pitch,
            'tts_volume' => $request->tts_volume,
        ];

        if ($request->hasFile('avatar')) {
            if ($child->avatar_path) {
                $oldPath = str_replace('/storage/', '', $child->avatar_path);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_path'] = '/storage/' . $path;
        }

        $child->update($data);

        return redirect()->back()->with('success', 'Ustawienia profilu zapisane.');
    }

    public function manageSchedule(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $pictograms = $child->pictograms()->get();

        return Inertia::render('Children/ManageSchedule', [
            'child' => $child,
            'availablePictograms' => $pictograms
        ]);
    }

    public function updateSchedule(Request $request, Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'schedule' => 'array',
        ]);

        $child->update([
            'daily_plan' => $request->schedule
        ]);

        return redirect()->route('children.index');
    }

    public function scheduleBoard(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $planIds = $child->daily_plan ?? [];
        $planPictograms = [];

        if (!empty($planIds)) {
            $unsorted = Pictogram::whereIn('id', $planIds)->get()->keyBy('id');
            $planPictograms = collect($planIds)->map(function($id) use ($unsorted) {
                return $unsorted->get($id);
            })->filter()->values();
        }

        return Inertia::render('Children/ScheduleBoard', [
            'child' => $child,
            'planPictograms' => $planPictograms
        ]);
    }

    public function quiz(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $pictograms = $child->pictograms()->inRandomOrder()->limit(20)->get();

        return Inertia::render('Children/Quiz', [
            'child' => $child,
            'pictograms' => $pictograms
        ]);
    }

    public function saveQuiz(Request $request, Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'score' => 'required|integer|min:0',
            'total' => 'required|integer|min:1',
            'clicked_ids' => 'nullable|array',
            'sentences' => 'nullable|array',
        ]);

        $child->quizResults()->create([
            'score' => $request->score,
            'total_questions' => $request->total,
        ]);

        if ($request->has('clicked_ids') && !empty($request->clicked_ids)) {
            foreach ($request->clicked_ids as $picId) {
                ClickLog::create([
                    'child_id' => $child->id,
                    'pictogram_id' => $picId,
                ]);
            }
        }

        if ($request->has('sentences') && !empty($request->sentences)) {
            foreach ($request->sentences as $length) {
                $child->sentenceLogs()->create([
                    'length' => $length,
                    'pictogram_ids' => [1],
                ]);
            }
        }

        return redirect()->route('children.index')->with('success', 'Trening zakończony! Statystyki zostały zaktualizowane.');
    }
}
