CREATE TABLE lists (
    list_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    list_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);
