<?php
/**
 * POST /actions/google_auth.php
 * Body (JSON or form): credential (Google ID token), csrf_token, redirect (optional)
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/config.php';

function google_auth_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    google_auth_json(405, ['success' => false, 'message' => 'Method not allowed.']);
}

if (!empty($_SESSION['customer_id'])) {
    google_auth_json(200, [
        'success' => true,
        'message' => 'Already signed in.',
        'redirect' => google_safe_redirect($_POST['redirect'] ?? null, '/'),
    ]);
}

$raw = file_get_contents('php://input');
$input = [];
if ($raw && str_starts_with(trim($raw), '{')) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}
if (!$input) {
    $input = $_POST;
}

$csrf = (string) ($input['csrf_token'] ?? '');
$credential = trim((string) ($input['credential'] ?? ''));
$redirect = isset($input['redirect']) ? (string) $input['redirect'] : '';

if (!verify_csrf_token($csrf)) {
    google_auth_json(403, [
        'success' => false,
        'message' => 'Session expired. Please refresh the page and try again.',
        'code' => 'csrf',
    ]);
}

if ($credential === '') {
    google_auth_json(400, [
        'success' => false,
        'message' => 'Missing Google credential.',
        'code' => 'missing_credential',
    ]);
}

// Basic rate limit per session
$_SESSION['google_auth_attempts'] = (int) ($_SESSION['google_auth_attempts'] ?? 0);
$_SESSION['google_auth_window_start'] = (int) ($_SESSION['google_auth_window_start'] ?? time());
if ((time() - $_SESSION['google_auth_window_start']) > 300) {
    $_SESSION['google_auth_attempts'] = 0;
    $_SESSION['google_auth_window_start'] = time();
}
$_SESSION['google_auth_attempts']++;
if ($_SESSION['google_auth_attempts'] > 20) {
    google_auth_json(429, [
        'success' => false,
        'message' => 'Too many sign-in attempts. Please wait a few minutes.',
        'code' => 'rate_limited',
    ]);
}

try {
    $expectedNonce = $_SESSION['google_auth_nonce'] ?? null;
    if ($expectedNonce === null || $expectedNonce === '') {
        google_auth_json(401, [
            'success' => false,
            'message' => 'Invalid sign-in session. Please refresh and try again.',
            'code' => 'nonce_missing',
        ]);
    }

    $payload = google_verify_id_token($credential, (string) $expectedNonce);

    if (!google_consume_nonce((string) ($payload['nonce'] ?? ''))) {
        google_auth_json(401, [
            'success' => false,
            'message' => 'Invalid or reused sign-in request. Please try again.',
            'code' => 'nonce',
        ]);
    }

    $customer = google_find_or_create_customer($conn, $payload);
    google_start_customer_session($customer);

    $_SESSION['google_auth_attempts'] = 0;

    $safeRedirect = google_safe_redirect($redirect !== '' ? $redirect : null, '/');

    google_auth_json(200, [
        'success' => true,
        'message' => 'Signed in successfully.',
        'redirect' => $safeRedirect,
        'user' => [
            'id' => (int) $customer['id'],
            'name' => (string) $customer['name'],
            'email' => (string) $customer['email'],
        ],
    ]);
} catch (InvalidArgumentException $e) {
    error_log('Google auth rejected: ' . $e->getMessage());
    google_auth_json(401, [
        'success' => false,
        'message' => 'Google sign-in could not be verified. Please try again.',
        'code' => 'invalid_token',
    ]);
} catch (Throwable $e) {
    error_log('Google auth error: ' . $e->getMessage());
    google_auth_json(500, [
        'success' => false,
        'message' => 'Something went wrong during Google sign-in. Please try again.',
        'code' => 'server_error',
    ]);
}
