<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "myDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$userid = $_SESSION['userid']; // assuming you have session data for the logged in user
$movieid = $_POST['movieid'];
$rating = $_POST['rating'];

$sql = "INSERT INTO MovieRatings (userid, movieid, rating) VALUES ($userid, $movieid, $rating)";

if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

