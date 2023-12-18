CREATE TABLE friend_requests (
    sender_id INT,
    receiver_id INT,
    PRIMARY KEY (sender_id, receiver_id),
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);