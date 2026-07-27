<?php
// actions/update_event.php




require_once __DIR__ . '/../auth_check.php'; // Essential security
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/image_handler.php'; // WebP Handler
require_once __DIR__ . '/../../includes/csrf_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
}

// 1. GET ID & BASIC INPUTS
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id === 0) {
    die("Error: Missing Event ID. <a href='javascript:history.back()'>Go back</a>");
}

// Sanitize Inputs
$title = $_POST['title'] ?? '';
$tag   = $_POST['tag'] ?? '';
$desc  = $_POST['description'] ?? '';

// SEO Inputs
$meta_title       = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_keywords    = $_POST['meta_keywords'] ?? '';

if ($title === '' || $desc === '') {
    die('Error: Title and Description are required. <a href="javascript:history.back()">Go back</a>');
}

// 2. IMAGE HANDLING (With WebP Conversion & Old File Cleanup)
$imageUpdateSQL = ""; 

if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
    
    $targetDir = __DIR__ . '/../../uploads/';
    
    // Convert new upload to optimized WebP
    $upload = uploadAndConvertToWebP('cover_image', $targetDir, 80);

    if ($upload['success']) {
        $newFilename = $upload['filename'];
        
        // CLEANUP: Fetch old image name to delete it from the server
        $stmt = $conn->prepare("SELECT cover_image FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $oldData = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($oldData && !empty($oldData['cover_image'])) {
            $oldFilePath = $targetDir . $oldData['cover_image'];
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath); // Delete the old file to save space
            }
        }

        $imageUpdateSQL = ", cover_image = ?";
        $imageVal = $newFilename;
    } else {
        die("Upload Error: " . $upload['error'] . " <a href='javascript:history.back()'>Go back</a>");
    }
}

// 3. UPDATE DATABASE
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

$sql = "UPDATE events 
        SET title = ?, 
            tag = ?, 
            description = ?,
            faqs = ?,
            meta_title = ?,
            meta_description = ?,
            meta_keywords = ?
            $imageUpdateSQL 
        WHERE id = ?";

$stmt = $conn->prepare($sql);

$types = "sssssss";
$params = [$title, $tag, $desc, $faqs_json, $meta_title, $meta_description, $meta_keywords];

if ($imageUpdateSQL !== "") {
    $types .= "s";
    $params[] = $imageVal;
}
$types .= "i";
$params[] = $id;

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: ../events.php?msg=updated");
    exit;
} else {
    $stmt->close();
    // If DB fails but we uploaded a new image, delete it to keep sync
    if ($imageUpdateSQL !== "") @unlink($targetDir . $newFilename);
    die("Database Error: " . $conn->error);
}
?>