<?php



require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/image_handler.php';
require_once __DIR__ . '/../../includes/csrf_helper.php';

try {
    csrf_verify_or_die();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../blog.php");
    exit;
}

// SANITIZE (Only for slug generation if needed, but we'll use prepared statements for queries)
$title   = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$status  = isset($_POST['status']) ? 1 : 0;

$meta_title       = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_keywords    = $_POST['meta_keywords'] ?? '';

$slug = $_POST['slug'] ?? '';

if ($title === '' || $content === '') {
    die('Title and Content required');
}

// AUTO GENERATE SLUG IF EMPTY
if($slug == ''){
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug,'-');
}

// ENSURE UNIQUE SLUG
$baseSlug = $slug;
$count = 1;

$stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = ? LIMIT 1");
while(true){
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $check = $stmt->get_result();
    if($check->num_rows == 0) break;
    $slug = $baseSlug.'-'.$count;
    $count++;
}
$stmt->close();

// IMAGE
$targetDir = __DIR__ . '/../../uploads/';
$upload = uploadAndConvertToWebP('image', $targetDir, 85);

if (!$upload['success']) {
    throw new Exception('Image Upload Error: '.$upload['error']);
}

$filename = $upload['filename'];

// GALLERY
$galleryPaths = [];
if(isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])){
    $files = $_FILES['gallery'];
    for($i=0; $i<count($files['name']); $i++) {
        if($files['error'][$i] == 0) {
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $tmp = $files['tmp_name'][$i];
                $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($files['name'][$i], PATHINFO_FILENAME));
                $galDir = __DIR__ . '/../../uploads/blogs/';
                if (!is_dir($galDir)) {
                    mkdir($galDir, 0755, true);
                }
                $destName = uniqid('img_') . '_' . $cleanName . '.webp';
                $galResult = uploadAndConvertToWebPFromTemp($tmp, $destName, $galDir, 85);
                if($galResult && isset($galResult['success']) && $galResult['success']) {
                    $galleryPaths[] = 'uploads/blogs/' . $galResult['filename'];
                }
            }
        }
    }
}
$imagesGallery = !empty($galleryPaths) ? json_encode($galleryPaths) : null;

// SAFETY CHECK for the column
$colCheck = $conn->query("SHOW COLUMNS FROM blogs LIKE 'images_gallery'");
if($colCheck && $colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE blogs ADD COLUMN images_gallery LONGTEXT DEFAULT NULL");
}

// INSERT
$stmt = $conn->prepare("INSERT INTO blogs 
(title, slug, content, image, images_gallery, status, created_at, meta_title, meta_description, meta_keywords)
VALUES 
(?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");

$stmt->bind_param("sssssisss", $title, $slug, $content, $filename, $imagesGallery, $status, $meta_title, $meta_description, $meta_keywords);

    if($stmt->execute()){
        $stmt->close();
        header("Location: ../blog.php?msg=added");
        exit;
    }else{
        $stmt->close();
        @unlink($targetDir.$filename);
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
