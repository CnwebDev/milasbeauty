<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$stmt = $pdo->prepare("SELECT size_label FROM product_sizes WHERE product_id=? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$pid]);
$sizes = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT color_label FROM product_colors WHERE product_id=? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$pid]);
$colors = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

$navHomeHref = '/';
$navPricesHref = '/prijzen.php';
$navContactHref = '/#contact';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($product['name']) ?> | Mila Beauty</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Taviraj:wght@600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandPink: "#ff80d5",
                        brandPinkSoft: "#ffccef",
                        brandBg: "#f4f3ef",
                        brandAccent: "#CA9A8E",
                        brandText: "#404447",
                        headerPink: "rgba(255,128,213,0.61)",
                    },
                    fontFamily: {
                        sans: ["Montserrat", "ui-sans-serif", "system-ui"],
                        serif: ["Taviraj", "ui-serif", "Georgia"],
                    },
                    boxShadow: {
                        card: "4px 4px 8px 0px rgba(0,0,0,0.1)",
                        img: "4px 4px 20px 0px rgba(132,153,148,0.5)",
                        insetGlow: "0 0 10px 0 #CA9A8E inset, 0 0 20px 2px #CA9A8E",
                    },
                    borderRadius: {
                        fancy: "20px 0px 20px 0px",
                        fancyImg: "100px 0px 0px 0px",
                    },
                    maxWidth: { container: "1220px" },
                }
            }
        }
    </script>

    <link rel="stylesheet" href="/assets/css/theme.css" />
</head>

<body class="text-brandText font-sans">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<section class="section-hero py-10">
    <div class="mx-auto max-w-container px-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-brandPinkSoft/70 px-4 py-1 text-sm font-semibold">Product</div>
                <h1 class="mt-4 font-serif text-4xl md:text-5xl leading-tight text-brandText">
                    <?= h($product['name']) ?>
                </h1>
                <?php if (!empty($product['short_description'])): ?>
                    <p class="mt-3 max-w-2xl text-brandText/80">
                        <?= h((string)$product['short_description']) ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <a href="products.php" class="inline-flex items-center justify-center rounded-2xl border border-brandAccent px-5 py-2 font-semibold text-brandAccent transition hover:bg-brandAccent/10">Terug naar producten</a>
                <a href="cart.php" class="btn-primary">Winkelwagen (<?= (int)$cartCount ?>)</a>
            </div>
        </div>
    </div>
</section>

