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
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
	<header>
	<nav class="navbar navbar-expand-lg navbar-light bg-info">
	<div class="container-fluid">
		<a class="navbar-brand text-white">490Central</a>
			<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
			<div class="navbar-nav">
				<a class="nav-item nav-link text-white" href="index.php">Home <span class="sr-only">(current)</span></a>
				<a class="nav-item nav-link text-white" href="recommendations.php">Recommendations</a>
				<a class="nav-item nav-link text-white" href="profile.php">Profile</a>
				<a class="nav-item nav-link text-white" href="bookmarks.php">Bookmarks</a>
				<a class="nav-item nav-link text-white" href="friends.php">Friends</a>
			</div>
			</div>
	</div>
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
	
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>