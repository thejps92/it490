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
$rabbitmqMainQueue = 'friendQueue';

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
    $friendData = json_decode($message->body, true);
    
    // Set the action, sender_id, and receiver_id variables if they are set otherwise set the action, user1_id, and user2_id variables if they are set
    if (isset($friendData['action'], $friendData['sender_id'], $friendData['receiver_id'])) {
        $action = $friendData['action'];
        $sender_id = $friendData['sender_id'];
        $receiver_id = $friendData['receiver_id'];
        
        // Check what the action variable is set to in order to determine the SQL queries
        if ($action === 'delete') {
            deleteFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $outgoing_friend_requests = getOutgoingFriendRequests($sender_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $response = [
                "status" => "GOOD",
                "outgoing_friend_requests" => $outgoing_friend_requests,
                "reason" => "Deleted"
            ];
            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Deleted friend request for Sender ID: $sender_id, Receiver ID: $receiver_id\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } elseif ($action === 'accept') {
            insertFriend($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            deleteFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $friends = getUserFriends($receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $incoming_friend_requests = getIncomingFriendRequests($receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $response = [
                "status" => "GOOD",
                "friends" => $friends,
                "incoming_friend_requests" => $incoming_friend_requests,
                "reason" => "Accepted"
            ];
            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Accepted friend request for Sender ID: $sender_id, Receiver ID: $receiver_id\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } elseif ($action === 'decline') {
            deleteFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $incoming_friend_requests = getIncomingFriendRequests($receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $response = [
                "status" => "GOOD",
                "incoming_friend_requests" => $incoming_friend_requests,
                "reason" => "Declined"
            ];
            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Declined friend request for Sender ID: $sender_id, Receiver ID: $receiver_id\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            $response = [
                "status" => "BAD"
            ];
            $badMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "Friend action failed" . "\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }
    } elseif (isset($friendData['action'], $friendData['user1_id'], $friendData['user2_id'])) {
        $action = $friendData['action'];
        $user1_id = $friendData['user1_id'];
        $user2_id = $friendData['user2_id'];

        if ($action === 'remove') {
            deleteFriend($user1_id, $user2_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $friends = getUserFriends($user1_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            $response = [
                "status" => "GOOD",
                "friends" => $friends,
                "reason" => "Removed"
            ];
            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "Removed friend for User1 ID: $user1_id, User2 ID: $user2_id\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            $response = [
                "status" => "BAD"
            ];
            $badMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "Friend action failed" . "\n";
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

// Delete friend request function
function deleteFriendRequest($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to delete the friend request from the friend requests table
    $query = "DELETE FROM friend_requests WHERE sender_id = ? AND receiver_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $sender_id, $receiver_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// Insert friend function
function insertFriend($sender_id, $receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to insert the new friend into the friends table
    $query = "INSERT INTO friends (user1_id, user2_id) VALUES (?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $sender_id, $receiver_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// Delete friend function
function deleteFriend($user1_id, $user2_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to delete the friend from the friends table
    $query = "DELETE FROM friends WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// Get user friends function
function getUserFriends($receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to query the friends table
    $query = "SELECT users.user_id, users.username
            FROM friends
            JOIN users ON (friends.user1_id = users.user_id OR friends.user2_id = users.user_id)
            WHERE (friends.user1_id = ? OR friends.user2_id = ?)
            AND users.user_id != ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iii", $receiver_id, $receiver_id, $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If there are friends, store them in an array
    if ($result->num_rows > 0) {
        $friends = [];

        while ($row = $result->fetch_assoc()) {
            $friends[] = $row;
        }

        $result->free();
        $stmt->close();
        $mysqli->close();
        return $friends;
    } else {
        $friends = [];
        $stmt->close();
        $mysqli->close();
        return $friends;
    }
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
function getIncomingFriendRequests($receiver_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
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
    $stmt->bind_param("i", $receiver_id);
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