<?php
// actions/save_seo.php



require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../includes/csrf_helper.php';

csrf_verify_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Prepare & Sanitize
    $id    = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $page  = trim($_POST['page_identifier'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $keys  = trim($_POST['keywords'] ?? '');

    // 2. SEO Best Practice Validation
    // Titles over 70 or descriptions over 160 chars are usually truncated by Google
    if (empty($page) || empty($title)) {
        header("Location: ../seo.php?msg=invalid");
        exit;
    }

    if ($id > 0) {
        // UPDATE EXISTING PAGE SEO
        // We don't allow changing the page_identifier here to maintain data integrity
        $stmt = $conn->prepare("UPDATE seo_meta SET title=?, description=?, keywords=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $desc, $keys, $id);
    } else {
        // INSERT NEW PAGE SEO
        // First check if this page identifier already exists to prevent duplicates
        $check = $conn->prepare("SELECT id FROM seo_meta WHERE page_identifier = ?");
        $check->bind_param("s", $page);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            header("Location: ../seo.php?msg=exists");
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO seo_meta (page_identifier, title, description, keywords) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $page, $title, $desc, $keys);
    }

    // 3. Execute and Redirect
    if ($stmt->execute()) {
        header("Location: ../seo.php?msg=success");
    } else {
        // Log error if needed: $conn->error
        header("Location: ../seo.php?msg=db_error");
    }
    exit;
}