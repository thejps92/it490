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
$rabbitmqMainQueue = 'reviewQueue';

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';
$mysqlReviewsTable = 'reviews';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlReviewsTable) {
    $reviewData = json_decode($message->body, true);

    // If there is review data, then insert the review data
    if ($reviewData) {
        insertReview($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlReviewsTable, $reviewData);
        $response = [
            "status" => "GOOD"
        ];
        $goodMessage = new AMQPMessage(json_encode($response));
        $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
        echo "Inserted review into database for user ID: " . $reviewData['user_id'] . " and movie ID: " . $reviewData['movie_id'] . "\n";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    }
    else {
        $response = [
            "status" => "BAD"
        ];
        $badMessage = new AMQPMessage(json_encode($response));
        $channel->basic_publish($badMessage, '', $message->get('reply_to'));
        echo "Bad request for inserting review";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    }
};

// Consume the message from the RabbitMQ main queue
$channel->basic_consume($rabbitmqMainQueue, '', false, false, false, false, $callback);

// Keep the RabbitMQ connection open
while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the RabbitMQ connection
$channel->close();
$connection->close();

// Database functions

// Insert review function
function insertReview($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlReviewsTable, $reviewData) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to insert the review into the reviews table
    $stmt = $mysqli->prepare("INSERT INTO $mysqlReviewsTable (user_id, movie_id, review, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $reviewData['user_id'], $reviewData['movie_id'], $reviewData['review'], $reviewData['rating']);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}
?>