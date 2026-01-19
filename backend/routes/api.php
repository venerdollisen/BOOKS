<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    // Add other protected routes here as needed
    // Route::get('/dashboard', [DashboardController::class, 'index']);
});
