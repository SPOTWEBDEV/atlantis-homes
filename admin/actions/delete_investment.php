<?php
/**
 * POST /admin/actions/delete_investment.php  { id, csrf_token }
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_api();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!csrf_valid($input['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session has expired — please refresh the page and try again.'], 419);
}

$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    json_response(['ok' => false, 'error' => 'Invalid opportunity.'], 422);
}

$pdo = get_db();
$check = $pdo->prepare('SELECT name FROM investment_opportunities WHERE id = ?');
$check->execute([$id]);
$inv = $check->fetch();

if (!$inv) {
    json_response(['ok' => false, 'error' => 'That opportunity was already removed.'], 404);
}

$pdo->prepare('DELETE FROM investment_opportunities WHERE id = ?')->execute([$id]);
// Any inquiries that referenced it keep their investment_id as a dangling
// reference for history; LEFT JOINs elsewhere handle a missing match gracefully.

json_response(['ok' => true, 'message' => $inv['name'] . ' has been removed.']);
