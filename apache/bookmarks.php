<?php
session_start();
// Check if the variable $_SESSION is set with the user's username
if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['fav_genre'])) {
    // Set the user's username to their username
	$user_id = $_SESSION['user_id'];
	$username = $_SESSION['username'];
	$fav_genre = $_SESSION['fav_genre'];
    $bookmarks = $_SESSION['bookmarks'];
} else {
    // If the user is not signed in, redirect them to the home page
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
		<h1>Bookmark Page</h1>
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
		<h2>Bookmarks</h2>
		<?php
		// Check if the user has any bookmarks
		if (empty($bookmarks)) {
		    echo "<p>No bookmarks found.</p>";
		} else {
		    // Display the bookmarks in a table
		    echo "<table>";
		    echo "<tr><th>Title</th><th>Year</th><th>Genre</th></tr>";
		    foreach ($bookmarks as $bookmark) {
		        echo "<tr>";
		        echo "<td>" . $bookmark['title'] . "</td>";
		        echo "<td>" . $bookmark['year'] . "</td>";
		        echo "<td>" . $bookmark['genre'] . "</td>";
		        echo "</tr>";
		    }
		    echo "</table>";
		}
		?>
	</section>
	</main>

	<footer>
	   <form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form> 
	</footer>
</body>
</html>