<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    $byId = [];
    foreach ($products as $p) {
        $byId[(int)$p['id']] = $p;
    }

    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if (!isset($byId[$pid])) {
            continue;
        }
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

$shipping = ($subtotal > 75.0 || $subtotal == 0.0) ? 0.0 : 4.95;
$total = $subtotal + $shipping;

$navHomeHref = '/';
$navPricesHref = '/prijzen.php';
$navContactHref = '/#contact';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Winkelwagen | Mila Beauty</title>

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
        <h1 class="font-serif text-4xl md:text-5xl leading-tight text-brandText">Jouw <span class="heading-highlight">winkelwagen</span></h1>
        <p class="mt-3 max-w-2xl text-brandText/80">
            Controleer je selectie, pas aantallen aan en ga door wanneer je klaar bent om af te rekenen.
        </p>

        <div class="mt-6 space-y-3">
            <?php if ($added): ?>
                <div class="rounded-2xl bg-brandPinkSoft/60 px-5 py-3 text-sm font-semibold">Toegevoegd aan winkelwagen ✅</div>
            <?php endif; ?>

            <?php if ($updated): ?>
                <div class="rounded-2xl bg-brandPinkSoft/60 px-5 py-3 text-sm font-semibold">Winkelwagen bijgewerkt ✅</div>
            <?php endif; ?>

            <?php if ($invalid): ?>
                <div class="rounded-2xl bg-red-100 px-5 py-3 text-sm font-semibold text-red-700">Sommige invoer was ongeldig en is genegeerd.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="pb-16">
    <div class="mx-auto max-w-container px-4 grid gap-8 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="rounded-2xl bg-white shadow-card p-6">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="font-semibold text-brandText">Items (<?= (int)$cartCount ?>)</div>
                    <?php if ($cartCount > 0): ?>
                        <form method="post">
                            <input type="hidden" name="action" value="clear">
                            <button class="rounded-full border border-red-300 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" onclick="return confirm('Winkelwagen leegmaken?');">
                                Leegmaken
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if ($cartCount === 0): ?>
                    <div class="mt-6 rounded-xl bg-brandBg px-4 py-4 text-sm text-brandText/70">
                        Je winkelwagen is leeg. <a class="font-semibold text-brandAccent" href="products.php">Bekijk producten</a>.
                    </div>
                <?php else: ?>
                    <form method="post" class="mt-6 space-y-4">
                        <input type="hidden" name="action" value="update">

                        <?php foreach ($items as $it): ?>
                            <div class="rounded-2xl border border-black/5 bg-brandBg/60 p-4 flex gap-4 items-center flex-wrap">
                                <a href="product.php?slug=<?= urlencode((string)$it['slug']) ?>" class="h-20 w-20 rounded-xl overflow-hidden bg-white shadow-card flex items-center justify-center">
                                    <?php if (!empty($it['main_image'])): ?>
                                        <img src="<?= h((string)$it['main_image']) ?>" class="h-full w-full object-cover" alt="">
                                    <?php else: ?>
                                        <span class="text-xs text-brandText/50">Geen foto</span>
                                    <?php endif; ?>
                                </a>

                                <div class="flex-1 min-w-[220px]">
                                    <a class="font-serif text-xl text-brandText" href="product.php?slug=<?= urlencode((string)$it['slug']) ?>">
                                        <?= h($it['name']) ?>
                                    </a>
                                    <div class="mt-1 text-sm text-brandText/70">
                                        € <?= h(money($it['price'])) ?>
                                        <?php if (!empty($it['volume_ml'])): ?>
                                            <span class="text-brandText/50">/ <?= (int)$it['volume_ml'] ?>ml</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2 text-xs text-brandText/60">
                                        Subtotaal: € <?= h(money($it['line'])) ?>
                                    </div>
                                </div>

                                <div class="flex items-end gap-3">
                                    <label class="text-xs font-semibold text-brandText/70">
                                        Aantal
                                        <input
                                            type="number"
                                            min="0"
                                            max="99"
                                            name="items[<?= (int)$it['id'] ?>]"
                                            value="<?= (int)$it['qty'] ?>"
                                            class="mt-2 w-20 rounded-xl border border-black/10 bg-white px-3 py-2 text-sm"
                                        />
                                    </label>

                                    <button
                                        type="submit"
                                        name="remove_id"
                                        value="<?= (int)$it['id'] ?>"
                                        class="rounded-2xl border border-red-300 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50"
                                        onclick="return confirm('Item verwijderen?');"
                                    >
                                        Verwijder
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="pt-2 flex flex-wrap gap-3">
                            <button class="btn-primary" type="submit">Update winkelwagen</button>
                            <a href="products.php" class="inline-flex items-center justify-center rounded-2xl border border-brandAccent px-6 py-2 font-semibold text-brandAccent transition hover:bg-brandAccent/10">Verder shoppen</a>
                        </div>

                        <p class="text-xs text-brandText/60">
                            Tip: zet aantal op 0 om te verwijderen.
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="rounded-2xl bg-white shadow-card p-6">
                <div class="font-semibold text-brandText">Overzicht</div>

                <div class="mt-5 space-y-3 text-sm text-brandText/80">
                    <div class="flex items-center justify-between">
                        <span>Subtotaal</span>
                        <span>€ <?= h(money($subtotal)) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Verzendkosten</span>
                        <span><?= $shipping == 0.0 ? 'Gratis' : '€ ' . h(money($shipping)) ?></span>
                    </div>
                    <div class="border-t border-black/5 pt-4 flex items-center justify-between font-semibold text-brandText">
                        <span>Totaal</span>
                        <span>€ <?= h(money($total)) ?></span>
                    </div>

                    <div class="mt-4 rounded-xl bg-brandBg px-4 py-3 text-xs">
                        Gratis verzending vanaf € 75.
                    </div>
                </div>

                <?php if ($cartCount === 0): ?>
                    <div class="mt-6 rounded-2xl bg-brandPinkSoft/60 px-4 py-3 text-center text-sm font-semibold text-brandText/70">
                        Doorgaan naar afrekenen
                    </div>
                <?php else: ?>
                    <a href="checkout.php" class="btn-primary mt-6 w-full justify-center">Doorgaan naar afrekenen</a>
                <?php endif; ?>

                <div class="mt-4 text-xs text-brandText/60">
                    Afrekenen via Mollie (iDEAL, creditcard, etc.).
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-white shadow-card p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Service</div>
                    <div class="mt-2 text-sm text-brandText/80">Persoonlijk advies bij vragen.</div>
                </div>
                <div class="rounded-2xl bg-white shadow-card p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Levering</div>
                    <div class="mt-2 text-sm text-brandText/80">Snelle verzending vanuit onze studio.</div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
