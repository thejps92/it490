<?php
session_start();
if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
	$movies = $_SESSION['movies'];
} else {
    header('Location: signin.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Recommendations</title>
</head>
<body>
	<header>
		<h1>Recommendations</h1>
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
		<h2>Recommendations</h2>
		<?php
		if (!empty($movies)) {
			echo '<table>';
			echo '<tr><th>Title</th><th>Year</th><th>Genre</th></tr>';
			foreach ($movies as $movie) {
				echo '<tr>';
				echo '<td>' . $movie['title'] . '</td>';
				echo '<td>' . $movie['year'] . '</td>';
				echo '<td>' . $movie['genre'] . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		} else {
			echo '<p>No recommended movies available.</p>';
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