<?php
/**
 * POST /api/submit_inquiry.php
 * Body (JSON): { type: 'booking'|'contact'|'estimate'|'investment', name, email,
 *                phone?, property_id?, investment_id?, preferred_date?, message,
 *                spec_details? }
 *
 * One endpoint backs every lead-capture form on the site (booking, contact,
 * the estimate quote request, and the Invest Now modal) since they all
 * reduce to the same shape: who's asking, how to reach them, and what they
 * want. spec_details is an optional JSON string of structured fields (e.g.
 * the exact build spec from the estimator) so the admin view can render a
 * clean breakdown instead of parsing free text.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$type = $input['type'] ?? '';
$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$phone = trim((string) ($input['phone'] ?? ''));
$propertyId = filter_var($input['property_id'] ?? null, FILTER_VALIDATE_INT);
$propertyId = $propertyId ?: null; // filter_var() returns false (not null) on an empty/missing value
$investmentId = filter_var($input['investment_id'] ?? null, FILTER_VALIDATE_INT);
$investmentId = $investmentId ?: null;
$preferredDate = trim((string) ($input['preferred_date'] ?? ''));
$message = trim((string) ($input['message'] ?? ''));
$specDetailsRaw = $input['spec_details'] ?? '';

$errors = [];

if (!in_array($type, ['booking', 'contact', 'estimate', 'investment'], true)) {
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

if ($investmentId) {
    $check = get_db()->prepare('SELECT id FROM investment_opportunities WHERE id = ?');
    $check->execute([$investmentId]);
    if (!$check->fetch()) {
        $investmentId = null;
    }
}

// A signed-in investor can only request a given opportunity once — repeat
// clicks (or a second visit) shouldn't pile up duplicate leads for admin.
if ($type === 'investment' && $investmentId && $user) {
    $dupeCheck = get_db()->prepare("
        SELECT id FROM inquiries WHERE type = 'investment' AND investment_id = ? AND user_id = ?
    ");
    $dupeCheck->execute([$investmentId, $user['id']]);
    if ($dupeCheck->fetch()) {
        json_response(['ok' => false, 'error' => "You've already requested to invest in this opportunity — our team will be in touch."], 409);
    }
}

// spec_details arrives as a JSON string (built client-side); re-encode
// defensively so we only ever store valid, reasonably-sized JSON.
$specDetails = '';
if (is_string($specDetailsRaw) && $specDetailsRaw !== '') {
    $decoded = json_decode($specDetailsRaw, true);
    if (is_array($decoded)) {
        $specDetails = substr(json_encode($decoded), 0, 4000);
    }
}

$stmt = get_db()->prepare("
    INSERT INTO inquiries (type, user_id, name, email, phone, property_id, investment_id, preferred_date, message, spec_details, status)
    VALUES (:type, :user_id, :name, :email, :phone, :property_id, :investment_id, :preferred_date, :message, :spec_details, 'new')
");
$stmt->execute([
    ':type' => $type,
    ':user_id' => $user['id'] ?? null,
    ':name' => $name,
    ':email' => $email,
    ':phone' => $phone,
    ':property_id' => $propertyId,
    ':investment_id' => $investmentId,
    ':preferred_date' => $preferredDate,
    ':message' => $message,
    ':spec_details' => $specDetails,
]);

$confirmations = [
    'booking' => "Thanks, $name — your session request has been received. Our team will confirm a time by email shortly.",
    'contact' => "Thanks, $name — we've received your message and will reply within one business day.",
    'estimate' => "Thanks, $name — we've received your build estimate and a consultant will follow up with a refined quote.",
    'investment' => "Thanks, $name — your investment reservation has been received. An investment consultant will follow up to finalise the contract.",
];

json_response(['ok' => true, 'message' => $confirmations[$type]]);
