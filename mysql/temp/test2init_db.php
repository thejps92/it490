<?php
// Database configuration
$dbName = 'project';


// OMDB API configuration
$apiKey = '8d92d6cb'; // Your OMDB API key
$apiUrl = 'http://www.omdbapi.com/';

// Function to fetch and insert movie data into the database
function fetchAndInsertMovieData($title, $year, $genre) {
    global $pdo;

    // Insert movie data into the database
    $stmt = $pdo->prepare("INSERT INTO movies (title, year, genre) VALUES (:title, :year, :genre)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':genre', $genre);
    $stmt->execute();
}

// Initialize API parameters
$page = 1; // Start with page 1

do {
    // Construct the API URL with page number
    $url = "$apiUrl?apikey=$apiKey&page=$page";

    // Fetch movie data from the API
    $data = file_get_contents($url);
    $movieData = json_decode($data, true);

    if ($movieData && isset($movieData['Search'])) {
        // Process and insert the movie data
        foreach ($movieData['Search'] as $movie) {
            $title = $movie['Title'];
            $year = $movie['Year'];
            $genre = $movie['Genre'];
            fetchAndInsertMovieData($title, $year, $genre);
        }

        // Move to the next page
        $page++;
    } else {
        // No more results, exit the loop
        break;
    }
} while (true);

echo "Database populated with all movies from the OMDB API.";

// Close the database connection
$pdo = null;
?>

