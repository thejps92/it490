<?php
session_start();
// Check if the variable $_SESSION is set with the user's username
if (isset($_SESSION['username'])) {
    // Set the user's username to their username
    $username = htmlspecialchars($_SESSION['username']);
} else {
    // If the user is not signed in redirect them to the home page
    header('Location: signin.php');
    exit();
}
//$db_host = "db_host";
//$db_user = "db_user";
//$db_pass = "db_pass";
//$db_name = "db_name";

//$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check if user has favorite genre
//$user_id = 1; //remember to replace with actual ID
//$query = "SELECT fav_genre FROM Users where user_id = $user_id";
//$result = mysqli_query($conn, $query);

//if($result && mysqli_num_rows($result) > 0){
	//User has fav already
//	$row = mysqli_fetch_assoc($result);
//	$fav_movie = $row["fav_genre"];
//}else{
	//User does not have fav genre, let them choose
//	$available_genres = array("comedy", "romance", "action", "fantasy", "drama", "horror");

//	if (isset($_POST["new_fav_genre"])){
//		$new_fav_genre = $_POST["new_fav_genre"];
//		if(in_array($new_fav_genre, $available_genres)){
//			$query = "UPDATE users SET fav_genre = '$new_fav_genre' WHERE user_id = $user_id";
//			mysqli_query($conn, $query);
//			$fav_genre = $new_fav_genre;
//		}else{
//			echo "Invalid genre selection.";
//		}
//	}
//}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Profile</title>
</head>
<body>
	<header>
		<h1>Profile Page</h1>
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
	<p>User ID: <?php echo $id; ?></p>
	<p>Username: <?php echo $username; ?></p>
	<p>Favorite Genre: <?php echo $fav_genre; ?></p>
</body>

	<footer>
	   <h1>Hello, <?php echo $username; ?></h1>
        <form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form>
	</footer>
</body>
</html>
