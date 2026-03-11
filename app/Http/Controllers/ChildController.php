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

        $categories = \App\Models\Category::whereHas('pictograms', function ($query) use ($child) {
            $query->whereHas('children', function ($q) use ($child) {
                $q->where('child_id', $child->id);
            });
        })->with(['pictograms' => function ($query) use ($child) {
            $query->whereHas('children', function ($q) use ($child) {
                $q->where('child_id', $child->id);
            });
        }])->get();

        return Inertia::render('Children/Board', [
            'child' => $child,
            'categories' => $categories
        ]);
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
}
