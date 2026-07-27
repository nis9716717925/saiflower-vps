<?php 
// 1. SETUP & ERROR REPORTING



require_once 'auth_check.php';
require_once '../config.php';

// 2. HANDLE TOGGLE STATUS
if (isset($_GET['toggle_id'])) {
    csrf_verify_or_die();
    $tid = intval($_GET['toggle_id']);
    $new_st = intval($_GET['st']);
    mysqli_query($conn, "UPDATE tags SET status = $new_st WHERE id = $tid");
    header("Location: tags.php?msg=updated");
    exit;
}

// 3. THE MASTER QUERY
$sql = "SELECT t.*, 
        (SELECT COUNT(*) FROM events WHERE LOWER(tag) = LOWER(t.name) OR LOWER(tag) LIKE CONCAT('%,', LOWER(t.name), ',%')) as count_events,
        (SELECT COUNT(*) FROM gallery WHERE LOWER(tag) = LOWER(t.name) OR LOWER(tag) LIKE CONCAT('%,', LOWER(t.name), ',%')) as count_gallery,
        (SELECT COUNT(*) FROM flowers WHERE LOWER(tag) = LOWER(t.name) OR LOWER(tag) LIKE CONCAT('%,', LOWER(t.name), ',%')) as count_flowers,
        (SELECT COUNT(*) FROM cakes WHERE LOWER(tag) = LOWER(t.name) OR LOWER(tag) LIKE CONCAT('%,', LOWER(t.name), ',%')) as count_cakes,
        (SELECT COUNT(*) FROM gifts WHERE LOWER(tag) = LOWER(t.name) OR LOWER(tag) LIKE CONCAT('%,', LOWER(t.name), ',%')) as count_gifts
        FROM tags t 
        ORDER BY t.name ASC";

