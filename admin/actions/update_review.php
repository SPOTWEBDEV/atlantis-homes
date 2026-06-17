<?php
/**
 * POST /admin/actions/update_review.php
 * Body (JSON): { id, action: 'approve' | 'reject' }
 * Header: X-CSRF-Token (must match the admin's session token)
 *
 * Approve flips status to 'approved' so it appears on reviews.php.
 * Reject deletes the row outright — there's no public-facing "rejected"
 * state to manage, matching the "Reject/Delete" action in the brief.
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_api();

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!csrf_valid($token)) {
    json_response(['ok' => false, 'error' => 'Your session has expired — please refresh the page and try again.'], 419);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
$action = $input['action'] ?? '';

if (!$id || !in_array($action, ['approve', 'reject'], true)) {
    json_response(['ok' => false, 'error' => 'Invalid request.'], 422);
}

$pdo = get_db();

if ($action === 'approve') {
    $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
    $stmt->execute([$id]);
    json_response(['ok' => true, 'status' => 'approved']);
}

// action === 'reject'
$stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
$stmt->execute([$id]);
json_response(['ok' => true, 'status' => 'deleted']);
