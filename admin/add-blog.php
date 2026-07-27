<?php
require_once __DIR__.'/auth_check.php';
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Add New Blog Post'; include 'partials/head.php'; ?>

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
background:white;
padding:30px 20px;
border-radius:20px;
box-shadow:0 10px 30px rgba(0,0,0,0.05);
width:100%;
max-width:650px;
}

h2{
margin-bottom:5px;
color:var(--primary);
}

.subtitle{
font-size:.9rem;
color:#888;
margin-bottom:25px;
}

label{
display:block;
margin-bottom:6px;
font-weight:700;
font-size:.8rem;
text-transform:uppercase;
letter-spacing:.5px;
}

input[type="text"],textarea{
width:100%;
padding:14px;
margin-bottom:18px;
border:1px solid #ddd;
border-radius:12px;
font-size:15px;
transition:.2s;
}

input:focus,textarea:focus{
outline:none;
border-color:var(--primary);
box-shadow:0 0 0 3px rgba(50,110,84,.08);
}

textarea{min-height:220px;}

.hint{
display:block;
font-size:.72rem;
color:#999;
margin-top:-12px;
margin-bottom:15px;
}

.slug-preview{
font-size:.75rem;
color:#666;
margin-top:-10px;
margin-bottom:20px;
}

.image-upload-wrapper{
width:100%;
height:200px;
border:2px dashed #ddd;
border-radius:14px;
margin-bottom:20px;
display:flex;
align-items:center;
justify-content:center;
background:#fafafa;
cursor:pointer;
overflow:hidden;
}

.image-upload-wrapper img{
width:100%;
height:100%;
object-fit:cover;
display:none;
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
transition:.25s;
}

.btn-submit:hover{
transform:translateY(-1px);
box-shadow:0 8px 20px rgba(0,0,0,.08);
}
</style>
</head>

<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">

<div class="card">
<h2>New Blog Post</h2>
<p class="subtitle">Create SEO optimized article with clean URL</p>

<form action="actions/add_blog.php" method="post" enctype="multipart/form-data">
<?php csrf_field(); ?>

<label>Blog Title</label>
<input type="text" name="title" id="titleInput" required>

<label>Page Slug (SEO URL)</label>
<input type="text" name="slug" id="slugInput" placeholder="auto-generated">
<div class="slug-preview">
URL Preview: <strong>/blog/<span id="slugPreview">your-slug</span></strong>
</div>
<span class="hint">You can edit slug manually. Use lowercase and hyphens only.</span>

<label>Featured Image</label>
<div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click()">
<i class="fas fa-cloud-upload-alt" id="placeholderIcon"></i>
<img id="imagePreview">
</div>
<input type="file" name="image" id="imageInput" accept="image/*" required style="display:none;">

<div style="display:flex; justify-content:space-between; align-items:center;">
    <label>Blog Content</label>
    <button type="button" onclick="applyLink('blogContent')" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:1.1rem;" title="Insert Link">
        <i class="fas fa-link"></i>
    </button>
</div>
<textarea name="content" id="blogContent" required><?= htmlspecialchars($blog['content']) ?></textarea>
<span class="hint"><i class="fas fa-link mr-1"></i> Tip: Use the link icon above or type <code>[word](https://link.com)</code> manually.</span>

<h3 style="margin-top:30px;color:var(--primary)">SEO Settings</h3>

<label>Meta Title</label>
<input type="text" name="meta_title">

<label>Meta Description</label>
<textarea name="meta_description" style="min-height:100px"></textarea>

<label>Meta Keywords</label>
<input type="text" name="meta_keywords">

<label style="display:flex;align-items:center;gap:10px;margin-top:15px">
<input type="checkbox" name="status" value="1" checked> Publish Immediately
</label>

<button type="submit" class="btn-submit">Publish Blog</button>
</form>

</div>
</main>

<script>
// IMAGE PREVIEW
const imageInput=document.getElementById('imageInput');
const imagePreview=document.getElementById('imagePreview');
const placeholderIcon=document.getElementById('placeholderIcon');

imageInput.addEventListener('change',function(){
const file=this.files[0];
if(file){
const reader=new FileReader();
reader.onload=function(e){
imagePreview.src=e.target.result;
imagePreview.style.display='block';
placeholderIcon.style.display='none';
}
reader.readAsDataURL(file);
}
});

// SMART AUTO SLUG SYSTEM
const titleInput=document.getElementById('titleInput');
const slugInput=document.getElementById('slugInput');
const slugPreview=document.getElementById('slugPreview');

let manualSlugEdited=false;

// detect manual slug edit
slugInput.addEventListener('input',()=>{
manualSlugEdited=true;
updatePreview();
});

titleInput.addEventListener('input',()=>{
if(manualSlugEdited) return;

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
slugPreview.textContent = slugInput.value || "your-slug";
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
