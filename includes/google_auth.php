<?php
/**
 * Google Identity Services (GIS) — server-side helpers for Sai Flowers.
 * Verifies Google ID tokens (signature, aud, iss, exp) and links/creates customers.
 */

if (!defined('GOOGLE_OAUTH_CLIENT_ID')) {
    define(
        'GOOGLE_OAUTH_CLIENT_ID',
        '591122868014-s8k3fdmgnb8kl186vpnner41d6bisb9b.apps.googleusercontent.com'
    );
}

if (!defined('GOOGLE_JWKS_URL')) {
    define('GOOGLE_JWKS_URL', 'https://www.googleapis.com/oauth2/v3/certs');
}

if (!defined('GOOGLE_JWKS_CACHE_TTL')) {
    define('GOOGLE_JWKS_CACHE_TTL', 3600);
}

/**
 * Ensure customers table has Google auth columns (self-healing).
 */
function google_ensure_customer_schema(mysqli $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $conn->query("CREATE TABLE IF NOT EXISTS `customers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL UNIQUE,
        `phone` varchar(20) DEFAULT NULL,
        `password` varchar(255) NOT NULL,
        `address` text,
        `is_verified` tinyint(1) DEFAULT 0,
        `verification_token` varchar(255) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $existing = [];
    $res = $conn->query('SHOW COLUMNS FROM `customers`');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $existing[strtolower($row['Field'])] = true;
        }
        $res->free();
    }

    $alters = [];
    if (empty($existing['google_id'])) {
        $alters[] = 'ADD COLUMN `google_id` varchar(255) DEFAULT NULL';
    }
    if (empty($existing['auth_provider'])) {
        $alters[] = "ADD COLUMN `auth_provider` varchar(32) NOT NULL DEFAULT 'local'";
    }
    if (empty($existing['avatar_url'])) {
        $alters[] = 'ADD COLUMN `avatar_url` varchar(512) DEFAULT NULL';
    }

    if ($alters) {
        @$conn->query('ALTER TABLE `customers` ' . implode(', ', $alters));
    }

    // Unique index on google_id (ignore if already present)
    $idx = $conn->query("SHOW INDEX FROM `customers` WHERE Key_name = 'uniq_customers_google_id'");
    if ($idx && $idx->num_rows === 0) {
        @$conn->query('ALTER TABLE `customers` ADD UNIQUE KEY `uniq_customers_google_id` (`google_id`)');
    }
    if ($idx) {
        $idx->free();
    }

    $done = true;
}

/**
 * Create / return a one-time nonce for GIS (replay protection).
 */
function google_auth_nonce(bool $rotate = false): string
{
    if ($rotate || empty($_SESSION['google_auth_nonce'])) {
        $_SESSION['google_auth_nonce'] = bin2hex(random_bytes(16));
        $_SESSION['google_auth_nonce_created'] = time();
    }
    return $_SESSION['google_auth_nonce'];
}

/**
 * Consume and validate a nonce from the ID token (GIS SHA-256 hashes it).
 */
function google_consume_nonce(?string $tokenNonce): bool
{
    if ($tokenNonce === null || $tokenNonce === '') {
        return false;
    }
    $expected = $_SESSION['google_auth_nonce'] ?? null;
    $created = (int) ($_SESSION['google_auth_nonce_created'] ?? 0);
    unset($_SESSION['google_auth_nonce'], $_SESSION['google_auth_nonce_created']);

    if (!$expected) {
        return false;
    }

    $expectedHash = hash('sha256', $expected);
    $ok = hash_equals($expectedHash, $tokenNonce) || hash_equals($expected, $tokenNonce);
    if (!$ok) {
        return false;
    }
    // Nonce older than 10 minutes is rejected
    if ($created > 0 && (time() - $created) > 600) {
        return false;
    }
    return true;
}

/**
 * Fetch and cache Google JWKS.
 *
 * @return array<string, array>
 */
