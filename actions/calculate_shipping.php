<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/shipping_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$addressLine = trim($input['address_line'] ?? '');
$city = trim($input['city'] ?? '');
$zip = trim($input['zip'] ?? '');

$parts = array_filter([$addressLine, $city, $zip !== '' ? $zip : null, 'India']);
$destination = implode(', ', $parts);

$result = calculate_shipping_from_address($destination);

if ($result['status'] === 'ok') {
    echo json_encode([
        'status'        => 'ok',
        'distance_km'   => $result['distance_km'],
        'distance_text' => $result['distance_text'],
        'shipping_fee'  => $result['shipping_fee'],
        'rate_per_km'   => SHIPPING_RATE_PER_KM,
        'store_address' => STORE_ADDRESS,
    ]);
} else {
    echo json_encode($result);
}
