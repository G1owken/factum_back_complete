<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/error.php';

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