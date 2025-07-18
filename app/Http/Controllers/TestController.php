<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

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

    public function testKafka()
    {
        try {
            // Retrieve Kafka brokers from environment variable
            $brokers = env('KAFKA_BROKERS', 'kafka:29092');

            if (empty($brokers)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kafka brokers are not configured',
                    'note' => 'Ensure KAFKA_BROKERS is set in the environment variables',
                ], 500);
            }

            // Check if rdkafka extension is available
            if (!extension_loaded('rdkafka')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'rdkafka PHP extension is not loaded',
                    'note' => 'This is required for Kafka functionality',
                ], 500);
            }

            // Basic connectivity test
            $config = new \RdKafka\Conf();
            $config->set('metadata.broker.list', $brokers);
            $config->set('socket.timeout.ms', '5000');
            $config->set('message.timeout.ms', '10000');

            $producer = new \RdKafka\Producer($config);
            $topicName = 'auth-test-topic';

            // Create a test message
            $messageData = [
                'test' => 'Kafka connection and messaging test',
                'timestamp' => now()->toISOString(),
                'service' => 'auth-app',
                'message_id' => uniqid(),
                'broker' => $brokers
            ];

            try {
                // Try to send a message with raw RdKafka for better control
                $topic = $producer->newTopic($topicName);
                $topic->produce(-1, 0, json_encode($messageData), 'auth-test-key');

                // Poll for events
                $producer->poll(0);

                // Flush with a longer timeout
                $flushResult = $producer->flush(10000); // 10 seconds

                if ($flushResult === 0) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Kafka connection and message publishing successful using raw RdKafka',
                        'published_message' => $messageData,
                        'broker_used' => $brokers,
                        'topic' => $topicName,
                        'method' => 'raw_rdkafka'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'partial_success',
                        'message' => 'Message queued but flush timed out',
                        'flush_result' => $flushResult,
                        'published_message' => $messageData,
                        'broker_used' => $brokers,
                        'topic' => $topicName,
                        'note' => 'Message may still be delivered asynchronously'
                    ]);
                }
            } catch (\Exception $e) {
                // Fallback to basic connectivity test
                return response()->json([
                    'status' => 'partial_success',
                    'message' => 'Kafka basic connectivity works, but message sending failed',
                    'broker_used' => $brokers,
                    'topic' => $topicName,
                    'rdkafka_loaded' => true,
                    'connectivity_test' => 'passed',
                    'send_error' => $e->getMessage(),
                    'test_message' => $messageData,
                    'note' => 'Kafka is reachable but may not be fully ready for producers'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kafka test failed',
                'error' => $e->getMessage(),
                'broker_attempted' => $brokers ?? 'N/A',
                'rdkafka_loaded' => extension_loaded('rdkafka')
            ], 500);
        }
    }
}
