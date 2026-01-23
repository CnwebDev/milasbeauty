<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();
$token = trim((string)($_GET['token'] ?? ''));
$order = null;
$items = [];

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_token = ? LIMIT 1');
    $stmt->execute([$token]);
    $order = $stmt->fetch();

    if ($order) {
        $itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$order['id']]);
        $items = $itemsStmt->fetchAll();
    }
}

$status = $order['payment_status'] ?? null;
$requestedStatus = trim((string)($_GET['status'] ?? ''));
$messageTitle = 'Bestelling ontvangen';
$messageBody = 'We controleren de status van je betaling.';
$statusTone = 'text-brandText';

if (!$order) {
    $messageTitle = 'Bestelling niet gevonden';
    $messageBody = 'We kunnen je bestelling niet terugvinden. Neem contact op met onze support.';
    $statusTone = 'text-red-600';
} elseif ($status === 'paid') {
    $messageTitle = 'Betaling gelukt';
    $messageBody = 'Bedankt! Je bestelling is betaald en wordt voorbereid voor verzending.';
    $statusTone = 'text-emerald-600';
    $_SESSION['cart'] = [];
} elseif (in_array($status, ['canceled', 'failed', 'expired'], true) || $requestedStatus === 'cancelled') {
    $messageTitle = 'Betaling mislukt';
    $messageBody = 'Je betaling is geannuleerd of mislukt. Probeer het opnieuw of neem contact op.';
    $statusTone = 'text-red-600';
}

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
    <title>Bestelstatus | Mila Beauty</title>

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

<main class="py-12">
    <div class="mx-auto max-w-container px-4">
        <section class="rounded-2xl bg-white shadow-card p-8">
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="inline-flex items-center rounded-full bg-brandPinkSoft/70 px-3 py-1 font-semibold">Bestelstatus</span>
                <?php if ($order): ?>
                    <span class="inline-flex items-center rounded-full bg-brandBg px-3 py-1 text-xs font-semibold">
                        Bestelling #<?= (int)$order['id'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="mt-4 font-serif text-3xl md:text-4xl <?= $statusTone ?>">
                <?= h($messageTitle) ?>
            </h1>
            <p class="mt-3 max-w-2xl text-sm md:text-base text-brandText/80">
                <?= h($messageBody) ?>
            </p>

            <?php if ($order): ?>
                <div class="mt-8 grid gap-6 lg:grid-cols-3">
                    <div class="rounded-xl border border-black/5 bg-brandBg/60 p-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Bestelling</div>
                        <div class="mt-3 text-lg font-semibold text-brandText">#<?= (int)$order['id'] ?></div>
                        <div class="mt-1 text-sm text-brandText/70">Status: <?= h($order['payment_status'] ?? 'open') ?></div>
                    </div>
                    <div class="rounded-xl border border-black/5 bg-brandBg/60 p-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Contact</div>
                        <div class="mt-3 text-sm text-brandText/80">
                            Vragen? Mail naar <a class="font-semibold text-brandAccent" href="mailto:info@beautybymilasujeiry.nl">info@beautybymilasujeiry.nl</a>.
                        </div>
                    </div>
                    <div class="rounded-xl border border-black/5 bg-brandBg/60 p-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Levering</div>
                        <div class="mt-3 text-sm text-brandText/80">We houden je op de hoogte via e-mail.</div>
                    </div>
                </div>

                <div class="mt-8 rounded-2xl border border-black/5 bg-white p-6">
                    <div class="text-sm font-semibold text-brandText">Items</div>
                    <div class="mt-4 space-y-3 text-sm text-brandText/80">
                        <?php foreach ($items as $item): ?>
                            <div class="flex items-center justify-between gap-4">
                                <span><?= h($item['product_name']) ?> × <?= (int)$item['qty'] ?></span>
                                <span class="font-semibold">€ <?= h(money((float)$item['line_total'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-5 border-t border-black/5 pt-4 text-sm text-brandText/80">
                        <div class="flex items-center justify-between">
                            <span>Subtotaal</span>
                            <span>€ <?= h(money((float)$order['subtotal'])) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Verzendkosten</span>
                            <span>€ <?= h(money((float)$order['shipping'])) ?></span>
                        </div>
                        <div class="flex items-center justify-between font-semibold text-brandText">
                            <span>Totaal</span>
                            <span>€ <?= h(money((float)$order['total'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="products.php" class="btn-primary">Terug naar producten</a>
                <a href="cart.php" class="inline-flex items-center justify-center rounded-2xl border border-brandAccent px-6 py-2 font-semibold text-brandAccent transition hover:bg-brandAccent/10">
                    Bekijk winkelwagen
                </a>
            </div>
        </section>
    </div>
</main>
</body>
</html>
