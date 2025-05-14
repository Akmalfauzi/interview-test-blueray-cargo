<?php

namespace App\Services\V1\API\ThirdParty\Biteship;

use App\Exceptions\V1\ThirdParty\BiteshipApiOrderException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Class BiteshipService
 *
 * Service class untuk berinteraksi dengan Biteship API.
 * Bertanggung jawab untuk mengelola konfigurasi, membuat request, dan menangani response.
 */
class BiteshipService
{
    /**
     * Endpoint terkait order.
     */
    private const ENDPOINT_ORDERS = '/orders';

    protected readonly string $version;
    protected readonly bool $isProduction;
    protected readonly string $apiUrl;
    protected readonly string $apiKey;

    /**
     * Constructor untuk BiteshipService.
     *
     * Menginisialisasi properti berdasarkan konfigurasi yang ada di `config/services.php`.
     * Melemparkan InvalidArgumentException jika API Key tidak dikonfigurasi.
     *
     * @throws InvalidArgumentException Jika Biteship API Key tidak ditemukan.
     */
    public function __construct()
    {
        // `services.biteship.development` lebih baik dinamai `services.biteship.sandbox_mode` atau `services.biteship.is_sandbox`
        // Jika `true`, maka mode sandbox/development aktif. Jika `false`, mode produksi.
        // Di sini, `isProduction` akan `true` jika `services.biteship.development` adalah `false` (default).
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

    /**
     * Membangun URL lengkap untuk endpoint API Biteship.
     *
     * @param string $endpoint Path endpoint API (e.g., '/orders').
     * @return string URL lengkap.
     */
    private function buildUrl(string $endpoint): string
    {
        return rtrim($this->apiUrl, '/') . '/' . $this->version . '/' . ltrim($endpoint, '/');
    }

    /**
     * Menyiapkan instance HTTP client dengan header yang diperlukan.
     *
     * @param bool $withContentTypeJson Menentukan apakah header 'Content-Type: application/json' perlu ditambahkan.
     * @return PendingRequest Instance HTTP client yang siap digunakan.
     * @throws BiteshipApiOrderException Jika API Key tidak tersedia saat metode ini dipanggil.
     */
    private function httpClient(bool $withContentTypeJson = false): PendingRequest
    {
        $headers = ['Authorization' => 'Bearer ' . $this->apiKey];
        if ($withContentTypeJson) {
            $headers['Content-Type'] = 'application/json';
        }

        return Http::withHeaders($headers)
            ->acceptJson();
    }

    /**
     * Membuat order baru melalui Biteship API.
     *
     * @param array $data Payload untuk membuat order.
     * @return array Respons JSON dari API jika sukses.
     * @throws BiteshipApiOrderException Jika terjadi kesalahan selama proses request atau respons API.
     */
    public function createOrder(array $data): array
    {
        $url = $this->buildUrl(self::ENDPOINT_ORDERS);
        try {
            $response = $this->httpClient(true)->post($url, $data); // Mengirim Content-Type JSON
            return $this->handleResponse($response, $url, 'POST', $data);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Biteship API Create Order RequestException:', [
                'url' => $url,
                'payload' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Tambahkan trace untuk debugging lebih detail
            ]);
            throw new BiteshipApiOrderException(
                'Koneksi ke API Biteship gagal saat membuat order: ' . $e->getMessage(),
                $e->getCode(), // Gunakan kode dari exception asli jika ada
                'CONN_ERROR_CREATE_ORDER',
                $e
            );
        }
    }

    /**
     * Mengambil detail order dari Biteship API berdasarkan ID order.
     *
     * @param string $orderId ID order yang ingin diambil.
     * @return array Respons JSON dari API jika sukses.
     * @throws BiteshipApiOrderException Jika terjadi kesalahan selama proses request atau respons API.
     */
    public function getOrder(string $orderId): array
    {
        $url = $this->buildUrl(self::ENDPOINT_ORDERS . '/' . $orderId);
        try {
            $response = $this->httpClient()->get($url);
            return $this->handleResponse($response, $url, 'GET');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Biteship API Get Order RequestException:', [
                'url' => $url,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Tambahkan trace
            ]);
            throw new BiteshipApiOrderException(
                'Koneksi ke API Biteship gagal saat mengambil order: ' . $e->getMessage(),
                $e->getCode(),
                'CONN_ERROR_GET_ORDER',
                $e
            );
        }
    }

    /**
     * Menangani respons dari HTTP request ke Biteship API.
     * Menganalisis status kode dan body respons untuk menentukan keberhasilan atau kegagalan.
     *
     * @param Response $response Objek respons dari HTTP client.
     * @param string $url URL yang di-request.
     * @param string $method Metode HTTP yang digunakan (GET, POST, dll. untuk logging).
     * @param array|null $payload Payload yang dikirim (opsional, untuk logging).
     * @return array Data JSON dari respons jika sukses.
     * @throws BiteshipApiOrderException Jika respons menunjukkan kegagalan atau format tidak sesuai.
     */
    private function handleResponse(Response $response, string $url, string $method, ?array $payload = null): array
    {
        $httpStatusCode = $response->status();

        // Kasus 1: Respons Gagal (status code 4xx atau 5xx)
        if ($response->failed()) {
            $responseData = $response->json(); // Coba parse JSON error response
            $biteshipSpecificCode = $responseData['code'] ?? null; // Kode error dari Biteship (jika ada)
            $apiResponseMessage = $responseData['message'] ?? ($response->reason() ?: 'Unknown API error');

            $errorContext = [
                'method' => $method,
                'url' => $url,
                'status_code' => $httpStatusCode,
                'biteship_code' => $biteshipSpecificCode,
                'api_message' => $apiResponseMessage,
                'response_body' => $response->body(), // Raw body untuk debug
            ];
            if ($payload) {
                $errorContext['payload'] = $payload;
            }
            Log::error('Biteship API Request Gagal:', $errorContext);

            throw new BiteshipApiOrderException(
                apiResponseMessage: $apiResponseMessage,
                httpStatusCode: $httpStatusCode, // HTTP status code
                biteshipErrorCode: (string) $biteshipSpecificCode,
                response: $response // Sertakan objek response utuh
            );
        }

        // Kasus 2: Respons Sukses (status code 2xx)
        $responseData = $response->json();

        // Periksa jika json() mengembalikan null padahal body tidak kosong (indikasi bukan JSON valid)
        if ($responseData === null && strlen(trim($response->body())) > 0) {
            Log::warning('Biteship API respons sukses tetapi bukan JSON valid atau null:', [
                'method' => $method,
                'url' => $url,
                'status_code' => $httpStatusCode,
                'response_body' => $response->body()
            ]);
            throw new BiteshipApiOrderException(
                apiResponseMessage: 'Respons dari Biteship API bukan JSON yang valid atau mengembalikan null meskipun status sukses.',
                httpStatusCode: $httpStatusCode,
                biteshipErrorCode: 'INVALID_JSON_SUCCESS_RESPONSE',
                response: $response
            );
        }

        // Periksa flag 'success: false' dalam body respons meskipun status HTTP adalah 2xx
        // Beberapa API mengembalikan status 200 OK namun ada indikator error di dalam body JSON.
        if (isset($responseData['success']) && $responseData['success'] === false) {
            $biteshipSpecificCode = $responseData['code'] ?? 'APP_LEVEL_ERROR_IN_SUCCESS_RESPONSE';
            $apiResponseMessage = $responseData['message'] ?? 'Biteship melaporkan kegagalan di body respons.';

            Log::error('Biteship API melaporkan kegagalan dalam body respons (status 2xx):', [
                'method' => $method,
                'url' => $url,
                'payload' => $payload,
                'status_code' => $httpStatusCode, // Akan tetap 2xx
                'biteship_code' => $biteshipSpecificCode,
                'api_message' => $apiResponseMessage,
                'response_body' => $responseData
            ]);

            throw new BiteshipApiOrderException(
                apiResponseMessage: $apiResponseMessage,
                httpStatusCode: $httpStatusCode, // Status HTTP tetap 2xx
                biteshipErrorCode: (string) $biteshipSpecificCode,
                response: $response
            );
        }

        // Jika semua pemeriksaan lolos, kembalikan data atau array kosong jika responseData null (misal response 204 No Content)
        return $responseData ?? [];
    }
}
