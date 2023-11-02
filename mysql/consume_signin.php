<?php
// Include the RabbitMQ library
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ connection parameters
$rabbitmqHost = '10.147.18.28';
$rabbitmqPort = 5672;
$rabbitmqUsername = 'rmqsUser';
$rabbitmqPassword = 'Password123';
$rabbitmqVHost = 'rmqsVHost';
$rabbitmqMainQueue = 'signInQueue';

// MySQL connection parameters
$mysqlHost = '127.0.0.1';
$mysqlUser = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';
$mysqlTable = 'Users';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    $signinData = json_decode($message->body, true);
    
    // Set the username and password variables
    if (is_array($signinData) && isset($signinData['username'], $signinData['password'])) {
        $username = $signinData['username'];
        $password = $signinData['password'];
        
        // Validate the username and password from the database
        if (validateUser($username, $password, $mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase, $mysqlTable)) {
            // If the username and password are valid in the database, publish a "GOOD" message to RabbitMQ
            $goodMessage = new AMQPMessage("GOOD");
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
function validateUser($username, $password, $mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    
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
        $result->free();
        $mysqli->close();
        return true;
    }
    
    // If the username and password don't match return false
    $mysqli->close();
    return false;
}
?>
