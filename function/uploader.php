<?php 

require_once __DIR__ . '/../function/error.php';

class Uploader 
{
    private $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    private $maxFileSize = 0.5 * 1024 * 1024; /* 0.5 MB */
    private $uploadDir;
    private $userName;
    private $file;

    public function __construct($dir, $userName) {
        $this->uploadDir = rtrim($dir, '/') . '/';
        $this->user = $user;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            errorResponse ('Ошибка при загрузке файла.', 400);
        }
        if ($file['size'] > $this->maxFileSize) {
            errorResponse ('Файл слишком большой. Максимальный размер: 0.5 MB.', 400);
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo->file( $file['tmp_name']);
        if (!in_array($mime, $this->allowedMimes)) {
            errorResponse ('Недопустимый формат файла. Разрешены: JPEG, PNG, WEBP.', 400);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('avatar_', true) . '_' . $userName . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $path);

        $img = Image::make($path);
        $img->orientate();
        $img->fit(150, 150)->save($this->uploadDir . 'thumb_' . $filename);

        return [
            'original' => 'uploads/' . $filename,
            'thumbnail' => 'uploads/thumb_' . $filename
        ];
    }

}