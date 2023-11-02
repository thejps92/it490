<?php
//$db_host = "db_host";
//$db_user = "db_user";
//$db_pass = "db_pass";
//$db_name = "db_name";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check if user has favorite genre
$user_id = 1; //remember to replace with actual ID
$query = "SELECT fav_genre FROM Users where user_id = $user_id";
$result = mysqli_query($conn, $query);

if($result && mysqli_num_rows($result) > 0){
	//User has fav already
	$row = mysqli_fetch_assoc($result);
	$fav_movie = $row["fav_genre"];
}else{
	//User does not have fav genre, let them choose
	$available_genres = array("comedy", "romance", "action", "fantasy", "drama", "horror");

	if (isset($_POST["new_fav_genre"])){
		$new_fav_genre = $_POST["new_fav_genre"];
		if(in_array($new_fav_genre, $available_genres)){
			$query = "UPDATE users SET fav_genre = '$new_fav_genre' WHERE user_id = $user_id";
			mysqli_query($conn, $query);
			$fav_genre = $new_fav_genre;
		}else{
			echo "Invalid genre selection.";
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Homepage</title>
</head>
<body>
	<header>
		<h1>Homepage</h1>
	<nav>
	<ul>
		<li><a href="homepage.php">Home</a></li>
		<li><a href="recomendations.php">Recommendations</a></li>
		<li><a href="profile.php">Profile</a></li>
		<li><a href="bookmark.php">Bookmarks</a></li>
		<li><a href="friends.php">Friends</a></li>
	</ul>
	</nav>
	</header>

	<main>
	<section>
		<h2>Profile</h2>
		<p>This is the profile page.</p>
	</section>
	</main>

	<body>
	<h1>User Profile</h1>
	<p>User ID: <?php echo $userid; ?></p>
	<p>Username: <?php echo $username; ?></p>
	<p>Favorite Movie: <?php echo $fav_movie; ?></p>
	<p>Favorite Genre: <?php echo $fav_genre; ?></p>
	<?php if (!isset($fav_genre)): ?>
	<form method="post">
	<label for="new_fav_genre">Select a new favorite genre:</label>
		<select name="new_fav_genre">
		<?php foreach($available_genres as $genre): ?>
			<option value="<?php echo $genre; ?>"><?php echo $genre;?></option>
		<?php endforeach; ?>
		</select>
		<input type="submit" value="Update">
	</form>
	<?php endif; ?>
</body>

	<p>Favorite Actor: <?php echo $fav_actor; ?></p>


	<footer>
	   <p>&copy; <?php echo date("Y"); ?> My Basic Website</p>
	</footer>
</body>
</html>

<?php
mysqli_close($conn);
?>
