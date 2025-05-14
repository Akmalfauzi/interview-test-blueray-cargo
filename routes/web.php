<?php

use App\Http\Controllers\V1\API\Dashboard\DashboardController as APIDashboardController;
use App\Http\Controllers\V1\API\Role\RoleController as APIRoleController;
use App\Http\Controllers\V1\API\RolePermission\RolePermissionController;
use App\Http\Controllers\V1\API\User\UserController as APIUserController;
use App\Http\Controllers\V1\Dashboard\DashboardController;
use App\Http\Controllers\V1\ForgotPassword\ForgotPasswordController;
use App\Http\Controllers\V1\Login\LoginController;
use App\Http\Controllers\V1\Register\RegisterController;
use App\Http\Controllers\V1\Order\OrderController;
use App\Http\Controllers\V1\ResetPassword\ResetPasswordController;
use App\Http\Controllers\V1\Role\RoleController;
use App\Http\Controllers\V1\Tracking\TrackingController;
use App\Http\Controllers\V1\Tracking\TrackingHistoryController;
use App\Http\Controllers\V1\User\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'login'])->name('login');
});

// Register
Route::get('/register', [RegisterController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth:sanctum')->group(function () {

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('forgot-password');

    // Reset Password
    Route::get('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('reset-password');

    // Dashboard

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
});

//-- API --//
Route::prefix('api/v1')->as('api.v1.')->group(function () {
    // Public routes
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');

    // Protected routes
    Route::middleware(['auth:sanctum', 'ensure_accepts_json'])->group(function () {
        // Dashboard data
        Route::get('/dashboard', [APIDashboardController::class, 'getDashboardData'])
            ->name('dashboard');

        // Roles
        Route::prefix('roles')->as('roles.')->group(function () {
            Route::get('/', [APIRoleController::class, 'index'])->name('index');
            Route::post('/', [APIRoleController::class, 'store'])->name('store');
            Route::get('/{role}', [APIRoleController::class, 'show'])->name('show');
            Route::put('/{role}', [APIRoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [APIRoleController::class, 'destroy'])->name('destroy');
            
            // Role Permissions Routes
            Route::get('/{role}/permissions', [RolePermissionController::class, 'getPermissions'])->name('permissions.index');
            Route::post('/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])->name('permissions.update');
        });

        // Users
        Route::prefix('users')->as('users.')->group(function () {
            Route::get('/', [APIUserController::class, 'index'])->name('index');
            Route::post('/', [APIUserController::class, 'store'])->name('store');
            Route::get('/{user}', [APIUserController::class, 'show'])->name('show');
            Route::put('/{user}', [APIUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [APIUserController::class, 'destroy'])->name('destroy');

            // User Roles
            Route::get('/{user}/roles', [APIUserController::class, 'getRoles'])->name('roles.index');

            // User Roles post
            Route::post('/{user}/roles', [APIUserController::class, 'updateRoles'])->name('roles.update');
        });
    });
});