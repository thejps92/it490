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

// MySQL connection parameters
$mysqlIP = '127.0.0.1';
$mysqlUsername = 'root';
$mysqlPassword = 'root';
$mysqlDatabase = 'newdb';
$mysqlTable = 'users';
$mysqlMoviesTable = 'movies';

// Establish RabbitMQ connection
$connection = new AMQPStreamConnection($rabbitmqIP, $rabbitmqPort, $rabbitmqUsername, $rabbitmqPassword, $rabbitmqVHost);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqMainQueue, false, true, false, false);
echo "Waiting for messages. To exit, press Ctrl+C\n";

// Callback function
$callback = function ($message) use ($channel, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable, $mysqlMoviesTable) {
    $signinData = json_decode($message->body, true);
    
    // Set the username and password variables
    if (isset($signinData['username'], $signinData['password'])) {
        $username = $signinData['username'];
        $password = $signinData['password'];
        
        // Validate the username and password from the database
        $userInfo = validateUser($username, $password, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable);

        // Set the users recommended movies and bookmarks
        if ($userInfo !== false) {
            $movies = getRecMovies($username, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable, $mysqlMoviesTable);
            $bookmarks = getUserBookmarks($userInfo['user_id'], $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable);

            if ($movies !== false) {
                $response = [
                    "status" => "GOOD",
                    "user_info" => $userInfo,
                    "movies" => $movies,
                    "bookmarks" => $bookmarks
                ];
            } else {
                $response = [
                    "status" => "GOOD",
                    "user_info" => $userInfo,
                    "movies" => [],
                    "bookmarks" => $bookmarks
                ];
            }

            $goodMessage = new AMQPMessage(json_encode($response));
            $channel->basic_publish($goodMessage, '', $message->get('reply_to'));
            echo "User $username successfully authenticated\n";
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            $badMessage = new AMQPMessage("BAD");
            $channel->basic_publish($badMessage, '', $message->get('reply_to'));
            echo "Incorrect username or password for user $username\n";
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

// Validate user function
function validateUser($username, $password, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }
    
    // Prepare a statement to retrieve user data based on username
    $query = "SELECT * FROM $mysqlTable WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Check if username exists and if the password matches
    if (password_verify($password, $row['password'])) {
        $user_id = $row['user_id'];
        $username = $row['username'];
        $fav_genre = $row['fav_genre'];
        $stmt->close();
        $mysqli->close();
        return ['user_id' => $user_id, 'username' => $username, 'fav_genre' => $fav_genre];
    } else {
        $stmt->close();
        $mysqli->close();
        return false;
    }
}

// Get recommended movies function
function getRecMovies($username, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlTable, $mysqlMoviesTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to get the user's favorite genre from the users table
    $query = "SELECT fav_genre FROM $mysqlTable WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user has a favorite genre, use it to query for the top 10 movies of that genre
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $favGenre = $row['fav_genre'];
        $result->free();
        $stmt->close();

        // Prepare a statement to get movies based on the genre
        $movieQuery = "SELECT title, year, genre FROM $mysqlMoviesTable WHERE genre LIKE '%$favGenre%' LIMIT 10";
        $movieStmt = $mysqli->prepare($movieQuery);
        $movieStmt->execute();

        // Get the result
        $movieResult = $movieStmt->get_result();

        if ($movieResult->num_rows > 0) {
            $movies = [];
            while ($row = $movieResult->fetch_assoc()) {
                $movies[] = $row;
            }
            $movieResult->free();
            $movieStmt->close();
            $mysqli->close();
            return $movies;
        }
    } else {
        // Close connections and return an empty array if no movies found or user not found
        $mysqli->close();
        return [];
    }
}

// Get user bookmarks function
function getUserBookmarks($user_id, $mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlMoviesTable) {
    // Establish MySQL connection
    $mysqli = new mysqli($mysqlIP, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
    
    if ($mysqli->connect_error) {
        die("Connection to MySQL failed: " . $mysqli->connect_error);
    }

    // Prepare a statement to query the bookmarks table, joining with the movies table to get movie details
    $query = "SELECT M.* FROM bookmarks AS B
              JOIN $mysqlMoviesTable AS M ON B.movie_id = M.movie_id
              WHERE B.user_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If there are bookmarks, store them in an array
    if ($result->num_rows > 0) {
        $bookmarks = [];

        while ($row = $result->fetch_assoc()) {
            $bookmarks[] = $row;
        }

        $result->free();
        $stmt->close();
        $mysqli->close();
        return $bookmarks;
    } else {
        $stmt->close();
        $mysqli->close();
        return [];
    }
}
?>