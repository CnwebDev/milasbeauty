<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\MollieApiClient;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    foreach ($products as $p) {
        $byId[(int)$p['id']] = $p;
    }

    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if (!isset($byId[$pid])) {
            continue;
        }
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

$navHomeHref = '/';
$navPricesHref = '/prijzen.php';
$navContactHref = '/#contact';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout | Mila Beauty</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Taviraj:wght@600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandPink: "#ff80d5",
                        brandPinkSoft: "#ffccef",
                        brandBg: "#f4f3ef",
                        brandAccent: "#CA9A8E",
                        brandText: "#404447",
                        headerPink: "rgba(255,128,213,0.61)",
                    },
                    fontFamily: {
                        sans: ["Montserrat", "ui-sans-serif", "system-ui"],
                        serif: ["Taviraj", "ui-serif", "Georgia"],
                    },
                    boxShadow: {
                        card: "4px 4px 8px 0px rgba(0,0,0,0.1)",
                        img: "4px 4px 20px 0px rgba(132,153,148,0.5)",
                        insetGlow: "0 0 10px 0 #CA9A8E inset, 0 0 20px 2px #CA9A8E",
                    },
                    borderRadius: {
                        fancy: "20px 0px 20px 0px",
                        fancyImg: "100px 0px 0px 0px",
                    },
                    maxWidth: { container: "1220px" },
                }
            }
        }
    </script>

    <link rel="stylesheet" href="/assets/css/theme.css" />
</head>

<body class="text-brandText font-sans">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<section class="section-hero py-10">
    <div class="mx-auto max-w-container px-4">
        <h1 class="font-serif text-4xl md:text-5xl leading-tight text-brandText">Afrekenen</h1>
        <p class="mt-3 max-w-2xl text-brandText/80">
            Controleer je items, vul je gegevens in en rond de bestelling af via Mollie.
        </p>
    </div>
</section>

