<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$socialLinks = $pdo->query("SELECT id, platform, url, is_active, sort_order, updated_at FROM social_media_links ORDER BY sort_order ASC, platform ASC")->fetchAll();

$pageTitle = "Social media";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Social media kanalen</h1>
        <p class="mt-3 text-brandText/70">Beheer alle social links die op de website worden getoond.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Links</div>
        <div class="mt-2 text-lg font-medium text-brandText"><?= count($socialLinks) ?> ingesteld</div>
    </div>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-4">
    <?php if (!$socialLinks): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5 text-brandText/70">
            Nog geen social links toegevoegd. Voeg ze toe in de database.
        </div>
    <?php endif; ?>

    <?php foreach ($socialLinks as $link): ?>
        <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="font-serif text-xl text-brandText"><?= h((string)$link['platform']) ?></div>
                    <div class="mt-2 text-sm text-brandText/70">URL: <span class="text-brandText/90"><?= h((string)$link['url']) ?></span></div>
                    <div class="mt-1 text-sm text-brandText/70">Status: <span class="text-brandText/90"><?= ((int)$link['is_active'] === 1) ? 'Actief' : 'Inactief' ?></span></div>
                    <div class="mt-1 text-xs text-brandText/50">Sortering: <?= h((string)$link['sort_order']) ?> · Laatst bijgewerkt: <?= h((string)$link['updated_at']) ?></div>
                </div>
                <span class="text-xs text-brandText/50">Bewerken volgt</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
