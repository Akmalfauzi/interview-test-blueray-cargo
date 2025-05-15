<?php

namespace App\Http\Controllers\V1\API\Order;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Order\OrderService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                ->with(['items', 'courier']) // Eager load relationships
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
        try {
            $order = $this->orderService->createOrder($request->all());
            return ApiResponse::success($order, 'Order berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::serverError(
                message: 'Gagal membuat order: ' . $e->getMessage()
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
            $order = Order::with(['items', 'courier'])
                ->find($id);

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

            // Cek apakah order bisa dihapus (misalnya hanya order dengan status tertentu)
            if (!in_array($order->status, ['PENDING', 'CANCELLED'])) {
                return ApiResponse::error(
                    message: 'Order tidak dapat dihapus karena statusnya ' . $order->status,
                    statusCode: 400
                );
            }

            // Hapus order dan relasinya
            $order->items()->delete(); // Hapus items terlebih dahulu
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
        Log::info('Webhook received', ['request' => $request->all()]);
        return response()->json(['message' => 'Webhook received']);
    }
}