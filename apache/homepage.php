<?php
include('search_bar.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Homepage</title>
</head>
<body>
	<header>
		<h1>Homepage</h1>
<br>
<form method="post" action="">
	<input type="text" name="search_query" placeholder="Search for a movie by title">
	<input type="submit" value="Search">
</form>

<?php

if(isset($_POST['search_query'])){
	$search_query = $_POST['search_query'];
	$movies = searchMovies($conn, $search_query);

	if(!empty($movies)){
		echo "<h2>Search Results:</h2>";
		echo "<ul>";
		foreach($movies as $movie){
			echo "<li>$movie</li>";
		}
		echo "</ul>";
	}else{
		echo "No matching movies found.";
	}
}

?>

<br>
<br>
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
		<h2>Home</h2>
		<p>This is the homepage.</p>
	</section>
	</main>

	<footer>
	    <form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form>
	</footer>
</body>
</html>
