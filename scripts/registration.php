<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/error.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    errorResponse(
        "Метод запрещён.",
        405
    );

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
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

if (!$user || !$password || !$confirmPassword || !$email) {
    errorResponse("Все поля обязательны для заполнения.");
}

$sql = "
insert into user (username, password_hash, email)
values (?, ?, ?)"
;

$stmt = $pdo->prepare($sql);
try {
    $stmt->execute([$user, $passwordHash, $email]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        errorResponse("Пользователь с таким именем или email уже существует.");
    } else {
        errorResponse("Ошибка базы данных: " . $e->getMessage(), 500);
    }
}