<section class="pb-16">
    <div class="mx-auto max-w-container px-4 grid gap-10 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="rounded-2xl overflow-hidden shadow-img bg-white">
                <?php if (!empty($product['main_image'])): ?>
                    <img src="<?= h((string)$product['main_image']) ?>" class="h-[480px] w-full object-cover" alt="<?= h($product['name']) ?>">
                <?php else: ?>
                    <div class="h-[480px] w-full bg-brandBg flex items-center justify-center text-brandText/50">Geen foto</div>
                <?php endif; ?>
            </div>

            <?php if ($extraImages): ?>
                <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <?php foreach ($extraImages as $img): ?>
                        <a href="<?= h((string)$img['image_path']) ?>" class="rounded-xl overflow-hidden shadow-card bg-white block">
                            <img src="<?= h((string)$img['image_path']) ?>" class="h-24 w-full object-cover hover:scale-105 transition duration-300" alt="">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-5">
            <div class="rounded-2xl bg-white shadow-card p-6">
                <div class="text-sm font-semibold text-brandText/70">Prijs</div>
                <div class="mt-2 text-3xl font-serif text-brandText">€ <?= h(money((float)($product['price'] ?? 0))) ?></div>
                <?php if (!empty($product['volume_ml'])): ?>
                    <div class="mt-1 text-sm text-brandText/60"><?= (int)$product['volume_ml'] ?>ml</div>
                <?php endif; ?>

                <?php if (!empty($product['description'])): ?>
                    <p class="mt-5 text-sm text-brandText/80 leading-relaxed">
                        <?= nl2br(h((string)$product['description'])) ?>
                    </p>
                <?php else: ?>
                    <p class="mt-5 text-sm text-brandText/70">
                        Een luxe beauty product dat jouw routine compleet maakt.
                    </p>
                <?php endif; ?>

                <?php if ($sizes || $colors): ?>
                    <div class="mt-5 space-y-4">
                        <?php if ($sizes): ?>
                            <div class="rounded-2xl bg-brandBg/70 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Maten</div>
                                <ul class="mt-3 grid gap-2 text-sm text-brandText/80">
                                    <?php foreach ($sizes as $size): ?>
                                        <li class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-brandAccent"></span>
                                            <?= h((string)$size) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($colors): ?>
                            <div class="rounded-2xl bg-brandBg/70 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Kleuren</div>
                                <ul class="mt-3 grid gap-2 text-sm text-brandText/80">
                                    <?php foreach ($colors as $color): ?>
                                        <li class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-brandAccent"></span>
                                            <?= h((string)$color) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-6 grid gap-4">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?= (int)$pid ?>">

                    <label class="text-sm font-semibold text-brandText/70">
                        Aantal
                        <select name="qty" class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-2">
                            <?php for ($i=1; $i<=5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>

                    <button class="btn-primary" type="submit">
                        Voeg toe aan winkelwagen
                    </button>
                    <a href="cart.php" class="inline-flex items-center justify-center rounded-2xl border border-brandAccent px-6 py-2 font-semibold text-brandAccent transition hover:bg-brandAccent/10">
                        Bekijk winkelwagen
                    </a>

                    <p class="text-xs text-brandText/60">
                        Premium verpakking en snelle levering.
                    </p>
                </form>
            </div>

            <?php if (!empty($product['ingredients']) || !empty($product['allergens'])): ?>
                <div class="mt-6 rounded-2xl bg-white shadow-card p-6">
                    <div class="text-sm font-semibold text-brandText/70">Productinformatie</div>

                    <?php if (!empty($product['ingredients'])): ?>
                        <div class="mt-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Ingrediënten</div>
                            <div class="mt-2 text-sm text-brandText/75 whitespace-pre-line">
                                <?= h((string)$product['ingredients']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['allergens'])): ?>
                        <div class="mt-5">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Allergenen</div>
                            <div class="mt-2 text-sm text-brandText/75 whitespace-pre-line">
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
    <section class="pb-16">
        <div class="mx-auto max-w-container px-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-brandText/60">Aanraders</div>
                    <h2 class="mt-3 font-serif text-3xl">Gerelateerde producten</h2>
                </div>
                <a href="products.php" class="inline-flex items-center justify-center rounded-2xl border border-brandAccent px-5 py-2 font-semibold text-brandAccent transition hover:bg-brandAccent/10">Bekijk alles</a>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($related as $rp): ?>
                    <article class="rounded-2xl bg-white shadow-card overflow-hidden flex flex-col">
                        <div class="h-48 w-full overflow-hidden">
                            <?php if (!empty($rp['main_image'])): ?>
                                <img src="<?= h((string)$rp['main_image']) ?>" class="h-full w-full object-cover transition duration-300 hover:scale-105" alt="<?= h($rp['name']) ?>">
                            <?php else: ?>
                                <div class="h-full w-full bg-brandBg flex items-center justify-center text-brandText/50">Geen foto</div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 p-6">
                            <h3 class="font-serif text-2xl text-brandText"><?= h($rp['name']) ?></h3>
                            <p class="mt-2 text-sm text-brandText/70"><?= h((string)($rp['short_description'] ?? '')) ?></p>
                            <div class="mt-5 flex items-center justify-between">
                                <div class="text-sm text-brandText/80">
                                    € <?= h(money((float)($rp['price'] ?? 0))) ?>
                                    <?php if (!empty($rp['volume_ml'])): ?>
                                        <span class="text-brandText/50">/ <?= (int)$rp['volume_ml'] ?>ml</span>
                                    <?php endif; ?>
                                </div>
                                <a href="product.php?slug=<?= urlencode((string)$rp['slug']) ?>" class="btn-primary">Bekijk</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
</body>
</html>
