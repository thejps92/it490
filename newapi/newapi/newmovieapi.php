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

$response = $client->request('GET', 'https://api.themoviedb.org/3/discover/movie?include_adult=false&include_video=false&language=en-US&page=500&sort_by=popularity.desc', [
  'headers' => [
    'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJlOThkYTBjNGQxMjM3MDE5OWEzNGQ1YTdjY2M5MWMyOCIsInN1YiI6IjY1NGFjMmRkNjdiNjEzMDEwMmUxM2U2YiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.ZHkeqy2Qtw8tZmaxeWU-fKeCC5eY7XAWuaX-i-nOC00',
    'accept' => 'application/json',
  ],
]);

// Decode the JSON response
$data = json_decode($response->getBody(), true);

// Loop through the results
foreach ($data['results'] as $movie) {
    $movieId = $movie['id'];

    // Fetch the movie details
    $response = $client->request('GET', "https://api.themoviedb.org/3/movie/$movieId", [
      'headers' => [
        'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJlOThkYTBjNGQxMjM3MDE5OWEzNGQ1YTdjY2M5MWMyOCIsInN1YiI6IjY1NGFjMmRkNjdiNjEzMDEwMmUxM2U2YiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.ZHkeqy2Qtw8tZmaxeWU-fKeCC5eY7XAWuaX-i-nOC00',
        'accept' => 'application/json',
      ],
    ]);
    $movieDetails = json_decode($response->getBody(), true);
    $runtime = $movieDetails['runtime'];

    // Fetch the movie credits
    $response = $client->request('GET', "https://api.themoviedb.org/3/movie/$movieId/credits", [
      'headers' => [
        'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJlOThkYTBjNGQxMjM3MDE5OWEzNGQ1YTdjY2M5MWMyOCIsInN1YiI6IjY1NGFjMmRkNjdiNjEzMDEwMmUxM2U2YiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.ZHkeqy2Qtw8tZmaxeWU-fKeCC5eY7XAWuaX-i-nOC00',
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
    $stmt = $db->prepare("INSERT INTO movies (id, title, overview, release_date, runtime, director, main_actor) VALUES (:id, :title, :overview, :release_date, :runtime, :director, :main_actor)");

    //```
    // Bind parameters
    $stmt->bindParam(':id', $movie['id']);
    $stmt->bindParam(':title', $movie['title']);
    $stmt->bindParam(':overview', $movie['overview']);
    $stmt->bindParam(':release_date', $movie['release_date']);
    $stmt->bindParam(':runtime', $runtime);
    $stmt->bindParam(':director', $director);
    $stmt->bindParam(':main_actor', $mainActor);

    // Execute the statement
    $stmt->execute();
}

echo "Movies inserted successfully!";
?>
