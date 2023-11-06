<?php
// Include the RabbitMQ library
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ connection parameters
$rabbitmqIP = '10.147.18.28';
$rabbitmqPort = 5672;
$rabbitmqUsername = 'rmqsUser';
$rabbitmqPassword = 'Password123';
$rabbitmqVHost = 'rmqsVHost';
$rabbitmqMainQueue = 'bookmarkQueue';
$rabbitmqReplyQueue = 'replyBookmarkQueue';

// User input from the button click
$bookmarkData = json_decode(file_get_contents('php://input'), true);

if ($bookmarkData && isset($bookmarkData['user_id'], $bookmarkData['movie_id'])) {
    // Convert the data back to a JSON string
    $jsonBookmarkData = json_encode($bookmarkData);

    // Establish RabbitMQ connection
    $connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
    $channel = $connection->channel();
    $channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

    // Create and publish the message to RabbitMQ
    $message = new AMQPMessage($jsonBookmarkData, ['reply_to' => $rabbitmqReplyQueue]);
    $channel->basic_publish($message, '', $rabbitmqMainQueue);

    // Close the RabbitMQ connection
    $channel->close();
    $connection->close();
} else {
    http_response_code(400);
    echo "Bad Request";
}

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqReplyQueue, false, true, false, false);

// Callback function
$callback = function ($message) {
    $response = json_decode($message->body, true);
    
    if ($response && $response['status'] === 'GOOD') {
        // Start a session
        session_start();
        $_SESSION['bookmarks'] = $response['bookmarks'];
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } else {
        // Figure out a way to do some sort of JS alert here that will appear on the search_results.php page
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    }
};

// Consume the message from the RabbitMQ reply queue
$channel->basic_consume($rabbitmqReplyQueue, '', false, false, false, false, $callback);

// Keep the RabbitMQ connection open
while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the RabbitMQ connection
$channel->close();
$connection->close();
?>