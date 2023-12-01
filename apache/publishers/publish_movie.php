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
$rabbitmqMainQueue = 'movieQueue';
$rabbitmqReplyQueue = 'replyMovieQueue';

// User input from the form
$movieId = $_POST['movie_id'];

// Create an associative array with the data
$movieData = array(
    'movie_id' => $movieId
);

// Convert the data to a JSON string
$jsonMovieData = json_encode($movieData);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and publish the message to RabbitMQ
$message = new AMQPMessage($jsonMovieData, ['reply_to' => $rabbitmqReplyQueue]);
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
    
    if ($response && $response['status'] === 'GOOD') {
        // Redirect to the movie.php page with movie details in the URL
        header("Location: movie.php?movie=" . urlencode(json_encode($response['movie'])) . "&reviews=" . urlencode(json_encode($response['reviews'])));
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } else {
        // Maybe redirect the user to a 404 not found page in the future...
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