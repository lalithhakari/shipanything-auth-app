<?php
// Simple health check endpoint that doesn't require Laravel bootstrapping
header('Content-Type: application/json');

try {
    // Check if basic PHP is working
    $health = [
        'status' => 'ok',
        'timestamp' => date('c'),
        'service' => 'auth-app'
    ];

    // Try to connect to database if environment variables are set
    if (isset($_ENV['DB_HOST']) && isset($_ENV['DB_DATABASE'])) {
        try {
            $dsn = "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}";
            $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
            $health['database'] = 'connected';
        } catch (Exception $e) {
            $health['database'] = 'disconnected';
            $health['db_error'] = $e->getMessage();
        }
    }

    // Try to connect to Redis if configured
    if (isset($_ENV['REDIS_HOST']) && extension_loaded('redis')) {
        try {
            $redis = new Redis();
            $redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'] ?? 6379);
            $redis->ping();
            $health['redis'] = 'connected';
            $redis->close();
        } catch (Exception $e) {
            $health['redis'] = 'disconnected';
        }
    }

    http_response_code(200);
    echo json_encode($health, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
