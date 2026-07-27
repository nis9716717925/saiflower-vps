<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/homepage_premium.php';

$key = isset($_GET['occasion']) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['occasion'])) : '';
if ($key === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Missing occasion']);
    exit;
}

$tabs = homepage_get_occasion_tabs($conn);
$match = null;
foreach ($tabs as $tab) {
    if ($tab['key'] === $key) {
        $match = $tab;
        break;
    }
}

if (!$match) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Occasion not found']);
    exit;
}

$products = homepage_fetch_occasion_products($conn, $match, 10);
$html = homepage_render_occasion_cards($products);

echo json_encode([
    'ok' => true,
    'html' => $html,
    'cta' => $match['cta'],
    'link' => $match['list_link'],
]);
