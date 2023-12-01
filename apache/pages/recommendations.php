<?php
session_start();
// Check if the variable $_SESSION is set with the user's information
if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['fav_genre'], $_SESSION['movies'])) {
    // Set the user's information
    $user_id = $_SESSION['user_id'];
	$username = $_SESSION['username'];
    $fav_genre = $_SESSION['fav_genre'];
    $movies = $_SESSION['movies'];
} else {
    // If the user is not signed in, redirect them to the home page
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
		<h1>Recommendations Page</h1>
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
		<p>This is the recommendations.</p>
	</section>
	</main>

	<body>
	<h1>User Profile</h1>
        <p>User ID: <?php echo $user_id; ?></p>
        <p>Username: <?php echo $username; ?></p>
        <p>Favorite Genre: <?php echo $fav_genre; ?></p>

        <h2>Top 10 Movies in Your Favorite Genre</h2>
        <ul>
            <?php
            // Iterate through the movies array and display each movie title and genre
            foreach ($movies as $movie) {
                echo '<li>' . $movie['title'] . ' - ' . $movie['year'] . ' - ' . $movie['genre'] . '</li>';
            }
            ?>
        </ul>
	</body>

	<footer>
	<form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form>	   
	</footer>
</body>
</html>