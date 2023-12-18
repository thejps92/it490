CREATE TABLE friends (
    friend_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user1_id INT,
    user2_id INT,
    FOREIGN KEY (user1_id) REFERENCES users(user_id),
    FOREIGN KEY (user2_id) REFERENCES users(user_id)
);