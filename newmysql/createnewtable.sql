CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Director VARCHAR(255),
    Genre VARCHAR(255),
    Runtime INT,
    Year INT,
    SampledTitle VARCHAR(255)
);

