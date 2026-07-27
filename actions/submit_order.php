<?php
// actions/submit_order.php
// Handles order submission from checkout.php via AJAX

header('Content-Type: application/json');

// 1. CONFIG & DB CONNECTION
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    echo json_encode(['status' => 'error', 'message' => 'Config not found']);
    exit;
}

// LOAD CSRF HELPER
require_once __DIR__ . '/../includes/csrf_helper.php';
require_once __DIR__ . '/../includes/shipping_helper.php';

// 2. GET POST DATA
// Expecting JSON input from fetch()
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

// 2.1 CSRF CHECK
// The frontend must send the CSRF token in the JSON body or headers
$token = $input['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Security Token']);
    exit;
}

$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? ''); 
$email = trim($input['email'] ?? '');
$address = trim($input['address'] ?? '');
$date = trim($input['date'] ?? '');
$items = trim($input['items'] ?? ''); 
$total = floatval($input['total'] ?? 0);
$clientShippingFee = intval($input['shipping_fee'] ?? 0);

// Basic Validation
if(empty($name) || empty($phone) || empty($address)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$shippingResult = calculate_shipping_from_address($address);
if ($shippingResult['status'] !== 'ok') {
    echo json_encode(['status' => 'error', 'message' => $shippingResult['message'] ?? 'Invalid delivery address']);
    exit;
}

$shippingFee = (int) $shippingResult['shipping_fee'];
$distanceKm = (float) $shippingResult['distance_km'];

if (abs($shippingFee - $clientShippingFee) > 1) {
    echo json_encode(['status' => 'error', 'message' => 'Shipping amount changed. Please refresh and try again.']);
    exit;
}

// Handle Discount Info
$discountAmount = floatval($input['discount_amount'] ?? 0);
$couponCode = trim($input['coupon_code'] ?? '');

$items .= "\n--------------------------------\n";
$items .= "Shipping (" . number_format($distanceKm, 2) . " km @ ₹" . SHIPPING_RATE_PER_KM . "/km): ₹" . number_format($shippingFee, 2);

if ($discountAmount > 0) {
    $items .= "\nDiscount (" . htmlspecialchars($couponCode) . "): -₹" . number_format($discountAmount, 2);
}

// 2.2 USAGE LIMIT CHECK
if (!empty($couponCode)) {
    $limitQuery = $conn->prepare("SELECT usage_limit FROM promo_codes WHERE code=? AND status=1 LIMIT 1");
    if ($limitQuery) {
        $limitQuery->bind_param("s", $couponCode);
        $limitQuery->execute();
        $limitRes = $limitQuery->get_result();
        if ($limitRes && $limitRes->num_rows > 0) {
            $promoRow = $limitRes->fetch_assoc();
            $limit = $promoRow['usage_limit'];
            if (!empty($limit)) {
                $usageQuery = $conn->prepare("SELECT COUNT(*) as used_count FROM orders WHERE customer_phone=? AND coupon_code=?");
                if ($usageQuery) {
                    $usageQuery->bind_param("ss", $phone, $couponCode);
                    $usageQuery->execute();
                    $usageRes = $usageQuery->get_result();
                    if ($usageRes && $usageRes->num_rows > 0) {
                        $usedCount = $usageRes->fetch_assoc()['used_count'];
                        if ($usedCount >= $limit) {
                            echo json_encode(['status' => 'error', 'message' => "You've reached the usage limit for ($couponCode)."]);
                            exit;
                        }
                    }
                }
            }
        }
    }
}

// 3. INSERT INTO DATABASE
// Use Prepared Statement
$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_phone, customer_email, delivery_address, delivery_date, order_items, total_amount, status, coupon_code) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");

if ($stmt) {
    $stmt->bind_param("ssssssds", $name, $phone, $email, $address, $date, $items, $total, $couponCode);
    
    if ($stmt->execute()) {
        $orderId = $stmt->insert_id;
        
        // 4. SEND EMAIL NOTIFICATION (Optional)
        $to = "searchlifterhexa@gmail.com"; // Admin Email
        $subject = "💰 NEW ORDER #$orderId - ₹$total";
        
        // Construct message safely
        $msg = "New Order Received!\n\n";
        $msg .= "Name: $name\n";
        $msg .= "Phone: $phone\n";
        $msg .= "Email: $email\n";
        $msg .= "Address: $address\n";
        $msg .= "Date: $date\n\n";
        $msg .= "Items:\n$items\n\n";
        $msg .= "Total: ₹$total\n";
        
        $headers = "From: no-reply@saiflowers.com\r\n";
        
        // Suppress errors if mail not configured
        @mail($to, $subject, $msg, $headers);
        
        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully', 'order_id' => $orderId]);
    } else {
        // Log error internally
        error_log("Order Insert Error: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Order could not be saved. Please try again.']);
    }
    $stmt->close();
} else {
    error_log("Order Prepare Error: " . $conn->error);
    echo json_encode(['status' => 'error', 'message' => 'System error. Please try again.']);
}
?>
