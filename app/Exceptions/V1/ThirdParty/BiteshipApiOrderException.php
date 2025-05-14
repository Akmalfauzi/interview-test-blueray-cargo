<?php

namespace App\Exceptions\V1\ThirdParty;

use Exception;
use Illuminate\Http\Client\Response;

class BiteshipApiOrderException extends Exception
{
    public ?Response $response;
    public ?string $biteshipErrorCode = null; // Untuk menyimpan kode error spesifik dari Biteship (mis. '02001')

    /**
     * Daftar pesan error statis berdasarkan kode error Biteship.
     * Kunci adalah kode error dari Biteship.
     * Pesan di sini adalah fallback atau referensi; pesan dari API response (jika ada) lebih diutamakan
     * karena mungkin mengandung detail dinamis.
     */
    protected static array $errorMessagesMap = [
        // Authentication & General Configuration Errors
        '02001' => "Booking courier API Key is not found.",
        '02002' => "Key has not been activated.",
        '02003' => "There's an error with your authentication key. Please contact info@biteship.com for this matter.",
        // Order Creation Specific Errors (/v1/orders)
        '02004' => "This is direct Biteship order and you're missing type field.",
        '02005' => "Delivery type has no value. Please specify whether it's a 'Now', or 'Scheduled' delivery type.",
        '02006' => "Please make sure to input the right delivery type value.",
        '02007' => "Courier is not available for scheduled delivery.",
        '02008' => "Please make sure you requested the correct extra feature(s).",
        '02009' => "Courier is not available for cash on delivery service.",
        '02010' => "Either destination needs to have postal code or coordinate.",
        '02011' => "Either origin needs to have postal code or coordinate.",
        '02012' => "Destination address is required for this courier.",
        '02013' => "Time already passed. Set new delivery time.",
        '02014' => "Please make sure origin contact name is filled.",
        '02015' => "Please make sure origin contact phone is filled.",
        '02016' => "Please make sure origin address is filled.",
        '02017' => "Please make sure destination contact name is filled.",
        '02018' => "Please make sure destination contact phone is filled.",
        '02019' => "Please make sure destination address is filled.",
        '02020' => "Failed due to invalid or missing postal code. Please contact info@biteship.com for this matter.",
        '02021' => "There's something wrong getting the courier rates. Please contact info@biteship.com for this matter.",
        '02022' => "Origin coordinate must have both latitude and longitude value.",
        '02023' => "Selected courier needs to have origin coordinate value.",
        '02024' => "Destination coordinate must have both latitude and longitude value.",
        '02025' => "Selected courier needs to have destination coordinate value.",
        '02026' => "Selected courier does not exist.",
        '02027' => "Courier service type does not exist.",
        '02028' => "Your account is not available for {{Courier Name}} COD feature. (Pesan dari API akan lebih detail)",
        '02029' => "Please specify the correct COD Type: 3_days, 5_days, 7_days or leave it null to set as default 7_days.",
        '02030' => "Cash on delivery value cannot exceed Rp. 15.000.000.",
        '02031' => "Existing courier cannot provide cash on delivery.",
        '02032' => "Need to fill proof of delivery note.",
        '02033' => "Courier is not available for providing proof of delivery service.",
        '02034' => "Courier is not available for providing insurance.",
        '02035' => "Delivery date has not been specified.",
        '02036' => "Delivery time has not been specified.",
        '02037' => "Restriction for same day delivery order time.",
        '02038' => "There's something wrong with the order item. Please contact info@biteship.com for more information.",
        '02039' => "There's something wrong with the payment. Please contact info@biteship.com for more information.",
        '02040' => "There's something wrong with ordering partner. Please contact info@biteship.com for more information.",
        '02041' => "Failed to create order. Please contact info@biteship.com for this problem.",
        '02060' => "Reference id has already been used before. Please input other reference id.", // Duplikasi, cek konteks
        '02061' => "Delivery date must be in 'YYYY-MM-DD' format.",
        '02062' => "Delivery time must be in 'HH:mm' format.",
        '02999' => "Something is wrong with the order. Please contact info@biteship.com for more info.",
        // Get Order Specific Errors (/v1/orders/:id)
        '02042' => "Something went wrong when getting the order's details.",
        '02057' => "Order not found.",
        // Update/Cancel Order Specific Errors (/v1/orders/:id)
        '02043' => "Something is wrong when updating a new order. Please contact info@biteship.com for this matter.",
        '02044' => "Order has already been confirmed therefore cannot edit order. Please create a new order instead.",
        '02045' => "Cannot update order because shipment already delivered.",
        '02046' => "Cannot update order because shipment already cancelled.",
        '02047' => "Cannot update order because shipment is on process.",
        '02048' => "Something error in development mode.", // Biasanya untuk update/cancel
        '02049' => "Something went wrong with the payment when updating the new order.",
        '02050' => "Order has already been {{new status}}. (Pesan dari API akan lebih detail)",
        '02051' => "Order failed to confirm in development mode.",
        '02052' => "Failed to confirm order. Please contact info@biteship.com for this matter.",
        '02053' => "Cannot cancel order because order already {{status}}. (Pesan dari API akan lebih detail)",
        '02054' => "Failed to cancel development order.",
        '02055' => "Failed to cancel the courier. Please contact info@biteship.com for this matter.",
        '02056' => "Something wrong when cancelling an order. Please contact info@biteship.com for this matter.",
        '02058' => "Tags must be in array format.",
        '02059' => "Waybill id is already created and cannot be duplicated.",
        // Transaction/Payment Errors (biasanya /v1/orders)
        '09001' => "Lack of transaction data.", // Kode dari user 9001 dst.
        '09002' => "Payment method is not found.",
        '09003' => "Payment failed to process. Please contact info@biteship.com for this matter.",
        '09004' => "Failed to create transaction.",
    ];

