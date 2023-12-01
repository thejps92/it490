<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
</head>
<body>
    <header>
        <h1>Search Results</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="recommendations.php">Recommendations</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="bookmarks.php">Bookmarks</a></li>
                <li><a href="friends.php">Friends</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Here are your search results:</h2>
            <?php
            if (isset($_GET['results'])) {
                $results = json_decode($_GET['results'], true);

                if (!empty($results)) {
                    echo '<ul>';
                    foreach ($results as $result) {
                        echo '<li>';
                        echo '<strong>Title:</strong> ' . $result['title'] . '<br>';
                        echo '<form action="publish_movie.php" method="post">';
                        echo '<input type="hidden" name="movie_id" value="' . $result['movie_id'] . '">';
                        echo '<input type="submit" value="View Movie">';
                        echo '</form>';
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo 'No results found.';
                }
            } else {
                echo 'Search failed. Please try again.';
            }
            ?>
        </section>
    </main>
</body>
</html>