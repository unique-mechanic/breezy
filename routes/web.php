<?php

use App\Http\Controllers\HabitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramGroupController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Habit tracking routes
    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::get('/habits/create', [HabitController::class, 'create'])->name('habits.create');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::get('/habits/{habit}', [HabitController::class, 'show'])->name('habits.show');
    Route::get('/habits/{habit}/edit', [HabitController::class, 'edit'])->name('habits.edit');
    Route::patch('/habits/{habit}', [HabitController::class, 'update'])->name('habits.update');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
    
    // API endpoints for habit logging
    Route::post('/habits/{habit}/log', [HabitController::class, 'log'])->name('habits.log');
    Route::post('/habits/{habit}/toggle-today', [HabitController::class, 'toggleToday'])->name('habits.toggle-today');

    // Telegram routes
    Route::get('/telegram/create-group', [TelegramGroupController::class, 'showForm'])
        ->name('telegram.create-group.form');

    Route::post('/telegram/create-group', [TelegramGroupController::class, 'createGroup'])
        ->name('telegram.create-group');
});


require __DIR__.'/auth.php';