try {
    $tags = mysqli_query($conn, $sql);
} catch (mysqli_sql_exception $e) {
    if (strpos(strtolower($e->getMessage()), "doesn't exist") !== false) {
        // Table might be missing, create it with explicit collation matching the rest of the DB
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        // Retry original query
        $tags = mysqli_query($conn, $sql);
    } else {
        die("<div style='padding:50px; font-family:sans-serif; background:#fff5f5; color:#c53030; border:2px solid #feb2b2; border-radius:10px;'>
                <h3>Database Error Detected</h3>
                <p>Error Details: " . $e->getMessage() . "</p>
             </div>");
    }
}

// 4. CATCH ERRORS (Fallback for non-exception modes)
if (!$tags) {
    die("<div style='padding:50px; font-family:sans-serif; background:#fff5f5; color:#c53030; border:2px solid #feb2b2; border-radius:10px;'>
            <h3>Database Error Detected</h3>
            <p>Error Details: " . mysqli_error($conn) . "</p>
         </div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Tags'; include 'partials/head.php'; ?>
    <style>
        :root { 
            --primary: #326e54; 
            --bg: #f4f7f6; 
            --sidebar-width: 260px;
        }

        /* --- THE MASTER MOBILE FIT & CLEARANCE --- */
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

        .card { background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; width: 100%; }
        
        .add-tag-form { display: flex; gap: 12px; margin-bottom: 30px; background: #fafafa; padding: 20px; border-radius: 15px; border: 1px solid #eee; }
        .form-input { flex: 1; padding: 14px; border-radius: 12px; border: 1px solid #ddd; font-size: 16px; background: #fff; }
        
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #fafafa; padding: 15px; text-align: left; font-size: 0.75rem; color: #999; text-transform: uppercase; border-bottom: 1px solid #eee; }
        .admin-table td { padding: 15px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }

        /* ITEM COUNT BADGES */
        .count-group { display: flex; gap: 5px; margin-top: 8px; flex-wrap: wrap; }
        .count-pill { 
            display: inline-flex; align-items: center; gap: 5px; 
            font-size: 0.65rem; padding: 3px 10px; border-radius: 8px; 
            font-weight: 700; border: 1px solid rgba(0,0,0,0.03); 
        }
        
        .pill-event { background: #f0fdf4; color: #166534; }
        .pill-gallery { background: #fff1f2; color: #be123c; }
        .pill-flower { background: #eff6ff; color: #1e40af; }
        .pill-cake { background: #fdf4ff; color: #86198f; }
        .pill-gift { background: #fff7ed; color: #c2410c; }

        .badge { padding: 6px 14px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-decoration: none; text-transform: uppercase; }
        .badge-success { background: #eef6f1; color: #326e54; }
        .badge-warning { background: #fee2e2; color: #ef4444; }
        
        .btn-action { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 12px; border: 1px solid #eee; color: #555; text-decoration: none; background: #fff; }

        /* --- MOBILE RESPONSIVENESS --- */
        @media (max-width: 992px) {
            .admin-main { 
                margin-left: 0 !important; 
                margin-bottom: 100px !important; 
                padding: 15px; 
                width: 100% !important;
                max-width: 100vw !important;
            }

            .admin-header { flex-direction: column; align-items: stretch; gap: 10px; text-align: center; }
            .add-tag-form { flex-direction: column; padding: 15px; }
            .add-tag-form .btn { width: 100%; padding: 15px; }
            
            .admin-table thead { display: none; }
            .admin-table tr { 
                display: block; background: #fff; border: 1px solid #eee; 
                border-radius: 15px; margin-bottom: 15px; padding: 15px; width: 100%;
            }
            .admin-table td { 
                display: flex; justify-content: space-between; align-items: center; 
                width: 100% !important; border: none; padding: 10px 0; font-size: 0.9rem;
            }
            .admin-table td::before { 
                content: attr(data-label); font-weight: 800; color: #bbb; 
                font-size: 0.65rem; text-transform: uppercase; text-align: left;
            }
            
            .admin-table td:first-child { 
                display: block; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 10px; text-align: left;
            }
            .admin-table td:first-child::before { display: none; }
        }
    </style>
</head>
<body class="admin-body">
    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-header">
            <h2 style="margin:0;"><i class="fas fa-tags" style="color:var(--primary);"></i> Category Tags</h2>
            <p style="color:#888; font-size: 0.85rem; margin-top: 5px;">Track usage stats across all site sections.</p>
        </div>

        <div class="card">
            <form class="add-tag-form" method="post" action="actions/add_tag.php">
                <?php csrf_field(); ?>
                <input type="text" name="name" class="form-input" placeholder="New Tag Name..." required>
                <button type="submit" class="btn" style="background:var(--primary); color:white; border:none; padding:0 25px; border-radius:12px; font-weight:800; cursor:pointer;">Create Tag</button>
            </form>

            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tag & Usage</th>
                            <th>Status</th>
                            <th width="100" style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($t = mysqli_fetch_assoc($tags)): ?>
                        <tr>
                            <td data-label="Tag Info">
                                <strong style="color:#333; font-size: 1.05rem;"><?= htmlspecialchars($t['name']) ?></strong>
                                <div class="count-group">
                                    <span class="count-pill pill-event" title="Events"><i class="fas fa-calendar-alt"></i> <?= $t['count_events'] ?></span>
                                    <span class="count-pill pill-gallery" title="Gallery"><i class="fas fa-images"></i> <?= $t['count_gallery'] ?></span>
                                    <span class="count-pill pill-flower" title="Flowers"><i class="fas fa-seedling"></i> <?= $t['count_flowers'] ?></span>
                                    <span class="count-pill pill-cake" title="Cakes"><i class="fas fa-birthday-cake"></i> <?= $t['count_cakes'] ?? 0 ?></span>
                                    <span class="count-pill pill-gift" title="Gifts"><i class="fas fa-gift"></i> <?= $t['count_gifts'] ?? 0 ?></span>
                                </div>
                            </td>
                            <td data-label="Visibility">
                                <a href="tags.php?toggle_id=<?= $t['id'] ?>&st=<?= $t['status']?0:1 ?>&csrf_token=<?php echo csrf_token(); ?>" 
                                   class="badge <?= $t['status'] ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $t['status'] ? 'Active' : 'Hidden' ?>
                                </a>
                            </td>
                            <td data-label="Manage">
                                <div style="display:flex; gap:10px; justify-content: flex-end;">
                                    <a href="actions/delete_tag.php?id=<?= $t['id'] ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn-action" style="color: #ef4444;" onclick="return confirm('Delete this tag?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>