<?php
require_once '../../config.php';
include '../auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    // 1. Clean Input
    $name = trim($_POST['name']);
    $name_lower = strtolower($name);

    // 2. Check for Duplicates (Prevent "Wedding" and "wedding" appearing twice)
    $stmt = $conn->prepare("SELECT id FROM tags WHERE LOWER(name) = ?");
    $stmt->bind_param("s", $name_lower);
    $stmt->execute();
    $check = $stmt->get_result();
    
    if ($check->num_rows == 0) {
        $stmt->close();
        // 3. Insert if unique
        $insStmt = $conn->prepare("INSERT INTO tags (name, status) VALUES (?, 1)");
        $insStmt->bind_param("s", $name);
        $insStmt->execute();
        $insStmt->close();
        $status = "success";
    } else {
        $stmt->close();
        $status = "exists";
    }
} else {
    $status = "empty";
}

// 4. Redirect back to the manager
header("Location: ../tags.php?msg=$status");
exit;