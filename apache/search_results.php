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
    <title>Search Results</title>
</head>
<body>
    <header>
        <h1>Search Results</h1>
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
            <h2>Here are your search results:</h2>
            <?php
            if (isset($_GET['results'])) {
                $results = json_decode($_GET['results'], true);

                if (!empty($results)) {
                    echo '<ul>';
                    foreach ($results as $result) {
                        echo '<li>';
                        echo '<strong>Title:</strong> ' . $result['title'] . '<br>';
                        echo '<strong>Year:</strong> ' . $result['year'] . '<br>';
                        echo '<strong>Genre:</strong> ' . $result['genre'] . '<br>';
                        echo '<button class="bookmark" userid="' . $_SESSION['user_id'] . '" movieid="' . $result['movie_id'] . '">Bookmark</button>';
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