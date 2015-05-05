ALTER TABLE article ADD INDEX idx_author (author(2));
ALTER TABLE list ADD INDEX idx_author (author(2));
ALTER TABLE comment ADD INDEX idx_author (author(2));