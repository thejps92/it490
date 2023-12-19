<?php
session_start();
if (isset($_SESSION['movieDetails'])) {
    $movieDetails = $_SESSION['movieDetails'];
} else {
    header('Location: index.php');
    exit();
}
?>

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
            if (!empty($movieDetails)) {
                echo '<ul>';
                foreach ($movieDetails as $movieDetail) {
                    echo '<li>';
                    echo '<strong>Title:</strong> ' . $movieDetail['title'] . '<br>';
                    echo '<form action="publish_movie.php" method="post">';
                    echo '<input type="hidden" name="movie_id" value="' . $movieDetail['movie_id'] . '">';
                    echo '<input type="submit" value="View Movie">';
                    echo '</form>';
                    echo '</li>';
                }
                echo '</ul>';
                unset($_SESSION['movieDetails']);
            } else {
                echo '<p>No results found.</p>';
                unset($_SESSION['movieDetails']);
            }
            ?>
        </section>
    </main>
</body>
</html>