<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once __DIR__ . '/../auth_check.php';

if (isset($_GET['id']) && isset($_GET['status']) && isset($_GET['csrf_token']) && $_GET['csrf_token'] === $_SESSION['csrf_token']) {
    $id = intval($_GET['id']);
    $status = intval($_GET['status']);
    
    if ($conn->query("UPDATE dynamic_pages SET status = $status WHERE id = $id")) {
        header("Location: ../pages.php?msg=updated");
    } else {
        header("Location: ../pages.php?msg=error");
    }
    exit;
}
header("Location: ../pages.php");
exit;
