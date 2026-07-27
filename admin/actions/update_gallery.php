<?php
// actions/update_gallery.php




require_once __DIR__ . '/../auth_check.php'; // Essential security
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/image_handler.php'; // WebP Handler
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detect if POST data was dropped due to post_max_size limit
    if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        die('Error: The uploaded image is too large. It exceeds the server\'s maximum upload limit. Please reduce the image size and try again. <a href="javascript:history.back()">Go back</a>');
    }

    csrf_verify_or_die();
}

// 1. GET ID & BASIC INPUTS
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id === 0) {
    die("Error: Missing Gallery ID. <a href='javascript:history.back()'>Go back</a>");
}

// Sanitize Inputs
$title = $_POST['title'] ?? '';
$tag   = $_POST['tag'] ?? '';

// SEO Inputs
$meta_title       = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_keywords    = $_POST['meta_keywords'] ?? '';

if ($title === '' || $tag === '') {
    die('Error: Title and Tag are required. <a href="javascript:history.back()">Go back</a>');
}

// 2. IMAGE HANDLING (With WebP Conversion & Storage Cleanup)
$imageUpdateSQL = ""; 
$targetDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    
    // Convert new upload to optimized WebP (85 quality for gallery)
    $upload = uploadAndConvertToWebP('image', $targetDir, 85);

    if ($upload['success']) {
        $newFilename = $upload['filename'];
        
        // PURGE LOGIC: Fetch old image name to delete it from the server
        $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $oldData = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($oldData && !empty($oldData['image'])) {
            $oldFilePath = $targetDir . $oldData['image'];
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath); // Silently delete old arrangement photo
            }
        }

        $imageUpdateSQL = ", image = ?";
        $imageVal = $newFilename;
    } else {
        die("Upload Error: " . $upload['error'] . " <a href='javascript:history.back()'>Go back</a>");
    }
}

// 3. UPDATE DATABASE
$sql = "UPDATE gallery 
        SET title = ?, 
            tag = ?,
            meta_title = ?,
            meta_description = ?,
            meta_keywords = ?
            $imageUpdateSQL 
        WHERE id = ?";

$stmt = $conn->prepare($sql);

$types = "sssss";
$params = [$title, $tag, $meta_title, $meta_description, $meta_keywords];

if ($imageUpdateSQL !== "") {
    $types .= "s";
    $params[] = $imageVal;
}
$types .= "i";
$params[] = $id;

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: ../gallery.php?msg=updated");
    exit;
} else {
    $stmt->close();
    // DB Fallback: if SQL fails, delete the new image we just uploaded
    if (!empty($imageUpdateSQL)) @unlink($targetDir . $newFilename);
    die("Database Error: " . $conn->error);
}
?>