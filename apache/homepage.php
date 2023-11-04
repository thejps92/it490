<!DOCTYPE html>
<html>
<head>
	<title>Homepage</title>
</head>
<body>
	<header>
		<h1>Homepage</h1>
<br>
<form action="publish_search.php" method="post">
	<input type="text" name="searchQuery" id="searchQuery" placeholder="Search for a movie" required>
	<input type="submit" value="Search">
</form>

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