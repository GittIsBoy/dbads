<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.main');
// keep legacy /main URL redirecting to root
Route::get('/main', function () { return redirect('/'); });
Route::get('/calculate', [DashboardController::class, 'calculate'])->name('dashboard.calculate');
