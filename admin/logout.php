<?php
session_start();

/* 1. Clear Session Variables */
$_SESSION = [];

/* 2. Destroy the Session on the Server */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

/* 3. CLEAR "REMEMBER ME" COOKIE (Critical Fix) */
if (isset($_COOKIE['admin_remember'])) {
    // Setting the time to past (time() - 3600) expires the cookie immediately
    setcookie("admin_remember", "", time() - 3600, "/");
}

/* 4. Redirect with a success message */
header("Location: login.php?msg=logged_out");
exit;