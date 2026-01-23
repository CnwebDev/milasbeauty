<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: /admin/products/");
    exit;
}

// Fetch images to delete files first
$stmt = $pdo->prepare("SELECT main_image FROM products WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$p = $stmt->fetch();

$stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id=?");
$stmt->execute([$id]);
$imgs = $stmt->fetchAll();

// Delete DB rows (cascade removes images + relations)
$pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);

// Remove files
if (!empty($p['main_image'])) {
    $abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . $p['main_image'];
    if (is_file($abs)) @unlink($abs);
}
foreach ($imgs as $img) {
    $abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . $img['image_path'];
    if (is_file($abs)) @unlink($abs);
}

// Remove product folder
$folder = public_upload_root() . DIRECTORY_SEPARATOR . $id;
if (is_dir($folder)) {
    $files = glob($folder . DIRECTORY_SEPARATOR . '*');
    if ($files) {
        foreach ($files as $f) { if (is_file($f)) @unlink($f); }
    }
    @rmdir($folder);
}

header("Location: /admin/products/");
exit;
