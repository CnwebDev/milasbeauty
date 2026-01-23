<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$orderId = (int)($_GET['id'] ?? 0);
$order = null;
$items = [];

if ($orderId > 0) {
    $stmt = $pdo->prepare(
        'SELECT id, order_token, status, payment_status, total, subtotal, shipping, currency,
                first_name, last_name, email, phone, address, zip, city, country, notes,
                paid_at, created_at
         FROM orders WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        $itemsStmt = $pdo->prepare(
            'SELECT product_name, price, qty, line_total, volume_ml, main_image
             FROM order_items WHERE order_id = ? ORDER BY id ASC'
        );
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();
    }
}

function money(float $value): string
{
    return number_format($value, 2, ',', '.');
}

$pageTitle = $order ? ('Bestelling #' . $order['id']) : 'Bestelling niet gevonden';
include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <a class="text-xs uppercase tracking-[.34em] text-brandText/60 hover:text-brandText/80 transition" href="/admin/orders/">← Terug naar bestellingen</a>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">
            <?= $order ? 'Bestelling #' . h((string)$order['id']) : 'Bestelling niet gevonden' ?>
        </h1>
        <?php if ($order): ?>
            <p class="mt-3 text-brandText/70">Bekijk de details, klantgegevens en orderregels.</p>
        <?php else: ?>
            <p class="mt-3 text-brandText/70">Controleer of het ordernummer klopt.</p>
        <?php endif; ?>
    </div>
</div>

<div class="mt-8 hairline"></div>

<?php if (!$order): ?>
    <div class="mt-8 rounded-[24px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-sm text-brandText/70">Deze bestelling bestaat niet (of is verwijderd).</div>
        <div class="mt-4">
            <a class="rounded-2xl px-4 py-2 text-sm border border-black/5 bg-brandBg hover:bg-white transition text-brandText/80" href="/admin/orders/">
                Ga terug naar het overzicht
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="rounded-[24px] p-6 bg-white shadow-card border border-black/5">
            <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Klant & levering</div>
            <div class="mt-4 grid gap-4 md:grid-cols-2 text-sm text-brandText/80">
                <div>
                    <div class="text-xs text-brandText/50 uppercase tracking-[.28em]">Klant</div>
                    <div class="mt-2 font-medium text-brandText">
                        <?= h($order['first_name'] . ' ' . $order['last_name']) ?>
                    </div>
                    <div class="mt-1 text-brandText/70"><?= h($order['email']) ?></div>
                    <?php if (!empty($order['phone'])): ?>
                        <div class="mt-1 text-brandText/70"><?= h((string)$order['phone']) ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="text-xs text-brandText/50 uppercase tracking-[.28em]">Adres</div>
                    <div class="mt-2 text-brandText/70"><?= h($order['address']) ?></div>
                    <div class="text-brandText/70"><?= h($order['zip']) ?> <?= h($order['city']) ?></div>
                    <div class="text-brandText/70"><?= h($order['country']) ?></div>
                </div>
            </div>

            <?php if (!empty($order['notes'])): ?>
                <div class="mt-6 rounded-2xl p-4 bg-brandBg border border-black/5 text-sm text-brandText/70">
                    <div class="text-xs text-brandText/50 uppercase tracking-[.28em]">Notities</div>
                    <p class="mt-2"><?= h((string)$order['notes']) ?></p>
                </div>
            <?php endif; ?>

            <div class="mt-8 text-xs tracking-[.34em] uppercase text-brandText/60">Items</div>
            <div class="mt-4 grid gap-3">
                <?php foreach ($items as $item): ?>
                    <div class="rounded-2xl p-4 bg-brandBg border border-black/5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <?php if (!empty($item['main_image'])): ?>
                                <?php $imagePath = '/' . ltrim((string)$item['main_image'], '/'); ?>
                                <img class="h-16 w-16 rounded-2xl object-cover border border-black/5" src="<?= h($imagePath) ?>" alt="<?= h($item['product_name']) ?>">
                            <?php else: ?>
                                <div class="h-16 w-16 rounded-2xl bg-white border border-black/5 flex items-center justify-center text-xs text-brandText/40">
                                    Geen foto
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="font-medium text-brandText"><?= h($item['product_name']) ?></div>
                                <div class="text-xs text-brandText/50">
                                    <?= (int)$item['qty'] ?> × € <?= h(money((float)$item['price'])) ?>
                                    <?php if (!empty($item['volume_ml'])): ?>
                                        · <?= (int)$item['volume_ml'] ?> ml
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-brandText/80">
                            € <?= h(money((float)$item['line_total'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="rounded-[24px] p-6 bg-white shadow-card border border-black/5">
            <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Orderstatus</div>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-[11px] tracking-[.28em] uppercase rounded-full px-3 py-1 border border-black/5 bg-brandPinkSoft/60 text-brandText/70">
                    <?= h($order['status']) ?>
                </span>
                <span class="text-[11px] tracking-[.28em] uppercase rounded-full px-3 py-1 border border-black/5 bg-brandBg text-brandText/60">
                    <?= h($order['payment_status'] ?? 'onbekend') ?>
                </span>
            </div>

            <div class="mt-6 text-sm text-brandText/70 space-y-3">
                <div>
                    <div class="text-xs text-brandText/50 uppercase tracking-[.28em]">Order token</div>
                    <div class="mt-1 text-brandText/80"><?= h($order['order_token']) ?></div>
                </div>
                <div>
                    <div class="text-xs text-brandText/50 uppercase tracking-[.28em]">Geplaatst op</div>
                    <div class="mt-1 text-brandText/80"><?= h($order['created_at']) ?></div>
                </div>
                <?php if (!empty($order['paid_at'])): ?>
                    <div>
                        <div class="text-xs text-brandText/50 uppercase tracking-[.28em]">Betaald op</div>
                        <div class="mt-1 text-brandText/80"><?= h($order['paid_at']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-8 rounded-2xl p-4 bg-brandBg border border-black/5 text-sm text-brandText/70 space-y-2">
                <div class="flex items-center justify-between">
                    <span>Subtotaal</span>
                    <span>€ <?= h(money((float)$order['subtotal'])) ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Verzendkosten</span>
                    <span>€ <?= h(money((float)$order['shipping'])) ?></span>
                </div>
                <div class="flex items-center justify-between font-medium text-brandText">
                    <span>Totaal</span>
                    <span>€ <?= h(money((float)$order['total'])) ?></span>
                </div>
                <div class="text-xs text-brandText/40"><?= h($order['currency']) ?></div>
            </div>

            <div class="mt-6">
                <a class="rounded-2xl px-4 py-2 text-sm border border-black/5 bg-brandBg hover:bg-white transition text-brandText/80" href="/admin/orders/">
                    Terug naar overzicht
                </a>
            </div>
        </aside>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
