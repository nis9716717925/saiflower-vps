<?php
// actions/delete_blog.php
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/csrf_helper.php';

csrf_verify_or_die();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Fetch the image filename before deleting the record
    $stmt = $conn->prepare("SELECT image FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $blog = $res->fetch_assoc();
    $stmt->close();

    if ($blog) {
        $imageFile = __DIR__ . '/../../uploads/' . $blog['image'];

        // 2. Delete the record from the database
        $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $delete = $stmt->execute();
        $stmt->close();

        if ($delete) {
            // 3. Delete the physical file from the server to save space
            if (!empty($blog['image']) && file_exists($imageFile)) {
                @unlink($imageFile);
            }
            $msg = "deleted";
        } else {
            $msg = "error";
        }
    }
} else {
    $msg = "invalid";
}

// Redirect back to the blog list with feedback
header("Location: ../blog.php?msg=$msg");
exit;