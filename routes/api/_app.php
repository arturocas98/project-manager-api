<?php

use App\Http\Controllers\CantonController;
use App\Http\Controllers\IncidenceAssignedController;
use App\Http\Controllers\IncidenceComentController;
use App\Http\Controllers\IncidenceController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProvinceController;
use App\Http\Middleware\CheckRole;

Route::get('projects/{project}/files', [ProjectController::class, 'files'])
    ->name('projects.files.public');

Route::middleware(['auth:api', CheckRole::class])->group(function () {
    Route::get('notification', [NotificationController::class, 'index']);
    Route::get('notifications', [NotificationController::class, 'notifications']);
    Route::get('notification/{notification}', [NotificationController::class, 'show'])->whereNumber(['notification']);
    Route::get('notification/user/{user}', [NotificationController::class, 'user'])->whereNumber(['user']);
    //Route::post('/notification', [NotificationController::class, 'store']);
    Route::patch('/notification/{notification}/read', [NotificationController::class, 'read'])->whereNumber(['notification']);
    Route::get('projects', [ProjectController::class, 'index'])
        ->name('projects.index');
    Route::get('projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show');
    Route::post('projects', [ProjectController::class, 'store'])
        ->name('projects.store');
    Route::get('projects/{project}/summary', [ProjectController::class, 'summary'])
        ->name('projects.summary');
    Route::get('projects/{project}/my-role', [ProjectController::class, 'myRole'])
        ->name('projects.my-role');
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
    Route::get('projects/{project}/incidences/{incidence}/comments', [IncidenceComentController::class, 'index'])
        ->name('projects.incidences.coments.index');
    Route::post('projects/{project}/incidences/{incidence}/comments', [IncidenceComentController::class, 'store'])
        ->name('projects.incidences.coments.store');
    Route::put('projects/{project}/incidences/{incidence}/comments/{comment}', [IncidenceComentController::class, 'update'])
        ->name('projects.incidences.coments.update');
    Route::delete('projects/{project}/incidences/{incidence}/comments/{comment}', [IncidenceComentController::class, 'destroy'])
        ->name('projects.incidences.coments.delete');

    Route::get('projects/{project}/messages', [MessageController::class, 'index'])
        ->name('projects.messages.index');
    Route::post('projects/{project}/messages', [MessageController::class, 'store'])
        ->name('projects.messages.store');
    Route::put('projects/{project}/messages/{message}', [MessageController::class, 'update'])
        ->name('projects.messages.update');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])
        ->name('projects.messages.delete');


    Route::post('projects/{project}/incidences', [IncidenceController::class, 'store'])
        ->name('projects.incidences.store');
    Route::put('projects/{project}/incidences/{incidence}/update', [IncidenceController::class, 'update'])
        ->name('projects.incidences.update');
    Route::delete('projects/{project}/incidences/{incidence}', [IncidenceController::class, 'destroy'])
        ->name('projects.incidences.destroy')->whereNumber(['incidence', 'project']);
    Route::get('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'show'])
        ->name('incidences.assignment.show');
    Route::post('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'store'])
        ->name('incidences.assignment.store');
    Route::put('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'update'])
        ->name('incidences.assignment.update');
    Route::delete('incidences/{incidence}/assignment', [IncidenceAssignedController::class, 'destroy'])
        ->name('incidences.assignment.destroy');
    Route::apiResource('menus', MenuController::class);
    Route::get('media', [MediaController::class, 'index'])
        ->name('media.index');
    Route::post('media', [MediaController::class, 'store'])
        ->name('media.store');
    Route::get('media/{media}', [MediaController::class, 'show'])
        ->name('media.show')->whereNumber('media');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])
        ->name('media.destroy')->whereNumber('media');

    Route::get('provinces', [ProvinceController::class, 'index']);
    Route::get('cantons', [CantonController::class, 'index']);
});
