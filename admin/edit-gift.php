<?php
// 1. Correct File Paths using Document Root
require_once __DIR__.'/auth_check.php';
require_once '../config.php';

// Auto-migration for faqs column
$checkFaqs = $conn->query("SHOW COLUMNS FROM gifts LIKE 'faqs'");
if($checkFaqs && $checkFaqs->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE gifts ADD COLUMN faqs TEXT DEFAULT NULL");
    } catch(Exception $e) { error_log("FAQ Migration failed: " . $e->getMessage()); }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch Existing Product Data
$id = intval($_GET['id'] ?? 0);

try {
    $res = mysqli_query($conn, "SELECT * FROM gifts WHERE id=$id");
    $f = mysqli_fetch_assoc($res);
} catch (Exception $e) {
    die("Gift not found or query error.");
}

if (!$f) {
    die("Gift not found.");
}

// Fetch Variants
$variants = [];
try {
    $variants_res = mysqli_query($conn, "SELECT * FROM gift_variants WHERE gift_id=$id");
    while($v = mysqli_fetch_assoc($variants_res)) {
        $variants[] = $v;
    }
} catch (Exception $e) {
    // Variants table might be missing
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<title>Edit Gift | Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Added Select2 CSS for Tags -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
:root{
--primary:#326e54;
--accent:#d4af37;
--bg:#f4f7f6;
--text:#333;
}
body{font-family:Inter,-apple-system,sans-serif;background:var(--bg);color:var(--text);margin:0;padding:20px 15px;display:flex;flex-direction:column;align-items:center;}
.card{background:#fff;padding:30px 20px;border-radius:20px;width:100%;max-width:650px;box-shadow:0 10px 30px rgba(0,0,0,.05);box-sizing:border-box;}
h2{margin-top:0;color:var(--primary);font-size:1.5rem;display:flex;align-items:center;gap:10px;}
.section-title{color:var(--primary);border-bottom:2px solid #f0f0f0;padding-bottom:10px;margin:35px 0 20px;font-size:1.1rem;display:flex;align-items:center;gap:8px;}
label{display:block;margin-bottom:8px;font-weight:700;font-size:.85rem;color:#444;text-transform:uppercase;}
input[type="text"],input[type="number"],textarea{width:100%;padding:14px;margin-bottom:20px;border:1px solid #ddd;border-radius:12px;box-sizing:border-box;font-size:16px;transition:.3s;}
input:focus,textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(50,110,84,.1);}
.status-toggle{display:flex;gap:10px;margin-bottom:20px;}
.status-toggle label{flex:1;background:#f8faf9;border:1px solid #eee;padding:12px;text-align:center;border-radius:10px;cursor:pointer;text-transform:none;font-weight:600;transition:.3s;}
.status-toggle input{display:none;}
.status-toggle label:has(input:checked){background:#e9f1ee;border-color:var(--primary);color:var(--primary);}
.btn-submit{background:var(--primary);color:#fff;padding:16px;border:none;width:100%;border-radius:50px;cursor:pointer;font-size:1rem;font-weight:700;transition:.3s;box-shadow:0 4px 15px rgba(50,110,84,.2);}
.btn-submit:hover{transform:translateY(-2px);opacity:.9;}
.back-link{display:block;text-align:center;margin-top:25px;color:#888;text-decoration:none;font-size:.9rem;}
#discount-hint{color:#e74c3c;font-weight:bold;font-size:.8rem;margin-top:-15px;margin-bottom:15px;display:block;}
.slug-note{font-size:.75rem;color:#888;margin-top:-15px;margin-bottom:20px;}

/* Custom styles for Select2 to match theme */
.select2-container .select2-selection--multiple {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 8px;
    font-size: 16px;
    min-height: 50px;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(50,110,84,.1);
}

/* Current Asset Boxes for Edit Mode */
.current-asset-box { background: #fafafa; border: 1px dashed #ddd; border-radius: 15px; padding: 15px; margin-bottom: 20px; text-align: center; }
.current-asset-box img { max-width: 100%; height: 180px; object-fit: cover; border-radius: 10px; margin-bottom: 10px; border: 1px solid #ddd; }
.file-info { font-size: 0.8rem; color: #666; margin-bottom: 10px; display: block; }
</style>
</head>
<body>

<?php if(isset($_SESSION['error'])): ?>
<div style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:15px;border-radius:12px;margin-bottom:20px;width:100%;max-width:650px;">
<i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<div class="card">
<h2><i class="fas fa-edit"></i> Edit Gift</h2>
<p style="color:#666;margin-bottom:30px;">Updating details for: <strong><?= htmlspecialchars($f['name']); ?></strong></p>

<form action="actions/update_gift.php" method="post" enctype="multipart/form-data">
<?php csrf_field(); ?>
<input type="hidden" name="id" value="<?= $id ?>">

<label>Gift Name</label>
<input type="text" name="name" id="flowerName" value="<?= htmlspecialchars($f['name']); ?>" required>

<label>Product Slug (SEO URL)</label>
<input type="text" name="slug" id="slugField" value="<?= htmlspecialchars($f['slug'] ?? ''); ?>">
<div class="slug-note">Final URL: /gifts/<span id="slugPreview" style="color:var(--primary); font-weight:bold;"><?= htmlspecialchars($f['slug'] ?? 'your-slug'); ?></span></div>

<div style="display:flex;gap:15px;">
<div style="flex:1;">
<label>MRP (₹)</label>
<input type="number" name="original_price" id="mrp" value="<?= $f['original_price'] ?? ''; ?>" placeholder="1000">
</div>
<div style="flex:1;">
<label>Selling Price (₹)</label>
<input type="number" name="price" id="sale" value="<?= $f['price']; ?>" required placeholder="800">
</div>
</div>
<span id="discount-hint"></span>

<label>Availability</label>
<div class="status-toggle">
<label><input type="radio" name="in_stock" value="1" <?= $f['in_stock'] == 1 ? 'checked' : ''; ?>><span>In Stock</span></label>
<label><input type="radio" name="in_stock" value="0" <?= $f['in_stock'] == 0 ? 'checked' : ''; ?>><span>Out of Stock</span></label>
</div>

<!-- UPGRADED FIELDS (Rating, Delivery, Tags, Occasions) -->
<div style="display:flex;gap:15px;margin-bottom:20px;">
    <div style="flex:1;">
        <label>Rating (Out of 5)</label>
        <input type="number" name="rating" step="0.1" min="0" max="5" value="<?= htmlspecialchars($f['rating'] ?? '5.0'); ?>" required style="margin-bottom:0;">
    </div>
    <div style="flex:2;">
        <label>Delivery Options</label>
        <div style="display:flex;gap:15px;padding:12px 0;">
            <label style="display:inline-flex;align-items:center;gap:5px;font-weight:normal;text-transform:none;">
                <input type="checkbox" name="delivery_sameday" value="1" <?= (!isset($f['delivery_sameday']) || $f['delivery_sameday'] == 1) ? 'checked' : ''; ?>> Same Day
            </label>
            <label style="display:inline-flex;align-items:center;gap:5px;font-weight:normal;text-transform:none;">
                <input type="checkbox" name="delivery_nextday" value="1" <?= (!isset($f['delivery_nextday']) || $f['delivery_nextday'] == 1) ? 'checked' : ''; ?>> Next Day
            </label>
        </div>
    </div>
</div>

<label>Product Tags</label>
<p style="font-size: 0.85rem; color: #666; margin-top:-5px; margin-bottom: 10px;">Type and press enter to add new tags, or select existing ones.</p>
<select name="tags[]" multiple class="tag-select" style="width:100%;margin-bottom:20px;">
    <?php 
    $current_tags_raw = isset($f['tag']) ? explode(',', trim($f['tag'], ',')) : [];
    $current_tags = array_map('trim', $current_tags_raw);
    $current_tags = array_filter($current_tags); // Remove empty values
    
    // Array to keep track of which tags from $current_tags were found in DB
    $matched_tags = [];

    try {
        $tag_res = $conn->query("SELECT name FROM tags WHERE status=1 ORDER BY name ASC");
        if($tag_res) {
            while($tg = $tag_res->fetch_assoc()) {
                $db_tag_name = $tg['name'];
                
                // Case-insensitive check
                $is_selected = false;
                foreach ($current_tags as $ct) {
                    if (strcasecmp($ct, $db_tag_name) === 0) {
                        $is_selected = true;
                        $matched_tags[] = $ct; // Mark as found
                        break;
                    }
                }
                
                $selected_attr = $is_selected ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($db_tag_name) . '" ' . $selected_attr . '>' . htmlspecialchars($db_tag_name) . '</option>';
            }
        }
    } catch(Exception $e) {
        // Suppress
    }

    // Now append the missing tags that were in the product but not in the tags table
    foreach ($current_tags as $ct) {
        if (!in_array($ct, $matched_tags)) {
            // These tags exist on the product, so we MUST render them as selected so they aren't lost on save
            echo '<option value="' . htmlspecialchars($ct) . '" selected>' . htmlspecialchars($ct) . '</option>';
        }
    }
    ?>
</select>

<label>Description</label>
<textarea name="description" required style="min-height:120px;"><?= htmlspecialchars($f['description']); ?></textarea>

<label>Image Alt Text (SEO)</label>
<input type="text" name="image_alt" value="<?= htmlspecialchars($f['image_alt'] ?? '') ?>" placeholder="e.g. Romantic gift hamper with chocolates and roses">

<label>Main Product Image</label>
<div class="current-asset-box">
    <?php 
        $imgPath = (strpos($f['image'], 'uploads/') === 0) ? "/" . $f['image'] : "/uploads/" . $f['image'];
    ?>
    <img src="<?= $imgPath ?>" onerror="this.src='https://placehold.co/400x300?text=No+Image+Found'">
    <input type="file" name="image" accept="image/*" style="font-size: 0.9rem;">
    <p style="font-size: 0.7rem; color: #888; margin-top: 5px;">Upload a file to replace current image</p>
</div>

<label>Additional Gallery (Optional - Max 2 Total)</label>
<p style="font-size: 0.75rem; color: #888; margin-bottom: 10px;">You can have a maximum of 2 additional gallery images. Currently using: <span id="currentImgCount"><?= !empty($f['images_gallery']) ? count(json_decode($f['images_gallery'], true)) : 0 ?></span>/2</p>
<input type="file" name="gallery[]" id="galleryInput" multiple accept="image/*" style="margin-bottom:20px;">


<h3 class="section-title">Product Variants (Optional) 📦</h3>
<p style="font-size: 0.85rem; color: #666; margin-top:-15px; margin-bottom: 15px;">Manage sizes or types and their specific prices.</p>
<div id="variantsContainer" style="margin-bottom: 20px;">
    <?php foreach($variants as $v): ?>
    <div style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
        <input type="text" name="variant_name[]" value="<?= htmlspecialchars($v['name']) ?>" required style="margin-bottom: 0; flex: 2; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        <input type="number" name="variant_original_price[]" value="<?= $v['original_price'] ?? '' ?>" style="margin-bottom: 0; flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        <input type="number" name="variant_price[]" value="<?= $v['price'] ?>" required style="margin-bottom: 0; flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        <button type="button" onclick="this.parentElement.remove()" style="background: #fee2e2; color: #b91c1c; border: none; width: 40px; height: 40px; border-radius: 8px; cursor: pointer;"><i class="fas fa-trash"></i></button>
    </div>
    <?php endforeach; ?>
</div>
<button type="button" onclick="addVariantRow()" style="background:#e9f1ee; color:var(--primary); border:1px dashed var(--primary); padding:10px 15px; border-radius:10px; cursor:pointer; font-weight:bold; width:100%; margin-bottom: 20px;"><i class="fas fa-plus"></i> Add Variant Option</button>

<label>3D Model (GLB/GLTF)</label>
<div class="current-asset-box" style="text-align: left;">
    <?php if(!empty($f['model_3d'])): ?>
        <div style="color:#27ae60; font-weight: bold; margin-bottom: 10px;">
            <i class="fas fa-check-circle"></i> 3D Model Attached: <?= basename($f['model_3d']) ?>
        </div>
    <?php else: ?>
        <div style="color:#888; margin-bottom: 10px;">
            <i class="fas fa-times-circle"></i> No 3D model uploaded.
        </div>
    <?php endif; ?>
    <input type="file" name="model_3d" accept=".glb,.gltf" style="font-size: 0.9rem;">
</div>

<h3 class="section-title">Frequently Asked Questions (FAQs) ❓</h3>
<p style="font-size: 0.85rem; color: #666; margin-top:-15px; margin-bottom: 15px;">Manage common questions and answers for this product.</p>
<div id="faqContainer" style="margin-bottom: 20px;">
    <!-- FAQ Rows will be injected here via JS -->
</div>
<button type="button" onclick="addFaqRow()" style="background:#e9f1ee; color:var(--primary); border:1px dashed var(--primary); padding:10px 15px; border-radius:10px; cursor:pointer; font-weight:bold; width:100%; margin-bottom: 20px;"><i class="fas fa-plus"></i> Add FAQ</button>

<h3 class="section-title">SEO Configuration 🚀</h3>

<label>Meta Title</label>
<input type="text" name="meta_title" value="<?= htmlspecialchars($f['meta_title'] ?? ''); ?>" placeholder="Gift name + City name">

<label>Meta Description</label>
<textarea name="meta_description" style="min-height:80px;" placeholder="Brief summary..."><?= htmlspecialchars($f['meta_description'] ?? ''); ?></textarea>

<label>Meta Keywords</label>
<input type="text" name="meta_keywords" value="<?= htmlspecialchars($f['meta_keywords'] ?? ''); ?>" placeholder="teddy bear, chocolates">

<button type="submit" class="btn-submit">Update Product Info</button>
</form>

<a href="gifts.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Gift List</a>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const nameInput=document.getElementById("flowerName");
const slugField=document.getElementById("slugField");
const slugPreview=document.getElementById("slugPreview");

function makeSlug(text){
    return text.toString().toLowerCase().trim()
        .replace(/\s+/g, '-')           
        .replace(/[^\w\-]+/g, '')       
        .replace(/\-\-+/g, '-')         
        .replace(/^-+/, '')             
        .replace(/-+$/, '');            
}

// Only auto-generate if slug field is empty when changing name
nameInput.addEventListener("input",()=>{
    if(slugField.value.trim() === ""){
        let slug = makeSlug(nameInput.value);
        slugField.value = slug;
        slugPreview.innerText = slug || "your-slug";
    }
});

slugField.addEventListener("input",()=>{
    let cleaned = slugField.value.toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]+/g, '');
    slugField.value = cleaned;
    slugPreview.innerText = cleaned || "your-slug";
});

const mrp=document.getElementById('mrp');
const sale=document.getElementById('sale');
const hint=document.getElementById('discount-hint');

function calcDiscount(){
    const valMRP=parseFloat(mrp.value);
    const valSale=parseFloat(sale.value);
    if(valMRP && valSale && valMRP > valSale){
        let disc=Math.round(((valMRP-valSale)/valMRP)*100);
        hint.innerText="✨ Saving: "+disc+"% Discount";
    }else{hint.innerText="";}
}
mrp.addEventListener('input',calcDiscount);
sale.addEventListener('input',calcDiscount);
window.onload = calcDiscount; // Run once on page load

// Variants Script
function addVariantRow() {
    const container = document.getElementById('variantsContainer');
    const row = document.createElement('div');
    row.style.display = 'flex';
    row.style.gap = '10px';
    row.style.marginBottom = '10px';
    row.style.alignItems = 'center';
    row.innerHTML = `
        <input type="text" name="variant_name[]" placeholder="Variant Name" required style="margin-bottom: 0; flex: 2; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        <input type="number" name="variant_original_price[]" placeholder="MRP" style="margin-bottom: 0; flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        <input type="number" name="variant_price[]" placeholder="Offer Price" required style="margin-bottom: 0; flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        <button type="button" onclick="this.parentElement.remove()" style="background: #fee2e2; color: #b91c1c; border: none; width: 40px; height: 40px; border-radius: 8px; cursor: pointer;"><i class="fas fa-trash"></i></button>
    `;
    container.appendChild(row);
}

// Select2 Initialization for Tags
$(document).ready(function() {
    $('.tag-select').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        placeholder: "Type to search or create tags...",
        width: '100%'
    });
    const galleryInput = document.getElementById('galleryInput');
    const currentImgCountElement = document.getElementById('currentImgCount');
    const currentImgCount = currentImgCountElement ? parseInt(currentImgCountElement.innerText) : 0;
    if(galleryInput) {
        galleryInput.addEventListener('change', function() {
            const maxAllowed = 2 - currentImgCount;
            if(this.files.length > maxAllowed) {
                alert("You can only upload " + (maxAllowed > 0 ? maxAllowed : 0) + " more gallery images (Total limit is 2).");
                this.value = '';
            }
        });
    }
});

// FAQs Script
function addFaqRow(q = '', a = '') {
    const container = document.getElementById('faqContainer');
    const row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '10px';
    row.style.marginBottom = '20px';
    row.style.padding = '15px';
    row.style.background = '#fafafa';
    row.style.border = '1px solid #ddd';
    row.style.borderRadius = '12px';
    
    // Safely assign values via DOM to prevent XSS if pre-populated
    const qInput = document.createElement('input');
    qInput.type = 'text';
    qInput.name = 'faq_question[]';
    qInput.placeholder = 'Question';
    qInput.required = true;
    qInput.style.marginBottom = '0';
    qInput.value = q;
    
    const aInput = document.createElement('textarea');
    aInput.name = 'faq_answer[]';
    aInput.placeholder = 'Answer';
    aInput.required = true;
    aInput.style.marginBottom = '0';
    aInput.style.minHeight = '60px';
    aInput.value = a;
    
    const delBtn = document.createElement('button');
    delBtn.type = 'button';
    delBtn.innerHTML = '<i class="fas fa-trash"></i> Remove FAQ';
    delBtn.style.background = '#fee2e2';
    delBtn.style.color = '#b91c1c';
    delBtn.style.border = 'none';
    delBtn.style.padding = '10px';
    delBtn.style.borderRadius = '8px';
    delBtn.style.cursor = 'pointer';
    delBtn.style.fontWeight = 'bold';
    delBtn.style.alignSelf = 'flex-end';
    delBtn.onclick = function() { row.remove(); };
    
    row.appendChild(qInput);
    row.appendChild(aInput);
    row.appendChild(delBtn);
    container.appendChild(row);
}

// Load existing FAQs
<?php 
$existingFaqs = [];
if (!empty($f['faqs'])) {
    $decoded = json_decode($f['faqs'], true);
    if (is_array($decoded)) $existingFaqs = $decoded;
}
?>
const existingFaqs = <?= json_encode($existingFaqs, JSON_UNESCAPED_UNICODE) ?>;
if (existingFaqs.length > 0) {
    existingFaqs.forEach(faq => addFaqRow(faq.question, faq.answer));
}
</script>

</body>
</html>