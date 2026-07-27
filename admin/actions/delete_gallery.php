<?php
// actions/delete_gallery.php
require_once __DIR__ . '/../auth_check.php'; // Essential security
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Fetch image name before deleting record
    $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $item = $res->fetch_assoc();
    $stmt->close();

    if ($item) {
        $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $item['image'];

        // 2. Delete database entry
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        $delete = $stmt->execute();
        $stmt->close();

        if ($delete) {
            // 3. Purge physical file if DB delete was successful
            if (!empty($item['image']) && file_exists($targetPath)) {
                @unlink($targetPath);
            }
            $msg = "deleted";
        } else {
            $msg = "error";
        }
    } else {
        $msg = "not_found";
    }
} else {
    $msg = "invalid";
}

// Redirect back to gallery with status
header("Location: ../gallery.php?msg=$msg");
exit;