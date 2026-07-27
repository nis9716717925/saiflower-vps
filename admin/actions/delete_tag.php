<?php
// actions/delete_tag.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../auth_check.php'; // Essential security
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

// 1. Validate ID strictly as an integer
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // 2. Perform the deletion
    $stmt = $conn->prepare("DELETE FROM tags WHERE id = ?");
    $stmt->bind_param("i", $id);
    $delete = $stmt->execute();
    $stmt->close();

    if ($delete) {
        $status = "deleted";
    } else {
        $status = "error";
    }
} else {
    $status = "invalid";
}

// 3. Redirect back with feedback
header("Location: ../tags.php?msg=$status");
exit;