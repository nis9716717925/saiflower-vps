<?php
// actions/toggle_wishlist.php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add to wishlist', 'redirect' => 'login.php']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['customer_id'];
$product_id = intval($input['product_id'] ?? 0);
$type = $conn->real_escape_string($input['type'] ?? 'flower');

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if exists
$checkSql = "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id AND type = '$type'";
$checkRes = $conn->query($checkSql);

if ($checkRes && $checkRes->num_rows > 0) {
    // Remove
    $delSql = "DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $product_id AND type = '$type'";
    if ($conn->query($delSql)) {
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    // Add
    $addSql = "INSERT INTO wishlist (user_id, product_id, type) VALUES ($user_id, $product_id, '$type')";
    if ($conn->query($addSql)) {
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
