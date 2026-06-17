<?php
/**
 * POST /admin/actions/update_inquiry.php  { id, status, csrf_token }
 * status must be 'new' or 'contacted'.
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
$status = $input['status'] ?? '';

if (!$id || !in_array($status, ['new', 'contacted'], true)) {
    json_response(['ok' => false, 'error' => 'Invalid request.'], 422);
}

$pdo = get_db();
$pdo->prepare('UPDATE inquiries SET status = ? WHERE id = ?')->execute([$status, $id]);

json_response(['ok' => true, 'status' => $status]);
