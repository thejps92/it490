CREATE TABLE users (
    user_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    fav_genre varchar(255) NOT NULL
);