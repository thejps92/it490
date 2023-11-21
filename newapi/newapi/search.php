<?php
require_once('vendor/autoload.php');

// Database connection parameters
$dbHost = 'localhost';
$dbUsername = 'justis';
$dbPassword = 'root';
$dbName = 'movie_db';

// Create a new PDO instance
$db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);

// Set the PDO error mode to exception
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ask the user for a movie name
$movieName = readline("Enter a movie name: ");

// Prepare a SELECT statement to search for the movie in the database
$stmt = $db->prepare("SELECT * FROM movies WHERE title LIKE :movieName");
$stmt->bindValue(':movieName', '%' . $movieName . '%');
$stmt->execute();

// Fetch the matching movies from the database
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if any movies were found
if (count($movies) > 0) {
    // Display the movie information
    foreach ($movies as $movie) {
        echo "Movie ID: " . $movie['id'] . PHP_EOL;
        echo "Title: " . $movie['title'] . PHP_EOL;
        echo "Overview: " . $movie['overview'] . PHP_EOL;
        echo "Release Date: " . $movie['release_date'] . PHP_EOL;
        echo "Runtime: " . $movie['runtime'] . " minutes" . PHP_EOL;
        echo "Director: " . $movie['director'] . PHP_EOL;
        echo "Main Actor: " . $movie['main_actor'] . PHP_EOL;
        echo PHP_EOL;
    }
} else {
    echo "No movies found with the given name.";
}
?>

