<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query("SELECT DATABASE() AS database_name");

    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Kết nối database thành công.',
        'database' => $result['database_name']
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Database query failed.'
    ], JSON_UNESCAPED_UNICODE);
}