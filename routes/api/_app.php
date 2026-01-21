<?php


Route::middleware(['auth:api'])->group(function () {

    Route::apiResource('/projects', App\Http\Controllers\ProjectController::class);
});
