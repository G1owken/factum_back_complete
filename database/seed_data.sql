use books_store;

INSERT INTO genre (
    genre_id,
    genre,
    open_library_subject,
    is_active
) VALUES
    (1, 'Фэнтези', 'fantasy', 1),
    (2, 'Научная фантастика', 'science_fiction', 1),
    (3, 'Детектив', 'detective_and_mystery_stories', 1),
    (4, 'Ужасы', 'horror', 1),
    (5, 'Роман', 'romance', 1),
    (6, 'Классика', 'classics', 1),
    (7, 'Приключения', 'adventure', 1);

INSERT IGNORE INTO user (user_id, username, password_hash, email, is_set) VALUES
    (1, 'admin', '$2y$10$f1TwkBqbFCmMyd49MY9NHOLAPjfvO2cXCg2ou9zaciq.PCEmBe.wi', 'admin@example.com', 1);
