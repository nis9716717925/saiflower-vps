<?php



require_once '../../config.php';
require_once '../auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

if (!isset($_GET['id'])) {
    header("Location: ../cakes.php");
    exit();
}

$id = intval($_GET['id']);

try {
    $conn->begin_transaction();

    // 1. Get Main Image
    $stmt = $conn->prepare("SELECT image FROM cakes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $cake = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($cake) {
        if (!empty($cake['image']) && file_exists('../../' . $cake['image'])) {
            unlink('../../' . $cake['image']);
        }
    }

    // 2. Delete Record
    $stmt = $conn->prepare("DELETE FROM cakes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $_SESSION['success'] = "Cake deleted successfully.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Delete failed: " . $e->getMessage();
}

header("Location: ../cakes.php");
exit();
?>
