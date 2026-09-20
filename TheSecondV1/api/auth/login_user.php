<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được phép.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Đọc JSON từ JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu gửi lên không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập đầy đủ email và mật khẩu.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Lấy user theo email
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, password, avatar, role, status
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Email hoặc mật khẩu không đúng.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($user['status'] === 'blocked') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($user['status'] === 'inactive') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Tài khoản chưa được kích hoạt.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Email hoặc mật khẩu không đúng.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Đăng nhập thành công → lưu session
    $_SESSION['user_id']     = (int)$user['id'];
    $_SESSION['user_name']   = $user['full_name'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_avatar'] = $user['avatar'];
    $_SESSION['user_role']   = $user['role'];

    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'user' => [
            'id'        => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'avatar'    => $user['avatar'],
            'role'      => $user['role']
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống.',
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

    exit;
}