<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

header("Content-Type: application/json; charset=utf-8");

$pdo = getDbConnection();
$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Метод запрещён.'
    ]);
    exit;
}

$book_id = $_POST['book_id'] ?? null;

if (!$user_id) {
    http_response_code(401);

    echo json_encode([
        'status' => 'error',
        'message' => 'Необходимо авторизоваться.'
    ]);

    exit;
}

if (!$book_id) {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Книга не найдена.'
    ]);

    exit;
}

$stmt = $pdo->prepare("
    SELECT favourite_id
    FROM favourites
    WHERE user_id = :user_id
    AND book_id = :book_id
");

$stmt->execute([
    'user_id' => $user_id,
    'book_id' => $book_id
]);

$existingFavourite = $stmt->fetch();

if ($existingFavourite) {
    $stmt = $pdo->prepare("
        DELETE FROM favourites
        WHERE user_id = :user_id
        AND book_id = :book_id
    ");

    $stmt->execute([
        'user_id' => $user_id,
        'book_id' => $book_id
    ]);

    echo json_encode([
        'status' => 'removed'
    ]);
} else {
    $stmt = $pdo->prepare("
        INSERT INTO favourites (user_id, book_id)
        VALUES (:user_id, :book_id)
    ");

    $stmt->execute([
        'user_id' => $user_id,
        'book_id' => $book_id
    ]);

    echo json_encode([
        'status' => 'added'
    ]);
}