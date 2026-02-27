<?php

use App\Http\Controllers\Auth;
use App\Http\Middleware\RequirePasswordToken;
use Illuminate\Support\Facades\Route;

Route::get('/profile', [Auth\ProfileController::class, 'show']);
Route::patch('/profile', [Auth\ProfileController::class, 'update']);

Route::post('/confirm-password', [Auth\ConfirmablePasswordController::class, 'store'])
    ->middleware('throttle:3,1');

Route::prefix('/two-factor')
    ->middleware([RequirePasswordToken::class])
    ->group(function () {
        Route::post('/authentication', [Auth\TwoFactorAuthenticationController::class, 'store']);
        Route::delete('/authentication', [Auth\TwoFactorAuthenticationController::class, 'destroy']);

        Route::get('/qr-code', [Auth\TwoFactorQrCodeController::class, 'show']);
        Route::get('/secret-key', [Auth\TwoFactorSecretKeyController::class, 'show']);

        Route::get('/recovery-codes', [Auth\RecoveryCodeController::class, 'index']);
        Route::post('/recovery-codes', [Auth\RecoveryCodeController::class, 'store']);

        Route::post('/confirmed', [Auth\ConfirmedTwoFactorAuthenticationController::class, 'store']);
    });
