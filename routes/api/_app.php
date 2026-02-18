<?php

use App\Http\Middleware\CheckRole;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/projects', ProjectController::class)->except(['update','destroy']);
    Route::middleware([CheckRole::class])->group(function () {
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::patch('/projects/{project}', [ProjectController::class, 'update']); // Para PATCH
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

        Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index']);
        Route::get('/projects/{project}/members/{member}', [ProjectMemberController::class, 'show']);
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store']);
        Route::patch('/projects/{project}/members/{member}/role', [ProjectMemberController::class, 'updateRole']);
        Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy']);
    });
    Route::middleware(['check.role'])->group(function () {

    });
    Route::apiResource('/teams', App\Http\Controllers\ProjectController::class);
});
