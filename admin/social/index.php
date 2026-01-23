<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$platforms = [
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
    'x' => 'Twitter / X',
    'tiktok' => 'TikTok',
    'linkedin' => 'LinkedIn',
    'reddit' => 'Reddit',
];

$existingLinks = [];
$placeholders = implode(',', array_fill(0, count($platforms), '?'));
$stmt = $pdo->prepare("SELECT id, platform, url, is_active, sort_order, updated_at FROM social_media_links WHERE platform IN ($placeholders)");
$stmt->execute(array_keys($platforms));
foreach ($stmt->fetchAll() as $row) {
    $existingLinks[(string)$row['platform']] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urls = $_POST['url'] ?? [];
    $actives = $_POST['is_active'] ?? [];
    $orders = $_POST['sort_order'] ?? [];

    $upsert = $pdo->prepare(
        "INSERT INTO social_media_links (platform, url, is_active, sort_order)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE url = VALUES(url), is_active = VALUES(is_active), sort_order = VALUES(sort_order)"
    );
    $delete = $pdo->prepare("DELETE FROM social_media_links WHERE platform = ?");

    foreach ($platforms as $key => $label) {
        $url = trim((string)($urls[$key] ?? ''));
        $isActive = isset($actives[$key]) ? 1 : 0;
        $sortOrder = isset($orders[$key]) ? (int)$orders[$key] : 0;
        $hasExisting = array_key_exists($key, $existingLinks);

        if ($url === '') {
            if ($hasExisting) {
                $delete->execute([$key]);
            }
            continue;
        }

        $upsert->execute([$key, $url, $isActive, $sortOrder]);
    }

    header('Location: /admin/social/?saved=1');
    exit;
}

$socialLinks = $pdo->query("SELECT id, platform, url, is_active, sort_order, updated_at FROM social_media_links ORDER BY sort_order ASC, platform ASC")->fetchAll();
$saved = isset($_GET['saved']);

$pageTitle = "Social media";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Social media kanalen</h1>
        <p class="mt-3 text-brandText/70">Beheer links voor Instagram, Facebook, Twitter / X, TikTok, LinkedIn en Reddit.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Links</div>
        <div class="mt-2 text-lg font-medium text-brandText"><?= count($socialLinks) ?> ingesteld</div>
    </div>
</div>

<?php if ($saved): ?>
    <div class="mt-6 rounded-[24px] p-5 border border-brandPinkSoft bg-brandPinkSoft/40 text-brandText/80">
        De social media links zijn opgeslagen.
    </div>
<?php endif; ?>

<div class="mt-8 hairline"></div>

<form class="mt-8 grid gap-6" method="post">
    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-sm font-medium">Social links instellen</div>
        <p class="mt-2 text-sm text-brandText/70">Vul de juiste URL in per kanaal. Laat leeg om het kanaal te verwijderen.</p>

        <div class="mt-6 grid gap-4">
            <?php foreach ($platforms as $key => $label): ?>
                <?php $existing = $existingLinks[$key] ?? null; ?>
                <div class="rounded-2xl p-4 border border-black/5 bg-brandBg">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="font-serif text-lg text-brandText"><?= h($label) ?></div>
                            <?php if ($existing): ?>
                                <div class="mt-1 text-xs text-brandText/50">Laatst bijgewerkt: <?= h((string)$existing['updated_at']) ?></div>
                            <?php endif; ?>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-brandText/70">
                            <input
                                type="checkbox"
                                name="is_active[<?= h($key) ?>]"
                                class="h-4 w-4"
                                <?= $existing && (int)$existing['is_active'] === 1 ? 'checked' : '' ?>
                            />
                            Actief
                        </label>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-[1fr_160px]">
                        <label class="text-xs text-brandText/70">
                            URL
                            <input
                                type="url"
                                name="url[<?= h($key) ?>]"
                                class="mt-2 input-field"
                                placeholder="https://"
                                value="<?= h((string)($existing['url'] ?? '')) ?>"
                            />
                        </label>
                        <label class="text-xs text-brandText/70">
                            Sorteer volgorde
                            <input
                                type="number"
                                name="sort_order[<?= h($key) ?>]"
                                class="mt-2 input-field"
                                value="<?= h((string)($existing['sort_order'] ?? 0)) ?>"
                                min="0"
                            />
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">Opslaan</button>
            <a class="btn btn-secondary" href="/admin/index.php">Terug naar overzicht</a>
        </div>
    </div>
</form>

<div class="mt-8 grid gap-4">
    <?php if (!$socialLinks): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5 text-brandText/70">
            Nog geen social links toegevoegd. Gebruik hierboven het formulier om ze te bewaren.
        </div>
    <?php endif; ?>

    <?php foreach ($socialLinks as $link): ?>
        <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="font-serif text-xl text-brandText"><?= h((string)($platforms[(string)$link['platform']] ?? $link['platform'])) ?></div>
                    <div class="mt-2 text-sm text-brandText/70">URL: <span class="text-brandText/90"><?= h((string)$link['url']) ?></span></div>
                    <div class="mt-1 text-sm text-brandText/70">Status: <span class="text-brandText/90"><?= ((int)$link['is_active'] === 1) ? 'Actief' : 'Inactief' ?></span></div>
                    <div class="mt-1 text-xs text-brandText/50">Sortering: <?= h((string)$link['sort_order']) ?> · Laatst bijgewerkt: <?= h((string)$link['updated_at']) ?></div>
                </div>
                <span class="text-xs text-brandText/50">Bewerken via formulier hierboven</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
