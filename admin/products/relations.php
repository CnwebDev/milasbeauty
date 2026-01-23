<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: /admin/products/");
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, slug FROM products WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    header("Location: /admin/products/");
    exit;
}

$errors = [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'add') {
        $relatedId = (int)($_POST['related_product_id'] ?? 0);
        if ($relatedId <= 0) $errors[] = 'Kies een product.';
        if ($relatedId === $id) $errors[] = 'Je kunt een product niet aan zichzelf relateren.';

        if (!$errors) {
            $pdo->prepare("INSERT IGNORE INTO product_relations (product_id, related_product_id) VALUES (?, ?)")
                ->execute([$id, $relatedId]);
            $saved = true;
        }
    }

    if ($action === 'remove') {
        $relatedId = (int)($_POST['related_product_id'] ?? 0);
        if ($relatedId > 0) {
            $pdo->prepare("DELETE FROM product_relations WHERE product_id=? AND related_product_id=?")
                ->execute([$id, $relatedId]);
            $saved = true;
        }
    }
}

$stmt = $pdo->prepare("
  SELECT pr.related_product_id, p.name, p.slug
  FROM product_relations pr
  JOIN products p ON p.id = pr.related_product_id
  WHERE pr.product_id = ?
  ORDER BY p.name ASC
");
$stmt->execute([$id]);
$current = $stmt->fetchAll();

$stmt = $pdo->prepare("
  SELECT id, name, slug
  FROM products
  WHERE id <> ?
  ORDER BY name ASC
");
$stmt->execute([$id]);
$allOthers = $stmt->fetchAll();

$pageTitle = "Relaties";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Relaties</div>
        <h1 class="mt-3 font-display text-3xl md:text-4xl">Gerelateerde producten</h1>
        <p class="mt-3 text-white/70">
            Voor: <span class="gold-text font-display"><?= h($product['name']) ?></span>
            <span class="text-white/40">•</span>
            <span class="text-white/60"><?= h($product['slug']) ?></span>
        </p>
    </div>

    <div class="flex gap-2">
        <a class="rounded-2xl px-4 py-2 text-sm luxe-ring bg-black/25 hover:bg-black/35 transition text-white/80"
           href="edit.php?id=<?= (int)$id ?>">Terug naar product</a>
    </div>
</div>

<?php if ($saved): ?>
    <div class="mt-6 rounded-[24px] p-5 luxe-ring bg-gold-500/10 text-white/80">
        Relaties bijgewerkt ✅
    </div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="mt-6 rounded-[24px] p-5 luxe-ring bg-red-500/10 text-white/80">
        <div class="font-medium mb-2">Er ging iets mis:</div>
        <ul class="list-disc ml-5 text-sm text-white/75">
            <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="mt-8 grid lg:grid-cols-12 gap-6">
    <div class="lg:col-span-5 rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
        <div class="text-sm font-medium">Relatie toevoegen</div>
        <form class="mt-4 grid gap-3" method="post">
            <input type="hidden" name="action" value="add" />
            <select name="related_product_id" class="w-full input-field">
                <option value="">— Kies product —</option>
                <?php foreach ($allOthers as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= h($p['name']) ?> (<?= h($p['slug']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-lg">
                Toevoegen
            </button>
            <p class="text-xs text-white/45">Tip: relaties zijn “directed” (A → B). Voeg andersom ook toe als je beide kanten wil.</p>
        </form>
    </div>

    <div class="lg:col-span-7 rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
        <div class="text-sm font-medium">Huidige relaties</div>

        <div class="mt-4 grid gap-3">
            <?php if (!$current): ?>
                <div class="rounded-2xl p-4 luxe-ring bg-black/30 text-white/70">
                    Nog geen gerelateerde producten ingesteld.
                </div>
            <?php endif; ?>

            <?php foreach ($current as $r): ?>
                <div class="rounded-2xl p-4 luxe-ring bg-black/30 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-white/85"><?= h($r['name']) ?></div>
                        <div class="text-xs text-white/50"><?= h($r['slug']) ?></div>
                    </div>
                    <form method="post">
                        <input type="hidden" name="action" value="remove" />
                        <input type="hidden" name="related_product_id" value="<?= (int)$r['related_product_id'] ?>" />
                        <button class="rounded-full px-4 py-2 text-xs luxe-ring bg-red-500/10 hover:bg-red-500/15 transition text-white/75"
                                onclick="return confirm('Relatie verwijderen?');">
                            Verwijder
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
