<?php
require_once 'auth_check.php';
require_once '../config.php';
// csrf_verify_or_die(); // Moved inside POST check below

// Force session start if not already active (needed for messages)
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$message = "";
$msgType = "";

// 11. Fetch All Promos
// Self-Healing Schema: Add is_featured if missing
$colCheck = $conn->query("SHOW COLUMNS FROM promo_codes LIKE 'is_featured'");
if($colCheck && $colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE promo_codes ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER status");
}
// Add new location columns
$cols = ['show_on_cakes', 'show_on_gifts', 'show_on_flowers'];
foreach($cols as $col) {
    $check = $conn->query("SHOW COLUMNS FROM promo_codes LIKE '$col'");
    if($check && $check->num_rows == 0) {
        $conn->query("ALTER TABLE promo_codes ADD COLUMN $col TINYINT(1) DEFAULT 0");
    }
}

// Add expiry_date and usage_limit columns
$checkExpiry = $conn->query("SHOW COLUMNS FROM promo_codes LIKE 'expiry_date'");
if($checkExpiry && $checkExpiry->num_rows == 0) {
    $conn->query("ALTER TABLE promo_codes ADD COLUMN expiry_date DATE DEFAULT NULL");
}
$checkLimit = $conn->query("SHOW COLUMNS FROM promo_codes LIKE 'usage_limit'");
if($checkLimit && $checkLimit->num_rows == 0) {
    $conn->query("ALTER TABLE promo_codes ADD COLUMN usage_limit INT DEFAULT NULL");
}

