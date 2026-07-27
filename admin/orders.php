<?php
// admin/orders.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// Handle Status Update
if(isset($_POST['update_status']) && isset($_POST['order_id'])) {
    $oid = (int)$_POST['order_id'];
    $sts = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $sts, $oid);
    $stmt->execute();
    header("Location: orders.php?msg=Status Updated");
    exit;
}

// Fetch Orders
$orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Orders'; include 'partials/head.php'; ?>
    <style>
        :root { --primary: #326e54; --bg: #f4f7f6; --sidebar-width: 260px; }
        * { box-sizing: border-box; }
        body { background: var(--bg); margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        
        .admin-main { margin-left: var(--sidebar-width); padding: 30px; min-height: 100vh; transition: 0.3s; }
        .order-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #eee; }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f5f5f5; padding-bottom: 15px; margin-bottom: 15px; }
        .order-id { font-weight: 800; font-size: 1.1rem; color: var(--primary); }
        .order-date { font-size: 0.85rem; color: #888; }
        
        .order-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-group h4 { margin: 0 0 10px 0; font-size: 0.8rem; color: #aaa; text-transform: uppercase; letter-spacing: 1px; }
        .info-group p { margin: 0 0 5px 0; font-size: 0.95rem; font-weight: 500; color: #333; }
        
        .items-box { background: #fafafa; padding: 15px; border-radius: 10px; margin-top: 15px; white-space: pre-wrap; font-family: monospace; font-size: 0.9rem; color: #555; }
        
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
        .status-Pending { background: #fff7ed; color: #c2410c; }
        .status-Completed { background: #f0fdf4; color: #15803d; }
        .status-Cancelled { background: #fef2f2; color: #b91c1c; }

        .order-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            border-top: 1px solid #f5f5f5;
            padding-top: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        @media(max-width: 768px) { 
            .admin-main { margin-left: 0; padding: 20px 15px 90px; } 
            .order-grid { grid-template-columns: 1fr; }
            .order-actions { flex-direction: column; align-items: stretch; }
            .order-actions form { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%; }
            .order-actions form select { grid-column: 1 / -1; }
            .total-amt { text-align: center; margin-bottom: 5px; }
        }
    </style>
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <h2 style="margin-bottom:20px;">All Orders</h2>

    <?php if(mysqli_num_rows($orders) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($orders)): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-id">#<?= $row['id'] ?></div>
                    <div class="order-date"><i class="far fa-clock"></i> <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></div>
                </div>
                <div>
                    <span class="status-badge status-<?= $row['status'] ?>"><?= $row['status'] ?></span>
                </div>
            </div>
            
            <div class="order-grid">
                <div class="info-group">
                    <h4>Customer</h4>
                    <p><i class="fas fa-user"></i> <?= htmlspecialchars($row['customer_name']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($row['customer_phone']) ?></p>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['customer_email']) ?></p>
                </div>
                <div class="info-group">
                    <h4>Delivery</h4>
                    <p><i class="fas fa-calendar-day"></i> <?= htmlspecialchars($row['delivery_date']) ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['delivery_address']) ?></p>
                </div>
            </div>

            <div class="items-box"><?= htmlspecialchars($row['order_items']) ?></div>
            
            <div class="order-actions">
                <div class="total-amt" style="font-size:1.2rem; font-weight:800; color:var(--primary);">Total: ₹<?= number_format($row['total_amount']) ?></div>
                
                <form method="POST" style="display:flex; gap:10px;">
                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                    <select name="status" style="padding:8px; border-radius:8px; border:1px solid #ddd;">
                        <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                        <option value="Completed" <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
                        <option value="Cancelled" <?= $row['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn" style="background:var(--primary); color:white; padding:8px 15px; border-radius:8px; border:none; cursor:pointer;">Update</button>
                    <a href="https://wa.me/91<?= $row['customer_phone'] ?>?text=Hi <?= urlencode($row['customer_name']) ?>, regarding your order #<?= $row['id'] ?>..." target="_blank" class="btn" style="background:#25d366; color:white; padding:8px 15px; border-radius:8px; text-decoration:none; text-align:center;"><i class="fab fa-whatsapp"></i> Chat</a>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align:center; padding:50px; color:#999;">No orders found.</div>
    <?php endif; ?>

</main>

</body>
</html>
