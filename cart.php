<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = db();

$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$cart = &$_SESSION['cart'];

function normalize_qty(mixed $value, int $min, int $max): ?int {
    if (is_array($value) || $value === null) {
        return null;
    }
    $value = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => $min, 'max_range' => $max],
    ]);
    if ($value === false) {
        return null;
    }
    return (int)$value;
}

// Actions: add/remove/update/clear
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $invalidInput = false;

    $removeRaw = $_POST['remove_id'] ?? null;
    $removeId = normalize_qty($removeRaw, 1, PHP_INT_MAX);
    if ($removeRaw !== null) {
        if ($removeId === null) {
            $invalidInput = true;
        } else {
            unset($cart[$removeId]);
        }
        header("Location: cart.php" . ($invalidInput ? "?invalid=1" : ""));
        exit;
    }

    if ($action === 'add') {
        $pid = normalize_qty($_POST['product_id'] ?? null, 1, PHP_INT_MAX);
        $qty = normalize_qty($_POST['qty'] ?? 1, 1, 99);
        if ($pid === null) {
            $invalidInput = true;
        } else {
            if ($qty === null) {
                $invalidInput = true;
                $qty = 1;
            }
            $cart[$pid] = (int)($cart[$pid] ?? 0) + $qty;
        }
        header("Location: cart.php" . ($invalidInput ? "?invalid=1" : ""));
        exit;
    }

    if ($action === 'update') {
        $items = $_POST['items'] ?? [];
        if (is_array($items)) {
            foreach ($items as $pid => $qty) {
                $pid = normalize_qty($pid, 1, PHP_INT_MAX);
                $qty = normalize_qty($qty, 0, 99);
                if ($pid === null) {
                    $invalidInput = true;
                    continue;
                }
                if ($qty === null) {
                    $invalidInput = true;
                    continue;
                }
                if ($qty === 0) {
                    unset($cart[$pid]);
                } else {
                    $cart[$pid] = $qty;
                }
            }
        } else {
            $invalidInput = true;
        }
        $query = $invalidInput ? "?updated=1&invalid=1" : "?updated=1";
        header("Location: cart.php" . $query);
        exit;
    }

    if ($action === 'remove') {
        $pid = normalize_qty($_POST['product_id'] ?? null, 1, PHP_INT_MAX);
        if ($pid === null) {
            $invalidInput = true;
        } else {
            unset($cart[$pid]);
        }
        header("Location: cart.php" . ($invalidInput ? "?invalid=1" : ""));
        exit;
    }

    if ($action === 'clear') {
        $cart = [];
        header("Location: cart.php");
        exit;
    }
}

$added = (int)($_GET['added'] ?? 0) === 1;
$updated = (int)($_GET['updated'] ?? 0) === 1;
$invalid = (int)($_GET['invalid'] ?? 0) === 1;

$cartCount = array_sum(array_map('intval', $cart));

$items = [];
$subtotal = 0.0;

if ($cart) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT id, name, slug, price, volume_ml, main_image FROM products WHERE id IN ($placeholders) AND is_active=1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    // index by id for easy mapping
    $byId = [];
    foreach ($products as $p) $byId[(int)$p['id']] = $p;

    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if (!isset($byId[$pid])) continue;
        $p = $byId[$pid];

        $price = (float)($p['price'] ?? 0);
        $line = $price * $qty;
        $subtotal += $line;

        $items[] = [
            'id' => $pid,
            'name' => $p['name'],
            'slug' => $p['slug'],
            'price' => $price,
            'volume_ml' => $p['volume_ml'],
            'main_image' => $p['main_image'],
            'qty' => $qty,
            'line' => $line,
        ];
    }
}

function money(float $v): string {
    return number_format($v, 2, ',', '.');
}

// Simple shipping rule (demo)
$shipping = ($subtotal > 75.0 || $subtotal == 0.0) ? 0.0 : 4.95;
$total = $subtotal + $shipping;
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASA Parfums — Winkelwagen</title>

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
        Verder shoppen
    </a>
    <div class="badge badge-gold">
        Items: <?= (int)$cartCount ?>
    </div>
</div>
<?php
$navbarRight = ob_get_clean();

require __DIR__ . '/partials/navbar.php';
?>

<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-radial-hero"></div>
    </div>

    <div class="mx-auto max-w-7xl px-6 pt-12 pb-8">
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Winkelwagen</div>
        <h1 class="mt-4 font-display text-4xl md:text-5xl leading-[1.05]">
            Jouw <span class="gold-text">winkelwagen</span>
        </h1>
        <p class="mt-4 text-white/75 max-w-2xl">
            Controleer je selectie, pas aantallen aan en ga door wanneer je klaar bent om af te rekenen.
        </p>

        <?php if ($added): ?>
            <div class="mt-6 rounded-[24px] p-5 luxe-ring bg-gold-500/10 text-white/80">
                Toegevoegd aan winkelwagen ✅
            </div>
        <?php endif; ?>

        <?php if ($updated): ?>
            <div class="mt-6 rounded-[24px] p-5 luxe-ring bg-gold-500/10 text-white/80">
                Winkelwagen bijgewerkt ✅
            </div>
        <?php endif; ?>

        <?php if ($invalid): ?>
            <div class="mt-6 rounded-[24px] p-5 luxe-ring bg-red-500/10 text-white/80">
                Sommige invoer was ongeldig en is genegeerd.
            </div>
        <?php endif; ?>

        <div class="mt-8 hairline"></div>
    </div>
</section>

