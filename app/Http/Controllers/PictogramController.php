<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Pictogram;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PictogramController extends Controller
{
    public function create()
    {
        $categories = Category::whereNull('user_id')->orWhere('user_id', auth()->id())->get();
        return Inertia::render('Pictograms/Create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('custom_pictograms', 'public');

        Pictogram::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'image_path' => '/storage/' . $path,
        ]);

        return redirect()->route('dashboard');
    }


    public function destroy(Pictogram $pictogram)
    {
        if ($pictogram->image_path) {
            $relativePath = str_replace('/storage/', '', $pictogram->image_path);
            Storage::disk('public')->delete($relativePath);
        }

        $pictogram->delete();
        return redirect()->route('dashboard');
    }
}
