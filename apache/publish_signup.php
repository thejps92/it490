<?php
// Include the RabbitMQ library
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Process form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // RabbitMQ connection parameters
    $rabbitmqHost = '10.147.18.28';
    $rabbitmqPort = '5672';
    $rabbitmqUser = 'rmqsUser';
    $rabbitmqPass = 'Password123';
    $rabbitmqVHost = 'rmqsVHost';
    $rabbitmqMainQueue = 'signUpQueue';

    // Establish RabbitMQ connection
    $connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass, $rabbitmqVHost);
    $channel = $connection->channel();
    $channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

    // Create a message with the signup data and send the message
    $message = new AMQPMessage(json_encode(['username' => $username, 'password' => $password]));
    $channel->basic_publish($message, '', $rabbitmqMainQueue);

    // Close the channel and connection
    $channel->close();
    $connection->close();

    // Redirect the user to a success page or display a success message
    echo "Success.";
} else {
    // Handle invalid requests
    echo "Invalid request.";
}
?>