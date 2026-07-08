<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return Category::where('user_id', $request->user()?->id)->get();
    }

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
