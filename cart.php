<?php
include 'config.php';
// include_once 'actions/setup_shop.php'; 
// include_once 'actions/setup_promo.php';

// LOAD CSRF HELPER
require_once __DIR__ . '/includes/csrf_helper.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// THEME
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);
$pCol = $settings['theme_primary'] ?? '#11d452';

// CART TOTAL
function getCartTotal($cart) {
    global $conn; // Access the DB connection
    $total = 0;
    if(!empty($cart)) {
        foreach ($cart as &$item) {
            $cat = $item['category'] ?? 'flower';
            $id = intval($item['id']);
            $table = 'flowers';
            if ($cat === 'cake') $table = 'cakes';
            if ($cat === 'gift') $table = 'gifts';
            if ($cat === 'addon') $table = 'addons';

            // Always fetch the true live base price from DB and apply the surge dynamically
            $q = $conn->query("SELECT price FROM `$table` WHERE id = $id LIMIT 1");
            if ($q && $q->num_rows > 0) {
                $dbPrice = $q->fetch_assoc()['price'];
                // Apply the frontend multiplier
                $livePrice = apply_surge_pricing((float)$dbPrice, $cat);
                
                // Update the session price snapshot so the display matches the live value
                $item['price'] = $livePrice; 
                
                $total += $livePrice * (int)$item['qty'];
            } else {
                // Fallback to session if DB fails
                $total += (float)$item['price'] * (int)$item['qty'];
            }
        }
    }
    return $total;
}

$msg = "";
$msgType = "";

