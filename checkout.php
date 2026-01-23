<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\MollieApiClient;

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = db();

$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$cart = &$_SESSION['cart'];

$cartCount = array_sum(array_map('intval', $cart));

$items = [];
$subtotal = 0.0;

if ($cart) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT id, name, slug, price, volume_ml, main_image FROM products WHERE id IN ($placeholders) AND is_active=1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $byId = [];
    foreach ($products as $p) $byId[(int)$p['id']] = $p;

    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if (!isset($byId[$pid])) continue;
        $p = $byId[$pid];

        $price = (float)($p['price'] ?? 0);
        $line = $price * $qty;
        $subtotal += $line;

        $items[] = [
            'id' => $pid,
            'name' => $p['name'],
            'slug' => $p['slug'],
            'price' => $price,
            'volume_ml' => $p['volume_ml'],
            'main_image' => $p['main_image'],
            'qty' => $qty,
            'line' => $line,
        ];
    }
}

function money(float $v): string {
    return number_format($v, 2, ',', '.');
}

$shipping = ($subtotal > 75.0 || $subtotal == 0.0) ? 0.0 : 4.95;
$total = $subtotal + $shipping;

function base_url(): string
{
    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isSecure ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host;
}

$debugMode = env('APP_DEBUG', '0') === '1';
function debug_error(string $message, bool $debugMode): string
{
    if (!$debugMode) {
        return '';
    }
    return ' [Debug] ' . $message;
}

