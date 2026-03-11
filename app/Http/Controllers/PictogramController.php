<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Pictogram;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PictogramController extends Controller
{
    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Pictograms/Create', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        $path = $request->file('image')->store('pictograms', 'public');

        Pictogram::create([
            'name' => $request->name,
            'image_path' => '/storage/' . $path,
            'category_id' => $request->category_id,
            'is_custom' => true,
        ]);

        return redirect()->route('dashboard');
    }
}
