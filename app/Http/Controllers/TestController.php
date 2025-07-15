<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TestController extends Controller
{
    public function testDbs()
    {
        Cache::put('test_cache', 'This is a test value');
        Redis::set('test_redis', 'This is a test value');

        $dbRow = DB::table('test')->first();

        return response()->json([
            'cacheTest' => Cache::get('test_cache'),
            'redisTest' => Redis::get('test_redis'),
            'dbTest' => $dbRow
        ]);
    }

    public function testRabbitMQ()
    {
        try {
            $host = config('rabbitmq.host');
            $port = config('rabbitmq.port');
            $user = config('rabbitmq.user');
            $password = config('rabbitmq.password');

            // Create connection
            $connection = new AMQPStreamConnection($host, $port, $user, $password);
            $channel = $connection->channel();

            // Declare a queue
            $queueName = 'test_queue';
            $channel->queue_declare($queueName, false, false, false, false);

            // Create a test message
            $messageBody = json_encode([
                'test' => 'RabbitMQ connection successful',
                'timestamp' => now()->toISOString(),
                'service' => 'auth-app'
            ]);

            $message = new AMQPMessage($messageBody, ['content_type' => 'application/json']);

            // Publish message to queue
            $channel->basic_publish($message, '', $queueName);

            // Consume the message to test both publish and consume
            $receivedMessage = null;
            $callback = function ($msg) use (&$receivedMessage, $channel) {
                $receivedMessage = json_decode($msg->body, true);
                $channel->basic_ack($msg->delivery_info['delivery_tag']);
            };

            $channel->basic_consume($queueName, '', false, false, false, false, $callback);

            // Process one message
            $channel->wait(null, false, 2); // Wait max 2 seconds

            // Clean up
            $channel->queue_delete($queueName);
            $channel->close();
            $connection->close();

            return response()->json([
                'status' => 'success',
                'message' => 'RabbitMQ connection and message handling successful',
                'connection_details' => [
                    'host' => $host,
                    'port' => $port,
                    'user' => $user
                ],
                'published_message' => json_decode($messageBody, true),
                'received_message' => $receivedMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'RabbitMQ connection failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
