<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = db();
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_map('intval', $cart));

$q = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT id, name, slug, short_description, price, volume_ml, main_image
        FROM products
        WHERE is_active = 1";
$params = [];

if ($q !== '') {
    $sql .= " AND (name LIKE ? OR slug LIKE ? OR short_description LIKE ?)";
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
}

$sql .= " ORDER BY updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASA Parfums — Producten</title>

    <link rel="stylesheet" href="assets/css/tailwind.css" />
    <link rel="stylesheet" href="assets/css/custom.css" />
</head>

<body class="page-body">

<!-- Top Glow -->
<div class="fixed inset-0 -z-50 pointer-events-none">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 h-[520px] w-[520px] rounded-full blur-[90px] bg-gold-400/10"></div>
    <div class="absolute top-40 -left-24 h-[520px] w-[520px] rounded-full blur-[100px] bg-gold-300/8"></div>
</div>

<!-- NAV -->
<?php
$navbarLogo = <<<'HTML'
<a href="index.php" class="group flex items-center gap-3">
<!--    <div class="h-10 w-10 rounded-2xl luxe-ring shadow-glow bg-black/30 flex items-center justify-center">-->
<!--        <span class="font-display gold-text">A</span>-->
<!--    </div>-->
    <div class="leading-none">
        <div class="font-display tracking-[.22em] text-sm gold-text">ASA</div>
        <div class="text-[11px] tracking-[.34em] text-white/60 -mt-1">PARFUMS</div>
    </div>
</a>
HTML;

ob_start();
?>
<div class="flex items-center gap-3">
    <form method="get" class="hidden sm:flex items-center gap-2">
        <input
            name="q"
            value="<?= h($q) ?>"
            class="w-56 input-field"
            placeholder="Zoek geur..."
        />
    </form>

    <a href="cart.php" class="btn btn-primary">
        Winkelwagen
        <?php if ($cartCount > 0): ?>
            <span class="ml-2 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[11px] luxe-ring bg-black/25 text-white/80">
                <?= (int)$cartCount ?>
            </span>
        <?php endif; ?>
    </a>
</div>
<?php
$navbarRight = ob_get_clean();

require __DIR__ . '/partials/navbar.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-radial-hero"></div>
    </div>

    <div class="mx-auto max-w-7xl px-6 pt-16 pb-10 md:pt-20">
        <div class="flex items-end justify-between gap-8 flex-wrap">
            <div>
                <div class="text-xs tracking-[.34em] uppercase text-white/60">Collectie</div>
                <h1 class="mt-4 font-display text-4xl md:text-5xl leading-[1.05]">
                    Ontdek de <span class="gold-text">ASA</span> geuren
                </h1>
                <p class="mt-4 text-white/75 max-w-2xl">
                    Luxe geurprofielen, clean & warm. Selecteer je favoriet en voeg toe aan je winkelwagen.
                </p>
            </div>

            <form method="get" class="sm:hidden w-full">
                <input
                    name="q"
                    value="<?= h($q) ?>"
                    class="w-full rounded-2xl px-4 py-3 bg-black/40 luxe-ring outline-none focus:ring-2 focus:ring-gold-400/30 text-sm"
                    placeholder="Zoek geur..."
                />
            </form>
        </div>

        <div class="mt-10 hairline"></div>
    </div>
</section>

<!-- PRODUCTS GRID -->
<section class="pb-20">
    <div class="mx-auto max-w-7xl px-6">
        <?php if (!$products): ?>
            <div class="rounded-[28px] p-8 luxe-ring bg-black/25 shadow-glow text-white/70">
                Geen producten gevonden.
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($products as $p): ?>
                    <article class="group rounded-[28px] overflow-hidden luxe-ring bg-black/35 shadow-glow">
                        <div class="relative h-[320px]">
                            <?php if (!empty($p['main_image'])): ?>
                                <img src="<?= h((string)$p['main_image']) ?>" class="h-full w-full object-cover group-hover:scale-[1.03] transition duration-700" alt="<?= h($p['name']) ?>">
                            <?php else: ?>
                                <div class="h-full w-full bg-black/40 flex items-center justify-center text-white/40">Geen foto</div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/15 to-transparent"></div>
                        </div>

                        <div class="p-6">
                            <h3 class="font-display text-2xl gold-text"><?= h($p['name']) ?></h3>
                            <p class="mt-2 text-sm text-white/75"><?= h((string)($p['short_description'] ?? '')) ?></p>

                            <div class="mt-5 flex items-center justify-between">
                                <div class="text-white/70 text-sm">
                                    € <?= h(number_format((float)($p['price'] ?? 0), 2, ',', '.')) ?>
                                    <?php if (!empty($p['volume_ml'])): ?>
                                        <span class="text-white/40">/ <?= (int)$p['volume_ml'] ?>ml</span>
                                    <?php endif; ?>
                                </div>

                                <a href="product.php?slug=<?= urlencode((string)$p['slug']) ?>"
                                   class="btn btn-primary">
                                    Bekijk
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<footer class="py-14 border-t border-white/5">
    <div class="mx-auto max-w-7xl px-6">
        <div class="flex flex-wrap items-start justify-between gap-10">
            <div>
                <div class="font-display tracking-[.22em] text-lg gold-text">ASA PARFUMS</div>
                <p class="mt-3 text-sm text-white/60 max-w-md">
                    Luxe parfums met een moderne signatuur. Zwart, goud, en een presence die je voelt.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-10 text-sm text-white/65">
                <div class="space-y-2">
                    <div class="text-white/85 font-medium">Shop</div>
                    <a class="block hover:text-white transition" href="products.php">Producten</a>
                    <a class="block hover:text-white transition" href="cart.php">Winkelwagen</a>
                </div>
                <div class="space-y-2">
                    <div class="text-white/85 font-medium">Info</div>
                    <a class="block hover:text-white transition" href="index.php#faq">FAQ</a>
<!--                    <a class="block hover:text-white transition" href="index.php#cta">Sample set</a>-->
                </div>
            </div>
        </div>
        <div class="mt-10 hairline"></div>
        <div class="mt-8 text-xs text-white/45 flex items-center justify-between gap-4 flex-wrap">
            <div>© <?= date('Y') ?> ASA Parfums. All rights reserved.</div>
            <div class="flex gap-4">
                <a class="hover:text-white transition" href="index.php">Home</a>
                <a class="hover:text-white transition" href="products.php">Producten</a>
                <a class="hover:text-white transition" href="cart.php">Winkelwagen</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
