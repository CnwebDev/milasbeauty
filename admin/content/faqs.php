<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$faqs = $pdo->query("SELECT id, question, answer, is_active, sort_order, updated_at FROM faqs ORDER BY sort_order ASC, id ASC")->fetchAll();

$pageTitle = "Veel gestelde vragen";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Veel gestelde vragen</h1>
        <p class="mt-3 text-brandText/70">Beheer veelgestelde vragen zodat bezoekers direct antwoord hebben.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">FAQ</div>
        <div class="mt-2 text-lg font-medium text-brandText"><?= count($faqs) ?> items</div>
    </div>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-4">
    <?php if (!$faqs): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5 text-brandText/70">
            Nog geen FAQ-items toegevoegd. Voeg ze toe in de database.
        </div>
    <?php endif; ?>

    <?php foreach ($faqs as $faq): ?>
        <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="font-serif text-xl text-brandText"><?= h((string)$faq['question']) ?></div>
                    <div class="mt-2 text-sm text-brandText/70"><?= h((string)$faq['answer']) ?></div>
                    <div class="mt-1 text-sm text-brandText/70">Status: <span class="text-brandText/90"><?= ((int)$faq['is_active'] === 1) ? 'Actief' : 'Inactief' ?></span></div>
                    <div class="mt-1 text-xs text-brandText/50">Sortering: <?= h((string)$faq['sort_order']) ?> · Laatst bijgewerkt: <?= h((string)$faq['updated_at']) ?></div>
                </div>
                <span class="text-xs text-brandText/50">Bewerken volgt</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
