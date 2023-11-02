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
$rabbitmqMainQueue = 'signInQueue';

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'random490';
$mysqlDatabase = 'newdb';
$mysqlTable = 'Users';
$mysqlMoviesTable = 'movies';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable, $mysqlMoviesTable) {
    $signinData = json_decode($message->body, true);
    
    // Set the username and password variables
    if (is_array($signinData) && isset($signinData['username'], $signinData['password'])) {
        $username = $signinData['username'];
        $password = $signinData['password'];
        
        // Validate the username and password from the database
        $userInfo = validateUser($username, $password, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable);
        if ($userInfo !== false) {
            // If the username and password are valid in the database, publish a "GOOD" message to RabbitMQ
            $movies = getTop10Movies($username, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable);

            if ($movies !== false) {
                $response = [
                    "status" => "GOOD",
                    "user_info" => $userInfo,
                    "movies" => $movies,
                ];
            } else {
                $response = [
                    "status" => "GOOD",
                    "user_info" => $userInfo,
                    "movies" => [], // No movies found
                ];
            }

            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "User $username successfully authenticated\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            // If the username and password are not valid in the database, publish a "BAD" message to RabbitMQ
            $badMessage = new AMQPMessage("BAD");
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "Incorrect username or password for user $username\n";
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

// Validate user function
function validateUser($username, $password, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }
    
    // Escape the username and password to prevent SQL injection
    $escapedUsername = $mysqli->real_escape_string($username);
    $escapedPassword = $mysqli->real_escape_string($password);
    
    // Query the user table for the provided username and password
    $query = "SELECT * FROM $mysqlTable WHERE username = '$escapedUsername' AND password = '$escapedPassword'";
    $result = $mysqli->query($query);
    
    // If the username and password match return true
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $fav_genre = $row['fav_genre'];
        $result->free();
        $mysqli->close();
        return ['id' => $id, 'fav_genre' => $fav_genre];
    }
    
    // If the username and password don't match return false
    $mysqli->close();
    return false;
}

function getTop10Movies($username, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable) {
    // Establish MySQL connection and retrieve the user's favorite genre
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Retrieve the user's favorite genre from the user table
    $query = "SELECT fav_genre FROM Users WHERE username = '$username'";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $favGenre = $row['fav_genre'];
        $result->free();

        // Use the retrieved favGenre to query for top 10 movies
        $query = "SELECT title FROM $mysqlMoviesTable WHERE genre LIKE '%$favGenre%' LIMIT 10";
        $result = $mysqli->query($query);

        if ($result && $result->num_rows > 0) {
            $movies = [];
            while ($row = $result->fetch_assoc()) {
                $movies[] = $row;
            }
            $result->free();
            $mysqli->close();
            return $movies;
        }
    }

    $mysqli->close();
    return [];
}
?>
