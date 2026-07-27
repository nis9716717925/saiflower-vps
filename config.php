<?php
// config.php - Main Configuration File

// 1. Database Connection
try {
    // 1. Database Connection
    $driver = new stdClass();
    $conn = new mysqli("localhost", "u977002836_Saiflower999", "Saiflower999", "u977002836_Saiflower999");
} catch (mysqli_sql_exception $e) {
    // Log error but don't expose sensitive info
    error_log("Database Connection Failed: " . $e->getMessage());
    die('<div style="text-align:center; padding:50px; font-family:sans-serif;">
        <h1>Service Temporarily Unavailable</h1>
        <p>We are currently upgrading our systems. Please check back in a few minutes.</p>
        <p style="color:#999; font-size:12px;">Error Code: DB_CONN_ERR</p>
    </div>');
}

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Service temporarily unavailable. Please try again later.");
}

// 2. Security & Error Handling
// In production, display_errors should be 0
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// 3. Session Management (skipped for sitemap / machine endpoints)
if (!defined('SKIP_SESSION') || !SKIP_SESSION) {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        session_start();
    }
}

// 4. Load Security Helpers
require_once __DIR__ . '/includes/csrf_helper.php';
require_once __DIR__ . '/includes/url_helper.php';
require_once __DIR__ . '/includes/seo_helper.php';
require_once __DIR__ . '/includes/pricing_helper.php';
require_once __DIR__ . '/includes/shipping_config.php';
require_once __DIR__ . '/includes/google_auth.php';

// 5. Caching Headers (Optimized for Performance)
// Removed global no-cache headers to allow browser caching of side assets.