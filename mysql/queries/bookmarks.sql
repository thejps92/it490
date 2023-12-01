CREATE TABLE bookmarks (
    bookmark_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id int,
    movie_id int,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id)
);