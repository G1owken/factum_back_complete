<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../function/uploader.php';
require_once __DIR__ . '/../function/validatePassword.php';
require_once __DIR__ . '/../function/error.php';

$pdo = getDbConnection();
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($currentUserId !== 1) {
    header('Location: catalogue.php');
    exit;
}

function deleteUserRelatedFiles(string $photoPath): void
{
    if ($photoPath === '') {
        return;
    }

    $root = __DIR__ . '/../';
    $baseName = basename($photoPath);

    foreach ([
        $root . $photoPath,
        $root . 'uploads/thumb_' . $baseName,
        $root . 'uploads/logo_' . $baseName,
    ] as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'toggle_genre') {
            $genreId = (int)($_POST['genre_id'] ?? 0);
            if ($genreId > 0) {
                $pdo->prepare('UPDATE genre SET is_active = IF(is_active = 1, 0, 1) WHERE genre_id = ?')->execute([$genreId]);
            }
        }

        if ($action === 'toggle_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            if ($userId > 0 && $userId !== $currentUserId) {
                $pdo->prepare('UPDATE user SET is_set = IF(is_set = 1, 0, 1) WHERE user_id = ?')->execute([$userId]);
            }
        }

        if ($action === 'toggle_book') {
            $bookId = (int)($_POST['book_id'] ?? 0);
            if ($bookId > 0) {
                $pdo->prepare('UPDATE book SET exist = IF(exist = 1, 0, 1) WHERE book_id = ?')->execute([$bookId]);
            }
        }

        if ($action === 'save_genre') {
            $genreId = (int)($_POST['genre_id'] ?? 0);
            $genre = trim((string)($_POST['genre'] ?? ''));
            $subject = trim((string)($_POST['open_library_subject'] ?? ''));

            if ($genre === '' || $subject === '') {
                throw new InvalidArgumentException('Genre name and subject are required.');
            }

            if ($genreId > 0) {
                $stmt = $pdo->prepare('UPDATE genre SET genre = ?, open_library_subject = ? WHERE genre_id = ?');
                $stmt->execute([$genre, $subject, $genreId]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO genre (genre, open_library_subject, is_active) VALUES (?, ?, 1)');
                $stmt->execute([$genre, $subject]);
            }
        }

        if ($action === 'delete_genre') {
            $genreId = (int)($_POST['genre_id'] ?? 0);
            if ($genreId > 0) {
                $pdo->prepare('DELETE FROM genre WHERE genre_id = ?')->execute([$genreId]);
            }
        }

        if ($action === 'save_book') {
            $bookId = (int)($_POST['book_id'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $releaseYear = trim((string)($_POST['release_year'] ?? ''));
            $price = trim((string)($_POST['price'] ?? '0'));
            $description = trim((string)($_POST['description'] ?? ''));
            $openLibraryKey = trim((string)($_POST['open_library_key'] ?? ''));
            $coverPath = trim((string)($_POST['cover_path'] ?? ''));

            if ($title === '' || $description === '') {
                throw new InvalidArgumentException('Book title and description are required.');
            }

            if ($bookId > 0) {
                $stmt = $pdo->prepare('UPDATE book SET title = ?, release_year = ?, price = ?, description = ?, open_library_key = ?, cover_path = ? WHERE book_id = ?');
                $stmt->execute([$title, $releaseYear === '' ? null : (int)$releaseYear, (float)$price, $description, $openLibraryKey === '' ? 'manual_' . uniqid() : $openLibraryKey, $coverPath, $bookId]);
            } else {
                if ($openLibraryKey === '') {
                    $openLibraryKey = 'manual_' . uniqid();
                }
                $stmt = $pdo->prepare('INSERT INTO book (title, release_year, cover_path, open_library_key, price, description, exist) VALUES (?, ?, ?, ?, ?, ?, 1)');
                $stmt->execute([$title, $releaseYear === '' ? null : (int)$releaseYear, $coverPath === '' ? 'uploads/empty.png' : $coverPath, $openLibraryKey, (float)$price, $description]);
            }
        }

        if ($action === 'delete_book') {
            $bookId = (int)($_POST['book_id'] ?? 0);
            if ($bookId > 0) {
                $pdo->prepare('DELETE FROM book WHERE book_id = ?')->execute([$bookId]);
            }
        }

        if ($action === 'save_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $username = trim((string)($_POST['username'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $password = trim((string)($_POST['password'] ?? ''));

            if ($username === '' || $email === '') {
                throw new InvalidArgumentException('Username and email are required.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('The email address is invalid.');
            }

            if ($userId > 0) {
                $current = $pdo->prepare('SELECT username, photo_path FROM user WHERE user_id = ? LIMIT 1');
                $current->execute([$userId]);
                $existingUser = $current->fetch();

                $photoPath = $existingUser['photo_path'] ?? null;
                $newPhotoPath = $photoPath;

                if (!empty($_FILES['photo']['name'])) {
                    $uploader = new Uploader(__DIR__ . '/../uploads', $username);
                    $uploadResult = $uploader->upload($_FILES['photo']);
                    $newPhotoPath = $uploadResult['original'];

                    if ($photoPath && $photoPath !== $newPhotoPath) {
                        deleteUserRelatedFiles($photoPath);
                    }
                }

                $fields = ['username = ?', 'email = ?'];
                $params = [$username, $email];

                if ($password !== '') {
                    $passwordErrors = validatePassword($password);
                    if ($passwordErrors !== []) {
                        throw new InvalidArgumentException(implode(' ', $passwordErrors));
                    }
                    $fields[] = 'password_hash = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }

                if ($newPhotoPath !== null) {
                    $fields[] = 'photo_path = ?';
                    $params[] = $newPhotoPath;
                }

                $params[] = $userId;
                $sql = 'UPDATE user SET ' . implode(', ', $fields) . ' WHERE user_id = ?';
                $pdo->prepare($sql)->execute($params);
            } else {
                if ($password === '') {
                    throw new InvalidArgumentException('Password is required for a new user.');
                }

                $passwordErrors = validatePassword($password);
                if ($passwordErrors !== []) {
                    throw new InvalidArgumentException(implode(' ', $passwordErrors));
                }

                $photoPath = null;
                if (!empty($_FILES['photo']['name'])) {
                    $uploader = new Uploader(__DIR__ . '/../uploads', $username);
                    $uploadResult = $uploader->upload($_FILES['photo']);
                    $photoPath = $uploadResult['original'];
                }

                $stmt = $pdo->prepare('INSERT INTO user (username, password_hash, email, photo_path, is_set) VALUES (?, ?, ?, ?, 1)');
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email, $photoPath]);
            }
        }

        if ($action === 'delete_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            if ($userId > 0 && $userId !== $currentUserId) {
                $stmt = $pdo->prepare('SELECT photo_path FROM user WHERE user_id = ? LIMIT 1');
                $stmt->execute([$userId]);
                $userPhoto = $stmt->fetchColumn();

                $pdo->prepare('DELETE FROM orders WHERE user_id = ?')->execute([$userId]);
                $pdo->prepare('DELETE FROM favourites WHERE user_id = ?')->execute([$userId]);

                if ($userPhoto) {
                    deleteUserRelatedFiles($userPhoto);
                }

                $pdo->prepare('DELETE FROM user WHERE user_id = ?')->execute([$userId]);
            }
        }

        $message = 'The change was saved successfully.';
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        errorResponse($message, 400);
    }
}

