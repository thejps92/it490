ysql.connector

# Connect to the MySQL database
conn = mysql.connector.connect(
    host=' 127.0.0.1',
    user='root',
    password='root',
    database='newdb'
)
cursor = conn.cursor()

# Add a bookmark
list_id = 1  
url = "https://www.sample.com"
title = "Example Website"
description = "A sample website"
cursor.execute(
    "INSERT INTO bookmarks (list_id, url, title, description) VALUES (%s, %s, %s, %s)",
    (list_id, url, title, description)
)

# Edit a bookmark
bookmark_id = 1  
new_url = "https://www.sample.com"
new_title = "New Example Website"
new_description = "A new sample website"
cursor.execute(
    "UPDATE bookmarks SET url = %s, title = %s, description = %s WHERE bookmark_id = %s",
    (new_url, new_title, new_description, bookmark_id)
)

# Delete a bookmark
cursor.execute("DELETE FROM bookmarks WHERE bookmark_id = %s", (bookmark_id,))

# Commit the changes and close the connection
conn.commit()
conn.close()
