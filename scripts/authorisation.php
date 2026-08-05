<?php

require_once __DIR__ . './config/db.php';

header("Content-Type: application/json; charset=utf-8");


$pdo = getDbConnection();

$user_id = $_POST["user_id"] ?? null;

$password = $_POST["password"] ?? null;