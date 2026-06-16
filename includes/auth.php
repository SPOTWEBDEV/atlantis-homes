<?php
/**
 * Session & authentication helpers.
 * Include this after db.php on any page or endpoint that needs to know
 * who is logged in, or that needs to gate access by role.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Returns the logged-in user's row (array) or null if no one is logged in.
 * Cached per-request so repeated calls don't hit the database twice.
 */
function current_user(): ?array
{
    static $user = false; // false = "not looked up yet", null = "looked up, no user"

    if ($user !== false) {
        return $user;
    }

    if (empty($_SESSION['user_id'])) {
        $user = null;
        return $user;
    }

    $stmt = get_db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    $user = $row ?: null;

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

function login_user(array $userRow): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userRow['id'];
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}

/** Redirects to the login page if no one is signed in. Use on protected *pages*. */
function require_login(string $redirectTo = '/login.php'): void
{
    if (!is_logged_in()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/** Redirects non-admins away. Use at the top of every admin/*.php page. */
function require_admin(string $redirectTo = '/login.php'): void
{
    if (!is_admin()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/** JSON 401/403 response for API endpoints, instead of an HTML redirect. */
function require_login_api(): void
{
    if (!is_logged_in()) {
        json_response(['ok' => false, 'error' => 'You need to sign in to do that.'], 401);
    }
}

function require_admin_api(): void
{
    if (!is_admin()) {
        json_response(['ok' => false, 'error' => 'Admin access required.'], 403);
    }
}

/** A small CSRF token, generated once per session, embedded in forms and AJAX payloads. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/** Sends a JSON body with the right header and stops execution. Used by every api/*.php endpoint. */
function json_response($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
