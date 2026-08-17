# Books Store PHP Project

A PHP-based bookstore with catalog browsing, favourites, profile management, admin controls, and order flow.

## Features

- User registration and login
- Book catalogue with filters and sorting
- Book detail view
- Favourites management
- User profile and avatar upload
- Admin panel for managing:
  - genres
  - users
  - books
  - enable/disable toggles
  - user deletion with related avatar cleanup
- Order processing and local order logging
- Open Library import support

## Stack

- PHP 8+
- MySQL / MariaDB
- Composer
- Vanilla JavaScript
- PHPMailer
- vlucas/phpdotenv
- Intervention Image for avatar resizing

## Project structure

- index.php — entry point
- pages/ — catalog, profile, favourites, admin pages
- scripts/ — backend handlers for auth, books, profile updates, import, logout
- config/ — DB and session configuration
- function/ — validation, uploads, error helpers, SQL update helpers
- database/ — SQL schema and seed data
- uploads/ — generated images and avatars
- orders/ — order log output
- vendor/ — Composer dependencies

## Requirements

- PHP 8+
- Composer
- MySQL or MariaDB
- Working database credentials in config/env.php or .env

## Setup

1. Install dependencies:

```bash
composer install
```

2. Import the database schema and seed data:

```bash
mysql -u root -p < database/books_store.sql
mysql -u root -p < database/seed_data.sql
```

3. Configure environment variables in the project config or .env file.

4. Start the project locally:

```bash
php -S localhost:8000
```

5. Open in browser:

```text
http://localhost:8000/index.php
```

## Admin access

The first user in the system is treated as the administrator and is redirected to the admin page.
Password for precreated admin in seed data is: admin_user1&

## Notes

- User deletion removes linked favourites and orders, and deletes avatar-related files from uploads.
- The admin page is intentionally simple and script-based for quick management tasks.

## License

Educational/demo project.
