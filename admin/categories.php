<?php
// 1. Correct File Paths
$root = $_SERVER['DOCUMENT_ROOT'];
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (file_exists(__DIR__ . '/auth_check.php')) {
    require_once __DIR__ . '/auth_check.php';
} elseif (file_exists($root . '/admin/auth_check.php')) {
    require_once $root . '/admin/auth_check.php';
}

require_once $root . '/config.php';

// Fetch Categories
$categories_res = mysqli_query($conn, "SELECT * FROM categories ORDER BY sort_order ASC, name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Categories'; include 'partials/head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #326e54;
            --accent: #d4af37;
            --bg: #f4f7f6;
        }
        .admin-main { padding: 30px; background: var(--bg); min-height: 100vh; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-title h2 { margin: 0; color: #333; font-size: 1.8rem; }
        .header-title p { margin: 5px 0 0; color: #666; font-size: 0.9rem; }
        
        .btn-add {
            background: var(--primary); color: #fff; padding: 12px 24px; border-radius: 12px;
            text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;
            transition: 0.3s; border: 1px solid var(--primary); cursor: pointer;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(50, 110, 84, 0.2); }

        .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .category-card {
            background: #fff; border-radius: 20px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; gap: 15px; border: 1px solid #eee; transition: 0.3s;
        }
        .category-card:hover { border-color: var(--primary); transform: translateY(-3px); }
        .cat-info { display: flex; align-items: center; gap: 15px; }
        .cat-img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; background: #f9f9f9; border: 1px solid #eee; }
        .cat-details h3 { margin: 0; font-size: 1.1rem; color: #333; }
        .cat-details p { margin: 2px 0 0; font-size: 0.8rem; color: #999; }
        
        .status-badge {
            padding: 4px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .status-active { background: #e6f4ea; color: #1e7e34; }
        .status-inactive { background: #fce8e8; color: #c62828; }

        .card-actions { display: flex; gap: 10px; margin-top: auto; padding-top: 15px; border-top: 1px solid #f5f5f5; }
        .btn-edit { color: var(--primary); border: 1px solid #eee; padding: 8px; border-radius: 8px; flex: 1; text-align: center; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s; }
        .btn-edit:hover { background: #f0f7f4; border-color: var(--primary); }
        .btn-delete { color: #e53935; border: 1px solid #eee; padding: 8px; border-radius: 8px; flex: 1; text-align: center; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-delete:hover { background: #fff5f5; border-color: #e53935; }

        /* Modal Styles */
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal.show { display: flex; }
        .modal-content { background: #fff; width: 90%; max-width: 500px; border-radius: 24px; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .modal-header h3 { margin: 0; font-size: 1.4rem; color: #333; }
        .close-modal { cursor: pointer; font-size: 1.5rem; color: #999; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: #555; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border-radius: 12px; border: 1.5px solid #eee; outline: none; transition: 0.2s; font-size: 0.95rem; }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(50, 110, 84, 0.1); }
        
        .btn-submit { width: 100%; background: var(--primary); color: #fff; padding: 14px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
    </style>
</head>
<body>
    <?php include 'partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                <h2><i class="fa-solid fa-tags" style="color:var(--primary);"></i> Category Management</h2>
                <p>View, edit, and organize flower categories.</p>
            </div>
            <button class="btn-add" onclick="openModal('add')">
                <i class="fa-solid fa-plus"></i> New Category
            </button>
        </div>

        <div class="category-grid">
            <?php while($cat = mysqli_fetch_assoc($categories_res)): 
                $img = !empty($cat['image']) ? '/uploads/categories/'.$cat['image'] : '';
            ?>
            <div class="category-card">
                <div class="cat-info">
                    <?php if($img): ?>
                        <img src="<?= $img ?>" class="cat-img">
                    <?php else: ?>
                        <div class="cat-img flex items-center justify-center bg-slate-50 text-slate-300">
                             <i class="fa-solid fa-image text-xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="cat-details">
                        <h3><?= htmlspecialchars($cat['name']) ?></h3>
                        <div style="display:flex; gap:8px; align-items:center; margin-top:5px;">
                            <span class="status-badge <?= $cat['status'] ? 'status-active' : 'status-inactive' ?>">
                                <i class="fa-solid <?= $cat['status'] ? 'fa-check' : 'fa-xmark' ?>"></i>
                                <?= $cat['status'] ? 'Active' : 'Inactive' ?>
                            </span>
                            <span style="font-size:0.75rem; color:#999; font-weight:600;">SN: <?= $cat['sort_order'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="btn-edit" onclick='openModal("edit", <?= json_encode($cat) ?>)'>
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </button>
                    <button class="btn-delete" onclick="deleteCategory(<?= $cat['id'] ?>)">
                        <i class="fa-solid fa-trash-can"></i> Delete
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Category</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <form id="categoryForm" action="actions/category_actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="catId">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" id="catName" required placeholder="e.g. Birthday Bouquets">
                </div>
                
                <div class="form-group">
                    <label>Thumbnail Image</label>
                    <div id="currentImageContainer" style="margin-bottom: 10px; display: none;">
                        <p style="font-size: 0.7rem; color: #999; margin-bottom: 5px;">Current Image:</p>
                        <img id="currentImagePreview" src="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 12px; border: 1px solid #eee;">
                    </div>
                    <input type="file" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Sort Order (SN)</label>
                    <input type="number" name="sort_order" id="catSort" value="0" min="0">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="catStatus">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-submit">Save Category</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(mode, data = null) {
            const modal = document.getElementById('categoryModal');
            const title = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            
            if(mode === 'edit') {
                title.innerText = 'Edit Category';
                formAction.value = 'edit';
                document.getElementById('catId').value = data.id;
                document.getElementById('catName').value = data.name;
                document.getElementById('catSort').value = data.sort_order || 0;
                document.getElementById('catStatus').value = data.status;
                
                const imgPreview = document.getElementById('currentImagePreview');
                const imgContainer = document.getElementById('currentImageContainer');
                if(data.image) {
                    imgPreview.src = '/uploads/categories/' + data.image;
                    imgContainer.style.display = 'block';
                } else {
                    imgContainer.style.display = 'none';
                }
            } else {
                title.innerText = 'Add Category';
                formAction.value = 'add';
                document.getElementById('categoryForm').reset();
                document.getElementById('currentImageContainer').style.display = 'none';
            }
            modal.classList.add('show');
        }

        function closeModal() {
            document.getElementById('categoryModal').classList.remove('show');
        }

        function deleteCategory(id) {
            if(confirm('Are you sure you want to delete this category? This might affect products assigned to it.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'actions/category_actions.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?= generate_csrf_token() ?>';
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                form.appendChild(csrfInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) closeModal();
        }
    </script>
</body>
</html>
