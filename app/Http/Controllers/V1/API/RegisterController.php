<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Register\RegisterRequest;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Auth\RegisterService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterService $registerService
    ) {}

    /**
     * Handle user registration request.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $response = $this->registerService->register($request->toDTO());

        return ApiResponse::success(
            data: $response->toArray(),
            message: $response->message,
            statusCode: 201
        );
    }
}
