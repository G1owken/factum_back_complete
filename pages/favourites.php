<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDbConnection();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('
select photo_path from user
where user_id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favourites</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Избранные книги</h1>
    <?php
        $logoPath = 'uploads/logo_' . basename($user['photo_path']);
    ?>

    <img
        src="../<?= htmlspecialchars($logoPath) ?>"
        alt="Аватар"
        width="50"
        height="50"
        id="profileAvatar"
    >
    <a href="catalogue.php">Каталог</a> | <a href="profile.php">Профиль</a> | <a href="../scripts/logout.php">Выйти из аккаунта</a>
    <hr>
    <div id="favourites"></div>
    <script src="/../scripts/script.js"></script>
</body>
</html>