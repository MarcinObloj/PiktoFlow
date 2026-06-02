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
            'name'     => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image'    => 'nullable|image|max:2048',
            'image_url'=> 'nullable|string',
            'audio'    => 'nullable|file|mimes:mp3,wav,ogg,webm|max:4096',
        ]);

        $finalImagePath = '';
        $finalAudioPath = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('custom_pictograms', 'public');
            $finalImagePath = '/storage/' . $path;
        } elseif ($request->image_url) {
            $finalImagePath = $request->image_url;
        }

        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('custom_audio', 'public');
            $finalAudioPath = '/storage/' . $audioPath;
        }

        Pictogram::create([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'image_path'  => $finalImagePath,
            'audio_path'  => $finalAudioPath,
            'is_custom'   => true,
            'user_id'     => auth()->id(),
        ]);

        return redirect()->route('dashboard');
    }

    public function destroy(Pictogram $pictogram)
    {
        if ($pictogram->user_id !== auth()->id()) {
            abort(403);
        }

        if ($pictogram->image_path && !str_starts_with($pictogram->image_path, 'http')) {
            $relativePath = str_replace('/storage/', '', $pictogram->image_path);
            Storage::disk('public')->delete($relativePath);
        }

        $pictogram->delete();
        return redirect()->route('dashboard');
    }
}
