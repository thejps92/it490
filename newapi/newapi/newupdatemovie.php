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

$client = new \GuzzleHttp\Client();

// Your TMDb API key
$apiKey = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJlOThkYTBjNGQxMjM3MDE5OWEzNGQ1YTdjY2M5MWMyOCIsInN1YiI6IjY1NGFjMmRkNjdiNjEzMDEwMmUxM2U2YiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.ZHkeqy2Qtw8tZmaxeWU-fKeCC5eY7XAWuaX-i-nOC00';

// Initialize the page number
$page = 400;

// Loop through all 200 pages (adjust as needed)
while ($page <= 500) {
    // Make the API request
    $url = "https://api.themoviedb.org/3/discover/movie?include_adult=false&include_video=false&language=en-US&page={$page}&sort_by=popularity.desc";
    $response = $client->request('GET', $url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
            'accept' => 'application/json',
        ],
    ]);

    // Decode the JSON response
    $data = json_decode($response->getBody(), true);

    // Loop through the results
    foreach ($data['results'] as $movie) {
        $movieId = $movie['id'];
        $title = $movie['title'];
        $overview = $movie['overview'];
        $releaseDate = $movie['release_date'];

        // Check if the movie already exists in the database
        $existingMovieQuery = $db->prepare("SELECT COUNT(*) FROM movies WHERE id = :movieId");
        $existingMovieQuery->bindParam(':movieId', $movieId);
        $existingMovieQuery->execute();
        $existingMovieCount = $existingMovieQuery->fetchColumn();

        if ($existingMovieCount > 0) {
            // Movie already exists, skip inserting
            echo "Movie with ID $movieId already exists in the database.<br>";
        } else {
            // Fetch the movie details including genres
            $response = $client->request('GET', "https://api.themoviedb.org/3/movie/$movieId", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'accept' => 'application/json',
                ],
            ]);
            $movieDetails = json_decode($response->getBody(), true);
            $runtime = $movieDetails['runtime'];
            $genres = array_column($movieDetails['genres'], 'name');
            $genreString = implode(', ', $genres);

            // Fetch the movie credits
            $response = $client->request('GET', "https://api.themoviedb.org/3/movie/$movieId/credits", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'accept' => 'application/json',
                ],
            ]);
            $movieCredits = json_decode($response->getBody(), true);

            // Get the director and main actor
            $director = '';
            $mainActor = '';
            foreach ($movieCredits['crew'] as $crewMember) {
                if ($crewMember['job'] === 'Director') {
                    $director = $crewMember['name'];
                    break;
                }
            }
            if (isset($movieCredits['cast'][0])) {
                $mainActor = $movieCredits['cast'][0]['name'];
            }

            // Prepare an INSERT statement
            $stmt = $db->prepare("INSERT INTO movies (id, title, overview, release_date, runtime, director, main_actor, genre) VALUES (:id, :title, :overview, :release_date, :runtime, :director, :main_actor, :genre)");

            // Bind the parameters
            $stmt->bindParam(':id', $movieId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':overview', $overview);
            $stmt->bindParam(':release_date', $releaseDate);
            $stmt->bindParam(':runtime', $runtime);
            $stmt->bindParam(':director', $director);
            $stmt->bindParam(':main_actor', $mainActor);
            $stmt->bindParam(':genre', $genreString);

            // Execute the statement
            $stmt->execute();

            // Movie inserted successfully
            echo "Movie with ID $movieId inserted into the database.<br>";
        }
    }

    // Increment the page number
    $page++;
}

echo "All movies inserted into the database.";
?>

