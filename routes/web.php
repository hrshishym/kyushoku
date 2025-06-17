<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;

Route::get('/', [MenuController::class, 'index'])->name('menu.index');
Route::get('/upload', [MenuController::class, 'uploadForm'])->name('menu.upload.form');
Route::post('/upload', [MenuController::class, 'upload'])->name('menu.upload');
Route::get('/monthly', [MenuController::class, 'showMonthly'])->name('menu.monthly');
Route::get('/pdf/{id}', [MenuController::class, 'showPdf'])->name('menu.pdf');