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
    if (isset($signupData['username'], $signupData['password'], $signupData['fav_genre'])) {
        $username = $signupData['username'];
        $password = $signupData['password'];
        $fav_genre = $signupData['fav_genre'];
        
        // Check if the username already exists in the database
        if (checkUser($username, $password, $fav_genre, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable)) {
            $response = [
                "status" => "BAD"
            ];
            $badMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "User $username already exists\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            $response = [
                "status" => "GOOD"
            ];
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

// Database functions

// Check username function
function checkUser($username, $password, $fav_genre, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to check if the username exists
    $query = "SELECT * FROM $mysqlTable WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If the username exists return true, else insert the user into the users table and return false
    if ($result->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return true;
    } else {
        $stmt->close();
        
        // Prepare a statement to insert the user with a hashed password
        $insertQuery = "INSERT INTO $mysqlTable (username, password, fav_genre) VALUES (?, ?, ?)";
        $insertStmt = $mysqli->prepare($insertQuery);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt->bind_param("sss", $username, $hashedPassword, $fav_genre);
        $insertStmt->execute();
        $insertStmt->close();
        $mysqli->close();
        return false;
    }
}
?>