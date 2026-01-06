<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/')
    ->group(base_path('routes/api/_auth.php'));

Route::prefix('/user')
    ->middleware(['auth:api'])
    ->group(base_path('routes/api/_user.php'));
