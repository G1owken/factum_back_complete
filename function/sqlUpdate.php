<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/error.php';

function changeRecord(PDO $pdo, string $field, string $data, int $user_id): void
{
    $allowedFields = [
        'username',
        'email',
        'password_hash',
        'photo_path'
    ];

    if (!in_array($field, $allowedFields, true)) {
        errorResponse('Недопустимое поле', 400);
    }

    $stmt = $pdo->prepare(
        "UPDATE user SET `$field` = ? WHERE user_id = ?"
    );

    $stmt->execute([$data, $user_id]);
}