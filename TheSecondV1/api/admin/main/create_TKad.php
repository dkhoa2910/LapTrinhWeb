<?php

$link = mysqli_connect(
    "localhost",
    "root",
    "",
    "dien_tu_cu_db"
) or die("Kết nối thất bại!");

$admins = [
    [
        "ho_ten" => "Administrator",
        "email" => "admin@gmail.com",
        "mat_khau" => "123456"
    ],
    [
        "ho_ten" => "Administrator 2",
        "email" => "admin2@gmail.com",
        "mat_khau" => "admin123"
    ]
];

foreach ($admins as $admin) {

    $hash = password_hash(
        $admin["mat_khau"],
        PASSWORD_ARGON2ID
    );

    $sql = "INSERT INTO admin (ho_ten, email, mat_khau)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($link, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $admin["ho_ten"],
        $admin["email"],
        $hash
    );

    mysqli_stmt_execute($stmt);
}

echo "Đã tạo tài khoản admin!";

mysqli_close($link);
?>