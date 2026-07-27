<?php
// verify.php
require_once 'config.php';

$msg = "";
$status = "error"; // success or error

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    if (!empty($token)) {
        // Find user with this token
        $stmt = $conn->prepare("SELECT id, name, email FROM customers WHERE verification_token = ? AND is_verified = 0");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $email);
            $stmt->fetch();
            
            // Mark as verified
            $update = $conn->prepare("UPDATE customers SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $update->bind_param("i", $id);
            
            if ($update->execute()) {
                $status = "success";
                $msg = "Your email has been successfully verified! You can now log in.";
                
                // Optional: Auto-login
                $_SESSION['customer_id'] = $id;
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_email'] = $email;
                header("Refresh: 3; url=index.php");
            } else {
                $msg = "System error during verification. Please try again.";
            }
        } else {
            $msg = "Invalid or expired verification link. Your account may already be verified.";
        }
    } else {
        $msg = "Invalid token provided.";
    }
} else {
    $msg = "No verification token found.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_canonical_link() ?>
    <title>Email Verification | Sai Flowers</title>
     
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen font-sans">

<div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-100 text-center">
    
    <div class="mb-6 flex justify-center">
        <?php if($status == 'success'): ?>
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl shadow-inner">
                <i class="fas fa-check"></i>
            </div>
        <?php else: ?>
            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-4xl shadow-inner">
                <i class="fas fa-times"></i>
            </div>
        <?php endif; ?>
    </div>

    <h2 class="text-2xl font-bold mb-2 text-slate-800">
        <?= ($status == 'success') ? "Verified!" : "Verification Failed" ?>
    </h2>
    
    <p class="text-slate-500 mb-8 leading-relaxed">
        <?= htmlspecialchars($msg) ?>
    </p>

    <?php if($status == 'success'): ?>
        <a href="index.php" class="inline-block bg-primary text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-primary/90 transition-transform hover:scale-105">
            Go to Homepage
        </a>
        <p class="text-xs text-slate-400 mt-4">Redirecting in 3 seconds...</p>
    <?php else: ?>
        <a href="index.php" class="inline-block bg-slate-200 text-slate-700 font-bold py-3 px-8 rounded-full hover:bg-slate-300 transition-colors">
            Return Home
        </a>
    <?php endif; ?>

</div>

<!-- FontAwesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</body>
</html>
