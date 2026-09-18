<?php

use App\Http\Controllers\Api\ManifestController;
use App\Http\Middleware\AuthenticateProjectToken;
use Illuminate\Support\Facades\Route;

// Токен идёт в заголовке Authorization, а не в URL:
// путь попадает в логи nginx/прокси и историю, заголовок — нет.
Route::middleware(AuthenticateProjectToken::class)
    ->prefix('v1')
    ->group(function () {
        Route::get('/manifest', [ManifestController::class, 'show']);
    });
