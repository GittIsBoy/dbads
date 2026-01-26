<?php

return [
    'base_url' => env('ADSTERA_BASE_URL', 'https://api3.adsterratools.com/publisher'),
    'api_key' => env('ADSTERA_API_KEY', ''),
    'client_id' => env('ADSTERA_CLIENT_ID', ''),
    'endpoints' => [
        'login' => env('ADSTERA_LOGIN_ENDPOINT', '/auth/login'),
        'refresh' => env('ADSTERA_REFRESH_ENDPOINT', '/auth/refresh'),
    ],
];
