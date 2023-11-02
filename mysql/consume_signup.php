<<<<<<< Updated upstream
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
$rabbitmqMainQueue = 'signUpQueue';

// MySQL connection parameters
$mysqlHost = '127.0.0.1';
$mysqlUser = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'Project';
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
=======
<?php
// Include the RabbitMQ library
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ connection parameters
$rabbitmqHost = '10.147.18.28';
$rabbitmqPort = 5672;
$rabbitmqUsername = 'rmqsUser';
$rabbitmqPassword = 'Password123';
$rabbitmqVHost = 'rmqsVHost';
$rabbitmqMainQueue = 'signUpQueue';

// MySQL connection parameters
$mysqlHost = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'testDB';
$mysqlTable = 'user_credentials';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);

// Establish MySQL connection
$mysqli = new mysqli($mysqlHost, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
if ($mysqli->connect_error) {
    die("Connection to MySQL failed: " . $mysqli->connect_error);
}
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqli, $mysqlTable) {
    $signupData = json_decode($message->body, true);
    
    // Set the username and password variables
    if (is_array($signupData) && isset($signupData['username'], $signupData['password'])) {
        $username = $signupData['username'];
        $password = $signupData['password'];
        
        // Check if the username already exists in the database
        if (checkUsername($username, $mysqli, $mysqlTable)) {
            // If the username already exists, publish a "BAD" message to RabbitMQ
            $badMessage = new AMQPMessage("BAD");
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "User $username already exists\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            // If the username doesn't exist, create the new user in the database
            $sql = "INSERT INTO $mysqlTable (username, password) VALUES ('$username', '$password')";
            
            if ($mysqli->query($sql)) {
                // Publish a "GOOD" message to RabbitMQ
                $goodMessage = new AMQPMessage("GOOD");
                $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                echo "User $username successfully created\n";
            } else {
                echo "Inserting data into MySQL failed: " . $mysqli->error . "\n";
            }
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
$mysqli->close();

// Check username function
function checkUsername($username, $mysqli, $mysqlTable) {
    // Escape the username to prevent SQL injection
    $escapedUsername = $mysqli->real_escape_string($username);
    
    // Query the user table for the provided username
    $query = "SELECT * FROM $mysqlTable WHERE username = '$escapedUsername'";
    $result = $mysqli->query($query);
    
    // If the username exists return ture
    if ($result && $result->num_rows > 0) {
        $result->free();
        return true;
    }
    
    // If the username doesn't exist return false
    $result->free();
    return false;
}
?>
>>>>>>> Stashed changes
