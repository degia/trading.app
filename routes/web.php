<?php

use App\Livewire\DashboardOverview;
use App\Livewire\DailyLogTable;
use App\Livewire\DepositWithdrawal;
use App\Livewire\TargetRules;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', DashboardOverview::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('daily-log', DailyLogTable::class)
    ->middleware(['auth', 'verified'])
    ->name('daily-log');

Route::get('target-rules', TargetRules::class)
    ->middleware(['auth', 'verified'])
    ->name('target-rules');

Route::get('deposit-withdrawal', DepositWithdrawal::class)
    ->middleware(['auth', 'verified'])
    ->name('deposit-withdrawal');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
