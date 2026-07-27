<?php
include '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['cart'])) {
    
    // 1. SANITIZE INPUT
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $total = floatval($_POST['total_amount']);
    
    // 2. INSERT ORDER
    $sql = "INSERT INTO orders (customer_name, customer_email, customer_phone, address, total_amount, status) 
            VALUES ('$name', '$email', '$phone', '$address', $total, 'pending')";
    
    if (mysqli_query($conn, $sql)) {
        $order_id = mysqli_insert_id($conn);
        
        // 3. INSERT ORDER ITEMS
        foreach ($_SESSION['cart'] as $item) {
            $pid = intval($item['id']);
            $qty = intval($item['qty']);
            $price = floatval($item['price']);
            
            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                         VALUES ($order_id, $pid, $qty, $price)";
            mysqli_query($conn, $item_sql);
        }
        
        // 4. CLEAR CART
        unset($_SESSION['cart']);
        
        // 5. REDIRECT
        // Ideally to a success page. For now, back to home with query param.
        header("Location: /index.php?order_success=1&oid=$order_id");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: /cart.php");
    exit;
}
?>
