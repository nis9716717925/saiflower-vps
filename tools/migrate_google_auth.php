<?php
/**
 * One-shot schema migration for Google Sign-In columns on `customers`.
 * Safe to run multiple times. Requires an active admin session.
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/google_auth.php';

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die('Forbidden — admin login required.');
}

header('Content-Type: text/plain; charset=utf-8');

try {
    google_ensure_customer_schema($conn);
    echo "OK: customers table ready for Google Sign-In.\n";
    echo "Columns ensured: google_id, auth_provider, avatar_url\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Migration failed: ' . $e->getMessage();
}
