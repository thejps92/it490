<?php
// Include the RabbitMQ library
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ connection parameters
$rabbitmqHost = '10.147.18.28';
$rabbitmqPort = 5672;
$rabbitmqUser = 'rmqsUser';
$rabbitmqPassword = 'Password123';
$rabbitmqVHost = 'rmqsVHost';
$rabbitmqMainQueue = 'signInQueue';

// MySQL connection parameters
$mysqlHost = '127.0.0.1';
$mysqlUser = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'testDB';
$mysqlTable = 'user_credentials';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for sign in messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    $signinData = json_decode($message->body, true);

    if (is_array($signinData) && isset($signinData['username'], $signinData['password'])) {
        $username = $signinData['username'];
        $password = $signinData['password'];

        // Perform user validation against the MySQL user table
        if (validateUser($username, $password, $mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase, $mysqlTable)) {
            // Publish a success message to RabbitMQ
            $successMessage = new AMQPMessage("OK");
            $channel->basic_publish($successMessage, '', $message->get('reply_to'));
            echo "User $username successfully authenticated.\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            // Publish an error message to RabbitMQ
            $errorMessage = new AMQPMessage("Invalid username or password");
            $channel->basic_publish($errorMessage, '', $message->get('reply_to'));
            echo "Invalid user or password for $username.\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }
    }
};

// Consume
$channel->basic_consume($rabbitmqMainQueue, '', false, false, false, false, $callback);

// Keep the connection open
while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the channel and connection
$channel->close();
$connection->close();

// Validate user function
function validateUser($username, $password, $mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    // Create a MySQL connection
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

    if ($result && $result->num_rows === 1) {
        // User authentication is successful
        $result->free();
        $mysqli->close();
        return true;
    }

    // User authentication failed
    $mysqli->close();
    return false;
}
?>