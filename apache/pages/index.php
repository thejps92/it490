<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
	
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	
	<style>
    .movie {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }

    .movie img {
      max-width: 150px;
      height: auto;
      margin-right: 20px;
    }
	
	.container-center {
	  display: flex;
	  justify-content: center;
	  align-items: center;
	  height: 100vh; /* Adjust height as needed */
	  flex-direction: column;
	}

	/* Center the search bar */
        .search-container {
          display: flex;
          justify-content: center;
          margin-bottom: 20px;
        }
  </style>
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
					<h2>Popular Movies</h2>
				</div>
			</div>
		</div>
        </section>
        <section>
            <div class="container mt-3">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
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
		<section>
			<div class="container mt-3">
				<div class="row">
					<div id="movies" class="col-md-8 offset-md-2"></div>
				</div>
			</div>
		</section>
    </main>
	
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script>
		async function fetchGenres() {
		  try {
			const response = await fetch('https://api.themoviedb.org/3/genre/movie/list?language=en-US', {
			  method: 'GET',
			  headers: {
				'Authorization': 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJlOThkYTBjNGQxMjM3MDE5OWEzNGQ1YTdjY2M5MWMyOCIsInN1YiI6IjY1NGFjMmRkNjdiNjEzMDEwMmUxM2U2YiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.ZHkeqy2Qtw8tZmaxeWU-fKeCC5eY7XAWuaX-i-nOC00',
				'accept': 'application/json'
			  }
			});
			const data = await response.json();
			return data.genres;
		  } catch (error) {
			console.error('Error fetching genres:', error);
			return [];
		  }
		}

		async function fetchMovies() {
		  try {
			const [genres, moviesResponse] = await Promise.all([
			  fetchGenres(),
			  fetch('https://api.themoviedb.org/3/movie/popular?language=en-US&page=1', {
				method: 'GET',
				headers: {
				  'Authorization': 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJlOThkYTBjNGQxMjM3MDE5OWEzNGQ1YTdjY2M5MWMyOCIsInN1YiI6IjY1NGFjMmRkNjdiNjEzMDEwMmUxM2U2YiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.ZHkeqy2Qtw8tZmaxeWU-fKeCC5eY7XAWuaX-i-nOC00',
				  'accept': 'application/json'
				}
			  }).then(response => response.json())
			]);

			const movies = moviesResponse.results;

			const moviesDiv = document.getElementById('movies');
			if (movies && movies.length) {
			  movies.forEach(movie => {
				const movieElement = document.createElement('div');
				movieElement.classList.add('movie');

				if (movie.poster_path) {
                            const posterUrl = `https://image.tmdb.org/t/p/w300${movie.poster_path}`;
                            const posterImage = document.createElement('img');
                            posterImage.src = posterUrl;
                            posterImage.alt = `${movie.title} poster`;
                            posterImage.classList.add('img-fluid');
                            movieElement.appendChild(posterImage);
                        }

				const movieDetails = document.createElement('div');
				const movieGenres = movie.genre_ids.map(genreId => {
				  const genre = genres.find(genre => genre.id === genreId);
				  return genre ? genre.name : 'Unknown';
				}).join(', ');

				movieDetails.innerHTML = `
				  <h2>${movie.title}</h2>
				  <p>${movie.overview}</p>
				  <p>Year: ${new Date(movie.release_date).getFullYear()}</p>
				  <p>Genres: ${movieGenres}</p>
				`;
				movieElement.appendChild(movieDetails);

				moviesDiv.appendChild(movieElement);
			  });
			} else {
			  moviesDiv.innerHTML = 'No movies found.';
			}
		  } catch (error) {
			console.error('Error fetching movies:', error);
		  }
		}

		fetchMovies();
	</script>
</body>
</html>
