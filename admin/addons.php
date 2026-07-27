<?php
// actions/addons.php



require_once __DIR__ . '/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    csrf_verify_or_die();
    $del_id = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM addons WHERE id=$del_id");
    header("Location: addons.php?msg=deleted");
    exit;
}

// Fetch Addons
$query = "SELECT * FROM addons ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Add-ons'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #326e54;
            --bg: #f4f7f6;
            --sidebar-width: 260px;
        }

        /* --- GLOBAL FIT FIXES --- */
        * { box-sizing: border-box; }
        
        html, body { 
            width: 100%;
            overflow-x: hidden; 
            margin: 0; 
            padding: 0;
            font-family: 'Inter', sans-serif; 
            background: var(--bg);
        }

        /* Desktop Layout: Push content to the right of fixed sidebar */
        .admin-main { 
            margin-left: var(--sidebar-width); 
            padding: 25px 20px; 
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width)); 
            transition: all 0.3s ease;
            display: block;
        }

        /* HEADER BOX */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 10px;
        }

        /* QUICK ADD FORM */
        #addForm {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            display: none;
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 120px;
            gap: 12px;
            align-items: flex-end;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 6px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }

        /* TABLE WRAPPER */
        .table-wrapper {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            width: 100%;
            border: 1px solid #eee;
        }

        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #fafafa; padding: 15px; text-align: left; font-size: 0.75rem; color: #999; border-bottom: 1px solid #eee; text-transform: uppercase; }
        .admin-table td { padding: 15px; border-bottom: 1px solid #f9f9f9; }

        .badge { padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-success { background: #e9f7ef; color: #27ae60; }
        .badge-warning { background: #fef5e7; color: #f39c12; }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px; height: 35px;
            border-radius: 8px;
            border: 1px solid #eee;
            color: #555;
            text-decoration: none;
            background: #fff;
            transition: 0.2s;
        }
        .btn-action:hover { background: #f8f8f8; border-color: #ddd; }

        /* --- MOBILE OPTIMIZATIONS --- */
        @media (max-width: 992px) {
            .admin-main { 
                margin-left: 0 !important; 
                margin-bottom: 90px !important; /* Bottom nav clearance */
                padding: 15px; 
                width: 100% !important;
            }

            .admin-header { flex-wrap: wrap; }
            .admin-header h2 { font-size: 1.3rem; }
            
            .form-row { 
                grid-template-columns: 1fr; 
                width: 100%;
            }

            .admin-table thead { display: none; }
            
            .admin-table tr { 
                display: block; 
                padding: 15px; 
                border-bottom: 8px solid #f4f7f6;
                width: 100%;
                background: #fff;
            }

            .admin-table td { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                padding: 10px 0; 
                width: 100%;
                font-size: 0.9rem;
                text-align: right;
            }

            .admin-table td::before { 
                content: attr(data-label); 
                font-weight: 700; 
                color: #bbb; 
                font-size: 0.7rem;
                text-transform: uppercase;
                text-align: left;
            }

            /* Mobile card "header" */
            .admin-table td:first-child { 
                background: #fcfdfc;
                margin: -15px -15px 10px -15px;
                padding: 15px;
                justify-content: center;
                border-bottom: 1px solid #eee;
            }
            .admin-table td:first-child::before { display: none; }
        }
    </style>
</head>
<body>

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-header">
            <div>
                <h2 style="margin:0;"><i class="fas fa-gift"></i> Add-ons</h2>
                <p style="color:#888; font-size: 0.8rem; margin: 2px 0 0;">Upsell items management</p>
            </div>
            <button onclick="toggleAddForm()" class="btn" style="padding: 10px 15px; font-size: 0.85rem;">
                <i class="fas fa-plus"></i> New
            </button>
        </div>

        <div id="addForm">
            <form action="actions/save_addon.php" method="POST" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" id="addon_id" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="addon_name" required placeholder="Item Name">
                    </div>
                    <div class="form-group">
                        <label>MRP (₹)</label>
                        <input type="number" name="original_price" id="addon_orig" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Sale (₹)</label>
                        <input type="number" name="price" id="addon_price" required step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Image (Optional)</label>
                        <input type="file" name="addon_image" id="addon_image" accept="image/*" style="padding: 9px;">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn" style="width:100%; padding:12px;">Save</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="60">Item</th>
                        <th>Details</th>
                        <th>Pricing</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td data-label="Image">
                                <?php 
                                    $iconImg = $row['icon'];
                                    if(empty($iconImg) || strpos($iconImg, 'fa-') === 0) $iconImg = '/favicon.png';
                                    elseif(strpos($iconImg, 'uploads/') === 0) $iconImg = '/' . $iconImg; 
                                ?>
                                <div style="width:40px; height:40px; border-radius:10px; overflow:hidden; border:1px solid #eee; background:#fff;">
                                    <img src="<?= htmlspecialchars($iconImg) ?>" style="width:100%; height:100%; object-fit:contain; padding:2px;">
                                </div>
                            </td>
                            <td data-label="Name">
                                <strong><?= htmlspecialchars($row['name']) ?></strong>
                            </td>
                            <td data-label="Price">
                                <b>₹<?= number_format($row['price'], 2) ?></b>
                            </td>
                            <td data-label="Status">
                                <span class="badge <?= $row['status'] ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $row['status'] ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td data-label="Manage">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    <a href="actions/save_addon.php?toggle_id=<?= $row['id'] ?>&status=<?= $row['status'] ? 0 : 1 ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn-action">
                                        <i class="fas <?= $row['status'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                    </a>
                                    <a href="addons.php?delete_id=<?= $row['id'] ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn-action" style="color:#e74c3c;" onclick="return confirm('Delete?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function toggleAddForm() {
            const el = document.getElementById('addForm');
            el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</body>
</html>