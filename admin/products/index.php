<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$q = trim((string)($_GET['q'] ?? ''));
$sql = "SELECT id, name, slug, price, volume_ml, is_active, main_image, updated_at
        FROM products
        WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (name LIKE ? OR slug LIKE ?)";
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
}

$sql .= " ORDER BY updated_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = "Producten";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Producten</h1>
        <p class="mt-3 text-brandText/70">Overzicht, aanpassen, verwijderen en relaties beheren.</p>
    </div>

    <form class="flex gap-3 items-center" method="get">
        <input
                name="q"
                value="<?= h($q) ?>"
                class="w-64 input-field"
                placeholder="Zoek op naam/slug"
        />
        <button class="btn btn-primary btn-lg">
            Zoeken
        </button>
    </form>
</div>

<div class="mt-8 hairline"></div>

<div class="mt-8 grid gap-4">
    <?php if (!$products): ?>
        <div class="rounded-[24px] p-6 bg-white shadow-card border border-black/5 text-brandText/70">
            Nog geen producten. <a class="text-brandText underline" href="create.php">Maak je eerste product</a>.
        </div>
    <?php endif; ?>

    <?php foreach ($products as $p): ?>
        <div class="rounded-[28px] overflow-hidden bg-white shadow-card border border-black/5">
            <div class="p-6 flex items-start justify-between gap-6 flex-wrap">
                <div class="flex items-start gap-4">
                    <div class="h-16 w-16 rounded-2xl border border-black/5 bg-brandBg overflow-hidden flex items-center justify-center">
                        <?php if (!empty($p['main_image'])): ?>
                            <img src="/<?= h($p['main_image']) ?>" class="h-full w-full object-cover" alt="">
                        <?php else: ?>
                            <span class="text-brandText/50 text-xs">no img</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="flex items-center gap-3">
                            <div class="font-serif text-xl text-brandText"><?= h($p['name']) ?></div>
                            <?php if ((int)$p['is_active'] === 1): ?>
                                <span class="text-[11px] tracking-[.28em] uppercase rounded-full px-3 py-1 border border-black/5 bg-brandPinkSoft/60 text-brandText/70">Actief</span>
                            <?php else: ?>
                                <span class="text-[11px] tracking-[.28em] uppercase rounded-full px-3 py-1 border border-black/5 bg-brandBg text-brandText/50">Inactief</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-1 text-sm text-brandText/60">Slug: <span class="text-brandText/80"><?= h($p['slug']) ?></span></div>
                        <div class="mt-1 text-sm text-brandText/60">
                            Prijs: <span class="text-brandText/80">€ <?= h((string)$p['price']) ?></span>
                            <span class="text-brandText/40">•</span>
                            Volume: <span class="text-brandText/80"><?= h((string)$p['volume_ml']) ?> ml</span>
                        </div>
                        <div class="mt-1 text-xs text-brandText/50">Laatst gewijzigd: <?= h((string)$p['updated_at']) ?></div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a class="btn btn-secondary"
                       href="edit.php?id=<?= (int)$p['id'] ?>">Aanpassen</a>

                    <a class="btn btn-primary"
                       href="relations.php?id=<?= (int)$p['id'] ?>">Relaties</a>

                    <a class="btn btn-danger"
                       href="delete.php?id=<?= (int)$p['id'] ?>"
                       onclick="return confirm('Weet je zeker dat je dit product wilt verwijderen?');"
                    >Verwijderen</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
