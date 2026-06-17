<?php
/**
 * POST /admin/actions/save_property.php
 * Body (JSON): { id?, name, location, type, price_naira, bedrooms, bathrooms,
 *                size_sqm, roi_5yr_pct, roi_10yr_pct, milestone_stage,
 *                summary, overview, amenities (comma-separated string),
 *                floor_plan_url, image_url, featured, csrf_token }
 *
 * Omitting `id` creates a new property; including it updates that row.
 * This intentionally only touches the listing fields — milestone_stage
 * here just sets the *starting* stage on creation. Day-to-day stage
 * changes belong on admin/update-property.php, which also logs a dated
 * note for the client dashboard.
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_api();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!csrf_valid($input['csrf_token'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Your session has expired — please refresh the page and try again.'], 419);
}

$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$name = trim((string) ($input['name'] ?? ''));
$location = trim((string) ($input['location'] ?? ''));
$type = $input['type'] ?? '';
$price = filter_var($input['price_naira'] ?? null, FILTER_VALIDATE_FLOAT);
$bedrooms = filter_var($input['bedrooms'] ?? 0, FILTER_VALIDATE_INT);
$bathrooms = filter_var($input['bathrooms'] ?? 0, FILTER_VALIDATE_INT);
$sizeSqm = filter_var($input['size_sqm'] ?? 0, FILTER_VALIDATE_INT);
$roi5 = filter_var($input['roi_5yr_pct'] ?? 0, FILTER_VALIDATE_FLOAT);
$roi10 = filter_var($input['roi_10yr_pct'] ?? 0, FILTER_VALIDATE_FLOAT);
$milestone = $input['milestone_stage'] ?? 'Foundation';
$summary = trim((string) ($input['summary'] ?? ''));
$overview = trim((string) ($input['overview'] ?? ''));
$amenitiesRaw = trim((string) ($input['amenities'] ?? ''));
$floorPlanUrl = trim((string) ($input['floor_plan_url'] ?? ''));
$imageUrl = trim((string) ($input['image_url'] ?? ''));
$featured = !empty($input['featured']) ? 1 : 0;

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
if ($imageUrl === '') $errors[] = 'Please provide a cover image URL.';

if ($errors) {
    json_response(['ok' => false, 'error' => implode(' ', $errors)], 422);
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