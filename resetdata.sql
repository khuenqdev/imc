SET foreign_key_checks = 0;
TRUNCATE link;
TRUNCATE page;
TRUNCATE text;
TRUNCATE image;
TRUNCATE keyword;
INSERT INTO link (url, title, visited, relevance) VALUES ('https://www.locationscout.net/', 'Locationscout - Discover the best places for photography', 0, 1.0);
SET foreign_key_checks = 1;