<?php

require_once __DIR__ . '/../config/db.php';

header("Content-Type: application/json; charset=utf-8");

$pdo = getDbConnection();

function generateDescription($title, $author)
{
    $data = [
        "model" => "llama3.2",
        "prompt" => "Напиши короткое описание книги {$title} автора {$author}. Ответ только описание без кавычек.",
        "stream" => false
    ];

    $ch = curl_init("http://localhost:11434/api/generate");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 180);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        die("Ollama cURL #{$errno}: {$error}");
    }

    if ($httpCode !== 200) {
        die("Ollama HTTP {$httpCode}: {$response}");
    }

    $result = json_decode($response, true);

    return trim($result["response"] ?? "");
}

$genreId = (int)($_POST["genre"] ?? 0);
$limit = max(1, min((int)($_POST["limit"] ?? 10), 50));
$offset = max(0, (int)($_POST["offset"] ?? 0));

if ($genreId === 0) {
    echo json_encode(["success"=>false,"message"=>"Жанр не выбран."]);
    exit;
}

$stmt = $pdo->prepare("SELECT open_library_subject FROM genre WHERE genre_id = ?");
$stmt->execute([$genreId]);
$subject = $stmt->fetchColumn();

if (!$subject) {
    echo json_encode(["success"=>false,"message"=>"Жанр не найден."]);
    exit;
}

$url = "https://openlibrary.org/subjects/{$subject}.json?limit={$limit}&offset={$offset}";