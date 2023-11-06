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
$rabbitmqReplyQueue = 'replySignInQueue';

// User input from the form
$username = $_POST['username'];
$password = $_POST['password'];

// Create an associative array with the data
$signinData = array(
    'username' => $username,
    'password' => $password
);

// Convert the data to a JSON string
$jsonSigninData = json_encode($signinData);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and publish the message to RabbitMQ
$message = new AMQPMessage($jsonSigninData, ['reply_to' => $rabbitmqReplyQueue]);
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
$callback = function ($message) {
    $response = json_decode($message->body, true);
    
    if ($response && $response['status'] === 'GOOD') {
        // Start a session
        session_start();
        $_SESSION['user_id'] = $response['user_info']['user_id'];
        $_SESSION['username'] = $response['user_info']['username'];
        $_SESSION['fav_genre'] = $response['user_info']['fav_genre'];
        $_SESSION['movies'] = $response['movies'];
        $_SESSION['bookmarks'] = $response['bookmarks'];
        $newSessionToken = session_id();
        
        // Redirect the user to the user page
        header('Location: homepage.php');
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } else {
        // Alert the user of an error and either redirect them to the sign in page or the home page based on their selection
        echo "<script>
        var confirmation = confirm('Incorrect username or password. Please try again.');
        if (confirmation) {
            window.location.href = 'signin.php';
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