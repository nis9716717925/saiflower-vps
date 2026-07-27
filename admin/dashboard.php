<?php
// dashboard.php
require_once __DIR__ . '/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. AUTO-FIX SCHEMA IF MISSING
// Wrapped in output buffering to prevent "All schema checks completed" from breaking the layout
// 1. AUTO-FIX SCHEMA IF MISSING
// Wrapped in output buffering to prevent "All schema checks completed" from breaking the layout
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/fix_db_schema.php')) {
    ob_start();
    include_once $_SERVER['DOCUMENT_ROOT'] . '/fix_db_schema.php';
    ob_end_clean();
}

// AUTO-MIGRATE CAKES & GIFTS
$tables = ['cakes', 'gifts'];
foreach ($tables as $t) {
    // Simple check to see if table exists
    $check = $conn->query("SHOW TABLES LIKE '$t'");
    if ($check && $check->num_rows == 0) {
        $sql = "CREATE TABLE `$t` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL UNIQUE,
            `description` text,
            `price` decimal(10,2) NOT NULL DEFAULT 0.00,
            `original_price` decimal(10,2) DEFAULT 0.00,
            `image` varchar(255) DEFAULT NULL,
            `model_3d` varchar(255) DEFAULT NULL,
            `in_stock` tinyint(1) DEFAULT 1,
            `status` tinyint(1) DEFAULT 1,
            `meta_title` varchar(255) DEFAULT NULL,
            `meta_description` text DEFAULT NULL,
            `meta_keywords` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($sql);
    }
}

// AUTO-MIGRATE DYNAMIC PAGES
$check_dp = $conn->query("SHOW TABLES LIKE 'dynamic_pages'");
if ($check_dp && $check_dp->num_rows == 0) {
    $sql = "CREATE TABLE `dynamic_pages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL UNIQUE,
        `layout_type` varchar(50) DEFAULT 'event_info',
        `page_tag` varchar(100) DEFAULT NULL,
        `hero_image` varchar(255) DEFAULT NULL,
        `extra_images` text,
        `content` longtext,
        `meta_title` varchar(255) DEFAULT NULL,
        `meta_description` text DEFAULT NULL,
        `meta_keywords` text DEFAULT NULL,
        `status` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($sql);
} else {
    // Auto-patch missing columns
    $conn->query("ALTER TABLE `dynamic_pages` ADD COLUMN IF NOT EXISTS `layout_type` VARCHAR(50) DEFAULT 'event_info' AFTER `slug`");
    $conn->query("ALTER TABLE `dynamic_pages` ADD COLUMN IF NOT EXISTS `page_tag` VARCHAR(100) DEFAULT NULL AFTER `layout_type`");
    $conn->query("ALTER TABLE `dynamic_pages` ADD COLUMN IF NOT EXISTS `extra_images` TEXT DEFAULT NULL AFTER `hero_image`");
    $conn->query("ALTER TABLE `dynamic_pages` ADD COLUMN IF NOT EXISTS `content` LONGTEXT NULL AFTER `extra_images`");
}

// 2. OPTIMIZED STATS FETCHING (Single Query)
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM events) as event_count,
    (SELECT COUNT(*) FROM gallery) as gallery_count,
    (SELECT COUNT(*) FROM blogs) as blog_count,
    (SELECT COUNT(*) FROM flowers) as flower_count,
    (SELECT COUNT(*) FROM cakes) as cake_count,
    (SELECT COUNT(*) FROM gifts) as gift_count,
    (
        (SELECT COUNT(*) FROM flowers WHERE in_stock = 0) + 
        (SELECT COUNT(*) FROM cakes WHERE in_stock = 0) + 
        (SELECT COUNT(*) FROM gifts WHERE in_stock = 0)
    ) as out_of_stock_count";

// Safely handle orders table check
$orders_check = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");
if($orders_check && mysqli_num_rows($orders_check)) {
    $stats_query .= ", (SELECT COUNT(*) FROM orders) as order_count";
} else {
    $stats_query .= ", (SELECT 0) as order_count";
}

$stats_result = mysqli_query($conn, $stats_query);
$s = mysqli_fetch_assoc($stats_result);

// 2.1 FETCH OUT OF STOCK DETAILS
$oos_flowers = mysqli_query($conn, "SELECT id, name FROM flowers WHERE in_stock = 0");
$oos_cakes   = mysqli_query($conn, "SELECT id, name FROM cakes WHERE in_stock = 0");
$oos_gifts   = mysqli_query($conn, "SELECT id, name FROM gifts WHERE in_stock = 0");

