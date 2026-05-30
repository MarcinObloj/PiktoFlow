<?php

namespace App\Http\Controllers;

use App\Models\TemplateSet;
use App\Models\Pictogram;
use App\Models\Category;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class TemplateSetController extends Controller
{
    public function index()
    {
        $templates = TemplateSet::withCount('items')->get();
        return Inertia::render('Templates/Index', [
            'templates' => $templates
        ]);
    }

    public function show(TemplateSet $templateSet)
    {
        $templateSet->load('items');
        $children = auth()->user()->children;
        return Inertia::render('Templates/Show', [
            'template' => $templateSet,
            'children' => $children
        ]);
    }

    public function applyToChild(Request $request, TemplateSet $templateSet)
    {
        $request->validate([
            'child_id' => 'required|exists:children,id'
        ]);

        $child = Child::findOrFail($request->child_id);

        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $templateSet->load('items');
        $attachedIds = [];

        foreach ($templateSet->items as $item) {
            $category = Category::firstOrCreate(
                ['name' => $item->category_name],
                ['color' => '#6b7280', 'user_id' => auth()->id()]
            );

            $pictogram = Pictogram::firstOrCreate(
                ['name' => $item->name, 'is_custom' => false],
                [
                    'image_path' => $item->image_path,
                    'category_id' => $category->id,
                    'is_custom' => false
                ]
            );

            $attachedIds[] = $pictogram->id;
        }

        $child->pictograms()->syncWithoutDetaching($attachedIds);

        return redirect()->route('children.manage', $child)
            ->with('success', 'Szablon "' . $templateSet->name . '" został dodany do tablicy ' . $child->name . '!');
    }

    public function exportPdf(Child $child)
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $pictograms = $child->pictograms()
            ->with('category')
            ->withPivot('position')
            ->orderByPivot('position', 'asc')
            ->get();

        return Inertia::render('Children/ExportPdf', [
            'child' => $child,
            'pictograms' => $pictograms
        ]);
    }
}
