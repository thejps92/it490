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
$rabbitmqMainQueue = 'signUpQueue';

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';
$mysqlTable = 'users';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    $signupData = json_decode($message->body, true);
    
    // Set the username and password variables
    if (is_array($signupData) && isset($signupData['username'], $signupData['password'], $signupData['fav_genre'])) {
        $username = $signupData['username'];
        $password = $signupData['password'];
        $fav_genre = $signupData['fav_genre'];
        
        // Check if the username already exists in the database
        if (checkUsername($username, $password, $fav_genre, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable)) {
            // If the username already exists, publish a "BAD" message to RabbitMQ
            $response = ["status" => "BAD"];
            $badMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "User $username already exists\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            // If the username doesn't exist, publish a "GOOD" message to RabbitMQ
            $response = ["status" => "GOOD"];
            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo "User $username successfully created\n";
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
$mysqli->close();

// Check username function
function checkUsername($username, $password, $fav_genre, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    // Check for a successful connection
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Escape the username and password to prevent SQL injection
    $escapedUsername = $mysqli->real_escape_string($username);
    $escapedPassword = $mysqli->real_escape_string($password);

    // Hash the password
    $hashedPassword = password_hash($escapedPassword, PASSWORD_DEFAULT);
    
    // Query the user table for the provided username
    $query = "SELECT * FROM $mysqlTable WHERE username = '$escapedUsername'";
    $result = $mysqli->query($query);
    
    // If the username exists return true
    if ($result && $result->num_rows > 0) {
        $result->free();
        return true;
    }
    
    // If the username doesn't exist insert the user into the users table and return false
    $result->free();
    $query = "INSERT INTO $mysqlTable (username, password, fav_genre) VALUES ('$escapedUsername', '$hashedPassword', '$fav_genre')";
    $mysqli->query($query);
    $mysqli->close();
    return false;
}
?>