<?php
/**
 * POST /admin/actions/update_property.php  (multipart/form-data)
 * Fields: property_id, milestone, note, csrf_token, photos[] (optional files)
 *
 * Updates properties.milestone_stage and inserts a row into
 * property_updates as a timestamped log entry, so the client dashboard's
 * milestone tracker and the public Property Management table both stay
 * current immediately.
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_api();

if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session has expired — please refresh the page and try again.'], 419);
}

$pdo = get_db();
$admin = current_user();

$propertyId = filter_var($_POST['property_id'] ?? null, FILTER_VALIDATE_INT);
$milestone = $_POST['milestone'] ?? '';
$note = trim($_POST['note'] ?? '');
$allowedStages = ['Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed'];

$errors = [];
if (!$propertyId) {
    $errors[] = 'Please choose a property.';
}
if (!in_array($milestone, $allowedStages, true)) {
    $errors[] = 'Please choose a valid milestone stage.';
}
if (strlen($note) < 5 || strlen($note) > 600) {
    $errors[] = 'Your update note should be between 5 and 600 characters.';
}

$check = $pdo->prepare('SELECT id, name FROM properties WHERE id = ?');
$check->execute([$propertyId]);
$property = $check->fetch();
if (!$property) {
    $errors[] = 'That property could not be found.';
}

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
}

// --- Handle (simulated) site photo uploads -------------------------------
$allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$maxBytes = 5 * 1024 * 1024;
$maxFiles = 8;
$savedPaths = [];

if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
    $count = count($_FILES['photos']['name']);
    if ($count > $maxFiles) {
        json_response(['ok' => false, 'error' => "Please upload at most $maxFiles photos at a time."], 422);
    }

    $uploadDir = __DIR__ . '/../../assets/uploads/';

    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue; // empty file input slot
        }
        if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
            json_response(['ok' => false, 'error' => 'One of the photos failed to upload — please try again.'], 422);
        }
        if ($_FILES['photos']['size'][$i] > $maxBytes) {
            json_response(['ok' => false, 'error' => 'Each photo must be 5MB or smaller.'], 422);
        }

        $tmpPath = $_FILES['photos']['tmp_name'][$i];
        $mime = function_exists('mime_content_type') ? mime_content_type($tmpPath) : $_FILES['photos']['type'][$i];

        if (!isset($allowedMime[$mime])) {
            json_response(['ok' => false, 'error' => 'Photos must be JPEG, PNG, or WebP images.'], 422);
        }

        $ext = $allowedMime[$mime];
        $filename = 'site-' . $propertyId . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($tmpPath, $destination)) {
            json_response(['ok' => false, 'error' => 'Could not save one of the uploaded photos.'], 500);
        }

        $savedPaths[] = 'assets/uploads/' . $filename;
    }
}

// --- Persist ---------------------------------------------------------------
$pdo->beginTransaction();

$pdo->prepare('UPDATE properties SET milestone_stage = ? WHERE id = ?')->execute([$milestone, $propertyId]);

$insert = $pdo->prepare("
    INSERT INTO property_updates (property_id, admin_id, milestone, note, photo_path)
    VALUES (:property_id, :admin_id, :milestone, :note, :photo_path)
");
$insert->execute([
    ':property_id' => $propertyId,
    ':admin_id' => $admin['id'],
    ':milestone' => $milestone,
    ':note' => $note,
    ':photo_path' => implode(',', $savedPaths),
]);
$updateId = (int) $pdo->lastInsertId();

$pdo->commit();

json_response([
    'ok' => true,
    'message' => 'Milestone update posted for ' . $property['name'] . '.',
    'update' => [
        'milestone' => $milestone,
        'note' => $note,
        'photo_count' => count($savedPaths),
        'admin_name' => $admin['name'],
        'created_label' => 'just now',
    ],
]);
