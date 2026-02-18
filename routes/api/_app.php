<?php

use App\Http\Middleware\CheckRole;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Middleware\CheckProjectAdmin;
use App\Http\Controllers\IncidenciaAssignedController;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/projects', ProjectController::class)->except(['update','destroy']);
    Route::middleware([CheckProjectAdmin::class])->group(function () {
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::patch('/projects/{project}', [ProjectController::class, 'update']); // Para PATCH
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

        Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index']);
        Route::get('/projects/{project}/members/{member}', [ProjectMemberController::class, 'show']);
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store']);
        Route::patch('/projects/{project}/members/{member}/role', [ProjectMemberController::class, 'updateRole']);
        Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy']);
    });
    Route::middleware([CheckRole::class])->group(function () {
        Route::get('projects/incidences/{project}', [IncidenciaController::class, 'index']);
        Route::post('projects/incidences/{project}', [IncidenciaController::class, 'store']);
        Route::put('projects/incidences/{incidences}', [IncidenciaController::class, 'update']);
        Route::delete('projects/incidences/{incidences}', [IncidenciaController::class, 'destroy']);
        Route::get('/incidences/assigment/{incidences}', [IncidenciaAssignedController::class, 'show']);
        Route::post('/incidences/assigment/{incidences}', [IncidenciaAssignedController::class, 'store']);
        Route::put('/incidences/assigment/{incidences}/update', [IncidenciaAssignedController::class, 'update']);
        Route::delete('/incidences/assigment/{incidences}', [IncidenciaAssignedController::class, 'destroy']);
    });
    Route::apiResource('/teams', App\Http\Controllers\ProjectController::class);
});
