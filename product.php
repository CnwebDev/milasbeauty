<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = db();

// Add to cart action (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    if ($pid > 0) {
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
        $_SESSION['cart'][$pid] = (int)($_SESSION['cart'][$pid] ?? 0) + $qty;
        header("Location: cart.php?added=1");
        exit;
    }
}

$slug = trim((string)($_GET['slug'] ?? ''));
$id = (int)($_GET['id'] ?? 0);

if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? AND is_active=1 LIMIT 1");
    $stmt->execute([$slug]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active=1 LIMIT 1");
    $stmt->execute([$id]);
}
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

$pid = (int)$product['id'];

$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$pid]);
$extraImages = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.slug, p.short_description, p.price, p.volume_ml, p.main_image
    FROM product_relations pr
    JOIN products p ON p.id = pr.related_product_id
    WHERE pr.product_id = ? AND p.is_active = 1
    ORDER BY p.name ASC
    LIMIT 6
");
$stmt->execute([$pid]);
$related = $stmt->fetchAll();

$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_map('intval', $cart));

function money(float $v): string {
    return number_format($v, 2, ',', '.');
}
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASA Parfums — <?= h($product['name']) ?></title>

    <link rel="stylesheet" href="assets/css/tailwind.css" />
    <link rel="stylesheet" href="assets/css/custom.css" />
</head>

<body class="page-body">

<div class="fixed inset-0 -z-50 pointer-events-none">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 h-[520px] w-[520px] rounded-full blur-[90px] bg-gold-400/10"></div>
    <div class="absolute top-40 -left-24 h-[520px] w-[520px] rounded-full blur-[100px] bg-gold-300/8"></div>
</div>

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
    <a href="products.php" class="hidden sm:inline-flex btn btn-secondary">
        Terug naar producten
    </a>
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

<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-radial-hero"></div>
    </div>

    <div class="mx-auto max-w-7xl px-6 pt-10 pb-6">
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Product</div>
        <h1 class="mt-4 font-display text-4xl md:text-5xl leading-[1.05]">
            <?= h($product['name']) ?> <span class="gold-text">—</span> <span class="gold-text">ASA</span>
        </h1>
        <?php if (!empty($product['short_description'])): ?>
            <p class="mt-4 text-white/75 max-w-2xl"><?= h((string)$product['short_description']) ?></p>
        <?php endif; ?>
        <div class="mt-8 hairline"></div>
    </div>
</section>

