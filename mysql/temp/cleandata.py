import pandas as pd

# Read the CSV file
df = pd.read_csv('updatedquery.csv')

# Identify and remove duplicates based on specific columns (e.g., title and director)
df_cleaned = df.drop_duplicates(subset=['itemLabel', 'directorLabel'])

# Write the cleaned data to a new CSV file
df_cleaned.to_csv('cleaned_data.csv', index=False)

