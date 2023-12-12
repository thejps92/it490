<?php
session_start();
if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['fav_genre'])) {
	$user_id = $_SESSION['user_id'];
	$username = $_SESSION['username'];
	$fav_genre = $_SESSION['fav_genre'];
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
		        echo "</tr>";
		    }
		    echo "</table>";
		} else {
			echo "<p>No bookmarks found.</p>";
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