<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Login\LoginRequest;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Auth\LoginService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService
    ) {}

    /**
     * Handle user login request.
     */
    public function processLogin(LoginRequest $request): JsonResponse
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
    