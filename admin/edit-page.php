<?php
ini_set('display_startup_errors', 1);

$root = $_SERVER['DOCUMENT_ROOT'];
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (file_exists(__DIR__ . '/auth_check.php')) {
    require_once __DIR__ . '/auth_check.php';
} elseif (file_exists($root . '/admin/auth_check.php')) {
    require_once $root . '/admin/auth_check.php';
}

require_once $root . '/config.php';

if (!isset($_GET['id'])) {
    header("Location: pages.php");
    exit;
}

$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM dynamic_pages WHERE id = $id");
if (!$res || $res->num_rows === 0) {
    header("Location: pages.php");
    exit;
}
$pageData = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Edit Custom Page'; include 'partials/head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script>
      function toggleLayoutFields() {
          const layout = document.getElementById('layout_type').value;
          if(layout === 'event_info') {
              document.getElementById('extra_images_group').style.display = 'block';
              document.getElementById('page_tag_group').style.display = 'none';
              if(document.getElementById('midgrid_image_group')) document.getElementById('midgrid_image_group').style.display = 'none';
          } else {
              document.getElementById('extra_images_group').style.display = 'none';
              document.getElementById('page_tag_group').style.display = 'block';
              if(document.getElementById('midgrid_image_group')) document.getElementById('midgrid_image_group').style.display = 'block';
          }
      }
      document.addEventListener('DOMContentLoaded', toggleLayoutFields);
    </script>
    
    <style>
        :root {
            --primary: #326e54;
            --accent: #d4af37;
            --bg: #f4f7f6;
            --sidebar-width: 260px;
        }

        * { box-sizing: border-box; }
        html, body { width: 100vw; max-width: 100%; overflow-x: hidden; margin: 0; background: var(--bg); font-family: 'Inter', sans-serif; }

        .admin-main { margin-left: var(--sidebar-width); padding: 30px; min-height: 100vh; width: calc(100% - var(--sidebar-width)); display: block; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .header-title h2 { margin: 0; color: #1a1a1a; font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        
        .form-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; margin-bottom: 30px; }
        .form-card h3 { margin-top: 0; padding-bottom: 15px; border-bottom: 2px solid #f9f9f9; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #444; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Inter', sans-serif; font-size: 0.95rem; transition: 0.3s; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(50,110,84,0.1); }
        
        .btn-submit { background: var(--primary); color: white; padding: 15px 30px; border-radius: 12px; font-size: 1rem; font-weight: 700; border: none; cursor: pointer; transition: 0.3s; width: 100%; box-shadow: 0 4px 15px rgba(50, 110, 84, 0.2); }
        .btn-submit:hover { transform: translateY(-2px); opacity: 0.9; }

        .img-preview { max-width: 200px; border-radius: 10px; margin-top: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        @media (max-width: 992px) {
            .admin-main { margin-left: 0 !important; padding: 15px; width: 100% !important; margin-bottom: 80px; }
            .form-card { padding: 20px; }
        }
    </style>
</head>
<body>

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                <h2><a href="pages.php" style="color:#aaa; text-decoration:none;"><i class="fa-solid fa-arrow-left"></i></a> &nbsp; Edit Custom Page</h2>
            </div>
            <a href="/<?= htmlspecialchars($pageData['slug']) ?>" target="_blank" class="btn-submit" style="width:auto; padding: 12px 20px; background: var(--accent);"><i class="fa-solid fa-eye"></i> View Live</a>
        </div>

        <form action="actions/edit_page.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $pageData['id'] ?>">
            
            <div class="form-card">
                <h3><i class="fa-solid fa-pen-nib"></i> Basic Information</h3>
                <div class="form-group">
                    <label>Page Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($pageData['title']) ?>">
                </div>
                <div class="form-group">
                    <label>Short Description</label>
                    <textarea name="short_description" class="form-control" rows="2" placeholder="e.g. A brief overview of the event or showcase..."><?= htmlspecialchars($pageData['short_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>URL Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($pageData['slug']) ?>">
                </div>
                <div class="form-group">
                    <label>Layout Type</label>
                    <select name="layout_type" id="layout_type" class="form-control" onchange="toggleLayoutFields()">
                        <option value="event_info" <?= $pageData['layout_type'] === 'event_info' ? 'selected' : '' ?>>Event Story (Text + Banner + Images)</option>
                        <option value="product_showcase" <?= $pageData['layout_type'] === 'product_showcase' ? 'selected' : '' ?>>Product Showcase (Text + Image + Product Grid)</option>
                    </select>
                </div>
                <div class="form-group" id="page_tag_group" style="display:none;">
                    <label>Product Tag <small style="color:#888;font-weight:normal;">(Matches products tagged in database)</small></label>
                    <input type="text" name="page_tag" class="form-control" value="<?= htmlspecialchars($pageData['page_tag'] ?? '') ?>" placeholder="e.g. birthday, wedding, anniversary">
                </div>
                
                <div class="form-group" id="midgrid_image_group" style="display:none; background:#fafafa; border:1px solid #eee; padding:20px; border-radius:15px; margin-top:15px;">
                    <h4 style="margin-top:0; color:#555; margin-bottom:15px;"><i class="fa-solid fa-image"></i> Mid-Grid Promotional Banner</h4>
                    <p style="font-size:0.85rem; color:#666; margin-bottom:15px;">This image will elegantly sit in the mathematical center of the product grid to break up the flow.</p>
                    
                    <?php if (!empty($pageData['midgrid_image'])): ?>
                        <div style="margin-bottom: 20px; background:white; padding:15px; border-radius:10px; border:1px solid #eee;">
                            <label style="color:var(--primary); font-weight:600;">Current Mid-Grid Banner:</label>
                            <img src="/uploads/<?= htmlspecialchars($pageData['midgrid_image']) ?>" style="max-width:300px; width:100%; border-radius:8px; margin-top:10px; display:block; border:1px solid #ddd;">
                            <label style="color:#d9534f; margin-top:10px; display:block; cursor:pointer;"><input type="checkbox" name="remove_midgrid_image" value="1"> Remove this banner entirely</label>
                        </div>
                    <?php endif; ?>
                    
                    <label>Upload New Banner (Replaces existing if any)</label>
                    <input type="file" name="midgrid_image" class="form-control" accept="image/*" style="margin-bottom:15px;">
                    <label>Image Alt Tag (For SEO & Accessibility)</label>
                    <input type="text" name="midgrid_image_alt" class="form-control" placeholder="e.g. 50% Off Valentine's Special Basket" value="<?= htmlspecialchars($pageData['midgrid_image_alt'] ?? '') ?>">
                </div>
                <div class="form-group" id="extra_images_group">
                    <label>Extra Event Images</label>
                    <div id="existing_extra_images_container">
                        <?php if (!empty($pageData['extra_images'])): 
                            $extras = json_decode($pageData['extra_images'], true) ?: [];
                            if (count($extras) > 0):
                        ?>
                            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap:15px; margin-bottom:20px;">
                                <?php foreach ($extras as $i => $ex): 
                                      $imgName = is_array($ex) ? $ex['image'] : $ex;
                                      $imgDesc = is_array($ex) ? ($ex['desc'] ?? '') : '';
                                      $imgLink = is_array($ex) ? ($ex['link'] ?? '') : '';
                                ?>
                                    <div style="background:#f9f9f9; border:1px solid #ddd; padding:15px; border-radius:12px; display:flex; flex-direction:column; gap:10px;">
                                        <img src="/uploads/<?= htmlspecialchars($imgName) ?>" class="img-preview" style="height:120px; width:100%; object-fit:cover; border-radius:6px; margin:0;">
                                        <input type="hidden" name="existing_extra_image[]" value="<?= htmlspecialchars($imgName) ?>">
                                        <input type="text" name="existing_extra_desc[]" class="form-control" placeholder="Description or Caption" value="<?= htmlspecialchars($imgDesc) ?>">
                                        <input type="text" name="existing_extra_link[]" class="form-control" placeholder="Redirection URL (e.g., https://...)" value="<?= htmlspecialchars($imgLink) ?>">
                                        <label style="color:#d9534f; font-weight:bold; cursor:pointer; font-size:0.9rem;"><input type="checkbox" name="remove_extra_image[]" value="<?= htmlspecialchars($imgName) ?>"> Remove this image</label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; endif; ?>
                    </div>
                    
                    <div id="new_extra_images_container"></div>
                    <button type="button" onclick="addNewExtraImage()" class="btn-submit" style="background:#555; width:auto; padding:10px 15px; margin-top:10px;"><i class="fa-solid fa-plus"></i> Add New Image</button>

                    <script>
                        function addNewExtraImage() {
                            const container = document.getElementById('new_extra_images_container');
                            const div = document.createElement('div');
                            div.style.cssText = 'background:#fafafa; border:1px solid #eee; padding:15px; border-radius:12px; margin-bottom:10px; margin-top:15px; position:relative;';
                            div.innerHTML = `
                                <button type="button" onclick="this.parentElement.remove()" style="position:absolute; top:10px; right:15px; background:none; border:none; color:#d9534f; cursor:pointer;"><i class="fa-solid fa-times"></i></button>
                                <label style="font-size:0.85rem; color:#666;">New Extra Image</label>
                                <input type="file" name="new_extra_image[]" class="form-control" style="margin-bottom:10px;" accept="image/*" required>
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <input type="text" name="new_extra_desc[]" class="form-control" placeholder="Description or Caption for Image">
                                    <input type="text" name="new_extra_link[]" class="form-control" placeholder="Redirection URL (e.g., https://...)">
                                </div>
                            `;
                            container.appendChild(div);
                        }
                    </script>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <label style="display:inline-flex; align-items:center; gap:10px; font-weight:normal; cursor:pointer;">
                        <input type="checkbox" name="status" <?= $pageData['status'] ? 'checked' : '' ?> style="width:18px; height:18px;"> Active (Published)
                    </label>
                </div>
            </div>

            <div class="form-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3><i class="fa-solid fa-file-lines"></i> Page Content</h3>
                </div>
                <div class="form-group">
                    <div id="quill-editor" style="min-height:300px; background:white;"><?= $pageData['content'] ?></div>
                    <input type="hidden" name="content" id="content_hidden">
                </div>
            </div>

            <div class="form-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3><i class="fa-solid fa-comments"></i> Dynamic FAQ Section</h3>
                    <button type="button" onclick="addFaq()" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:1.1rem; padding:10px;" title="Add FAQ">
                        <i class="fas fa-plus-circle"></i> Add FAQ
                    </button>
                </div>
                <div id="faq-container">
                    <?php 
                    $faqs_data = !empty($pageData['faqs']) ? json_decode($pageData['faqs'], true) : [];
                    if (!empty($faqs_data)):
                        foreach ($faqs_data as $faq):
                    ?>
                        <div style="background:#fafafa; border:1px solid #eee; padding:15px; border-radius:12px; margin-bottom:15px; position:relative;">
                            <button type="button" onclick="this.parentElement.remove()" style="position:absolute; top:10px; right:15px; background:none; border:none; color:#d9534f; cursor:pointer;"><i class="fa-solid fa-times"></i></button>
                            <div class="form-group">
                                <label>Question</label>
                                <input type="text" name="faq_question[]" class="form-control" required value="<?= htmlspecialchars($faq['question'] ?? '') ?>" placeholder="Enter FAQ Question">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Answer</label>
                                <textarea name="faq_answer[]" class="form-control" rows="3" required placeholder="Enter FAQ Answer"><?= htmlspecialchars($faq['answer'] ?? '') ?></textarea>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>

            <div class="form-card">
                <h3><i class="fa-solid fa-search"></i> SEO Optimization</h3>
                <div class="form-group">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($pageData['meta_title']) ?>">
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($pageData['meta_description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($pageData['meta_keywords']) ?>">
                </div>
            </div>

            <button type="submit" class="btn-submit"><i class="fa-solid fa-save"></i> Save Changes</button>
            <div style="height: 40px;"></div>
        </form>
    </main>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var SizeStyle = Quill.import('attributors/style/size');
        SizeStyle.whitelist = ['10px', '12px', '14px', '16px', '18px', '20px', '24px', '32px'];
        Quill.register(SizeStyle, true);

        var AlignStyle = Quill.import('attributors/style/align');
        Quill.register(AlignStyle, true);

        var quill = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'size': ['10px', '12px', '14px', '16px', '18px', '20px', '24px', '32px'] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        });

        function addFaq() {
            const container = document.getElementById('faq-container');
            const div = document.createElement('div');
            div.style.cssText = 'background:#fafafa; border:1px solid #eee; padding:15px; border-radius:12px; margin-bottom:15px; position:relative;';
            div.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" style="position:absolute; top:10px; right:15px; background:none; border:none; color:#d9534f; cursor:pointer;"><i class="fa-solid fa-times"></i></button>
                <div class="form-group">
                    <label>Question</label>
                    <input type="text" name="faq_question[]" class="form-control" required placeholder="Enter FAQ Question">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Answer</label>
                    <textarea name="faq_answer[]" class="form-control" rows="3" required placeholder="Enter FAQ Answer"></textarea>
                </div>
            `;
            container.appendChild(div);
        }

        quill.on('text-change', function() {
            document.getElementById('content_hidden').value = quill.root.innerHTML;
        });
        // Set initial value perfectly
        document.getElementById('content_hidden').value = quill.root.innerHTML;
    </script>
</body>
</html>
