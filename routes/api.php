<?php

use App\Http\Controllers\Api\ManifestController;
use App\Http\Middleware\AuthenticateProjectToken;
use Illuminate\Support\Facades\Route;

// The token is passed in the Authorization header, not in the URL:
// the URL path appears in Nginx/proxy logs and history, while headers do not.
Route::middleware(AuthenticateProjectToken::class)
    ->prefix('v1')
    ->group(function () {
        Route::get('/manifest', [ManifestController::class, 'show'])->name('api.v1.manifest');
    });
