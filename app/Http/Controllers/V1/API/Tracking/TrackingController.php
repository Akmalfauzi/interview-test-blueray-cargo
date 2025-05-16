<?php

namespace App\Http\Controllers\V1\API\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Models\Tracking;
use App\Services\V1\API\Tracking\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function __construct(
        private readonly TrackingService $trackingService
    ) {}

    public function track($number)
    {
        try {
            $trackingData = $this->trackingService->getTrackingInfo($number);
            return ApiResponse::success($trackingData, 'Data tracking berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Error retrieving tracking data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::error(
                message: 'Gagal mengambil data tracking: ' . $e->getMessage()
            );
        }
    }

    public function history(Request $request)
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

            // Query builder untuk trackings
            $query = Tracking::query()
                ->orderBy('created_at', 'desc');

            // Implementasi pencarian
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("json_extract(raw_biteship_payload, '$.status') = ?", [$search])
                        ->orWhereRaw("json_extract(raw_biteship_payload, '$.courier.name') = ?", [$search])
                        ->orWhereRaw("json_extract(raw_biteship_payload, '$.courier.tracking_id') = ?", [$search]);
                });
            }

            // cek apakah user adalah admin
            if (!auth()->user()->hasRole('admin')) {
                $query->where('user_id', auth()->user()->id);
            }

            // Ambil data dengan pagination
            $trackings = $query->paginate($perPage, ['*'], 'page', $page);

            return ApiResponse::success(
                data: $trackings,
                message: 'Daftar tracking berhasil diambil'
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving tracking history: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::serverError(
                message: 'Gagal mengambil daftar tracking: ' . $e->getMessage()
            );
        }
    }
} 