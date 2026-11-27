<?php

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\Users\ImportUsersController;
use App\Http\Controllers\Api\Users\ProfileController;
use App\Http\Controllers\Api\Users\RevokeUserSessionsController;
use App\Http\Controllers\Api\Users\UnlockUserController;
use App\Http\Controllers\Api\Users\UserController;
use App\Http\Controllers\Api\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', LoginController::class)->middleware('throttle:login')->name('login');

    Route::post('forgot-password', ForgotPasswordController::class)->middleware('throttle:password-reset-link')->name('password.forgot');
    Route::post('reset-password', ResetPasswordController::class)->middleware('throttle:password-reset')->name('password.reset');

    Route::middleware(['auth:sanctum', 'active.user'])->group(function (): void {
        Route::post('logout', LogoutController::class)->name('logout');
        Route::get('me', MeController::class)->name('me');
    });
});

Route::middleware(['auth:sanctum', 'active.user'])->group(function (): void {
    Route::get('menu', MenuController::class)->name('menu');
    Route::get('welcome', WelcomeController::class)->middleware('can:welcome.view')->name('welcome');
    Route::get('dashboard', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
    Route::patch('profile', ProfileController::class)->name('profile.update');

    Route::controller(ImportUsersController::class)->prefix('users/imports')->name('users.imports.')->group(function (): void {
        Route::post('/', 'store')->name('store');
        Route::get('{import}', 'show')->middleware('can:view,import')->name('show');
        Route::get('{import}/rows', 'rows')->middleware('can:view,import')->name('rows');
    });
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/unlock', UnlockUserController::class)
        ->middleware('can:unlock,user')
        ->name('users.unlock');
    Route::post('users/{user}/sessions/revoke', RevokeUserSessionsController::class)
        ->middleware('can:revokeSessions,user')
        ->name('users.sessions.revoke');
});
