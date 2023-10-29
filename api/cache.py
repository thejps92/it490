import sqlite3
import requests
from datetime import datetime, timedelta

# Function to fetch data from the API
def fetch_data_from_api():
    api_url = 'http://www.omdbapi.com/?apikey=your_api_key&s=search_term'
    response = requests.get(api_url)
    data = response.json()
    return data

# Function to retrieve data from the cache or fetch from the API
def get_data():
    conn = sqlite3.connect('cache.db')
    cursor = conn.cursor()

    # Check if data exists in the cache and if it's not expired
    cursor.execute("SELECT data, timestamp FROM cache WHERE key = ?", ('your_cache_key',))
    result = cursor.fetchone()
    
    if result:
        data, timestamp = result
        expiration_time = timestamp + timedelta(days=1)  # Example: Cache data for 1 day
        if datetime.now() < expiration_time:
            conn.close()
            return data

    # If data doesn't exist in the cache or is expired, fetch from the API
    data = fetch_data_from_api()

    # Update the cache with the new data
    cursor.execute("REPLACE INTO cache (key, data, timestamp) VALUES (?, ?, ?)", ('your_cache_key', data, datetime.now()))
    conn.commit()
    conn.close()

    return data

# Use the get_data() function to retrieve data
data = get_data()
print(data)
