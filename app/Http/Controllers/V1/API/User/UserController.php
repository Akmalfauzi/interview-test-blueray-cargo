<?php

namespace App\Http\Controllers\V1\API\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\User\UserService;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        $users = $this->userService->getAllUsers();

        return ApiResponse::success($users);
    }
}