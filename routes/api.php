<?php

use App\Http\Controllers\V1\API\LoginController;
use App\Http\Controllers\V1\API\LogoutController;
use App\Http\Controllers\V1\API\RegisterController;
use App\Http\Controllers\V1\API\Role\RoleController;
use App\Http\Controllers\V1\API\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\API\DashboardController;

// Route::prefix('v1')->as('v1.')->group(function () {
//     // Login
//     Route::post('/login', [LoginController::class, 'login'])
//         ->middleware(['throttle:5,1', StartSession::class])
//         ->name('login');

//     // Register
//     Route::post('/register', [RegisterController::class, 'register'])
//         ->middleware('throttle:5,1')
//         ->name('register');

//     // Role
//     Route::resource('roles', RoleController::class)->names('roles');

//     // Protected routes
//     Route::middleware('auth:sanctum')->group(function () {
//         // User
//         Route::prefix('user')->group(function () {
//             Route::get('/', [UserController::class, 'index'])->name('user.index');
//         });
//         // Route::post('/logout', [LogoutController::class, 'logout']);
//     });
// });

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::middleware(['auth:sanctum', 'accept.json'])->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index']);
// });
