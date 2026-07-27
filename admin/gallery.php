<?php



require_once __DIR__ . '/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// FETCH GALLERY
$query = "SELECT id, title, tag, image, status, created_at FROM gallery ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Gallery'; include 'partials/head.php'; ?>
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

        /* Desktop: Sidebar Offset */
        .admin-main { 
            margin-left: var(--sidebar-width); 
            padding: 25px 20px; 
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width)); 
            transition: all 0.3s ease;
            display: block;
        }

        /* HEADER SECTION */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* TABLE & CARD DESIGN */
        .table-wrapper {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            width: 100%;
            border: 1px solid #eee;
        }

        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #fafafa; padding: 15px; text-align: left; font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #eee; }
        .admin-table td { padding: 15px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }

        .gallery-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: 0.3s; }
        .gallery-thumb:hover { transform: scale(1.1); }
        
        /* BADGES */
        .badge { padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-success { background: #e9f7ef; color: #27ae60; }
        .badge-warning { background: #fff4e5; color: #f39c12; }
        .badge-tag { background:#eef6f1; color:var(--primary); font-size: 0.65rem; }

        /* ACTION BUTTONS */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 10px;
            border: 1px solid #eee;
            color: #555;
            text-decoration: none;
            transition: 0.3s;
            background: #fff;
        }
        .btn-action:hover { background: var(--primary); color: #fff; border-color: var(--primary); transform: translateY(-2px); }
        .btn-delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

        /* --- MOBILE RESPONSIVENESS --- */
        @media (max-width: 992px) {
            .admin-main { 
                margin-left: 0 !important; 
                margin-bottom: 100px !important; /* Clearance for Bottom Mobile Nav */
                padding: 15px; 
                width: 100% !important;
                max-width: 100vw !important;
            }

            .admin-header { flex-direction: column; align-items: stretch; }
            .admin-header .btn { width: 100%; justify-content: center; margin-top: 10px; padding: 12px; }
            
            .admin-table thead { display: none; }
            .admin-table tr { 
                display: block; 
                padding: 15px; 
                border-bottom: 8px solid #f4f7f6; 
                background: #fff;
                width: 100%;
            }
            .admin-table td { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                padding: 10px 0; 
                border: none; 
                font-size: 0.9rem;
                width: 100%;
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
            
            /* Center the thumbnail and make it larger on mobile cards */
            .admin-table td:first-child { 
                background: #fcfdfc;
                margin: -15px -15px 10px -15px;
                padding: 15px;
                justify-content: center;
                border-bottom: 1px solid #eee;
            }
            .admin-table td:first-child::before { display: none; }
            .gallery-thumb { width: 100%; height: 180px; border-radius: 12px; }
            
            .admin-table td[data-label="Title"] { font-weight: 800; color: #333; }
        }
    </style>
</head>

<body class="admin-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        
        <div class="admin-header">
            <div>
                <h2 style="margin:0;"><i class="fas fa-images" style="color:var(--primary);"></i> Gallery Manager</h2>
                <p style="color:#888; font-size: 0.8rem; margin-top: 5px;">Manage floral arrangement photos and bouquet showcase.</p>
            </div>
            <a href="add-gallery.php" class="btn">
                <i class="fas fa-plus"></i> Add New Image
            </a>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="100">Preview</th>
                        <th>Image Title</th>
                        <th>Category</th>
                        <th>Visibility</th>
                        <th>Date</th>
                        <th width="110" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td data-label="Preview">
                                    <img src="/uploads/<?= htmlspecialchars($row['image']) ?>" 
                                         onerror="this.src='/assets/default-post.jpg'" 
                                         class="gallery-thumb" alt="gallery">
                                </td>
                                <td data-label="Title">
                                    <strong style="color:#333;"><?= htmlspecialchars($row['title']) ?></strong>
                                </td>
                                <td data-label="Category">
                                    <span class="badge badge-tag">
                                        <?= htmlspecialchars($row['tag']) ?>
                                    </span>
                                </td>
                                <td data-label="Visibility">
                                    <span class="badge <?= $row['status'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $row['status'] ? 'Active' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td data-label="Date">
                                    <span style="color:#888; font-size: 0.8rem;"><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                                </td>
                                <td data-label="Manage">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="edit-gallery.php?id=<?= $row['id'] ?>" class="btn-action" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="actions/delete_gallery.php?id=<?= $row['id'] ?>&csrf_token=<?php echo csrf_token(); ?>" 
                                           onclick="return confirm('Delete this image from gallery?')"
                                           class="btn-action btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:60px; color:#ccc;">
                                <i class="fas fa-image" style="font-size: 2.5rem; display:block; margin-bottom:10px;"></i>
                                The gallery is currently empty.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>