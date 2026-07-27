<?php

require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../includes/csrf_helper.php';

try {
    csrf_verify_or_die();
    require_once __DIR__ . '/../../config.php';
    

    require_once __DIR__ . '/../includes/image_handler.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../blog.php");
        exit;
    }

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id === 0) {
    die("Missing Blog ID");
}

$title   = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$status  = isset($_POST['status']) ? 1 : 0;

$meta_title       = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_keywords    = $_POST['meta_keywords'] ?? '';

$slug = $_POST['slug'] ?? '';

if ($title === '' || $content === '') {
    die('Title and content required');
}

// AUTO SLUG IF EMPTY
if($slug == ''){
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug,'-');
}

// UNIQUE SLUG CHECK
$baseSlug = $slug;
$count = 1;
$stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = ? AND id != ? LIMIT 1");
while(true){
    $stmt->bind_param("si", $slug, $id);
    $stmt->execute();
    $check = $stmt->get_result();
    if($check->num_rows == 0) break;
    $slug = $baseSlug.'-'.$count;
    $count++;
}
$stmt->close();

// IMAGE UPDATE
$imageUpdateSQL = ""; 

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    
    $targetDir = __DIR__ . '/../../uploads/';
    $upload = uploadAndConvertToWebP('image', $targetDir, 85);

    if ($upload['success']) {
        $newFilename = $upload['filename'];

        $stmt = $conn->prepare("SELECT image FROM blogs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $oldData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($oldData && !empty($oldData['image'])) {
            $oldFilePath = $targetDir . $oldData['image'];
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath);
            }
        }

        $imageUpdateSQL = ", image = ?";
        $imageVal = $newFilename;
    } else {
        die("Upload Error: ".$upload['error']);
    }
}

// GALLERY UPDATE
$galleryUpdateSQL = "";

// SAFETY CHECK for the column
$colCheck = $conn->query("SHOW COLUMNS FROM blogs LIKE 'images_gallery'");
if($colCheck && $colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE blogs ADD COLUMN images_gallery LONGTEXT DEFAULT NULL");
}

if(isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])){
    $galDir = __DIR__ . '/../../uploads/blogs/';
    if (!is_dir($galDir)) {
        mkdir($galDir, 0755, true);
    }
    $galleryPaths = [];
    $getGQuery = $conn->query("SELECT images_gallery FROM blogs WHERE id=$id");
    if($getGQuery){
        $existingG = $getGQuery->fetch_assoc();
        if($existingG && !empty($existingG['images_gallery'])){
            $galleryPaths = json_decode($existingG['images_gallery'], true) ?: [];
        }
    }
    
    $files = $_FILES['gallery'];
    for($i=0; $i<count($files['name']); $i++) {
        if($files['error'][$i] == 0) {
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $tmp = $files['tmp_name'][$i];
                $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($files['name'][$i], PATHINFO_FILENAME));
                $destName = uniqid('img_') . '_' . $cleanName . '.webp';
                $galResult = uploadAndConvertToWebPFromTemp($tmp, $destName, $galDir, 85);
                if($galResult && isset($galResult['success']) && $galResult['success']) {
                    $galleryPaths[] = 'uploads/blogs/' . $galResult['filename'];
                }
            }
        }
    }
    
    if(!empty($galleryPaths)){
        $imagesGallery = json_encode($galleryPaths);
        $galleryUpdateSQL = ", images_gallery = ?";
        $galleryVal = $imagesGallery;
    }
}

// UPDATE QUERY
$sql = "UPDATE blogs 
SET title = ?,
slug = ?,
content = ?,
status = ?,
meta_title = ?,
meta_description = ?,
meta_keywords = ?
$imageUpdateSQL
$galleryUpdateSQL
WHERE id = ?";

$stmt = $conn->prepare($sql);

$types = "sssisss";
$params = [$title, $slug, $content, $status, $meta_title, $meta_description, $meta_keywords];

if ($imageUpdateSQL !== "") {
    $types .= "s";
    $params[] = $imageVal;
}
if ($galleryUpdateSQL !== "") {
    $types .= "s";
    $params[] = $galleryVal;
}
$types .= "i";
$params[] = $id;

$stmt->bind_param($types, ...$params);

    if($stmt->execute()){
        $stmt->close();
        header("Location: ../blog.php?msg=updated");
        exit;
    }else{
        $stmt->close();
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo "<h1>Error</h1><pre>" . $e->getMessage() . "</pre>";
    echo "File: " . $e->getFile() . " on line " . $e->getLine();
} catch (Throwable $t) {
    echo "<h1>Fatal Error</h1><pre>" . $t->getMessage() . "</pre>";
    echo "File: " . $t->getFile() . " on line " . $t->getLine();
}
?>
