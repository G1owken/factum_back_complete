<?php 

require_once __DIR__ . '/../function/error.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Uploader
{
    private $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    private $maxFileSize = 0.5 * 1024 * 1024; // 0.5 MB

    private $uploadDir;
    private $userName;
    private ImageManager $manager;


    public function __construct($dir, $userName)
    {
        $this->uploadDir = rtrim($dir, '/') . '/';
        $this->userName = $userName;
        $this->manager = new ImageManager(
            new Driver()
        );
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }


    public function upload($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            errorResponse('Ошибка при загрузке файла.', 400);
        }

        if ($file['size'] > $this->maxFileSize) {
            errorResponse(
                'Файл слишком большой. Максимальный размер: 0.5 MB.',
                400
            );
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $this->allowedMimes)) {
            errorResponse(
                'Недопустимый формат файла. Разрешены: JPEG, PNG, WEBP.',
                400
            );
        }

        $ext = pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        );

        $newFileName =
            uniqid('avatar_', true)
            . '_'
            . $this->userName
            . '.'
            . $ext;

        $path = $this->uploadDir . $newFileName;

        move_uploaded_file(
            $file['tmp_name'],
            $path
        );

        $img = $this->manager->read($path);

        $img->orient()
            ->cover(150, 150)
            ->save(
                $this->uploadDir . 'thumb_' . $newFileName
            );

        $img = $this->manager->read($path);

        $img->orient()
            ->cover(50, 50)
            ->save(
                $this->uploadDir . 'logo_' . $newFileName
            );

        return [
            'original' =>
                'uploads/' . $newFileName,

            'thumbnail' =>
                'uploads/thumb_' . $newFileName,
            'logo' =>
                'uploads/logo_' . $newFileName
        ];
    }
}