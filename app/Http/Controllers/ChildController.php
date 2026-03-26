<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        ]);

        auth()->user()->children()->create([
            'name' => $request->name,
        ]);

        return redirect()->route('children.index');
    }
    public function board(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $pictograms = $child->pictograms()
            ->with('category') // Pobierz dane o kategorii
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
        $ids = $request->input('ids'); // tablica ID piktogramów

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
            abort(403);
        }

        $categories = \App\Models\Category::with('pictograms')->get();
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
    public function logClick(\Illuminate\Http\Request $request, \App\Models\Child $child)
    {
        $request->validate([
            'pictogram_id' => 'required|exists:pictograms,id',
        ]);

        \App\Models\ClickLog::create([
            'child_id' => $child->id,
            'pictogram_id' => $request->pictogram_id,
        ]);

        return response()->json(['status' => 'success']);
    }
}
