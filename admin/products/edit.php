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

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    header("Location: /admin/products/");
    exit;
}

$created = (int)($_GET['created'] ?? 0) === 1;

$errors = [];
$saved = false;
$sizeInputs = [''];
$colorInputs = [''];

function normalize_option_inputs(array $values): array {
    $items = [];
    foreach ($values as $value) {
        $trimmed = trim((string)$value);
        if ($trimmed !== '') {
            $items[] = $trimmed;
        }
    }
    return array_values(array_unique($items));
}

function prepare_option_inputs(array $values): array {
    $items = array_map('trim', $values);
    if (!$items) {
        return [''];
    }
    return $items;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $short_description = trim((string)($_POST['short_description'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $price = trim((string)($_POST['price'] ?? ''));
    $volume_ml = trim((string)($_POST['volume_ml'] ?? ''));
    $is_active = (int)(($_POST['is_active'] ?? '1')) === 1 ? 1 : 0;
    $ingredients = trim((string)($_POST['ingredients'] ?? ''));
    $allergens = trim((string)($_POST['allergens'] ?? ''));
    $sizeInputs = $_POST['sizes'] ?? [];
    $colorInputs = $_POST['colors'] ?? [];
    $sizeInputs = is_array($sizeInputs) ? prepare_option_inputs($sizeInputs) : [''];
    $colorInputs = is_array($colorInputs) ? prepare_option_inputs($colorInputs) : [''];

    if ($name === '') $errors[] = 'Naam is verplicht.';

    $baseSlug = $slug !== '' ? slugify($slug) : slugify($name);
    $finalSlug = ensure_unique_slug($pdo, $baseSlug, $id);

    if (!$errors) {
        $pdo->prepare("UPDATE products
               SET name=?, slug=?, short_description=?, description=?, ingredients=?, allergens=?, price=?, volume_ml=?, is_active=?
               WHERE id=?")
                ->execute([
                        $name,
                        $finalSlug,
                        $short_description ?: null,
                        $description ?: null,
                        $ingredients ?: null,
                        $allergens ?: null,
                        $price !== '' ? (float)$price : null,
                        $volume_ml !== '' ? (int)$volume_ml : null,
                        $is_active,
                        $id
                ]);

        $pdo->prepare("DELETE FROM product_sizes WHERE product_id=?")->execute([$id]);
        $sizeValues = normalize_option_inputs($sizeInputs);
        if ($sizeValues) {
            $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (?, ?, ?)");
            foreach ($sizeValues as $index => $value) {
                $stmt->execute([$id, $value, $index + 1]);
            }
        }

        $pdo->prepare("DELETE FROM product_colors WHERE product_id=?")->execute([$id]);
        $colorValues = normalize_option_inputs($colorInputs);
        if ($colorValues) {
            $stmt = $pdo->prepare("INSERT INTO product_colors (product_id, color_label, sort_order) VALUES (?, ?, ?)");
            foreach ($colorValues as $index => $value) {
                $stmt->execute([$id, $value, $index + 1]);
            }
        }

        $uploadBase = public_upload_root() . DIRECTORY_SEPARATOR . $id;
        if (!is_dir($uploadBase)) mkdir($uploadBase, 0775, true);

        // Replace main image (optional)
        if (!empty($_FILES['main_image']['name'])) {
            $file = $_FILES['main_image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $mime = mime_content_type($file['tmp_name']);
                if (!allowed_image_mime($mime)) {
                    $errors[] = 'Hoofdfoto moet jpg/png/webp zijn.';
                } else {
                    // remove old main if exists
                    if (!empty($product['main_image'])) {
                        $oldAbs = dirname(__DIR__) . DIRECTORY_SEPARATOR . $product['main_image'];
                        if (is_file($oldAbs)) @unlink($oldAbs);
                    }

                    $ext = match ($mime) {
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg'
                    };
                    $filename = 'main.' . $ext;
                    $absPath = $uploadBase . DIRECTORY_SEPARATOR . $filename;
                    move_uploaded_image($file, $absPath);

                    $relPath = 'uploads/products/' . $id . '/' . $filename;

                    $pdo->prepare("UPDATE products SET main_image=? WHERE id=?")
                            ->execute([$relPath, $id]);
                }
            } else {
                $errors[] = 'Hoofdfoto upload fout.';
            }
        }

        // Add extra images (respect max 4 total)
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM product_images WHERE product_id=?");
        $stmt->execute([$id]);
        $currentExtraCount = (int)$stmt->fetch()['c'];

        if (!empty($_FILES['extra_images']) && isset($_FILES['extra_images']['name']) && is_array($_FILES['extra_images']['name'])) {
            $names = $_FILES['extra_images']['name'];
            $tmp = $_FILES['extra_images']['tmp_name'];
            $err = $_FILES['extra_images']['error'];

            $newCount = 0;
            for ($i = 0; $i < count($names); $i++) {
                if ($names[$i] !== '') $newCount++;
            }

            if ($currentExtraCount + $newCount > 4) {
                $errors[] = 'Je hebt al ' . $currentExtraCount . ' extra afbeeldingen. Max totaal is 4.';
            } else {
                // determine next sort order
                $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order),0) AS m FROM product_images WHERE product_id=?");
                $stmt->execute([$id]);
                $order = (int)$stmt->fetch()['m'] + 1;

                for ($i = 0; $i < count($names); $i++) {
                    if ($names[$i] === '') continue;

                    if ($err[$i] !== UPLOAD_ERR_OK) {
                        $errors[] = 'Extra afbeelding upload fout.';
                        continue;
                    }
                    $mime = mime_content_type($tmp[$i]);
                    if (!allowed_image_mime($mime)) {
                        $errors[] = 'Extra afbeeldingen moeten jpg/png/webp zijn.';
                        continue;
                    }

                    $ext = match ($mime) {
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg'
                    };

                    $filename = 'extra-' . $order . '.' . $ext;
                    $absPath = $uploadBase . DIRECTORY_SEPARATOR . $filename;

                    // Minimal array for helper
                    move_uploaded_image(['tmp_name' => $tmp[$i]] + $_FILES['extra_images'], $absPath);

                    $relPath = 'uploads/products/' . $id . '/' . $filename;

                    $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)")
                            ->execute([$id, $relPath, $order]);

                    $order++;
                }
            }
        }

        if (!$errors) {
            $saved = true;
            // refresh product data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
        }
    }
}

$stmt = $pdo->prepare("SELECT size_label FROM product_sizes WHERE product_id=? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$id]);
$sizeRows = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT color_label FROM product_colors WHERE product_id=? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$id]);
$colorRows = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$errors) {
    $sizeInputs = $sizeRows ?: [''];
    $colorInputs = $colorRows ?: [''];
}

