<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once __DIR__ . '/../auth_check.php';

if (isset($_GET['id']) && isset($_GET['csrf_token']) && $_GET['csrf_token'] === $_SESSION['csrf_token']) {
    $id = intval($_GET['id']);
    
    // Delete image file first
    $res = $conn->query("SELECT hero_image FROM dynamic_pages WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['hero_image'])) {
            $filepath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $row['hero_image'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }
    
    if ($conn->query("DELETE FROM dynamic_pages WHERE id = $id")) {
        header("Location: ../pages.php?msg=deleted");
    } else {
        header("Location: ../pages.php?msg=error");
    }
    exit;
}
header("Location: ../pages.php");
exit;
