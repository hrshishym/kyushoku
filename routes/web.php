<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('menu.index');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('menu.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/upload', [MenuController::class, 'uploadForm'])->name('menu.upload.form');
    Route::post('/menu/upload', [MenuController::class, 'upload'])->name('menu.upload');
    Route::get('/menu/monthly', [MenuController::class, 'showMonthly'])->name('menu.monthly');
    Route::get('/menu/pdf/{id}', [MenuController::class, 'showPdf'])->name('menu.pdf');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
