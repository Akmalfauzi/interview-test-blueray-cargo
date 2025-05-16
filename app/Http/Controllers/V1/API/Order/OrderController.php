<?php

namespace App\Http\Controllers\V1\API\Order;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Order\OrderService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function getCouriers()
    {
        $couriers = $this->orderService->getCouriers();
        return ApiResponse::success($couriers, 'Daftar kurir berhasil diambil');
    }

    public function getMapLocation(Request $request)
    {
        $data['input'] = $request->input('query');
        $data['type'] = $request->type ?? 'single';

        $mapLocation = $this->orderService->getMapLocation($data);
        return ApiResponse::success($mapLocation, 'Data lokasi berhasil diambil');
    }

    public function index(Request $request)
    {
        try {
            // Ambil parameter pagination dari request
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $search = $request->input('search', '');

            // Validasi parameter pagination
            if (!is_numeric($perPage) || $perPage <= 0) {
                $perPage = 10;
            }
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }

            // Query builder untuk orders
            $query = Order::query()
                ->orderBy('created_at', 'desc');

            // Implementasi pencarian
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', '%' . $search . '%')
                      ->orWhere('receiver_name', 'like', '%' . $search . '%')
                      ->orWhere('sender_name', 'like', '%' . $search . '%');
                });
            }

            // Ambil data dengan pagination
            $orders = $query->paginate($perPage, ['*'], 'page', $page);

            return ApiResponse::success(
                data: $orders,
                message: 'Daftar order berhasil diambil'
            );

        } catch (\Exception $e) {
            Log::error('Error retrieving orders from API: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::serverError(
                message: 'Gagal mengambil daftar order: ' . $e->getMessage()
            );
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'sender_address' => 'required|string',
            'receiver_name' => 'required|string',
            'receiver_phone' => 'required|string',
            'receiver_address' => 'required|string',
            'courier_code' => 'required',
            'courier_name' => 'required',
            'service_type' => 'required',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'required|numeric|min:0.1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                message: 'Validasi gagal',
                errors: $validator->errors(),
                statusCode: 422
            );
        }

        try {
            $order = $this->orderService->createOrder($request->all());
            return ApiResponse::success($order, 'Order berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            $statusCode = $e->getCode() ?: 500;
            return ApiResponse::error(
                message: $e->getMessage(),
                statusCode: $statusCode
            );
        }
    }
    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return ApiResponse::notFound('Order tidak ditemukan');
            }

            return ApiResponse::success(
                data: $order,
                message: 'Detail order berhasil diambil'
            );

        } catch (\Exception $e) {
            Log::error("Error retrieving order ID {$id} from API: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::serverError(
                message: 'Gagal mengambil detail order: ' . $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified order from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return ApiResponse::notFound('Order tidak ditemukan');
            }

            if (!in_array($order->status, ['confirmed'])) {
                return ApiResponse::error(
                    message: 'Order tidak dapat dihapus karena statusnya ' . $order->status,
                    statusCode: 400
                );
            }

            $order->delete();

            return ApiResponse::success(
                data: null,
                message: 'Order berhasil dihapus'
            );

        } catch (\Exception $e) {
            Log::error("Error deleting order ID {$id} via API: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::serverError(
                message: 'Gagal menghapus order: ' . $e->getMessage()
            );
        }
    }

    public function webhook(Request $request)
    {
        try {
            Log::info('Webhook received', ['request' => $request->all()]);
            Log::info('Order ID', ['order_id' => $request->order_id]);

            // Find the order
            $order = Order::where('raw_biteship_payload->id', $request->order_id)->first();
            
            if (!$order) {
                Log::error('Order not found', ['order_id' => $request->id]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update order status and store raw payload
            $order->update([
                'status' => $request->status,
                'raw_biteship_payload' => $request->all(),
                'status_updated_at' => now(),
            ]);

            Log::info('Order status updated successfully', [
                'order_id' => $order->order_number,
                'new_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Order status updated successfully',
                'order_id' => $order->order_number,
                'status' => $request->status
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error processing webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}