<?php
// actions/save_addon.php



require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

/**
 * QUICK TOGGLE (AJAX or Link-based)
 * Handles instant visibility switching
 */
if (isset($_GET['toggle_id'])) {
    $id = intval($_GET['toggle_id']);
    $status = intval($_GET['status']);
    
    $stmt = $conn->prepare("UPDATE addons SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);
    
    if ($stmt->execute()) {
        header("Location: ../addons.php?msg=updated");
    } else {
        header("Location: ../addons.php?msg=error");
    }
    exit;
}

/**
 * SAVE / UPDATE ADDON
 * Handles the main form submission
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize & Prepare Data
    $id    = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name  = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $orig_price = floatval($_POST['original_price'] ?? 0);
    $status = isset($_POST['status']) ? 1 : (isset($_POST['name']) ? 1 : 0); // Default to active for new
    $icon = '';

    // 2. Validation
    if (empty($name) || $price <= 0) {
        header("Location: ../addons.php?msg=invalid");
        exit;
    }
    
    // 3. Handle File Upload
    if (isset($_FILES['addon_image']) && $_FILES['addon_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileTmp = $_FILES['addon_image']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['addon_image']['name']);
        $fileName = preg_replace('/[^A-Za-z0-9.\-_]/', '', $fileName); // Sanitize filename
        $destPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($fileTmp, $destPath)) {
            $icon = 'uploads/' . $fileName;
        }
    } else {
        // Fallback to existing icon if updating, or generic if new
        $icon = trim($_POST['icon'] ?? ($id > 0 ? '' : 'fa-gift'));
        if ($id > 0 && empty($icon)) {
            $stmt = $conn->prepare("SELECT icon FROM addons WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            if ($r = $stmt->get_result()->fetch_assoc()) {
                $icon = $r['icon'];
            }
            $stmt->close();
        }
    }

    if ($id > 0) {
        // UPDATE EXISTING
        $stmt = $conn->prepare("UPDATE addons SET name=?, price=?, original_price=?, icon=?, status=? WHERE id=?");
        $stmt->bind_param("sddsii", $name, $price, $orig_price, $icon, $status, $id);
    } else {
        // INSERT NEW
        $stmt = $conn->prepare("INSERT INTO addons (name, price, original_price, icon, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sddsi", $name, $price, $orig_price, $icon, $status);
    }
    
    // 4. Execute & Redirect
    if ($stmt->execute()) {
        header("Location: ../addons.php?msg=success");
    } else {
        // Log error if necessary
        header("Location: ../addons.php?msg=db_error");
    }
    exit;
}