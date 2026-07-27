<?php
// actions/add_gallery.php



require_once __DIR__ . '/../auth_check.php'; // Essential security check
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/image_handler.php'; // Auto-WebP Converter
require_once __DIR__ . '/../../includes/csrf_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detect if POST data was dropped due to post_max_size limit
    if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        die('Error: The uploaded image is too large. It exceeds the server\'s maximum upload limit. Please reduce the image size and try again. <a href="javascript:history.back()">Go back</a>');
    }
    
    csrf_verify_or_die();
}

// 1. INPUTS
$title = $_POST['title'] ?? '';
$tag   = $_POST['tag'] ?? '';

// SEO Inputs
$meta_title       = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_keywords    = $_POST['meta_keywords'] ?? '';

// Validation
if ($title === '' || $tag === '') {
    die('Title and Tag are required. <a href="javascript:history.back()">Go back</a>');
}

// 2. IMAGE UPLOAD & OPTIMIZATION
$targetDir = __DIR__ . '/../../uploads/';
// Using 80 quality for a perfect balance of crisp visuals and small file size
$res = uploadAndConvertToWebP('image', $targetDir, 80); 

if (!$res['success']) {
    die('Image Upload Error: ' . $res['error'] . ' <a href="javascript:history.back()">Go back</a>');
}

$filename = $res['filename'];

// 3. DATABASE INSERTION
// Added created_at to ensure proper ordering in your gallery
$stmt = $conn->prepare("INSERT INTO gallery 
        (title, tag, image, status, created_at, meta_title, meta_description, meta_keywords)
        VALUES 
        (?, ?, ?, 1, NOW(), ?, ?, ?)");

$stmt->bind_param("ssssss", $title, $tag, $filename, $meta_title, $meta_description, $meta_keywords);

if ($stmt->execute()) {
    $stmt->close();
    // Redirect with success message
    header("Location: ../gallery.php?msg=added");
    exit;
} else {
    $stmt->close();
    // CLEANUP: If DB fails, delete the file to prevent server clutter
    @unlink($targetDir . $filename);
    die('Database error: ' . $conn->error);
}
?>