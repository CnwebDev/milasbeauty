<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_login();

$pdo = db();
$contact = $pdo->query(
    "SELECT id, phone, email, whatsapp, address_line, postal_code, city, country, updated_at
     FROM contact_details
     ORDER BY updated_at DESC
     LIMIT 1"
)->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
    $addressLine = trim((string)($_POST['address_line'] ?? ''));
    $postalCode = trim((string)($_POST['postal_code'] ?? ''));
    $city = trim((string)($_POST['city'] ?? ''));
    $country = trim((string)($_POST['country'] ?? ''));

    if ($contact) {
        $stmt = $pdo->prepare(
            "UPDATE contact_details
             SET phone = ?, email = ?, whatsapp = ?, address_line = ?, postal_code = ?, city = ?, country = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $phone,
            $email,
            $whatsapp,
            $addressLine,
            $postalCode,
            $city,
            $country,
            (int)$contact['id'],
        ]);
        $contactId = (int)$contact['id'];
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO contact_details (phone, email, whatsapp, address_line, postal_code, city, country)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $phone,
            $email,
            $whatsapp,
            $addressLine,
            $postalCode,
            $city,
            $country,
        ]);
        $contactId = (int)$pdo->lastInsertId();
    }

    $cleanup = $pdo->prepare("DELETE FROM contact_details WHERE id <> ?");
    $cleanup->execute([$contactId]);

    header('Location: /admin/contact/?saved=1');
    exit;
}

$contact = $pdo->query(
    "SELECT id, phone, email, whatsapp, address_line, postal_code, city, country, updated_at
     FROM contact_details
     ORDER BY updated_at DESC
     LIMIT 1"
)->fetch();
$saved = isset($_GET['saved']);

$pageTitle = "Contactgegevens";
include __DIR__ . '/../includes/header.php';
?>

<div class="flex items-end justify-between gap-6 flex-wrap">
    <div>
        <div class="text-xs tracking-[.34em] uppercase text-brandText/60">Website beheer</div>
        <h1 class="mt-3 font-serif text-3xl md:text-4xl">Contactgegevens beheren</h1>
        <p class="mt-3 text-brandText/70">Werk de contactinformatie bij die overal in de shop wordt getoond.</p>
    </div>

    <div class="rounded-[22px] px-5 py-4 bg-white shadow-card border border-black/5">
        <div class="text-xs uppercase tracking-[.3em] text-brandText/60">Status</div>
        <div class="mt-2 text-lg font-medium text-brandText">
            <?= $contact ? 'Ingevuld' : 'Nog leeg' ?>
        </div>
        <?php if ($contact): ?>
            <div class="mt-1 text-xs text-brandText/60">Laatst bijgewerkt: <?= h((string)$contact['updated_at']) ?></div>
        <?php endif; ?>
    </div>
</div>

<?php if ($saved): ?>
    <div class="mt-6 rounded-[24px] p-5 border border-brandPinkSoft bg-brandPinkSoft/40 text-brandText/80">
        De contactgegevens zijn opgeslagen. Er is altijd slechts één record actief.
    </div>
<?php endif; ?>

<div class="mt-8 hairline"></div>

<form class="mt-8 grid gap-6" method="post">
    <div class="rounded-[28px] p-6 bg-white shadow-card border border-black/5">
        <div class="text-sm font-medium">Contactinformatie</div>
        <p class="mt-2 text-sm text-brandText/70">Pas de gegevens aan en sla op om ze direct overal te gebruiken.</p>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="text-xs text-brandText/70">
                Telefoon
                <input
                    type="text"
                    name="phone"
                    class="mt-2 input-field"
                    placeholder="+31 6 123 456 78"
                    value="<?= h((string)($contact['phone'] ?? '')) ?>"
                />
            </label>
            <label class="text-xs text-brandText/70">
                E-mail
                <input
                    type="email"
                    name="email"
                    class="mt-2 input-field"
                    placeholder="info@milasbeauty.nl"
                    value="<?= h((string)($contact['email'] ?? '')) ?>"
                />
            </label>
            <label class="text-xs text-brandText/70">
                WhatsApp
                <input
                    type="text"
                    name="whatsapp"
                    class="mt-2 input-field"
                    placeholder="+31 6 123 456 78"
                    value="<?= h((string)($contact['whatsapp'] ?? '')) ?>"
                />
            </label>
            <label class="text-xs text-brandText/70">
                Straat + huisnummer
                <input
                    type="text"
                    name="address_line"
                    class="mt-2 input-field"
                    placeholder="Voorbeeldstraat 12"
                    value="<?= h((string)($contact['address_line'] ?? '')) ?>"
                />
            </label>
            <label class="text-xs text-brandText/70">
                Postcode
                <input
                    type="text"
                    name="postal_code"
                    class="mt-2 input-field"
                    placeholder="1234 AB"
                    value="<?= h((string)($contact['postal_code'] ?? '')) ?>"
                />
            </label>
            <label class="text-xs text-brandText/70">
                Plaats
                <input
                    type="text"
                    name="city"
                    class="mt-2 input-field"
                    placeholder="Amsterdam"
                    value="<?= h((string)($contact['city'] ?? '')) ?>"
                />
            </label>
            <label class="text-xs text-brandText/70 md:col-span-2">
                Land
                <input
                    type="text"
                    name="country"
                    class="mt-2 input-field"
                    placeholder="Nederland"
                    value="<?= h((string)($contact['country'] ?? '')) ?>"
                />
            </label>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">Opslaan</button>
            <a class="btn btn-secondary" href="/admin/index.php">Terug naar overzicht</a>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
