<?php

use App\Livewire\DashboardOverview;
use App\Livewire\DailyLogTable;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', DashboardOverview::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('daily-log', DailyLogTable::class)
    ->middleware(['auth', 'verified'])
    ->name('daily-log');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
