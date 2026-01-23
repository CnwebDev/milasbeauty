<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$paymentFilter = trim((string)($_GET['payment_status'] ?? ''));

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(o.order_token LIKE ? OR o.first_name LIKE ? OR o.last_name LIKE ? OR o.email LIKE ?)';
    $like = "%{$q}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($statusFilter !== '') {
    $where[] = 'o.status = ?';
    $params[] = $statusFilter;
}

if ($paymentFilter !== '') {
    $where[] = 'o.payment_status = ?';
    $params[] = $paymentFilter;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT o.id, o.order_token, o.status, o.payment_status, o.total, o.currency,
            o.first_name, o.last_name, o.email, o.created_at,
            COUNT(oi.id) AS item_count
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        {$whereSql}
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statusOptions = $pdo->query("SELECT DISTINCT status FROM orders ORDER BY status")->fetchAll(PDO::FETCH_COLUMN);
$paymentOptions = $pdo->query("SELECT DISTINCT payment_status FROM orders WHERE payment_status IS NOT NULL ORDER BY payment_status")
    ->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Bestellingen";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Bestellingen</h1>
        <p class="mt-3 text-brandText/70">Alle binnenkomende bestellingen in één overzicht.</p>
    </div>

    <form class="w-full xl:w-auto flex flex-col md:flex-row flex-wrap gap-3 items-stretch md:items-center" method="get">
        <input
                name="q"
                value="<?= h($q) ?>"
                class="w-full md:w-64 input-field"
                placeholder="Zoek op klant/order"
        />
        <select
                name="status"
                class="w-full md:w-auto input-field"
        >
            <option value="">Alle statussen</option>
            <?php foreach ($statusOptions as $option): ?>
                <option value="<?= h($option) ?>" <?= $option === $statusFilter ? 'selected' : '' ?>>
                    <?= h(ucfirst($option)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select
                name="payment_status"
                class="w-full md:w-auto input-field"
        >
            <option value="">Alle betaalstatussen</option>
            <?php foreach ($paymentOptions as $option): ?>
                <option value="<?= h($option) ?>" <?= $option === $paymentFilter ? 'selected' : '' ?>>
                    <?= h(ucfirst($option)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-lg">
            Filter
        </button>
        <?php if ($q !== '' || $statusFilter !== '' || $paymentFilter !== ''): ?>
            <a class="btn btn-secondary btn-md" href="/admin/orders/">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-6 grid gap-3">
    <?php if (!$orders): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5">
            <div class="text-sm text-brandText/70">Geen bestellingen gevonden voor deze filters.</div>
            <p class="mt-2 text-xs text-brandText/50">Pas de filters aan om andere resultaten te zien.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($orders as $order): ?>
        <div class="rounded-[24px] overflow-hidden bg-white shadow-card border border-black/5">
            <div class="p-4 md:p-5 grid gap-4 md:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_auto]">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2 text-[11px] tracking-[.28em] uppercase text-brandText/50">
                        <span>Bestelling #<?= h((string)$order['id']) ?></span>
                        <span class="text-brandText/30">•</span>
                        <span class="text-brandText/40"><?= h($order['order_token']) ?></span>
                    </div>
                    <div class="font-serif text-lg md:text-xl text-brandText">
                        <?= h($order['first_name'] . ' ' . $order['last_name']) ?>
                    </div>
                    <div class="text-xs text-brandText/60"><?= h($order['email']) ?></div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs md:text-sm">
                    <div>
                        <div class="text-brandText/50">Totaal</div>
                        <div class="text-base md:text-lg text-brandText font-medium">€ <?= number_format((float)$order['total'], 2, ',', '.') ?></div>
                        <div class="text-[11px] text-brandText/40"><?= h($order['currency']) ?> • <?= (int)$order['item_count'] ?> items</div>
                    </div>
                    <div>
                        <div class="text-brandText/50">Datum</div>
                        <div class="text-sm text-brandText/80"><?= h($order['created_at']) ?></div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 items-start md:items-end justify-between">
                    <div class="flex flex-wrap gap-2">
                        <span class="text-[11px] tracking-[.28em] uppercase rounded-full px-3 py-1 border border-black/5 bg-brandPinkSoft/60 text-brandText/70">
                            <?= h($order['status']) ?>
                        </span>
                        <span class="text-[11px] tracking-[.28em] uppercase rounded-full px-3 py-1 border border-black/5 bg-brandBg text-brandText/60">
                            <?= h($order['payment_status'] ?? 'onbekend') ?>
                        </span>
                    </div>
                    <a class="btn btn-primary text-xs md:text-sm" href="/admin/orders/show.php?id=<?= (int)$order['id'] ?>">
                        Bekijk
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