function google_fetch_jwks(): array
{
    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'saiflower_google_jwks.json';

    if (is_readable($cacheFile)) {
        $raw = @file_get_contents($cacheFile);
        $cached = $raw ? json_decode($raw, true) : null;
        if (
            is_array($cached)
            && !empty($cached['keys'])
            && !empty($cached['fetched_at'])
            && (time() - (int) $cached['fetched_at']) < GOOGLE_JWKS_CACHE_TTL
        ) {
            return $cached['keys'];
        }
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 8,
            'header' => "Accept: application/json\r\nUser-Agent: SaiFlower-GoogleAuth/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $body = @file_get_contents(GOOGLE_JWKS_URL, false, $ctx);
    if ($body === false && function_exists('curl_init')) {
        $ch = curl_init(GOOGLE_JWKS_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: SaiFlower-GoogleAuth/1.0'],
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
    }

    if ($body === false || $body === '') {
        throw new RuntimeException('Unable to fetch Google signing keys.');
    }

    $data = json_decode($body, true);
    if (!is_array($data) || empty($data['keys']) || !is_array($data['keys'])) {
        throw new RuntimeException('Invalid Google JWKS response.');
    }

    $byKid = [];
    foreach ($data['keys'] as $key) {
        if (!empty($key['kid'])) {
            $byKid[$key['kid']] = $key;
        }
    }

    @file_put_contents(
        $cacheFile,
        json_encode(['fetched_at' => time(), 'keys' => $byKid]),
        LOCK_EX
    );

    return $byKid;
}

/**
 * Convert a JWK RSA key to PEM.
 */
function google_jwk_to_pem(array $jwk): string
{
    if (($jwk['kty'] ?? '') !== 'RSA' || empty($jwk['n']) || empty($jwk['e'])) {
        throw new RuntimeException('Unsupported Google JWK.');
    }

    $n = google_base64url_decode($jwk['n']);
    $e = google_base64url_decode($jwk['e']);

    $modulus = google_asn1_integer($n);
    $exponent = google_asn1_integer($e);
    $rsaPublicKey = google_asn1_sequence($modulus . $exponent);
    $algorithmIdentifier = hex2bin('300d06092a864886f70d0101010500'); // rsaEncryption NULL
    $bitString = google_asn1_bit_string($rsaPublicKey);
    $spki = google_asn1_sequence($algorithmIdentifier . $bitString);

    $pem = "-----BEGIN PUBLIC KEY-----\n"
        . chunk_split(base64_encode($spki), 64, "\n")
        . "-----END PUBLIC KEY-----\n";

    return $pem;
}

function google_base64url_decode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    $decoded = base64_decode(strtr($data, '-_', '+/'), true);
    if ($decoded === false) {
        throw new RuntimeException('Invalid base64url encoding.');
    }
    return $decoded;
}

function google_asn1_length(int $length): string
{
    if ($length < 0x80) {
        return chr($length);
    }
    $temp = ltrim(pack('N', $length), "\x00");
    return chr(0x80 | strlen($temp)) . $temp;
}

function google_asn1_integer(string $bytes): string
{
    if ($bytes === '' || (ord($bytes[0]) & 0x80)) {
        $bytes = "\x00" . $bytes;
    }
    return "\x02" . google_asn1_length(strlen($bytes)) . $bytes;
}

function google_asn1_sequence(string $contents): string
{
    return "\x30" . google_asn1_length(strlen($contents)) . $contents;
}

function google_asn1_bit_string(string $bytes): string
{
    $bytes = "\x00" . $bytes;
    return "\x03" . google_asn1_length(strlen($bytes)) . $bytes;
}

/**
 * Verify a Google ID token and return the payload.
 *
 * @return array<string, mixed>
 */
function google_verify_id_token(string $idToken, ?string $expectedNonce = null): array
{
    $parts = explode('.', $idToken);
    if (count($parts) !== 3) {
        throw new InvalidArgumentException('Malformed ID token.');
    }

    [$headerB64, $payloadB64, $sigB64] = $parts;
    $headerJson = google_base64url_decode($headerB64);
    $payloadJson = google_base64url_decode($payloadB64);
    $signature = google_base64url_decode($sigB64);

    $header = json_decode($headerJson, true);
    $payload = json_decode($payloadJson, true);

    if (!is_array($header) || !is_array($payload)) {
        throw new InvalidArgumentException('Invalid ID token JSON.');
    }

    if (($header['alg'] ?? '') !== 'RS256') {
        throw new InvalidArgumentException('Unsupported token algorithm.');
    }

    $kid = $header['kid'] ?? '';
    if ($kid === '') {
        throw new InvalidArgumentException('Missing key id on token.');
    }

    $keys = google_fetch_jwks();
    if (empty($keys[$kid])) {
        // Refresh cache once in case keys rotated
        @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'saiflower_google_jwks.json');
        $keys = google_fetch_jwks();
    }
    if (empty($keys[$kid])) {
        throw new InvalidArgumentException('Unknown signing key.');
    }

    $pem = google_jwk_to_pem($keys[$kid]);
    $ok = openssl_verify(
        $headerB64 . '.' . $payloadB64,
        $signature,
        $pem,
        OPENSSL_ALGO_SHA256
    );
    if ($ok !== 1) {
        throw new InvalidArgumentException('Invalid token signature.');
    }

    $iss = (string) ($payload['iss'] ?? '');
    if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
        throw new InvalidArgumentException('Invalid token issuer.');
    }

    $aud = $payload['aud'] ?? '';
    if (is_array($aud)) {
        if (!in_array(GOOGLE_OAUTH_CLIENT_ID, $aud, true)) {
            throw new InvalidArgumentException('Invalid token audience.');
        }
    } elseif ($aud !== GOOGLE_OAUTH_CLIENT_ID) {
        throw new InvalidArgumentException('Invalid token audience.');
    }

    $azp = $payload['azp'] ?? null;
    if ($azp !== null && $azp !== '' && $azp !== GOOGLE_OAUTH_CLIENT_ID) {
        // azp should match our client when present
        throw new InvalidArgumentException('Invalid authorized party.');
    }

    $exp = (int) ($payload['exp'] ?? 0);
    if ($exp < time()) {
        throw new InvalidArgumentException('Token has expired.');
    }

    $iat = (int) ($payload['iat'] ?? 0);
    if ($iat > 0 && $iat > (time() + 60)) {
        throw new InvalidArgumentException('Token issued in the future.');
    }

    $email = trim((string) ($payload['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Token is missing a valid email.');
    }

    $emailVerified = $payload['email_verified'] ?? false;
    if (!($emailVerified === true || $emailVerified === 'true' || $emailVerified === 1 || $emailVerified === '1')) {
        throw new InvalidArgumentException('Google email is not verified.');
    }

    if ($expectedNonce !== null) {
        $tokenNonce = isset($payload['nonce']) ? (string) $payload['nonce'] : '';
        // GIS SHA-256-hashes the nonce before embedding it in the ID token.
        $expectedHash = hash('sha256', $expectedNonce);
        $nonceOk = ($tokenNonce !== '')
            && (hash_equals($expectedHash, $tokenNonce) || hash_equals($expectedNonce, $tokenNonce));
        if (!$nonceOk) {
            throw new InvalidArgumentException('Invalid authentication nonce.');
        }
    }

    return $payload;
}

