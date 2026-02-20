<?php

use App\Enums\RoleName;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest:api'])->group(function () {
    Route::post('/login', [Auth\AuthenticationController::class, 'login']);
    Route::post('/register', [Auth\AuthenticationController::class, 'register']);
    Route::post('/two-factor-challenge', [Auth\TwoFactorAuthenticatedTokenController::class, 'store'])
        ->middleware(['throttle:3,1']);

    Route::post('/forgot-password', [Auth\ForgotPasswordController::class, 'send']);
    Route::post('/reset-password', [Auth\ForgotPasswordController::class, 'reset']);

    Route::get('/email/verify/{id}/{hash}', [Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('api.email-verification.verify');
});

Route::middleware(['auth:api', 'role:' . RoleName::Admin->value])->group(function () {
    Route::get('/users', [UserController::class, 'index']);

    Route::get('/users/{id}', [UserController::class, 'show'])->whereNumber(['id']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update'])->whereNumber(['id']);
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->whereNumber(['user']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('/email/verification-notification', [Auth\EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1');

    Route::post('/logout', [Auth\AuthenticationController::class, 'logout']);
});
