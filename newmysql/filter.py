import mysql.connector

# Database connection configuration
db_config = {
    "host": "localhost",
    "user": "jac227",
    "password": "jac227",
    "database": "newdb",
}

# Create a connection to the MySQL database
connection = mysql.connector.connect(**db_config)

try:
    # Create a cursor
    cursor = connection.cursor()

    # SQL query to delete duplicate entries based on "title" and "director"
    delete_query = """
    DELETE m1 FROM movies m1
    INNER JOIN movies m2
    WHERE m1.title = m2.title
    AND m1.director = m2.director
    AND m1.id > m2.id
    """

    # Execute the delete query
    cursor.execute(delete_query)

    # Commit the changes to the database
    connection.commit()

finally:
    # Close the cursor and connection
    cursor.close()
    connection.close()
