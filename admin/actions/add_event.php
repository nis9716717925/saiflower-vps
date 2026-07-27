<?php
// actions/add_event.php



require_once __DIR__ . '/../auth_check.php'; // Essential security
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/image_handler.php'; // WebP Converter
require_once __DIR__ . '/../../includes/csrf_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
}

// 1. INPUTS
$title       = $_POST['title'] ?? '';
$tag         = $_POST['tag'] ?? '';
$description = $_POST['description'] ?? '';
$status      = isset($_POST['status']) ? (int)$_POST['status'] : 1; // Default to active

// SEO Inputs
$meta_title       = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_keywords    = $_POST['meta_keywords'] ?? '';

// Basic Validation
if ($title === '' || $description === '') {
    die('Title and Description are required. <a href="javascript:history.back()">Go back</a>');
}

// 2. IMAGE UPLOAD & WEBP CONVERSION
$targetDir = __DIR__ . '/../../uploads/';
// The handler already checks for directory existence, but we pass the path clearly
$res = uploadAndConvertToWebP('cover_image', $targetDir, 80); 

if (!$res['success']) {
    die('Image Upload Error: ' . $res['error'] . ' <a href="javascript:history.back()">Go back</a>');
}

$filename = $res['filename'];

// 3. DATABASE INSERTION
/* ================= FAQS ================= */
$faqs_json = NULL;
if (isset($_POST['faq_question']) && is_array($_POST['faq_question']) && isset($_POST['faq_answer']) && is_array($_POST['faq_answer'])) {
    $faqs_arr = [];
    $questions = $_POST['faq_question'];
    $answers = $_POST['faq_answer'];
    for ($i = 0; $i < count($questions); $i++) {
        $q = trim($questions[$i]);
        $a = trim($answers[$i]);
        if (!empty($q) && !empty($a)) {
            $faqs_arr[] = [
                'question' => $q,
                'answer' => $a
            ];
        }
    }
    if (count($faqs_arr) > 0) {
        $faqs_json = json_encode($faqs_arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

$stmt = $conn->prepare("INSERT INTO events 
        (title, tag, description, faqs, cover_image, created_at, meta_title, meta_description, meta_keywords, status)
        VALUES 
        (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)");

$stmt->bind_param("ssssssssi", $title, $tag, $description, $faqs_json, $filename, $meta_title, $meta_description, $meta_keywords, $status);

if ($stmt->execute()) {
    $stmt->close();
    // Success redirect
    header("Location: ../events.php?msg=added");
    exit;
} else {
    $stmt->close();
    // Cleanup the file if DB fails to prevent "orphaned" images
    @unlink($targetDir . $filename);
    die('Database error: ' . $conn->error);
}
?>