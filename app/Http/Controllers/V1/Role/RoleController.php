<?php

namespace App\Http\Controllers\V1\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('backend.v1.role.index');
    }
}
