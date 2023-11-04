<?php

function searchMovies($conn, $search_query){
	$query = "SELECT * FROM movies WHERE title LIKE '%$search_query'";
	$result = mysqli_query($conn, $query);

	$movies = array();
	while($row = mysqli_fetch_assoc($result)){
		$movies[] = $row['title'];
	}
	return $movies;
}
?>
