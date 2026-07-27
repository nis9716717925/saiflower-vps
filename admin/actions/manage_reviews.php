<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../includes/csrf_helper.php';

csrf_verify_or_die();

$action = $_REQUEST['action'] ?? '';

if ($action === 'add') {
    $name = $_POST['name'] ?? '';
    $text = $_POST['review_text'] ?? '';
    $plat = $_POST['platform'] ?? '';
    $rating = (int)($_POST['rating'] ?? 5);

    $stmt = $conn->prepare("INSERT INTO reviews (name, review_text, platform, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $text, $plat, $rating);
    $stmt->execute();
    $stmt->close();
    header("Location: ../reviews.php?msg=added");
}

if ($action === 'delete') {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: ../reviews.php?msg=deleted");
}

if ($action === 'toggle') {
    $id = (int)$_GET['id'];
    $status = (int)$_GET['status'];
    $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: ../reviews.php?msg=updated");
}
