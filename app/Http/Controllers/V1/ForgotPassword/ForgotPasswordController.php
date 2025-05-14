<?php

namespace App\Http\Controllers\V1\ForgotPassword;

use App\Http\Controllers\Controller;

class ForgotPasswordController extends Controller
{
    public function forgotPassword()
    {
        return view('backend.v1.forgot-password.index');
    }
}