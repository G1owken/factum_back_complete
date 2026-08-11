<?php 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$pdo = getDbConnection();

$user_id = $_SESSION['user_id'];

$sql = "
select username, email, photo_path
from user
where user_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.css">
    <title>Profile</title>
</head>
<body>
    <h1>Личный кабинет</h1>
    <a href="catalogue.php">Каталог</a> | <a href="favourites.php">Избранные</a> | <button id="logoutButton">Выйти</button>
    <hr>
    <?php if (!empty($user['photo_path'])): ?>

        <?php
        $thumbnailPath = 'uploads/thumb_' . basename($user['photo_path']);
        ?>

        <img
            src="../<?= htmlspecialchars($thumbnailPath) ?>"
            alt="Аватар"
            width="150"
            height="150"
            id="profileAvatar"
            style="object-fit: cover;"
        >

    <?php else: ?>

        <img
            src="../uploads/empty.png"
            alt="Аватар"
            width="150"
            height="150"
            id="profileAvatar"
            style="object-fit: cover;"
        >

    <?php endif; ?>
    <br>
    <hr>
    <label>Никнейм:</label>
    <p><?= htmlspecialchars($user['username'])?></p>
    <br>
    <hr>
    <label>Почта:</label>
    <p><?= htmlspecialchars($user['email'])?></p>
    <br>
    <hr>
    <a data-fancybox data-src="#edit-form" href="#edit-form">Изменить</a>
    <div style="display: none;" id="edit-form">
        <form id = "editForm">
            <h2>Редактировать личные данные</h2>
            <label>Аватар</label>
            <input type = "file" id = "avatar" name = "avatar" accept = "image/jpeg, image/png, image/webp">
            <br>
            <hr>
            <label>Никнейм</label>
            <input type = "text" name = "username">
            <br>
            <hr>
            <label>Почта</label>
            <input type = "email" name = "email">
            <br>
            <hr>
            <label>Старый пароль</label>
            <input type = "password" name = "old_password" minlength = "10">
            <br>
            <hr>
            <label>Новый пароль</label>
            <input type = "password" name = "password" minlength = "10">
            <br>
            <hr>
            <label>Введите новый пароль повторно</label>
            <input type = "password" name = "confirm_password" minlength = "10">
            <br>
            <hr>
            <button type = "submit">Изменить</button>
        </form>
    </div>

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.umd.js"></script>
<script src="../scripts/script.js"></script>
<script> Fancybox.bind("[data-fancybox]", {}); </script>  
    
</body>
</html>