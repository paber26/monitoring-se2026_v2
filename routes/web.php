<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;

Route::get('/', [AssignmentController::class, 'index'])->name('dashboard');
Route::post('/upload', [AssignmentController::class, 'upload'])->name('upload');
