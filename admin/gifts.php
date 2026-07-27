<?php
// 1. Error Reporting (Keep on for debugging)

ini_set('display_startup_errors', 1);


// 2. Correct File Paths
$root = $_SERVER['DOCUMENT_ROOT'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (file_exists(__DIR__ . '/auth_check.php')) {
    require_once __DIR__ . '/auth_check.php';
} elseif (file_exists($root . '/admin/auth_check.php')) {
    require_once $root . '/admin/auth_check.php';
}

require_once $root . '/config.php';

// 3. Database Check
if (!isset($conn)) {
    die("Error: Database connection not found.");
}

// 4. FETCH GIFTS
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'gifts'");
if(mysqli_num_rows($tableCheck) == 0) {
    // Attempt to create table on the fly if missing
    $sql = "CREATE TABLE gifts LIKE flowers";
    if(mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>Table 'gifts' created successfully. Refreshing...</div>";
        echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
        exit;
    } else {
        die("Error: Table 'gifts' missing and could not be created: " . mysqli_error($conn));
    }
}
$query = "SELECT id, name, slug, price, original_price, image, status, in_stock, created_at FROM gifts ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Gifts'; include 'partials/head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #326e54;
            --accent: #d4af37;
            --bg: #f4f7f6;
            --sidebar-width: 260px;
        }

        /* --- LAYOUT FIXES --- */
        * { box-sizing: border-box; }
        
        html, body { 
            width: 100vw; 
            max-width: 100%;
            overflow-x: hidden; 
            margin: 0; 
            padding: 0; 
            background: var(--bg);
            font-family: 'Inter', -apple-system, sans-serif;
            position: relative;
        }

        /* Desktop Layout Fix */
        .admin-main { 
            margin-left: var(--sidebar-width); 
            padding: 30px; 
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width)); 
            display: block;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .header-title h2 { margin: 0; color: #1a1a1a; font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .header-title p { margin: 5px 0 0; color: #888; font-size: 0.85rem; }

        .btn-add {
            background: var(--primary);
            color: white !important;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(50, 110, 84, 0.2);
            transition: 0.3s;
        }
        .btn-add:hover { transform: translateY(-2px); opacity: 0.9; }

        .table-wrapper {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            width: 100%;
            border: 1px solid #eee;
        }

        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { 
            background: #fafafa; 
            padding: 18px 20px; 
            text-align: left; 
            font-size: 0.75rem; 
            color: #999; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            border-bottom: 1px solid #eee; 
        }
        .admin-table td { padding: 18px 20px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }

        .prod-img { 
            width: 55px; 
            height: 55px; 
            object-fit: cover; 
            border-radius: 12px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
            background: #f0f0f0;
            display: block;
        }

        .badge { 
            padding: 6px 14px; 
            border-radius: 50px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            display: inline-flex; 
            align-items: center; 
            gap: 5px;
            text-transform: uppercase; 
            text-decoration: none;
            border: none;
            transition: 0.2s;
        }
        .badge-success { background: #e9f7ef; color: #27ae60; }
        .badge-danger { background: #fdedec; color: #e74c3c; }
        .badge-warning { background: #fff4e5; color: #f39c12; }
        
        .badge-clickable:hover { 
            transform: scale(1.05);
            filter: brightness(0.95);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #eee;
            color: #555;
            text-decoration: none;
            transition: 0.3s;
            background: #fff;
        }
        .btn-action:hover { background: var(--primary); color: #fff; border-color: var(--primary); transform: translateY(-2px); }
        .btn-delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

        @media (max-width: 992px) {
            .admin-main { margin-left: 0 !important; padding: 15px; width: 100% !important; margin-bottom: 80px; }
            .admin-header { flex-direction: column; align-items: stretch; }
            .btn-add { justify-content: center; }
            .admin-table thead { display: none; }
            .admin-table tr { display: block; padding: 15px; border-bottom: 8px solid #f4f7f6; background: #fff; }
            .admin-table td { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border: none; text-align: right; }
            .admin-table td::before { content: attr(data-label); font-weight: 700; color: #bbb; font-size: 0.7rem; text-transform: uppercase; text-align: left; }
            .admin-table td[data-label="Image"] { justify-content: center; background: #fcfdfc; margin: -15px -15px 10px -15px; padding: 20px; }
            .admin-table td[data-label="Image"]::before { display: none; }
            .prod-img { width: 120px; height: 120px; border-radius: 15px; }
        }
    </style>
</head>
<body>

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        
        <div class="admin-header">
            <div class="header-title">
                <h2><i class="fa-solid fa-gift" style="color:var(--primary);"></i> Gift Inventory</h2>
                <p>Manage your gifts, pricing, and live availability.</p>
            </div>
            <a href="add-gift.php" class="btn-add">
                <i class="fa-solid fa-plus"></i> Add New Gift
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div id="statusAlert" style="margin-bottom: 20px;">
                <?php if ($_GET['msg'] === 'restocked'): ?>
                    <div style="background:#e9fdf3; color:#10b981; padding:15px; border-radius:12px; font-weight:700; border:1px solid #d1fae5; display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-check-circle"></i> Gift restocked successfully!
                    </div>
                <?php elseif ($_GET['msg'] === 'soldout'): ?>
                    <div style="background:#fff1f2; color:#f43f5e; padding:15px; border-radius:12px; font-weight:700; border:1px solid #ffe4e6; display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-info-circle"></i> Gift marked as Sold Out.
                    </div>
                <?php elseif ($_GET['msg'] === 'error'): ?>
                    <div style="background:#fefce8; color:#a16207; padding:15px; border-radius:12px; font-weight:700; border:1px solid #fef08a; display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-exclamation-triangle"></i> Error updating database.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="70">ID</th>
                        <th width="100">Image</th>
                        <th>Product Details</th>
                        <th>Pricing</th>
                        <th>Stock Status</th>
                        <th>Visibility</th>
                        <th width="110" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td data-label="ID" style="font-weight:700; color:#999;">#<?= $row['id'] ?></td>
                                <td data-label="Image">
                                    <img src="/uploads/<?= htmlspecialchars($row['image']) ?>" 
                                         class="prod-img" 
                                         alt="gift"
                                         onerror="this.onerror=null; this.src='https://placehold.co/200x200/f8faf9/326e54?text=Gift'">
                                </td>
                                <td data-label="Product">
                                    <div style="font-weight:700; color: #333; font-size: 1rem;"><?= htmlspecialchars($row['name']) ?></div>
                                    <?php $liveLink = product_url(['type' => 'gift', 'slug' => $row['slug'] ?? '', 'id' => $row['id']]); ?>
                                    <a href="<?= $liveLink ?>" target="_blank" style="font-size:0.75rem; color:var(--primary); text-decoration: none; font-weight:600;">
                                        <i class="fa-solid fa-up-right-from-square" style="font-size:0.6rem;"></i> View Live Link
                                    </a>
                                </td>
                                <td data-label="Pricing">
                                    <div style="font-weight:800; color:var(--primary); font-size:1.1rem;">₹<?= number_format($row['price']) ?></div>
                                    <?php if($row['original_price'] > $row['price']): ?>
                                        <div style="font-size:0.7rem; text-decoration:line-through; color:#bbb;">₹<?= number_format($row['original_price']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Stock">
                                    <?php if($row['in_stock']): ?>
                                        <a href="actions/toggle_stock.php?type=gifts&id=<?= $row['id'] ?>&stock=0&csrf_token=<?php echo csrf_token(); ?>" class="badge badge-success badge-clickable">
                                            <i class="fa-solid fa-circle-check"></i> In Stock
                                        </a>
                                    <?php else: ?>
                                        <a href="actions/toggle_stock.php?type=gifts&id=<?= $row['id'] ?>&stock=1&csrf_token=<?php echo csrf_token(); ?>" class="badge badge-danger badge-clickable">
                                            <i class="fa-solid fa-circle-xmark"></i> Sold Out
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <span class="badge <?= $row['status'] ? 'badge-success' : 'badge-warning' ?>">
                                        <i class="fa-solid <?= $row['status'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i> 
                                        <?= $row['status'] ? 'Active' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                        <a href="edit-gift.php?id=<?= $row['id'] ?>" class="btn-action" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="actions/delete_gift.php?id=<?= $row['id'] ?>&csrf_token=<?php echo csrf_token(); ?>" 
                                           onclick="return confirm('Permanently delete this gift?')"
                                           class="btn-action btn-delete" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:100px; color:#bbb;">
                                <i class="fa-solid fa-gift" style="font-size: 3rem; display:block; margin-bottom:15px; opacity:0.3;"></i>
                                No gifts found in your inventory.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const alert = document.getElementById('statusAlert');
        if (alert) {
            setTimeout(() => {
                alert.style.transition = "opacity 0.6s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 600);
            }, 4000);
        }
    </script>
</body>
</html>