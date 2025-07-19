<?php

return [
    'frontend_spa_url' => env('FRONTEND_SPA_URL', 'http://localhost'),
    'auth_cookie_expiry' => env('AUTH_COOKIE_EXPIRY', 1440),
    'auth_cookie_http_only' => env('AUTH_COOKIE_HTTP_ONLY', false),
    'auth_cookie_secure' => env('AUTH_COOKIE_SECURE', false),
];
