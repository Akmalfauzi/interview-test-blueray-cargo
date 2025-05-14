<?php

namespace App\Http\Controllers\V1\Login;

use App\DTOs\V1\Login\LoginRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Login\LoginRequest;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Auth\LoginService;

class LoginController extends Controller
{

    public function __construct(
        private readonly LoginService $loginService
    ) {}


    public function login()
    {
        return view('backend.v1.login.index');
    }

    public function processLogin(LoginRequest $request)
    {
        $login = $this->loginService->login($request->toDTO());
        if (!$login) {
            return ApiResponse::unauthorized('Invalid login credentials');
        }

        return ApiResponse::success(
            data: $login->toArray(),
            message: $login->message
        );
    }
}