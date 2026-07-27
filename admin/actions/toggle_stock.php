<?php
// admin/actions/toggle_stock.php
ob_start(); 
session_start();

// 1. Load Config
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

// 2. Logic Execution
if (isset($_GET['id']) && isset($_GET['stock']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $stock = intval($_GET['stock']); 
    $type = $_GET['type'];
    
    // Whitelist allowed types to prevent SQL injection on table name
    $allowed_types = ['flowers', 'cakes', 'gifts'];
    if(!in_array($type, $allowed_types)) {
        die("Invalid product type.");
    }

    if (!$conn) { die("Database connection failed."); }

    $stmt = $conn->prepare("UPDATE {$type} SET in_stock = ? WHERE id = ?");
    
    if ($stmt) {
        $stmt->bind_param("ii", $stock, $id);
        
        if ($stmt->execute()) {
            $msg_key = ($stock === 1) ? 'restocked' : 'soldout';
            
            header("Location: /admin/{$type}.php?msg=" . $msg_key);
            exit;
        }
        $stmt->close();
    }
}

// Fallback redirect if something goes wrong
header("Location: /admin/index.php");
exit;