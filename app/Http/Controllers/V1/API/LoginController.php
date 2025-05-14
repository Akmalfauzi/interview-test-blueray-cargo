<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\Auth\API\V1\LoginService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService
    ) {}

    /**
     * Handle user login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $response = $this->loginService->login($request->toDTO());

        if (!$response) {
            return ApiResponse::unauthorized('Invalid login credentials');
        }

        return ApiResponse::success(
            data: $response->toArray(),
            message: $response->message
        );
    }
}
    