<?php

use App\Http\Middleware\CheckProjectAdmin;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/projects', ProjectController::class)->except(['update','destroy']);
    Route::middleware([CheckProjectAdmin::class])->group(function () {
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::patch('/projects/{project}', [ProjectController::class, 'update']); // Para PATCH
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

        Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index']);
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store']);
        Route::patch('/projects/{project}/members/{member}/role', [ProjectMemberController::class, 'updateRole']);
        Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy']);
    });
    Route::apiResource('/teams', App\Http\Controllers\ProjectController::class);
});
