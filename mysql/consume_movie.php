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

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';
$mysqlMoviesTable = 'movies';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable) {
    $movieData = json_decode($message->body, true);
    
    // Set the movie_id variable
    if ($movieData && isset($movieData['movie_id'])) {
        $movieId = $movieData['movie_id'];
        
        // Get the movie for the movie_id
        $movie = getMovie($movieId, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable);
        
        $response = [
            "status" => "GOOD",
            "movie" => $movie
        ];
        
        $goodMessage = new AMQPMessage(json_encode($response));
        $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
        echo "Movie sent for Movie ID: $movieId\n";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    } else {
        
        $response = [
            "status" => "BAD"
        ];
        
        $badMessage = new AMQPMessage(json_encode($response));
        $channel->basic_publish($badMessage, '', $message->get('reply_to'));
        echo "Bad";
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

// Get movie function
function getMovie($movieId, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Query the movies table for the movie
    $query = "SELECT * FROM $mysqlMoviesTable WHERE movie_id = $movieId";

    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $movie = array();

        while ($row = $result->fetch_assoc()) {
            $movie[] = $row;
        }

        $result->free();
        $mysqli->close();
        
        return $movie;
    }

    $mysqli->close();
    return [];
}
?>