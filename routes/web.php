<?php

use App\Livewire\Analytics;
use App\Livewire\DashboardOverview;
use App\Livewire\DailyLogTable;
use App\Livewire\DepositWithdrawal;
use App\Livewire\Journal;
use App\Livewire\TargetRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

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

Route::get('analytics', Analytics::class)
    ->middleware(['auth', 'verified'])
    ->name('analytics');

Route::get('journal', Journal::class)
    ->middleware(['auth', 'verified'])
    ->name('journal');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('logout', function () {
    Auth::guard('web')->logout();
    Session::invalidate();
    Session::regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

require __DIR__.'/auth.php';
