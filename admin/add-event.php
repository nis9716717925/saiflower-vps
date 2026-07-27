<?php
require_once __DIR__.'/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Add Event'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #326e54;
            --bg: #f8faf9;
            --text: #333;
        }

        .admin-main {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 40px;
        }

        .card { 
            background: white; 
            padding: 30px 20px; 
            border-radius: 15px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 600px; 
            margin: 0 auto;
            box-sizing: border-box;
        }

        h2 { 
            font-size: 1.5rem;
            color: var(--primary); 
            margin-bottom: 10px;
        }

        /* Responsive spacing */
        label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 700; 
            font-size: 0.85rem; 
            color: #444;
        }

        input[type="text"], 
        textarea, 
        input[type="file"] { 
            width: 100%; 
            padding: 14px; /* Larger tap area for mobile */
            margin-bottom: 18px; 
            border: 1px solid #ddd; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-size: 16px; /* Prevents auto-zoom on iPhone */
            background: #fff;
        }

        /* Mobile Image Preview */
        .image-preview-wrapper {
            width: 100%;
            height: 180px;
            border: 2px dashed #ccc;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fdfdfd;
        }

        .image-preview-wrapper img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 8px;
            display: none;
        }

        .section-title { 
            font-size: 1rem;
            color: var(--primary); 
            border-bottom: 1px solid #eee; 
            padding-bottom: 8px; 
            margin: 30px 0 15px;
        }

        .btn-submit { 
            background: var(--primary); 
            color: white; 
            padding: 16px; 
            border: none; 
            width: 100%; 
            border-radius: 50px; /* Rounded buttons are better for mobile */
            cursor: pointer; 
            font-size: 1rem; 
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(50, 110, 84, 0.2);
            -webkit-tap-highlight-color: transparent;
        }

        /* Mobile specific tweaks */
        @media (max-width: 480px) {
            h2 { font-size: 1.3rem; }
            .card { padding: 25px 15px; border-radius: 12px; }
            .btn-submit { font-size: 0.95rem; }
            .section-title { font-size: 0.9rem; }
        }
    </style>
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">

<div class="card">
    <h2><i class="fas fa-plus-circle"></i> New Event</h2>
    <p style="font-size: 0.9rem; color: #666; margin-bottom: 25px;">Upload your latest masterpiece.</p>

    <form action="actions/add_event.php" method="post" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        
        <label>Event Name</label>
        <input type="text" name="title" placeholder="Enter title" required>

        <label>Category / Tag</label>
        <input type="text" name="tag" placeholder="Wedding, Party, etc." required>

        <label>Description</label>
        <textarea name="description" placeholder="Write about the flowers used..." required style="min-height:100px;"></textarea>

        <label>Cover Photo</label>
        <div class="image-preview-wrapper" id="previewBox">
            <i class="fas fa-camera" id="placeholderIcon" style="color: #bbb; font-size: 1.5rem;"></i>
            <img src="" id="imagePreview">
        </div>
        <input type="file" name="cover_image" id="imageInput" accept="image/*" required>

        <h3 class="section-title">Frequently Asked Questions (FAQs) ❓</h3>
        <p style="font-size: 0.85rem; color: #666; margin-top:-15px; margin-bottom: 15px;">Add common questions and answers for this event.</p>
        <div id="faqContainer" style="margin-bottom: 20px;">
            <!-- FAQ Rows will be injected here -->
        </div>
        <button type="button" onclick="addFaqRow()" style="background:#e9f1ee; color:var(--primary); border:1px dashed var(--primary); padding:10px 15px; border-radius:10px; cursor:pointer; font-weight:bold; width:100%; margin-bottom: 20px;"><i class="fas fa-plus"></i> Add FAQ</button>

        <div style="background: #fdfdfd; padding: 15px; border-radius: 12px; border: 1px solid #eee;">
            <h3 class="section-title" style="margin-top: 0;">SEO (Optional)</h3>
            
            <label>SEO Title</label>
            <input type="text" name="meta_title" placeholder="Meta Title">
            
            <label>SEO Description</label>
            <textarea name="meta_description" placeholder="Short description for Google" style="min-height:60px; margin-bottom: 0;"></textarea>
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" class="btn-submit">Publish Event</button>
        </div>
    </form>

    <a href="/admin/events" style="display: block; text-align: center; margin-top: 20px; color: #999; text-decoration: none; font-size: 0.85rem;">
        <i class="fas fa-arrow-left"></i> Cancel and Go Back
    </a>
</div>

<script>
    // Responsive Image Preview
    document.getElementById('imageInput').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imagePreview');
                img.src = e.target.result;
                img.style.display = 'block';
                document.getElementById('placeholderIcon').style.display = 'none';
            }
            reader.readAsDataURL(file);
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
        
        const qInput = document.createElement('input');
        qInput.type = 'text';
        qInput.name = 'faq_question[]';
        qInput.placeholder = 'Question (e.g., Do you provide decor services for weddings?)';
        qInput.required = true;
        qInput.style.marginBottom = '0';
        qInput.value = q;
        
        const aInput = document.createElement('textarea');
        aInput.name = 'faq_answer[]';
        aInput.placeholder = 'Answer (e.g., Yes, we offer complete decor services.)';
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
</script>

</main>
</body>
</html>