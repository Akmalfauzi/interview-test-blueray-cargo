<?php

namespace App\Services\V1\API\Dashboard;

use App\Http\Responses\API\V1\ApiResponse;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function getDashboardData()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            // Admin view - all orders and statistics
            $data = [
                'total_orders' => Order::count(),
                'confirmed_orders' => Order::where('status', 'confirmed')->count(),
                'allocated_orders' => Order::where('status', 'allocated')->count(),
                'picking_up_orders' => Order::where('status', 'pickingUp')->count(),
                'picked_orders' => Order::where('status', 'picked')->count(),
                'dropping_off_orders' => Order::where('status', 'droppingOff')->count(),
                'return_in_transit_orders' => Order::where('status', 'returnInTransit')->count(),
                'on_hold_orders' => Order::where('status', 'onHold')->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'rejected_orders' => Order::where('status', 'rejected')->count(),
                'courier_not_found_orders' => Order::where('status', 'courierNotFound')->count(),
                'returned_orders' => Order::where('status', 'returned')->count(),
                'cancelled_orders' => Order::where('status', 'cancelled')->count(),
                'disposed_orders' => Order::where('status', 'disposed')->count(),
                'recent_orders' => Order::with(['user'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'total_users' => User::count(),
            ];
        } else {
            // User view - only their orders
            $data = [
                'total_orders' => Order::where('user_id', $user->id)->count(),
                'confirmed_orders' => Order::where('user_id', $user->id)->where('status', 'confirmed')->count(),
                'allocated_orders' => Order::where('user_id', $user->id)->where('status', 'allocated')->count(),
                'picking_up_orders' => Order::where('user_id', $user->id)->where('status', 'pickingUp')->count(),
                'picked_orders' => Order::where('user_id', $user->id)->where('status', 'picked')->count(),
                'dropping_off_orders' => Order::where('user_id', $user->id)->where('status', 'droppingOff')->count(),
                'return_in_transit_orders' => Order::where('user_id', $user->id)->where('status', 'returnInTransit')->count(),
                'on_hold_orders' => Order::where('user_id', $user->id)->where('status', 'onHold')->count(),
                'delivered_orders' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
                'rejected_orders' => Order::where('user_id', $user->id)->where('status', 'rejected')->count(),
                'courier_not_found_orders' => Order::where('user_id', $user->id)->where('status', 'courierNotFound')->count(),
                'returned_orders' => Order::where('user_id', $user->id)->where('status', 'returned')->count(),
                'cancelled_orders' => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
                'disposed_orders' => Order::where('user_id', $user->id)->where('status', 'disposed')->count(),
                'recent_orders' => Order::where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        }

        return ApiResponse::success($data, 'Dashboard data retrieved successfully');
    }
}