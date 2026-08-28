<?php
// config.php — shared storefront + PHP admin bootstrap (PostgreSQL via bridge)

require_once __DIR__ . '/includes/pg_mysqli_bridge.php';

try {
    $conn = pg_mysqli_connect_from_env();
} catch (Throwable $e) {
    error_log('Database Connection Failed: ' . $e->getMessage());
    die('<div style="text-align:center; padding:50px; font-family:sans-serif;">
        <h1>Service Temporarily Unavailable</h1>
        <p>We are currently upgrading our systems. Please check back in a few minutes.</p>
        <p style="color:#999; font-size:12px;">Error Code: DB_CONN_ERR</p>
    </div>');
}

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error_log.txt');

if (!defined('SKIP_SESSION') || !SKIP_SESSION) {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }
}

require_once __DIR__ . '/includes/csrf_helper.php';
require_once __DIR__ . '/includes/url_helper.php';
require_once __DIR__ . '/includes/seo_helper.php';
require_once __DIR__ . '/includes/pricing_helper.php';
require_once __DIR__ . '/includes/shipping_config.php';
