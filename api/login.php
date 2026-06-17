<?php
/**
 * POST /api/login.php  { email, password }
 * Returns: { ok: true, redirect: "/dashboard.php" | "/admin/index.php" } or { ok: false, error }
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

if ($email === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Please enter both your email and password.'], 422);
}

$stmt = get_db()->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    // Same generic message either way — don't reveal whether the email exists.
    json_response(['ok' => false, 'error' => 'Incorrect email or password.'], 401);
}

login_user($user);

json_response([
    'ok' => true,
    'redirect' => $user['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php',
]);
