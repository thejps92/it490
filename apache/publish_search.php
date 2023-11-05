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
$rabbitmqMainQueue = 'searchQueue';
$rabbitmqReplyQueue = 'replySearchQueue';

// User input from the form
$searchQuery = $_POST['searchQuery'];
$searchType = $_POST['searchType'];

// Create an associative array with the data
$searchData = array(
    'searchQuery' => $searchQuery,
    'searchType' => $searchType
);

// Convert the data to a JSON string
$jsonSearchData = json_encode($searchData);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and publish the message to RabbitMQ
$message = new AMQPMessage($jsonSearchData, ['reply_to' => $rabbitmqReplyQueue]);
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
    
    // If the response from MySQL is 'GOOD' then redirect the user to the search results page with the results in the URL
    if ($response && $response['status'] === 'GOOD') {
        header('Location: search_results.php?results=' . urlencode(json_encode($response['movieDetails'])));
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } else {
        // If the response from MySQL is not 'GOOD' then redirect the user to to search results page with error in the URL
        header('Location: search_results.php?results=error');
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