<?php
// admin/pages.php
ini_set('display_startup_errors', 1);

$root = $_SERVER['DOCUMENT_ROOT'];
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (file_exists(__DIR__ . '/auth_check.php')) {
    require_once __DIR__ . '/auth_check.php';
} elseif (file_exists($root . '/admin/auth_check.php')) {
    require_once $root . '/admin/auth_check.php';
}

require_once $root . '/config.php';

// Check schema just in case
$check_dp = $conn->query("SHOW TABLES LIKE 'dynamic_pages'");
if ($check_dp && $check_dp->num_rows == 0) {
    header("Location: dashboard.php");
    exit;
}

$query = "SELECT * FROM dynamic_pages ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Custom Pages'; include 'partials/head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #326e54;
            --accent: #d4af37;
            --bg: #f4f7f6;
            --sidebar-width: 260px;
        }

        * { box-sizing: border-box; }
        html, body { 
            width: 100vw; max-width: 100%; overflow-x: hidden; 
            margin: 0; background: var(--bg); font-family: 'Inter', sans-serif;
        }

        .admin-main { 
            margin-left: var(--sidebar-width); padding: 30px; 
            min-height: 100vh; width: calc(100% - var(--sidebar-width)); display: block;
        }

        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .header-title h2 { margin: 0; color: #1a1a1a; font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .header-title p { margin: 5px 0 0; color: #888; font-size: 0.85rem; }

        .btn-add { background: var(--primary); color: white !important; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(50, 110, 84, 0.2); transition: 0.3s; }
        .btn-add:hover { transform: translateY(-2px); opacity: 0.9; }

        .table-wrapper { background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #fafafa; padding: 18px 20px; text-align: left; font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #eee; }
        .admin-table td { padding: 18px 20px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }

        .badge { padding: 6px 14px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; text-transform: uppercase; text-decoration: none; border: none; transition: 0.2s; }
        .badge-success { background: #e9f7ef; color: #27ae60; }
        .badge-warning { background: #fff4e5; color: #f39c12; }
        
        .badge-clickable:hover { transform: scale(1.05); filter: brightness(0.95); }

        .btn-action { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; border: 1px solid #eee; color: #555; text-decoration: none; transition: 0.3s; background: #fff; }
        .btn-action:hover { background: var(--primary); color: #fff; border-color: var(--primary); transform: translateY(-2px); }
        .btn-delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

        @media (max-width: 992px) {
            .admin-main { margin-left: 0 !important; padding: 15px; width: 100% !important; margin-bottom: 80px; }
            .admin-table thead { display: none; }
            .admin-table tr { display: block; padding: 15px; border-bottom: 8px solid #f4f7f6; background: #fff; border-radius: 12px; margin-bottom: 12px; }
            .admin-table td { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border: none; text-align: right; border-bottom: 1px solid #f9fafb; }
            .admin-table td:last-child { border-bottom: none; padding-top: 12px; }
            .admin-table td::before { content: attr(data-label); font-weight: 700; color: #bbb; font-size: 0.65rem; text-transform: uppercase; text-align: left; }
        }
    </style>
</head>
<body>

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                <h2><i class="fa-solid fa-file-alt" style="color:var(--primary);"></i> Custom Pages</h2>
                <p>Manage SEO-friendly dynamic event and occasion pages.</p>
            </div>
            <div class="header-actions">
                <a href="add-page.php" class="btn-add">
                    <i class="fa-solid fa-plus"></i> Create New Page
                </a>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div id="statusAlert" style="margin-bottom: 20px;">
                <?php if ($_GET['msg'] === 'added'): ?>
                    <div style="background:#e9fdf3; color:#10b981; padding:15px; border-radius:12px; font-weight:700; border:1px solid #d1fae5;"><i class="fas fa-check-circle"></i> Page created successfully!</div>
                <?php elseif ($_GET['msg'] === 'updated'): ?>
                    <div style="background:#e9fdf3; color:#10b981; padding:15px; border-radius:12px; font-weight:700; border:1px solid #d1fae5;"><i class="fas fa-check-circle"></i> Page updated successfully!</div>
                <?php elseif ($_GET['msg'] === 'deleted'): ?>
                    <div style="background:#fff1f2; color:#f43f5e; padding:15px; border-radius:12px; font-weight:700; border:1px solid #ffe4e6;"><i class="fas fa-trash"></i> Page deleted.</div>
                <?php elseif ($_GET['msg'] === 'error'): ?>
                    <div style="background:#fefce8; color:#a16207; padding:15px; border-radius:12px; font-weight:700; border:1px solid #fef08a;"><i class="fas fa-exclamation-triangle"></i> Something went wrong.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="70">ID</th>
                        <th>Title</th>
                        <th>URL Preview</th>
                        <th>Status</th>
                        <th width="150" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td data-label="ID" style="font-weight:700; color:#999;">#<?= $row['id'] ?></td>
                                <td data-label="Title">
                                    <div style="font-weight:700; color: #333; font-size: 1rem;"><?= htmlspecialchars($row['title']) ?></div>
                                    <div style="font-size:0.7rem; color:#888;">Created: <?= date('M d, Y', strtotime($row['created_at'])) ?></div>
                                </td>
                                <td data-label="URL Preview">
                                    <a href="/<?= htmlspecialchars($row['slug']) ?>" target="_blank" style="color:var(--primary); font-weight:600; text-decoration:none; font-size:0.85rem;">
                                        /<?= htmlspecialchars($row['slug']) ?> <i class="fa-solid fa-up-right-from-square" style="font-size:0.7rem;"></i>
                                    </a>
                                </td>
                                <td data-label="Status">
                                    <a href="actions/toggle_page_status.php?id=<?= $row['id'] ?>&status=<?= $row['status'] ? '0' : '1' ?>&csrf_token=<?php echo csrf_token(); ?>" 
                                       class="badge <?= $row['status'] ? 'badge-success' : 'badge-warning' ?> badge-clickable">
                                        <i class="fa-solid <?= $row['status'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i> 
                                        <?= $row['status'] ? 'Active' : 'Hidden' ?>
                                    </a>
                                </td>
                                <td data-label="Actions">
                                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                        <a href="edit-page.php?id=<?= $row['id'] ?>" class="btn-action" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="actions/delete_page.php?id=<?= $row['id'] ?>&csrf_token=<?php echo csrf_token(); ?>" 
                                           onclick="return confirm('Permanently delete this page?')"
                                           class="btn-action btn-delete" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:100px; color:#bbb;">
                                <i class="fa-solid fa-file-alt" style="font-size: 3rem; display:block; margin-bottom:15px; opacity:0.3;"></i>
                                No custom pages found. Click "Create New Page" to start.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        setTimeout(() => {
            const alert = document.getElementById('statusAlert');
            if(alert) { alert.style.transition = "opacity 0.6s"; alert.style.opacity = "0"; setTimeout(() => alert.remove(), 600); }
        }, 4000);
    </script>
</body>
</html>
