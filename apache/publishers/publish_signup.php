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
$rabbitmqReplyQueue = 'replySignUpQueue';

// User input from the form
$username = $_POST['username'];
$password = $_POST['password'];
$fav_genre = $_POST['fav_genre'];

// Create an associative array with the data
$signupData = array(
    'username' => $username,
    'password' => $password,
    'fav_genre' => $fav_genre
);

// Convert the data to a JSON string
$jsonSignupData = json_encode($signupData);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and publish the message to RabbitMQ
$message = new AMQPMessage($jsonSignupData, ['reply_to' => $rabbitmqReplyQueue]);
$channel->basic_publish($message, '', $rabbitmqMainQueue);

// Close the RabbitMQ connection
$channel->close();
$connection->close();

// Redirection logic
// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqReplyQueue, false, true, false, false);

// Callback function
$callback = function ($message) use ($username) {
    $response = $message->body;
    
    if ($response === 'GOOD') {
        // Redirect the user to the sign in page
        header('Location: signin.php');
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } else {
        // Alert the user of an error and either redirect them to the sign up page or the home page based on their selection
        echo "<script>
        var confirmation = confirm('Username already exists. Please try again.');
        if (confirmation) {
            window.location.href = 'signup.php';
        } else {
            window.location.href = 'index.php';
        }
        </script>";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    }
};

// Consume the message from the RabbitMQ reply queue
$channel->basic_consume($rabbitmqReplyQueue, '', false, false, false, false, $callback);

// Keep the RabbitMQ connection open
while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the RabbitMQ connection
$channel->close();
$connection->close();
?>