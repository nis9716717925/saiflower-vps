<?php
require_once __DIR__.'/auth_check.php';
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Add Gallery Image'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #d81b60;
            --bg: #f8f9fa;
            --text: #333;
            --card-bg: #ffffff;
        }

        .admin-main {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 40px;
        }

        .card {
            background: var(--card-bg);
            padding: 30px 20px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            width: 100%;
            max-width: 500px;
            box-sizing: border-box;
        }

        h2 { 
            margin: 0 0 10px; 
            color: var(--primary); 
            font-size: 1.5rem; 
            text-align: center;
        }

        p.subtitle {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            font-size: 0.85rem; 
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"], 
        textarea {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-sizing: border-box;
            font-size: 16px; /* Essential to prevent iOS zoom */
            transition: 0.3s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(216, 27, 96, 0.1);
        }

        /* Responsive Preview Box */
        .image-preview-area {
            width: 100%;
            height: 220px;
            border: 2px dashed #ddd;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fdfdfd;
            overflow: hidden;
            cursor: pointer;
            position: relative;
        }

        .image-preview-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .image-preview-area i { font-size: 2rem; color: #ccc; margin-bottom: 10px; }
        .image-preview-area span { color: #aaa; font-size: 0.8rem; }

        .section-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary);
            margin: 30px 0 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 6px 15px rgba(216, 27, 96, 0.2);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            opacity: 0.95;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            text-decoration: none;
            color: #888;
            font-size: 0.9rem;
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            .card { padding: 25px 15px; border-radius: 15px; }
            h2 { font-size: 1.3rem; }
            .image-preview-area { height: 180px; }
        }
    </style>
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">

<div class="card">
    <h2>Add Gallery Image</h2>
    <p class="subtitle">Upload a masterpiece to your portfolio.</p>
    
    <form action="actions/add_gallery.php" method="post" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <label>Title</label>
        <input type="text" name="title" placeholder="e.g. Royal Red Bouquet" required>

        <label>Tag (Category)</label>
        <input type="text" name="tag" placeholder="e.g. Wedding, Birthday" required>

        <label>Image Upload</label>
        <div class="image-preview-area" onclick="document.getElementById('imageInput').click()">
            <i class="fas fa-camera" id="placeholderIcon"></i>
            <span id="placeholderText">Tap to select photo</span>
            <img src="" id="imagePreview">
        </div>
        <input type="file" name="image" id="imageInput" accept="image/*" required style="display: none;">

        <h3 class="section-title"><i class="fas fa-rocket"></i> SEO Config</h3>
        
        <label>Meta Title</label>
        <input type="text" name="meta_title" placeholder="SEO optimized name">
        
        <label>Meta Description</label>
        <textarea name="meta_description" placeholder="Summary for Google results..." style="min-height:80px;"></textarea>
        
        <label>Meta Keywords</label>
        <input type="text" name="meta_keywords" placeholder="flowers, luxury, florist">

        <button type="submit" class="btn-submit">Publish to Gallery</button>
    </form>
    
    <a href="gallery.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

<script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const placeholderIcon = document.getElementById('placeholderIcon');
    const placeholderText = document.getElementById('placeholderText');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                placeholderIcon.style.display = 'none';
                placeholderText.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</main>
</body>
</html>