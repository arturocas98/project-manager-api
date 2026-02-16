<?php

use App\Http\Middleware\CheckProjectAdmin;
use App\Http\Controllers\ProjectController;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/projects', ProjectController::class)->except(['update','destroy']);
    Route::middleware([CheckProjectAdmin::class])->group(function () {
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::patch('/projects/{project}', [ProjectController::class, 'update']); // Para PATCH
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    });
    Route::post('/add-participant', [App\Http\Controllers\ProjectController::class, 'addParticipant']);
    Route::apiResource('/teams', App\Http\Controllers\ProjectController::class);
});
