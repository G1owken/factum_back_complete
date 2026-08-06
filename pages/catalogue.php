<?php

require_once __DIR__ . '/../config/db.php';

$pdo = getDbConnection();

$stmt = $pdo->query("
    SELECT genre_id, genre
    FROM genre
    ORDER BY genre
");
$genres = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT author_id, author
    FROM author
    ORDER BY author
");
$authors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books store</title>
</head>

<body>
    <h1>Каталог книг</h1>

    <form id="filterForm">
        <label for="genre">Жанр:</label>
        <select id="genre" name="genre">
            <option value="">Все</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= $g["genre_id"] ?>"><?= htmlspecialchars($g["genre"], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>

        <label for="author">Автор:</label>
        <select id="author" name="author">
            <option value="">Все</option>
            <?php foreach ($authors as $a): ?>
                <option value="<?= $a["author_id"] ?>"><?= htmlspecialchars($a["author"], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>

        <label for="sortField">Сортировка:</label>
        <select id="sortField" name="sortField">
            <option value="title">Название</option>
            <option value="release_year">Год выпуска</option>
        </select>

        <select id="sortDirection" name="sortDirection">
            <option value="ASC">Возрастание</option>
            <option value="DESC">Убывание</option>
        </select>

        <label for="title">Поиск:</label>
        <input id="title" type="search" name="title" placeholder="Название книги">

        <button type="submit">Найти</button>
    </form>

    <hr>

    <div id="importSection" hidden>
        <label for="limit">Количество книг:</label>
        <input id="limit" type="number" min="1" max="50" value="10">
        <button id="import" type="button">Импортировать</button>
    </div>

    <hr>

    <div id="books"></div>

    <script src="../scripts/script.js"></script>
</body>

</html>
