<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$errors = [];

$name = '';
$slug = '';
$short_description = '';
$description = '';
$ingredients = '';
$allergens = '';
$price = '';
$volume_ml = '';
$is_active = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $short_description = trim((string)($_POST['short_description'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $ingredients = trim((string)($_POST['ingredients'] ?? ''));
    $allergens = trim((string)($_POST['allergens'] ?? ''));
    $price = trim((string)($_POST['price'] ?? ''));
    $volume_ml = trim((string)($_POST['volume_ml'] ?? ''));
    $is_active = (int)(($_POST['is_active'] ?? '1')) === 1 ? 1 : 0;

    if ($name === '') $errors[] = 'Naam is verplicht.';

    $baseSlug = $slug !== '' ? slugify($slug) : slugify($name);
    $finalSlug = ensure_unique_slug($pdo, $baseSlug);

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, slug, short_description, description, ingredients, allergens, price, volume_ml, is_active
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
                $name,
                $finalSlug,
                $short_description ?: null,
                $description ?: null,
                $ingredients ?: null,
                $allergens ?: null,
                $price !== '' ? (float)$price : null,
                $volume_ml !== '' ? (int)$volume_ml : null,
                $is_active
        ]);

        $productId = (int)$pdo->lastInsertId();

        $uploadBase = public_upload_root() . DIRECTORY_SEPARATOR . $productId;
        if (!is_dir($uploadBase)) mkdir($uploadBase, 0775, true);

        // Main image
        if (!empty($_FILES['main_image']['name'])) {
            $file = $_FILES['main_image'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Hoofdfoto upload fout.';
            } else {
                $mime = mime_content_type($file['tmp_name']);
                if (!allowed_image_mime($mime)) {
                    $errors[] = 'Hoofdfoto moet jpg/png/webp zijn.';
                } else {
                    $ext = match ($mime) {
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg'
                    };
                    $filename = 'main.' . $ext;

                    $absPath = $uploadBase . DIRECTORY_SEPARATOR . $filename;
                    move_uploaded_image($file, $absPath);

                    $relPath = 'uploads/products/' . $productId . '/' . $filename;

                    $pdo->prepare("UPDATE products SET main_image = ? WHERE id = ?")
                            ->execute([$relPath, $productId]);
                }
            }
        }

        // Extra images (max 4)
        if (!empty($_FILES['extra_images']) && isset($_FILES['extra_images']['name']) && is_array($_FILES['extra_images']['name'])) {
            $names = $_FILES['extra_images']['name'];
            $tmp = $_FILES['extra_images']['tmp_name'];
            $err = $_FILES['extra_images']['error'];

            $count = 0;
            for ($i = 0; $i < count($names); $i++) {
                if ($names[$i] === '') continue;
                $count++;
            }

            if ($count > 4) {
                $errors[] = 'Je mag maximaal 4 extra afbeeldingen uploaden.';
            } else {
                $order = 1;
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

                    move_uploaded_image(['tmp_name' => $tmp[$i]] + $_FILES['extra_images'], $absPath);

                    $relPath = 'uploads/products/' . $productId . '/' . $filename;

                    $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)")
                            ->execute([$productId, $relPath, $order]);

                    $order++;
                }
            }
        }

        if (!$errors) {
            header("Location: /admin/products/edit.php?id=" . $productId . "&created=1");
            exit;
        }
    }
}

$pageTitle = "Nieuw product";
include __DIR__ . '/../includes/header.php';
?>

<div class="text-xs tracking-[.34em] uppercase text-white/60">Product</div>
<h1 class="mt-3 font-display text-3xl md:text-4xl">Nieuw product</h1>

<?php if ($errors): ?>
    <div class="mt-6 rounded-[24px] p-5 luxe-ring bg-red-500/10 text-white/80">
        <div class="font-medium mb-2">Er ging iets mis:</div>
        <ul class="list-disc ml-5 text-sm text-white/75">
            <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="mt-8 grid lg:grid-cols-12 gap-6" method="post" enctype="multipart/form-data">
    <div class="lg:col-span-7 rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
        <div class="grid gap-4">
            <label class="text-sm text-white/80">
                Naam *
                <input name="name" value="<?= h($name) ?>" class="mt-2 input-field" />
            </label>

            <label class="text-sm text-white/80">
                Slug (optioneel)
                <input name="slug" value="<?= h($slug) ?>" class="mt-2 input-field" placeholder="bv: noir-ambre" />
            </label>

            <label class="text-sm text-white/80">
                Korte omschrijving
                <input name="short_description" value="<?= h($short_description) ?>" class="mt-2 input-field" />
            </label>

            <label class="text-sm text-white/80">
                Beschrijving
                <textarea name="description" rows="5" class="mt-2 input-field"><?= h($description) ?></textarea>
            </label>

            <!-- NEW -->
            <label class="text-sm text-white/80">
                Ingrediënten (optioneel)
                <textarea name="ingredients" rows="4" class="mt-2 input-field" placeholder="Bijv: Alcohol Denat., Parfum, Aqua, ..."><?= h($ingredients) ?></textarea>
                <div class="mt-1 text-xs text-white/45">Tip: plak hier je ingrediëntenlijst (INCI) zoals op de verpakking.</div>
            </label>

            <!-- NEW -->
            <label class="text-sm text-white/80">
                Allergenen (optioneel)
                <textarea name="allergens" rows="3" class="mt-2 input-field" placeholder="Bijv: Limonene, Linalool, Citral, ..."><?= h($allergens) ?></textarea>
            </label>

            <div class="grid sm:grid-cols-3 gap-4">
                <label class="text-sm text-white/80">
                    Prijs (€)
                    <input name="price" value="<?= h($price) ?>" class="mt-2 input-field" placeholder="20.00" />
                </label>

                <label class="text-sm text-white/80">
                    Volume (ml)
                    <input name="volume_ml" value="<?= h($volume_ml) ?>" class="mt-2 input-field" placeholder="50" />
                </label>

                <label class="text-sm text-white/80">
                    Status
                    <select name="is_active" class="mt-2 input-field">
                        <option value="1" <?= $is_active === 1 ? 'selected' : '' ?>>Actief</option>
                        <option value="0" <?= $is_active === 0 ? 'selected' : '' ?>>Inactief</option>
                    </select>
                </label>
            </div>
        </div>
    </div>

    <div class="lg:col-span-5 rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
        <div class="text-sm font-medium">Afbeeldingen</div>
        <p class="mt-2 text-sm text-white/65">Hoofdfoto + maximaal 4 extra afbeeldingen (jpg/png/webp).</p>

        <div class="mt-5 grid gap-4">
            <label class="text-sm text-white/80">
                Hoofdfoto
                <input type="file" name="main_image" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-white/70" />
            </label>

            <label class="text-sm text-white/80">
                Extra afbeeldingen (max 4)
                <input type="file" name="extra_images[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-white/70" />
            </label>

            <div class="pt-2 flex gap-3">
                <button class="btn btn-primary btn-lg">
                    Opslaan
                </button>
                <a href="/admin/products/" class="btn btn-secondary btn-lg">
                    Annuleren
                </a>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
