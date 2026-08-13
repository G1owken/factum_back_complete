<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/error.php';
$pdo = getDbConnection();

$userId = $_SESSION['user_id'] ?? null;

header("Content-Type: application/json; charset=utf-8");

if (!$userId) {
    errorResponse('Необходимо авторизоваться.', 401);
    exit;
}

$sql = "
select 
    b.book_id,
    b.title, 
    b.release_year, 
    b.price, 
    b.cover_path,
    coalesce(sum(s.amount), 0) as stock,
    group_concat(
        distinct g.genre 
        order by g.genre 
        separator ', ') 
        as genres,
    group_concat(distinct a.author
    order by a.author 
    separator ', ') 
    as authors
from favourites f
inner join book b 
    on f.book_id = b.book_id
left join book_genre bg 
    on b.book_id = bg.book_id
left join genre g 
    on bg.genre_id = g.genre_id
left join book_author ba 
    on b.book_id = ba.book_id
left join author a 
    on ba.author_id = a.author_id
left join stock s
    on b.book_id = s.book_id
where f.user_id = ? and b.exist = 1
group by 
    b.book_id,
    f.added_at
order by f.added_at desc
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

$favourites = $stmt->fetchAll();

echo json_encode(
    $favourites,
    JSON_UNESCAPED_UNICODE
);
