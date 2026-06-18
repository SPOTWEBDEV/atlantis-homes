<?php
/**
 * POST /admin/actions/update_purchase.php
 * Body (JSON): { purchase_id, total_price, milestone, csrf_token }
 *
 * Updates purchases.total_price directly, and properties.milestone_stage
 * for that purchase's property (milestone lives on the property since one
 * construction site serves every investor in it). Also logs a
 * property_updates row so this stays consistent with the dedicated
 * Milestone Updater's history. Both dashboard.php (investor view) and
 * admin/properties.php read live from these tables, so the change shows
 * up immediately everywhere — no caching to invalidate.
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
$totalPrice = filter_var($input['total_price'] ?? null, FILTER_VALIDATE_FLOAT);
$milestone = $input['milestone'] ?? '';
$allowedStages = ['Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed'];

$errors = [];
if (!$purchaseId) $errors[] = 'Invalid purchase.';
if ($totalPrice === false || $totalPrice <= 0) $errors[] = 'Please enter a valid total price.';
if (!in_array($milestone, $allowedStages, true)) $errors[] = 'Please choose a valid milestone stage.';

$pdo = get_db();
$check = $pdo->prepare('SELECT * FROM purchases WHERE id = ?');
$check->execute([$purchaseId]);
$purchase = $check->fetch();
if (!$purchase) $errors[] = 'That purchase could not be found.';

if ($totalPrice !== false && $purchase && $totalPrice < (float) $purchase['amount_paid']) {
    $errors[] = 'Total price cannot be less than the amount already paid (' . naira((float) $purchase['amount_paid']) . ').';
}

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
}

$admin = current_user();

$pdo->beginTransaction();

$pdo->prepare('UPDATE purchases SET total_price = ? WHERE id = ?')->execute([$totalPrice, $purchaseId]);

$propStmt = $pdo->prepare('SELECT milestone_stage FROM properties WHERE id = ?');
$propStmt->execute([$purchase['property_id']]);
$currentStage = $propStmt->fetchColumn();

if ($currentStage !== $milestone) {
    $pdo->prepare('UPDATE properties SET milestone_stage = ? WHERE id = ?')->execute([$milestone, $purchase['property_id']]);
    $pdo->prepare("
        INSERT INTO property_updates (property_id, admin_id, milestone, note, photo_path)
        VALUES (?, ?, ?, ?, '')
    ")->execute([$purchase['property_id'], $admin['id'], $milestone, 'Milestone updated from the Investor Ledger.']);
}

$pdo->commit();

$outstanding = max(0, $totalPrice - (float) $purchase['amount_paid']);
$paidPct = $totalPrice > 0 ? min(100, round(((float) $purchase['amount_paid'] / $totalPrice) * 100)) : 0;

json_response([
    'ok' => true,
    'message' => 'Purchase updated.',
    'total_price' => naira($totalPrice),
    'outstanding' => naira($outstanding),
    'paid_pct' => $paidPct,
    'milestone' => $milestone,
]);
