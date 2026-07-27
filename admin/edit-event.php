<?php
require_once __DIR__.'/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';

// Auto-migration for faqs column
$checkFaqs = $conn->query("SHOW COLUMNS FROM events LIKE 'faqs'");
if($checkFaqs && $checkFaqs->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE events ADD COLUMN faqs TEXT DEFAULT NULL");
    } catch(Exception $e) { error_log("FAQ Migration failed: " . $e->getMessage()); }
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('Invalid ID');

$res = mysqli_query($conn, "SELECT * FROM events WHERE id=$id");
$event = mysqli_fetch_assoc($res);

if (!$event) {
    die('Event not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Edit Event'; include 'partials/head.php'; ?>
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
            max-width: 600px; 
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
            font-size: 16px; /* Essential for mobile zoom fix */
            transition: 0.3s;
            background: #fff;
            font-family: inherit;
        }

        input:focus, textarea:focus { 
            outline: none; 
            border-color: var(--primary); 
            box-shadow: 0 0 0 3px rgba(50, 110, 84, 0.1); 
        }

        /* Image Section Grid */
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
        }

        .preview-box img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }

        .preview-label {
            font-size: 0.7rem;
            color: #999;
            margin-top: 8px;
            display: block;
            text-transform: uppercase;
            font-weight: 700;
        }

        .upload-placeholder {
            width: 100%;
            height: 120px;
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

        .upload-placeholder:hover { border-color: var(--primary); color: var(--primary); }

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
    <h2><i class="fas fa-calendar-check"></i> Edit Event</h2>
    <p style="color:#888; font-size: 0.9rem; margin-bottom: 30px;">Update your event portfolio details.</p>

    <form action="actions/update_event.php" method="post" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?= $event['id']; ?>">

        <label>Event Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($event['title']); ?>" required>

        <label>Tag (e.g. Wedding, Corporate)</label>
        <input type="text" name="tag" value="<?= htmlspecialchars($event['tag']); ?>" required>

        <label>Description</label>
        <textarea name="description" required style="min-height: 120px;"><?= htmlspecialchars($event['description']); ?></textarea>

        <label>Cover Image</label>
        <div class="image-preview-container">
            <div class="preview-box">
                <img src="/uploads/<?= $event['cover_image']; ?>" alt="Current">
                <span class="preview-label">Current Image</span>
            </div>
            
            <div class="preview-box" onclick="document.getElementById('imageInput').click()" style="cursor:pointer;">
                <div id="uploadUI" class="upload-placeholder">
                    <i class="fas fa-camera" style="font-size: 1.5rem;"></i>
                    <span style="font-size: 0.65rem; margin-top: 5px;">TAP TO REPLACE</span>
                </div>
                <img src="" id="newPreview" style="display:none;">
                <span class="preview-label" id="newLabel" style="display:none;">New Selection</span>
            </div>
        </div>
        <input type="file" name="cover_image" id="imageInput" accept="image/*" style="display:none;">

        <h3 class="section-title">Frequently Asked Questions (FAQs) ❓</h3>
        <p style="font-size: 0.85rem; color: #666; margin-top:-15px; margin-bottom: 15px;">Manage common questions and answers for this event.</p>
        <div id="faqContainer" style="margin-bottom: 20px;">
            <!-- FAQ Rows will be injected here via JS -->
        </div>
        <button type="button" onclick="addFaqRow()" style="background:#e9f1ee; color:var(--primary); border:1px dashed var(--primary); padding:10px 15px; border-radius:10px; cursor:pointer; font-weight:bold; width:100%; margin-bottom: 20px;"><i class="fas fa-plus"></i> Add FAQ</button>

        <h3 class="section-title">SEO Configuration 🚀</h3>

        <label>Meta Title</label>
        <input type="text" name="meta_title" value="<?= htmlspecialchars($event['meta_title'] ?? ''); ?>" placeholder="SEO Title">
        
        <label>Meta Description</label>
        <textarea name="meta_description" placeholder="Google search summary..." style="min-height:80px;"><?= htmlspecialchars($event['meta_description'] ?? ''); ?></textarea>
        
        <label>Meta Keywords</label>
        <input type="text" name="meta_keywords" value="<?= htmlspecialchars($event['meta_keywords'] ?? ''); ?>" placeholder="wedding, flowers, delhi">

        <button type="submit" class="btn-submit">Update Event</button>
    </form>

    <a href="events.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Events List</a>
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
    if (!empty($event['faqs'])) {
        $decoded = json_decode($event['faqs'], true);
        if (is_array($decoded)) $existingFaqs = $decoded;
    }
    ?>
    const existingFaqs = <?= json_encode($existingFaqs, JSON_UNESCAPED_UNICODE) ?>;
    if (existingFaqs.length > 0) {
        existingFaqs.forEach(faq => addFaqRow(faq.question, faq.answer));
    }
</script>

</main>
</body>
</html>