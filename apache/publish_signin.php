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
$rabbitmqReplyQueue = 'replySignInQueue';

// User input from the form
$username = $_POST['username'];
$password = $_POST['password'];

// Create an associative array with the signin data
$signinData = array(
    'username' => $username,
    'password' => $password
);

// Convert the data to a JSON string
$jsonSigninData = json_encode($signinData);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and send the message
$message = new AMQPMessage($jsonSigninData, ['reply_to' => $rabbitmqReplyQueue]);
$channel->basic_publish($message, '', $rabbitmqMainQueue);

// Close the channel and connection
$channel->close();
$connection->close();

// Redirection logic
// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqReplyQueue, false, true, false, false);

// Callback function
$callback = function ($message) {
    $response = $message->body;

    if ($response === 'OK') {
        // Redirect the user to the user's page
        header('Location: user.php?username=' . urlencode($_POST['username']));
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } else {
        // Redirect the user to an error page
        header('Location: index.php');
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    }
};

// Consume
$channel->basic_consume($rabbitmqReplyQueue, '', false, false, false, false, $callback);

// Keep the connection open
while (count($channel->callbacks)) {
	$channel->wait();
}

// Close the channel and connection
$channel->close();
$connection->close();
?>