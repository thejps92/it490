<!DOCTYPE html>
<html>
<head>
    <title>Movie Search</title>
</head>
<body>
    <h1>Movie Search</h1>
    <form method="POST" action="">
        <label for="movieTitle">Enter a movie title:</label>
        <input type="text" id="movieTitle" name="movieTitle" required>
        <button type="submit">Search</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the user's search query
        $searchTerm = $_POST['movieTitle'];

        // Construct the API URL with the search term and your API key
        $apiKey = '8d92d6cb'; // Replace with your API key
        $apiUrl = "http://www.omdbapi.com/?apikey={$apiKey}&s=" . urlencode($searchTerm);

        // Make an API request
        $apiResponse = file_get_contents($apiUrl);
        $apiData = json_decode($apiResponse, true);

        if ($apiData && isset($apiData['Search'])) {
            echo "<h2>Search results for '{$searchTerm}':</h2>";
            echo "<ul>";
            foreach ($apiData['Search'] as $movie) {
                echo "<li>";
                echo "Title: " . $movie['Title'] . "<br>";
                echo "Year: " . $movie['Year'] . "<br>";
                echo "IMDb ID: " . $movie['imdbID'] . "<br>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "No results found for '{$searchTerm}'.";
        }
    }
    ?>
</body>
</html>

