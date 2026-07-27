<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/csrf_helper.php';

// 1. Auth Guard: Redirect to login if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$success_msg = "";
$error_msg = "";

// 2. Fetch Theme Settings
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);
$pCol = $settings['theme_primary'] ?? '#11d452';

// 3. Handle Update Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    csrf_verify_or_die();
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $city = mysqli_real_escape_string($conn, trim($_POST['city']));
    $pincode = mysqli_real_escape_string($conn, trim($_POST['pincode']));

    $update_sql = "UPDATE customers SET name=?, phone=?, address=?, city=?, pincode=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssi", $name, $phone, $address, $city, $pincode, $customer_id);
    
    if ($stmt->execute()) {
        // Update session variables for consistency
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_phone'] = $phone;
        $_SESSION['customer_address'] = $address;
        $success_msg = "Your details have been updated successfully.";
    } else {
        $error_msg = "Something went wrong. Please try again.";
    }
}

// 4. Fetch Fresh Data
$stmt = $conn->prepare("SELECT name, email, phone, address, city, pincode FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_canonical_link() ?>
    <title>My Profile | Sai Flowers</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="icon" href="/favicon.png" type="image/x-icon">

    <style>
        :root { --primary: <?= $pCol ?>; }
        .input-focus:focus { border-color: var(--primary); ring-color: var(--primary); }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container mx-auto px-4 py-12 max-w-5xl">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
        <div>
            <h1 class="text-3xl font-black">Account Settings</h1>
            <p class="text-slate-500 text-sm mt-1">Keep your delivery information up to date for faster checkout.</p>
        </div>
        <a href="logout.php" class="flex items-center gap-2 text-red-500 font-bold hover:bg-red-50 px-4 py-2 rounded-xl transition-all">
            <span class="material-icons-outlined">logout</span> Logout
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm text-center">
                <div class="w-24 h-24 bg-primary/10 text-primary rounded-full flex items-center justify-center text-4xl font-black mx-auto mb-4 border-4 border-white shadow-inner">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h2 class="text-xl font-bold"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="text-slate-400 text-sm mb-6"><?= htmlspecialchars($user['email']) ?></p>
                
                <div class="text-left space-y-4 pt-6 border-t border-slate-50">
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="material-icons-outlined text-primary text-lg">local_shipping</span>
                        <span>Saved Address Set</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <?php if($success_msg): ?>
                <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl mb-6 font-bold flex items-center gap-3 border border-emerald-100">
                    <span class="material-icons-outlined">check_circle</span> <?= $success_msg ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-8 md:p-10 rounded-3xl border border-slate-100 shadow-sm">
                <form method="POST" class="space-y-6">
                    <?php csrf_field(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required 
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" 
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Email (Account ID)</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly 
                               class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Complete Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">City</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>" 
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Pincode</label>
                            <input type="text" name="pincode" value="<?= htmlspecialchars($user['pincode']) ?>" 
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" 
                            class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:brightness-95 transition-all shadow-lg shadow-primary/30 mt-4">
                        Save Account Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>