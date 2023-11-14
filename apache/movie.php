<?php
session_start();
// Check if the variable $_SESSION is set with the user's user_id
if (isset($_SESSION['user_id'])) {
    // Set the user's user_id to their user_id
	$user_id = $_SESSION['user_id'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movie Details</title>
</head>
<body>
    <header>
        <h1>Movie Details</h1>
        <nav>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="recommendations.php">Recommendations</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="bookmarks.php">Bookmarks</a></li>
                <li><a href="friends.php">Friends</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Movie Details</h2>
            <?php
            if (isset($_GET['movie'])) {
                $movieDetails = json_decode(urldecode($_GET['movie']), true);
                foreach ($movieDetails as $movie) {
                    echo '<strong>Title:</strong> ' . $movie['title'] . '<br>';
                    echo '<strong>Year:</strong> ' . $movie['year'] . '<br>';
                    echo '<strong>Genre:</strong> ' . $movie['genre'] . '<br>';
                    echo '<button class="bookmark" userid="' . $_SESSION['user_id'] . '" movieid="' . $movie['movie_id'] . '">Bookmark</button>';
                }
            }
            ?>
        </section>
    </main>

    <script>
    document.addEventListener('click', function (event) {
        if (event.target && event.target.className === 'bookmark') {
            const userId = event.target.getAttribute('userid');
            const movieId = event.target.getAttribute('movieid');

            if (userId) {
                // User is signed in; allow bookmarking
                const data = {
                    user_id: userId,
                    movie_id: movieId
                };
                const jsonData = JSON.stringify(data);

                fetch('publish_bookmark.php', {
                    method: 'POST',
                    body: jsonData,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        alert('Movie bookmarked successfully!');
                    } else {
                        alert('Bookmarking failed.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            } else {
                // User is not signed in; provide a message to sign in
                alert('Please sign in to bookmark movies.');
            }
        }
    });
    </script>
</body>
</html>