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

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';
$mysqlMoviesTable = 'movies';
$mysqlBookmarksTable = 'bookmarks';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable, $mysqlBookmarksTable) {
    $bookmarkData = json_decode($message->body, true);
    
    // Set the user_id and movie_id variables
    if ($bookmarkData && isset($bookmarkData['user_id'], $bookmarkData['movie_id'])) {
        $user_id = $bookmarkData['user_id'];
        $movie_id = $bookmarkData['movie_id'];
        
        // Check if the bookmark already exists
        if (!bookmarkExists($user_id, $movie_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlBookmarksTable)) {
            // If the bookmark doesn't exist, insert the bookmark
            addBookmark($user_id, $movie_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlBookmarksTable);

            // Fetch user bookmarks to send the updated bookmarks after the insert so that the bookmarks.php page will update
            $bookmarks = getUserBookmarks($user_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable);

            $response = [
                "status" => "GOOD",
                "bookmarks" => $bookmarks
            ];

            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Bookmark added for User ID: $user_id, Movie ID: $movie_id\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            // If the bookmark already exists, publish a "BAD" message to RabbitMQ
            $response = [
                "stats" => "BAD"
            ];

            $badMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "Bookmark already exists";
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

// Function to check if a bookmark already exists
function bookmarkExists($user_id, $movie_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlBookmarksTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Check if the bookmark already exists in the bookmarks table for the specific user
    $query = "SELECT * FROM $mysqlBookmarksTable WHERE user_id = '$user_id' AND movie_id = '$movie_id'";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $mysqli->close();
        return true;
    }

    $mysqli->close();
    return false;
}

// Function to add a new bookmark
function addBookmark($user_id, $movie_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlBookmarksTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Insert the new bookmark into the bookmarks table
    $query = "INSERT INTO $mysqlBookmarksTable (user_id, movie_id) VALUES ('$user_id', '$movie_id')";
    $mysqli->query($query);
    $mysqli->close();
}

// Get user bookmarks function
function getUserBookmarks($user_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Escape the user_id to prevent SQL injection
    $escapedUserID = $mysqli->real_escape_string($user_id);

    // Query the bookmarks table, joining with the movies table to get movie details
    $query = "SELECT M.* FROM bookmarks AS B
              JOIN $mysqlMoviesTable AS M ON B.movie_id = M.movie_id
              WHERE B.user_id = '$escapedUserID'";

    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $bookmarks = array();

        // Fetch all user's bookmarks with movie details
        while ($row = $result->fetch_assoc()) {
            $bookmarks[] = $row;
        }

        $result->free();
        $mysqli->close();

        return $bookmarks;
    }

    $mysqli->close();
    return [];
}
?>