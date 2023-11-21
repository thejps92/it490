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
    $searchData = json_decode($message->body, true);

    // Set the searchQuery and searchType variable
    if (is_array($searchData) && isset($searchData['searchQuery'], $searchData['searchType'])) {
        $searchQuery = $searchData['searchQuery'];
        $searchType = $searchData['searchType'];

        // Get movie details based on the search query and search type
        $movieDetails = getMovieDetails($searchQuery, $searchType, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable);

        // If there are movie details, publish a 'GOOD' message to RabbitMQ
        if ($movieDetails !== false) {
            $response = [
                "status" => "GOOD",
                "movieDetails" => $movieDetails
            ];

            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Movie details sent for query: $searchQuery\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            // If there are no movie details, publish a 'BAD' message to RabbitMQ
            $badMessage = new AMQPMessage("BAD");
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "No movie details found for query: $searchQuery\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
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

// getMovieDetails function
function getMovieDetails($searchQuery, $searchType, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Escape the search query to prevent SQL injection
    $escapedSearchQuery = $mysqli->real_escape_string($searchQuery);

    // Query the movies table based on the search query and search type
    if ($searchType === 'title') {
        $query = "SELECT movie_id, title FROM $mysqlMoviesTable WHERE title LIKE '%$escapedSearchQuery%'";
    } elseif ($searchType === 'year') {
        $query = "SELECT movie_id, title FROM $mysqlMoviesTable WHERE year = '$escapedSearchQuery'";
    } elseif ($searchType === 'genre') {
        $query = "SELECT movie_id, title FROM $mysqlMoviesTable WHERE genre = '$escapedSearchQuery'";
    }
    
    $result = $mysqli->query($query);

    // Check if any rows are returned
    if ($result && $result->num_rows > 0) {
        $movieDetails = array();

        // Fetch all matching movie details
        while ($row = $result->fetch_assoc()) {
            $movieDetails[] = $row;
        }

        $result->free();
        $mysqli->close();

        return $movieDetails;
    }

    // If no results found, return false
    $mysqli->close();
    return false;
}
?>