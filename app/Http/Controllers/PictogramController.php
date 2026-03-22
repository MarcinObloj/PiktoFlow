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


    public function searchArasaac()
    {
        $categories = Category::whereNull('user_id')->orWhere('user_id', auth()->id())->get();
        return Inertia::render('Pictograms/SearchArasaac', ['categories' => $categories]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048', // Plik jest opcjonalny, jeśli mamy URL
            'image_url' => 'nullable|string',     // URL jest opcjonalny, jeśli mamy plik
        ]);

        $finalPath = '';

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('custom_pictograms', 'public');
            $finalPath = '/storage/' . $path;
        }
        elseif ($request->image_url) {
            $finalPath = $request->image_url;
        }

        Pictogram::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'image_path' => $finalPath,
            'is_custom' => $request->hasFile('image'),
        ]);

        return redirect()->route('dashboard');
    }

    public function destroy(Pictogram $pictogram)
    {
        if ($pictogram->image_path && !str_starts_with($pictogram->image_path, 'http')) {
            $relativePath = str_replace('/storage/', '', $pictogram->image_path);
            Storage::disk('public')->delete($relativePath);
        }

        $pictogram->delete();
        return redirect()->route('dashboard');
    }
}
