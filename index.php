<?php

session_start();

if (isset($_SESSION["user_id"])) {

    if ($_SESSION["user_id"] === 1) {
        header("Location: admin.php");
    } else {
        header("Location: catalog.php");
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
</head>

<body>

<h1>Вход</h1>

<form action="scripts/authorisation.php" method="post">

    <p>Логин</p>

    <input
        type="text"
        name="username"
        required
    >

    <p>Пароль</p>

    <input
        type="password"
        name="password"
        required
    >

    <br><br>

    <button type="submit">
        Войти
    </button>

</form>

<br>
<hr>

<a data-fancybox data-src="#registrationForm" href="#registrationForm">
    Регистрация
</a>

</body>

</html>