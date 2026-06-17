<?php
/**
 * POST /admin/actions/create_purchase.php
 * Body (JSON): { user_id, property_id, total_price, initial_payment?, csrf_token }
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_api();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!csrf_valid($input['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session has expired — please refresh the page and try again.'], 419);
}

$userId = filter_var($input['user_id'] ?? null, FILTER_VALIDATE_INT);
$propertyId = filter_var($input['property_id'] ?? null, FILTER_VALIDATE_INT);
$totalPrice = filter_var($input['total_price'] ?? null, FILTER_VALIDATE_FLOAT);
$initialPayment = filter_var($input['initial_payment'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;

$errors = [];
if (!$userId) $errors[] = 'Please select an investor.';
if (!$propertyId) $errors[] = 'Please select a property.';
if ($totalPrice === false || $totalPrice <= 0) $errors[] = 'Please enter a valid total contract price.';
if ($initialPayment < 0) $errors[] = 'The initial deposit cannot be negative.';
if ($initialPayment > $totalPrice) $errors[] = 'The initial deposit cannot exceed the total price.';

$pdo = get_db();

if ($userId) {
    $check = $pdo->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'client'");
    $check->execute([$userId]);
    $investor = $check->fetch();
    if (!$investor) $errors[] = 'That investor could not be found.';
}
if ($propertyId) {
    $check = $pdo->prepare('SELECT id, name FROM properties WHERE id = ?');
    $check->execute([$propertyId]);
    $property = $check->fetch();
    if (!$property) $errors[] = 'That property could not be found.';
}

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
}

$pdo->beginTransaction();

$pdo->prepare('INSERT INTO purchases (user_id, property_id, total_price, amount_paid) VALUES (?, ?, ?, ?)')
    ->execute([$userId, $propertyId, $totalPrice, $initialPayment]);
$purchaseId = (int) $pdo->lastInsertId();

if ($initialPayment > 0) {
    $pdo->prepare("INSERT INTO payments (purchase_id, amount, label) VALUES (?, ?, 'Initial deposit')")
        ->execute([$purchaseId, $initialPayment]);
}

$pdo->commit();

json_response([
    'ok' => true,
    'message' => $investor['name'] . ' has been assigned to ' . $property['name'] . '.',
]);
