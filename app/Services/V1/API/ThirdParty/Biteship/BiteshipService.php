<?php

namespace App\Services\V1\API\ThirdParty\Biteship;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Http\Responses\API\V1\ApiResponse;
use Illuminate\Http\JsonResponse;

class BiteshipService
{
    protected readonly bool $isProduction;
    protected readonly string $version;
    protected readonly string $apiKey;
    protected readonly string $apiUrl;

    public function __construct() {
        $this->isProduction = (bool) config('services.biteship.is_production', false);
        $this->version = config('services.biteship.version', 'v1');
        $this->apiKey = config('services.biteship.api_key');
        $this->apiUrl = config('services.biteship.api_url');

        if (empty($this->apiKey)) {
            $errorMessage = 'Biteship API Key tidak dikonfigurasi. Harap periksa file .env atau konfigurasi services.biteship.';
            Log::critical($errorMessage);
            throw new InvalidArgumentException($errorMessage);
        }

        if (empty($this->apiUrl)) {
            $errorMessage = 'Biteship API URL tidak dikonfigurasi. Harap periksa file .env atau konfigurasi services.biteship.';
            Log::critical($errorMessage);
            throw new InvalidArgumentException($errorMessage);
        }
    }

    public function getCouriers(): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->apiUrl . '/v1/couriers');

            if (!$response->successful()) {
                Log::error('Biteship API Error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                
                return ApiResponse::error(
                    message: 'Gagal mengambil data kurir dari Biteship',
                    errors: $response->json(),
                    statusCode: $response->status()
                );
            }

            return ApiResponse::success(
                data: $response->json(),
                message: 'Berhasil mengambil data kurir'
            );

        } catch (\Exception $e) {
            Log::error('Biteship API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::serverError(
                message: 'Terjadi kesalahan saat mengambil data kurir',
                errors: $e->getMessage()
            );
        }
    }

    public function getMapLocation(array $data): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->apiUrl . '/v1/maps/areas', $data);

            if (!$response->successful()) {
                Log::error('Biteship API Error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                
                return ApiResponse::error(
                    message: 'Gagal mengambil data lokasi dari Biteship',
                    errors: $response->json(),
                    statusCode: $response->status()
                );
            }

            return ApiResponse::success(
                data: $response->json(),
                message: 'Berhasil mengambil data lokasi'
            );
        } catch (\Exception $e) {
            Log::error('Biteship API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::serverError(
                message: 'Terjadi kesalahan saat mengambil data lokasi',
                errors: $e->getMessage()
            );
        }
    }

    public function createOrder(array $data): JsonResponse
    {
        try {

            $orderData['origin_contact_name'] = $data['sender_name'];
            $orderData['origin_contact_phone'] = $data['sender_phone'];
            $orderData['origin_address'] = $data['sender_address'];
            $orderData['origin_area_id'] = $data['sender_address_id'];

            $orderData['destination_contact_name'] = $data['receiver_name'];
            $orderData['destination_contact_phone'] = $data['receiver_phone'];
            $orderData['destination_address'] = $data['receiver_address'];
            $orderData['destination_area_id'] = $data['receiver_address_id'];

            $orderData['courier_company'] = 'jne';
            $orderData['courier_type'] = 'reg';
            // $orderData['courier_company'] = $data['courier_name'];
            // $orderData['courier_type'] = $data['service_type'];
            
            // $orderData['delivery_type'] = $data['delivery_type'];
            $orderData['delivery_type'] = 'now';

            $orderData['order_note'] = $data['notes'];
            $orderData['items'] = $data['items'];

            Log::info('Order Data', ['orderData' => $orderData]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/v1/orders', $orderData);

            if (!$response->successful()) {
                Log::error('Biteship API Error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                
                return ApiResponse::error(
                    message: 'Gagal membuat order di Biteship',
                    errors: $response->json(),
                    statusCode: $response->status()
                );
            }

            return ApiResponse::success(
                data: $response->json(),
                message: 'Order berhasil dibuat di Biteship'
            );
        } catch (\Exception $e) {
            Log::error('Biteship API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::serverError(
                message: 'Terjadi kesalahan saat membuat order di Biteship! ' . $e->getMessage(),
                errors: $e->getMessage()
            );
        }
    }
    

}