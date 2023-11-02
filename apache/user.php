<?php
// Start the session (if not already started)
session_start();

// Check if the variable $_SESSION is set with the user's username
if (isset($_SESSION['username'])) {
    // Set the user's username to their username
    $username = htmlspecialchars($_SESSION['username']);
} else {
    // If the user is not signed in redirect them to the home page
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>User Page</title>
</head>
<body>
	<h1>Hello, <?php echo $username; ?></h1>
	<form method="post" action="publish_signout.php">
		<input type="submit" name="signout" value="Sign Out">
	</form>
</body>
</html>