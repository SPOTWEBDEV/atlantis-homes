<?php
/**
 * POST /api/submit_review.php
 * Body (JSON): { rating, title, body, captcha, guest_name?, guest_email? }
 * guest_name/guest_email are required only when no one is logged in —
 * logged-in users are attributed via their session instead.
 *
 * New reviews always land as status='pending' and only appear publicly
 * once approved from admin/reviews.php.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$user = current_user();

$rating = filter_var($input['rating'] ?? null, FILTER_VALIDATE_INT);
$title = trim((string) ($input['title'] ?? ''));
$body = trim((string) ($input['body'] ?? ''));
$captcha = (bool) ($input['captcha'] ?? false);

$errors = [];

if (!$rating || $rating < 1 || $rating > 5) {
    $errors[] = 'Please choose a star rating from 1 to 5.';
}
if ($title === '' || strlen($title) > 120) {
    $errors[] = 'Please add a short review title (under 120 characters).';
}
if (strlen($body) < 20 || strlen($body) > 1000) {
    $errors[] = 'Your review should be between 20 and 1000 characters.';
}
if (!$captcha) {
    $errors[] = 'Please confirm the spam-check box.';
}

$guestName = '';
$guestEmail = '';

if (!$user) {
    $guestName = trim((string) ($input['guest_name'] ?? ''));
    $guestEmail = trim((string) ($input['guest_email'] ?? ''));

    if ($guestName === '') {
        $errors[] = 'Please tell us your name.';
    }
    if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
}

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
}

// "Verified owner" is earned, not claimed — only true if the logged-in
// user actually has a purchase on file. Guests are never verified.
$verifiedOwner = false;
if ($user) {
    $check = get_db()->prepare('SELECT COUNT(*) FROM purchases WHERE user_id = ?');
    $check->execute([$user['id']]);
    $verifiedOwner = ((int) $check->fetchColumn()) > 0;
}

$stmt = get_db()->prepare("
    INSERT INTO reviews (user_id, guest_name, guest_email, rating, title, body, verified_owner, status)
    VALUES (:user_id, :guest_name, :guest_email, :rating, :title, :body, :verified_owner, 'pending')
");
$stmt->execute([
    ':user_id' => $user['id'] ?? null,
    ':guest_name' => $guestName,
    ':guest_email' => $guestEmail,
    ':rating' => $rating,
    ':title' => $title,
    ':body' => $body,
    ':verified_owner' => $verifiedOwner ? 1 : 0,
]);

json_response(['ok' => true, 'message' => 'Thanks — your review has been submitted and is awaiting approval.']);
