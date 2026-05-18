<?php

use App\Http\Controllers\Web\AccountPasswordController;
use App\Http\Controllers\Web\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Web\Admin\OperatorController as AdminOperatorController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Guest\AuthController;
use App\Http\Controllers\Web\Operator\PlcConnectionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/account/password', [AccountPasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [AccountPasswordController::class, 'update'])->name('account.password.update');

    Route::get('/settings/plc', [PlcConnectionController::class, 'index'])->name('settings.plc');
    Route::put('/settings/plc/{plc_device}', [PlcConnectionController::class, 'update'])->name('settings.plc.update');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/operators', [AdminOperatorController::class, 'index'])->name('operators.index');
        Route::get('/operators/create', [AdminOperatorController::class, 'create'])->name('operators.create');
        Route::post('/operators', [AdminOperatorController::class, 'store'])->name('operators.store');
        Route::get('/operators/{user}/edit', [AdminOperatorController::class, 'edit'])->name('operators.edit');
        Route::put('/operators/{user}', [AdminOperatorController::class, 'update'])->name('operators.update');
        Route::delete('/operators/{user}', [AdminOperatorController::class, 'destroy'])->name('operators.destroy');
        Route::patch('/operators/{user}/toggle', [AdminOperatorController::class, 'toggle'])->name('operators.toggle');
        Route::patch('/operators/{user}/password', [AdminOperatorController::class, 'resetPassword'])->name('operators.password');
        Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