$checkoutError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_payment') {
    if ($cartCount === 0) {
        $checkoutError = 'Je winkelwagen is leeg. Voeg eerst producten toe.';
    } else {
        $apiKey = env('MOLLIE_API_KEY');
        if ($apiKey === null) {
            $checkoutError = 'Mollie API key ontbreekt. Vul MOLLIE_API_KEY in je .env.';
        } else {
            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $lastName = trim((string)($_POST['last_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $address = trim((string)($_POST['address'] ?? ''));
            $zip = trim((string)($_POST['zip'] ?? ''));
            $city = trim((string)($_POST['city'] ?? ''));
            $country = trim((string)($_POST['country'] ?? ''));
            $notes = trim((string)($_POST['notes'] ?? ''));

            $missing = [];
            if ($firstName === '') $missing[] = 'voornaam';
            if ($lastName === '') $missing[] = 'achternaam';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $missing[] = 'e-mailadres';
            if ($address === '') $missing[] = 'adres';
            if ($zip === '') $missing[] = 'postcode';
            if ($city === '') $missing[] = 'plaats';
            if ($country === '') $missing[] = 'land';

            if ($missing) {
                $checkoutError = 'Vul alle verplichte velden in: ' . implode(', ', $missing) . '.';
            } else {
                $orderToken = bin2hex(random_bytes(16));
                $orderId = null;
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare(
                        'INSERT INTO orders (order_token, status, subtotal, shipping, total, currency, first_name, last_name, email, phone, address, zip, city, country, notes)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $orderToken,
                        'pending',
                        $subtotal,
                        $shipping,
                        $total,
                        'EUR',
                        $firstName,
                        $lastName,
                        $email,
                        $phone ?: null,
                        $address,
                        $zip,
                        $city,
                        $country,
                        $notes ?: null,
                    ]);
                    $orderId = (int)$pdo->lastInsertId();

                    $itemStmt = $pdo->prepare(
                        'INSERT INTO order_items (order_id, product_id, product_name, price, qty, line_total, volume_ml, main_image)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    foreach ($items as $item) {
                        $itemStmt->execute([
                            $orderId,
                            $item['id'],
                            $item['name'],
                            $item['price'],
                            $item['qty'],
                            $item['line'],
                            $item['volume_ml'],
                            $item['main_image'],
                        ]);
                    }

                    $pdo->commit();
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log('Order save failed: ' . $e->getMessage());
                    $checkoutError = 'Kon de bestelling niet opslaan. Probeer het opnieuw.';
//                        . debug_error($e->getMessage(), $debugMode);
                }

                if ($checkoutError === null && $orderId !== null) {
                    $mollie = new MollieApiClient();
                    $mollie->setApiKey($apiKey);

                    if ($total < 0.01) {
                        $checkoutError = 'Het totaalbedrag is te laag om een Mollie betaling te starten.';
                    } else {
                        $amountValue = number_format(round($total, 2), 2, '.', '');
                        $redirectUrl = base_url() . '/order-status.php?token=' . urlencode($orderToken);
                        $webhookUrl = base_url() . '/mollie-webhook.php';

                        try {
                            $payment = $mollie->send(new CreatePaymentRequest(
                                description: 'ASA Parfums bestelling #' . $orderId,
                                amount: new Money('EUR', $amountValue),
                                redirectUrl: $redirectUrl,
                                webhookUrl: $webhookUrl,
                                metadata: ['order_token' => $orderToken]
                            ));

                            $paymentId = $payment->id ?? null;
                            if ($paymentId) {
                                $stmt = $pdo->prepare('UPDATE orders SET mollie_payment_id = ?, payment_status = ? WHERE id = ?');
                                $stmt->execute([$paymentId, $payment->status ?? 'open', $orderId]);
                            }

                            $checkoutUrl = $payment->getCheckoutUrl();
                            if ($checkoutUrl) {
                                header('Location: ' . $checkoutUrl);
                                exit;
                            }

                            $checkoutError = 'Kon geen Mollie checkout URL ophalen. Probeer het opnieuw.';
                        } catch (Throwable $e) {
                            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
                            $stmt->execute(['failed', $orderId]);
                            error_log('Mollie payment create failed: ' . $e->getMessage());
                            $checkoutError = 'Betaling kon niet worden aangemaakt. Controleer je Mollie-configuratie.';
//                                . debug_error($e->getMessage(), $debugMode);
                            if (!$debugMode) {
                                $checkoutError .= ' Zet APP_DEBUG=1 in je .env voor de exacte foutmelding.';
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASA Parfums — Checkout</title>

    <link rel="stylesheet" href="assets/css/tailwind.css" />
    <link rel="stylesheet" href="assets/css/custom.css" />

</head>

<body class="page-body">

<div class="fixed inset-0 -z-50 pointer-events-none">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 h-[520px] w-[520px] rounded-full blur-[90px] bg-gold-400/10"></div>
    <div class="absolute top-40 -left-24 h-[520px] w-[520px] rounded-full blur-[100px] bg-gold-300/8"></div>
</div>

<?php
$navbarLogo = <<<'HTML'
<a href="index.php" class="group flex items-center gap-3">
<!--    <div class="h-10 w-10 rounded-2xl luxe-ring shadow-glow bg-black/30 flex items-center justify-center">-->
<!--        <span class="font-display gold-text">A</span>-->
<!--    </div>-->
    <div class="leading-none">
        <div class="font-display tracking-[.22em] text-sm gold-text">ASA</div>
        <div class="text-[11px] tracking-[.34em] text-white/60 -mt-1">PARFUMS</div>
    </div>
</a>
HTML;

ob_start();
?>
<div class="flex items-center gap-3">
    <a href="cart.php" class="hidden sm:inline-flex btn btn-secondary">
        Terug naar winkelwagen
    </a>
    <div class="badge badge-gold">
        Items: <?= (int)$cartCount ?>
    </div>
</div>
<?php
$navbarRight = ob_get_clean();

require __DIR__ . '/partials/navbar.php';
?>

<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-radial-hero"></div>
    </div>

    <div class="mx-auto max-w-7xl px-6 pt-12 pb-8">
        <div class="text-xs tracking-[.34em] uppercase text-white/60">Checkout</div>
        <h1 class="mt-4 font-display text-4xl md:text-5xl leading-[1.05]">
            Afrekenen in <span class="gold-text">ASA-stijl</span>
        </h1>
        <p class="mt-4 text-white/75 max-w-2xl">
            Controleer je items, vul je gegevens in en kies de gewenste bezorging. De winkelwagen is hier alleen te bekijken.
        </p>

        <div class="mt-8 hairline"></div>
    </div>
</section>

<section class="pb-20">
    <div class="mx-auto max-w-7xl px-6 grid lg:grid-cols-12 gap-8 items-start">

        <form id="checkout-form" method="post" class="lg:col-span-7 space-y-6">
            <input type="hidden" name="action" value="create_payment">
            <div class="rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="text-sm font-medium">Jouw producten</div>
                    <span class="text-xs text-white/50">Wijzigingen zijn hier niet mogelijk</span>
                </div>

                <?php if ($cartCount === 0): ?>
                    <div class="mt-6 rounded-2xl p-5 luxe-ring bg-black/30 text-white/70">
                        Je winkelwagen is leeg. <a class="underline text-white" href="products.php">Bekijk producten</a>.
                    </div>
                <?php else: ?>
                    <div class="mt-6 space-y-4">
                        <?php foreach ($items as $it): ?>
                            <div class="rounded-2xl p-4 luxe-ring bg-black/30 flex gap-4 items-center flex-wrap">
                                <a href="product.php?slug=<?= urlencode((string)$it['slug']) ?>"
                                   class="h-20 w-20 rounded-2xl luxe-ring overflow-hidden bg-black/40 shrink-0 flex items-center justify-center">
                                    <?php if (!empty($it['main_image'])): ?>
                                        <img src="<?= h((string)$it['main_image']) ?>" class="h-full w-full object-cover" alt="">
                                    <?php else: ?>
                                        <span class="text-white/40 text-xs">Geen foto</span>
                                    <?php endif; ?>
                                </a>

                                <div class="flex-1 min-w-[220px]">
                                    <a class="font-display text-xl gold-text" href="product.php?slug=<?= urlencode((string)$it['slug']) ?>">
                                        <?= h($it['name']) ?>
                                    </a>
                                    <div class="mt-1 text-sm text-white/60">
                                        € <?= h(money($it['price'])) ?>
                                        <?php if (!empty($it['volume_ml'])): ?>
                                            <span class="text-white/40">/ <?= (int)$it['volume_ml'] ?>ml</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2 text-xs text-white/45">
                                        Aantal: <?= (int)$it['qty'] ?> · Subtotaal: € <?= h(money($it['line'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
                <div class="text-sm font-medium">Jouw gegevens</div>
                <p class="mt-2 text-xs text-white/55">Vul je gegevens in, zodat we de bestelling kunnen voorbereiden.</p>

                <div class="mt-6 grid gap-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="text-xs text-white/60">
                            Voornaam
                            <input class="input-field mt-2" type="text" name="first_name" placeholder="Sarah" required>
                        </label>
                        <label class="text-xs text-white/60">
                            Achternaam
                            <input class="input-field mt-2" type="text" name="last_name" placeholder="Bakker" required>
                        </label>
                    </div>
                    <label class="text-xs text-white/60">
                        E-mailadres
                        <input class="input-field mt-2" type="email" name="email" placeholder="sarah@email.nl" required>
                    </label>
                    <label class="text-xs text-white/60">
                        Telefoon
                        <input class="input-field mt-2" type="tel" name="phone" placeholder="06 12345678">
                    </label>
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="text-xs text-white/60">
                            Straat en huisnummer
                            <input class="input-field mt-2" type="text" name="address" placeholder="Herengracht 15" required>
                        </label>
                        <label class="text-xs text-white/60">
                            Postcode
                            <input class="input-field mt-2" type="text" name="zip" placeholder="1017 AB" required>
                        </label>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="text-xs text-white/60">
                            Plaats
                            <input class="input-field mt-2" type="text" name="city" placeholder="Amsterdam" required>
                        </label>
                        <label class="text-xs text-white/60">
                            Land
                            <select class="input-field mt-2" name="country" required>
                                <option value="Nederland">Nederland</option>
                                <option value="België">België</option>
                                <option value="Duitsland">Duitsland</option>
                            </select>
                        </label>
                    </div>
                    <label class="text-xs text-white/60">
                        Opmerkingen (optioneel)
                        <textarea class="input-field mt-2 h-24" name="notes" placeholder="Laat een bericht achter voor ons team..."></textarea>
                    </label>
                </div>
            </div>

            <div class="rounded-[28px] p-6 luxe-ring bg-black/25 shadow-glow">
                <div class="text-sm font-medium">Bezorging & betaling</div>
                <div class="mt-5 space-y-3 text-sm text-white/70">
                    <label class="flex items-center gap-3 rounded-2xl p-4 luxe-ring bg-black/30">
                        <input type="radio" name="shipping" value="standard" checked class="accent-gold-400">
                        <div>
                            <div class="text-white/80">Standaard verzending</div>
                            <div class="text-xs text-white/45">2-3 werkdagen · Gratis boven € 75</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl p-4 luxe-ring bg-black/30">
                        <input type="radio" name="shipping" value="express" class="accent-gold-400">
                        <div>
                            <div class="text-white/80">Express</div>
                            <div class="text-xs text-white/45">Volgende werkdag · € 9,95</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl p-4 luxe-ring bg-black/30">
                        <input type="radio" name="payment" value="ideal" checked class="accent-gold-400">
                        <div>
                            <div class="text-white/80">iDEAL</div>
                            <div class="text-xs text-white/45">Veilig betalen via jouw bank</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl p-4 luxe-ring bg-black/30">
                        <input type="radio" name="payment" value="card" class="accent-gold-400">
                        <div>
                            <div class="text-white/80">Creditcard</div>
                            <div class="text-xs text-white/45">Visa, MasterCard, Amex</div>
                        </div>
                    </label>
                </div>
            </div>
        </form>

        <div class="lg:col-span-5">
            <div class="rounded-[28px] p-6 luxe-ring bg-black/35 shadow-glow relative overflow-hidden">
                <div class="absolute inset-0 bg-gold-sheen opacity-15"></div>
                <div class="relative">
                    <div class="text-sm font-medium">Overzicht</div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between text-white/75">
                            <span>Subtotaal</span>
                            <span>€ <?= h(money($subtotal)) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-white/75">
                            <span>Verzendkosten</span>
                            <span><?= $shipping == 0.0 ? 'Gratis' : '€ ' . h(money($shipping)) ?></span>
                        </div>
                        <div class="hairline my-4"></div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-white/90">Totaal</span>
                            <span class="font-display text-2xl gold-text">€ <?= h(money($total)) ?></span>
                        </div>

                        <div class="mt-5 rounded-2xl p-4 luxe-ring bg-black/25 text-xs text-white/60">
                            Je gegevens worden veilig verwerkt. Je wordt doorgestuurd naar Mollie om af te rekenen.
                        </div>

                        <?php if ($checkoutError): ?>
                            <div class="mt-4 rounded-2xl p-4 luxe-ring bg-red-500/10 text-xs text-red-200">
                                <?= h($checkoutError) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button
                        form="checkout-form"
                        type="submit"
                        class="btn btn-primary btn-md btn-block shadow-glow mt-6 <?= $cartCount === 0 ? 'btn-disabled' : '' ?>"
                        <?= $cartCount === 0 ? 'disabled' : '' ?>
                    >
                        Bestelling afronden
                    </button>

                    <div class="mt-4 text-xs text-white/45">
                        Afrekenen via Mollie (iDEAL, creditcard, etc.).
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl p-5 luxe-ring bg-black/25">
                    <div class="text-xs tracking-[.28em] uppercase text-white/60">Exclusief</div>
                    <div class="mt-2 text-sm text-white/80">Ambachtelijke parfums, snel bezorgd.</div>
                </div>
                <div class="rounded-2xl p-5 luxe-ring bg-black/25">
                    <div class="text-xs tracking-[.28em] uppercase text-white/60">Support</div>
                    <div class="mt-2 text-sm text-white/80">Persoonlijk advies bij vragen.</div>
                </div>
            </div>
        </div>

    </div>
</section>

<footer class="py-14 border-t border-white/5">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mt-8 flex flex-wrap items-center justify-between gap-4 text-xs text-white/45">
            <div>© <?= date('Y') ?> ASA Parfums. All rights reserved.</div>
            <div class="flex gap-4">
                <a class="hover:text-white transition" href="products.php">Producten</a>
                <a class="hover:text-white transition" href="cart.php">Winkelwagen</a>
                <a class="hover:text-white transition" href="checkout.php">Checkout</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
