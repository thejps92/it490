LOAD DATA LOCAL INFILE 'updatedquery.csv'
INTO TABLE movies
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;  -- Skip the header row if your CSV has one

