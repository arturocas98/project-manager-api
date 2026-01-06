<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web', config('jetstream.auth_session'), 'verified'])
    ->group(base_path('routes/web/_dashboard.php'));
