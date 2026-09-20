<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'message' => 'Bạn chưa đăng nhập.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

echo json_encode([
    'success' => true,
    'authenticated' => true,
    'user' => $_SESSION['user']
], JSON_UNESCAPED_UNICODE);