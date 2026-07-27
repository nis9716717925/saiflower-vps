<?php
// Redirect to the correct file with underscores
$queryString = $_SERVER['QUERY_STRING'];
header("Location: edit_item.php?" . $queryString);
exit;
?>
