<?php
/**
 * POST /admin/actions/delete_property.php  { id, csrf_token }
 * Deleting a property cascades to its property_updates, purchases, and
 * payments rows via the foreign key ON DELETE CASCADE constraints.
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
    json_response(['ok' => false, 'error' => 'Invalid property.'], 422);
}

$pdo = get_db();
$check = $pdo->prepare('SELECT name FROM properties WHERE id = ?');
$check->execute([$id]);
$property = $check->fetch();

if (!$property) {
    json_response(['ok' => false, 'error' => 'That property was already removed.'], 404);
}

$pdo->prepare('DELETE FROM properties WHERE id = ?')->execute([$id]);

json_response(['ok' => true, 'message' => $property['name'] . ' has been removed from the portfolio.']);
