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
		        <li><a href="bookmark.php">Bookmarks</a></li>
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
                        echo '<strong>Genre:</strong> ' . $result['genre'];
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