<section class="pb-20">
    <div class="mx-auto max-w-7xl px-6 grid lg:grid-cols-12 gap-8 items-start">

        <!-- Items -->
        <div class="lg:col-span-7">
            <div class="rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="text-sm font-medium">Items</div>

                    <?php if ($cartCount > 0): ?>
                        <form method="post">
                            <input type="hidden" name="action" value="clear">
                            <button class="rounded-full px-4 py-2 text-xs luxe-ring bg-red-500/10 hover:bg-red-500/15 transition text-white/75"
                                    onclick="return confirm('Winkelwagen leegmaken?');">
                                Leegmaken
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if ($cartCount === 0): ?>
                    <div class="mt-6 rounded-2xl p-5 luxe-ring bg-black/30 text-white/70">
                        Je winkelwagen is leeg. <a class="underline text-white" href="products.php">Bekijk producten</a>.
                    </div>
                <?php else: ?>
                    <form method="post" class="mt-6 space-y-4">
                        <input type="hidden" name="action" value="update">

                        <?php foreach ($items as $it): ?>
                            <div class="rounded-2xl p-4 luxe-ring bg-black/30 flex gap-4 items-center flex-wrap">
                                <a href="product.php?slug=<?= urlencode((string)$it['slug']) ?>"
                                   class="h-20 w-20 rounded-2xl luxe-ring overflow-hidden bg-black/40 shrink-0 flex items-center justify-center">
                                    <?php if (!empty($it['main_image'])): ?>
                                        <img src="<?= h((string)$it['main_image']) ?>" class="h-full w-full object-cover" alt="">
                                    <?php else: ?>
                                        <span class="text-white/40 text-xs">Geen foto</span>
                                    <?php endif; ?>
                                </a>

                                <div class="flex-1 min-w-[220px]">
                                    <a class="font-display text-xl gold-text" href="product.php?slug=<?= urlencode((string)$it['slug']) ?>">
                                        <?= h($it['name']) ?>
                                    </a>
                                    <div class="mt-1 text-sm text-white/60">
                                        € <?= h(money($it['price'])) ?>
                                        <?php if (!empty($it['volume_ml'])): ?>
                                            <span class="text-white/40">/ <?= (int)$it['volume_ml'] ?>ml</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2 text-xs text-white/45">
                                        Subtotaal: € <?= h(money($it['line'])) ?>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <label class="text-xs text-white/60">
                                        Aantal
                                        <input
                                            type="number"
                                            min="0"
                                            max="99"
                                            name="items[<?= (int)$it['id'] ?>]"
                                            value="<?= (int)$it['qty'] ?>"
                                            class="mt-2 w-20 input-field text-white/85"
                                        />
                                    </label>

                                    <button
                                        type="submit"
                                        name="remove_id"
                                        value="<?= (int)$it['id'] ?>"
                                        class="mt-6 btn btn-danger text-xs"
                                        onclick="return confirm('Item verwijderen?');"
                                    >
                                        Verwijder
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="pt-2 flex gap-3 flex-wrap">
                            <button class="btn btn-primary btn-lg">
                                Update winkelwagen
                            </button>
                            <a href="products.php" class="btn btn-secondary btn-lg">
                                Verder shoppen
                            </a>
                        </div>

                        <p class="text-xs text-white/45">
                            Tip: zet aantal op 0 om te verwijderen.
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary -->
        <div class="lg:col-span-5">
            <div class="rounded-[28px] p-6 luxe-ring bg-black/35 shadow-glow relative overflow-hidden">
                <div class="absolute inset-0 bg-gold-sheen opacity-15"></div>
                <div class="relative">
                    <div class="text-sm font-medium">Overzicht</div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between text-white/75">
                            <span>Subtotaal</span>
                            <span>€ <?= h(money($subtotal)) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-white/75">
                            <span>Verzendkosten</span>
                            <span><?= $shipping == 0.0 ? 'Gratis' : '€ ' . h(money($shipping)) ?></span>
                        </div>
                        <div class="hairline my-4"></div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-white/90">Totaal</span>
                            <span class="font-display text-2xl gold-text">€ <?= h(money($total)) ?></span>
                        </div>

                        <div class="mt-5 rounded-2xl p-4 luxe-ring bg-black/25 text-xs text-white/60">
                            Gratis verzending vanaf € 29.
                        </div>
                    </div>

                    <?php if ($cartCount === 0): ?>
                        <div class="btn btn-primary btn-md btn-block btn-disabled shadow-glow mt-6 text-center">
                            Doorgaan naar afrekenen
                        </div>
                    <?php else: ?>
                        <a
                            href="checkout.php"
                            class="btn btn-primary btn-md btn-block shadow-glow mt-6 inline-flex items-center justify-center"
                        >
                            Doorgaan naar afrekenen
                        </a>
                    <?php endif; ?>

                    <div class="mt-4 text-xs text-white/45">
                        Afrekenen met iDeal Voeg Mollie/Stripe later toe.
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl p-5 luxe-ring bg-black/25">
                    <div class="text-xs tracking-[.28em] uppercase text-white/60">Packaging</div>
                    <div class="mt-2 text-sm text-white/80">Premium unboxing feel.</div>
                </div>
                <div class="rounded-2xl p-5 luxe-ring bg-black/25">
                    <div class="text-xs tracking-[.28em] uppercase text-white/60">Support</div>
                    <div class="mt-2 text-sm text-white/80">Snelle service & advies.</div>
                </div>
            </div>
        </div>

    </div>
</section>

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
