CREATE TABLE bookmarks (
    bookmark_id INT AUTO_INCREMENT PRIMARY KEY,
    list_id INT,
    url VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    description TEXT,
    FOREIGN KEY (list_id) REFERENCES lists(list_id)
);
