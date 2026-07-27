<?php
// Redirect to the correct file with underscores
$queryString = $_SERVER['QUERY_STRING'];
header("Location: add_item.php?" . $queryString);
exit;
?>
