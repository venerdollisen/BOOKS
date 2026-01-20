<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubsidiaryAccountController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\ReportController;
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
        Route::get('check', [AuthController::class, 'check'])->name('auth.check');
    });

    // Chart of Accounts routes
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
        Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/hierarchy', [AccountController::class, 'hierarchy'])->name('accounts.hierarchy');
        Route::get('/{account}', [AccountController::class, 'show'])->name('accounts.show');
        Route::put('/{account}', [AccountController::class, 'update'])->name('accounts.update');
        Route::delete('/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    });

    // Transactions routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');
        Route::post('/', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::post('/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
        Route::post('/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');
    });

    // Departments routes
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('departments.show');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    });

    // Projects routes
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
        Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    // Subsidiary Accounts routes
    Route::prefix('subsidiary-accounts')->group(function () {
        Route::get('/', [SubsidiaryAccountController::class, 'index'])->name('subsidiary-accounts.index');
        Route::post('/', [SubsidiaryAccountController::class, 'store'])->name('subsidiary-accounts.store');
        Route::get('/{subsidiaryAccount}', [SubsidiaryAccountController::class, 'show'])->name('subsidiary-accounts.show');
        Route::put('/{subsidiaryAccount}', [SubsidiaryAccountController::class, 'update'])->name('subsidiary-accounts.update');
        Route::delete('/{subsidiaryAccount}', [SubsidiaryAccountController::class, 'destroy'])->name('subsidiary-accounts.destroy');
    });

    // Periods routes
    Route::prefix('periods')->group(function () {
        Route::get('/', [PeriodController::class, 'index'])->name('periods.index');
        Route::post('/', [PeriodController::class, 'store'])->name('periods.store');
        Route::get('/{period}', [PeriodController::class, 'show'])->name('periods.show');
        Route::put('/{period}', [PeriodController::class, 'update'])->name('periods.update');
        Route::post('/{period}/close', [PeriodController::class, 'close'])->name('periods.close');
        Route::delete('/{period}', [PeriodController::class, 'destroy'])->name('periods.destroy');
    });

    // Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('/gl-summary', [ReportController::class, 'glSummary'])->name('reports.gl-summary');
        Route::get('/gl/{account}', [ReportController::class, 'generalLedger'])->name('reports.gl');
    });
});

