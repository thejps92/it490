<?php
session_start();
if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['fav_genre'], $_SESSION['movies'])) {
	$user_id = $_SESSION['user_id'];
	$username = $_SESSION['username'];
	$fav_genre = $_SESSION['fav_genre'];
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
		<ul>
            <?php
            foreach ($movies as $movie) {
                echo '<li>' . $movie['title'] . ' - ' . $movie['year'] . ' - ' . $movie['genre'] . '</li>';
            }
            ?>
        </ul>
	</section>
	</main>

	<footer>
	<form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form>	   
	</footer>
</body>
</html>