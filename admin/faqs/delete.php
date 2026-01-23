<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: /admin/faqs/");
    exit;
}

$pdo->prepare("DELETE FROM faqs WHERE id = ?")->execute([$id]);

header("Location: /admin/faqs/");
exit;