// Add coupon_code column to orders
$checkCoupon = $conn->query("SHOW COLUMNS FROM orders LIKE 'coupon_code'");
if($checkCoupon && $checkCoupon->num_rows == 0) {
    // Add it after status to be safe, or just add it
    $conn->query("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    

    if (isset($_POST['add_promo'])) {
        try {
            $code = strtoupper(trim($_POST['code']));
            $discount_text = trim($_POST['discount_text']);
            $description = trim($_POST['description']);
            $discount_type = $_POST['discount_type'];
            $discount_value = floatval($_POST['discount_value']);
            $min_order = floatval($_POST['min_order_value']);
            $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
            $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
            
            $is_featured = 0;
            $show_cakes = isset($_POST['show_on_cakes']) ? 1 : 0;
            $show_gifts = isset($_POST['show_on_gifts']) ? 1 : 0;
            $show_flowers = isset($_POST['show_on_flowers']) ? 1 : 0;

            if ($code && $discount_value) {
                // Previously this removed other featured codes, but we now allow multiple.
                // Prepare Statement Debugging
                // FIX: Updated column names to discount_type and discount_value
                $sql = "INSERT INTO promo_codes 
                (code, discount_type, discount_value, min_order_amount, discount_text, description, status, is_featured, show_on_cakes, show_on_gifts, show_on_flowers, expiry_date, usage_limit) 
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                
                if(!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("ssddssiiiisi", $code, $discount_type, $discount_value, $min_order, $discount_text, $description, $is_featured, $show_cakes, $show_gifts, $show_flowers, $expiry_date, $usage_limit);
                
                if ($stmt->execute()) {
                    $_SESSION['success_msg'] = "Promo code '$code' added successfully!";
                    echo "<script>window.location.href='promo-codes.php';</script>"; 
                    exit();
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $msgType = "error";
        }
    } elseif (isset($_POST['toggle_status'])) {
        $id = intval($_POST['id']);
        $current = intval($_POST['current_status']);
        $new = $current ? 0 : 1;
        $conn->query("UPDATE promo_codes SET status=$new WHERE id=$id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['delete_promo'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM promo_codes WHERE id=$id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch message from session (if redirect just happened)
if(isset($_SESSION['success_msg'])) {
    $message = $_SESSION['success_msg'];
    $msgType = "success";
    unset($_SESSION['success_msg']);
}

// Fetch All Promos
$promos = $conn->query("SELECT * FROM promo_codes ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Promo Codes'; include 'partials/head.php'; ?>
    <style>
        :root { --primary: #326e54; }
        .promo-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; border: 1px solid #f0f0f0; }
        .promo-info h3 { margin: 0; color: var(--primary); font-size: 1.2rem; font-weight: 800; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .badge.active { background: #e9f7ef; color: #27ae60; }
        .badge.inactive { background: #f3f4f6; color: #6b7280; }
        .condition-tag { font-size: 0.75rem; background: #f0fdf4; color: #166534; padding: 3px 10px; border-radius: 6px; font-weight: 700; margin-top: 8px; display: inline-block; }
        .btn-icon { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 8px; transition: 0.2s; color: #999; }
        .btn-icon:hover { transform: scale(1.1); color: var(--primary); }
    </style>
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h1 style="margin:0; font-weight: 800;">Promo Codes</h1>
            <p style="color:#777; margin:5px 0 0; font-size: 0.9rem;">Setup discounts and minimum order rules</p>
        </div>
        <button onclick="document.getElementById('addModal').style.display='flex'" class="btn-primary" style="background:var(--primary); color:white; border:none; padding:12px 25px; border-radius:12px; cursor:pointer; font-weight:bold; box-shadow: 0 4px 12px rgba(50,110,84,0.2);">
            <i class="fas fa-plus mr-2"></i> Create New
        </button>
    </div>

    <?php if ($message): ?>
        <div style="padding:15px; margin-bottom:25px; border-radius:12px; color:white; font-weight: bold; background:<?= $msgType=='success'?'#10b981':'#ef4444' ?>; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <i class="fas <?= $msgType=='success'?'fa-check-circle':'fa-exclamation-triangle' ?> mr-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="promo-list">
        <?php if ($promos && $promos->num_rows > 0): while($p = $promos->fetch_assoc()): ?>
            <div class="promo-card">
                <div class="promo-info">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <h3><?= htmlspecialchars($p['code'] ?? '') ?></h3>
                        <span class="badge <?= $p['status'] ? 'active' : 'inactive' ?>">
                            <?= $p['status'] ? 'Live' : 'Paused' ?>
                        </span>
                    </div>
                    <p style="margin: 8px 0; font-size: 0.95rem; color: #444;"><strong>Offer:</strong> <?= htmlspecialchars($p['discount_text'] ?? '') ?></p>
                    <div class="condition-tag">Min Order: ₹<?= number_format($p['min_order_amount'] ?? 0, 0) ?></div>
                    <div class="condition-tag" style="background:#eff6ff; color:#1e40af;">Type: <?= ucfirst($p['discount_type'] ?? 'percentage') ?></div>
                    <?php if(!empty($p['expiry_date'])): ?>
                        <div class="condition-tag" style="background:#fef2f2; color:#b91c1c; border:1px solid #fee2e2;">
                            <i class="fas fa-clock" style="margin-right:4px;"></i> Expires: <?= date('d M Y', strtotime($p['expiry_date'])) ?>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($p['usage_limit'])): ?>
                        <div class="condition-tag" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;">
                            <i class="fas fa-users" style="margin-right:4px;"></i> Limit: <?= $p['usage_limit'] ?> / User
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($p['show_on_cakes'])): ?>
                        <div class="condition-tag" style="background:#fce7f3; color:#db2777; border:1px solid #fbcfe8;">
                            <i class="fas fa-birthday-cake" style="margin-right:4px;"></i> Cakes
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($p['show_on_gifts'])): ?>
                        <div class="condition-tag" style="background:#ede9fe; color:#7c3aed; border:1px solid #ddd6fe;">
                            <i class="fas fa-gift" style="margin-right:4px;"></i> Gifts
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($p['show_on_flowers'])): ?>
                        <div class="condition-tag" style="background:#ecfccb; color:#4d7c0f; border:1px solid #d9f99d;">
                            <i class="fas fa-leaf" style="margin-right:4px;"></i> Flowers
                        </div>
                    <?php endif; ?>
                </div>
                <div class="actions" style="display: flex; gap: 5px;">
                    <form method="POST">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="current_status" value="<?= $p['status'] ?>">
                        <button type="submit" name="toggle_status" class="btn-icon" title="Toggle Status"><i class="fas fa-power-off"></i></button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Permanently delete this code?');">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" name="delete_promo" class="btn-icon" style="color:#f87171;" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div style="text-align:center; color:#ccc; padding: 80px 20px; background: white; border-radius: 20px;">
                <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.2;"></i>
                <p>No promo codes found. Click "Create New" to start.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:2000; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div style="background:white; padding:35px; border-radius:24px; width:95%; max-width:480px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h2 style="margin-top:0; font-weight: 800;">Create Promo Code</h2>
        <form method="POST">
            <?php csrf_field(); ?>
            <div style="margin-bottom:18px;">
                <label>PROMO CODE</label>
                <input type="text" name="code" required placeholder="e.g. FESTIVE20" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; text-transform:uppercase; font-weight: bold;">
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:18px;">
                <div>
                    <label>TYPE</label>
                    <select name="discount_type" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        <option value="percentage">Percentage (%)</option>
                        <option value="flat">Flat Amount (₹)</option>
                    </select>
                </div>
                <div>
                    <label>VALUE</label>
                    <input type="number" step="0.01" name="discount_value" required placeholder="20" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label>MINIMUM ORDER (₹)</label>
                <input type="number" step="0.01" name="min_order_value" value="0" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:18px;">
                <div>
                    <label>EXPIRY DATE (Optional)</label>
                    <input type="date" name="expiry_date" min="<?= date('Y-m-d') ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; font-family: inherit;">
                </div>
                <div>
                    <label>USAGE LIMIT / USER (Optional)</label>
                    <input type="number" name="usage_limit" min="1" placeholder="e.g. 1" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label>DISPLAY TEXT (User sees this)</label>
                <input type="text" name="discount_text" required placeholder="20% Off on orders above ₹1000" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
            </div>

            <div style="margin-bottom:25px;">
                <label>NOTES (Internal)</label>
                <textarea name="description" rows="2" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; resize: none;"></textarea>
            </div>
            
            <div style="margin-bottom:25px; background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e5e7eb;">
                <label style="display:block; margin-bottom:10px; color:#4b5563;">SHOW ON PAGES:</label>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="show_on_cakes" id="cake_chk" style="width:18px; height:18px;">
                        <label for="cake_chk" style="margin:0; font-weight:600; cursor:pointer;">Cakes Page</label>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="show_on_gifts" id="gift_chk" style="width:18px; height:18px;">
                        <label for="gift_chk" style="margin:0; font-weight:600; cursor:pointer;">Gifts Page</label>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="show_on_flowers" id="flower_chk" style="width:18px; height:18px;">
                        <label for="flower_chk" style="margin:0; font-weight:600; cursor:pointer;">Flowers Page</label>
                    </div>
                </div>
            </div>

            <button type="submit" name="add_promo" style="width:100%; padding:15px; background:var(--primary); color:white; border:none; border-radius:12px; font-weight:800; cursor:pointer; font-size: 1rem; transition: 0.3s;">Save Promo Code</button>
            <button type="button" onclick="document.getElementById('addModal').style.display='none'" style="width:100%; padding:12px; background:none; color:#999; border:none; margin-top:10px; cursor:pointer; font-weight: 600;">Discard</button>
        </form>
    </div>
</div>

</body>
</html>