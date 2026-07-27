<?php
// Redirect to dashboard if logged in, otherwise login
session_start();
if(isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
?>
