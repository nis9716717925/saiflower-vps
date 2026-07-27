<?php
require_once __DIR__.'/auth_check.php';
require_once '../config.php';

$id = intval($_GET['id'] ?? 0);
$res = mysqli_query($conn, "SELECT * FROM gallery WHERE id=$id");
$g = mysqli_fetch_assoc($res);

if (!$g) {
    die("Gallery item not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Edit Gallery'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #326e54;
            --accent: #d4af37;
            --bg: #f4f7f6;
            --text: #333;
        }

        .admin-main {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .card { 
            background: white; 
            padding: 30px 20px; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 550px; 
            height: fit-content;
        }

        h2 { 
            margin-top: 0; 
            color: var(--primary); 
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 700; 
            font-size: 0.85rem; 
            color: #444;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"], 
        textarea, 
        input[type="file"] { 
            width: 100%; 
            padding: 14px; 
            margin-bottom: 20px; 
            border: 1px solid #ddd; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-size: 16px; /* Fixes iOS zoom */
            transition: 0.3s;
            background: #fff;
            font-family: inherit;
        }

        input:focus, textarea:focus { 
            outline: none; 
            border-color: var(--primary); 
            box-shadow: 0 0 0 3px rgba(50, 110, 84, 0.1); 
        }

        /* Image Preview Section */
        .image-preview-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .preview-box {
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 10px;
            text-align: center;
            position: relative;
        }

        .preview-box img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }

        .preview-label {
            font-size: 0.65rem;
            color: #999;
            margin-top: 8px;
            display: block;
            font-weight: 700;
            text-transform: uppercase;
        }

        .upload-placeholder {
            width: 100%;
            height: 110px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ccc;
            cursor: pointer;
            transition: 0.3s;
        }

        .section-title { 
            font-size: 1rem;
            color: var(--primary); 
            border-bottom: 1px solid #eee; 
            padding-bottom: 8px; 
            margin: 35px 0 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit { 
            background: var(--primary); 
            color: white; 
            padding: 16px; 
            border: none; 
            width: 100%; 
            border-radius: 50px; 
            cursor: pointer; 
            font-size: 1rem; 
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(50, 110, 84, 0.2);
            margin-top: 10px;
        }

        .btn-submit:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(50, 110, 84, 0.3);
        }

        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            color: #888; 
            text-decoration: none; 
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            /* body { padding: 20px 10px; } */
            .image-preview-container { grid-template-columns: 1fr; }
            h2 { font-size: 1.3rem; }
        }
    </style>
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">

<div class="card">
    <h2><i class="fas fa-images"></i> Edit Gallery</h2>
    <p style="color:#888; font-size: 0.9rem; margin-bottom: 30px;">Update arrangement details and SEO.</p>

    <form action="actions/update_gallery.php" method="post" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?= $g['id']; ?>">

        <label>Image Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($g['title']); ?>" required>

        <label>Tag (e.g. Wedding, Birthday)</label>
        <input type="text" name="tag" value="<?= htmlspecialchars($g['tag']); ?>" required>

        <label>Arrangement Photo</label>
        <div class="image-preview-container">
            <div class="preview-box">
                <img src="/uploads/<?= $g['image']; ?>" alt="Current">
                <span class="preview-label">Current Photo</span>
            </div>
            
            <div class="preview-box" onclick="document.getElementById('imageInput').click()" style="cursor:pointer;">
                <div id="uploadUI" class="upload-placeholder">
                    <i class="fas fa-camera" style="font-size: 1.2rem;"></i>
                    <span style="font-size: 0.6rem; margin-top: 5px;">CHANGE</span>
                </div>
                <img src="" id="newPreview" style="display:none;">
                <span class="preview-label" id="newLabel" style="display:none;">New Selection</span>
            </div>
        </div>
        <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">

        <h3 class="section-title"><i class="fas fa-rocket"></i> SEO Configuration</h3>

        <label>Meta Title</label>
        <input type="text" name="meta_title" value="<?= htmlspecialchars($g['meta_title'] ?? ''); ?>" placeholder="Google Search Title">
        
        <label>Meta Description</label>
        <textarea name="meta_description" placeholder="Short arrangement description..." style="min-height:80px;"><?= htmlspecialchars($g['meta_description'] ?? ''); ?></textarea>
        
        <label>Meta Keywords</label>
        <input type="text" name="meta_keywords" value="<?= htmlspecialchars($g['meta_keywords'] ?? ''); ?>" placeholder="flowers, florist, delhi">

        <button type="submit" class="btn-submit">Update Gallery Item</button>
    </form>

    <a href="gallery.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Gallery List</a>
</div>

<script>
    // Logic for New Image Preview
    document.getElementById('imageInput').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('newPreview');
                const ui = document.getElementById('uploadUI');
                const label = document.getElementById('newLabel');
                
                img.src = e.target.result;
                img.style.display = 'block';
                ui.style.display = 'none';
                label.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</main>
</body>
</html>