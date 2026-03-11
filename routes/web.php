<?php

use App\Http\Controllers\PictogramController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Category;
use App\Http\Controllers\ChildController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $categories = Category::with('pictograms')->get();

    return Inertia::render('Dashboard', [
        'categories' => $categories
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pictograms/create', [PictogramController::class, 'create'])->name('pictograms.create');
    Route::post('/pictograms', [PictogramController::class, 'store'])->name('pictograms.store');

    Route::get('/children', [ChildController::class, 'index'])->name('children.index');
    Route::get('/children/create', [ChildController::class, 'create'])->name('children.create');
    Route::post('/children', [ChildController::class, 'store'])->name('children.store');
    Route::get('/children/{child}/board', [ChildController::class, 'board'])->name('children.board');

    Route::get('/children/{child}/manage', [ChildController::class, 'manage'])->name('children.manage');
    Route::post('/children/{child}/manage', [ChildController::class, 'updateWords'])->name('children.update-words');
});
require __DIR__.'/auth.php';
