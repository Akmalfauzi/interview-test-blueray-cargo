<?php

use App\Http\Controllers\V1\Dashboard\DashboardController;
use App\Http\Controllers\V1\Order\OrderController;
use App\Http\Controllers\V1\Role\RoleController;
use App\Http\Controllers\V1\Tracking\TrackingController;
use App\Http\Controllers\V1\Tracking\TrackingHistoryController;
use App\Http\Controllers\V1\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Order
Route::prefix('order')->as('order.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
});

// Tracking
Route::prefix('tracking')->as('tracking.')->group(function () {
    Route::get('/', [TrackingController::class, 'index'])->name('index');
    Route::get('/history', [TrackingHistoryController::class, 'index'])->name('history');
});

// User
Route::prefix('user')->as('user.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
});

// Role
Route::prefix('roles')->as('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
});
