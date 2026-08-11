<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../function/error.php';
require_once __DIR__ . '/../function/sqlUpdate.php';
require_once __DIR__ . '/../function/validatePassword.php';
require_once __DIR__ . '/../function/uploader.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    errorResponse("Метод запрещён.", 405);
}
$new_user = trim($_POST['username'] ?? "");
$new_email = trim($_POST['email'] ?? "");
$old_pass = trim($_POST['old_password'] ?? "");
$new_pass = trim($_POST['password'] ?? "");
$confirm_new_pass = trim($_POST['confirm_password'] ?? "");

if (!empty($new_user)) {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $new_user)) {
        errorResponse("Логин может содержать только латинские буквы, цифры и подчеркивания.");
    } else {
        changeRecord($pdo, 'username', $new_user, $user_id );
    }
}


if (!empty($new_email)) {
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        errorResponse("Неверный формат почты.");
    } else {
        changeRecord($pdo, 'email', $new_email, $user_id);
    }
}


$stmt = $pdo->prepare ("select password_hash
from user
where user_id = ?");
$stmt->execute([$user_id]);
$stored_pass = $stmt->fetchColumn();

if (!empty($old_pass)) {
    if (!password_verify($old_pass, $stored_pass)) {
        errorResponse ('Неверно введен текущий пароль', 400);
    }
    if (empty($new_pass) || empty($confirm_new_pass)) {
        errorResponse('Введите новый пароль и подтверждение.', 400);
    }
    $passError = validatePassword($new_pass);
    if (!empty($passError)) {
        errorResponse(implode(" ", $passError));
    }
    if ($new_pass !== $confirm_new_pass) {
        errorResponse('Пароли не совпадают.');
    } 
    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
    changeRecord ($pdo, 'password_hash', $new_hash, $user_id);
}

$stmt = $pdo->prepare(
    'select username
    from user
    where user_id = ?'
);
$stmt->execute([$user_id]);
$userName = $stmt->fetchColumn();

if (!empty($_FILES['avatar']['name'])) {
    $uploader = new Uploader(__DIR__ . '/../uploads', $userName);
    $result = $uploader->upload($_FILES['avatar']);
    $photoPath = $result['original'];

    changeRecord($pdo, 'photo_path', $photoPath, $user_id);
}

echo json_encode([
    'success' => true,
    'message' => 'Данные успешно изменены.'
]);
exit;