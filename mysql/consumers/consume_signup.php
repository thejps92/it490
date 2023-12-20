<?php
// Include the RabbitMQ library
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// RabbitMQ connection parameters
$rabbitmqIP = '10.147.18.28';
$rabbitmqPort = 5672;
$rabbitmqUsername = 'rmqsUser';
$rabbitmqPassword = 'Password123';
$rabbitmqVHost = 'rmqsVHost';
$rabbitmqMainQueue = 'signUpQueue';

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
    $signupData = json_decode($message->body, true);
    
    // Set the username, password, email, and fav_genre variables
    if (isset($signupData['username'], $signupData['password'], $signupData['email'], $signupData['fav_genre'])) {
        $username = $signupData['username'];
        $password = $signupData['password'];
        $email = $signupData['email'];
        $fav_genre = $signupData['fav_genre'];

        // Check all variations of the username and email
        $checkUsername = checkUsername($username, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
        $checkEmail = checkEmail($email, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
        $validateEmail = validateEmail($email);

        if ($checkUsername === false && $checkEmail === false && $validateEmail === true) {
            insertUser($username, $password, $email, $fav_genre, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
            sendEmail($username, $email);
            $response = [
                "status" => "GOOD"
            ];
            $replyMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($replyMessage, '', $message->get('reply_to'));
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo "User $username created\n";
        } elseif ($checkUsername === true && $checkEmail === false && $validateEmail === true) {
            $response = [
                "status" => "Duplicate username"
            ];
            $replyMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($replyMessage, '', $message->get('reply_to'));
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo "User $username already exists\n";
        } elseif ($checkUsername === false && $checkEmail === true && $validateEmail === true) {
            $response = [
                "status" => "Duplicate email"
            ];
            $replyMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($replyMessage, '', $message->get('reply_to'));
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo "Email $email already exists\n";
        } elseif ($checkUsername === false && $checkEmail === false && $validateEmail === false) {
            $response = [
                "status" => "Invalid email"
            ];
            $replyMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($replyMessage, '', $message->get('reply_to'));
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo "Email $email is invalid\n";
        } elseif ($checkUsername === true && $checkEmail === true && $validateEmail === true) {
            $response = [
                "status" => "Duplicate username and email"
            ];
            $replyMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($replyMessage, '', $message->get('reply_to'));
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo "User $username and email $email already exist\n";
        } elseif ($checkUsername === true && $checkEmail === false && $validateEmail === false) {
            $response = [
                "status" => "Duplicate username and invalid email"
            ];
            $replyMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($replyMessage, '', $message->get('reply_to'));
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo "User $username already exists and email $email is invalid\n";
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

// Database functions

// Check username function
function checkUsername($username, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to check if the username exists
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If the username exists return true, else return false
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

// Check email function
function checkEmail($email, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to check if the email exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If the email exists return true, else return false
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

// Validate email function
function validateEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

// Insert user function
function insertUser($username, $password, $email, $fav_genre, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);

    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to insert the user with a hashed password
    $query = "INSERT INTO users (username, password, email, fav_genre) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("ssss", $username, $hashedPassword, $email, $fav_genre);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// Send email function
function sendEmail($username, $email) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'REPLACE_WITH_EMAIL';
        $mail->Password = 'REPLACE_WITH_APP_PASSWORD';
        $mail->SMTPSecure = 'tls';
        $mail->Port = '587';

        $mail->addAddress($email);
        $mail->Subject = "Welcome $username to www.example.com!";
        $mail->Body = "$username,\n\nThis email is to confirm the creation of your account on www.example.com.";
        $mail->send();

        echo "Email sent.";
    } catch (Exception $e) {
        echo "Email failed. Mailer error: {$mail->ErrorInfo}";
    }
}
?>