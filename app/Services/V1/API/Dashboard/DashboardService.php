<?php

namespace App\Services\V1\API\Dashboard;

use App\Http\Responses\API\V1\ApiResponse;
use App\Models\User;

class DashboardService
{
    public function getDashboardData()
    {
        $data = [
            'user_registrations' => User::count(),
        ];

        return ApiResponse::success($data, 'Dashboard data retrieved successfully');
    }
}