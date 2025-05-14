<?php

namespace App\Http\Controllers\V1\ResetPassword;

use App\Http\Controllers\Controller;

class ResetPasswordController extends Controller
{
    public function resetPassword()
    {
        return view('backend.v1.reset-password.index');
    }
}