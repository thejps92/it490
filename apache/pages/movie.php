<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

if (isset($_SESSION['movie'], $_SESSION['reviews'])) {
    $movie = $_SESSION['movie'];
    $reviews = $_SESSION['reviews'];
} else {
    header('Location: index.php');
    exit();
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
            <h2>Movie Details</h2>
            <?php
            if (!empty($movie)) {
                foreach ($movie as $item) {
                    echo '<strong>Title:</strong> ' . $item['title'] . '<br>';
                    echo '<strong>Year:</strong> ' . $item['year'] . '<br>';
                    echo '<strong>Genre:</strong> ' . $item['genre'] . '<br>';
                    echo '<button class="bookmark" userid="' . $user_id . '" movieid="' . $item['movie_id'] . '">Bookmark</button>';
                }
                unset($_SESSION['movie']);
            } else {
                echo '<p>No movie details available.</p>';
                unset($_SESSION['movie']);
            }
            ?>
        </section>
        
        <section>
            <h2>Reviews</h2>
            <?php
            if (!empty($reviews)) {
                echo '<table>';
                echo '<tr><th>User</th><th>Review</th><th>Rating</th><th>Date</th></tr>';
                foreach ($reviews as $review) {
                    echo '<tr>';
                    echo '<td>' . $review['username'] . '</td>';
                    echo '<td>' . $review['review'] . '</td>';
                    echo '<td>' . $review['rating'] . '</td>';
                    echo '<td>' . $review['review_date'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                unset($_SESSION['reviews']);
            } else {
                echo '<p>No reviews available.</p>';
                unset($_SESSION['reviews']);
            }
            ?>
        </section>

        <section>
            <h2>Write a Review</h2>
            <?php
            if (!empty($user_id)) {
                if (!empty($movie)) {
                    foreach ($movie as $item) {
                        echo '
                        <form action="publish_review.php" method="POST">
                            <input type="hidden" name="user_id" value="' . $user_id . '">
                            <input type="hidden" name="movie_id" value="' . $item['movie_id'] . '">
                            <label for="review">Your Review:</label><br>
                            <textarea id="review" name="review" rows="4" cols="50" required></textarea><br><br>
        
                            <label for="rating">Your Rating:</label>
                            <select id="rating" name="rating" required>
                                <option value="" disabled selected>Select rating...</option>
                                <option value="5">5</option>
                                <option value="4">4</option>
                                <option value="3">3</option>
                                <option value="2">2</option>
                                <option value="1">1</option>
                            </select><br><br>
                            
                            <input type="submit" value="Submit Review">
                        </form>
                        ';
                    }
                } else {
                    echo '<p>Unable to write a review.</p>';
                }
            } else {
                echo '<p>Please <a href="signin.php">sign in</a> to write a review.</p>';
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
                    if (response.status === 200) {
                        alert('Movie bookmarked.');
                    } else if (response.status === 401) {
                        alert('Bookmark already exists.');
                    } else if (response.status === 400) {
                        alert('Bookmark failed.')
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            } else {
                alert('Please sign in to bookmark movies.');
            }
        }
    });
    </script>
</body>
</html>