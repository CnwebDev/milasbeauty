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
$statusTone = 'text-white/80';

if (!$order) {
    $messageTitle = 'Bestelling niet gevonden';
    $messageBody = 'We kunnen je bestelling niet terugvinden. Neem contact op met onze support.';
    $statusTone = 'text-red-200';
} elseif ($status === 'paid') {
    $messageTitle = 'Betaling gelukt';
    $messageBody = 'Bedankt! Je bestelling is betaald en wordt voorbereid voor verzending.';
    $statusTone = 'text-emerald-200';
    $_SESSION['cart'] = [];
} elseif (in_array($status, ['canceled', 'failed', 'expired'], true) || $requestedStatus === 'cancelled') {
    $messageTitle = 'Betaling mislukt';
    $messageBody = 'Je betaling is geannuleerd of mislukt. Probeer het opnieuw of neem contact op.';
    $statusTone = 'text-red-200';
}

function money(float $v): string {
    return number_format($v, 2, ',', '.');
}
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASA Parfums — Bestelstatus</title>

    <link rel="stylesheet" href="assets/css/tailwind.css" />
    <link rel="stylesheet" href="assets/css/custom.css" />
</head>

<body class="page-body">
<div class="fixed inset-0 -z-50 pointer-events-none">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 h-[520px] w-[520px] rounded-full blur-[90px] bg-gold-400/10"></div>
    <div class="absolute top-40 -left-24 h-[520px] w-[520px] rounded-full blur-[100px] bg-gold-300/8"></div>
</div>

<?php
$navbarContainerClass = 'max-w-6xl';
$navbarLogo = <<<'HTML'
<a href="index.php" class="group flex items-center gap-3">
    <div class="h-10 w-10 rounded-2xl shadow-glow bg-black/30 flex items-center justify-center">
        <span class="font-display text-white">A</span>
    </div>
    <div class="leading-none">
        <div class="font-display tracking-[.22em] text-sm text-white">ASA</div>
        <div class="text-[11px] tracking-[.34em] text-white/60 -mt-1">PARFUMS</div>
    </div>
</a>
HTML;

$navbarRight = <<<'HTML'
<a href="products.php" class="btn btn-ghost">Verder winkelen</a>
HTML;

require __DIR__ . '/partials/navbar.php';
?>

<main class="mx-auto max-w-6xl px-6 py-14">
    <section class="rounded-[28px] p-8 bg-black/25 shadow-glow border border-white/5">
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Bestelstatus</div>
        <h1 class="mt-3 font-display text-3xl md:text-4xl <?= $statusTone ?>"><?= h($messageTitle) ?></h1>
        <p class="mt-3 text-white/70"><?= h($messageBody) ?></p>

        <?php if ($order): ?>
            <div class="mt-8 grid gap-6 text-sm text-white/70">
                <div class="rounded-2xl p-5 bg-black/30 border border-white/10">
                    <div class="text-xs text-white/50 uppercase tracking-[.28em]">Bestelling</div>
                    <div class="mt-2 text-white/85">#<?= (int)$order['id'] ?></div>
                    <div class="mt-1 text-white/50">Status: <?= h($order['payment_status'] ?? 'open') ?></div>
                </div>
            </div>

            <div class="mt-8 rounded-2xl p-5 bg-black/30 border border-white/10">
                <div class="text-xs text-white/50 uppercase tracking-[.28em]">Items</div>
                <div class="mt-4 space-y-3 text-sm text-white/70">
                    <?php foreach ($items as $item): ?>
                        <div class="flex items-center justify-between">
                            <span><?= h($item['product_name']) ?> × <?= (int)$item['qty'] ?></span>
                            <span>€ <?= h(money((float)$item['line_total'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 border-t border-white/10 pt-4 text-sm text-white/70">
                    <div class="flex items-center justify-between">
                        <span>Subtotaal</span>
                        <span>€ <?= h(money((float)$order['subtotal'])) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Verzendkosten</span>
                        <span>€ <?= h(money((float)$order['shipping'])) ?></span>
                    </div>
                    <div class="flex items-center justify-between font-medium text-white/90">
                        <span>Totaal</span>
                        <span>€ <?= h(money((float)$order['total'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="products.php" class="btn btn-primary btn-lg">
                Terug naar producten
            </a>
            <a href="cart.php" class="btn btn-secondary btn-lg">
                Bekijk winkelwagen
            </a>
        </div>
    </section>
</main>
</body>
</html>
