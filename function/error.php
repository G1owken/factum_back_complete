<?php
function errorResponse(string $message, int $status = 400): never
{
    http_response_code($status);

    echo json_encode([
        "success" => false,
        "message" => $message
    ]);

    exit;
}