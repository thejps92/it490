CREATE DATABASE IF NOT EXISTS movie_db;
USE movie_db;

CREATE TABLE IF NOT EXISTS movies (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    overview TEXT,
    release_date DATE,
    runtime INT,
    director VARCHAR(255),
    main_actor VARCHAR(255),
    genre VARCHAR(255),
    watch_providers TEXT
);

