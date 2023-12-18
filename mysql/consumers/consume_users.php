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
$rabbitmqMainQueue = 'usersQueue';

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    $searchData = json_decode($message->body, true);

    // Set the user_id and searchQuery variable
    if (isset($searchData['user_id'], $searchData['searchQuery'])) {
        $user_id = $searchData['user_id'];
        $searchQuery = $searchData['searchQuery'];

        // Get users based on the search query
        $users = getUsers($user_id, $searchQuery, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

        // Send the users
        if ($users) {
            $response = [
                "status" => "GOOD",
                "users" => $users
            ];

            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Users sent for query: $searchQuery\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            $response = [
                "status" => "BAD",
                "users" => $users
            ];

            $badMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "No users found for query: $searchQuery\n";
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

// Database functions

// getUsers function
function getUsers($user_id, $searchQuery, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to query the users table based on the search query
    $query = "SELECT user_id, username FROM users WHERE username LIKE ? AND user_id != ?";
    $param = '%' . $searchQuery . '%';

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("si", $param, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows are returned and return the users
    if ($result->num_rows > 0) {
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        $result->free();
        $stmt->close();
        $mysqli->close();
        return $users;
    } else {
        $users = [];
        $stmt->close();
        $mysqli->close();
        return $users;
    }
}
?>