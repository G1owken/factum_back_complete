<?php

require_once __DIR__ . './config/db.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();

$user_info = $_POST;

