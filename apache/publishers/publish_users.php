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
$rabbitmqMainQueue = 'usersQueue';
$rabbitmqReplyQueue = 'replyUsersQueue';

// Data from the form
$user_id = $_POST['user_id'];
$searchQuery = $_POST['searchQuery'];

// Create an associative array with the data
$searchData = array(
    'user_id' => $user_id,
    'searchQuery' => $searchQuery
);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and publish the message to RabbitMQ
$message = new AMQPMessage(json_encode($searchData), ['reply_to' => $rabbitmqReplyQueue]);
$channel->basic_publish($message, '', $rabbitmqMainQueue);

// Close the RabbitMQ connection
$channel->close();
$connection->close();

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqReplyQueue, false, true, false, false);

// Callback function
$callback = function ($message) {
    $response = json_decode($message->body, true);
    
    if ($response['status']) {
        session_start();
        $_SESSION['users'] = $response['users'];
        header('Location: user_results.php');
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } else {
        header('Location: error.php');
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