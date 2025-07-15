<?php

return [
    'host' => env('RABBITMQ_HOST', 'auth-rabbitmq'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'auth_user'),
    'password' => env('RABBITMQ_PASSWORD', 'auth_password'),
];
