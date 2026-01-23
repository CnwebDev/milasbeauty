<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_login();

$pdo = db();

$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$lastUpdated = $pdo->query("SELECT updated_at FROM products ORDER BY updated_at DESC LIMIT 1")->fetchColumn();

$pageTitle = "Admin overzicht";
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Beheer</div>
        <h1 class="mt-3 font-display text-3xl md:text-4xl">Admin overzicht</h1>
        <p class="mt-3 text-white/70">Beheer producten en spring direct naar het bestellingenoverzicht.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 luxe-ring bg-black/30">
        <div class="text-xs uppercase tracking-[.3em] text-white/50">Producten</div>
        <div class="mt-2 text-lg font-medium">
            <?= $totalProducts ?> totaal · <?= $activeProducts ?> actief
        </div>
        <?php if ($lastUpdated): ?>
            <div class="mt-1 text-xs text-white/50">Laatste update: <?= h((string)$lastUpdated) ?></div>
        <?php else: ?>
            <div class="mt-1 text-xs text-white/50">Nog geen updates beschikbaar</div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-6 md:grid-cols-2">
    <div class="rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Catalogus</div>
        <h2 class="mt-3 font-display text-2xl">Producten beheren</h2>
        <p class="mt-3 text-white/70">Bewerk bestaande producten, beheer relaties en voeg nieuwe items toe.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="rounded-2xl px-4 py-2 text-sm luxe-ring bg-black/25 hover:bg-black/35 transition text-white/80 hover:text-white" href="/admin/products/">Open productoverzicht</a>
            <a class="btn btn-primary" href="/admin/products/create.php">Nieuw product</a>
        </div>
        <div class="mt-6 text-xs text-white/40"><?= $activeProducts ?> actieve producten van <?= $totalProducts ?> in totaal.</div>
    </div>

    <div class="rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Bestellingen</div>
        <h2 class="mt-3 font-display text-2xl">Bestellingen overzicht</h2>
        <p class="mt-3 text-white/70">Ga naar het bestellingenoverzicht zodra deze is gekoppeld.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="rounded-2xl px-4 py-2 text-sm luxe-ring bg-black/25 hover:bg-black/35 transition text-white/80 hover:text-white" href="/admin/orders/">Open bestellingen</a>
        </div>
        <div class="mt-6 text-xs text-white/40">Referentiepunt voor orderbeheer vanuit het adminpaneel.</div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
