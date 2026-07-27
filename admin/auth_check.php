<?php
/* Conditional Session Start to prevent redundant call notices */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| REMEMBER ME LOGIC
|--------------------------------------------------------------------------
*/
if(!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['admin_remember'])){
    list($admin_id, $token) = explode(':', $_COOKIE['admin_remember']);
    $admin_id = intval($admin_id);
    $tokenHash = hash('sha256', $token);
    
    // Ensure config is loaded because auth_check is often included BEFORE config
    if (!isset($conn)) {
        require_once __DIR__ . '/../config.php';
    }
    
    global $conn; 
    if ($conn) {
        $stmt = $conn->prepare("SELECT id FROM admin_tokens WHERE admin_id = ? AND token = ? AND expiry > NOW()");
        $stmt->bind_param("is", $admin_id, $tokenHash);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['last_activity'] = time();
        }
    }
}

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/
$session_timeout = 900; // 15 minutes inactivity auto logout

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AUTO LOGOUT ON INACTIVITY
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $session_timeout) {

    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

/* Update last activity time */
$_SESSION['last_activity'] = time();