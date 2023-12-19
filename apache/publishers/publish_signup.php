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

// Data from the form
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$fav_genre = $_POST['fav_genre'];

// Create an associative array with the data
$signupData = array(
    'username' => $username,
    'password' => $password,
    'email' => $email,
    'fav_genre' => $fav_genre
);

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Create and publish the message to RabbitMQ
$message = new AMQPMessage(json_encode($signupData), ['reply_to' => $rabbitmqReplyQueue]);
$channel->basic_publish($message, '', $rabbitmqMainQueue);

// Close the RabbitMQ connection
$channel->close();
$connection->close();

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqReplyQueue, false, true, false, false);

// Callback function
$callback = function ($message) use ($username) {
    $response = json_decode($message->body, true);
    
    if ($response['status'] === 'GOOD') {
        header('Location: signin.php');
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'Duplicate username') {
        echo "<script>
              var confirmation = confirm('Username already exists. Please try again.');
              if (confirmation) {
                window.location.href = 'signup.php';
              } else {
                window.location.href = 'signup.php';
              }
              </script>";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'Duplicate email') {
        echo "<script>
              var confirmation = confirm('Email already exists. Please try again.');
              if (confirmation) {
                window.location.href = 'signup.php';
              } else {
                window.location.href = 'signup.php';
              }
              </script>";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'Invalid email') {
        echo "<script>
              var confirmation = confirm('Email is invalid. Please try again.');
              if (confirmation) {
                window.location.href = 'signup.php';
              } else {
                window.location.href = 'signup.php';
              }
              </script>";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'Duplicate username and email') {
        echo "<script>
              var confirmation = confirm('Username and email already exist. Please try again.');
              if (confirmation) {
                window.location.href = 'signup.php';
              } else {
                window.location.href = 'signup.php';
              }
              </script>";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        exit();
    } elseif ($response['status'] === 'Duplicate username and invalid email') {
        echo "<script>
              var confirmation = confirm('Username already exists and email is invalid. Please try again.');
              if (confirmation) {
                window.location.href = 'signup.php';
              } else {
                window.location.href = 'signup.php';
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