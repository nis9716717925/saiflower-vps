<?php
require_once 'auth_check.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/pricing_helper.php';

// ENABLE STRICT MODE
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$msgType = "";

// 1. HANDLE ROLLBACK ACTION
if (isset($_GET['rollback_id'])) {
    csrf_verify_or_die();
    $rollback_id = intval($_GET['rollback_id']);
    
    $res = $conn->query("SELECT * FROM pricing_log WHERE id = $rollback_id LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $log = $res->fetch_assoc();
        $cat = $log['category'];
        
        // To "rollback" a surge of X%, we basically reset it to 0 or its previous state.
        // For simplicity and safety "Rollback" here will reset that specific category to 0.
        $col = "";
        if ($cat === 'flower') $col = "flower_surge";
        elseif ($cat === 'cake') $col = "cake_surge";
        elseif ($cat === 'gift') $col = "gift_surge";
        else $col = "surge_percentage";
        
        $stmt = $conn->prepare("UPDATE global_pricing SET $col = 0.00 WHERE id = 1");
        if ($stmt->execute()) {
            // Log the rollback
            $lStmt = $conn->prepare("INSERT INTO pricing_log (category, percentage, action) VALUES (?, 0.00, 'revert')");
            $lStmt->bind_param("s", $cat);
            $lStmt->execute();
            
            $message = "Successfully reverted " . ucfirst($cat) . " pricing to Normal state.";
            $msgType = "success";
        }
    }
}

// 2. HANDLE BULK UPDATE ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    csrf_verify_or_die();
    
    if(empty($_POST['confirm']) || strtoupper($_POST['confirm']) !== 'YES') {
        $message = "Please type 'YES' to confirm this bulk action.";
        $msgType = "error";
    } else {
        $action_type = $_POST['action_type'];
        $product_type = $_POST['product_type'];
        $percent = isset($_POST['percentage']) ? floatval($_POST['percentage']) : 0;
        
        if ($action_type === 'percentage' && $percent == 0) {
            $message = "Surge percentage cannot be zero.";
            $msgType = "error";
        } else {
            $surge_val = ($action_type === 'reset') ? 0.00 : $percent;
            
            $col = "surge_percentage"; // Default to 'all'
            if ($product_type === 'flowers') $col = "flower_surge";
            elseif ($product_type === 'cakes') $col = "cake_surge";
            elseif ($product_type === 'gifts') $col = "gift_surge";

            $stmt = $conn->prepare("UPDATE global_pricing SET $col = ? WHERE id = 1");
            $stmt->bind_param("d", $surge_val);
            
            if ($stmt->execute()) {
                // Log the change
                $action_name = ($action_type === 'reset') ? 'reset' : 'surge';
                $lStmt = $conn->prepare("INSERT INTO pricing_log (category, percentage, action) VALUES (?, ?, ?)");
                $log_cat = ($product_type === 'all') ? 'all' : rtrim($product_type, 's');
                $lStmt->bind_param("sds", $log_cat, $surge_val, $action_name);
                $lStmt->execute();
                
                if ($action_type === 'reset') {
                    $message = "Successfully restored " . ucfirst($product_type ?: 'Storefront') . " to Morning Normal state.";
                } else {
                    $message = "Successfully activated a {$percent}% surge for " . ucfirst($product_type ?: 'Storefront') . "!";
                }
                $msgType = "success";
            } else {
                $message = "Database error: Failed to update global pricing.";
                $msgType = "error";
            }
        }
    }
}

// 3. FETCH CURRENT STATUS
$res = $conn->query("SELECT * FROM global_pricing WHERE id = 1");
$pricing = $res->fetch_assoc();

