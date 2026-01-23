<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$services = $pdo->query("SELECT id, title, description, image_path, is_active, sort_order, updated_at FROM services ORDER BY sort_order ASC, title ASC")->fetchAll();

$pageTitle = "Diensten";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Diensten beheren</h1>
        <p class="mt-3 text-brandText/70">Beheer titels, omschrijvingen en afbeeldingen voor de dienstenpagina.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Diensten</div>
        <div class="mt-2 text-lg font-medium text-brandText"><?= count($services) ?> aangemaakt</div>
    </div>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-4">
    <?php if (!$services): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5 text-brandText/70">
            Nog geen diensten toegevoegd. Voeg ze toe in de database.
        </div>
    <?php endif; ?>

    <?php foreach ($services as $service): ?>
        <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-start gap-4">
                    <div class="h-16 w-16 rounded-2xl border border-black/5 bg-brandBg overflow-hidden flex items-center justify-center">
                        <?php if (!empty($service['image_path'])): ?>
                            <img src="/<?= h((string)$service['image_path']) ?>" class="h-full w-full object-cover" alt="">
                        <?php else: ?>
                            <span class="text-brandText/50 text-xs">no img</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="font-serif text-xl text-brandText"><?= h((string)$service['title']) ?></div>
                        <div class="mt-2 text-sm text-brandText/70"><?= h((string)$service['description']) ?></div>
                        <div class="mt-1 text-sm text-brandText/70">Status: <span class="text-brandText/90"><?= ((int)$service['is_active'] === 1) ? 'Actief' : 'Inactief' ?></span></div>
                        <div class="mt-1 text-xs text-brandText/50">Sortering: <?= h((string)$service['sort_order']) ?> · Laatst bijgewerkt: <?= h((string)$service['updated_at']) ?></div>
                    </div>
                </div>
                <span class="text-xs text-brandText/50">Bewerken volgt</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
