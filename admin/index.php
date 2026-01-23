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
$contactCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_details")->fetchColumn();
$socialCount = (int)$pdo->query("SELECT COUNT(*) FROM social_media_links")->fetchColumn();
$openingHoursCount = (int)$pdo->query("SELECT COUNT(*) FROM opening_hours")->fetchColumn();
$serviceCount = (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$faqCount = (int)$pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();

$pageTitle = "Admin overzicht";
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Admin overzicht</h1>
        <p class="mt-3 text-brandText/70">Beheer producten en spring direct naar het bestellingenoverzicht.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Producten</div>
        <div class="mt-2 text-lg font-medium text-brandText">
            <?= $totalProducts ?> totaal · <?= $activeProducts ?> actief
        </div>
        <?php if ($lastUpdated): ?>
            <div class="mt-1 text-xs text-brandText/60">Laatste update: <?= h((string)$lastUpdated) ?></div>
        <?php else: ?>
            <div class="mt-1 text-xs text-brandText/60">Nog geen updates beschikbaar</div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-6 md:grid-cols-2">
    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Catalogus</div>
        <h2 class="mt-3 font-serif text-2xl">Producten beheren</h2>
        <p class="mt-3 text-brandText/70">Bewerk bestaande producten, beheer relaties en voeg nieuwe items toe.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="rounded-2xl px-4 py-2 text-sm border border-black/5 bg-brandBg hover:bg-white transition text-brandText/80 hover:text-brandText" href="/admin/products/">Open productoverzicht</a>
            <a class="btn btn-primary" href="/admin/products/create.php">Nieuw product</a>
        </div>
        <div class="mt-6 text-xs text-brandText/50"><?= $activeProducts ?> actieve producten van <?= $totalProducts ?> in totaal.</div>
    </div>

    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Bestellingen</div>
        <h2 class="mt-3 font-serif text-2xl">Bestellingen overzicht</h2>
        <p class="mt-3 text-brandText/70">Ga naar het bestellingenoverzicht zodra deze is gekoppeld.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="rounded-2xl px-4 py-2 text-sm border border-black/5 bg-brandBg hover:bg-white transition text-brandText/80 hover:text-brandText" href="/admin/orders/">Open bestellingen</a>
        </div>
        <div class="mt-6 text-xs text-brandText/50">Referentiepunt voor orderbeheer vanuit het adminpaneel.</div>
    </div>
</div>

<div class="mt-10">
    <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
    <h2 class="mt-3 font-serif text-2xl">Content & informatie</h2>
    <p class="mt-3 text-brandText/70">Werk contactinformatie, openingstijden, diensten en veelgestelde vragen bij.</p>
</div>

<div class="mt-6 grid gap-6 md:grid-cols-2">
    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Contact</div>
        <h3 class="mt-3 font-serif text-xl">Contactgegevens beheren</h3>
        <p class="mt-3 text-brandText/70">Telefoon, e-mail en adresgegevens die overal in de shop worden getoond.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="btn btn-secondary" href="/admin/content/contact.php">Beheer contactgegevens</a>
        </div>
        <div class="mt-6 text-xs text-brandText/50"><?= $contactCount ?> contactset(s) opgeslagen.</div>
    </div>

    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Socials</div>
        <h3 class="mt-3 font-serif text-xl">Social media kanalen</h3>
        <p class="mt-3 text-brandText/70">Beheer links naar Instagram, TikTok, Facebook en andere kanalen.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="btn btn-secondary" href="/admin/content/social-media.php">Beheer social links</a>
        </div>
        <div class="mt-6 text-xs text-brandText/50"><?= $socialCount ?> social link(s) ingesteld.</div>
    </div>

    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Openingstijden</div>
        <h3 class="mt-3 font-serif text-xl">Openingstijden beheren</h3>
        <p class="mt-3 text-brandText/70">Stel per dag de openingstijden en eventuele sluitingen in.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="btn btn-secondary" href="/admin/opening-hours/">Beheer openingstijden</a>
        </div>
        <div class="mt-6 text-xs text-brandText/50"><?= $openingHoursCount ?> dag(en) ingesteld.</div>
    </div>

    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Diensten</div>
        <h3 class="mt-3 font-serif text-xl">Diensten beheren</h3>
        <p class="mt-3 text-brandText/70">Titels, omschrijvingen en afbeeldingen voor de dienstencatalogus.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="btn btn-secondary" href="/admin/content/services.php">Beheer diensten</a>
        </div>
        <div class="mt-6 text-xs text-brandText/50"><?= $serviceCount ?> dienst(en) aangemaakt.</div>
    </div>

    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5 md:col-span-2">
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">FAQ</div>
        <h3 class="mt-3 font-serif text-xl">Veel gestelde vragen</h3>
        <p class="mt-3 text-brandText/70">Beheer FAQ-items zodat klanten snel antwoord vinden.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="btn btn-secondary" href="/admin/content/faqs.php">Beheer FAQ</a>
        </div>
        <div class="mt-6 text-xs text-brandText/50"><?= $faqCount ?> FAQ-item(s) beschikbaar.</div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
