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
$rabbitmqMainQueue = 'friendRequestQueue';

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
    $friendRequestData = json_decode($message->body, true);
    
    // Set the sender_id and receiver_id variables
    if (isset($friendRequestData['sender_id'], $friendRequestData['receiver_id'])) {
        $sender_id = $friendRequestData['sender_id'];
        $receiver_id = $friendRequestData['receiver_id'];
        
        // Check if the friend request already exists, if it doesn't then insert the friend request
        if (!checkFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) && !checkFriend($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase)) {
            insertFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $outgoing_friend_requests = getOutgoingFriendRequests($sender_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $incoming_friend_requests = getIncomingFriendRequests($sender_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $response = [
                "status" => "GOOD",
                "outgoing_friend_requests" => $outgoing_friend_requests,
                "incoming_friend_requests" => $incoming_friend_requests
            ];
            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Friend request added for Sender ID: $sender_id, Receiver ID: $receiver_id\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            if (checkFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase)) {
                $response = [
                    "status" => "BAD",
                    "reason" => "Friend Request"
                ];
            } elseif (checkFriend($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase)) {
                $response = [
                    "status" => "BAD",
                    "reason" => "Friend"
                ];
            }
            $badMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "Friend request failed" . "\n";
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

// Check friend request function
function checkFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to check if the friend request already exists in the friend requests table
    $query = "SELECT * FROM friend_requests WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows are returned and return true if there are and false if there aren't
    if ($result->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return true;
    } else {
        $stmt->close();
        $mysqli->close();
        return false;
    }
}

// Check friend function
function checkFriend($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to check if the friend already exists in the friends table
    $query = "SELECT * FROM friends WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows are returned and return true if there are and false if there aren't
    if ($result->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return true;
    } else {
        $stmt->close();
        $mysqli->close();
        return false;
    }
}

// Insert friend request function
function insertFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to insert the new friend request into the friend requests table
    $query = "INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $sender_id, $receiver_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// Get outgoing friend requests function
function getOutgoingFriendRequests($sender_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to query the friend requests table, joining with the users table to get usernames
    $query = "SELECT friend_requests.receiver_id, users.username
            FROM friend_requests
            INNER JOIN users ON friend_requests.receiver_id = users.user_id
            WHERE friend_requests.sender_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows are returned and return the outgoing friend requests
    if ($result->num_rows > 0) {
        $outgoing_friend_requests = [];

        while ($row = $result->fetch_assoc()) {
            $outgoing_friend_requests[] = $row;
        }

        $result->free();
        $stmt->close();
        $mysqli->close();
        return $outgoing_friend_requests;
    } else {
        $outgoing_friend_requests = [];
        $stmt->close();
        $mysqli->close();
        return $outgoing_friend_requests;
    }
}

// Get incoming friend requests function
function getIncomingFriendRequests($sender_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to query the friend requests table, joining with the users table to get usernames
    $query = "SELECT friend_requests.sender_id, users.username
            FROM friend_requests
            INNER JOIN users ON friend_requests.sender_id = users.user_id
            WHERE friend_requests.receiver_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows are returned and return the incoming friend requests
    if ($result->num_rows > 0) {
        $incoming_friend_requests = [];

        while ($row = $result->fetch_assoc()) {
            $incoming_friend_requests[] = $row;
        }

        $result->free();
        $stmt->close();
        $mysqli->close();
        return $incoming_friend_requests;
    } else {
        $incoming_friend_requests = [];
        $stmt->close();
        $mysqli->close();
        return $incoming_friend_requests;
    }
}
?>