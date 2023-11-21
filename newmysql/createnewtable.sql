CREATE TABLE movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    director VARCHAR(255),
    genre VARCHAR(255),
    runtime INT,
    year INT,
    sampledTitle VARCHAR(255)
);