/**
 * Find, link, or create a customer from a verified Google payload.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>
 */
function google_find_or_create_customer(mysqli $conn, array $payload): array
{
    google_ensure_customer_schema($conn);

    $googleId = (string) ($payload['sub'] ?? '');
    $email = strtolower(trim((string) ($payload['email'] ?? '')));
    $name = trim((string) ($payload['name'] ?? ''));
    if ($name === '') {
        $given = trim((string) ($payload['given_name'] ?? ''));
        $family = trim((string) ($payload['family_name'] ?? ''));
        $name = trim($given . ' ' . $family);
    }
    if ($name === '') {
        $name = explode('@', $email)[0];
    }
    $avatar = trim((string) ($payload['picture'] ?? ''));
    if (strlen($avatar) > 500) {
        $avatar = substr($avatar, 0, 500);
    }

    if ($googleId === '') {
        throw new RuntimeException('Missing Google subject.');
    }

    // 1) Existing Google-linked account
    $stmt = $conn->prepare(
        'SELECT id, name, email, phone, address, is_verified, google_id, auth_provider, avatar_url
         FROM customers WHERE google_id = ? LIMIT 1'
    );
    if (!$stmt) {
        throw new RuntimeException('Database error (google lookup).');
    }
    $stmt->bind_param('s', $googleId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $dbName, $dbEmail, $phone, $address, $isVerified, $dbGoogleId, $authProvider, $avatarUrl);
        $stmt->fetch();
        $stmt->close();

        $updName = $dbName !== '' && $dbName !== null ? $dbName : $name;
        $updAvatar = $avatar !== '' ? $avatar : $avatarUrl;
        $upd = $conn->prepare('UPDATE customers SET name = ?, avatar_url = ?, is_verified = 1 WHERE id = ?');
        $upd->bind_param('ssi', $updName, $updAvatar, $id);
        $upd->execute();
        $upd->close();

        return [
            'id' => (int) $id,
            'name' => $updName,
            'email' => $dbEmail,
            'phone' => $phone,
            'address' => $address,
            'is_verified' => 1,
            'google_id' => $dbGoogleId,
            'auth_provider' => $authProvider,
            'avatar_url' => $updAvatar,
        ];
    }
    $stmt->close();

    // 2) Link by email (password or previous account)
    $stmt = $conn->prepare(
        'SELECT id, name, email, phone, address, is_verified, google_id, auth_provider, avatar_url, password
         FROM customers WHERE email = ? LIMIT 1'
    );
    if (!$stmt) {
        throw new RuntimeException('Database error (email lookup).');
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $dbName, $dbEmail, $phone, $address, $isVerified, $dbGoogleId, $authProvider, $avatarUrl, $passwordHash);
        $stmt->fetch();
        $stmt->close();

        $provider = (!empty($passwordHash)) ? 'local_google' : 'google';
        $keepName = ($dbName !== null && $dbName !== '') ? $dbName : $name;
        $keepAvatar = $avatar !== '' ? $avatar : $avatarUrl;

        $upd = $conn->prepare(
            'UPDATE customers
             SET google_id = ?, auth_provider = ?, avatar_url = ?, name = ?, is_verified = 1, verification_token = NULL
             WHERE id = ?'
        );
        $upd->bind_param('ssssi', $googleId, $provider, $keepAvatar, $keepName, $id);
        $upd->execute();
        $upd->close();

        return [
            'id' => (int) $id,
            'name' => $keepName,
            'email' => $dbEmail,
            'phone' => $phone,
            'address' => $address,
            'is_verified' => 1,
            'google_id' => $googleId,
            'auth_provider' => $provider,
            'avatar_url' => $keepAvatar,
        ];
    }
    $stmt->close();

    // 3) Create new Google account (unusable random password)
    $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    $provider = 'google';

    $ins = $conn->prepare(
        'INSERT INTO customers (name, email, phone, password, is_verified, verification_token, google_id, auth_provider, avatar_url)
         VALUES (?, ?, NULL, ?, 1, NULL, ?, ?, ?)'
    );
    if (!$ins) {
        throw new RuntimeException('Could not prepare account insert: ' . $conn->error);
    }
    $ins->bind_param('ssssss', $name, $email, $randomPassword, $googleId, $provider, $avatar);

    if (!$ins->execute()) {
        $err = $ins->error;
        $ins->close();
        throw new RuntimeException('Could not create account: ' . $err);
    }
    $newId = (int) $ins->insert_id;
    $ins->close();

    return [
        'id' => $newId,
        'name' => $name,
        'email' => $email,
        'phone' => null,
        'address' => null,
        'is_verified' => 1,
        'google_id' => $googleId,
        'auth_provider' => $provider,
        'avatar_url' => $avatar,
    ];
}

