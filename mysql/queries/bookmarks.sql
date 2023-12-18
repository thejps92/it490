CREATE TABLE bookmarks (
    bookmark_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    movie_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id)
);