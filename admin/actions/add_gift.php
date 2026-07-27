<?php
ini_set('display_errors',1);


require_once '../../config.php';
require_once '../auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/image_handler.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error']="Invalid request";
    header("Location: ../add-gift.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

$name = trim($_POST['name'] ?? '');
$slugInput = trim($_POST['slug'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$original_price = floatval($_POST['original_price'] ?? 0);
$description = trim($_POST['description'] ?? '');
$image_alt = trim($_POST['image_alt'] ?? '');
$in_stock = isset($_POST['in_stock']) ? intval($_POST['in_stock']) : 1;
$status   = isset($_POST['status']) ? intval($_POST['status']) : 1;
$meta_title = trim($_POST['meta_title'] ?? '');
$meta_description = trim($_POST['meta_description'] ?? '');
$meta_keywords = trim($_POST['meta_keywords'] ?? '');

$rating = floatval($_POST['rating'] ?? 5.0);
$delivery_sameday = intval($_POST['delivery_sameday'] ?? 0);
$delivery_nextday = intval($_POST['delivery_nextday'] ?? 0);
$tag = isset($_POST['tags']) && is_array($_POST['tags']) ? ',' . implode(',', array_map('trim', $_POST['tags'])) . ',' : '';

if (isset($_POST['tags'])) {
    require_once __DIR__ . '/save_tags_helper.php';
    auto_save_tags($conn, $_POST['tags']);
}

if ($name=='' || $price<=0){
    $_SESSION['error']="Enter valid name and price";
    header("Location: ../add-gift.php");
    exit();
}

function makeSlug($text){
    $text=strtolower(trim($text));
    $text=preg_replace('/[^a-z0-9\s-]/','',$text);
    $text=preg_replace('/\s+/','-',$text);
    $text=preg_replace('/-+/','-',$text);
    return $text;
}

if($slugInput!=''){
    $baseSlug = makeSlug($slugInput);
}else{
    $baseSlug = makeSlug($name);
}

$slug = $baseSlug;
$count=1;
$stmt = $conn->prepare("SELECT id FROM gifts WHERE slug = ? LIMIT 1");
while(true){
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $q = $stmt->get_result();
    if($q->num_rows == 0) break;
    $slug = $baseSlug.'-'.$count;
    $count++;
}
$stmt->close();

$uploadDir='../../uploads/gifts/';
if(!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

function uploadFile($input,$dir,$required=false,$allowed=[]){
    if(!isset($_FILES[$input])||$_FILES[$input]['error']!=0){
        if($required) throw new Exception("Upload failed: $input");
        return null;
    }
    $file=$_FILES[$input];
    $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,$allowed)) throw new Exception("Invalid file type");

    $new=uniqid().'_'.time().'.'.$ext;
    if(move_uploaded_file($file['tmp_name'],$dir.$new)){
        return 'uploads/gifts/'.$new;
    }
    throw new Exception("Upload move failed");
}

try{
    $conn->begin_transaction();
    try { $conn->query("ALTER TABLE gifts ADD COLUMN image_alt VARCHAR(255) NULL"); } catch (Exception $e) {}

    $uploadResult = uploadAndConvertToWebP('image',$uploadDir, 85);
    if(!$uploadResult['success']) throw new Exception("Upload failed: " . $uploadResult['error']);
    $mainImage = 'uploads/gifts/'.$uploadResult['filename'];

    $model3d = uploadFile('model_3d',$uploadDir,false,['glb','gltf']);

    /* ================= GALLERY ================= */
    $galleryPaths = [];
    if(isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])){
        $files = $_FILES['gallery'];
        for($i=0; $i<min(count($files['name']), 2); $i++){
            if($files['error'][$i] == 0){
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if(in_array($ext, ['jpg','jpeg','png','webp'])){
                    $newGalName = "gal_" . uniqid() . ".webp";
                    $galResult = uploadAndConvertToWebPFromTemp($files['tmp_name'][$i], $newGalName, $uploadDir, 85);
                    if($galResult['success']){
                        $galleryPaths[] = 'uploads/gifts/' . $galResult['filename'];
                    }
                }
            }
        }
    }
    $imagesGallery = !empty($galleryPaths) ? json_encode($galleryPaths) : null;

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

    $sql="INSERT INTO gifts
    (name,slug,description,image_alt,faqs,price,original_price,image,model_3d,images_gallery,in_stock,status,meta_title,meta_description,meta_keywords,rating,delivery_sameday,delivery_nextday,tag,created_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

    $stmt=$conn->prepare($sql);
    $stmt->bind_param("sssssddsssiisssdiis",
        $name,$slug,$description,$image_alt,$faqs_json,$price,$original_price,
        $mainImage,$model3d,$imagesGallery,$in_stock,$status,
        $meta_title,$meta_description,$meta_keywords,
        $rating,$delivery_sameday,$delivery_nextday,$tag
    );
    $stmt->execute();
    $giftId=$conn->insert_id;
    $stmt->close();

    /* ================= VARIANTS ================= */
    if(isset($_POST['variant_name']) && is_array($_POST['variant_name'])){
        $vstmt = $conn->prepare("INSERT INTO gift_variants (gift_id, name, original_price, price) VALUES (?, ?, ?, ?)");
        $vnames = $_POST['variant_name'];
        $vorig_prices = $_POST['variant_original_price'] ?? [];
        $vprices = $_POST['variant_price'];
        
        for($i = 0; $i < count($vnames); $i++){
            $vname = trim($vnames[$i]);
            $vorig_price = isset($vorig_prices[$i]) && trim($vorig_prices[$i]) !== '' ? floatval($vorig_prices[$i]) : null;
            $vprice = floatval($vprices[$i]);
            
            if($vname !== '' && $vprice > 0){
                $vstmt->bind_param("isdd", $giftId, $vname, $vorig_price, $vprice);
                $vstmt->execute();
            }
        }
        $vstmt->close();
    }


    $conn->commit();

    $_SESSION['success']="Gift added successfully";
    header("Location: ../gifts.php");
    exit();

}catch(Exception $e){
    $conn->rollback();
    $_SESSION['error']=$e->getMessage();
    header("Location: ../add-gift.php");
    exit();
}
?>
