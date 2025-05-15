<?php

namespace App\Services\V1\API\Order;

use App\Services\V1\API\ThirdParty\Biteship\BiteshipService;

class OrderService
{

    public function __construct(
        private readonly BiteshipService $biteshipService
    ) {}

    public function createOrder(array $data)
    {
        $response = $this->biteshipService->createOrder($data);
        $responseData = json_decode($response->getContent(), true);
        
        return $responseData;
    }


    public function getCouriers(): array
    {
        $response = $this->biteshipService->getCouriers();
        $responseData = json_decode($response->getContent(), true);
        
        return $responseData['success'] 
            ? $responseData['data']['couriers'] 
            : [];
    }
}