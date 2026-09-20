<?php
header('Content-Type: application/json; charset=utf-8');
include("../../config/database.php");

$category_id = (int)($_GET['category_id'] ?? 0);

if ($category_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT spec_key, is_required, sort_order
    FROM category_spec_templates
    WHERE category_id = ?
    ORDER BY sort_order ASC
");
$stmt->execute([$category_id]);
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($templates, JSON_UNESCAPED_UNICODE);