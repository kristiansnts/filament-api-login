<?php

namespace Kristiansnts\FilamentApiLogin\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalAuthService
{
    private string $apiUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('filament-api-login.api_url');
        $this->timeout = config('filament-api-login.timeout', 30);
    }

    /**
     * Authenticate user against external API
     */
    public function authenticate(string $email, string $password): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'email' => $email, 
                    'password' => $password,
                ]);

            if ($response->successful()) {
                $userData = $response->json();
                
                // Expected API response format:
                // {
                //     "token": "string",
                //     "data": {
                //         "id": "int/string",
                //         "email": "string",
                //         "role": "string", 
                //         "username": "string"
                //     }
                // }
                
                if (isset($userData['token']) && isset($userData['data'])) {
                    return $userData;
                }

                // Fallback for flat response format (backwards compatibility)
                if (isset($userData['token']) && isset($userData['username'])) {
                    return $userData;
                }

                // Fallback for different response formats
                if (is_array($userData) && !empty($userData)) {
                    return $userData;
                }
            }

            if (config('filament-api-login.log_failures', true)) {
                Log::warning('External API authentication failed', [
                    'email' => $email,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

            return null;

        } catch (\Exception $e) {
            if (config('filament-api-login.log_failures', true)) {
                Log::error('External API authentication error', [
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }

            return null;
        }
    }
}