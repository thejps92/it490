const axios = require('axios');

// Your OMDB API key
const apiKey = '8d92d6cb';

// Function to fetch movie details by title
async function fetchMovieDetailsByTitle(title) {
    try {
        const url = `http://www.omdbapi.com/?apikey=${apiKey}&t=${title}`;
        const response = await axios.get(url);
        return response.data;
    } catch (error) {
        console.error('Error:', error);
    }
}

// Example usage
const movieTitle = 'The Shawshank Redemption';
fetchMovieDetailsByTitle(movieTitle)
    .then(data => {
        console.log('Movie Details:', data);
    });
