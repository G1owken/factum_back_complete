<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDbConnection();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favourites</title>
</head>
<body>
    <h1>Избранные книги</h1>
    <a href="catalogue.php">Каталог</a> | <a href="profile.php">Профиль</a> | <button id="logoutButton">Выйти</button>
    <hr>
    <div id="favourites"></div>
    <script src="/../scripts/script.js"></script>
</body>
</html>