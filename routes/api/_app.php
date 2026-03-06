<?php

use App\Http\Controllers\IncidenceAssignedController;
use App\Http\Controllers\IncidenceController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Middleware\CheckRole;

Route::middleware(['auth:api', CheckRole::class])->group(function () {
    Route::get('projects', [ProjectController::class, 'index'])
        ->name('projects.index');
    Route::get('projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show');
    Route::post('projects', [ProjectController::class, 'store'])
        ->name('projects.store');
    Route::get('projects/{project}/summary', [ProjectController::class, 'summary'])
        ->name('projects.summary');
    Route::get('projects/{project}/unassigned-users', [ProjectController::class, 'getUnassignedUsers'])
        ->name('projects.unassigned-users');
    Route::put('projects/{project}', [ProjectController::class, 'update'])
        ->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
        ->name('projects.destroy');
    Route::get('projects/{project}/members/{member}', [ProjectMemberController::class, 'show'])
        ->name('projects.members.show');
    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])
        ->name('projects.members.store');
    Route::patch('projects/{project}/members/{member}/role', [ProjectMemberController::class, 'updateRole'])
        ->name('projects.members.updateRole');
    Route::delete('projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])
        ->name('projects.members.destroy');
    Route::get('projects/{project}/members', [ProjectMemberController::class, 'index'])
        ->name('projects.members.index');
    Route::get('projects/{project}/incidences', [IncidenceController::class, 'index'])
        ->name('projects.incidences.index');
    Route::get('projects/{project}/incidences/{incidence}', [IncidenceController::class, 'show'])
        ->name('projects.incidences.show');
    Route::post('projects/{project}/incidences', [IncidenceController::class, 'store'])
        ->name('projects.incidences.store');
    Route::put('projects/{project}/incidences/{incidence}/update', [IncidenceController::class, 'update'])
        ->name('projects.incidences.update');
    Route::delete('projects/{project}/incidences/{incidence}', [IncidenceController::class, 'destroy'])
        ->name('projects.incidences.destroy');
    Route::get('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'show'])
        ->name('incidences.assignment.show');
    Route::post('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'store'])
        ->name('incidences.assignment.store');
    Route::put('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'update'])
        ->name('incidences.assignment.update');
    Route::delete('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'destroy'])
        ->name('incidences.assignment.destroy');
    Route::apiResource('menus', MenuController::class);
    //Route::apiResource('teams', ProjectController::class);
});
