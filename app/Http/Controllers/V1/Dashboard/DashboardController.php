<?php

namespace App\Http\Controllers\V1\Dashboard;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('backend.v1.dashboard.index');
    }
}
