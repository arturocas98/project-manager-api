<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::view('/dashboard', 'dashboard')->name('dashboard');

Route::get('health', Spatie\Health\Http\Controllers\HealthCheckResultsController::class);
