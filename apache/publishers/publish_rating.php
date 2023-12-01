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
$rabbitmqMainQueue = 'ratingQueue'; // Modify the queue name
$rabbitmqReplyQueue = 'replyRatingQueue'; // Modify the reply queue name

// User input from the form
$movieId = $_POST['movieId']; // Assuming the movie ID is posted
$rating = $_POST['rating']; // Assuming the user's rating is posted

// Create an associative array with the rating data
$ratingData = array(
    'movieId' => $movieId,
    'rating' => $rating
);

// Convert the data to a JSON string
$jsonRatingData = json_encode($ratingData);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and publish the message to RabbitMQ
$message = new AMQPMessage($jsonRatingData, ['reply_to' => $rabbitmqReplyQueue]);
$channel->basic_publish($message, '', $rabbitmqMainQueue);

// Close the RabbitMQ connection
$channel->close();
$connection->close();

// Establish RabbitMQ connection for handling response (similar to your search script)
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqReplyQueue, false, true, false, false);

// Callback function for handling the response
$callback = function ($message) {
    $response = json_decode($message->body, true);
    
    if ($response && $response['status'] === 'GOOD') {
        // Handle success (e.g., display a success message to the user)
        header('Location: movie_details.php?movieId=' . $response['movieId']); // Redirect to movie details page
    } else {
        // Handle errors (e.g., display an error message to the user)
        header('Location: error.php'); // Redirect to an error page
    }
    
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    exit();
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

