<?php 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../function/error.php';
require_once __DIR__ . '/../function/uploader.php';

$pdo = getDbConnection ();

$user_id = $_SESSION['user_id'];

$sql = "
select username
from user
where user_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$userName = $stmt->fetchColumn();

$uploader = new Uploader (__DIR__ . '/../uploads', $userName);
$file = $uploader->upload($_FILES['avatar']);
$photoPath = $file['thumbnail'];

$sql = "
update user
set photo_path = ?
where user_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$photoPath, $user_id]);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
    <h1>Личный кабинет</h1>
    <a href="catalogue.php">Каталог</a> | <a href="favourites.php">Избранные</a> | <button id="logoutButton">Выйти</button>
    <hr>
    
    
    
</body>
</html>