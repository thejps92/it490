const express = require('express');
const app = express();

// API route for movie lookup
app.get('/movie-lookup', async (req, res) => {
    const title = req.query.title;
    const movieDetails = await fetchMovieDetailsByTitle(title);
    res.json(movieDetails);
});

const port = 3000;
app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});
