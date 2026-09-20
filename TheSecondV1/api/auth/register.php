<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method không được phép.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$input = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu gửi lên không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$fullName = trim((string)($input['full_name'] ?? ''));
$email = strtolower(trim((string)($input['email'] ?? '')));
$password = (string)($input['password'] ?? '');


// ======================================================
// VALIDATION
// ======================================================

if ($fullName === '') {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập họ và tên.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Email không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (strlen($password) < 6) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================================
// CHECK EMAIL
// ======================================================

$stmt = $pdo->prepare(
    'SELECT id
     FROM users
     WHERE email = :email
     LIMIT 1'
);

$stmt->execute([
    'email' => $email
]);

if ($stmt->fetch()) {
    http_response_code(409);

    echo json_encode([
        'success' => false,
        'message' => 'Email này đã được sử dụng.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================================
// HASH PASSWORD
// ======================================================

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// ======================================================
// CREATE USER
// ======================================================

$stmt = $pdo->prepare(
    'INSERT INTO users
        (full_name, email, password, role, status)
     VALUES
        (:full_name, :email, :password, :role, :status)'
);

$stmt->execute([
    'full_name' => $fullName,
    'email'     => $email,
    'password'  => $passwordHash,
    'role'      => 'user',
    'status'    => 'active'
]);

$userId = (int)$pdo->lastInsertId();


// ======================================================
// RESPONSE
// ======================================================

http_response_code(201);

echo json_encode([
    'success' => true,
    'message' => 'Đăng ký tài khoản thành công.',
    'user' => [
        'id' => $userId,
        'full_name' => $fullName,
        'email' => $email,
        'role' => 'user'
    ]
], JSON_UNESCAPED_UNICODE);