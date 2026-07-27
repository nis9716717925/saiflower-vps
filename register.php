<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

google_ensure_customer_schema($conn);
$googleNonce = google_auth_nonce();
$csrfToken = generate_csrf_token();

$redirectParam = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '';

if (isset($_SESSION['customer_id'])) {
    header('Location: ' . google_safe_redirect($redirectParam !== '' ? $redirectParam : null, '/'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/csrf_helper.php';
    if (!verify_csrf_token()) {
        $error = 'Invalid session. Please refresh.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            try {
                google_ensure_customer_schema($conn);

                $stmt = $conn->prepare('SELECT id FROM customers WHERE email = ?');
                if (!$stmt) {
                    throw new Exception('Prepare failed: (' . $conn->errno . ') ' . $conn->error);
                }
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = 'Email already registered. Please login.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $token = bin2hex(random_bytes(32));

                    $ins = $conn->prepare('INSERT INTO customers (name, email, phone, password, is_verified, verification_token, auth_provider) VALUES (?, ?, ?, ?, 0, ?, ?)');
                    if (!$ins) {
                        throw new Exception('Prepare INSERT failed: (' . $conn->errno . ') ' . $conn->error);
                    }
                    $provider = 'local';
                    $ins->bind_param('ssssss', $name, $email, $phone, $hashed, $token, $provider);

                    if ($ins->execute()) {
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $verifyLink = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/verify.php?token=' . $token;
                        $subject = 'Verify Your Sai Flowers Account';

                        $message = '
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <style>
                                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333; }
                                .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
                                .header { background-color: #326e54; padding: 40px 20px; text-align: center; color: white; }
                                .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 1px; }
                                .content { padding: 40px 30px; line-height: 1.6; }
                                .btn { display: inline-block; background-color: #326e54; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 50px; font-weight: bold; margin-top: 20px; text-transform: uppercase; font-size: 14px; letter-spacing: 0.5px; }
                                .footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
                            </style>
                        </head>
                        <body>
                            <div class="container">
                                <div class="header">
                                    <h1>Validating Your Account</h1>
                                </div>
                                <div class="content">
                                    <h2 style="color: #326e54; margin-top: 0;">Welcome to Sai Flowers, ' . htmlspecialchars($name) . '!</h2>
                                    <p>Thank you for joining our community of floral enthusiasts. We are thrilled to have you with us.</p>
                                    <p>To get started and unlock full access to your account, please verify your email address by clicking the button below:</p>
                                    <center>
                                        <a href="' . $verifyLink . '" class="btn">Verify Email Address</a>
                                    </center>
                                    <p style="margin-top: 30px; font-size: 13px; color: #777;">If the button above does not work, you can scan or paste the following link into your browser:<br>
                                    <a href="' . $verifyLink . '" style="color: #326e54;">' . $verifyLink . '</a></p>
                                </div>
                                <div class="footer">
                                    &copy; ' . date('Y') . ' Sai Flowers. All rights reserved.<br>
                                    Need help? Contact us at <a href="mailto:support@saiflowers.com" style="color:#777;">support@saiflowers.com</a>
                                </div>
                            </div>
                        </body>
                        </html>';

                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                        $headers .= 'From: Sai Flowers <no-reply@' . $_SERVER['HTTP_HOST'] . ">\r\n";

                        if (@mail($email, $subject, $message, $headers)) {
                            $success = 'Registration successful! Please check your email to verify your account.';
                        } else {
                            $success = 'Registration successful, but we failed to send the email. Please contact support.';
                            error_log("Mail failed for $email");
                        }
                    } else {
                        throw new Exception('Registration execution failed: ' . $conn->error);
                    }
                    $ins->close();
                }
                $stmt->close();
            } catch (Exception $e) {
                die('<h1>Error:</h1> ' . htmlspecialchars($e->getMessage()));
            }
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
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <?= render_canonical_link() ?>
    <title>Create Account | Sai Flowers</title>
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
        <h2 class="text-xl font-bold mt-4 text-slate-800">Create Account</h2>
        <p class="text-sm text-slate-500">Join us for faster checkout & tracking</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4 text-center border border-red-100">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 p-4 rounded-lg text-sm mb-6 text-center border border-green-100">
            <i class="fas fa-envelope-open-text text-2xl mb-2 block"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php else: ?>

    <div id="google-auth-status" class="hidden p-3 rounded-lg text-sm mb-4 text-center border" role="status" aria-live="polite"></div>

    <div class="mb-5">
        <div id="google-btn-wrap" class="w-full flex justify-center [&>div]:w-full"></div>
    </div>

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center text-xs">
            <span class="bg-white px-3 text-slate-400 font-semibold uppercase tracking-wider">or register with email</span>
        </div>
    </div>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Full Name</label>
            <input type="text" name="name" required autocomplete="name" class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Email Address</label>
            <input type="email" name="email" required autocomplete="email" class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Phone Number</label>
            <input type="tel" name="phone" autocomplete="tel" class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Password</label>
            <input type="password" name="password" required autocomplete="new-password" class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Confirm Password</label>
            <input type="password" name="confirm_password" required autocomplete="new-password" class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary">
        </div>

        <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/30">
            Sign Up
        </button>
        <?php csrf_field(); ?>
    </form>
    <?php endif; ?>

    <p class="text-center text-sm text-slate-500 mt-6">
        Already have an account? <a href="login.php<?= $redirectParam !== '' ? ('?redirect=' . rawurlencode($redirectParam)) : '' ?>" class="text-primary font-bold hover:underline">Login</a>
    </p>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<?php if (!$success): ?>
<script>
window.SAIFLOWER_GOOGLE = {
    clientId: <?= json_encode(GOOGLE_OAUTH_CLIENT_ID) ?>,
    nonce: <?= json_encode($googleNonce) ?>,
    csrfToken: <?= json_encode($csrfToken) ?>,
    redirect: <?= json_encode($redirectParam) ?>,
    endpoint: '/actions/google_auth',
    context: 'signup'
};
</script>
<script src="/assets/js/google-auth.js" defer></script>
<?php endif; ?>
</body>
</html>
