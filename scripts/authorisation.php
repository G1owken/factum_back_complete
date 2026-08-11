<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/error.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    errorResponse("Метод запрещён.", 405);
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    errorResponse("Логин и пароль обязательны.");
}

$pdo = getDbConnection();

$stmt = $pdo->prepare("
    SELECT user_id, password_hash
    FROM user
    WHERE username = ?
    LIMIT 1
");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($_SESSION['user_id'] === 1) {
    echo json_encode([
        'success' => true,
        'redirect' => 'pages/admin.php'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'redirect' => 'pages/catalogue.php'
    ]);
}

exit;



