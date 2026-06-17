<?php
/**
 * POST /api/register.php  { name, email, password }
 * Creates a 'client' account — there's no public path to register as an
 * admin, that's seeded/assigned directly in the database.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');

$errors = [];
if ($name === '') {
    $errors[] = 'Please enter your full name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 8) {
    $errors[] = 'Your password must be at least 8 characters.';
}

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
}

$pdo = get_db();
$check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    json_response(['ok' => false, 'error' => 'An account with that email already exists. Try signing in instead.'], 409);
}

$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'client')");
$stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

$newUser = ['id' => (int) $pdo->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'client'];
login_user($newUser);

json_response(['ok' => true, 'redirect' => '/dashboard.php']);
