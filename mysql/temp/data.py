import csv
import mysql.connector

# Replace with your MySQL database connection details
host = "justis-VirtualBox"
user = "justis"
password = "it490vmm"
database = "Project"
csv_file = "query.csv"

try:
    # Establish a connection to the MySQL database
    connection = mysql.connector.connect(
        host=host,
        user=user,
        password=password,
        database=database
    )

    cursor = connection.cursor()

    # Read the CSV file
    with open(csv_file, 'r') as csv_file:
        csv_reader = csv.DictReader(csv_file)

        for row in csv_reader:
            # Customize the SQL query based on your table structure
            sql = "INSERT INTO your_table (column1, column2, column3) VALUES (%s, %s, %s)"
            values = (row["column1"], row["column2"], row["column3"])

            cursor.execute(sql, values)

    # Commit the changes to the database
    connection.commit()
    print("Data imported into the database successfully!")

except mysql.connector.Error as err:
    print(f"Error: {err}")

finally:
    if connection.is_connected():
        cursor.close()
        connection.close()

