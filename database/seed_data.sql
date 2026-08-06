use books_store;

INSERT INTO genre (genre_id, genre, open_library_subject) VALUES
    (1, 'Фэнтези', 'fantasy'),
    (2, 'Научная фантастика', 'science_fiction'),
    (3, 'Детектив', 'detective_and_mystery_stories'),
    (4, 'Ужасы', 'horror'),
    (5, 'Роман', 'romance'),
    (6, 'Классика', 'classics'),
    (7, 'Приключения', 'adventure');

INSERT IGNORE INTO user (user_id, username, password_hash, email) VALUES
    (1, 'admin', '$2y$10$f1TwkBqbFCmMyd49MY9NHOLAPjfvO2cXCg2ou9zaciq.PCEmBe.wi', 'admin@example.com');
