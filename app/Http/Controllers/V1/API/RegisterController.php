<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\API\V1\RegisterRequest;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\Auth\API\V1\RegisterService;
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
