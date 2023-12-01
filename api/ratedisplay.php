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

$movieid = 123; // replace with the ID of the movie you want to display

$sql = "SELECT AVG(rating) as average_rating FROM MovieRatings WHERE movieid = $movieid";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "Average rating: " . $row["average_rating"];
  }
} else {
  echo "No ratings yet";
}
$conn->close();
?>

