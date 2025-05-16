<?php

namespace App\Services\V1\API\Order;

use App\Models\Order;
use App\Services\V1\API\ThirdParty\Biteship\BiteshipService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderService
{

    public function __construct(
        private readonly BiteshipService $biteshipService
    ) {}

    public function createOrder(array $data)
    {
        try {
            $response = $this->biteshipService->createOrder($data);
            $responseData = json_decode($response->getContent(), true);

            if (!$responseData['success']) {
                Log::error('Failed to create order in Biteship', [
                    'data' => $data,
                    'response' => $responseData
                ]);
                throw new \Exception($responseData['message'] ?? 'Gagal membuat order di Biteship', 400);
            }

            // save to database
            $order = Order::create([
                'shipper_name' => $data['sender_name'],
                'shipper_phone' => $data['sender_phone'],
                'shipper_address' => $data['sender_address'],
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'receiver_address' => $data['receiver_address'],
                'user_id' => Auth::user()->id,
                'raw_biteship_payload' => $responseData['data'],
                'items' => $data['items'],
                'status' => $responseData['data']['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
            
            return $order;
        } catch (\Exception $e) {
            Log::error('Error creating order', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    public function getCouriers(): array
    {
        $response = $this->biteshipService->getCouriers();
        $responseData = json_decode($response->getContent(), true);
        
        return $responseData['success'] 
            ? $responseData['data']['couriers'] 
            : [];
    }

    public function getMapLocation(array $data)
    {
        $response = $this->biteshipService->getMapLocation($data);
        $responseData = json_decode($response->getContent(), true);
        
        return $responseData['success'] 
            ? $responseData['data']['areas'] 
            : [];
    }
}