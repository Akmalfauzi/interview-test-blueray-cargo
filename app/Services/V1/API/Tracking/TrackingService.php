<?php

namespace App\Services\V1\API\Tracking;

use App\Models\Order;
use App\Models\Tracking;
use App\Services\V1\API\ThirdParty\Biteship\BiteshipService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    public function __construct(
        private readonly BiteshipService $biteshipService
    ) {}

    public function getTrackingInfo(string $number)
    {
        try {
            return $this->getBiteshipTracking($number);
        } catch (\Exception $e) {
            Log::error('Error in getTrackingInfo: ' . $e->getMessage(), [
                'number' => $number,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getBiteshipTracking(string $trackingId)
    {
        $response = $this->biteshipService->getTracking($trackingId);
        $responseData = json_decode($response->getContent(), true);

        if (!$responseData['success']) {
            throw new \Exception($responseData['message'] ?? 'Gagal mengambil data tracking dari Biteship');
        }

        $this->saveTracking($responseData['data']);

        return $responseData['data'];
    }

    public function saveTracking(array $data)
    {
        try {
            // Get all orders and filter in PHP for SQLite compatibility
            $orders = Order::all();
            $order = $orders->first(function ($order) use ($data) {
                $payload = is_array($order->raw_biteship_payload)
                    ? $order->raw_biteship_payload
                    : json_decode($order->raw_biteship_payload, true);
                return isset($payload['courier']['tracking_id']) && $payload['courier']['tracking_id'] === $data['id'];
            });

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Check if tracking record exists
            $tracking = Tracking::where('order_id', $order->id)->first();

            if ($tracking) {
                // Update existing tracking record
                $tracking->update([
                    'raw_biteship_payload' => $data,
                ]);
            } else {
                // Create new tracking record
                $tracking = Tracking::create([
                    'user_id' => Auth::user()->id,
                    'order_id' => $order->id,
                    'raw_biteship_payload' => $data,
                ]);
            }

            return $tracking;
        } catch (\Throwable $th) {
            Log::error('Error in saveTracking: ' . $th->getMessage(), [
                'data' => $data,
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }
} 