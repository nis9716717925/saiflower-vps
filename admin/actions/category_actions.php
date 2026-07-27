<?php
// actions/category_actions.php
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF validation failed.");
    }

    if ($action === 'add' || $action === 'edit') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $status = intval($_POST['status']);
        $sort_order = intval($_POST['sort_order']);
        
        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/categories/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                $image_name = ''; // Reset if upload fails
            }
        }

        if ($action === 'add') {
            $sql = "INSERT INTO categories (name, image, status, sort_order) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $name, $image_name, $status, $sort_order);
        } else {
            if ($image_name) {
                // Remove old image if needed
                $old_q = $conn->query("SELECT image FROM categories WHERE id = $id");
                $old_res = $old_q->fetch_assoc();
                if ($old_res && $old_res['image'] && file_exists(__DIR__ . '/../../uploads/categories/' . $old_res['image'])) {
                    unlink(__DIR__ . '/../../uploads/categories/' . $old_res['image']);
                }
                $sql = "UPDATE categories SET name = ?, image = ?, status = ?, sort_order = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiii", $name, $image_name, $status, $sort_order, $id);
            } else {
                $sql = "UPDATE categories SET name = ?, status = ?, sort_order = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siii", $name, $status, $sort_order, $id);
            }
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Category saved successfully.";
        } else {
            $_SESSION['error'] = "Error saving category: " . $conn->error;
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        // Remove image
        $old_q = $conn->query("SELECT image FROM categories WHERE id = $id");
        $old_res = $old_q->fetch_assoc();
        if ($old_res && $old_res['image'] && file_exists(__DIR__ . '/../../uploads/categories/' . $old_res['image'])) {
            unlink(__DIR__ . '/../../uploads/categories/' . $old_res['image']);
        }
        
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Category deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting category.";
        }
    }
    
    header("Location: ../categories.php");
    exit;
}
