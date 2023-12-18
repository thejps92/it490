<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
    <header>
        <h1>Home</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="recommendations.php">Recommendations</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="bookmarks.php">Bookmarks</a></li>
                <li><a href="friends.php">Friends</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Search Movies</h2>
            <form method="post" action="publish_search.php">
                <input type="text" name="searchQuery" placeholder="Search movies..." required>
                <select name="searchType" required>
                    <option value="" disabled selected>Select search type...</option>
                    <option value="title">Title</option>
                    <option value="year">Year</option>
                    <option value="genre">Genre</option>
                </select>
                <input type="submit" name="search" value="Search">
            </form>
        </section>
    </main>
</body>
</html>