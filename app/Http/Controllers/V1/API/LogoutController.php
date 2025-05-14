<?php

namespace App\Http\Controllers\V1\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\Auth\API\V1\LogoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private readonly LogoutService $logoutService
    ) {}

    /**
     * Handle user logout request.
     */
    public function logout(Request $request): JsonResponse
    {
        $response = $this->logoutService->logout($request->user());

        return ApiResponse::success(
            data: $response->toArray(),
            message: $response->message
        );
    }
} 