$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$id]);
$extraImages = $stmt->fetchAll();

$pageTitle = "Product aanpassen";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Product</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Aanpassen</h1>
        <p class="mt-3 text-brandText/70">Product: <span class="text-brandText font-serif"><?= h($product['name']) ?></span></p>
    </div>

    <div class="flex gap-2">
        <a class="btn btn-primary"
           href="relations.php?id=<?= (int)$id ?>">Relaties beheren</a>
        <a class="btn btn-secondary"
           href="/admin/products/">Terug</a>
    </div>
</div>

<?php if ($created): ?>
    <div class="mt-6 rounded-[24px] p-5 border border-brandPinkSoft bg-brandPinkSoft/40 text-brandText/80">
        Product aangemaakt ✅ Je kunt nu afbeeldingen/relaties beheren.
    </div>
<?php endif; ?>

<?php if ($saved): ?>
    <div class="mt-6 rounded-[24px] p-5 border border-brandPinkSoft bg-brandPinkSoft/40 text-brandText/80">
        Opgeslagen ✅
    </div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="mt-6 rounded-[24px] p-5 border border-red-200 bg-red-50 text-red-700">
        <div class="font-medium mb-2">Er ging iets mis:</div>
        <ul class="list-disc ml-5 text-sm text-red-600">
            <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="mt-8 grid lg:grid-cols-12 gap-6" method="post" enctype="multipart/form-data">
    <div class="lg:col-span-7 rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="grid gap-4">
            <label class="text-sm text-brandText/80">
                Naam *
                <input name="name" value="<?= h((string)$product['name']) ?>" class="mt-2 input-field" />
            </label>

            <label class="text-sm text-brandText/80">
                Slug
                <input name="slug" value="<?= h((string)$product['slug']) ?>" class="mt-2 input-field" />
            </label>

            <label class="text-sm text-brandText/80">
                Korte omschrijving
                <input name="short_description" value="<?= h((string)($product['short_description'] ?? '')) ?>" class="mt-2 input-field" />
            </label>

            <label class="text-sm text-brandText/80">
                Beschrijving
                <textarea name="description" rows="6" class="mt-2 input-field"><?= h((string)($product['description'] ?? '')) ?></textarea>
            </label>
            <label class="text-sm text-brandText/80">
                Ingrediënten (optioneel)
                <textarea name="ingredients" rows="4"
                          class="mt-2 input-field"
                          placeholder="Bijv: Alcohol Denat., Parfum, Aqua, ..."><?= h((string)($product['ingredients'] ?? '')) ?></textarea>
                <div class="mt-1 text-xs text-brandText/50">INCI lijst van de verpakking.</div>
            </label>

            <label class="text-sm text-brandText/80">
                Allergenen (optioneel)
                <textarea name="allergens" rows="3"
                          class="mt-2 input-field"
                          placeholder="Bijv: Limonene, Linalool, ..."><?= h((string)($product['allergens'] ?? '')) ?></textarea>
            </label>

            <div class="text-sm text-brandText/80">
                <div class="font-medium">Maten (optioneel)</div>
                <div class="mt-2 grid gap-2" data-list="sizes">
                    <?php foreach ($sizeInputs as $value): ?>
                        <div class="flex gap-2">
                            <input name="sizes[]" value="<?= h($value) ?>" class="input-field flex-1" placeholder="Bijv: 50 ml" />
                            <button type="button" class="btn btn-secondary" data-remove>Verwijder</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="mt-2 btn btn-secondary" data-add>Voeg nog een toe</button>
                <div class="mt-1 text-xs text-brandText/50">Voeg elke maat als losse regel toe.</div>
            </div>

            <div class="text-sm text-brandText/80">
                <div class="font-medium">Kleuren (optioneel)</div>
                <div class="mt-2 grid gap-2" data-list="colors">
                    <?php foreach ($colorInputs as $value): ?>
                        <div class="flex gap-2">
                            <input name="colors[]" value="<?= h($value) ?>" class="input-field flex-1" placeholder="Bijv: Rose Gold" />
                            <button type="button" class="btn btn-secondary" data-remove>Verwijder</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="mt-2 btn btn-secondary" data-add>Voeg nog een toe</button>
                <div class="mt-1 text-xs text-brandText/50">Voeg elke kleur als losse regel toe.</div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <label class="text-sm text-brandText/80">
                    Prijs (€)
                    <input name="price" value="<?= h((string)($product['price'] ?? '')) ?>" class="mt-2 input-field" />
                </label>

                <label class="text-sm text-brandText/80">
                    Volume (ml)
                    <input name="volume_ml" value="<?= h((string)($product['volume_ml'] ?? '')) ?>" class="mt-2 input-field" />
                </label>

                <label class="text-sm text-brandText/80">
                    Status
                    <select name="is_active" class="mt-2 input-field">
                        <option value="1" <?= (int)$product['is_active'] === 1 ? 'selected' : '' ?>>Actief</option>
                        <option value="0" <?= (int)$product['is_active'] === 0 ? 'selected' : '' ?>>Inactief</option>
                    </select>
                </label>
            </div>

            <div class="pt-2 flex gap-3">
                <button class="btn btn-primary btn-lg">
                    Opslaan
                </button>
            </div>
        </div>
    </div>

    <div class="lg:col-span-5 rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-sm font-medium">Afbeeldingen</div>

        <div class="mt-4 grid gap-4">
            <div class="rounded-2xl p-4 border border-black/5 bg-brandBg">
                <div class="text-xs tracking-[.28em] uppercase text-brandText/60">Hoofdfoto</div>
                <div class="mt-3 flex items-center gap-4">
                    <div class="h-20 w-20 rounded-2xl border border-black/5 bg-white overflow-hidden flex items-center justify-center">
                        <?php if (!empty($product['main_image'])): ?>
                            <img src="/<?= h((string)$product['main_image']) ?>" class="h-full w-full object-cover" alt="">
                        <?php else: ?>
                            <span class="text-brandText/50 text-xs">no img</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="main_image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-brandText/70" />
                        <div class="mt-1 text-xs text-brandText/50">Upload vervangt de huidige hoofdfoto.</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-4 border border-black/5 bg-brandBg">
                <div class="text-xs tracking-[.28em] uppercase text-brandText/60">Extra afbeeldingen</div>
                <div class="mt-3 text-sm text-brandText/65">Max 4 totaal. Huidig: <?= count($extraImages) ?>.</div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <?php foreach ($extraImages as $img): ?>
                        <div class="rounded-2xl overflow-hidden border border-black/5 bg-white">
                            <div class="h-28">
                                <img src="/<?= h((string)$img['image_path']) ?>" class="h-full w-full object-cover" alt="">
                            </div>
                            <div class="p-3 flex items-center justify-between gap-2">
                                <div class="text-xs text-brandText/50">#<?= (int)$img['sort_order'] ?></div>
                                <a class="text-xs rounded-full px-3 py-1 border border-red-200 bg-red-50 hover:bg-red-100 transition text-red-700"
                                   href="delete_image.php?id=<?= (int)$img['id'] ?>&product_id=<?= (int)$id ?>"
                                   onclick="return confirm('Afbeelding verwijderen?');"
                                >Verwijder</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <input type="file" name="extra_images[]" multiple accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-brandText/70" />
                    <div class="mt-1 text-xs text-brandText/50">Je kunt meerdere tegelijk uploaden, zolang het totaal ≤ 4 blijft.</div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('[data-list]').forEach((list) => {
        const addButton = list.parentElement.querySelector('[data-add]');
        const buildRow = () => {
            const row = document.createElement('div');
            row.className = 'flex gap-2';
            row.innerHTML = `
                <input class="input-field flex-1" />
                <button type="button" class="btn btn-secondary" data-remove>Verwijder</button>
            `;
            row.querySelector('input').name = list.dataset.list + '[]';
            row.querySelector('input').placeholder = list.dataset.list === 'sizes' ? 'Bijv: 50 ml' : 'Bijv: Rose Gold';
            return row;
        };

        const ensureOneRow = () => {
            if (list.children.length === 0) {
                list.appendChild(buildRow());
            }
        };

        list.addEventListener('click', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.matches('[data-remove]')) {
                const row = target.closest('.flex');
                if (row) {
                    if (list.children.length > 1) {
                        row.remove();
                    } else {
                        const input = row.querySelector('input');
                        if (input) input.value = '';
                    }
                }
            }
        });

        addButton?.addEventListener('click', () => {
            list.appendChild(buildRow());
        });

        ensureOneRow();
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
