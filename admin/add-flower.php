<?php
require_once __DIR__.'/auth_check.php';
require_once '../config.php';

// Auto-migration for faqs column
$checkFaqs = $conn->query("SHOW COLUMNS FROM flowers LIKE 'faqs'");
if($checkFaqs && $checkFaqs->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE flowers ADD COLUMN faqs TEXT DEFAULT NULL");
    } catch(Exception $e) { error_log("FAQ Migration failed: " . $e->getMessage()); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<title>Add New Flower | Admin</title>
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
.image-upload-wrapper{width:100%;min-height:200px;border:2px dashed #ddd;border-radius:15px;margin-bottom:20px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#fafafa;position:relative;cursor:pointer;padding:10px;}
.image-upload-wrapper i{font-size:2rem;color:#ccc;margin-bottom:10px;}
.image-upload-wrapper span{color:#aaa;font-size:.8rem;}
#previewGrid img{width:100%;height:150px;object-fit:cover;border-radius:8px;border:1px solid #eee;}
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
</style>
</head>
<body>

<?php if(isset($_SESSION['error'])): ?>
<div style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:15px;border-radius:12px;margin-bottom:20px;width:100%;max-width:650px;">
<i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<div class="card">
<h2><i class="fas fa-plus-circle"></i> Add New Flower</h2>
<p style="color:#666;margin-bottom:30px;">Add a fresh flower to your online store.</p>

<form action="actions/add_flower.php" method="POST" enctype="multipart/form-data">
<?php csrf_field(); ?>

<label>Flower Name</label>
<input type="text" name="name" id="flowerName" placeholder="e.g. Elegant Red Roses" required>

<label>Product Slug (SEO URL)</label>
<input type="text" name="slug" id="slugField" placeholder="auto-generated">
<div class="slug-note">Final URL: /flowers/<span id="slugPreview" style="color:var(--primary); font-weight:bold;">your-slug</span></div>

<div style="display:flex;gap:15px;">
<div style="flex:1;">
<label>MRP (₹)</label>
<input type="number" name="original_price" id="mrp" placeholder="1000">
</div>
<div style="flex:1;">
<label>Selling Price (₹)</label>
<input type="number" name="price" id="sale" required placeholder="800">
</div>
</div>
<span id="discount-hint"></span>

<div style="display:flex; justify-content:between; align-items:center;">
    <label>Root Category</label>
    <a href="categories.php" target="_blank" style="font-size: 0.75rem; color: var(--primary); font-weight: bold; margin-bottom: 8px; text-decoration: none;"><i class="fas fa-external-link-alt"></i> Manage / Add New</a>
</div>
<select name="category_ids[]" class="cat-select" multiple style="width:100%; margin-bottom: 20px;" required>
    <?php 
    $cat_res = $conn->query("SELECT id, name FROM categories WHERE status=1 ORDER BY name ASC");
    while($cat = $cat_res->fetch_assoc()) {
        echo '<option value="'.$cat['id'].'">'.htmlspecialchars($cat['name']).'</option>';
    }
    ?>
</select>

<label>Availability</label>
<div class="status-toggle">
<label><input type="radio" name="in_stock" value="1" checked><span>In Stock</span></label>
<label><input type="radio" name="in_stock" value="0"><span>Out of Stock</span></label>
</div>

<!-- NEW UPGRADED FIELDS (Rating, Delivery, Tags, Occasions) -->
<div style="display:flex;gap:15px;margin-bottom:20px;">
    <div style="flex:1;">
        <label>Rating (Out of 5)</label>
        <input type="number" name="rating" step="0.1" min="0" max="5" value="5.0" required style="margin-bottom:0;">
    </div>
    <div style="flex:2;">
        <label>Delivery Options</label>
        <div style="display:flex;gap:15px;padding:12px 0;">
            <label style="display:inline-flex;align-items:center;gap:5px;font-weight:normal;text-transform:none;"><input type="checkbox" name="delivery_sameday" value="1" checked> Same Day</label>
            <label style="display:inline-flex;align-items:center;gap:5px;font-weight:normal;text-transform:none;"><input type="checkbox" name="delivery_nextday" value="1" checked> Next Day</label>
        </div>
    </div>
</div>

<label>Product Tags</label>
<p style="font-size: 0.85rem; color: #666; margin-top:-5px; margin-bottom: 10px;">Type and press enter to add new tags, or select existing ones.</p>
<select name="tags[]" multiple class="tag-select" style="width:100%;margin-bottom:20px;">
    <?php 
    try {
        $tag_res = $conn->query("SELECT name FROM tags WHERE status=1 ORDER BY name ASC");
        if($tag_res) {
            while($tg = $tag_res->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($tg['name']) . '">' . htmlspecialchars($tg['name']) . '</option>';
            }
        }
    } catch(Exception $e) {
        // Table mighty missing, swallow error so it doesn't crash HTML
    }
    ?>
</select>

<label>Description</label>
<textarea name="description" placeholder="Describe the flower..." required style="min-height:120px;"></textarea>

<label>Image Alt Text (SEO)</label>
<input type="text" name="image_alt" placeholder="e.g. Premium red rose bouquet for anniversary">

<label>Product Photos (Main + Gallery)</label>
<div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click()">
<div id="uploadUI"><i class="fas fa-camera"></i><br><span>Tap to upload main photo</span></div>
<div id="previewGrid" style="display:none;width:100%;"></div>
</div>
<input type="file" name="image" id="imageInput" accept="image/*" required style="display:none;">

<label>Additional Gallery (Optional - Max 2 Images)</label>
<input type="file" name="gallery[]" id="galleryInput" multiple accept="image/*" style="margin-bottom:20px;">

<!-- UPGRADED FIELDS (Variants, 3D Models, SEO) -->
<h3 class="section-title">Product Variants (Optional) 📦</h3>
<p style="font-size: 0.85rem; color: #666; margin-top:-15px; margin-bottom: 15px;">Add different sizes or types and their specific prices.</p>
<div id="variantsContainer" style="margin-bottom: 20px;">
    <!-- Variant Rows will be injected here -->
</div>
<button type="button" onclick="addVariantRow()" style="background:#e9f1ee; color:var(--primary); border:1px dashed var(--primary); padding:10px 15px; border-radius:10px; cursor:pointer; font-weight:bold; width:100%; margin-bottom: 20px;"><i class="fas fa-plus"></i> Add Variant Option</button>


<label>3D Model (GLB/GLTF)</label>
<input type="file" name="model_3d" accept=".glb,.gltf" style="margin-bottom:20px;">

<h3 class="section-title">Frequently Asked Questions (FAQs) ❓</h3>
<p style="font-size: 0.85rem; color: #666; margin-top:-15px; margin-bottom: 15px;">Add common questions and answers for this product.</p>
<div id="faqContainer" style="margin-bottom: 20px;">
    <!-- FAQ Rows will be injected here -->
</div>
<button type="button" onclick="addFaqRow()" style="background:#e9f1ee; color:var(--primary); border:1px dashed var(--primary); padding:10px 15px; border-radius:10px; cursor:pointer; font-weight:bold; width:100%; margin-bottom: 20px;"><i class="fas fa-plus"></i> Add FAQ</button>

<h3 class="section-title">SEO Configuration 🚀</h3>

<label>Meta Title</label>
<input type="text" name="meta_title" placeholder="Flower name + City name">

<label>Meta Description</label>
<textarea name="meta_description" placeholder="Brief summary..." style="min-height:80px;"></textarea>

<label>Meta Keywords</label>
<input type="text" name="meta_keywords" placeholder="keywords, separated by comma">

<button type="submit" class="btn-submit">Add to Inventory</button>
</form>

<a href="flowers.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Flower List</a>
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

let userEditedSlug = false;

nameInput.addEventListener("input",()=>{
    if(!userEditedSlug){
        let slug = makeSlug(nameInput.value);
        slugField.value = slug;
        slugPreview.innerText = slug || "your-slug";
    }
});

slugField.addEventListener("input",()=>{
    userEditedSlug = slugField.value.trim() !== "";
    let cleaned = slugField.value.toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]+/g, '');
    slugField.value = cleaned;
    slugPreview.innerText = cleaned || "your-slug";
});

const imageInput=document.getElementById('imageInput');
const previewGrid=document.getElementById('previewGrid');
const uploadUI=document.getElementById('uploadUI');

imageInput.addEventListener('change',function(){
    const file=this.files[0];
    if(file){
        previewGrid.innerHTML='';
        previewGrid.style.display='block';
        uploadUI.style.display='none';
        const reader=new FileReader();
        reader.onload=function(e){
            const img=document.createElement('img');
            img.src=e.target.result;
            previewGrid.appendChild(img);
        }
        reader.readAsDataURL(file);
    }
});

const mrp=document.getElementById('mrp');
const sale=document.getElementById('sale');
const hint=document.getElementById('discount-hint');

function calcDiscount(){
    const valMRP=parseFloat(mrp.value);
    const valSale=parseFloat(sale.value);
    if(valMRP && valSale && valMRP > valSale){
        let disc=Math.round(((valMRP-valSale)/valMRP)*100);
        hint.innerText="✨ Auto-tag: "+disc+"% Discount";
    }else{hint.innerText="";}
}
mrp.addEventListener('input',calcDiscount);
sale.addEventListener('input',calcDiscount);

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
    qInput.placeholder = 'Question (e.g., How long will the flowers last?)';
    qInput.required = true;
    qInput.style.marginBottom = '0';
    qInput.value = q;
    
    const aInput = document.createElement('textarea');
    aInput.name = 'faq_answer[]';
    aInput.placeholder = 'Answer (e.g., With proper care, they last 5-7 days.)';
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

// Select2 Initialization for Tags
$(document).ready(function() {
    $('.tag-select').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        placeholder: "Type to search or create tags...",
        width: '100%'
    });

    $('.cat-select').select2({
        tags: true,
        placeholder: "Select or type new category...",
        width: '100%'
    });

    const galleryInput = document.getElementById('galleryInput');
    if(galleryInput) {
        galleryInput.addEventListener('change', function() {
            if(this.files.length > 2) {
                alert("You can only upload a maximum of 2 gallery images.");
                this.value = '';
            }
        });
    }
});
</script>

</body>
</html>