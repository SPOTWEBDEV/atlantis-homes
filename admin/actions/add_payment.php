<?php
/**
 * POST /admin/actions/add_payment.php
 * Body (JSON): { purchase_id, amount, label?, csrf_token }
 * Inserts a payments row and bumps purchases.amount_paid by the same amount.
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_api();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!csrf_valid($input['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session has expired — please refresh the page and try again.'], 419);
}

$purchaseId = filter_var($input['purchase_id'] ?? null, FILTER_VALIDATE_INT);
$amount = filter_var($input['amount'] ?? null, FILTER_VALIDATE_FLOAT);
$label = trim((string) ($input['label'] ?? '')) ?: 'Installment';

if (!$purchaseId || $amount === false || $amount <= 0) {
    json_response(['ok' => false, 'error' => 'Please enter a valid payment amount.'], 422);
}

$pdo = get_db();
$check = $pdo->prepare('SELECT * FROM purchases WHERE id = ?');
$check->execute([$purchaseId]);
$purchase = $check->fetch();

if (!$purchase) {
    json_response(['ok' => false, 'error' => 'That purchase could not be found.'], 404);
}

$pdo->beginTransaction();
$pdo->prepare('INSERT INTO payments (purchase_id, amount, label) VALUES (?, ?, ?)')->execute([$purchaseId, $amount, $label]);
$newPaid = (float) $purchase['amount_paid'] + $amount;
$pdo->prepare('UPDATE purchases SET amount_paid = ? WHERE id = ?')->execute([$newPaid, $purchaseId]);
$pdo->commit();

$outstanding = max(0, (float) $purchase['total_price'] - $newPaid);
$paidPct = $purchase['total_price'] > 0 ? min(100, round(($newPaid / (float) $purchase['total_price']) * 100)) : 0;

json_response([
    'ok' => true,
    'message' => 'Payment of ' . naira($amount) . ' recorded.',
    'amount_paid' => naira($newPaid),
    'outstanding' => naira($outstanding),
    'paid_pct' => $paidPct,
]);
