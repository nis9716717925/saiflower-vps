<?php
// actions/delete_event.php
require_once __DIR__ . '/../auth_check.php'; // Essential security
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Find the image name before we lose the database record
    $stmt = $conn->prepare("SELECT cover_image FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $event = $res->fetch_assoc();
    $stmt->close();

    if ($event) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $event['cover_image'];

        // 2. Perform the database deletion
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $delete = $stmt->execute();
        $stmt->close();

        if ($delete) {
            // 3. Physical cleanup: Remove the image from the /uploads folder
            if (!empty($event['cover_image']) && file_exists($filePath)) {
                @unlink($filePath);
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

// Redirect back to the manager with a status message
header("Location: ../events.php?msg=$msg");
exit;