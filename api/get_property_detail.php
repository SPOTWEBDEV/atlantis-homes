<?php
/**
 * GET /api/get_property_detail.php?id=123
 * Returns full detail for one property — overview copy, amenities list,
 * and the floor-plan image — used to populate the tabbed expansion on a
 * property card without a page reload.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'A valid property id is required.']);
    exit;
}

$stmt = get_db()->prepare('SELECT * FROM properties WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Property not found.']);
    exit;
}

echo json_encode([
    'ok' => true,
    'property' => [
        'id'             => (int) $p['id'],
        'name'           => $p['name'],
        'location'       => $p['location'],
        'type_label'     => type_label($p['type']),
        'price'          => naira((float) $p['price_naira']),
        'bedrooms'       => (int) $p['bedrooms'],
        'bathrooms'      => (int) $p['bathrooms'],
        'size_sqm'       => (int) $p['size_sqm'],
        'roi_5yr_pct'    => (float) $p['roi_5yr_pct'],
        'roi_10yr_pct'   => (float) $p['roi_10yr_pct'],
        'milestone_stage'=> $p['milestone_stage'],
        'overview'       => $p['overview'],
        'amenities'      => json_decode($p['amenities_json'], true) ?: [],
        'floor_plan_url' => $p['floor_plan_url'],
        'image_url'      => $p['image_url'],
    ],
]);
