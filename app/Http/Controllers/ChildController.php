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
class ChildController extends Controller
{
    public function index()
    {
        $children = auth()->user()->children;

        return Inertia::render('Children/Index', [
            'children' => $children
        ]);
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

        $allStarterIds = [];

        foreach ($categoriesDefinition as $catName => $data) {
            $category = Category::firstOrCreate(
                ['name' => $catName],
                ['color' => $data['color']]
            );

            foreach ($data['words'] as $word) {
                $existing = Pictogram::where('name', ucfirst($word))->first();

                if ($existing) {
                    $allStarterIds[] = $existing->id;
                } else {
                    $resp = Http::get("https://api.arasaac.org/api/pictograms/pl/search/" . urlencode($word));
                    if ($resp->successful() && count($resp->json()) > 0) {
                        $picData = $resp->json()[0];
                        $arasaacId = $picData['_id'];

                        $newPic = Pictogram::create([
                            'name' => ucfirst($word),
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
                $response = Http::get("https://api.arasaac.org/api/pictograms/pl/search/" . urlencode($hobby));
                if ($response->successful() && count($response->json()) > 0) {
                    $picData = $response->json()[0];
                    $arasaacId = $picData['_id'];
                    $pictogram = Pictogram::firstOrCreate(
                        ['name' => ucfirst($hobby)],
                        ['category_id' => $hobbyCategory->id, 'image_path' => "https://static.arasaac.org/pictograms/{$arasaacId}/{$arasaacId}_300.png", 'is_custom' => false]
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
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
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

        $categories = \App\Models\Category::with('pictograms')->get();

        $activePictogramIds = $child->pictograms()->pluck('pictograms.id')->toArray();

        return \Inertia\Inertia::render('Children/Manage', [
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

        if (!$lastId) {
            // Jeśli puste zdanie, zasugeruj najpopularniejsze ze wszystkich piktogramów dziecka
            $popularIds = ClickLog::where('child_id', $child->id)
                ->select('pictogram_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('pictogram_id')
                ->orderByDesc('total')
                ->limit(4)
                ->pluck('pictogram_id');

            $pictograms = Pictogram::whereIn('id', $popularIds)->get();
            return response()->json($pictograms);
        }

        // Łańcuch Markowa - predykcja następnego symbolu na podstawie historii kliknięć (czas okna: 2 minuty)
        $logs = ClickLog::where('child_id', $child->id)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        $transitions = [];

        // Pętla od tyłu (od najstarszych w ramach pobranego 1000)
        for ($i = count($logs) - 1; $i > 0; $i--) {
            $current = $logs[$i];
            $next = $logs[$i - 1]; // Kolejne kliknięcie w chronologii czasowej

            // Jeśli kolejne kliknięcie nastąpiło w ciągu 120 sekund
            if ($current->created_at->diffInSeconds($next->created_at) < 120) {
                if ($current->pictogram_id == $lastId) {
                    $nextId = $next->pictogram_id;
                    $transitions[$nextId] = ($transitions[$nextId] ?? 0) + 1;
                }
            }
        }

        // Sortowanie malejąco według częstotliwości wystąpień po danym symbolu
        arsort($transitions);
        $predictedIds = array_slice(array_keys($transitions), 0, 4);

        if (empty($predictedIds)) {
            // Fallback: jeśli nie znaleziono wzorców dla tego słowa, podaj globalnie najczęstsze
            $popularIds = ClickLog::where('child_id', $child->id)
                ->select('pictogram_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('pictogram_id')
                ->orderByDesc('total')
                ->limit(4)
                ->pluck('pictogram_id');
            $predictedIds = $popularIds->toArray();
        }

        // Pobranie modeli i zachowanie kolejności z predykcji
        $pictograms = Pictogram::whereIn('id', $predictedIds)->get();
        $sortedPictograms = $pictograms->sortBy(function($model) use ($predictedIds) {
            return array_search($model->id, $predictedIds);
        })->values();

        return response()->json($sortedPictograms);
    }

    public function statistics()
    {
        $children = auth()->user()->children;
        $statistics = [];

        foreach ($children as $child) {
            $stats = \App\Models\ClickLog::where('child_id', $child->id)
                ->select('pictogram_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('pictogram_id')
                ->orderByDesc('total')
                ->limit(5)
                ->with('pictogram')
                ->get();

            // Pobieranie MLU pogrupowane po dniach z ostatnich 14 dni
            $mluData = \App\Models\SentenceLog::where('child_id', $child->id)
                ->where('created_at', '>=', now()->subDays(14))
                ->select(\Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'), \Illuminate\Support\Facades\DB::raw('AVG(length) as mlu'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $statistics[] = [
                'child' => $child,
                'chartLabels' => $stats->map(fn($log) => $log->pictogram->name),
                'chartData' => $stats->map(fn($log) => $log->total),
                'mluLabels' => $mluData->map(fn($log) => $log->date),
                'mluData' => $mluData->map(fn($log) => round($log->mlu, 2)),
            ];
        }

        return \Inertia\Inertia::render('Statistics/Index', [
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

        return \Inertia\Inertia::render('Children/ManageSchedule', [
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
            $unsorted = \App\Models\Pictogram::whereIn('id', $planIds)->get()->keyBy('id');
            $planPictograms = collect($planIds)->map(function($id) use ($unsorted) {
                return $unsorted->get($id);
            })->filter()->values();
        }

        return \Inertia\Inertia::render('Children/ScheduleBoard', [
            'child' => $child,
            'planPictograms' => $planPictograms
        ]);
    }
}
