<?php

namespace App\Http\Controllers\V1\Order;

use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index()
    {
        return view('backend.v1.order.index');
    }
}
