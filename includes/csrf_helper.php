<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Generate CSRF Token
|--------------------------------------------------------------------------
*/
if (!function_exists('generate_csrf_token')) {
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
        }
    }
    return $_SESSION['csrf_token'];
}
}

/*
|--------------------------------------------------------------------------
| Alias for backward compatibility (old code using csrf_token())
|--------------------------------------------------------------------------
*/
if (!function_exists('csrf_token')) {
function csrf_token(){
    return generate_csrf_token();
}
}

/*
|--------------------------------------------------------------------------
| CSRF Hidden Field
|--------------------------------------------------------------------------
*/
if (!function_exists('csrf_field')) {
function csrf_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
}

/*
|--------------------------------------------------------------------------
| Verify CSRF Token (secure compare)
|--------------------------------------------------------------------------
*/
if (!function_exists('verify_csrf_token')) {
function verify_csrf_token($token = null){
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
    }

    if (!$token || !isset($_SESSION['csrf_token'])) {
        return false;
    }

    // constant time comparison
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }

    return true;
}
}

/*
|--------------------------------------------------------------------------
| Strict verify (use in forms that MUST be valid)
|--------------------------------------------------------------------------
*/
if (!function_exists('csrf_verify_or_die')) {
function csrf_verify_or_die(){
    if(!verify_csrf_token()){
        http_response_code(403);
        die('Invalid CSRF token');
    }
}
}

/*
|--------------------------------------------------------------------------
| Optional: regenerate token after successful form submit
|--------------------------------------------------------------------------
*/
if (!function_exists('csrf_regenerate')) {
function csrf_regenerate(){
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
    }
}
}
?>
