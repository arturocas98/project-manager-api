<?php


Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/projects', App\Http\Controllers\ProjectController::class);
    Route::post('/add-participant', [App\Http\Controllers\ProjectController::class, 'addParticipant']);
    Route::apiResource('/teams', App\Http\Controllers\ProjectController::class);
});
