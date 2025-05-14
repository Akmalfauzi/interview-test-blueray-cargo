<?php

namespace App\Http\Controllers\V1\Register;

use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function register()
    {
        $title = 'Register';
        $description = 'Register';
        $keywords = 'Register';
        
        return view('backend.v1.register.index', compact('title', 'description', 'keywords'));
    }
}