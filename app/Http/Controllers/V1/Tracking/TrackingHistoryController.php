<?php

namespace App\Http\Controllers\V1\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingHistoryController extends Controller
{
    public function index()
    {
        return view('backend.v1.tracking.history');
    }
}
