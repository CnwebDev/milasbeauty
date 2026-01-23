<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: /admin/faqs/");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$faq = $stmt->fetch();

if (!$faq) {
    header("Location: /admin/faqs/");
    exit;
}

$created = (int)($_GET['created'] ?? 0) === 1;
$errors = [];
$saved = false;

$question = (string)$faq['question'];
$answer = (string)$faq['answer'];
$sort_order = (string)$faq['sort_order'];
$is_active = (int)$faq['is_active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim((string)($_POST['question'] ?? ''));
    $answer = trim((string)($_POST['answer'] ?? ''));
    $sort_order = trim((string)($_POST['sort_order'] ?? '0'));
    $is_active = (int)($_POST['is_active'] ?? 1) === 1 ? 1 : 0;

    if ($question === '') {
        $errors[] = 'Vraag is verplicht.';
    }

    if ($answer === '') {
        $errors[] = 'Antwoord is verplicht.';
    }

    $sortValue = is_numeric($sort_order) ? (int)$sort_order : 0;

    if (!$errors) {
        $pdo->prepare("UPDATE faqs SET question = ?, answer = ?, is_active = ?, sort_order = ? WHERE id = ?")
            ->execute([$question, $answer, $is_active, $sortValue, $id]);
        $saved = true;
    }
}

$pageTitle = "FAQ aanpassen";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">FAQ</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">FAQ aanpassen</h1>
        <p class="mt-3 text-brandText/70">Werk de vraag of het antwoord bij.</p>
    </div>
    <a class="btn btn-secondary" href="/admin/faqs/">Terug naar overzicht</a>
 </div>

<?php if ($created): ?>
    <div class="mt-6 rounded-[24px] p-5 border border-green-200 bg-green-50 text-green-700">
        FAQ is opgeslagen en klaar om te bewerken.
    </div>
<?php endif; ?>

<?php if ($saved): ?>
    <div class="mt-6 rounded-[24px] p-5 border border-green-200 bg-green-50 text-green-700">
        Wijzigingen opgeslagen.
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

<div class="mt-8 hairline"></div>

<form method="post" class="mt-8 grid gap-6 max-w-3xl">
    <label class="block">
        <span class="text-sm font-medium text-brandText">Vraag</span>
        <input name="question" value="<?= h($question) ?>" class="mt-2 input-field" />
    </label>

    <label class="block">
        <span class="text-sm font-medium text-brandText">Antwoord</span>
        <textarea name="answer" rows="5" class="mt-2 input-field"><?= h($answer) ?></textarea>
    </label>

    <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
            <span class="text-sm font-medium text-brandText">Sortering</span>
            <input name="sort_order" value="<?= h($sort_order) ?>" class="mt-2 input-field" />
        </label>

        <label class="block">
            <span class="text-sm font-medium text-brandText">Status</span>
            <select name="is_active" class="mt-2 input-field">
                <option value="1" <?= $is_active === 1 ? 'selected' : '' ?>>Actief</option>
                <option value="0" <?= $is_active === 0 ? 'selected' : '' ?>>Inactief</option>
            </select>
        </label>
    </div>

    <div class="flex gap-3">
        <button class="btn btn-primary">Opslaan</button>
        <a class="btn btn-secondary" href="/admin/faqs/">Annuleren</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
