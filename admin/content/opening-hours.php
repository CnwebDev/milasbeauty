<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$openingHours = $pdo->query("SELECT id, day_of_week, opens_at, closes_at, is_closed, note, updated_at FROM opening_hours ORDER BY day_of_week ASC")->fetchAll();
$dayLabels = [
    1 => 'Maandag',
    2 => 'Dinsdag',
    3 => 'Woensdag',
    4 => 'Donderdag',
    5 => 'Vrijdag',
    6 => 'Zaterdag',
    7 => 'Zondag',
];

$pageTitle = "Openingstijden";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Openingstijden beheren</h1>
        <p class="mt-3 text-brandText/70">Beheer de openingstijden per dag en markeer sluitingen.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Dagen</div>
        <div class="mt-2 text-lg font-medium text-brandText"><?= count($openingHours) ?> ingesteld</div>
    </div>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-4">
    <?php if (!$openingHours): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5 text-brandText/70">
            Nog geen openingstijden toegevoegd. Voeg ze toe in de database.
        </div>
    <?php endif; ?>

    <?php foreach ($openingHours as $hours): ?>
        <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="font-serif text-xl text-brandText"><?= h($dayLabels[(int)$hours['day_of_week']] ?? 'Onbekend') ?></div>
                    <div class="mt-2 text-sm text-brandText/70">
                        <?php if ((int)$hours['is_closed'] === 1): ?>
                            Gesloten
                        <?php else: ?>
                            <?= h((string)$hours['opens_at']) ?> - <?= h((string)$hours['closes_at']) ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($hours['note'])): ?>
                        <div class="mt-1 text-sm text-brandText/60"><?= h((string)$hours['note']) ?></div>
                    <?php endif; ?>
                    <div class="mt-1 text-xs text-brandText/50">Laatst bijgewerkt: <?= h((string)$hours['updated_at']) ?></div>
                </div>
                <span class="text-xs text-brandText/50">Bewerken volgt</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
