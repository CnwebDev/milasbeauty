<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();

$errors = [];
$question = '';
$answer = '';
$sort_order = '0';
$is_active = 1;

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
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, is_active, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$question, $answer, $is_active, $sortValue]);

        header("Location: /admin/faqs/edit.php?id=" . $pdo->lastInsertId() . "&created=1");
        exit;
    }
}

$pageTitle = "Nieuwe FAQ";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">FAQ</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Nieuwe FAQ</h1>
        <p class="mt-3 text-brandText/70">Voeg een nieuwe veelgestelde vraag toe.</p>
    </div>
    <a class="btn btn-secondary" href="/admin/faqs/">Terug naar overzicht</a>
</div>

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
        <input name="question" value="<?= h($question) ?>" class="mt-2 input-field" placeholder="Bijv: Hoe snel wordt mijn bestelling geleverd?" />
    </label>

    <label class="block">
        <span class="text-sm font-medium text-brandText">Antwoord</span>
        <textarea name="answer" rows="5" class="mt-2 input-field" placeholder="Schrijf het antwoord"><?= h($answer) ?></textarea>
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
