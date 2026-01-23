<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$imageId = (int)($_GET['id'] ?? 0);
$productId = (int)($_GET['product_id'] ?? 0);

if ($imageId <= 0 || $productId <= 0) {
    header("Location: /admin/products/");
    exit;
}

$stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id=? AND product_id=? LIMIT 1");
$stmt->execute([$imageId, $productId]);
$row = $stmt->fetch();

if ($row) {
    $abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . $row['image_path'];
    if (is_file($abs)) @unlink($abs);

    $pdo->prepare("DELETE FROM product_images WHERE id=?")->execute([$imageId]);
}

header("Location: /admin/products/edit.php?id={$productId}");
exit;
