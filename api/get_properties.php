<?php
/**
 * GET /api/get_properties.php?type=off-plan|under-construction|completed|all
 * Returns: { ok: true, properties: [...] }
 *
 * Backs the AJAX filter buttons on portfolio.php. Kept deliberately thin —
 * one prepared statement, one validated input — since this runs on every
 * filter click.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$allowedTypes = ['off-plan', 'under-construction', 'completed'];
$type = $_GET['type'] ?? 'all';

$pdo = get_db();

if ($type === 'all' || !in_array($type, $allowedTypes, true)) {
    $stmt = $pdo->query("SELECT * FROM properties ORDER BY featured DESC, id ASC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE type = ? ORDER BY id ASC");
    $stmt->execute([$type]);
}

$properties = $stmt->fetchAll();

// Shape the response with already-formatted display fields so portfolio.js
// stays a pure render layer with no business logic duplicated in JS.
$out = array_map(function ($p) {
    return [
        'id'           => (int) $p['id'],
        'name'         => $p['name'],
        'location'     => $p['location'],
        'type'         => $p['type'],
        'type_label'   => type_label($p['type']),
        'price'        => naira((float) $p['price_naira'], true),
        'bedrooms'     => (int) $p['bedrooms'],
        'bathrooms'    => (int) $p['bathrooms'],
        'size_sqm'     => (int) $p['size_sqm'],
        'roi_5yr_pct'  => (float) $p['roi_5yr_pct'],
        'roi_10yr_pct' => (float) $p['roi_10yr_pct'],
        'summary'      => $p['summary'],
        'image_url'    => $p['image_url'],
    ];
}, $properties);

echo json_encode(['ok' => true, 'count' => count($out), 'properties' => $out]);
