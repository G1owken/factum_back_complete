create database if not exists books_store
    character set utf8mb4
    collate utf8mb4_unicode_ci;

use books_store;

create table book (
    book_id int auto_increment,
    title varchar(200) not null,
    release_year smallint unsigned,
    cover_path varchar(255) not null,
    open_library_key varchar(100) not null,
    price decimal(10,2) not null default 0,
    description text not null,
    constraint pk_book_id primary key (book_id),
    constraint uq_open_library_key unique (open_library_key)
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

create table stock (
    stock_id int auto_increment,
    book_id int not null,
    amount int unsigned not null default 0,
    constraint pk_stock_id primary key (stock_id),
    constraint uq_stock_book unique (book_id),
    constraint fk_stock_book_id foreign key (book_id)
        references book (book_id)
        on delete cascade
        on update cascade
);

create table user (
    user_id int auto_increment,
    username varchar(100) not null,
    password_hash varchar(255) not null,
    email varchar(150) not null,
    photo_path varchar(255),
    created_at datetime not null default current_timestamp,
    constraint pk_user_id primary key (user_id),
    constraint uq_username unique (username),
    constraint uq_user_email unique (email),
    constraint uk_photo_path unique (photo_path)
);

create table orders (
    order_id int auto_increment,
    order_date datetime not null default current_timestamp,
    user_id int not null,
    book_id int not null,

    firstname varchar(100) not null,
    surname varchar(100) not null,
    fathername varchar(100),
    email varchar(150) not null,
    phone varchar(30) not null,

    city varchar(100) not null,
    postal_code varchar(10) not null,
    address varchar(255) not null,

    constraint pk_order_id primary key (order_id),

    constraint fk_order_user_id foreign key (user_id)
        references user (user_id)
        on delete restrict
        on update cascade,

    constraint fk_order_book_id foreign key (book_id)
        references book (book_id)
        on delete restrict
        on update cascade
);

create table favourites (
    favourite_id int auto_increment,
    user_id int not null,
    book_id int not null,
    added_at datetime not null default current_timestamp,

    constraint pk_favourite_id
        primary key (favourite_id),

    constraint uq_favourite_user_book
        unique (user_id, book_id),

    constraint fk_favourite_user_id
        foreign key (user_id)
        references user (user_id)
        on delete cascade
        on update cascade,

    constraint fk_favourite_book_id
        foreign key (book_id)
        references book (book_id)
        on delete cascade
        on update cascade
);