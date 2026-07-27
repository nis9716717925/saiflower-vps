<?php
// admin/actions/delete_flower.php

require_once '../../config.php';
require_once '../auth_check.php'; // Correct path
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

if (!isset($_GET['id'])) {
    header("Location: ../flowers.php");
    exit();
}

$id = intval($_GET['id']);

try {
    $conn->begin_transaction();

    // 1. Get Main Data (Image & Model)
    $stmt = $conn->prepare("SELECT image, model_3d FROM flowers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $flower = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($flower) {
        if (!empty($flower['image']) && file_exists('../../' . $flower['image'])) {
            unlink('../../' . $flower['image']);
        }
        if (!empty($flower['model_3d']) && file_exists('../../' . $flower['model_3d'])) {
            unlink('../../' . $flower['model_3d']);
        }
    }

    // 2. Get and Delete Gallery Images
    $stmt = $conn->prepare("SELECT image_path FROM flower_images WHERE flower_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while($row = $res->fetch_assoc()) {
        if (!empty($row['image_path']) && file_exists('../../' . $row['image_path'])) {
            unlink('../../' . $row['image_path']);
        }
    }
    $stmt->close();

    // 3. Delete Records
    $stmt = $conn->prepare("DELETE FROM flower_images WHERE flower_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM flowers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $_SESSION['success'] = "Flower deleted successfully.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Delete failed: " . $e->getMessage();
}

header("Location: ../flowers.php");
exit();
?>
