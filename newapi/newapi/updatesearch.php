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
	echo "Genre: " . $movie['genre'] . PHP_EOL; 
        echo PHP_EOL;
    }
} else {
    // Movie not found in the database, search using TMDb API
    $client = new \GuzzleHttp\Client();

    // Your TMDb API key
    $apiKey = 'e98da0c4d12370199a34d5a7ccc91c28';
    // Make the API request
    $url = "https://api.themoviedb.org/3/search/movie?include_adult=false&language=en-US&page=1&query=" . urlencode($movieName);
    $response = $client->request('GET', $url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
            'accept' => 'application/json',
        ],
    ]);

    // Decode the JSON response
    $data = json_decode($response->getBody(), true);

    // Check if there are results
    if (isset($data['results']) && count($data['results']) > 0) {
        // Display the details of the first movie found
        $firstMovie = $data['results'][0];
        echo "Movie found using TMDb API:" . PHP_EOL;
        echo "Title: " . $firstMovie['title'] . PHP_EOL;
        echo "Overview: " . $firstMovie['overview'] . PHP_EOL;
	echo "Release Date: " . $firstMovie['release_date'] . PHP_EOL;
	echo PHP_EOL;

        // Make the API request to get additional details including runtime
	$urlMovieDetails = "https://api.themoviedb.org/3/movie/{$firstMovie['id']}?api_key=$apiKey&language=en-US";
	$responseMovieDetails = $client->request('GET', $urlMovieDetails);
        $movieDetails = json_decode($movieDetails->getBody(), true);

        $runtime = $movieDetails['runtime'] ?? null;

        // Add the movie details to the database
        $stmt = $db->prepare("INSERT INTO movies (id, title, overview, release_date) VALUES (:id, :title, :overview, :release_date)");
        $stmt->bindParam(':id', $firstMovie['id']);
        $stmt->bindParam(':title', $firstMovie['title']);
        $stmt->bindParam(':overview', $firstMovie['overview']);
        $stmt->bindParam(':release_date', $firstMovie['release_date']);
        $stmt->execute();

        echo "Movie added to the database.";
    } else {
        echo "No movies found with the given name.";
    }
}
?>
