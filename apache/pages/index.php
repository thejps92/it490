<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
	
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
				<a class="nav-item nav-link active text-white" href="index.php">Home <span class="sr-only">(current)</span></a>
				<a class="nav-item nav-link text-white" href="recommendations.php">Recommendations</a>
				<a class="nav-item nav-link text-white" href="profile.php">Profile</a>
				<a class="nav-item nav-link text-white" href="bookmarks.php">Bookmarks</a>
				<a class="nav-item nav-link text-white" href="friends.php">Friends</a>
			</div>
			</div>
	</div>
	</nav>



    </header>
<br>
    <main>
        <section>
		
		<div class="container mt-3">
    <div class="row">
        <div class="col-12 text-center">
            <h2>Search Movies</h2>
        </div>

        <div class="col-12 mx-auto">
            <form class="form-inline my-2 my-lg-0" method="post" action="publish_search.php">
                <div class="input-group">
                    <input class="form-control" type="text" name="searchQuery" placeholder="Search movies...">
                    <select class="custom-select" name="searchType">
                        <option value="title">Title</option>
                        <option value="year">Year</option>
                        <option value="genre">Genre</option>
                    </select>
                    <div class="input-group-append">
                        <input class="btn btn-outline-success" type="submit" name="search" value="Search">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
        </section>
    </main>
	
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	
</body>
</html>
