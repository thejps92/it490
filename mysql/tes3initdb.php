<?php



// Database configuration

$dbName = 'Project';



// Initialize PDO connection

try {

    $pdo = new PDO("mysql:host=localhost;dbname=$dbName", 'root', '');

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Database connection failed: " . $e->getMessage());

}



// Function to insert movie data into the database

function insertMovieData($title, $year, $director) {

    global $pdo;



    $stmt = $pdo->prepare("INSERT INTO movies (title, year, genre) VALUES (:title, :year, :genre)");

    $stmt->bindParam(':title', $title);

    $stmt->bindParam(':year', $year);

    $stmt->bindParam(':director', $director);

    $stmt->execute();

}



// CSV file path

$csvFilePath = 'query.csv';



// Open and read the CSV file

if (($handle = fopen($csvFilePath, "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        $title = $data[0]; // Assuming the title is in the first column

        $year = $data[1];  // Assuming the year is in the second column

        $director = $data[2]; // Assuming the genre is in the third column



        // Insert movie data into the database

        insertMovieData($title, $year, $director);

    }

    fclose($handle);

}



echo "Database populated with movie data from the CSV file.";



// Close the database connection

$pdo = null;



?>