    /**
     * BiteshipApiException constructor.
     *
     * @param string $apiResponseMessage Pesan error langsung dari response API Biteship (mungkin mengandung detail dinamis).
     * @param int $httpStatusCode Kode status HTTP (mis. 400, 401, 500).
     * @param string|null $biteshipErrorCode Kode error spesifik dari Biteship (mis. '02001').
     * @param \Throwable|null $previous Exception sebelumnya jika ada.
     * @param \Illuminate\Http\Client\Response|null $response Objek Response dari HTTP Client.
     */
    public function __construct(
        string $apiResponseMessage = "",
        int $httpStatusCode = 0,
        ?string $biteshipErrorCode = null,
        ?\Throwable $previous = null,
        ?Response $response = null
    ) {
        $this->biteshipErrorCode = $biteshipErrorCode;
        $this->response = $response;

        $finalMessage = trim($apiResponseMessage);

        // Jika pesan dari API kosong, coba gunakan pesan dari map statis berdasarkan kode error Biteship
        if (empty($finalMessage) && $biteshipErrorCode && isset(self::$errorMessagesMap[$biteshipErrorCode])) {
            $finalMessage = self::$errorMessagesMap[$biteshipErrorCode];
        } elseif (empty($finalMessage)) {
            // Fallback jika tidak ada pesan sama sekali
            $finalMessage = "Biteship API error occurred. HTTP Status: {$httpStatusCode}";
            if ($biteshipErrorCode) {
                $finalMessage .= ", Biteship Code: {$biteshipErrorCode}";
            }
        }

        // Tambahkan kode error Biteship ke pesan jika belum ada, untuk kejelasan
        if ($biteshipErrorCode && strpos($finalMessage, (string) $biteshipErrorCode) === false) {
            $finalMessage = "Biteship Error [{$biteshipErrorCode}]: " . $finalMessage;
        }

        // Kode exception di sini adalah HTTP status code, atau bisa juga 0 jika tidak relevan
        parent::__construct($finalMessage, $httpStatusCode, $previous);
    }

    /**
     * Mendapatkan pesan error yang terpetakan secara statis berdasarkan kode error Biteship.
     *
     * @param string $biteshipErrorCode
     * @return string|null
     */
    public static function getMappedErrorMessage(string $biteshipErrorCode): ?string
    {
        return self::$errorMessagesMap[$biteshipErrorCode] ?? null;
    }

    public function getResponseData(): ?array
    {
        return $this->response?->json();
    }

    public function getResponseStatus(): ?int
    {
        return $this->response?->status();
    }
}