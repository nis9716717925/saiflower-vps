<?php



require_once __DIR__ . '/auth_check.php';
require_once '../config.php';

$result = mysqli_query($conn, "SELECT * FROM seo_meta ORDER BY page_identifier ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Intelligence SEO Manager'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #326e54;
            --bg: #f4f7f6;
            --sidebar-width: 260px;
        }

        /* --- THE ULTIMATE MOBILE FIT & CLEARANCE --- */
        * { box-sizing: border-box; }
        
        html, body { 
            width: 100vw; 
            max-width: 100%;
            overflow-x: hidden; 
            margin: 0; 
            padding: 0; 
            background: var(--bg);
            position: relative;
        }

        /* Desktop Layout Offset */
        .admin-main { 
            margin-left: var(--sidebar-width); 
            padding: 25px 20px; 
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width)); 
            transition: all 0.3s ease;
            display: block;
        }

        /* Google Preview Box */
        .google-preview { 
            background: #fff; padding: 15px; border-radius: 12px; 
            border: 1px solid #dfe1e5; font-family: arial, sans-serif; 
            margin-bottom: 20px; width: 100%; box-sizing: border-box;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .gp-url { color: #202124; font-size: 13px; margin-bottom: 4px; display: block; overflow: hidden; text-overflow: ellipsis; }
        .gp-title { color: #1a0dab; font-size: 18px; line-height: 1.3; display: block; margin-bottom: 3px; font-weight: 400; }
        .gp-desc { color: #4d5156; font-size: 13px; line-height: 1.5; display: block; }
        
        /* Badges */
        .count-badge { font-size: 0.65rem; font-weight: 800; float: right; padding: 2px 8px; border-radius: 50px; text-transform: uppercase; }
        .count-good { color: #27ae60; background: #e9f7ef; }
        .count-bad { color: #e74c3c; background: #fdedec; }
        .status-pill { padding: 4px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }

        /* Modal Stucture */
        #seoModal { 
            display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); 
            z-index:10000; align-items:center; justify-content:center; 
            backdrop-filter: blur(8px); padding: 15px;
        }
        .modal-card {
            background:white; padding:25px; border-radius:25px; width:100%; 
            max-width:700px; position:relative; max-height: 90vh; 
            overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        .form-input { 
            width: 100%; padding: 14px; margin-bottom: 15px; border: 1px solid #ddd; 
            border-radius: 12px; font-size: 16px; box-sizing: border-box; background: #fafafa;
        }

        /* --- MOBILE OVERRIDES --- */
        @media (max-width: 992px) {
            .admin-main { 
                margin-left: 0 !important; 
                margin-bottom: 100px !important; /* Bottom Nav Clearance */
                padding: 15px; 
                width: 100% !important;
                max-width: 100vw !important;
            }

            .admin-header { flex-direction: column; align-items: stretch; gap: 10px; }
            .admin-header .btn { width: 100%; justify-content: center; padding: 14px; }
            
            .admin-table thead { display: none; }
            .admin-table tr { 
                display: block; background: #fff; border-radius: 15px; 
                margin-bottom: 15px; padding: 15px; border: 1px solid #eee;
            }
            .admin-table td { 
                display: flex; justify-content: space-between; align-items: center; 
                padding: 10px 0; border: none; font-size: 0.9rem; width: 100% !important;
            }
            .admin-table td::before { 
                content: attr(data-label); font-weight: 700; color: #bbb; 
                font-size: 0.65rem; text-transform: uppercase;
            }

            /* Fix Modal Layout on Phones */
            .modal-grid { grid-template-columns: 1fr !important; gap: 0 !important; }
        }
    </style>
</head>
<body class="admin-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-header">
            <div>
                <h2 style="margin:0;"><i class="fas fa-search-dollar" style="color:var(--primary);"></i> SEO Manager</h2>
                <p style="color:#888; font-size: 0.8rem; margin-top: 5px;">Manage meta tags and Google Search appearance.</p>
            </div>
            <button onclick="openModal()" class="btn"><i class="fas fa-plus"></i> New Page</button>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div style="padding:15px; background:#dcfce7; color:#166534; border-radius:12px; margin-bottom:20px; font-size: 0.85rem; border:1px solid #bbf7d0;">
                <i class="fas fa-check-circle"></i> Strategy Synchronized: SEO tags updated.
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Page Target</th>
                        <th>Health Status</th>
                        <th>Description Snippet</th>
                        <th width="100" style="text-align:right;">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $titleLen = strlen($row['title']);
                        $descLen = strlen($row['description']);
                    ?>
                    <tr>
                        <td data-label="Page">
                            <div style="font-weight:800; color:var(--primary);"><?= htmlspecialchars($row['page_identifier']) ?></div>
                            <div style="font-size:0.7rem; color:#999;"><?= htmlspecialchars(mb_strimwidth($row['title'], 0, 40, "...")) ?></div>
                        </td>
                        <td data-label="Health">
                            <span class="status-pill <?= ($titleLen > 10 && $descLen > 50) ? 'count-good' : 'count-bad' ?>">
                                <?= ($titleLen > 10 && $descLen > 50) ? 'Optimized' : 'Needs Work' ?>
                            </span>
                        </td>
                        <td data-label="Snippet">
                            <div style="font-size:0.8rem; color:#666; max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?= htmlspecialchars($row['description']) ?>
                            </div>
                        </td>
                        <td data-label="Action" style="text-align:right;">
                            <button onclick="editSEO(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)" class="btn" style="padding:8px 15px; font-size:0.75rem; background:#f0f7f4; color:var(--primary); border:none; border-radius:10px; font-weight:700;">
                                <i class="fas fa-cog"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="seoModal">
        <div class="modal-card">
            <span onclick="closeModal()" style="position:absolute; top:20px; right:25px; cursor:pointer; font-size:1.8rem; color:#ccc;">&times;</span>
            
            <h3 style="margin:0 0 20px 0; color:var(--primary); font-family: var(--font-main);">SEO Page Strategy</h3>

            <label style="font-size: 0.65rem; font-weight: 800; color: #bbb; text-transform: uppercase; letter-spacing: 1.5px; display: block; margin-bottom: 10px;">Live Google Preview</label>
            <div class="google-preview">
                <span class="gp-url">https://saiflowers.com/ <span id="prev-url-slug" style="color: #5f6368;">page</span></span>
                <span class="gp-title" id="prev-title">Enter a title...</span>
                <span class="gp-desc" id="prev-desc">Provide a meta description to see how your site will appear in search results...</span>
            </div>
            
            <form action="actions/save_seo.php" method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" id="seo_id">
                
                <div class="modal-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-size:0.7rem; font-weight:800; color:#999;">PAGE FILENAME</label>
                        <input type="text" name="page_identifier" id="page_identifier" required class="form-input" oninput="updatePreview()" placeholder="e.g. index.php">
                    </div>
                    <div class="form-group">
                        <label style="font-size:0.7rem; font-weight:800; color:#999;">KEYWORDS</label>
                        <input type="text" name="keywords" id="keywords" class="form-input" placeholder="roses, florist delhi, etc">
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-size:0.7rem; font-weight:800; color:#999; display: flex; justify-content: space-between;">
                        SEO TITLE <span id="title-count" class="count-badge">0/60</span>
                    </label>
                    <input type="text" name="title" id="title" required class="form-input" oninput="updatePreview()" maxlength="70">
                </div>

                <div class="form-group">
                    <label style="font-size:0.7rem; font-weight:800; color:#999; display: flex; justify-content: space-between;">
                        META DESCRIPTION <span id="desc-count" class="count-badge">0/160</span>
                    </label>
                    <textarea name="description" id="description" rows="4" required class="form-input" oninput="updatePreview()" maxlength="200"></textarea>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn" style="width:100%; padding:16px; border-radius:15px; font-weight:800;"><i class="fas fa-save"></i> Save SEO Settings</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updatePreview() {
            const title = document.getElementById('title').value;
            const desc = document.getElementById('description').value;
            const slug = document.getElementById('page_identifier').value;

            document.getElementById('prev-title').innerText = title || 'Enter a title...';
            document.getElementById('prev-desc').innerText = desc || 'Enter a description...';
            document.getElementById('prev-url-slug').innerText = slug;

            const tCount = title.length;
            const dCount = desc.length;

            const tBadge = document.getElementById('title-count');
            tBadge.innerText = `${tCount}/60`;
            tBadge.className = `count-badge ${tCount > 60 ? 'count-bad' : 'count-good'}`;

            const dBadge = document.getElementById('desc-count');
            dBadge.innerText = `${dCount}/160`;
            dBadge.className = `count-badge ${dCount > 160 ? 'count-bad' : 'count-good'}`;
        }

        function openModal() {
            document.getElementById('seo_id').value = '';
            document.getElementById('page_identifier').value = '';
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('keywords').value = '';
            document.getElementById('page_identifier').readOnly = false;
            updatePreview();
            document.getElementById('seoModal').style.display='flex';
        }

        function editSEO(data) {
            document.getElementById('seo_id').value = data.id;
            document.getElementById('page_identifier').value = data.page_identifier;
            document.getElementById('title').value = data.title;
            document.getElementById('description').value = data.description;
            document.getElementById('keywords').value = data.keywords;
            document.getElementById('page_identifier').readOnly = true;
            updatePreview();
            document.getElementById('seoModal').style.display='flex';
        }

        function closeModal() {
            document.getElementById('seoModal').style.display='none';
        }

        // Close modal if clicking background
        window.onclick = function(event) {
            let modal = document.getElementById('seoModal');
            if (event.target == modal) closeModal();
        }
    </script>
</body>
</html>