<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/error.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    errorResponse("Метод запрещён.", 405);
}

$user = trim($_POST['username'] ?? "");
$password = trim($_POST['password'] ?? "");
$confirmPassword = trim($_POST['confirm_password'] ?? "");
$email = trim($_POST['email'] ?? "");

function validatePassword(string $password): array
{
    $errors = [];

    if (mb_strlen($password) < 8) {
        $errors[] = "Пароль должен содержать минимум 8 символов.";
    }

    if (!preg_match('/[A-ZА-Я]/u', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну заглавную букву.";
    }

    if (!preg_match('/[a-zа-я]/u', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну строчную букву.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну цифру.";
    }

    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы один спецсимвол (!@#$%^&* и т.д.).";
    }

    return $errors;
}

$validationErrors = validatePassword($password);
if (!empty($validationErrors)) {
    errorResponse(implode(" ", $validationErrors));
}

if ($password !== $confirmPassword) {
    errorResponse("Пароли не совпадают.");
}

if (!$user || !$password || !$confirmPassword || !$email) {
    errorResponse("Все поля обязательны для заполнения.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse("Некорректный формат email.");
}

if (!preg_match('/^[A-Za-z0-9_]+$/', $user)) {
    errorResponse("Логин может содержать только латинские буквы, цифры и подчеркивания.");
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO user (username, password_hash, email) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$user, $passwordHash, $email]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        errorResponse("Пользователь с таким именем или email уже существует.");
    }
    errorResponse("Ошибка базы данных: " . $e->getMessage(), 500);
}

http_response_code(201);
echo json_encode([
    "success" => true,
    "message" => "Регистрация прошла успешно."
]);
