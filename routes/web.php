<?php

use App\Models\TastingSession;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Tasting: create requires auth; join and session room work without login (Kahoot-style)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('tasting/create', \App\Livewire\Tasting\CreateSession::class)->name('tasting.create');
});

Route::prefix('tasting')->name('tasting.')->group(function () {
    Route::get('{tastingSession}/leave', \App\Http\Controllers\Tasting\LeaveSessionController::class)->name('leave');
    Route::get('{tastingSession}', \App\Livewire\Tasting\SessionRoom::class)->name('show');
});

// Join a session (public: enter code + display name)
Route::get('join', \App\Livewire\Tasting\JoinSession::class)->name('tasting.join');

require __DIR__.'/settings.php';
