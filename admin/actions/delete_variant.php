<?php
require_once __DIR__ . '/../auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';

csrf_verify_or_die();

$id = intval($_GET['id'] ?? 0);
$flower_id = intval($_GET['flower_id'] ?? 0);

if ($id > 0 && $flower_id > 0) {
    mysqli_query($conn, "DELETE FROM flower_variants WHERE id=$id AND flower_id=$flower_id");
}

header("Location: ../edit-flower.php?id=$flower_id&msg=variant_deleted");
exit;
