<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Auth\LogoutService;
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
    public function processLogout(Request $request): JsonResponse
    {
        $response = $this->logoutService->logout($request->user());

        return ApiResponse::success(
            data: $response->toArray(),
            message: $response->message
        );
    }
} 