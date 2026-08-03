<?php

require_once __DIR__ . '/../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json; charset=utf-8");


function errorResponse($message, $status = 400)
{
    http_response_code($status);

    echo json_encode([
        "success" => false,
        "message" => $message
    ]);

    exit;
}



if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    errorResponse(
        "Метод запрещён.",
        405
    );

}



$bookId = $_POST["book_id"] ?? null;


$firstName = trim($_POST["firstname"] ?? "");
$surname = trim($_POST["surname"] ?? "");
$fatherName = trim($_POST["fathername"] ?? "");


$phone = trim($_POST["phone"] ?? "");
$email = trim($_POST["email"] ?? "");


$city = trim($_POST["city"] ?? "");
$postalCode = trim($_POST["postal_code"] ?? "");
$address = trim($_POST["address"] ?? "");




if (
    !$bookId ||
    !$firstName ||
    !$surname ||
    !$phone ||
    !$email ||
    !$city ||
    !$postalCode ||
    !$address
) {

    errorResponse(
        "Заполните все поля."
    );

}



if (!filter_var($bookId, FILTER_VALIDATE_INT)) {

    errorResponse(
        "Некорректная книга."
    );

}



if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    errorResponse(
        "Некорректный email."
    );

}



if (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {

    errorResponse(
        "Некорректный телефон."
    );

}



$pdo = getDbConnection();




$stmt = $pdo->prepare("
    SELECT title, price
    FROM book
    WHERE book_id = ?
");


$stmt->execute([
    $bookId
]);


$book = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$book) {

    errorResponse(
        "Книга не найдена.",
        404
    );

}





try {


    $pdo->beginTransaction();




    $stmt = $pdo->prepare("
        SELECT stock_id
        FROM stock
        WHERE book_id = ?
        AND amount > 0
        LIMIT 1
        FOR UPDATE
    ");



    $stmt->execute([
        $bookId
    ]);



    $stock = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!$stock) {


        $pdo->rollBack();


        errorResponse(
            "Книги нет в наличии.",
            404
        );


    }




    $stmt = $pdo->prepare("
        INSERT INTO orders
        (
            book_id,
            firstname,
            surname,
            fathername,
            phone,
            email,
            city,
            postal_code,
            address
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");




    $stmt->execute([

        $bookId,
        $firstName,
        $surname,
        $fatherName,
        $phone,
        $email,
        $city,
        $postalCode,
        $address

    ]);




    $orderId = $pdo->lastInsertId();





    $stmt = $pdo->prepare("
        UPDATE stock
        SET amount = amount - 1
        WHERE stock_id = ?
        AND amount > 0
    ");




    $stmt->execute([

        $stock["stock_id"]

    ]);





    $pdo->commit();



} catch (Exception $e) {


    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    errorResponse(
        "Ошибка оформления заказа.",
        500
    );

}






$mail = new PHPMailer(true);



try {


    $mail->CharSet = "UTF-8";


    $mail->isSMTP();


    $mail->Host = $_ENV["MAIL_HOST"];

    $mail->SMTPAuth = true;


    $mail->Username = $_ENV["MAIL_USERNAME"];

    $mail->Password = $_ENV["MAIL_PASSWORD"];


    $mail->SMTPSecure = $_ENV["MAIL_ENCRYPTION"];

    $mail->Port = $_ENV["MAIL_PORT"];





    $mail->setFrom(
        $_ENV["MAIL_FROM"],
        $_ENV["MAIL_FROM_NAME"]
    );



    $mail->addAddress(
        $email,
        $firstName
    );



    $mail->isHTML(true);



    $mail->Subject = "Заказ оформлен";



    $mail->Body = "

        <h2>Здравствуйте, {$firstName}</h2>

        <p>
            Ваш заказ успешно оформлен.
        </p>

        <p>
            Номер заказа:
            <b>{$orderId}</b>
        </p>

        <p>
            Книга:
            <b>{$book["title"]}</b>
        </p>

        <p>
            Цена:
            <b>{$book["price"]} ₸</b>
        </p>

    ";



    $mail->send();



} catch (Exception $e) {


    error_log(
        $mail->ErrorInfo
    );

}







$file = fopen(
    __DIR__ . "/../orders/order.txt",
    "a+"
);




$text =

str_repeat("-", 40) . "\n\n" .

"Номер заказа: {$orderId}\n" .

"Дата заказа: " . date("d.m.Y H:i:s") . "\n" .

"ID книги: {$bookId}\n" .

"Название книги: {$book["title"]}\n" .

"Цена: {$book["price"]} тенге\n" .

"Имя: {$firstName}\n" .

"Фамилия: {$surname}\n" .

"Отчество: {$fatherName}\n" .

"Телефон: {$phone}\n" .

"Email: {$email}\n" .

"Город: {$city}\n" .

"Почтовый индекс: {$postalCode}\n" .

"Адрес: {$address}\n\n" .

str_repeat("-", 40) . "\n\n";





fwrite(
    $file,
    $text
);



fclose($file);






http_response_code(201);



echo json_encode([

    "success" => true,

    "message" => "Заказ успешно оформлен.",

    "firstname" => $firstName

]);