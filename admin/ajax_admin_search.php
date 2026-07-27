<?php
// ajax_admin_search.php
// Returns JSON array of matching items across flowers, cakes, gifts

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists(__DIR__ . '/auth_check.php')) {
    require_once __DIR__ . '/auth_check.php';
} elseif (file_exists($root . '/admin/auth_check.php')) {
    require_once $root . '/admin/auth_check.php';
}

require_once $root . '/config.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
if (empty($query)) {
    echo json_encode([]);
    exit;
}

$q_safe = $conn->real_escape_string($query);
$results = [];
$limit_per_category = 4; // limit results so the dropdown isn't too huge

// Search flowers
$sql_flowers = "SELECT id, name, image FROM flowers WHERE name LIKE '%$q_safe%' ORDER BY id DESC LIMIT $limit_per_category";
$res_flowers = $conn->query($sql_flowers);
if ($res_flowers) {
    while ($row = $res_flowers->fetch_assoc()) {
        $row['type'] = 'flower';
        $row['link'] = 'edit-flower.php?id=' . $row['id'];
        $results[] = $row;
    }
}

// Search cakes
$sql_cakes = "SELECT id, name, image FROM cakes WHERE name LIKE '%$q_safe%' ORDER BY id DESC LIMIT $limit_per_category";
$res_cakes = $conn->query($sql_cakes);
if ($res_cakes) {
    while ($row = $res_cakes->fetch_assoc()) {
        $row['type'] = 'cake';
        $row['link'] = 'edit-cake.php?id=' . $row['id'];
        $results[] = $row;
    }
}

// Search gifts
$sql_gifts = "SELECT id, name, image FROM gifts WHERE name LIKE '%$q_safe%' ORDER BY id DESC LIMIT $limit_per_category";
$res_gifts = $conn->query($sql_gifts);
if ($res_gifts) {
    while ($row = $res_gifts->fetch_assoc()) {
        $row['type'] = 'gift';
        $row['link'] = 'edit-gift.php?id=' . $row['id'];
        $results[] = $row;
    }
}

// Format images
foreach ($results as &$r) {
    $img = $r['image'];
    $r['image'] = (strpos($img, 'uploads/') === 0) ? '/' . $img : '/uploads/' . $img;
    $r['name'] = htmlspecialchars($r['name']);
}

echo json_encode($results);
exit;
