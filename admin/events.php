<?php



require_once __DIR__ . '/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// FETCH EVENTS
$query = "SELECT id, title, tag, status, created_at, cover_image FROM events ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Events'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #326e54;
            --bg: #f4f7f6;
            --sidebar-width: 260px;
        }

        /* --- CRITICAL MOBILE FIT & CLEARANCE --- */
        * { box-sizing: border-box; }
        
        html, body { 
            width: 100%;
            overflow-x: hidden; 
            margin: 0; 
            padding: 0;
            font-family: 'Inter', sans-serif; 
            background: var(--bg);
        }

        /* Desktop: Push content to the right of fixed sidebar */
        .admin-main { 
            margin-left: var(--sidebar-width);
            padding: 25px 20px; 
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* HEADER */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* TABLE WRAPPER */
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

        .event-img { width: 70px; height: 50px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
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

        /* MOBILE RESPONSIVENESS */
        @media (max-width: 992px) {
            .admin-main { 
                margin-left: 0 !important; 
                margin-bottom: 90px !important; /* CLEARANCE FOR MOBILE NAV */
                padding: 20px 15px;
                width: 100%;
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
            }
            .admin-table td::before { 
                content: attr(data-label); 
                font-weight: 700; 
                color: #bbb; 
                font-size: 0.7rem; 
                text-transform: uppercase;
            }
            
            /* Card Header Visual */
            .admin-table td:first-child { 
                background: #fcfdfc;
                margin: -15px -15px 10px -15px;
                padding: 15px;
                justify-content: center;
                border-bottom: 1px solid #eee;
            }
            .admin-table td:first-child::before { display: none; }
            .event-img { width: 100%; height: 180px; border-radius: 10px; }
            
            .admin-table td[data-label="Title"] { font-weight: 800; color: var(--primary); text-align: right; }
        }
    </style>
</head>

<body class="admin-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        
        <div class="admin-header">
            <div>
                <h2 style="margin:0;"><i class="fas fa-calendar-alt" style="color:var(--primary);"></i> Event Portfolio</h2>
                <p style="color:#888; font-size: 0.8rem; margin-top: 5px;">Showcase your weddings, corporate events, and parties.</p>
            </div>
            <a href="add-event.php" class="btn">
                <i class="fas fa-plus"></i> Add New Event
            </a>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="100">Cover</th>
                        <th>Event Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="110" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td data-label="Cover">
                                    <img src="/uploads/<?= htmlspecialchars($row['cover_image']) ?>" 
                                         onerror="this.src='https://images.unsplash.com/photo-1519225421980-715cb0215aed?q=80&w=400'" 
                                         class="event-img" alt="event">
                                </td>
                                <td data-label="Title">
                                    <strong style="color:#333;"><?= htmlspecialchars($row['title']) ?></strong>
                                </td>
                                <td data-label="Category">
                                    <span class="badge badge-tag">
                                        <?= htmlspecialchars($row['tag']) ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge <?= $row['status'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $row['status'] ? 'Active' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td data-label="Date">
                                    <span style="color:#888; font-size: 0.8rem;"><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                                </td>
                                <td data-label="Manage">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="edit-event.php?id=<?= $row['id'] ?>" class="btn-action" title="Edit Event">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="actions/delete_event.php?id=<?= $row['id'] ?>&csrf_token=<?php echo csrf_token(); ?>" 
                                           onclick="return confirm('Delete this event permanently?')"
                                           class="btn-action btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:60px; color:#aaa;">
                                <i class="fas fa-calendar-times" style="font-size: 2.5rem; display:block; margin-bottom:10px;"></i>
                                No events found in the database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>