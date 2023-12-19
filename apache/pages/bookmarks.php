<?php
session_start();
if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
	$bookmarks = $_SESSION['bookmarks'];
} else {
    header('Location: signin.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Bookmarks</title>
</head>
<body>
	<header>
		<h1>Bookmarks</h1>
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
		<h2>Bookmarks</h2>
		<?php
		if (!empty($bookmarks)) {
			echo "<table>";
		    echo "<tr><th>Title</th><th>Year</th><th>Genre</th></tr>";
		    foreach ($bookmarks as $bookmark) {
		        echo "<tr>";
		        echo "<td>" . $bookmark['title'] . "</td>";
		        echo "<td>" . $bookmark['year'] . "</td>";
		        echo "<td>" . $bookmark['genre'] . "</td>";
				echo "<td> <button class='remove' action='remove' userid='" . $user_id . "' movieid='" . $bookmark['movie_id'] . "'>Remove</button> </td>";
		        echo "</tr>";
		    }
		    echo "</table>";
		} else {
			echo "<p>No bookmarks found.</p>";
		}
		?>
	</section>
	</main>

	<script>
    document.addEventListener('click', function (event) {
        if (event.target && event.target.className === 'remove') {
			const action = event.target.getAttribute('action');
            const userId = event.target.getAttribute('userid');
            const movieId = event.target.getAttribute('movieid');
            const data = {
				action: action,
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
                    alert('Bookmark removed.');
                } else if (response.status === 400 || response.status === 401) {
                    alert('Removing bookmark failed.')
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
		}
	});
	</script>

	<footer>
	   <form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form> 
	</footer>
</body>
</html>