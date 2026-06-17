
<?php
/**
 * POST /admin/actions/save_property.php  (multipart/form-data)
 * Fields: id?, name, location, type, price_naira, bedrooms, bathrooms,
 *         size_sqm, roi_5yr_pct, roi_10yr_pct, milestone_stage, summary,
 *         overview, amenities (comma-separated), featured, csrf_token,
 *         existing_image_url, existing_floor_plan_url,
 *         image (file, required when creating), floor_plan (file, optional)
 *
 * Omitting `id` creates a new property; including it updates that row.
 * Uploaded files replace the cover image / floor plan; if no file is
 * selected on an edit, the existing_* hidden fields preserve what's
 * already there. This intentionally only touches listing fields —
 * day-to-day milestone changes belong on admin/update-property.php.
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_api();

if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session has expired — please refresh the page and try again.'], 419);
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$name = trim((string) ($_POST['name'] ?? ''));
$location = trim((string) ($_POST['location'] ?? ''));
$type = $_POST['type'] ?? '';
$price = filter_var($_POST['price_naira'] ?? null, FILTER_VALIDATE_FLOAT);
$bedrooms = filter_var($_POST['bedrooms'] ?? 0, FILTER_VALIDATE_INT);
$bathrooms = filter_var($_POST['bathrooms'] ?? 0, FILTER_VALIDATE_INT);
$sizeSqm = filter_var($_POST['size_sqm'] ?? 0, FILTER_VALIDATE_INT);
$roi5 = filter_var($_POST['roi_5yr_pct'] ?? 0, FILTER_VALIDATE_FLOAT);
$roi10 = filter_var($_POST['roi_10yr_pct'] ?? 0, FILTER_VALIDATE_FLOAT);
$milestone = $_POST['milestone_stage'] ?? 'Foundation';
$summary = trim((string) ($_POST['summary'] ?? ''));
$overview = trim((string) ($_POST['overview'] ?? ''));
$amenitiesRaw = trim((string) ($_POST['amenities'] ?? ''));
$featured = !empty($_POST['featured']) ? 1 : 0;
$existingImageUrl = trim((string) ($_POST['existing_image_url'] ?? ''));
$existingFloorPlanUrl = trim((string) ($_POST['existing_floor_plan_url'] ?? ''));

$allowedTypes = ['off-plan', 'under-construction', 'completed'];
$allowedStages = ['Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed'];

$errors = [];
if ($name === '') $errors[] = 'Please enter a property name.';
if ($location === '') $errors[] = 'Please enter a location.';
if (!in_array($type, $allowedTypes, true)) $errors[] = 'Please choose a valid development type.';
if ($price === false || $price <= 0) $errors[] = 'Please enter a valid price.';
if (!in_array($milestone, $allowedStages, true)) $errors[] = 'Please choose a valid milestone stage.';
if ($summary === '' || strlen($summary) > 300) $errors[] = 'Please add a short summary (under 300 characters).';
if ($overview === '') $errors[] = 'Please add an overview description.';
if (!$id && empty($_FILES['image']['name'])) $errors[] = 'Please upload a cover image.';

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
}

// --- Handle file uploads (cover image required-on-create, floor plan optional) --
$allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$maxBytes = 5 * 1024 * 1024;
$uploadDir = __DIR__ . '/../../assets/uploads/';

function save_uploaded_image(array $file, string $prefix, string $uploadDir, array $allowedMime, int $maxBytes): string
{
    if ($file['size'] > $maxBytes) {
        json_response(['ok' => false, 'error' => 'Each image must be 5MB or smaller.'], 422);
    }
    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type'];
    if (!isset($allowedMime[$mime])) {
        json_response(['ok' => false, 'error' => 'Images must be JPEG, PNG, or WebP.'], 422);
    }
    $ext = $allowedMime[$mime];
    $filename = $prefix . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        json_response(['ok' => false, 'error' => 'Could not save the uploaded image.'], 500);
    }
    return 'assets/uploads/' . $filename;
}

$imageUrl = $existingImageUrl;
if (!empty($_FILES['image']['name'])) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        json_response(['ok' => false, 'error' => 'The cover image failed to upload — please try again.'], 422);
    }
    $imageUrl = save_uploaded_image($_FILES['image'], 'property', $uploadDir, $allowedMime, $maxBytes);
}

$floorPlanUrl = $existingFloorPlanUrl;
if (!empty($_FILES['floor_plan']['name'])) {
    if ($_FILES['floor_plan']['error'] !== UPLOAD_ERR_OK) {
        json_response(['ok' => false, 'error' => 'The floor plan image failed to upload — please try again.'], 422);
    }
    $floorPlanUrl = save_uploaded_image($_FILES['floor_plan'], 'floorplan', $uploadDir, $allowedMime, $maxBytes);
}

if ($imageUrl === '') {
    json_response(['ok' => false, 'error' => 'Please provide a cover image.'], 422);
}

$amenities = array_values(array_filter(array_map('trim', explode(',', $amenitiesRaw))));

$pdo = get_db();

if ($id) {
    $check = $pdo->prepare('SELECT id FROM properties WHERE id = ?');
    $check->execute([$id]);
    if (!$check->fetch()) {
        json_response(['ok' => false, 'error' => 'That property no longer exists.'], 404);
    }

    $stmt = $pdo->prepare("
        UPDATE properties SET
            name = :name, location = :location, type = :type, price_naira = :price_naira,
            bedrooms = :bedrooms, bathrooms = :bathrooms, size_sqm = :size_sqm,
            roi_5yr_pct = :roi_5yr_pct, roi_10yr_pct = :roi_10yr_pct, milestone_stage = :milestone_stage,
            summary = :summary, overview = :overview, amenities_json = :amenities_json,
            floor_plan_url = :floor_plan_url, image_url = :image_url, featured = :featured
        WHERE id = :id
    ");
    $params = ['id' => $id];
} else {
    $stmt = $pdo->prepare("
        INSERT INTO properties
            (name, location, type, price_naira, bedrooms, bathrooms, size_sqm, roi_5yr_pct, roi_10yr_pct,
             milestone_stage, summary, overview, amenities_json, floor_plan_url, image_url, featured)
        VALUES
            (:name, :location, :type, :price_naira, :bedrooms, :bathrooms, :size_sqm, :roi_5yr_pct, :roi_10yr_pct,
             :milestone_stage, :summary, :overview, :amenities_json, :floor_plan_url, :image_url, :featured)
    ");
    $params = [];
}

$params += [
    'name' => $name, 'location' => $location, 'type' => $type, 'price_naira' => $price,
    'bedrooms' => $bedrooms, 'bathrooms' => $bathrooms, 'size_sqm' => $sizeSqm,
    'roi_5yr_pct' => $roi5, 'roi_10yr_pct' => $roi10, 'milestone_stage' => $milestone,
    'summary' => $summary, 'overview' => $overview, 'amenities_json' => json_encode($amenities),
    'floor_plan_url' => $floorPlanUrl, 'image_url' => $imageUrl, 'featured' => $featured,
];
$stmt->execute($params);

$newId = $id ?: (int) $pdo->lastInsertId();

json_response([
    'ok' => true,
    'id' => $newId,
    'message' => $id ? "$name has been updated." : "$name has been added to the portfolio.",
]);
