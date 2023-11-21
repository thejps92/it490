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
$rabbitmqMainQueue = 'ratingQueue';

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';
$mysqlRatingsTable = 'ratings';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for rating messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlRatingsTable) {
    $ratingData = json_decode($message->body, true);

    // Set the rating data variables
    if (is_array($ratingData) && isset($ratingData['movieId'], $ratingData['userId'], $ratingData['rating'])) {
        $movieId = $ratingData['movieId'];
        $userId = $ratingData['userId'];
        $rating = $ratingData['rating'];

        // Insert the rating into the ratings table
        $insertResult = insertMovieRating($movieId, $userId, $rating, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlRatingsTable);

        if ($insertResult) {
            echo "Rating inserted for movie ID: $movieId by user ID: $userId\n";
        } else {
            echo "Failed to insert rating for movie ID: $movieId by user ID: $userId\n";
        }
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

// insertMovieRating function
function insertMovieRating($movieId, $userId, $rating, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlRatingsTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Escape user inputs to prevent SQL injection
    $escapedMovieId = $mysqli->real_escape_string($movieId);
    $escapedUserId = $mysqli->real_escape_string($userId);
    $escapedRating = $mysqli->real_escape_string($rating);

    // Insert the rating into the ratings table
    $query = "INSERT INTO $mysqlRatingsTable (movie_id, user_id, rating) VALUES ('$escapedMovieId', '$escapedUserId', '$escapedRating')";

    $result = $mysqli->query($query);

    // Close the MySQL connection
    $mysqli->close();

    return $result;
}
?>