// HANDLE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die(); // Strict CSRF Check

    $index = isset($_POST['index']) ? intval($_POST['index']) : -1;

    // 1. ADD TO CART LOGIC (optional immediate checkout via buy_now)
    if (isset($_POST['add_to_cart']) || isset($_POST['buy_now'])) {
        $found = false;
        $newItem = [
            'id'    => $_POST['product_id'],
            'category' => $_POST['category'] ?? 'flower', // Default to flower for legacy
            'name'  => $_POST['name'],
            'price' => floatval($_POST['price']),
            'image' => $_POST['image'] ?? '',
            'qty'   => intval($_POST['quantity'] ?? 1)
        ];

        foreach ($_SESSION['cart'] as &$item) {
            $existingCat = $item['category'] ?? 'flower';
            if ($item['id'] == $newItem['id'] && $existingCat == $newItem['category']) {
                $item['qty'] += $newItem['qty'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = $newItem;
        }
        if (isset($_POST['buy_now'])) {
            header("Location: /checkout.php"); exit;
        }
        header("Location: /cart.php?status=added"); exit;
    }

    // 2. REMOVE ITEM
    if (isset($_POST['remove_item']) && isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        header("Location: /cart.php?status=removed"); exit;
    } 

    // 3. UPDATE QUANTITY
    elseif (isset($_POST['update_qty']) && isset($_SESSION['cart'][$index])) {
        $change = intval($_POST['update_qty']);
        $_SESSION['cart'][$index]['qty'] += $change;
        
        if ($_SESSION['cart'][$index]['qty'] <= 0) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
        header("Location: /cart.php?status=updated"); exit;
    }
    
    // APPLY COUPON LOGIC
    if (isset($_POST['apply_coupon'])) {

        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $currentTotal = getCartTotal($_SESSION['cart']);

        // UPDATED QUERY TO TARGET SPECIFIC COLUMNS
        $stmt = $conn->prepare("SELECT code, discount_type, discount_value, min_order_amount FROM promo_codes WHERE code=? AND status=1 AND (expiry_date IS NULL OR expiry_date = '0000-00-00' OR expiry_date >= CURDATE()) LIMIT 1");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {

            $coupon = $res->fetch_assoc();

            if ($currentTotal >= $coupon['min_order_amount']) {

                // CONFIRMED SESSION STORAGE ALIGNMENT
                $_SESSION['coupon'] = [
                    'code'      => $coupon['code'],
                    'type'      => $coupon['discount_type'],
                    'value'     => $coupon['discount_value'],
                    'min_order' => $coupon['min_order_amount']
                ];

                header("Location: /cart.php?status=coupon_ok");
                exit;

            } else {

                unset($_SESSION['coupon']);
                header("Location: /cart.php?status=min_req&amt=".$coupon['min_order_amount']);
                exit;

            }

        } else {

            unset($_SESSION['coupon']);
            header("Location: /cart.php?status=invalid_code");
            exit;

        }
    }

    if(isset($_POST['remove_coupon'])){
        unset($_SESSION['coupon']);
        header("Location: /cart.php?status=coupon_removed"); exit;
    }
}

// MESSAGES
if(isset($_GET['status'])) {
    switch($_GET['status']) {
        case 'added': $msg = "Item added to cart!"; $msgType = "success"; break;
        case 'updated': $msg = "Quantity updated."; $msgType = "success"; break;
        case 'removed': $msg = "Item removed from cart."; $msgType = "success"; break;
        case 'coupon_ok': $msg = "Coupon applied successfully!"; $msgType = "success"; break;
        case 'min_req': $msg = "Minimum order of ₹".$_GET['amt']." required."; $msgType = "error"; break;
        case 'invalid_code': $msg = "Invalid or expired promo code."; $msgType = "error"; break;
        case 'coupon_removed': $msg = "Promo code removed."; $msgType = "success"; break;
    }
}

$cartTotal = getCartTotal($_SESSION['cart']);

// VALIDATE EXISTING COUPON
if(isset($_SESSION['coupon'])){
    if($cartTotal < $_SESSION['coupon']['min_order']){
        unset($_SESSION['coupon']);
    }
}

// LIVE PROMOS
$livePromos = [];
$promoQuery = $conn->query("SELECT * FROM promo_codes WHERE status=1 AND (expiry_date IS NULL OR expiry_date = '0000-00-00' OR expiry_date >= CURDATE()) ORDER BY id DESC");
if ($promoQuery && $promoQuery->num_rows > 0) {
    while($row = $promoQuery->fetch_assoc()) $livePromos[] = $row;
}

// DISCOUNT CALCULATION
$grandTotal = $cartTotal;
$discountAmount = 0;

if(isset($_SESSION['coupon'])){

    // FIX 1: Correct types to flat/percentage
    if($_SESSION['coupon']['type'] == 'flat'){
        $discountAmount = $_SESSION['coupon']['value'];
    }

    if($_SESSION['coupon']['type'] == 'percentage'){
        $discountAmount = ($cartTotal * $_SESSION['coupon']['value']) / 100;
    }

    if($discountAmount > $cartTotal){
        $discountAmount = $cartTotal;
    }

    $grandTotal = $cartTotal - $discountAmount;

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <?= render_canonical_link() ?>
    <title>Your Cart | Sai Flower</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="icon" href="/favicon.png" type="image/x-icon">

    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { 
            overflow-x: hidden; 
            width: 100%; 
            position: relative;
            background-color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        main { max-width: 100% !important; }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="text-slate-900">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="w-full px-3 py-4 md:py-10 max-w-6xl mx-auto">
    <h1 class="text-xl md:text-3xl font-bold mb-5 flex items-center gap-2">
        <span class="material-icons-outlined text-green-600">shopping_bag</span>
        Your Cart (<?= count($_SESSION['cart']) ?>)
    </h1>

    <?php if (!empty($msg)): ?>
        <div class="mb-5 p-3 rounded-lg flex items-center gap-2 <?= $msgType=='success'?'bg-green-50 text-green-700 border border-green-100':'bg-red-50 text-red-700 border border-red-100' ?>">
            <span class="material-icons-outlined text-sm"><?= $msgType=='success'?'check_circle':'error_outline' ?></span>
            <span class="text-xs font-semibold"><?= htmlspecialchars($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if(empty($_SESSION['cart'])): ?>
        <div class="bg-white rounded-2xl p-8 text-center border border-slate-100 shadow-sm">
            <span class="material-icons-outlined text-5xl text-slate-200 mb-2">local_florist</span>
            <h2 class="text-lg font-bold">Cart is empty</h2>
            <p class="text-slate-400 text-sm mb-5">Browse our collection today.</p>
            <a href="flowers.php" class="bg-green-600 text-white px-6 py-2.5 rounded-full font-bold text-sm inline-block">Shop Now</a>
        </div>
    <?php else: ?>

    <div class="flex flex-col lg:flex-row gap-5">
        <div class="w-full lg:w-2/3 space-y-3">
            <?php foreach($_SESSION['cart'] as $index=>$item): 
                $finalImg = (strpos($item['image'], 'uploads/') === 0) ? "/" . $item['image'] : "/uploads/" . $item['image'];
            ?>
            <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 flex items-center gap-3">
                <img src="<?= $finalImg ?>" class="w-16 h-16 rounded-lg object-cover bg-slate-50 flex-shrink-0" alt="<?= htmlspecialchars($item['name']) ?> in cart" onerror="this.src='/assets/images/flower1.jpg'">
                
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-green-600 font-bold text-xs">₹<?= number_format($item['price'], 2) ?></p>
                    
                    <div class="flex items-center gap-2 mt-2">
                        <form method="POST" class="flex items-center bg-slate-100 rounded-lg p-0.5 border border-slate-200">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button name="update_qty" value="-1" class="w-6 h-6 bg-white rounded shadow-sm flex items-center justify-center">
                                <span class="material-icons-outlined text-xs">remove</span>
                            </button>
                            <span class="w-7 text-center font-bold text-xs"><?= $item['qty'] ?></span>
                            <button name="update_qty" value="1" class="w-6 h-6 bg-white rounded shadow-sm flex items-center justify-center">
                                <span class="material-icons-outlined text-xs">add</span>
                            </button>
                            <?php csrf_field(); ?>
                        </form>
                    </div>
                </div>

                <form method="POST" onsubmit="return confirm('Remove?')">
                    <input type="hidden" name="index" value="<?= $index ?>">
                    <button name="remove_item" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                        <span class="material-icons-outlined text-xl">delete_outline</span>
                    </button>
                    <?php csrf_field(); ?>
                </form>
            </div>
            <?php endforeach; ?>

            <?php
            $addon_query = mysqli_query($conn, "SELECT id, name, price, icon FROM addons WHERE status = 1 LIMIT 8");
            if ($addon_query && mysqli_num_rows($addon_query) > 0):
            ?>
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <h3 class="font-bold text-sm mb-4 text-slate-700">Add something special?</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <?php while ($addon = mysqli_fetch_assoc($addon_query)): 
                        $addonImg = $addon['icon'];
                        if(empty($addonImg) || strpos($addonImg, 'fa-') === 0) $addonImg = '/favicon.png';
                        elseif(strpos($addonImg, 'uploads/') === 0) $addonImg = '/' . $addonImg;
                    ?>
                    <div class="bg-slate-50 rounded-xl p-2 border border-slate-100 flex flex-col justify-between items-center text-center">
                        <img src="<?= htmlspecialchars($addonImg) ?>" class="w-10 h-10 object-contain mb-1" alt="<?= htmlspecialchars($addon['name']) ?> add-on" onerror="this.src='/favicon.png'">
                        <p class="text-[10px] font-bold text-slate-700 line-clamp-1 w-full"><?= htmlspecialchars($addon['name']) ?></p>
                        <p class="text-[10px] font-black text-slate-900 mb-2">₹<?= number_format($addon['price']) ?></p>
                        <form method="POST" class="w-full">
                            <input type="hidden" name="product_id" value="<?= $addon['id'] ?>">
                            <input type="hidden" name="category" value="addon">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($addon['name']) ?>">
                            <input type="hidden" name="price" value="<?= $addon['price'] ?>">
                            <input type="hidden" name="image" value="<?= htmlspecialchars($addon['icon']) ?>">
                            <button type="submit" name="add_to_cart" value="1" class="w-full bg-white text-pink-500 border border-pink-100 py-1 rounded-lg text-[10px] font-bold">+ ADD</button>
                            <?php csrf_field(); ?>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="w-full lg:w-1/3">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm sticky top-5">
                <h2 class="font-bold text-base mb-4">Summary</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-slate-500 font-medium">
                        <span>Subtotal</span>
                        <span class="text-slate-900">₹<?= number_format($cartTotal,2) ?></span>
                    </div>
                    <?php if(isset($_SESSION['coupon'])): ?>
                    <div class="flex justify-between text-green-600 font-bold">
                        <span>Discount</span>
                        <span>-₹<?= number_format($discountAmount,2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="border-t border-dashed pt-3 flex justify-between items-center">
                        <span class="font-bold text-slate-800">Total Amount</span>
                        <span class="text-xl font-black text-green-600">₹<?= number_format($grandTotal,2) ?></span>
                    </div>
                </div>

                <a href="checkout.php" class="block w-full bg-green-600 text-white text-center py-3.5 rounded-xl mt-6 font-bold text-sm shadow-lg shadow-green-100 active:scale-95 transition-all">
                    Proceed to Checkout
                </a>

                <?php if(!empty($livePromos)): ?>
                <div class="mt-6 space-y-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Coupons</p>
                    <?php foreach($livePromos as $promo): 
                        $isApplied = isset($_SESSION['coupon']) && $_SESSION['coupon']['code'] == $promo['code'];
                        $eligible = $cartTotal >= $promo['min_order_amount'];
                    ?>
                    <div class="p-2.5 rounded-xl border border-dashed flex justify-between items-center <?= $isApplied ? 'bg-green-50 border-green-200' : 'bg-slate-50 border-slate-200' ?>">
                        <div class="min-w-0">
                            <p class="font-bold text-[10px]"><?= htmlspecialchars($promo['code']) ?></p>
                            <p class="text-[9px] text-slate-400">
                                Save <?= $promo['discount_type']=='percentage' ? $promo['discount_value'].'%' : '₹'.$promo['discount_value'] ?>
                            </p>
                        </div>
                        <?php if($isApplied): ?>
                            <form method="POST"><button name="remove_coupon" class="text-red-500 text-[9px] font-bold">REMOVE</button><?php csrf_field(); ?></form>
                        <?php elseif($eligible): ?>
                            <form method="POST">
                                <input type="hidden" name="coupon_code" value="<?= htmlspecialchars($promo['code']) ?>">
                                <button name="apply_coupon" class="bg-slate-900 text-white px-3 py-1 rounded-lg text-[9px] font-bold">APPLY</button>
                                <?php csrf_field(); ?>
                            </form>
                        <?php else: ?>
                            <span class="text-[9px] text-slate-300 font-bold">Min ₹<?= $promo['min_order_amount'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

</body>
</html>