// 4. FETCH HISTORY
$history = $conn->query("SELECT * FROM pricing_log ORDER BY created_at DESC LIMIT 10");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Dynamic Pricing Engine'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #2f6f4e;
            --primary-light: #ecfdf5;
            --secondary: #d4af37;
            --danger: #ef4444;
            --dark: #1e293b;
            --gray: #64748b;
            --border: #e2e8f0;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .tool-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .pricing-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .status-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid var(--border);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .status-card:hover { transform: translateY(-5px); }
        .status-card.active { border-color: var(--primary); background: var(--primary-light); }
        
        .status-card .label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray); margin-bottom: 10px; display: block; }
        .status-card .value { font-size: 2rem; font-weight: 800; color: var(--dark); }
        .status-card .unit { font-size: 1rem; color: var(--gray); margin-left: 2px; }
        .status-card .indicator { margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.8rem; font-weight: 600; }
        .indicator.on { color: var(--primary); }
        .indicator.off { color: var(--gray); opacity: 0.6; }

        .main-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 40px;
        }

        .card-header {
            background: var(--dark);
            color: white;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h2 { margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 12px; }
        
        .card-body { padding: 40px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .pricing-status-grid { grid-template-columns: 1fr 1fr; }
        }

        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; font-weight: 700; margin-bottom: 10px; color: var(--dark); font-size: 0.9rem; }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(47, 111, 78, 0.1);
        }

        .btn-execute {
            background: var(--primary);
            color: white;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: 0.3s;
        }

        .btn-execute:hover { background: #23543d; transform: translateY(-2px); }
        
        .history-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
            padding: 30px;
        }

        .history-card h3 { margin-bottom: 20px; font-weight: 800; display: flex; align-items: center; gap: 10px; }

        .history-table { width: 100%; border-collapse: collapse; }
        .history-table th { text-align: left; padding: 15px; border-bottom: 2px solid var(--border); font-size: 0.8rem; color: var(--gray); text-transform: uppercase; }
        .history-table td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }

        .badge-type { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .type-surge { background: #fff7ed; color: #9a3412; }
        .type-reset { background: #f0fdf4; color: #166534; }
        .type-revert { background: #f1f5f9; color: #475569; }

        .btn-rollback {
            background: white;
            border: 1px solid var(--border);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--danger);
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn-rollback:hover { background: #fef2f2; border-color: #fecaca; }

        .alert {
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
        }
        .alert.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="tool-container">
        
        <div class="pricing-status-grid">
            <div class="status-card <?= $pricing['surge_percentage'] > 0 ? 'active' : '' ?>">
                <span class="label">General Surge</span>
                <span class="value"><?= number_format($pricing['surge_percentage'], 1) ?><span class="unit">%</span></span>
                <div class="indicator <?= $pricing['surge_percentage'] > 0 ? 'on' : 'off' ?>">
                    <i class="fas fa-circle text-[8px]"></i> <?= $pricing['surge_percentage'] > 0 ? 'Active' : 'Neutral' ?>
                </div>
            </div>
            <div class="status-card <?= $pricing['flower_surge'] > 0 ? 'active' : '' ?>">
                <span class="label">Flowers Surge</span>
                <span class="value"><?= number_format($pricing['flower_surge'], 1) ?><span class="unit">%</span></span>
                <div class="indicator <?= $pricing['flower_surge'] > 0 ? 'on' : 'off' ?>">
                    <i class="fas fa-circle text-[8px]"></i> <?= $pricing['flower_surge'] > 0 ? 'Active' : 'Neutral' ?>
                </div>
            </div>
            <div class="status-card <?= $pricing['cake_surge'] > 0 ? 'active' : '' ?>">
                <span class="label">Cakes Surge</span>
                <span class="value"><?= number_format($pricing['cake_surge'], 1) ?><span class="unit">%</span></span>
                <div class="indicator <?= $pricing['cake_surge'] > 0 ? 'on' : 'off' ?>">
                    <i class="fas fa-circle text-[8px]"></i> <?= $pricing['cake_surge'] > 0 ? 'Active' : 'Neutral' ?>
                </div>
            </div>
            <div class="status-card <?= $pricing['gift_surge'] > 0 ? 'active' : '' ?>">
                <span class="label">Gifts Surge</span>
                <span class="value"><?= number_format($pricing['gift_surge'], 1) ?><span class="unit">%</span></span>
                <div class="indicator <?= $pricing['gift_surge'] > 0 ? 'on' : 'off' ?>">
                    <i class="fas fa-circle text-[8px]"></i> <?= $pricing['gift_surge'] > 0 ? 'Active' : 'Neutral' ?>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= $msgType ?>">
                <i class="fas <?= $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="main-card">
            <div class="card-header">
                <h2><i class="fas fa-bolt text-yellow-400"></i> Execution Engine</h2>
                <div style="font-size: 0.8rem; font-weight: 700; background: rgba(255,255,255,0.1); padding: 5px 12px; border-radius: 50px;">v3.0 Accurate Engine</div>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div class="form-grid">
                        <div class="side-settings">
                            <div class="input-group">
                                <label>1. Select Phase</label>
                                <select name="action_type" id="actionType" class="form-control" onchange="toggleInputs()">
                                    <option value="percentage">🌙 Apply Night Surge</option>
                                    <option value="reset">☀️ Morning Reset (0%)</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>2. Product Category</label>
                                <select name="product_type" class="form-control">
                                    <option value="all">🌐 Entire Inventory</option>
                                    <option value="flowers">🌸 Flowers Only</option>
                                    <option value="cakes">🎂 Cakes Only</option>
                                    <option value="gifts">🎁 Gifts Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="main-inputs">
                            <div id="surgeInputs">
                                <div class="input-group">
                                    <label>3. Surge Percentage (%)</label>
                                    <input type="number" name="percentage" step="0.5" min="0" max="100" class="form-control" placeholder="e.g. 15.0">
                                    <p style="font-size: 0.75rem; color: var(--gray); margin-top: 8px;">Recommended: 10% - 25% for high-demand periods.</p>
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <label>4. Safety Confirmation</label>
                                <input type="text" name="confirm" class="form-control" placeholder="Type 'YES' to unlock action" autocomplete="off" required>
                                <p style="font-size: 0.75rem; color: var(--danger); font-weight: 700; margin-top: 8px;"><i class="fas fa-shield-alt"></i> Final check prevents accidental price shifts.</p>
                            </div>

                            <button type="submit" class="btn-execute">
                                <i class="fas fa-rocket"></i> Launch Strategy
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="history-card">
            <h3><i class="fas fa-history text-blue-500"></i> Operation History</h3>
            <div style="overflow-x: auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>DateTime</th>
                            <th>Target</th>
                            <th>Value</th>
                            <th>Action</th>
                            <th>Safety</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $history->fetch_assoc()): ?>
                        <tr>
                            <td class="font-bold"><?= date('M d, H:i', strtotime($row['created_at'])) ?></td>
                            <td class="font-bold text-dark"><?= ucfirst($row['category']) ?></td>
                            <td class="font-black text-primary"><?= $row['percentage'] ?>%</td>
                            <td><span class="badge-type type-<?= $row['action'] ?>"><?= $row['action'] ?></span></td>
                            <td>
                                <?php if($row['action'] === 'surge'): ?>
                                    <a href="?rollback_id=<?= $row['id'] ?>&csrf_token=<?= csrf_token() ?>" onclick="return confirm('Immediately reset this category to 0%?')" class="btn-rollback">Rollback</a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1; font-size: 0.7rem; font-weight: 700;">SECURED</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="text-align:center; margin-top:40px;">
            <a href="dashboard.php" style="text-decoration:none; color: var(--gray); font-size: 0.9rem; font-weight: 700;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

    </div>
</main>

<script>
function toggleInputs() {
    const type = document.getElementById('actionType').value;
    const surgeInputs = document.getElementById('surgeInputs');
    const pInput = surgeInputs.querySelector('input');
    
    if (type === 'reset') {
        surgeInputs.style.opacity = '0.4';
        surgeInputs.style.pointerEvents = 'none';
        pInput.value = '0';
        pInput.removeAttribute('required');
    } else {
        surgeInputs.style.opacity = '1';
        surgeInputs.style.pointerEvents = 'auto';
        pInput.setAttribute('required', 'required');
    }
}
</script>

</body>
</html>
