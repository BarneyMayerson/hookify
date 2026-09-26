<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectRuleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projects/{project}/rules', [ProjectRuleController::class, 'update'])->name('projects.rules.update');
});

Route::get('auth/github/redirect', [GitHubController::class, 'redirect'])->name('auth.github.redirect');
Route::get('auth/github/callback', [GitHubController::class, 'callback'])->name('auth.github.callback');

require __DIR__.'/settings.php';