/**
 * Establish the same PHP session keys as email/password login.
 *
 * @param array<string, mixed> $customer
 */
function google_start_customer_session(array $customer): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['customer_id'] = (int) $customer['id'];
    $_SESSION['customer_name'] = (string) ($customer['name'] ?? '');
    $_SESSION['customer_email'] = (string) ($customer['email'] ?? '');
    $_SESSION['customer_phone'] = (string) ($customer['phone'] ?? '');
    $_SESSION['customer_address'] = (string) ($customer['address'] ?? '');
    if (!empty($customer['avatar_url'])) {
        $_SESSION['customer_avatar'] = (string) $customer['avatar_url'];
    }

    // Rotate CSRF after privilege change
    if (function_exists('csrf_regenerate')) {
        csrf_regenerate();
    }
    google_auth_nonce(true);
}

/**
 * Allow only same-site relative redirects.
 */
function google_safe_redirect(?string $redirect, string $fallback = '/'): string
{
    if ($redirect === null || $redirect === '') {
        return $fallback;
    }

    $redirect = trim($redirect);
    // Block protocol-relative / absolute URLs
    if (preg_match('#^(https?:)?//#i', $redirect) || str_contains($redirect, "\n") || str_contains($redirect, "\r")) {
        return $fallback;
    }
    // Must be a relative path
    if ($redirect[0] !== '/' && !preg_match('#^[a-zA-Z0-9_\-./]+\.php(\?.*)?$#', $redirect)) {
        return $fallback;
    }

    // Normalize to leading slash for consistency
    if ($redirect[0] !== '/') {
        $redirect = '/' . ltrim($redirect, '/');
    }

    return $redirect;
}
