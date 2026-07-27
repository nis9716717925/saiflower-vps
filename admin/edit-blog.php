<?php
require_once __DIR__.'/auth_check.php';
require_once '../config.php';

$id = intval($_GET['id'] ?? 0);
$res = mysqli_query($conn, "SELECT * FROM blogs WHERE id=$id");
$b = mysqli_fetch_assoc($res);

if(!$b){
    die("Blog not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Edit Blog Post'; include 'partials/head.php'; ?>

<style>
:root{
--primary:#326e54;
--accent:#d4af37;
--bg:#f4f7f6;
--text:#333;
}

.admin-main{
    margin-left: 0; 
    padding: 30px; 
    min-height: 100vh;
    display: block;
    width: 100%;
}
@media (min-width: 992px) {
    .admin-main { margin-left: 260px; width: calc(100% - 260px); }
}

.card{
background:#fff;
padding:30px 22px;
border-radius:20px;
box-shadow:0 10px 30px rgba(0,0,0,.05);
width:100%;
max-width:700px;
}

label{
display:block;
margin-bottom:6px;
font-weight:700;
font-size:.82rem;
text-transform:uppercase;
}

input[type="text"],textarea{
width:100%;
padding:14px;
margin-bottom:18px;
border:1px solid #ddd;
border-radius:12px;
font-size:15px;
}

input:focus,textarea:focus{
outline:none;
border-color:var(--primary);
box-shadow:0 0 0 3px rgba(50,110,84,.08);
}

textarea{min-height:220px;}

.slug-preview{
font-size:.75rem;
color:#666;
margin-top:-10px;
margin-bottom:20px;
}

.hint{
font-size:.72rem;
color:#999;
margin-top:-10px;
margin-bottom:18px;
}

.image-section{
display:grid;
grid-template-columns:1fr 1fr;
gap:15px;
margin-bottom:25px;
}

.preview-box{
background:#fafafa;
border:1px solid #eee;
border-radius:12px;
padding:10px;
text-align:center;
}

.preview-box img{
width:100%;
height:120px;
object-fit:cover;
border-radius:8px;
}

.image-upload-wrapper{
width:100%;
height:140px;
border:2px dashed #ddd;
border-radius:12px;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
overflow:hidden;
background:#fafafa;
}

.btn-submit{
background:var(--primary);
color:#fff;
border:none;
width:100%;
padding:16px;
font-weight:700;
border-radius:50px;
cursor:pointer;
}
</style>
</head>

<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
<div class="card">

<h2>Edit Blog</h2>

<form action="actions/update_blog.php" method="post" enctype="multipart/form-data">
<?php csrf_field(); ?>
<input type="hidden" name="id" value="<?= $b['id']; ?>">

<label>Blog Title</label>
<input type="text" name="title" id="titleInput" value="<?= htmlspecialchars($b['title']); ?>" required>

<label>Page Slug (SEO URL)</label>
<input type="text" name="slug" id="slugInput" value="<?= htmlspecialchars($b['slug'] ?? ''); ?>">
<div class="slug-preview">
URL: <strong>/blog/<span id="slugPreview"><?= htmlspecialchars($b['slug'] ?? ''); ?></span></strong>
</div>
<span class="hint">Changing slug changes page URL</span>

<label>Featured Image</label>
<div class="image-section">
<div class="preview-box">
<?php if($b['image']): ?>
<img src="/uploads/<?= $b['image']; ?>">
<?php endif; ?>
<div style="font-size:.7rem;color:#888">Current</div>
</div>

<div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click()">
<div id="uploadPlaceholder">Replace</div>
<img id="newImagePreview" style="display:none;">
</div>
</div>
<input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">

<label>Additional Gallery (Optional)</label>
<div class="current-asset-box" style="text-align: left; display: flex; gap: 10px; flex-wrap: wrap; border: 1px dashed #ddd; border-radius: 12px; padding: 15px; margin-bottom: 20px; min-height: 80px; background: #fafafa;">
    <?php 
    if(!empty($b['images_gallery'])) {
        $gallery = json_decode($b['images_gallery'], true);
        if($gallery) {
            foreach($gallery as $index => $gPath):
                $fullGPath = (strpos($gPath, 'uploads/') === 0) ? "/" . $gPath : "/uploads/" . $gPath;
    ?>
    <div style="position: relative; width: 80px; height: 80px;">
        <img src="<?= htmlspecialchars($fullGPath) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
        <a href="actions/delete_blog_image.php?id=<?= $id ?>&index=<?= $index ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirm('Delete this image?')" style="position: absolute; top: -5px; right: -5px; background: rgba(255,0,0,0.8); color: white; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 10px; text-decoration: none;"><i class="fas fa-times"></i></a>
    </div>
    <?php 
            endforeach;
        } else {
            echo "<span style='color: #888; font-size: 0.85rem;'>No gallery images yet.</span>";
        }
    } else {
        echo "<span style='color: #888; font-size: 0.85rem;'>No gallery images yet.</span>";
    }
    ?>
</div>
<input type="file" name="gallery[]" multiple accept="image/*" style="margin-bottom:20px; width: 100%;">

<div style="display:flex; justify-content:space-between; align-items:center;">
    <label>Content</label>
    <button type="button" onclick="applyLink('blogContent')" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:1.1rem;" title="Insert Link">
        <i class="fas fa-link"></i>
    </button>
</div>
<textarea name="content" id="blogContent" required><?= htmlspecialchars($b['content']); ?></textarea>
<span class="hint"><i class="fas fa-link mr-1"></i> Tip: Use the link icon above or type <code>[word](https://link.com)</code> manually.</span>

<h3 style="color:var(--primary)">SEO</h3>

<label>Meta Title</label>
<input type="text" name="meta_title" value="<?= htmlspecialchars($b['meta_title'] ?? ''); ?>">

<label>Meta Description</label>
<textarea name="meta_description"><?= htmlspecialchars($b['meta_description'] ?? ''); ?></textarea>

<label>Meta Keywords</label>
<input type="text" name="meta_keywords" value="<?= htmlspecialchars($b['meta_keywords'] ?? ''); ?>">

<label style="display:flex;gap:10px;margin-top:15px">
<input type="checkbox" name="status" value="1" <?= $b['status']==1?'checked':''; ?>> Published
</label>

<button type="submit" class="btn-submit">Update Blog</button>
</form>

</div>
</main>

<script>
// image preview
document.getElementById('imageInput').addEventListener('change',function(){
const file=this.files[0];
if(file){
const reader=new FileReader();
reader.onload=function(e){
let img=document.getElementById('newImagePreview');
img.src=e.target.result;
img.style.display='block';
document.getElementById('uploadPlaceholder').style.display='none';
}
reader.readAsDataURL(file);
}
});

// slug system
const titleInput=document.getElementById('titleInput');
const slugInput=document.getElementById('slugInput');
const slugPreview=document.getElementById('slugPreview');

let manual=false;

slugInput.addEventListener('input',()=>{
manual=true;
updatePreview();
});

titleInput.addEventListener('input',()=>{
if(manual) return;

let slug=titleInput.value
.toLowerCase()
.replace(/[^a-z0-9\s-]/g,'')
.replace(/\s+/g,'-')
.replace(/-+/g,'-')
.replace(/^-+|-+$/g,'');

slugInput.value=slug;
updatePreview();
});

function updatePreview(){
slugPreview.textContent=slugInput.value || '';
}

function applyLink(textareaId) {
    const textarea = document.getElementById(textareaId);
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);

    if (!selectedText) {
        alert('Please select some text first to apply a link.');
        return;
    }

    const url = prompt('Enter the redirection URL (e.g., https://example.com):', 'https://');
    
    if (url && url !== 'https://') {
        const replacement = `[${selectedText}](${url})`;
        textarea.setRangeText(replacement, start, end, 'select');
    }
}
</script>

</body>
</html>
