<?php
// admin/actions/delete_blog_image.php

require_once '../../config.php';
require_once '../auth_check.php';
require_once __DIR__ . '/../../includes/csrf_helper.php';

csrf_verify_or_die();

if (!isset($_GET['id']) || !isset($_GET['index'])) {
    header("Location: ../blog.php");
    exit();
}

$id = intval($_GET['id']);
$index = intval($_GET['index']);

$stmt = $conn->prepare("SELECT images_gallery FROM blogs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (!empty($row['images_gallery'])) {
        $gallery = json_decode($row['images_gallery'], true);
        if (is_array($gallery) && isset($gallery[$index])) {
            $imagePath = $gallery[$index];
            $filePath = '../../' . $imagePath;
            
            // Delete file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Remove from array and reindex
            unset($gallery[$index]);
            $gallery = array_values($gallery);
            
            $newGalleryJson = json_encode($gallery);
            
            // Update db
            $updStmt = $conn->prepare("UPDATE blogs SET images_gallery = ? WHERE id = ?");
            $updStmt->bind_param("si", $newGalleryJson, $id);
            $updStmt->execute();
            
            $_SESSION['success'] = "Image deleted from gallery.";
        } else {
            $_SESSION['error'] = "Image not found in gallery array.";
        }
    } else {
         $_SESSION['error'] = "Gallery is empty.";
    }
} else {
    $_SESSION['error'] = "Blog not found.";
}

header("Location: ../edit-blog.php?id=$id");
exit();
?>
