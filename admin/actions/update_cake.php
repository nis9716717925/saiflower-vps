<?php



require_once '../../config.php';
require_once '../auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/image_handler.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../cakes.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';
csrf_verify_or_die();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = "Invalid cake ID.";
    header("Location: ../cakes.php");
    exit();
}

/* ================= INPUT ================= */
$name = $_POST['name'] ?? '';
$price = floatval($_POST['price'] ?? 0);
$original_price = floatval($_POST['original_price'] ?? 0);
$description = $_POST['description'] ?? '';
$image_alt = trim($_POST['image_alt'] ?? '');

$in_stock = intval($_POST['in_stock'] ?? 1);
$status = 1; // Assuming always active on update unless specific toggle logic added

$meta_title = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_keywords = $_POST['meta_keywords'] ?? '';

$rating = floatval($_POST['rating'] ?? 5.0);
$delivery_sameday = intval($_POST['delivery_sameday'] ?? 0);
$delivery_nextday = intval($_POST['delivery_nextday'] ?? 0);
$tag = isset($_POST['tags']) && is_array($_POST['tags']) ? ',' . implode(',', array_map('trim', $_POST['tags'])) . ',' : '';

if (empty($name) || $price <= 0) {
    $_SESSION['error'] = "Enter valid name & price.";
    header("Location: ../edit-cake.php?id=$id");
    exit();
}

/* ================= SLUG GENERATOR ================= */
function createSlug($text){
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/','',$text);
    $text = preg_replace('/\s+/','-',$text);
    $text = preg_replace('/-+/','-',$text);
    return $text;
}

$baseSlug = createSlug($name);
$slug = $baseSlug;
$count = 1;

$stmt = $conn->prepare("SELECT id FROM cakes WHERE slug = ? AND id != ? LIMIT 1");
while(true){
    $stmt->bind_param("si", $slug, $id);
    $stmt->execute();
    $check = $stmt->get_result();
    if($check->num_rows == 0) break;
    $slug = $baseSlug.'-'.$count;
    $count++;
}
$stmt->close();

/* ================= UPLOAD DIR ================= */
$uploadDir = '../../uploads/cakes/';
if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

/* ================= FILE UPLOAD FUNC ================= */
function handleFileUpload($fileInputName,$targetDir,$allowedTypes){
    if(!isset($_FILES[$fileInputName])||$_FILES[$fileInputName]['error']!=0){
        return null;
    }
    $file=$_FILES[$fileInputName];
    $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,$allowedTypes)) return null;

    $newFilename=uniqid().'_'.time().'.'.$ext;
    if(move_uploaded_file($file['tmp_name'],$targetDir.$newFilename)){
        return 'uploads/cakes/'.$newFilename;
    }
    return null;
}

try{
    $conn->begin_transaction();
    try { $conn->query("ALTER TABLE cakes ADD COLUMN image_alt VARCHAR(255) NULL"); } catch (Exception $e) {}

    /* ================= UPDATE CORE ================= */
    $fields=[];
    $types="";
    $params=[];

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

    $fields[]="name=?"; $types.="s"; $params[]=$name;
    $fields[]="slug=?"; $types.="s"; $params[]=$slug;
    $fields[]="description=?"; $types.="s"; $params[]=$description;
    $fields[]="image_alt=?"; $types.="s"; $params[]=$image_alt;
    $fields[]="faqs=?"; $types.="s"; $params[]=$faqs_json;
    $fields[]="price=?"; $types.="d"; $params[]=$price;
    $fields[]="original_price=?"; $types.="d"; $params[]=$original_price;
    $fields[]="in_stock=?"; $types.="i"; $params[]=$in_stock;
    $fields[]="meta_title=?"; $types.="s"; $params[]=$meta_title;
    $fields[]="meta_description=?"; $types.="s"; $params[]=$meta_description;
    $fields[]="meta_keywords=?"; $types.="s"; $params[]=$meta_keywords;
    
    $fields[]="rating=?"; $types.="d"; $params[]=$rating;
    $fields[]="delivery_sameday=?"; $types.="i"; $params[]=$delivery_sameday;
    $fields[]="delivery_nextday=?"; $types.="i"; $params[]=$delivery_nextday;
    $fields[]="tag=?"; $types.="s"; $params[]=$tag;

    /* MAIN IMAGE */
    $uploadResult = uploadAndConvertToWebP('image', $uploadDir, 85);
    if($uploadResult['success']){
        $fields[]="image=?"; $types.="s"; $params[]='uploads/cakes/'.$uploadResult['filename'];
    }

    /* 3D MODEL */
    $newModel = handleFileUpload('model_3d',$uploadDir,['glb','gltf']);
    if($newModel){
        $fields[]="model_3d=?"; $types.="s"; $params[]=$newModel;
    }

    /* ================= GALLERY ================= */
    if(isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])){
        $galleryPaths = [];
        $stmt = $conn->prepare("SELECT images_gallery FROM cakes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $existingG = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if($existingG && !empty($existingG['images_gallery'])){
            $galleryPaths = json_decode($existingG['images_gallery'], true) ?: [];
        }

        $files = $_FILES['gallery'];
        $maxAllowed = 2 - count($galleryPaths);
        if($maxAllowed > 0) {
            for($i=0; $i<min(count($files['name']), $maxAllowed); $i++){
                if($files['error'][$i] == 0){
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    if(in_array($ext, ['jpg','jpeg','png','webp'])){
                        $newGalName = "gal_" . uniqid() . ".webp";
                        $galResult = uploadAndConvertToWebPFromTemp($files['tmp_name'][$i], $newGalName, $uploadDir, 85);
                        if($galResult['success']){
                            $galleryPaths[] = 'uploads/cakes/' . $galResult['filename'];
                        }
                    }
                }
            }
        }
        $imagesGallery = json_encode($galleryPaths);
        $fields[]="images_gallery=?"; $types.="s"; $params[]=$imagesGallery;
    }

    $params[]=$id; 
    $types.="i";

    $sql="UPDATE cakes SET ".implode(",",$fields)." WHERE id=?";
    $stmt=$conn->prepare($sql);
    $stmt->bind_param($types,...$params);
    $stmt->execute();
    $stmt->close();

    /* ================= VARIANTS ================= */
    // Delete old variants
    $stmt = $conn->prepare("DELETE FROM cake_variants WHERE cake_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Insert new variants
    if(isset($_POST['variant_name']) && is_array($_POST['variant_name'])){
        $vstmt = $conn->prepare("INSERT INTO cake_variants (cake_id, name, original_price, price) VALUES (?, ?, ?, ?)");
        $vnames = $_POST['variant_name'];
        $vorig_prices = $_POST['variant_original_price'] ?? [];
        $vprices = $_POST['variant_price'];
        
        for($i = 0; $i < count($vnames); $i++){
            $vname = trim($vnames[$i]);
            $vorig_price = isset($vorig_prices[$i]) && trim($vorig_prices[$i]) !== '' ? floatval($vorig_prices[$i]) : null;
            $vprice = floatval($vprices[$i]);
            
            if($vname !== '' && $vprice > 0){
                $vstmt->bind_param("isdd", $id, $vname, $vorig_price, $vprice);
                $vstmt->execute();
            }
        }
        $vstmt->close();
    }

    $conn->commit();
    $_SESSION['success']="Cake updated successfully.";
    header("Location: ../cakes.php");
    exit();

}catch(Exception $e){
    $conn->rollback();
    $_SESSION['error']="Error: ".$e->getMessage();
    header("Location: ../edit-cake.php?id=$id");
    exit();
}
?>
