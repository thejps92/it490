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

// sql to create table
$sql = "CREATE TABLE MovieRatings (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
userid INT(6) NOT NULL,
movieid INT(6) NOT NULL,
rating INT(1) NOT NULL,
reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
  echo "Table MovieRatings created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
?>

