<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        auth()->user()->categories()->create([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('dashboard');
    }


    public function destroy(Category $category)
    {
        if ($category->user_id === auth()->id()) {
            $category->delete();
        }
        return redirect()->route('dashboard');
    }
}
