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
$rabbitmqMainQueue = 'friendQueue';
$rabbitmqReplyQueue = 'replyFriendQueue';

// Data from the button click
$friendData = json_decode(file_get_contents('php://input'), true);

// Send the data to RabbitMQ
if (isset($friendData['action'])) {
    // Establish RabbitMQ connection
    $connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
    $channel = $connection->channel();
    $channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

    // Create and publish the message to RabbitMQ
    $message = new AMQPMessage(json_encode($friendData), ['reply_to' => $rabbitmqReplyQueue]);
    $channel->basic_publish($message, '', $rabbitmqMainQueue);

    // Close the RabbitMQ connection
    $channel->close();
    $connection->close();
} else {
    // Post a 400 HTTP response code to indicate a bad request
    http_response_code(400);
}

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqReplyQueue, false, true, false, false);

// Callback function
$callback = function ($message) {
    $response = json_decode($message->body, true);
    
    if ($response['status'] === 'GOOD' && $response['reason'] === 'Deleted') {
        session_start();
        $_SESSION['outgoing_friend_requests'] = $response['outgoing_friend_requests'];
        http_response_code(200);
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'GOOD' && $response['reason'] === 'Accepted') {
        session_start();
        $_SESSION['friends'] = $response['friends'];
        $_SESSION['incoming_friend_requests'] = $response['incoming_friend_requests'];
        http_response_code(200);
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'GOOD' && $response['reason'] === 'Declined') {
        session_start();
        $_SESSION['incoming_friend_requests'] = $response['incoming_friend_requests'];
        http_response_code(200);
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'GOOD' && $response['reason'] === 'Removed') {
        session_start();
        $_SESSION['friends'] = $response['friends'];
        http_response_code(200);
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