<section class="py-10">
    <div class="mx-auto max-w-7xl px-6 grid lg:grid-cols-12 gap-10 items-start">

        <!-- Gallery -->
        <div class="lg:col-span-7">
            <div class="rounded-[28px] overflow-hidden shadow-luxe luxe-ring bg-black/30">
                <?php if (!empty($product['main_image'])): ?>
                    <img src="<?= h((string)$product['main_image']) ?>" class="w-full h-[520px] object-cover" alt="<?= h($product['name']) ?>">
                <?php else: ?>
                    <div class="w-full h-[520px] bg-black/40 flex items-center justify-center text-white/40">Geen foto</div>
                <?php endif; ?>
            </div>

            <?php if ($extraImages): ?>
                <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <?php foreach ($extraImages as $img): ?>
                        <a href="<?= h((string)$img['image_path']) ?>" class="rounded-2xl overflow-hidden luxe-ring bg-black/25 block">
                            <img src="<?= h((string)$img['image_path']) ?>" class="h-24 w-full object-cover hover:scale-[1.03] transition duration-700" alt="">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Buy box -->
        <div class="lg:col-span-5">
            <div class="rounded-[28px] p-6 luxe-ring bg-black/35 shadow-glow relative overflow-hidden">
                <div class="absolute inset-0 bg-gold-sheen opacity-15"></div>

                <div class="relative">
                    <div class="text-xs tracking-[.34em] uppercase text-white/60">Prijs</div>
                    <div class="mt-2 text-3xl font-display gold-text">
                        € <?= h(money((float)($product['price'] ?? 0))) ?>
                    </div>
                    <?php if (!empty($product['volume_ml'])): ?>
                        <div class="mt-2 text-sm text-white/60">Eau de Parfum • <?= (int)$product['volume_ml'] ?>ml</div>
                    <?php endif; ?>

                    <?php if (!empty($product['description'])): ?>
                        <p class="mt-6 text-sm text-white/75 leading-relaxed">
                            <?= nl2br(h((string)$product['description'])) ?>
                        </p>
                    <?php else: ?>
                        <p class="mt-6 text-sm text-white/70">
                            Luxe geur met een modern karakter — clean opening, warme dry down, premium presence.
                        </p>
                    <?php endif; ?>

                    <form method="post" class="mt-7 grid gap-3">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= (int)$pid ?>">

                        <label class="text-sm text-white/80">
                            Aantal
                            <select name="qty" class="mt-2 input-field">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </label>

                        <button class="btn btn-primary btn-md btn-block shadow-glow">
                            Voeg toe aan winkelwagen
                        </button>

                        <a href="cart.php" class="btn btn-secondary btn-md btn-block">
                            Bekijk winkelwagen
                        </a>

                        <p class="text-xs text-white/45">
                            Premium verpakking, snelle levering.
                        </p>
                    </form>
                </div>
            </div>
            <?php if (!empty($product['ingredients']) || !empty($product['allergens'])): ?>
                <div class="mt-6 rounded-2xl p-5 luxe-ring bg-black/25">
                    <div class="text-m tracking-[.34em] uppercase text-white/60">Productinformatie</div>

                    <?php if (!empty($product['ingredients'])): ?>
                        <div class="mt-4">
                            <div class="text-xs tracking-[.28em] uppercase text-white/50">Ingrediënten</div>
                            <div class="mt-2 text-sm text-white/75 leading-relaxed whitespace-pre-line">
                                <?= h((string)$product['ingredients']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['allergens'])): ?>
                        <div class="mt-5">
                            <div class="text-xs tracking-[.28em] uppercase text-white/50">Allergenen</div>
                            <div class="mt-2 text-sm text-white/75 leading-relaxed whitespace-pre-line">
                                <?= h((string)$product['allergens']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($related): ?>
    <section class="pb-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex items-end justify-between gap-6 flex-wrap">
                <div>
                    <div class="text-xs tracking-[.34em] uppercase text-white/60">Aanraders</div>
                    <h2 class="mt-3 font-display text-3xl">Gerelateerde producten</h2>
                </div>
                <a href="products.php" class="btn btn-secondary btn-lg">
                    Bekijk alles
                </a>
            </div>

            <div class="mt-8 grid md:grid-cols-3 gap-6">
                <?php foreach ($related as $rp): ?>
                    <article class="group rounded-[28px] overflow-hidden luxe-ring bg-black/35 shadow-glow">
                        <div class="relative h-[280px]">
                            <?php if (!empty($rp['main_image'])): ?>
                                <img src="<?= h((string)$rp['main_image']) ?>" class="h-full w-full object-cover group-hover:scale-[1.03] transition duration-700" alt="<?= h($rp['name']) ?>">
                            <?php else: ?>
                                <div class="h-full w-full bg-black/40 flex items-center justify-center text-white/40">Geen foto</div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/15 to-transparent"></div>
                        </div>
                        <div class="p-6">
                            <h3 class="font-display text-2xl gold-text"><?= h($rp['name']) ?></h3>
                            <p class="mt-2 text-sm text-white/75"><?= h((string)($rp['short_description'] ?? '')) ?></p>
                            <div class="mt-5 flex items-center justify-between">
                                <div class="text-white/70 text-sm">
                                    € <?= h(money((float)($rp['price'] ?? 0))) ?>
                                    <?php if (!empty($rp['volume_ml'])): ?>
                                        <span class="text-white/40">/ <?= (int)$rp['volume_ml'] ?>ml</span>
                                    <?php endif; ?>
                                </div>
                                <a href="product.php?slug=<?= urlencode((string)$rp['slug']) ?>"
                                   class="btn btn-primary">
                                    Bekijk
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<footer class="py-14 border-t border-white/5">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mt-8 flex flex-wrap items-center justify-between gap-4 text-xs text-white/45">
            <div>© <?= date('Y') ?> ASA Parfums. All rights reserved.</div>
            <div class="flex gap-4">
                <a class="hover:text-white transition" href="products.php">Producten</a>
                <a class="hover:text-white transition" href="cart.php">Winkelwagen</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
