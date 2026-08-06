<?php

require_once __DIR__ . '/../config/db.php';

$pdo = getDbConnection();

$genre = $_GET["genre"] ?? "";
$title = $_GET["title"] ?? "";
$author = $_GET["author"] ?? "";
$sortField = $_GET["sortField"] ?? "title";
$sortDirection = $_GET["sortDirection"] ?? "ASC";

$fields = [
    "title" => "b.title",
    "release_year" => "b.release_year",
];

if (!isset($fields[$sortField])) {
    $sortField = "title";
}

if (!in_array($sortDirection, ["ASC", "DESC"], true)) {
    $sortDirection = "ASC";
}

$sql = "
SELECT
    b.book_id,
    b.title,
    b.release_year,
    b.cover_path,
    b.price,
    COALESCE(SUM(s.amount), 0) AS stock,
    GROUP_CONCAT(DISTINCT g.genre ORDER BY g.genre SEPARATOR ', ') AS genres,
    GROUP_CONCAT(DISTINCT au.author ORDER BY au.author SEPARATOR ', ') AS authors
FROM book b
LEFT JOIN book_genre bg ON b.book_id = bg.book_id
LEFT JOIN genre g ON bg.genre_id = g.genre_id
LEFT JOIN book_author ba ON b.book_id = ba.book_id
LEFT JOIN author au ON ba.author_id = au.author_id
LEFT JOIN stock s ON b.book_id = s.book_id
";

$where = [];
$params = [];

if ($genre !== "") {
    $where[] = "
    EXISTS(
        SELECT 1
        FROM book_genre x
        WHERE x.book_id = b.book_id
          AND x.genre_id = ?
    )";
    $params[] = $genre;
}

if ($author !== "") {
    $where[] = "
    EXISTS(
        SELECT 1
        FROM book_author x
        WHERE x.book_id = b.book_id
          AND x.author_id = ?
    )";
    $params[] = $author;
}

if ($title !== "") {
    $where[] = "b.title LIKE ?";
    $params[] = "%" . $title . "%";
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= "
GROUP BY
    b.book_id
ORDER BY
    {$fields[$sortField]} $sortDirection
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header("Content-Type: application/json; charset=utf-8");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
