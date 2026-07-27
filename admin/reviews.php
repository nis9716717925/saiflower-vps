<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';

$pageTitle = "Manage Reviews";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/partials/head.php'; ?>
    <style>
        :root { --primary: #326e54; --bg: #f4f7f6; --sidebar-width: 260px; }
        * { box-sizing: border-box; }
        html, body { width: 100%; margin: 0; padding: 0; background: var(--bg); font-family: 'Inter', sans-serif; }
        
        .admin-main { 
            padding: 30px; 
            min-height: 100vh; 
            display: block; 
             /* Mobile first default */
             margin-left: 0; 
             width: 100%;
        }
        @media (min-width: 992px) { 
            .admin-main { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); } 
        }

        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-title h2 { margin: 0; color: #1a1a1a; font-size: 1.6rem; font-weight: 800; }
        .header-title p { margin: 5px 0 0; color: #888; font-size: 0.85rem; }

        .btn-add { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: 0.3s; }
        .btn-add:hover { transform: translateY(-2px); opacity: 0.9; }

        .table-wrapper { background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; border: 1px solid #eee; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #fafafa; padding: 18px 20px; text-align: left; font-size: 0.75rem; color: #999; text-transform: uppercase; border-bottom: 1px solid #eee; }
        .admin-table td { padding: 18px 20px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }

        .rating-star { color: #f59e0b; font-size: 0.8rem; }
        .badge { padding: 6px 14px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; }
        .badge-success { background: #e9fdf3; color: #10b981; }
        .badge-warning { background: #fffbeb; color: #f59e0b; }

        .btn-action { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #eee; color: #555; display: inline-flex; justify-content: center; align-items: center; transition: 0.3s; }
        .btn-action:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .btn-delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

        .form-container { background: #fff; padding: 25px; border-radius: 15px; margin-bottom: 30px; display: none; border: 1px solid #eee; }
        .form-container.active { display: block; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                <h2><i class="fas fa-star" style="color:var(--primary)"></i> Reviews</h2>
                <p>Manage customer testimonials.</p>
            </div>
            <button onclick="document.getElementById('addForm').classList.toggle('active')" class="btn-add">
                <i class="fas fa-plus"></i> Add Review
            </button>
        </div>

        <div id="addForm" class="form-container">
            <form action="actions/manage_reviews.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="font-bold text-xs uppercase text-gray-500">Customer Name</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="font-bold text-xs uppercase text-gray-500">Platform</label>
                        <select name="platform" class="form-control">
                            <option value="Google">Google</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Direct">Direct</option>
                        </select>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="font-bold text-xs uppercase text-gray-500">Review Text</label>
                        <textarea name="review_text" rows="3" required class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-bold text-xs uppercase text-gray-500">Rating (1-5)</label>
                        <input type="number" name="rating" min="1" max="5" value="5" class="form-control">
                    </div>
                    <div class="form-group flex justify-end items-end">
                        <button type="submit" class="btn-add">Save Review</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Review</th>
                        <th>Rating</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM reviews ORDER BY created_at DESC");
                    while($row = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td class="font-bold text-gray-800"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="text-sm text-gray-600 max-w-xs truncate"><?= htmlspecialchars($row['review_text']) ?></td>
                        <td>
                            <?php for($i=0; $i<$row['rating']; $i++) echo '<i class="fas fa-star rating-star"></i>'; ?>
                        </td>
                        <td><span class="text-xs bg-gray-100 px-2 py-1 rounded"><?= $row['platform'] ?></span></td>
                        <td>
                            <a href="actions/manage_reviews.php?action=toggle&id=<?= $row['id'] ?>&status=<?= $row['status'] ? 0 : 1 ?>&csrf_token=<?= htmlspecialchars(generate_csrf_token()) ?>" 
                               class="badge <?= $row['status'] ? 'badge-success' : 'badge-warning' ?>">
                                <?= $row['status'] ? 'Active' : 'Hidden' ?>
                            </a>
                        </td>
                        <td class="text-right">
                            <a href="actions/manage_reviews.php?action=delete&id=<?= $row['id'] ?>&csrf_token=<?= htmlspecialchars(generate_csrf_token()) ?>" 
                               onclick="return confirm('Delete this review?')" class="btn-action btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>