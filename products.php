<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$navHomeHref = '/';
$navPricesHref = '/prijzen.php';
$navContactHref = '/#contact';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Producten | Mila Beauty</title>

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

<section class="section-hero py-12">
    <div class="mx-auto max-w-container px-4">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 md:items-center">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-brandPinkSoft/70 px-4 py-1 text-sm font-semibold">
                    Shop Mila Beauty
                </div>

                <h1 class="mt-4 font-serif text-4xl md:text-5xl leading-tight text-brandText">
                    Onze <span class="heading-highlight">Producten</span>
                </h1>

                <p class="mt-4 max-w-xl text-brandText/80">
                    Ontdek onze geselecteerde beauty & verzorgingsproducten. Kies jouw favoriet en voeg toe aan je winkelwagen.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a class="btn-primary" href="cart.php">Bekijk winkelwagen</a>
                    <a class="inline-flex items-center justify-center rounded-2xl border border-brandAccent px-6 py-2 font-semibold text-brandAccent transition hover:bg-brandAccent/10" href="https://salonkee.nl/salon/milas-beauty">Afspraak maken</a>
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-card p-5">
                <form method="get" class="space-y-3">
                    <label class="text-sm font-semibold" for="productSearch">Zoek een product</label>
                    <input
                        id="productSearch"
                        name="q"
                        value="<?= h($q) ?>"
                        type="text"
                        placeholder="Bijv. serum, crème, olie..."
                        class="w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3 outline-none focus:ring-2 focus:ring-brandPink/40"
                    />
                    <div class="flex items-center justify-between text-xs text-brandText/60">
                        <span><?= count($products) ?> resultaten</span>
                        <?php if ($cartCount > 0): ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-brandPinkSoft/60 px-3 py-1 font-semibold">
                                <?= (int)$cartCount ?> item(s) in je cart
                            </span>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="pb-16">
    <div class="mx-auto max-w-container px-4">
        <?php if (!$products): ?>
            <div class="rounded-2xl bg-white p-8 shadow-card text-brandText/70">
                Geen producten gevonden. Probeer een andere zoekterm.
            </div>
        <?php else: ?>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($products as $p): ?>
                    <article class="rounded-2xl bg-white shadow-card overflow-hidden flex flex-col">
                        <div class="h-56 w-full overflow-hidden">
                            <?php if (!empty($p['main_image'])): ?>
                                <img src="<?= h((string)$p['main_image']) ?>" class="h-full w-full object-cover transition duration-300 hover:scale-105" alt="<?= h($p['name']) ?>">
                            <?php else: ?>
                                <div class="h-full w-full bg-brandBg flex items-center justify-center text-brandText/50">Geen foto</div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 p-6">
                            <h3 class="font-serif text-2xl text-brandText">
                                <?= h($p['name']) ?>
                            </h3>
                            <p class="mt-2 text-sm text-brandText/70">
                                <?= h((string)($p['short_description'] ?? '')) ?>
                            </p>

                            <div class="mt-5 flex items-center justify-between">
                                <div class="text-sm text-brandText/80">
                                    € <?= h(number_format((float)($p['price'] ?? 0), 2, ',', '.')) ?>
                                    <?php if (!empty($p['volume_ml'])): ?>
                                        <span class="text-brandText/50">/ <?= (int)$p['volume_ml'] ?>ml</span>
                                    <?php endif; ?>
                                </div>
                                <a href="product.php?slug=<?= urlencode((string)$p['slug']) ?>" class="btn-primary">Bekijk</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
</body>
</html>