$all_oos = [];
while($row = mysqli_fetch_assoc($oos_flowers)) { $row['type'] = 'flower'; $row['link'] = 'edit-flower.php?id='.$row['id']; $all_oos[] = $row; }
while($row = mysqli_fetch_assoc($oos_cakes))   { $row['type'] = 'cake';   $row['link'] = 'edit-cake.php?id='.$row['id'];   $all_oos[] = $row; }
while($row = mysqli_fetch_assoc($oos_gifts))   { $row['type'] = 'gift';   $row['link'] = 'edit-gift.php?id='.$row['id'];   $all_oos[] = $row; }

// Variables for ease of use
$events       = $s['event_count'] ?? 0;
$gallery      = $s['gallery_count'] ?? 0;
$blogs        = $s['blog_count'] ?? 0;
$flowers      = $s['flower_count'] ?? 0;
$cakes        = $s['cake_count'] ?? 0;
$gifts        = $s['gift_count'] ?? 0;
$total_products = $flowers + $cakes + $gifts;
$out_of_stock = $s['out_of_stock_count'] ?? 0;
$orders       = $s['order_count'] ?? 0;

// 3. RECENT FLOWERS
$recent_flowers = mysqli_query($conn, "SELECT name, price, in_stock FROM flowers ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Dashboard'; include 'partials/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #326e54;
            --secondary: #d4af37;
            --bg: #f4f7f6;
            --sidebar-width: 260px;
        }

        * { box-sizing: border-box; }
        html, body { 
            max-width: 100vw;
            overflow-x: hidden;
            background: var(--bg); 
            margin: 0; 
            font-family: 'Inter', sans-serif; 
        }

        .admin-main { 
            margin-left: var(--sidebar-width);
            padding: 30px; 
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Stats Grid - Fluid Layout */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin-bottom: 25px; 
        }

        .stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 18px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border: 1px solid #eee;
            transition: transform 0.2s;
            position: relative; /* Fixed for absolute positioned icon on mobile */
        }
        .stat-card:hover { transform: translateY(-3px); }

        .stat-icon { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.2rem; }
        
        .dashboard-container { 
            display: grid; 
            grid-template-columns: 1.8fr 1.2fr; 
            gap: 20px; 
        }

        .card { background: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #eee; }
        
        .activity-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f9f9f9; font-size: 0.85rem; }
        .badge-stock { padding: 4px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: bold; }
        .bg-low { background: #fee2e2; color: #dc2626; }
        .bg-ok { background: #f0fdf4; color: #16a34a; }

        .quick-actions-wrap { display: flex; gap: 10px; flex-wrap: wrap; }
        .quick-actions-wrap a { flex: 1; min-width: 120px; text-align: center; text-decoration: none; }

        @media (max-width: 992px) {
            .admin-main { margin-left: 0; margin-bottom: 80px; padding: 20px; }
            .dashboard-container { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .stat-card { padding: 15px; flex-direction: column; align-items: flex-start; gap: 10px; }
            .stat-icon { position: absolute; right: 15px; top: 15px; width: 35px; height: 35px; font-size: 1rem; }
            .stat-card > div:first-child { width: 100%; }
            
            .quick-actions-wrap a { width: 100%; flex: none; display: flex; align-items: center; justify-content: center; gap: 10px; }
        }
    </style>
</head>
<body class="admin-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:15px;">
            <div>
                <h2 style="margin:0; font-size: 1.6rem; color: #333;">Dashboard</h2>
                <p style="color:#888; margin:2px 0 0; font-size: 0.85rem;">Sai Flowers Overview</p>
            </div>
            <a href="/" target="_blank" class="btn btn-outline" style="background: #fff; border-radius: 10px; padding: 8px 15px; font-size: 0.8rem;">
                <i class="fas fa-external-link-alt"></i> Preview Site
            </a>
        </header>

        <div class="stats-grid">
            <?php
            $cards = [
                ['Events', $events, 'fa-calendar-alt', '#f0fdf4', '#16a34a'],
                ['Gallery', $gallery, 'fa-images', '#fff1f2', '#e11d48'],
                ['Flowers', $flowers, 'fa-leaf', '#ecfdf5', '#059669'],
                ['Cakes', $cakes, 'fa-birthday-cake', '#fef2f2', '#b91c1c'],
                ['Gifts', $gifts, 'fa-gift', '#f5f3ff', '#7c3aed'],
                ['Blogs', $blogs, 'fa-newspaper', '#eff6ff', '#2563eb'],
                ['Orders', $orders, 'fa-shopping-bag', '#fef3c7', '#d97706']
            ];
            foreach ($cards as $card): ?>
            <div class="stat-card">
                <div>
                    <p style="margin:0; font-size:0.7rem; color:#999; text-transform:uppercase; font-weight:700;"><?= $card[0] ?></p>
                    <div style="font-size:1.4rem; font-weight:800;"><?= $card[1] ?></div>
                </div>
                <div class="stat-icon" style="background:<?= $card[3] ?>; color:<?= $card[4] ?>;"><i class="fas <?= $card[2] ?>"></i></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="dashboard-container">
            <div class="card">
                <h3 style="margin:0 0 20px 0; font-size: 1rem;"><i class="fas fa-chart-bar" style="color:#bbb; margin-right:8px;"></i> Content Distribution</h3>
                <div style="height: 250px;"><canvas id="growthChart"></canvas></div>
            </div>

            <div class="card">
                <h3 style="margin:0 0 20px 0; font-size: 1rem;"><i class="fas fa-shopping-basket" style="color:#bbb; margin-right:8px;"></i> Inventory Overview</h3>
                
                <div style="background: #fff5f5; padding: 12px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #fee2e2;">
                    <span style="font-weight:700; color:#b91c1c; display: block; margin-bottom: 10px;">Out of Stock Items (<?= count($all_oos) ?>)</span>
                    <div style="max-height: 250px; overflow-y: auto; padding-right: 5px;">
                        <?php if(empty($all_oos)): ?>
                            <p style="color: #16a34a; font-size: 0.85rem; margin: 0;">🎉 All items are in stock!</p>
                        <?php else: ?>
                            <?php foreach($all_oos as $item): ?>
                                <div class="activity-item" style="border-bottom: 1px solid #fee2e2;">
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;"><?= htmlspecialchars($item['name']) ?></span>
                                        <span style="font-size: 0.7rem; color: #999; text-transform: uppercase;"><?= $item['type'] ?></span>
                                    </div>
                                    <a href="<?= $item['link'] ?>" style="color: #b91c1c; text-decoration: none; font-size: 0.75rem; font-weight: 700; background: #fff; padding: 4px 10px; border-radius: 6px; border: 1px solid #fee2e2;">Restock</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <p style="font-size: 0.65rem; text-transform: uppercase; color: #999; letter-spacing: 1px; font-weight: 800; margin-bottom: 10px;">Recently Added Flowers</p>
                <?php while($row = mysqli_fetch_assoc($recent_flowers)): ?>
                <div class="activity-item">
                    <span><?= htmlspecialchars($row['name']) ?></span>
                    <span class="badge-stock <?= $row['in_stock'] ? 'bg-ok' : 'bg-low' ?>">
                        <?= $row['in_stock'] ? '₹'.number_format($row['price']) : 'OUT' ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div style="margin-top:30px;">
            <h3 style="font-size: 1rem; margin-bottom: 15px;">Quick Actions</h3>
            <div class="quick-actions-wrap">
                <a href="add-event.php" class="btn btn-primary" style="padding:15px; border-radius:15px; background:var(--primary); color:white;"><i class="fas fa-plus"></i> Event</a>
                <a href="add-flower.php" class="btn" style="padding:15px; border-radius:15px; background:var(--secondary); color:white;"><i class="fas fa-plus"></i> Flower</a>
                <a href="add-gallery.php" class="btn btn-outline" style="padding:15px; border-radius:15px; background:#fff; border:1px solid #ddd;"><i class="fas fa-camera"></i> Photo</a>
                <a href="orders.php" class="btn btn-outline" style="padding:15px; border-radius:15px; background:#fff; border:1px solid #ddd;"><i class="fas fa-shopping-bag"></i> Orders</a>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('growthChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Orders', 'Events', 'Flowers', 'Cakes', 'Gifts', 'Gallery', 'Blogs'],
                datasets: [{
                    data: [<?= $orders ?>, <?= $events ?>, <?= $flowers ?>, <?= $cakes ?>, <?= $gifts ?>, <?= $gallery ?>, <?= $blogs ?>],
                    backgroundColor: ['#d4af37', '#326e54', '#16a34a', '#b91c1c', '#7c3aed', '#e11d48', '#2563eb'],
                    borderRadius: 8,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f5f5f5' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>