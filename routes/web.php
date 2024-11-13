<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

/* Contest routes */
Route::get('/contests', function () {
    return view('contests.index', [
        'contests' => App\Models\Contest::query()->orderBy('updated_at', 'desc')->get(),
    ]);
})->name('contests.index');
Route::get('/contests/{contest}', function (App\Models\Contest $contest) {
    return view('contests.show', compact('contest'));
})->name('contests.show');
Route::get('/contests/{contest}/vote', function (App\Models\Contest $contest) {
    return view('contests.vote', compact('contest'));
})->name('contests.vote');

require __DIR__ . '/auth.php';
