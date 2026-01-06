<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest:api'])->group(function () {
    Route::post('/login', [Auth\AuthenticationController::class, 'login']);
    Route::post('/two-factor-challenge', [Auth\TwoFactorAuthenticatedTokenController::class, 'store'])
        ->middleware(['throttle:3,1']);

    Route::post('/forgot-password', [Auth\ForgotPasswordController::class, 'send']);
    Route::post('/reset-password', [Auth\ForgotPasswordController::class, 'reset']);

    Route::get('/email/verify/{id}/{hash}', [Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('api.email-verification.verify');
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('/email/verification-notification', [Auth\EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1');

    Route::post('/logout', [Auth\AuthenticationController::class, 'logout']);
});
