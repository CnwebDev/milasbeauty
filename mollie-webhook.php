<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Mollie\Api\MollieApiClient;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Mollie webhook called with invalid method.');
    http_response_code(405);
    exit;
}

$paymentId = trim((string)($_POST['id'] ?? ''));
if ($paymentId === '') {
    error_log('Mollie webhook missing payment id.');
    http_response_code(400);
    exit;
}

$apiKey = env('MOLLIE_API_KEY');
if ($apiKey === null) {
    error_log('Mollie webhook missing API key.');
    http_response_code(500);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mollie = new MollieApiClient();
$mollie->setApiKey($apiKey);

try {
    $payment = $mollie->payments->get($paymentId);
} catch (Throwable $e) {
    error_log('Mollie webhook payment fetch failed: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

$pdo = db();

$stmt = $pdo->prepare('SELECT * FROM orders WHERE mollie_payment_id = ? LIMIT 1');
$stmt->execute([$paymentId]);
$order = $stmt->fetch();

if (!$order) {
    error_log('Mollie webhook order not found for payment: ' . $paymentId);
    http_response_code(200);
    exit;
}

$status = (string)($payment->status ?? 'open');
$paidAt = null;
if ($status === 'paid') {
    $paidAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
}

$pdo->beginTransaction();
$update = $pdo->prepare('UPDATE orders SET payment_status = ?, status = ?, paid_at = COALESCE(paid_at, ?) WHERE id = ?');
$update->execute([
    $status,
    $status === 'paid' ? 'paid' : ($status === 'failed' ? 'failed' : 'pending'),
    $paidAt,
    $order['id'],
]);

if ($status === 'paid') {
    $_SESSION['cart'] = [];
}

if ($status === 'paid' && empty($order['email_sent_at'])) {
    $itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $itemsStmt->execute([$order['id']]);
    $items = $itemsStmt->fetchAll();

    $from = env('MAIL_FROM', 'no-reply@asaparfums.local');
    $subject = 'ASA Parfums bestelling #' . $order['id'];

    $lines = [];
    $lines[] = 'Bedankt voor je bestelling bij ASA Parfums.';
    $lines[] = '';
    $lines[] = 'Bestelnummer: ' . $order['id'];
    $lines[] = 'Naam: ' . $order['first_name'] . ' ' . $order['last_name'];
    $lines[] = 'E-mailadres: ' . $order['email'];
    $lines[] = 'Adres: ' . $order['address'] . ', ' . $order['zip'] . ' ' . $order['city'] . ', ' . $order['country'];
    if (!empty($order['notes'])) {
        $lines[] = 'Opmerkingen: ' . $order['notes'];
    }
    $lines[] = '';
    $lines[] = 'Items:';
    foreach ($items as $item) {
        $lines[] = '- ' . $item['product_name'] . ' x' . $item['qty'] . ' (€ ' . number_format((float)$item['line_total'], 2, ',', '.') . ')';
    }
    $lines[] = '';
    $lines[] = 'Subtotaal: € ' . number_format((float)$order['subtotal'], 2, ',', '.');
    $lines[] = 'Verzendkosten: € ' . number_format((float)$order['shipping'], 2, ',', '.');
    $lines[] = 'Totaal: € ' . number_format((float)$order['total'], 2, ',', '.');

    $message = implode("\r\n", $lines);
    $headers = implode("\r\n", [
        'From: ' . $from,
        'Reply-To: ' . $from,
        'Content-Type: text/plain; charset=UTF-8',
    ]);

    $customerMail = filter_var((string)$order['email'], FILTER_VALIDATE_EMAIL);
    if ($customerMail) {
        @mail($customerMail, $subject, $message, $headers);
    }
    @mail('connor@cnweb.nl', $subject, $message, $headers);

    $sentAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    $sentStmt = $pdo->prepare('UPDATE orders SET email_sent_at = ? WHERE id = ?');
    $sentStmt->execute([$sentAt, $order['id']]);
}

$pdo->commit();

http_response_code(200);
