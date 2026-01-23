<?php
declare(strict_types=1);
require_once __DIR__ . '/env.php';

load_env(dirname(__DIR__) . '/.env');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    // Pas dit aan naar jouw omgeving
    $host = 'localhost';
    $dbname = 'milasbeauty';
    $user = 'root';
    $pass = '';
//$user = 'milasdnasdwqioqe';
//$pass = 'sDnLlGonu8%b75*yasdddddqweqw';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Simpele slugify (zonder externe libs)
 */
function slugify(string $text): string
{
    $text = trim(mb_strtolower($text, 'UTF-8'));
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~-+~', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'product';
}

function ensure_unique_slug(PDO $pdo, string $baseSlug, ?int $ignoreId = null): string
{
    $slug = $baseSlug;
    $i = 2;

    while (true) {
        if ($ignoreId) {
            $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1");
            $stmt->execute([$slug, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
        }

        if (!$stmt->fetch()) return $slug;

        $slug = $baseSlug . '-' . $i;
        $i++;
    }
}

function public_upload_root(): string
{
    // projectroot/uploads/products
    return rtrim(dirname(__DIR__), '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
}

function allowed_image_mime(string $mime): bool
{
    return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
}

function move_uploaded_image(array $file, string $targetPath): void
{
    if (!is_dir(dirname($targetPath))) {
        mkdir(dirname($targetPath), 0775, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Upload mislukt (move_uploaded_file).');
    }
}

function require_login(): void {
    if (empty($_SESSION['admin_logged_in'])) {
        header("Location: /admin/auth/login.php");
        exit;
    }
}
