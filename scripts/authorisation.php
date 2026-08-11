<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/error.php';
session_start();

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    errorResponse("Метод запрещён.", 405);
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    errorResponse("Логин и пароль обязательны.", 400);
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

if (!$user) {
    errorResponse("Неверный логин или пароль.", 401);
}

if (!password_verify($password, $user['password_hash'])) {
    errorResponse("Неверный логин или пароль.", 401);
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['user_id'];

if ($_SESSION['user_id'] === 1) {
    echo json_encode([
        'success' => true,
        'message' => 'Авторизация успешна.',
        'redirect' => '../pages/admin.php'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Авторизация успешна.',
    'redirect' => '../pages/catalogue.php'
]);

exit;
