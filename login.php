<?php
require_once 'config.php';

google_ensure_customer_schema($conn);
$googleNonce = google_auth_nonce();
$csrfToken = generate_csrf_token();

$redirectParam = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '';
$safeRedirect = google_safe_redirect($redirectParam !== '' ? $redirectParam : null, '/');

if (isset($_SESSION['customer_id'])) {
    header('Location: ' . $safeRedirect);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/csrf_helper.php';
    if (!verify_csrf_token()) {
        $error = 'Session expired. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $postRedirect = isset($_POST['redirect']) ? (string) $_POST['redirect'] : $redirectParam;
        $afterLogin = google_safe_redirect($postRedirect !== '' ? $postRedirect : null, '/');

        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
        } else {
            $stmt = $conn->prepare('SELECT id, name, password, email, phone, address, is_verified FROM customers WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $name, $hash, $dbEmail, $phone, $address, $isVerified);
                $stmt->fetch();

                if (password_verify($password, $hash)) {
                    if ((int) $isVerified === 1) {
                        session_regenerate_id(true);
                        $_SESSION['customer_id'] = $id;
                        $_SESSION['customer_name'] = $name;
                        $_SESSION['customer_email'] = $dbEmail;
                        $_SESSION['customer_phone'] = $phone;
                        $_SESSION['customer_address'] = $address;

                        header('Location: ' . $afterLogin);
                        exit;
                    }
                    $error = 'Please verify your email address before logging in. Check your inbox.';
                } else {
                    $error = 'Invalid password.';
                }
            } else {
                $error = 'No account found with this email.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_canonical_link() ?>
    <title>Login | Sai Flowers</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen font-sans">

<div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-100">
    <div class="text-center mb-8">
        <a href="index.php" class="flex items-center justify-center gap-2">
            <?php
            if (!isset($settings)) {
                $sQ = mysqli_query($conn, 'SELECT * FROM settings WHERE id=1');
                $settings = mysqli_fetch_assoc($sQ);
            }
            $logoPath = 'uploads/logo_transparent.png';
            if (strpos($logoPath, 'uploads/') !== 0 && !empty($logoPath)) {
                $logoPath = 'uploads/' . $logoPath;
            }
            ?>
            <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
                <img src="<?= htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($settings['site_title'] ?? 'Sai Flower') ?>" class="h-24 w-auto object-contain">
            <?php else: ?>
                <span class="text-2xl font-bold text-primary flex items-center gap-2">
                    <span class="material-icons-outlined">local_florist</span>
                    <?= htmlspecialchars($settings['site_title'] ?? 'Sai Flower') ?>
                </span>
            <?php endif; ?>
        </a>
        <h2 class="text-xl font-bold mt-4 text-slate-800">Welcome Back</h2>
        <p class="text-sm text-slate-500">Login to your account</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4 text-center border border-red-100">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div id="google-auth-status" class="hidden p-3 rounded-lg text-sm mb-4 text-center border" role="status" aria-live="polite"></div>

    <div class="mb-5">
        <div id="google-btn-wrap" class="w-full flex justify-center [&>div]:w-full"></div>
    </div>

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center text-xs">
            <span class="bg-white px-3 text-slate-400 font-semibold uppercase tracking-wider">or continue with email</span>
        </div>
    </div>

    <form method="POST" class="space-y-4">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectParam) ?>">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Email Address</label>
            <input type="email" name="email" required autocomplete="email" class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary">
        </div>
        <div>
            <div class="flex justify-between mb-1">
                <label class="block text-sm font-bold text-slate-700">Password</label>
            </div>
            <input type="password" name="password" required autocomplete="current-password" class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary">
        </div>

        <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/30">
            Login
        </button>
        <?php csrf_field(); ?>
    </form>

    <p class="text-center text-sm text-slate-500 mt-6">
        Don't have an account? <a href="register.php<?= $redirectParam !== '' ? ('?redirect=' . rawurlencode($redirectParam)) : '' ?>" class="text-primary font-bold hover:underline">Sign Up</a>
    </p>
</div>

<script>
window.SAIFLOWER_GOOGLE = {
    clientId: <?= json_encode(GOOGLE_OAUTH_CLIENT_ID) ?>,
    nonce: <?= json_encode($googleNonce) ?>,
    csrfToken: <?= json_encode($csrfToken) ?>,
    redirect: <?= json_encode($redirectParam) ?>,
    endpoint: '/actions/google_auth',
    context: 'signin'
};
</script>
<script src="/assets/js/google-auth.js" defer></script>
</body>
</html>
