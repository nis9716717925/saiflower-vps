<?php
// admin/actions/delete_flower_image.php

require_once '../../config.php';
require_once '../auth_check.php';

if (!isset($_GET['id'])) {
    header("Location: ../flowers.php");
    exit();
}

$id = intval($_GET['id']);
$type = $_GET['type'] ?? 'json';

if ($type === 'json' && isset($_GET['index'])) {
    $index = intval($_GET['index']);
    $stmt = $conn->prepare("SELECT images_gallery FROM flowers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['images_gallery'])) {
            $gallery = json_decode($row['images_gallery'], true);
            if (is_array($gallery) && isset($gallery[$index])) {
                $imagePath = $gallery[$index];
                $filePath = '../../' . $imagePath;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                unset($gallery[$index]);
                $gallery = array_values($gallery);
                $newGalleryJson = json_encode($gallery);
                $updStmt = $conn->prepare("UPDATE flowers SET images_gallery = ? WHERE id = ?");
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
        $_SESSION['error'] = "Flower not found.";
    }
} elseif ($type === 'table' && isset($_GET['image_id'])) {
    $image_id = intval($_GET['image_id']);
    $stmt = $conn->prepare("SELECT image_path FROM flower_images WHERE id = ? AND flower_id = ?");
    $stmt->bind_param("ii", $image_id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['image_path'])) {
            $filePath = '../../' . $row['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $delStmt = $conn->prepare("DELETE FROM flower_images WHERE id = ?");
        $delStmt->bind_param("i", $image_id);
        $delStmt->execute();
        $_SESSION['success'] = "Legacy image deleted.";
    } else {
        $_SESSION['error'] = "Image not found.";
    }
} else {
    $_SESSION['error'] = "Invalid deletion parameters.";
}

header("Location: ../edit-flower.php?id=$id");
exit();
?>
