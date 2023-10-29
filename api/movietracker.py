import requests

# OMDB API URL
api_url = "http://www.omdbapi.com/"
api_key = "8d92d6cb"

# Function to search for a movie by title
def search_movie(title):
    params = {
        "apikey": api_key,
        "t": title
    }
    response = requests.get(api_url, params=params)
    data = response.json()

    if data["Response"] == "True":
        return data
    else:
        return None

# Function to track a movie
def track_movie(username, title):
    movie_data = search_movie(title)

    if movie_data:
        # Here, you can implement the logic to save the movie in a user's watched list.
        # This might involve database operations or saving to a user's account, depending on your application's architecture.
        print(f"Movie '{movie_data['Title']}' added to {username}'s watched list.")
    else:
        print("Movie not found or an error occurred.")

# Example usage
if __name__ == "__main__":
    username = "Justis"
    movie_title = "Inception"
    track_movie(username, movie_title)
