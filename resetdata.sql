SET foreign_key_checks = 0;
TRUNCATE Link;
TRUNCATE Image;
INSERT INTO Link (url, title, visited, relevance) VALUES ('https://www.locationscout.net/', 'Locationscout - Discover the best places for photography', 0, 1.0);
SET foreign_key_checks = 1;