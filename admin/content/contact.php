<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$contactEntries = $pdo->query("SELECT id, phone, email, address_line, postal_code, city, country, updated_at FROM contact_details ORDER BY updated_at DESC")->fetchAll();

$pageTitle = "Contactgegevens";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Contactgegevens beheren</h1>
        <p class="mt-3 text-brandText/70">Beheer de basisgegevens die overal in de shop worden getoond.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Sets</div>
        <div class="mt-2 text-lg font-medium text-brandText"><?= count($contactEntries) ?> opgeslagen</div>
    </div>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-4">
    <?php if (!$contactEntries): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5 text-brandText/70">
            Nog geen contactgegevens opgeslagen. Voeg een eerste set toe in de database.
        </div>
    <?php endif; ?>

    <?php foreach ($contactEntries as $contact): ?>
        <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="font-serif text-xl text-brandText">Contactset #<?= (int)$contact['id'] ?></div>
                    <div class="mt-2 text-sm text-brandText/70">Telefoon: <span class="text-brandText/90"><?= h((string)$contact['phone']) ?></span></div>
                    <div class="mt-1 text-sm text-brandText/70">E-mail: <span class="text-brandText/90"><?= h((string)$contact['email']) ?></span></div>
                    <div class="mt-1 text-sm text-brandText/70">Adres: <span class="text-brandText/90"><?= h((string)$contact['address_line']) ?>, <?= h((string)$contact['postal_code']) ?> <?= h((string)$contact['city']) ?> (<?= h((string)$contact['country']) ?>)</span></div>
                    <div class="mt-1 text-xs text-brandText/50">Laatst bijgewerkt: <?= h((string)$contact['updated_at']) ?></div>
                </div>
                <span class="text-xs text-brandText/50">Bewerken volgt</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
