<html>
    <head>
        trang admin
    </head>
    <body>
<?php

    include("../../config/database.php");

    $sl="select * from admin";
    $kq=mysqli_query($link,$sl);


    $mat_khau = [
    "123456",
    "admin123",
    ];

    foreach ($mat_khau as $mk) {
        $hash = password_hash($mk, PASSWORD_ARGON2ID);

        $sql = "INSERT INTO admin (ho_ten, email, mat_khau) VALUES (?, ?, ?)";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("sss", $ho_ten, $email, $hash);
        $stmt->execute();
    }

    // matkhau
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();


    if ($admin && password_verify($_POST['mat_khau'], $admin['mat_khau'])) {
    echo "Đăng nhập thành công";
    } else {
        echo "Email hoặc mật khẩu không đúng";
    }

    mysqli_close($link);
?>
    </body>

</html>

