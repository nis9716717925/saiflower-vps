<?php
session_start();

// INCLUDE CONFIG
require_once '../config.php'; 

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 0. AUTO-MIGRATION: ENSURE ADMIN TABLE EXISTS
$checkTable = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($checkTable && $checkTable->num_rows == 0) {
    // Create Table
    $conn->query("CREATE TABLE `admin_users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
      `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Insert Default Admin (admin / StrongPassword@123)
    $defaultPass = '$2y$10$aLfWifmRT3gmU3WmOlh6UO9uQ900eFOKTcCkBA5JslOwG4qsExbx2';
    $conn->query("INSERT INTO `admin_users` (username, password) VALUES ('admin', '$defaultPass')");
}

// 0.5 AUTO-MIGRATION: ENSURE ADMIN_TOKENS TABLE EXISTS
$checkTokensTable = $conn->query("SHOW TABLES LIKE 'admin_tokens'");
if ($checkTokensTable && $checkTokensTable->num_rows == 0) {
    // Create Table
    $conn->query("CREATE TABLE `admin_tokens` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `admin_id` int(11) NOT NULL,
      `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
      `expiry` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// 1. AUTO-LOGIN CHECK (Remember Me Logic cleanup)
if (!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['admin_remember'])) {
    // Clear old static cookie method for security as per original intent
    setcookie("admin_remember", "", time() - 3600, "/"); 
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

// GENERATE CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VALIDATE CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Session expired. Please refresh the page.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // PREPARE STATEMENT
        $stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hash);
            $stmt->fetch();
            
            if (password_verify($password, $hash)) {
                // Success - Standard Login
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $id;
                $_SESSION['last_activity'] = time();

                // HANDLE "REMEMBER ME"
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 Days
                    
                    // Store hash of token in DB security best practice
                    $tokenHash = hash('sha256', $token);
                    
                    $conn->query("INSERT INTO admin_tokens (admin_id, token, expiry) VALUES ($id, '$tokenHash', '$expiry')");
                    
                    // Set secure cookie httpOnly
                    setcookie('admin_remember', "$id:$token", time() + (86400 * 30), "/", "", false, true);
                }

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Admin Login'; include 'partials/head.php'; ?>
    <style>
        :root { --primary: #326e54; }
        body { background: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: 'Inter', sans-serif; }
        .login-box { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .form-group { position: relative; margin-bottom: 20px; }
        .form-input { width: 100%; padding: 14px 45px 14px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; box-sizing: border-box; transition: 0.3s; }
        .form-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(47, 111, 78, 0.1); }
        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #aaa; transition: 0.3s; }
        .toggle-password:hover { color: var(--primary); }
        .btn { background: var(--primary); color: white; border: none; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s; width: 100%; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
        .remember-wrap { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .checkbox-container { display: flex; align-items: center; cursor: pointer; font-size: 0.9rem; color: #666; }
        .checkbox-container input { margin-right: 8px; }
    </style>
</head>
<body>

<div class="login-box">
    <div style="margin-bottom:15px; display:flex; justify-content:center;">
        <?php 
        $sQ = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
        $settings = mysqli_fetch_assoc($sQ);
        
        $logoPath = 'uploads/logo_transparent.png';
        if(strpos($logoPath, 'uploads/') !== 0 && !empty($logoPath)) $logoPath = 'uploads/' . $logoPath;
        // Admin path: ../
        ?>
        
        <?php if (!empty($logoPath) && file_exists(__DIR__ . '/../' . $logoPath)): ?>
            <img src="../<?= htmlspecialchars($logoPath) ?>" alt="Admin" style="height: 100px; width: auto; object-fit: contain;">
        <?php else: ?>
            <div style="font-size:3.5rem; color:var(--primary);">
                <i class="fas fa-user-shield"></i>
            </div>
        <?php endif; ?>
    </div>
    <h2 style="margin-bottom: 5px; color: #333;">Welcome Back</h2>
    <p style="color: #888; font-size: 0.9rem; margin-bottom: 30px;">Enter your credentials to manage Sai Flowers</p>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="form-group">
            <input type="text" name="username" class="form-input" placeholder="Username" required autofocus>
        </div>
        
        <div class="form-group">
            <input type="password" name="password" id="passwordField" class="form-input" placeholder="Password" required>
            <i class="fas fa-eye toggle-password" id="toggleIcon" onclick="togglePassword()"></i>
        </div>

        <div class="remember-wrap">
            <label class="checkbox-container">
                <input type="checkbox" name="remember">
                Remember me
            </label>
            <a href="#" style="font-size: 0.85rem; color: var(--primary); text-decoration: none;">Forgot?</a>
        </div>

        <button type="submit" class="btn">Login to Dashboard</button>
    </form>

    <?php if ($error): ?>
        <div style="margin-top:20px; color:#e74c3c; font-size:0.9rem; background:#fdedec; padding:12px; border-radius:10px; border: 1px solid #fadbd8;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top:35px; font-size:0.75rem; color:#bbb; text-transform: uppercase; letter-spacing: 1px;">
        Hexa CMS v2.0
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('passwordField');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>