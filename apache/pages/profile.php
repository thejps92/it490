<?php
session_start();
if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
	$username = $_SESSION['username'];
	$email = $_SESSION['email'];
	$fav_genre = $_SESSION['fav_genre'];
} else {
    header('Location: signin.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Profile</title>
</head>
<body>
	<header>
		<h1>Profile</h1>
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
		<h2>Profile</h2>
		<p>User ID: <strong><?php echo $user_id; ?></strong></p>
		<p>Username: <strong><?php echo $username; ?></strong></p>
		<p>Email: <strong><?php echo $email; ?></strong></p>
		<p>Favorite Genre: <strong><?php echo $fav_genre; ?></strong></p>
	</section>
	</main>

	<footer>
        <form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form>
	</footer>
</body>
</html>