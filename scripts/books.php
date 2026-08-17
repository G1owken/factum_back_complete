<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDbConnection();

$userId = $_SESSION['user_id'] ?? null;

$genre = $_GET["genre"] ?? "";
$title = $_GET["title"] ?? "";
$author = $_GET["author"] ?? "";
$sortField = $_GET["sortField"] ?? "title";
$sortDirection = $_GET["sortDirection"] ?? "ASC";
$page = max(1, (int)($_GET["page"] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$fields = [
    "title" => "b.title",
    "release_year" => "b.release_year"
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

    CASE
        WHEN EXISTS (
            SELECT 1
            FROM favourites f
            WHERE f.book_id = b.book_id
            AND f.user_id = ?
        )
        THEN 1
        ELSE 0
    END AS is_favourite,

    GROUP_CONCAT(
        DISTINCT g.genre
        ORDER BY g.genre
        SEPARATOR ', '
    ) AS genres,

    GROUP_CONCAT(
        DISTINCT au.author
        ORDER BY au.author
        SEPARATOR ', '
    ) AS authors

FROM book b

LEFT JOIN book_genre bg
    ON b.book_id = bg.book_id

LEFT JOIN genre g
    ON bg.genre_id = g.genre_id

LEFT JOIN book_author ba
    ON b.book_id = ba.book_id

LEFT JOIN author au
    ON ba.author_id = au.author_id

LEFT JOIN stock s
    ON b.book_id = s.book_id
";

$where = ["s.amount > 0"];
$params = [$userId];

if ($genre !== "") {
    $where[] = "
    EXISTS (
        SELECT 1
        FROM book_genre x
        WHERE x.book_id = b.book_id
        AND x.genre_id = ?
    )";

    $params[] = $genre;
}

if ($author !== "") {
    $where[] = "
    EXISTS (
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

$countSql = "
    SELECT COUNT(DISTINCT b.book_id)
    FROM book b
    LEFT JOIN stock s
        ON b.book_id = s.book_id
";

if ($where) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}

$countParams = array_slice($params, 1);

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);

$totalBooks = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalBooks / $perPage));

$sql .= " 
GROUP BY 
    b.book_id 
ORDER BY 
    {$fields[$sortField]} $sortDirection 
LIMIT $perPage OFFSET $offset 
"; 
 
$stmt = $pdo->prepare($sql); 
$stmt->execute($params); 
 
$books = $stmt->fetchAll();

header("Content-Type: application/json; charset=utf-8"); 
 
echo json_encode([
    "books" => $books,
    "totalPages" => $totalPages,
    "currentPage" => $page
], JSON_UNESCAPED_UNICODE);