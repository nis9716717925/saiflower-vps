<?php
// actions/update_settings.php



require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../auth_check.php'; 
require_once __DIR__ . '/../../includes/csrf_helper.php';

// WebP Handler for performance
if (file_exists(__DIR__ . '/../../includes/image_helper.php')) {
    include_once __DIR__ . '/../../includes/image_helper.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header("Location: ../settings.php");
    exit; 
}

csrf_verify_or_die();

// 1. SANITIZE CORE INPUTS
$hero_title    = trim($_POST['hero_title'] ?? '');
$hero_subtitle = trim($_POST['hero_subtitle'] ?? '');
$logo_width    = intval($_POST['logo_width'] ?? 150);

$phone         = trim($_POST['phone'] ?? '');
$whatsapp      = trim($_POST['whatsapp'] ?? '');
$email         = trim($_POST['email'] ?? '');
$address       = trim($_POST['address'] ?? '');
$footer_about  = trim($_POST['footer_about'] ?? '');
$newsletter_text = trim($_POST['newsletter_text'] ?? '');

$site_title    = trim($_POST['site_title'] ?? 'Sai Flower');
$maint_mode    = isset($_POST['maintenance_mode']) ? 1 : 0;

// 2. FILE UPLOAD LOGIC
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
$fileSQL = "";
$updateParams = [];
$paramTypes = "";

// ... (File upload logic kept same, omit for brevity in replacement if possible, but replace_file_content needs contiguous block. I'll rely on context or just replace the query part separately if easier. 
// Actually, I can just replace the variable init separate from query.

// Block 1: Init variables
// Block 2: Query update

// Let's do it in one go if possible, but the file upload logic is in between.
// I'll split into two replaces.

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
$fileSQL = "";
$updateParams = [];
$paramTypes = "";

// Fetch current hero image for cleanup if a new one is uploaded
$stmt = $conn->prepare("SELECT hero_image FROM settings WHERE id = 1");
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
    // Check if conversion helper exists, else use standard upload
    if (function_exists('uploadAndConvertToWebP')) {
        $upload = uploadAndConvertToWebP('hero_image', $uploadDir, 80);
        if ($upload['success']) {
            $heroName = $upload['filename'];
            if (!empty($current['hero_image']) && file_exists($uploadDir . $current['hero_image'])) {
                @unlink($uploadDir . $current['hero_image']);
            }
            $fileSQL .= ", hero_image=?";
            $updateParams[] = $heroName;
            $paramTypes .= "s";
        }
    } else {
        // Fallback standard upload
        $ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $heroName = 'hero_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $uploadDir . $heroName)) {
                if (!empty($current['hero_image']) && file_exists($uploadDir . $current['hero_image'])) {
                    @unlink($uploadDir . $current['hero_image']);
                }
                $fileSQL .= ", hero_image=?";
                $updateParams[] = $heroName;
                $paramTypes .= "s";
            }
        }
    }
} elseif (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Handle Upload Errors (e.g., file too large)
    $errCode = $_FILES['hero_image']['error'];
    $errMsg = "File Upload Error: ";
    switch ($errCode) {
        case UPLOAD_ERR_INI_SIZE: $errMsg .= "File exceeds upload_max_filesize directive in php.ini"; break;
        case UPLOAD_ERR_FORM_SIZE: $errMsg .= "File exceeds MAX_FILE_SIZE directive in HTML form"; break;
        case UPLOAD_ERR_PARTIAL: $errMsg .= "The uploaded file was only partially uploaded"; break;
        case UPLOAD_ERR_NO_TMP_DIR: $errMsg .= "Missing a temporary folder"; break;
        case UPLOAD_ERR_CANT_WRITE: $errMsg .= "Failed to write file to disk"; break;
        case UPLOAD_ERR_EXTENSION: $errMsg .= "File upload stopped by extension"; break;
        default: $errMsg .= "Unknown error code: $errCode";
    }
    die($errMsg);
}

// 3. DATABASE SYNCHRONIZATION
// We only update the fields relevant to the new streamlined UI
$sql = "UPDATE settings SET 
        hero_title=?, 
        hero_subtitle=?, 
        logo_width=?, 
        phone=?, 
        whatsapp=?, 
        email=?, 
        address=?, 
        footer_about=?,
        newsletter_text=?,
        site_title=?, 
        maintenance_mode=?
        $fileSQL
        WHERE id=1";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Types: s=string, i=integer (added 2 strings)
    $types = "ssissssssi" . $paramTypes;
    
    // Base params
    $params = [
        $hero_title, 
        $hero_subtitle, 
        $logo_width, 
        $phone, 
        $whatsapp, 
        $email, 
        $address, 
        $footer_about,
        $newsletter_text,
        $site_title, 
        $maint_mode
    ];
    
    // Merge with file params if any
    if (!empty($updateParams)) {
        $params = array_merge($params, $updateParams);
    }
    
    $stmt->bind_param($types, ...$params);
    
    try {
        if ($stmt->execute()) {
            header("Location: ../settings.php?updated=1");
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        die("Synchronization Error: " . $e->getMessage());
    }
    $stmt->close();
} else {
    die("Intelligence Engine Prepare Failed: " . $conn->error);
}
exit;