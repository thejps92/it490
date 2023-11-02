<?php
session_start();
if (isset($_SESSION['username'])) {
    // Set the user's username to their username
    $username = htmlspecialchars($_SESSION['username']);
} else {
    // If the user is not signed in redirect them to the home page
    header('Location: signin.php');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Friends</title>
</head>
<body>
	<header>
		<h1>Friends List</h1>
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
		<h2>Friends List</h2>
		<p>This is the friends list.</p>
	</section>
	</main>

	<footer>
	   <form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form>
	</footer>
</body>
</html>
