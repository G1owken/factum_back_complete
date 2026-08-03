# Books Store PHP Project

A simple PHP-based bookstore catalog with book browsing, detail pages, order placement, and Open Library import functionality. The project uses a MySQL database, PHP scripts for the backend, and vanilla JavaScript for the frontend interactions.

## Features

- Browse books by genre, author, title, and sort order
- View detailed information about a selected book
- Place customer orders with validation
- Reduce stock after an order is successfully created
- Send a confirmation email to the customer
- Save order details to a local text log in the orders folder
- Import books from Open Library by genre
- Generate short book descriptions using an Ollama local model

## Tech Stack

- PHP
- MySQL / MariaDB
- Composer
- Vanilla JavaScript
- PHPMailer
- vlucas/phpdotenv
- Open Library API
- Ollama for AI-generated descriptions

## Project Structure

- index.php - main catalog page
- book.php - book detail page and order form
- config/ - database and environment configuration
- scripts/ - backend handlers for books, import, and orders
- database/ - SQL schema and seed data
- orders/ - order log output
- vendor/ - Composer dependencies

## Requirements

Before running the project, make sure you have:

- PHP 8+
- Composer
- MySQL or MariaDB
- An Ollama server running locally on http://localhost:11434
- The model llama3.2 available in Ollama

## Setup

1. Clone or open the project folder.
2. Install PHP dependencies:

```bash
composer install
```

3. Create the database and import the schema:

```bash
mysql -u root -p < database/books_store.sql
mysql -u root -p < database/seed_data.sql
```

4. Update the environment variables in .env if needed:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=books_store
DB_USER=root
DB_PASS=your_password
DB_CHARSET=utf8mb4
```

5. Make sure Ollama is installed and running, and that the llama3.2 model is available.

## Running the Application

Start a local PHP server from the project root:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000/index.php
```

## How It Works

### Catalog Page
The main page loads books from the database and lets users filter and sort the catalog.

### Book Detail Page
Each book has a detail page with:
- title
- author(s)
- genre(s)
- release year
- price
- stock status
- description
- order form

### Order Handling
When a customer submits an order:
- the order is saved in the database
- one unit of stock is decremented
- an email is attempted to be sent
- a copy of the order is written to orders/order.txt

### Importing Books
The import feature uses the selected genre and calls the Open Library API to fetch books. For each imported book, the app:
- creates or reuses the author
- inserts the book into the database
- stores genre relations
- generates a short description using Ollama

## Notes

- The import feature requires a working Ollama installation.
- If email sending fails, the order is still logged locally in the orders folder.
- The user interface is currently in Russian.

## License

This project is for educational/demo purposes.
