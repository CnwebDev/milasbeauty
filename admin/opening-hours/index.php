<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$dayLabels = [
    1 => 'Maandag',
    2 => 'Dinsdag',
    3 => 'Woensdag',
    4 => 'Donderdag',
    5 => 'Vrijdag',
    6 => 'Zaterdag',
    7 => 'Zondag',
];

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = $_POST['days'] ?? [];
    $updates = [];
    $errors = [];

    foreach ($dayLabels as $dayNumber => $dayLabel) {
        $dayData = $payload[$dayNumber] ?? [];
        $opensAt = trim((string)($dayData['opens_at'] ?? ''));
        $closesAt = trim((string)($dayData['closes_at'] ?? ''));
        $note = trim((string)($dayData['note'] ?? ''));
        $isClosed = isset($dayData['is_closed']) ? 1 : 0;

        if ($isClosed === 1) {
            $opensAt = '';
            $closesAt = '';
        } else {
            if ($opensAt === '' || $closesAt === '') {
                $errors[] = "Vul openingstijden in voor {$dayLabel}.";
            }

            if ($opensAt !== '' && !preg_match('/^\d{2}:\d{2}$/', $opensAt)) {
                $errors[] = "Ongeldig tijdstip voor {$dayLabel} (open).";
            }

            if ($closesAt !== '' && !preg_match('/^\d{2}:\d{2}$/', $closesAt)) {
                $errors[] = "Ongeldig tijdstip voor {$dayLabel} (sluit).";
            }
        }

        $updates[$dayNumber] = [
            'opens_at' => $opensAt !== '' ? $opensAt : null,
            'closes_at' => $closesAt !== '' ? $closesAt : null,
            'is_closed' => $isClosed,
            'note' => $note !== '' ? $note : null,
        ];
    }

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare(
                "INSERT INTO opening_hours (day_of_week, opens_at, closes_at, is_closed, note)
                 VALUES (:day_of_week, :opens_at, :closes_at, :is_closed, :note)
                 ON DUPLICATE KEY UPDATE
                    opens_at = VALUES(opens_at),
                    closes_at = VALUES(closes_at),
                    is_closed = VALUES(is_closed),
                    note = VALUES(note)"
            );

            foreach ($updates as $dayNumber => $values) {
                $statement->execute([
                    ':day_of_week' => $dayNumber,
                    ':opens_at' => $values['opens_at'],
                    ':closes_at' => $values['closes_at'],
                    ':is_closed' => $values['is_closed'],
                    ':note' => $values['note'],
                ]);
            }

            $pdo->commit();
            $successMessage = 'Openingstijden opgeslagen.';
        } catch (Throwable $exception) {
            $pdo->rollBack();
            $errorMessage = 'Opslaan mislukt. Probeer het opnieuw.';
        }
    } else {
        $errorMessage = implode(' ', $errors);
    }
}

$openingHours = $pdo->query("SELECT id, day_of_week, opens_at, closes_at, is_closed, note, updated_at FROM opening_hours ORDER BY day_of_week ASC")->fetchAll();
$openingHoursByDay = [];
foreach ($openingHours as $row) {
    $openingHoursByDay[(int)$row['day_of_week']] = $row;
}

$pageTitle = "Openingstijden";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Openingstijden beheren</h1>
        <p class="mt-3 text-brandText/70">Bewerk het schema per dag. Er is één set openingstijden voor de hele salon.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Dagen</div>
        <div class="mt-2 text-lg font-medium text-brandText"><?= count($openingHoursByDay) ?> ingesteld</div>
    </div>
</div>

<div class="mt-8 hairline"></div>

<?php if ($successMessage): ?>
    <div class="mt-6 rounded-[24px] p-4 bg-emerald-50 text-emerald-800 border border-emerald-200">
        <?= h($successMessage) ?>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="mt-6 rounded-[24px] p-4 bg-rose-50 text-rose-700 border border-rose-200">
        <?= h($errorMessage) ?>
    </div>
<?php endif; ?>

<form class="mt-8 grid gap-4" method="post">
    <?php foreach ($dayLabels as $dayNumber => $dayLabel): ?>
        <?php $day = $openingHoursByDay[$dayNumber] ?? null; ?>
        <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5" data-day-row>
            <div class="flex items-start justify-between gap-6 flex-wrap">
                <div class="min-w-[220px]">
                    <div class="font-serif text-xl text-brandText"><?= h($dayLabel) ?></div>
                    <div class="mt-1 text-xs text-brandText/50">
                        Laatst bijgewerkt: <?= h((string)($day['updated_at'] ?? 'Nog niet ingesteld')) ?>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <label class="flex flex-col gap-1">
                        <span class="text-brandText/70">Open</span>
                        <input
                            type="time"
                            step="60"
                            name="days[<?= (int)$dayNumber ?>][opens_at]"
                            value="<?= h((string)($day['opens_at'] ?? '')) ?>"
                            class="rounded-xl border border-black/10 bg-brandBg px-3 py-2"
                            data-time-input
                            <?= ((int)($day['is_closed'] ?? 0) === 1) ? 'disabled' : '' ?>
                        />
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-brandText/70">Dicht</span>
                        <input
                            type="time"
                            step="60"
                            name="days[<?= (int)$dayNumber ?>][closes_at]"
                            value="<?= h((string)($day['closes_at'] ?? '')) ?>"
                            class="rounded-xl border border-black/10 bg-brandBg px-3 py-2"
                            data-time-input
                            <?= ((int)($day['is_closed'] ?? 0) === 1) ? 'disabled' : '' ?>
                        />
                    </label>

                    <label class="inline-flex items-center gap-2 text-brandText/70">
                        <input
                            type="checkbox"
                            name="days[<?= (int)$dayNumber ?>][is_closed]"
                            value="1"
                            class="h-4 w-4 rounded border-black/20"
                            <?= ((int)($day['is_closed'] ?? 0) === 1) ? 'checked' : '' ?>
                            data-closed-toggle
                        />
                        Gesloten
                    </label>
                </div>
            </div>

            <label class="mt-4 flex flex-col gap-2 text-sm text-brandText/70">
                Opmerking (optioneel)
                <input
                    type="text"
                    name="days[<?= (int)$dayNumber ?>][note]"
                    value="<?= h((string)($day['note'] ?? '')) ?>"
                    class="rounded-xl border border-black/10 bg-brandBg px-3 py-2"
                />
            </label>
        </div>
    <?php endforeach; ?>

    <div class="flex justify-end">
        <button class="btn btn-primary" type="submit">Opslaan</button>
    </div>
</form>

<script>
    document.querySelectorAll('[data-day-row]').forEach((row) => {
        const toggle = row.querySelector('[data-closed-toggle]');
        const timeInputs = row.querySelectorAll('[data-time-input]');

        if (!toggle || timeInputs.length === 0) {
            return;
        }

        const updateState = () => {
            const isClosed = toggle.checked;
            timeInputs.forEach((input) => {
                if (isClosed) {
                    input.value = '';
                }
                input.disabled = isClosed;
            });
        };

        toggle.addEventListener('change', updateState);
        updateState();
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
