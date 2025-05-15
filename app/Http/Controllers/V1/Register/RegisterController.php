<?php

namespace App\Http\Controllers\V1\Register;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Register\RegisterRequest;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Auth\RegisterService;
class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterService $registerService
    ) {}

    public function register()
    {
        $title = 'Register';
        $description = 'Register';
        $keywords = 'Register';
        
        return view('backend.v1.register.index', compact('title', 'description', 'keywords'));
    }
}