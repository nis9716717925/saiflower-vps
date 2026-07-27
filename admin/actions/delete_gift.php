<?php



require_once '../../config.php';
require_once '../auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

if (!isset($_GET['id'])) {
    header("Location: ../gifts.php");
    exit();
}

$id = intval($_GET['id']);

try {
    $conn->begin_transaction();

    // 1. Get Main Image
    $stmt = $conn->prepare("SELECT image FROM gifts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $gift = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($gift) {
        if (!empty($gift['image']) && file_exists('../../' . $gift['image'])) {
            unlink('../../' . $gift['image']);
        }
    }

    // 2. Delete Record
    $stmt = $conn->prepare("DELETE FROM gifts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $_SESSION['success'] = "Gift deleted successfully.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Delete failed: " . $e->getMessage();
}

header("Location: ../gifts.php");
exit();
?>
