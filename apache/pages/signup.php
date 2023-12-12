<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
</head>
<body>
    <h2>Sign Up</h2>
    <form action="publish_signup.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <label for="fav_genre">Favorite Genre:</label>
        <select name="fav_genre" id="fav_genre" required>
            <option value="action">Action</option>
            <option value="comedy">Comedy</option>
            <option value="romance">Romance</option>
            <option value="fantasy">Fantasy</option>
            <option value="horror">Horror</option>
            <option value="drama">Drama</option>
        </select>
        <br><br>
        <input type="submit" value="Submit">
    </form>
    <br>
    <p>Already a user? <a href="signin.php">Sign in</a></p>
    <p>Go to the Home page -> <a href="index.php">Home</a></p>
</body>
</html>