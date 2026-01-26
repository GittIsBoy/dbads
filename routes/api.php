<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdsteraAuthController;

Route::post('adstera/login', [AdsteraAuthController::class, 'login']);
Route::post('adstera/refresh', [AdsteraAuthController::class, 'refresh']);
Route::get('adstera/test-login', [AdsteraAuthController::class, 'testLogin']);
Route::get('adstera/domains', [AdsteraAuthController::class, 'domains']);
Route::get('adstera/placements', [AdsteraAuthController::class, 'placements']);
Route::get('adstera/domain/{domain}/placements', [AdsteraAuthController::class, 'domainPlacements']);
Route::get('dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'stats']);