$genres = $pdo->query('SELECT * FROM genre ORDER BY genre')->fetchAll();
$users = $pdo->query('SELECT * FROM user ORDER BY created_at DESC')->fetchAll();
$books = $pdo->query('SELECT * FROM book ORDER BY title')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">
        <a href="catalogue.php">Каталог</a> |
        <a href="profile.php">Профиль</a> |
        <a href="../scripts/logout.php">Выйти из аккаунта</a>
    </div>

    <h1>Администрирование</h1>

    <?php if ($message !== ''): ?>
        <p class="status-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <div class="section">
        <h2>Жанры</h2>

        <form method="post" class="inline-form">
            <input type="hidden" name="action" value="save_genre">
            <input type="hidden" name="genre_id" value="">
            <input type="text" name="genre" placeholder="Genre name" required>
            <input type="text" name="open_library_subject" placeholder="Open Library subject" required>
            <button type="submit">Добавить жанр</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Open Library subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($genres as $genre): ?>
                    <tr>
                        <td><?= (int)$genre['genre_id']; ?></td>
                        <td>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="save_genre">
                                <input type="hidden" name="genre_id" value="<?= (int)$genre['genre_id']; ?>">
                                <input type="text" name="genre" value="<?= htmlspecialchars($genre['genre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </td>
                        <td>
                                <input type="text" name="open_library_subject" value="<?= htmlspecialchars($genre['open_library_subject'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </td>
                        <td>
                                <button type="submit">Сохранить</button>
                            </form>

                            <form method="post">
                                <input type="hidden" name="action" value="toggle_genre">
                                <input type="hidden" name="genre_id" value="<?= (int)$genre['genre_id']; ?>">
                                <button type="submit"><?= (int)$genre['is_active'] === 1 ? 'Скрыть' : 'Показать' ?></button>
                            </form>

                            <form method="post" onsubmit="return confirm('Delete this genre?');">
                                <input type="hidden" name="action" value="delete_genre">
                                <input type="hidden" name="genre_id" value="<?= (int)$genre['genre_id']; ?>">
                                <button type="submit" class="danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Пользователи</h2>

        <form method="post" enctype="multipart/form-data" class="inline-form">
            <input type="hidden" name="action" value="save_user">
            <input type="hidden" name="user_id" value="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            <button type="submit">Добавить пользователя</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Avatar</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int)$user['user_id']; ?></td>
                        <td>
                            <form method="post" enctype="multipart/form-data" class="inline-form">
                                <input type="hidden" name="action" value="save_user">
                                <input type="hidden" name="user_id" value="<?= (int)$user['user_id']; ?>">
                                <input type="text" name="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </td>
                        <td>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <br>
                                <input type="password" name="password" placeholder="Новый пароль">
                        </td>
                        <td>
                                <?php if (!empty($user['photo_path'])): ?>
                                    <img src="../<?= htmlspecialchars($user['photo_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="user avatar" width="50" height="50" style="object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <p>Отсутствует аватар</p>
                                <?php endif; ?><br>
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                        </td>
                        <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                                <button type="submit">Сохранить</button>
                            </form>

                            <form method="post">
                                <input type="hidden" name="action" value="toggle_user">
                                <input type="hidden" name="user_id" value="<?= (int)$user['user_id']; ?>">
                                <button type="submit" <?= (int)$user['user_id'] === $currentUserId ? 'disabled' : ''; ?>><?= (int)$user['is_set'] === 1 ? 'Скрыть' : 'Показать' ?></button>
                            </form>

                            <form method="post" onsubmit="return confirm('Удалить пользователя?');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= (int)$user['user_id']; ?>">
                                <button type="submit" class="danger" <?= (int)$user['user_id'] === $currentUserId ? 'disabled' : ''; ?>>Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Книги</h2>

        <form method="post" class="inline-form">
            <input type="hidden" name="action" value="save_book">
            <input type="hidden" name="book_id" value="">
            <input type="text" name="title" placeholder="Title" required>
            <input type="number" name="release_year" placeholder="Year" min="1900" max="2100">
            <input type="number" step="0.01" name="price" placeholder="Price" min="0">
            <input type="text" name="open_library_key" placeholder="Open Library key">
            <input type="text" name="cover_path" placeholder="Cover path">
            <textarea name="description" placeholder="Description" required></textarea>
            <button type="submit">Добавить книгу</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Price</th>
                    <th>Cover</th>
                    <th>Open key</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= (int)$book['book_id']; ?></td>
                        <td>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="save_book">
                                <input type="hidden" name="book_id" value="<?= (int)$book['book_id']; ?>">
                                <input type="text" name="title" value="<?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </td>
                        <td>
                                <input type="number" name="release_year" value="<?= htmlspecialchars((string)($book['release_year'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" min="1900" max="2100">
                        </td>
                        <td>
                                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars((string)$book['price'], ENT_QUOTES, 'UTF-8'); ?>" min="0">
                        </td>
                        <td>
                                <?php if (!empty($book['cover_path'])): ?>
                                    <img src="../<?= htmlspecialchars($book['cover_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="cover" width="70">
                                <?php else: ?>
                                    <span>Обложка отсутствует</span>
                                <?php endif; ?><br>
                                <input type="text" name="cover_path" value="<?= htmlspecialchars($book['cover_path'], ENT_QUOTES, 'UTF-8'); ?>">
                        </td>
                        <td>
                                <input type="text" name="open_library_key" value="<?= htmlspecialchars($book['open_library_key'], ENT_QUOTES, 'UTF-8'); ?>">
                        </td>
                        <td>
                                <textarea name="description" required><?= htmlspecialchars($book['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </td>
                        <td>
                                <button type="submit">Сохранить</button>
                            </form>

                            <form method="post">
                                <input type="hidden" name="action" value="toggle_book">
                                <input type="hidden" name="book_id" value="<?= (int)$book['book_id']; ?>">
                                <button type="submit"><?= (int)$book['exist'] === 1 ? 'Скрыть' : 'Показать' ?></button>
                            </form>

                            <form method="post" onsubmit="return confirm('Delete this book?');">
                                <input type="hidden" name="action" value="delete_book">
                                <input type="hidden" name="book_id" value="<?= (int)$book['book_id']; ?>">
                                <button type="submit" class="danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
