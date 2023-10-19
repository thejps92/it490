<?php
// Include the RabbitMQ library
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ connection parameters
$rabbitmqHost = '10.0.2.11';
$rabbitmqPort = 5672;
$rabbitmqUser = 'testUser';
$rabbitmqPassword = '123';
$rabbitmqVHost = 'testHost';
$rabbitmqMainQueue = 'testQueue';

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

// Establish MySQL connection
$mysqli = new mysqli($mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase);
if ($mysqli->connect_error) {
    die("Connection to MySQL failed: " . $mysqli->connect_error);
}
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($mysqli, $mysqlTable) {
    $data = json_decode($message->body, true);

    if (is_array($data) && isset($data['username'], $data['password'])) {
        $username = $mysqli->real_escape_string($data['username']);
        $password = $mysqli->real_escape_string($data['password']);

        $sql = "INSERT INTO $mysqlTable (username, password) VALUES ('$username', '$password')";

        if ($mysqli->query($sql)) {
            echo "Data inserted into MySQL: Username: $username\n";
        } else {
            echo "Error inserting data into MySQL: " . $mysqli->error . "\n";
        }
    } else {
        echo "Invalid message format: " . $message->body . "\n";
    }
};

// Consume
$channel->basic_consume($rabbitmqMainQueue, '', false, true, false, false, $callback);

// Keep the connection open
while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the channel and connections
$channel->close();
$connection->close();
$mysqli->close();
?>