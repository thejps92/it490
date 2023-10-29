import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import linear_kernel

# Sample movie dataset (you'd typically use a larger dataset)
data = {
    'Title': ['Movie 1', 'Movie 2', 'Movie 3', 'Movie 4'],
    'Genre': ['Action', 'Comedy', 'Action', 'Drama'],
    'Director': ['Director A', 'Director B', 'Director A', 'Director C']
}

df = pd.DataFrame(data)

# TF-IDF Vectorization for movie descriptions (in this case, we're using 'Genre' and 'Director')
tfidf_vectorizer = TfidfVectorizer(stop_words='english')
tfidf_matrix = tfidf_vectorizer.fit_transform(df['Genre'] + ' ' + df['Director'])

# Compute the cosine similarity between movies
cosine_sim = linear_kernel(tfidf_matrix, tfidf_matrix)

# Function to get movie recommendations
def get_recommendations(title, cosine_sim=cosine_sim):
    idx = df.index[df['Title'] == title].tolist()[0]
    sim_scores = list(enumerate(cosine_sim[idx]))
    sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)
    sim_scores = sim_scores[1:11]  # Top 10 recommendations (excluding the input movie itself)
    movie_indices = [i[0] for i in sim_scores]
    return df['Title'].iloc[movie_indices]

# Example usage
if __name__ == "__main__":
    movie_title = "Movie 1"
    recommendations = get_recommendations(movie_title)
    print(f"Recommended movies for '{movie_title}':")
    for movie in recommendations:
        print(movie)