<section class="pb-16">
    <div class="mx-auto max-w-container px-4 grid gap-8 lg:grid-cols-12 items-start">
        <form id="checkout-form" method="post" class="lg:col-span-7 space-y-6">
            <input type="hidden" name="action" value="create_payment">
            <div class="rounded-2xl bg-white shadow-card p-6">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="font-semibold text-brandText">Jouw producten</div>
                    <span class="text-xs text-brandText/60">Wijzigingen zijn hier niet mogelijk</span>
                </div>

                <?php if ($cartCount === 0): ?>
                    <div class="mt-6 rounded-xl bg-brandBg px-4 py-4 text-sm text-brandText/70">
                        Je winkelwagen is leeg. <a class="font-semibold text-brandAccent" href="products.php">Bekijk producten</a>.
                    </div>
                <?php else: ?>
                    <div class="mt-6 space-y-4">
                        <?php foreach ($items as $it): ?>
                            <div class="rounded-2xl border border-black/5 bg-brandBg/60 p-4 flex gap-4 items-center flex-wrap">
                                <a href="product.php?slug=<?= urlencode((string)$it['slug']) ?>" class="h-20 w-20 rounded-xl overflow-hidden bg-white shadow-card flex items-center justify-center">
                                    <?php if (!empty($it['main_image'])): ?>
                                        <img src="<?= h((string)$it['main_image']) ?>" class="h-full w-full object-cover" alt="">
                                    <?php else: ?>
                                        <span class="text-xs text-brandText/50">Geen foto</span>
                                    <?php endif; ?>
                                </a>

                                <div class="flex-1 min-w-[220px]">
                                    <a class="font-serif text-xl text-brandText" href="product.php?slug=<?= urlencode((string)$it['slug']) ?>">
                                        <?= h($it['name']) ?>
                                    </a>
                                    <div class="mt-1 text-sm text-brandText/70">
                                        € <?= h(money($it['price'])) ?>
                                        <?php if (!empty($it['volume_ml'])): ?>
                                            <span class="text-brandText/50">/ <?= (int)$it['volume_ml'] ?>ml</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2 text-xs text-brandText/60">
                                        Aantal: <?= (int)$it['qty'] ?> · Subtotaal: € <?= h(money($it['line'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="rounded-2xl bg-white shadow-card p-6">
                <div class="font-semibold text-brandText">Jouw gegevens</div>
                <p class="mt-2 text-xs text-brandText/60">Vul je gegevens in zodat we je bestelling kunnen voorbereiden.</p>

                <div class="mt-6 grid gap-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="text-xs font-semibold text-brandText/60">
                            Voornaam
                            <input class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" type="text" name="first_name" placeholder="Sarah" required>
                        </label>
                        <label class="text-xs font-semibold text-brandText/60">
                            Achternaam
                            <input class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" type="text" name="last_name" placeholder="Bakker" required>
                        </label>
                    </div>
                    <label class="text-xs font-semibold text-brandText/60">
                        E-mailadres
                        <input class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" type="email" name="email" placeholder="sarah@email.nl" required>
                    </label>
                    <label class="text-xs font-semibold text-brandText/60">
                        Telefoon
                        <input class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" type="tel" name="phone" placeholder="06 12345678">
                    </label>
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="text-xs font-semibold text-brandText/60">
                            Straat en huisnummer
                            <input class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" type="text" name="address" placeholder="Herengracht 15" required>
                        </label>
                        <label class="text-xs font-semibold text-brandText/60">
                            Postcode
                            <input class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" type="text" name="zip" placeholder="1017 AB" required>
                        </label>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="text-xs font-semibold text-brandText/60">
                            Plaats
                            <input class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" type="text" name="city" placeholder="Amsterdam" required>
                        </label>
                        <label class="text-xs font-semibold text-brandText/60">
                            Land
                            <select class="mt-2 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" name="country" required>
                                <option value="Nederland">Nederland</option>
                                <option value="België">België</option>
                                <option value="Duitsland">Duitsland</option>
                            </select>
                        </label>
                    </div>
                    <label class="text-xs font-semibold text-brandText/60">
                        Opmerkingen (optioneel)
                        <textarea class="mt-2 h-24 w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3" name="notes" placeholder="Laat een bericht achter voor ons team..."></textarea>
                    </label>
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-card p-6">
                <div class="font-semibold text-brandText">Bezorging & betaling</div>
                <div class="mt-5 space-y-3 text-sm text-brandText/70">
                    <label class="flex items-center gap-3 rounded-2xl border border-black/5 bg-brandBg/60 p-4">
                        <input type="radio" name="shipping" value="standard" checked class="accent-brandAccent">
                        <div>
                            <div class="text-brandText">Standaard verzending</div>
                            <div class="text-xs text-brandText/60">2-3 werkdagen · Gratis boven € 75</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-black/5 bg-brandBg/60 p-4">
                        <input type="radio" name="shipping" value="express" class="accent-brandAccent">
                        <div>
                            <div class="text-brandText">Express</div>
                            <div class="text-xs text-brandText/60">Volgende werkdag · € 9,95</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-black/5 bg-brandBg/60 p-4">
                        <input type="radio" name="payment" value="ideal" checked class="accent-brandAccent">
                        <div>
                            <div class="text-brandText">iDEAL</div>
                            <div class="text-xs text-brandText/60">Veilig betalen via jouw bank</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-black/5 bg-brandBg/60 p-4">
                        <input type="radio" name="payment" value="card" class="accent-brandAccent">
                        <div>
                            <div class="text-brandText">Creditcard</div>
                            <div class="text-xs text-brandText/60">Visa, MasterCard, Amex</div>
                        </div>
                    </label>
                </div>
            </div>
        </form>

        <div class="lg:col-span-5">
            <div class="rounded-2xl bg-white shadow-card p-6">
                <div class="font-semibold text-brandText">Overzicht</div>

                <div class="mt-5 space-y-3 text-sm text-brandText/80">
                    <div class="flex items-center justify-between">
                        <span>Subtotaal</span>
                        <span>€ <?= h(money($subtotal)) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Verzendkosten</span>
                        <span><?= $shipping == 0.0 ? 'Gratis' : '€ ' . h(money($shipping)) ?></span>
                    </div>
                    <div class="border-t border-black/5 pt-4 flex items-center justify-between font-semibold text-brandText">
                        <span>Totaal</span>
                        <span>€ <?= h(money($total)) ?></span>
                    </div>

                    <div class="mt-4 rounded-xl bg-brandBg px-4 py-3 text-xs">
                        Je wordt doorgestuurd naar Mollie om veilig af te rekenen.
                    </div>

                    <?php if ($checkoutError): ?>
                        <div class="mt-4 rounded-xl bg-red-100 px-4 py-3 text-xs text-red-700">
                            <?= h($checkoutError) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button
                    form="checkout-form"
                    type="submit"
                    class="btn-primary mt-6 w-full justify-center <?= $cartCount === 0 ? 'opacity-60 cursor-not-allowed' : '' ?>"
                    <?= $cartCount === 0 ? 'disabled' : '' ?>
                >
                    Bestelling afronden
                </button>

                <div class="mt-4 text-xs text-brandText/60">
                    Afrekenen via Mollie (iDEAL, creditcard, etc.).
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-white shadow-card p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Exclusief</div>
                    <div class="mt-2 text-sm text-brandText/80">Ambachtelijke producten, snel bezorgd.</div>
                </div>
                <div class="rounded-2xl bg-white shadow-card p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-brandText/60">Support</div>
                    <div class="mt-2 text-sm text-brandText/80">Persoonlijk advies bij vragen.</div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
