<?php

namespace App\Http\Controllers\V1\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\V1\API\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function getDashboardData()
    {
        return $this->dashboardService->getDashboardData();
    }
}