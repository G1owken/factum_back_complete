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
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);


    if ($response === false) {
        curl_close($ch);
        return "";
    }


    curl_close($ch);


    $result = json_decode($response, true);


    return trim($result["response"] ?? "");
}



$genreId = $_POST["genre"] ?? "";

$limit = (int)($_POST["limit"] ?? 10);

$offset = (int)($_POST["offset"] ?? 0);



$limit = max(1, min($limit, 50));

$offset = max(0, $offset);



if ($genreId === "") {

    echo json_encode([
        "success" => false,
        "message" => "Жанр не выбран."
    ]);

    exit;
}



$stmt = $pdo->prepare("
    SELECT open_library_subject
    FROM genre
    WHERE genre_id = ?
");


$stmt->execute([$genreId]);


$subject = $stmt->fetchColumn();



if (!$subject) {

    echo json_encode([
        "success" => false,
        "message" => "Жанр не найден."
    ]);

    exit;
}



$url = "https://openlibrary.org/subjects/{$subject}.json?limit={$limit}&offset={$offset}";



$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$json = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);



if ($json === false || $httpCode !== 200) {

    echo json_encode([
        "success" => false,
        "message" => "Ошибка загрузки Open Library."
    ]);

    exit;
}



$data = json_decode($json, true);



if (!isset($data["works"])) {

    echo json_encode([
        "success" => false,
        "message" => "Некорректный ответ Open Library."
    ]);

    exit;
}



$imported = 0;



try {


    $pdo->beginTransaction();



    foreach ($data["works"] as $work) {


        $title = $work["title"] ?? "Без названия";

        $releaseYear = $work["first_publish_year"] ?? null;

        $openLibraryKey = $work["key"] ?? "";



        if ($openLibraryKey === "") {
            continue;
        }



        $coverPath = "";



        if (!empty($work["cover_id"])) {

            $coverPath = "https://covers.openlibrary.org/b/id/".$work["cover_id"]."-L.jpg";

        }



        $authorName = "Неизвестный";



        if (!empty($work["authors"][0]["name"])) {

            $authorName = trim($work["authors"][0]["name"]);

        }



        $stmt = $pdo->prepare("
            SELECT author_id
            FROM author
            WHERE author = ?
        ");


        $stmt->execute([$authorName]);


        $authorId = $stmt->fetchColumn();



        if (!$authorId) {


            $stmt = $pdo->prepare("
                INSERT INTO author(author)
                VALUES(?)
            ");


            $stmt->execute([$authorName]);


            $authorId = $pdo->lastInsertId();

        }




        $stmt = $pdo->prepare("
            SELECT book_id
            FROM book
            WHERE open_library_key = ?
        ");


        $stmt->execute([$openLibraryKey]);


        $bookId = $stmt->fetchColumn();




        if (!$bookId) {



            $description = generateDescription(
                $title,
                $authorName
            );



            if ($description === "") {

                $description = "Описание отсутствует.";

            }



            $price = random_int(5000, 20000);



            $stmt = $pdo->prepare("
                INSERT INTO book
                (
                    title,
                    release_year,
                    cover_path,
                    open_library_key,
                    price,
                    description
                )
                VALUES
                (?, ?, ?, ?, ?, ?)
            ");



            $stmt->execute([
                $title,
                $releaseYear,
                $coverPath,
                $openLibraryKey,
                $price,
                $description
            ]);



            $bookId = $pdo->lastInsertId();



            $amount = random_int(1, 50);



            $stmt = $pdo->prepare("
                INSERT INTO stock
                (
                    book_id,
                    amount
                )
                VALUES
                (?, ?)
            ");



            $stmt->execute([
                $bookId,
                $amount
            ]);



            $imported++;


        }



        $stmt = $pdo->prepare("
            INSERT IGNORE INTO book_author
            (
                book_id,
                author_id
            )
            VALUES
            (?,?)
        ");



        $stmt->execute([
            $bookId,
            $authorId
        ]);





        $stmt = $pdo->prepare("
            INSERT IGNORE INTO book_genre
            (
                genre_id,
                book_id
            )
            VALUES
            (?,?)
        ");



        $stmt->execute([
            $genreId,
            $bookId
        ]);



    }



    $pdo->commit();



    echo json_encode([
        "success" => true,
        "message" => "Импортировано новых книг: {$imported}"
    ]);



} catch (Exception $e) {


    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }



    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}