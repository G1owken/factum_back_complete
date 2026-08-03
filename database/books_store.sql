create database if not exists books_store
    character set utf8mb4
    collate utf8mb4_unicode_ci;

use books_store;

create table book (
    book_id int auto_increment,
    title varchar(200) not null,
    release_year smallint unsigned,
    cover_path varchar(255) not null,
    open_library_key varchar(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    description text not null,
    constraint pk_book_id primary key (book_id),
    constraint uq_open_library_key unique(open_library_key)
);

create table genre (
    genre_id int auto_increment,
    genre varchar(200) not null,
    open_library_subject varchar(200) not null,
    constraint pk_genre_id primary key (genre_id),
    constraint uq_genre unique (genre),
    constraint uq_open_library_subject unique (open_library_subject)
);

create table book_genre (
    genre_id int not null,
    book_id int not null,
    constraint pk_book_genre primary key (genre_id, book_id),
    constraint fk_book_genre_book_id foreign key (book_id)
        references book (book_id)
        on delete cascade
        on update cascade,
    constraint fk_book_genre_genre_id foreign key (genre_id)
        references genre (genre_id)
        on delete cascade
        on update cascade
);

create table author (
    author_id int auto_increment,
    author varchar(200) not null,
    constraint pk_author_id primary key (author_id),
    constraint uq_author unique (author)
);

create table book_author (
    book_id int not null,
    author_id int not null,
    constraint pk_book_author primary key (book_id, author_id),
    constraint fk_book_author_book_id foreign key (book_id)
        references book (book_id)
        on delete cascade
        on update cascade,
    constraint fk_book_author_author_id foreign key (author_id)
        references author (author_id)
        on delete cascade
        on update cascade
);

CREATE TABLE stock (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    amount INT UNSIGNED NOT NULL DEFAULT 0,

    CONSTRAINT uq_stock_book UNIQUE(book_id),

    FOREIGN KEY (book_id)
        REFERENCES book(book_id)
        ON DELETE CASCADE
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    book_id INT NOT NULL,

    firstname VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    fathername VARCHAR(100),

    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NOT NULL,

    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    address VARCHAR(255) NOT NULL,

    FOREIGN KEY(book_id)
    REFERENCES book(book_id)
    ON DELETE RESTRICT
);


