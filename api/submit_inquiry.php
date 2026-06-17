<?php
/**
 * POST /api/submit_inquiry.php
 * Body (JSON): { type: 'booking'|'contact'|'estimate', name, email, phone?, property_id?, preferred_date?, message }
 *
 * One endpoint backs three forms (book-a-session.php, contact.php, and the
 * "Request a Detailed Quote" action on estimate.php) since they all reduce
 * to the same shape: who's asking, how to reach them, and what they want.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$type = $input['type'] ?? '';
$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$phone = trim((string) ($input['phone'] ?? ''));
$propertyId = filter_var($input['property_id'] ?? null, FILTER_VALIDATE_INT);
$propertyId = $propertyId ?: null; // filter_var() returns false (not null) on an empty/missing value
$preferredDate = trim((string) ($input['preferred_date'] ?? ''));
$message = trim((string) ($input['message'] ?? ''));

$errors = [];

if (!in_array($type, ['booking', 'contact', 'estimate'], true)) {
    $errors[] = 'Invalid request type.';
}
if ($name === '') {
    $errors[] = 'Please tell us your name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($message) < 10 || strlen($message) > 2000) {
    $errors[] = 'Please add a message between 10 and 2000 characters.';
}

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
}

if ($propertyId) {
    $check = get_db()->prepare('SELECT id FROM properties WHERE id = ?');
    $check->execute([$propertyId]);
    if (!$check->fetch()) {
        $propertyId = null;
    }
}

$stmt = get_db()->prepare("
    INSERT INTO inquiries (type, name, email, phone, property_id, preferred_date, message, status)
    VALUES (:type, :name, :email, :phone, :property_id, :preferred_date, :message, 'new')
");
$stmt->execute([
    ':type' => $type,
    ':name' => $name,
    ':email' => $email,
    ':phone' => $phone,
    ':property_id' => $propertyId,
    ':preferred_date' => $preferredDate,
    ':message' => $message,
]);

$confirmations = [
    'booking' => "Thanks, $name — your session request has been received. Our team will confirm a time by email shortly.",
    'contact' => "Thanks, $name — we've received your message and will reply within one business day.",
    'estimate' => "Thanks, $name — we've received your build estimate and a consultant will follow up with a refined quote.",
];

json_response(['ok' => true, 'message' => $confirmations[$type]]);
