CREATE TABLE Friends (
  user_id int NOT NULL,
  Friend_id int NOT NULL,
  FOREIGN KEY (user_id) REFERENCES Users(user_id),
  FOREIGN KEY (friend_id) REFERENCES Users(user_id)
);
