<?php

use App\Http\Controllers\API\V1\LoginController;
use App\Http\Controllers\API\V1\LogoutController;
use App\Http\Controllers\API\V1\RegisterController;
use App\Http\Controllers\V1\API\Role\RoleController;
use App\Http\Controllers\V1\API\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);

    // Role
    Route::resource('roles', RoleController::class);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'index']);
        });
        // Route::post('/logout', [LogoutController::class, 'logout']);
    });
});
