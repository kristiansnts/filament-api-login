<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External API Authentication URL
    |--------------------------------------------------------------------------
    |
    | The URL endpoint for your external authentication API. This is where
    | the package will send login credentials for validation.
    |
    */

    'api_url' => env('FILAMENT_API_LOGIN_URL', 'https://your-api.com/api/auth'),

    /*
    |--------------------------------------------------------------------------
    | API Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests. Adjust this based on your
    | external API's typical response time.
    |
    */

    'timeout' => env('FILAMENT_API_LOGIN_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging of authentication failures.
    | Useful for debugging and monitoring.
    |
    */

    'log_failures' => env('FILAMENT_API_LOGIN_LOG_FAILURES', true),

];