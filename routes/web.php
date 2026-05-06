<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PictogramController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\ArasaacController;
use App\Models\Category;
use App\Models\Pictogram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/dashboard', function () {
    $myCategories = Category::where('user_id', auth()->id())->get();

    $myPictograms = Pictogram::where('is_custom', true)
        ->orWhereHas('category', function ($query) {
            $query->where('user_id', auth()->id());
        })
        ->with('category')
        ->get();

    return Inertia::render('Dashboard', [
        'myCategories' => $myCategories,
        'myPictograms' => $myPictograms
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pictograms/create', [PictogramController::class, 'create'])->name('pictograms.create');
    Route::get('/pictograms/arasaac', [PictogramController::class, 'searchArasaac'])->name('pictograms.arasaac');
    Route::post('/pictograms', [PictogramController::class, 'store'])->name('pictograms.store');
    Route::delete('/pictograms/{pictogram}', [PictogramController::class, 'destroy'])->name('pictograms.destroy');
    Route::post('/children/{child}/update', [\App\Http\Controllers\ChildController::class, 'update'])->name('children.update');
    Route::get('/arasaac/search', [ArasaacController::class, 'search'])->name('arasaac.search');

    Route::get('/children/{child}/quiz', [App\Http\Controllers\ChildController::class, 'quiz'])->name('children.quiz');
    Route::post('/children/{child}/quiz', [App\Http\Controllers\ChildController::class, 'saveQuiz'])->name('children.quiz.save');
    Route::get('/children', [ChildController::class, 'index'])->name('children.index');
    Route::get('/children/create', [ChildController::class, 'create'])->name('children.create');
    Route::post('/children', [ChildController::class, 'store'])->name('children.store');
    Route::get('/children/{child}/board', [ChildController::class, 'board'])->name('children.board');
    Route::get('/children/{child}/manage', [ChildController::class, 'manage'])->name('children.manage');
    Route::post('/children/{child}/manage', [ChildController::class, 'updateWords'])->name('children.update-words');
    Route::post('/children/{child}/reorder', [ChildController::class, 'reorder'])->name('children.reorder');
    Route::get('/statistics', [\App\Http\Controllers\ChildController::class, 'statistics'])->name('statistics.index');
    Route::post('/children/{child}/log-click', [ChildController::class, 'logClick'])->name('children.log-click');
    Route::post('/children/{child}/log-sentence', [ChildController::class, 'logSentence'])->name('children.log-sentence');
    Route::get('/children/{child}/predict', [ChildController::class, 'predict'])->name('children.predict');
    Route::get('/categories/create', function () {
        return Inertia::render('Categories/Create');
    })->name('categories.create');
    Route::delete('/children/{child}', [\App\Http\Controllers\ChildController::class, 'destroy'])->name('children.destroy');
    Route::post('/categories', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        auth()->user()->categories()->create([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('dashboard');
    })->name('categories.store');

    Route::get('/children/{child}/schedule/manage', [\App\Http\Controllers\ChildController::class, 'manageSchedule'])->name('children.manage-schedule');
    Route::post('/children/{child}/schedule', [\App\Http\Controllers\ChildController::class, 'updateSchedule'])->name('children.update-schedule');
    Route::get('/children/{child}/schedule/board', [\App\Http\Controllers\ChildController::class, 'scheduleBoard'])->name('children.schedule-board');
    Route::delete('/categories/{category}', function (Category $category) {
        if ($category->user_id === auth()->id()) {
            $category->delete();
        }
        return redirect()->route('dashboard');
    })->name('categories.destroy');
});
Route::get('/children/{child}/mediapipe/face_mesh/{file}', function ($child, $file) {
    return redirect()->away('https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/' . $file);
});

require __DIR__.'/auth.php';
