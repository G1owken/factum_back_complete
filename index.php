<?php

session_start();

if (isset($_SESSION["user_id"])) {

    if ($_SESSION["user_id"] === 1) {
        header("Location: pages/admin.php");
    } else {
        header("Location: pages/catalogue.php");
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.css">
</head>

<body>

<h1>Вход</h1>

<form
id = "loginForm" 
action="scripts/authorisation.php" 
method="post"
>

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

<a data-fancybox data-src="#registrationModal" href="#registrationModal">
    Регистрация
</a>

<div style="display:none" id="registrationModal">

    <form
        id="registrationForm"
        action="scripts/registration.php"
        method="POST"
    >

        <h2>
            Регистрация
        </h2>

        <p>Логин</p>

        <input
            type="text"
            name="username"
            required
        >

        <p>Email</p>

        <input
            type="email"
            name="email"
            required
        >

        <p>Пароль</p>

        <input
            type="password"
            name="password"
            minlength = "10"
            required
        >

        <p>Введите пароль еще раз</p>

        <input
            type="password"
            name="confirm_password"
            minlength = "10"
            required
        >


        <br><br>
        <hr>

        <button type="submit">
            Зарегистрироваться
        </button>

    </form>
    
</div>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.umd.js"></script>
<script src="scripts/script.js"></script>
</body>

</html>