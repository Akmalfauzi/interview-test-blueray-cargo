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
        $response = $this->biteshipService->createOrder($data);
        $responseData = json_decode($response->getContent(), true);

        if (!$responseData['success']) {
            throw new \Exception($responseData['message']);
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
            'status' => $responseData['data']['status'],
            'notes' => $data['notes'],
        ]);
        
        return $order;
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