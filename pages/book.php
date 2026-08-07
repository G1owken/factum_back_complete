<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
$pdo = getDbConnection();

$id = $_GET["id"] ?? 0;
if (!filter_var($id, FILTER_VALIDATE_INT)) {
    die("Некорректная книга.");
}

$sql = "
SELECT
    b.book_id,
    b.title,
    b.release_year,
    b.cover_path,
    b.price,
    b.description,
    GROUP_CONCAT(
        DISTINCT g.genre
        ORDER BY g.genre
        SEPARATOR ', '
    ) AS genres,
    GROUP_CONCAT(
        DISTINCT a.author
        ORDER BY a.author
        SEPARATOR ', '
    ) AS authors,
    COALESCE(SUM(s.amount), 0) AS stock
FROM book b
LEFT JOIN book_genre bg ON b.book_id = bg.book_id
LEFT JOIN genre g ON bg.genre_id = g.genre_id
LEFT JOIN book_author ba ON b.book_id = ba.book_id
LEFT JOIN author a ON ba.author_id = a.author_id
LEFT JOIN stock s ON b.book_id = s.book_id
WHERE b.book_id = ?
GROUP BY b.book_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    die("Книга не найдена.");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book["title"]) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.css">
</head>
<body>
    <h1><?= htmlspecialchars($book["title"]) ?></h1>
    <?php if ($book["cover_path"]): ?>
        <img src="<?= htmlspecialchars($book["cover_path"]) ?>" alt="<?= htmlspecialchars($book["title"]) ?>" width="200">
    <?php endif; ?>
    <p>Автор: <?= htmlspecialchars($book["authors"] ?? "Нет") ?></p>
    <p>Жанр: <?= htmlspecialchars($book["genres"] ?? "Нет") ?></p>
    <p>Год выпуска: <?= htmlspecialchars($book["release_year"] ?? "Не указан") ?></p>
    <p>Цена: <?= htmlspecialchars($book["price"]) ?> тенге</p>
    <p>В наличии: <?= $book["stock"] > 0 ? $book["stock"]." шт." : "Нет" ?></p>
    <p><?= nl2br(htmlspecialchars($book["description"])) ?></p>
    <hr>
    <?php if ($book["stock"] > 0): ?>
        <a data-fancybox data-src="#hidden-form" href="#hidden-form">Сделать заказ</a>
        <div style="display:none" id="hidden-form">
            <form id="orderForm">
                <h2>Сделайте заказ</h2>
                <input type="hidden" name="book_id" value="<?= $id ?>">
                <p>Имя</p>
                <input type="text" name="firstname" required>
                <p>Фамилия</p>
                <input type="text" name="surname" required>
                <p>Отчество</p>
                <input type="text" name="fathername">
                <p>Телефон</p>
                <input type="tel" id="phone" name="phone" placeholder="+7 (___) ___-__-__" required>
                <p>Город</p>
                <input type="text" name="city" required>
                <p>Почтовый индекс</p>
                <input type="text" name="postal_code" pattern="[0-9]{6}" maxlength="6" required>
                <p>Адрес</p>
                <input type="text" name="address" required>
                <br><br>
                <button type="submit">Заказать</button>
            </form>
        </div>
    <?php else: ?>
        <p>Книга отсутствует на складе.</p>
    <?php endif; ?>
    <br><br>
    <a href="catalogue.php">Назад</a>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.umd.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="../scripts/script.js"></script>
    <script> Fancybox.bind("[data-fancybox]", {}); </script>
</body>
</html>