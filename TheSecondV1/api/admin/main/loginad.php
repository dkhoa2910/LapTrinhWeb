<?php

session_start();

require_once __DIR__ . '/../../config/database.php';


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../../admin/admin_login.html");
    exit();
}

$email = trim($_POST["email"] ?? "");
$mat_khau = $_POST["mat_khau"] ?? "";

if ($email === "" || $mat_khau === "") {
    header("Location: ../../../admin/admin_login.html?error=1");
    exit();
}

$sql = "SELECT admin_id, ho_ten, email, mat_khau
        FROM admin
        WHERE email = ?
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);

$admin = $stmt->fetch();

if ($admin && password_verify($mat_khau, $admin["mat_khau"])) {

    $_SESSION["admin_id"] = $admin["admin_id"];
    $_SESSION["admin_name"] = $admin["ho_ten"];
    $_SESSION["admin_email"] = $admin["email"];

    header("Location: /../../../../../TheSecondV1/api/admin/dashboard.php");
    exit();

} else {

    header("Location: ../../../admin/admin_login.html?error=1");
    exit();
}