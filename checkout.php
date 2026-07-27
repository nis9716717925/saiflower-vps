<?php
// 1. ERROR REPORTING
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. SESSION & CONFIG
if (session_status() == PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/config.php';

$googleMapsKey = GOOGLE_MAPS_API_KEY;
$storeAddress = STORE_ADDRESS;
$shippingRatePerKm = SHIPPING_RATE_PER_KM;

if (empty($_SESSION['cart'])) {
    header("Location: flowers.php");
    exit;
}

// FETCH THEME SETTINGS
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);

$pCol = $settings['theme_primary'] ?? '#11d452';
$sCol = $settings['theme_secondary'] ?? '#d4af37';

// 3. AUTO-FILL LOGIC: Fetch Fresh Customer Data if Logged In
$cust_data = [
    'name' => $_SESSION['customer_name'] ?? '',
    'email' => $_SESSION['customer_email'] ?? '',
    'phone' => $_SESSION['customer_phone'] ?? '',
    'address' => $_SESSION['customer_address'] ?? '',
    'city' => '',
    'pincode' => ''
];

if (isset($_SESSION['customer_id'])) {
    $c_id = $_SESSION['customer_id'];
    $c_query = mysqli_query($conn, "SELECT name, email, phone, address, city, pincode FROM customers WHERE id = $c_id");
    if ($c_row = mysqli_fetch_assoc($c_query)) {
        $cust_data = $c_row; 
    }
}

// 4. PREPARE BASE TOTALS (SECURE DATABASE PRICE FETCH)
if (!function_exists('checkout_product_url')) {
    function checkout_product_url(string $category, int $id, string $slug = ''): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'saiflower.com';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = rtrim($scheme . '://' . $host, '/');

        if ($category === 'addon') {
            return '';
        }

        return $base . product_url_by_parts($category, $slug, $id);
    }
}

$baseTotal = 0;
$cartItemsList = [];

foreach ($_SESSION['cart'] as $item) {

    $id = intval($item['id']);
    $cat = $item['category'] ?? 'flower';

    $table = 'flowers';
    if ($cat === 'cake') $table = 'cakes';
    if ($cat === 'gift') $table = 'gifts';
    if ($cat === 'addon') $table = 'addons';

    $slug = '';
    if ($cat === 'addon') {
        $q = $conn->query("SELECT price FROM `$table` WHERE id=$id LIMIT 1");
    } else {
        $q = $conn->query("SELECT price, slug FROM `$table` WHERE id=$id LIMIT 1");
    }

    if ($q && $q->num_rows > 0) {
        $row = $q->fetch_assoc();
        $price = (float) $row['price'];
        $slug = trim($row['slug'] ?? '');
    } else {
        $price = (float) $item['price'];
    }

    $qty = (int) $item['qty'];
    $sub = $price * $qty;
    $baseTotal += $sub;

    $cleanName = str_replace(["'", '"'], '', $item['name']);
    $line = '• ' . $cleanName . ' (x' . $qty . ') - ₹' . number_format($sub, 2);

    $productUrl = checkout_product_url($cat, $id, $slug);
    if ($productUrl !== '') {
        $line .= "\n   Link: " . $productUrl;
    }

    $cartItemsList[] = $line;
}

// 5. COUPON CALCULATION (SECURE VALIDATION)
$discountAmount = 0;
$couponCode = "";

if(isset($_SESSION['coupon'])){

    $code = $_SESSION['coupon']['code'];

    $stmt = $conn->prepare("SELECT discount_type, discount_value, min_order_amount 
                            FROM promo_codes 
                            WHERE code=? AND status=1 AND (expiry_date IS NULL OR expiry_date = '0000-00-00' OR expiry_date >= CURDATE()) LIMIT 1");

    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res && $res->num_rows > 0){

        $coupon = $res->fetch_assoc();

        if($baseTotal >= $coupon['min_order_amount']){

            $couponCode = $code;

            if($coupon['discount_type'] == 'flat'){
                $discountAmount = $coupon['discount_value'];
            }

            if($coupon['discount_type'] == 'percentage'){
                $discountAmount = ($baseTotal * $coupon['discount_value']) / 100;
            }

            if($discountAmount > $baseTotal){
                $discountAmount = $baseTotal;
            }

        }else{
            unset($_SESSION['coupon']);
        }

    }else{
        unset($_SESSION['coupon']);
    }

}

// FETCH ADD-ONS
$addons = [];
$addon_query = mysqli_query($conn, "SELECT name, price, original_price, icon, id FROM addons WHERE status = 1 ORDER BY id DESC");
if ($addon_query) {
    while ($row = mysqli_fetch_assoc($addon_query)) { $addons[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<?= render_canonical_link() ?>
<title>Checkout | Sai Flower</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" href="/favicon.png" type="image/x-icon">

<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "<?= $pCol ?>",
                        "background-light": "#f6f8f6",
                        "background-dark": "#102216",
                    },
                    fontFamily: { "display": ["Plus Jakarta Sans"] },
                },
            },
        }
    </script>
<style type="text/tailwindcss">
        .checkout-input { @apply w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 text-sm focus:ring-primary focus:border-primary transition-all outline-none; }
        .section-card { @apply bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 md:p-8 mb-6; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="container mx-auto px-4 py-8 md:py-12">
<form id="checkoutForm">
    <div class="max-w-6xl mx-auto flex flex-col lg:flex-row gap-8">
        <div class="lg:w-7/12">
            <h1 class="text-3xl font-bold mb-8">Checkout</h1>
            
            <div class="section-card">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-icons-outlined text-primary">local_shipping</span>
                    <h2 class="text-xl font-bold">Delivery Information</h2>
                </div>
                
                <?php if(isset($_SESSION['customer_id'])): ?>
                    <div class="mb-6 p-3 bg-green-50 text-green-700 rounded-xl flex items-center gap-2 border border-green-100 text-xs font-bold">
                        <span class="material-icons-outlined text-sm">verified_user</span>
                        Welcome back! We've pre-filled your saved details.
                    </div>
                <?php else: ?>
                    <div class="mb-6 p-4 bg-blue-50 text-blue-800 rounded-xl flex items-center gap-3 border border-blue-100">
                        <span class="material-icons-outlined">info</span>
                        <p class="text-sm">Already a member? <a href="/login.php" class="font-bold underline">Login here</a> for a faster experience.</p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Sender Name</label>
                        <input class="checkout-input" id="sender_name" placeholder="Your name" type="text" value="<?= htmlspecialchars($cust_data['name']) ?>" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Sender Phone</label>
                        <input class="checkout-input" id="sender_phone" placeholder="Your phone number" type="tel" value="<?= htmlspecialchars($cust_data['phone']) ?>" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Recipient Name</label>
                        <input class="checkout-input" id="recipient_name" placeholder="Who is this for?" type="text" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Recipient Phone</label>
                        <input class="checkout-input" id="recipient_phone" placeholder="Their phone number" type="tel" required/>
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Email Address</label>
                        <input class="checkout-input" id="cust_email" placeholder="For receipt..." type="email" value="<?= htmlspecialchars($cust_data['email']) ?>" required/>
                    </div>

                    <div class="md:col-span-2 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                        <div class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <span class="material-icons-outlined text-primary text-sm mt-0.5">storefront</span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-slate-100">Dispatching from Sai Flower</p>
                                <p class="mt-1 leading-relaxed"><?= htmlspecialchars($storeAddress) ?></p>
                                <p class="mt-2 text-primary font-semibold">Shipping: ₹<?= $shippingRatePerKm ?> per km (based on driving distance)</p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Delivery Address</label>
                        <input class="checkout-input" id="cust_address_line" placeholder="Start typing your address..." type="text" value="<?= htmlspecialchars($cust_data['address']) ?>" required autocomplete="off"/>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">City</label>
                        <input class="checkout-input" id="cust_city" placeholder="e.g. New Delhi" type="text" value="<?= htmlspecialchars($cust_data['city']) ?>" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Postal Code</label>
                        <input class="checkout-input" id="cust_zip" placeholder="e.g. 110003" type="text" value="<?= htmlspecialchars($cust_data['pincode']) ?>" required/>
                    </div>

                    <div class="md:col-span-2">
                        <div id="shippingStatus" class="hidden p-4 rounded-xl border text-sm"></div>
                    </div>
                </div>
            </div>
            
            <div class="section-card">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-icons-outlined text-primary">calendar_month</span>
                    <h2 class="text-xl font-bold">Delivery Schedule</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Preferred Date</label>
                        <input class="checkout-input" id="del_date" type="date" min="<?= date('Y-m-d') ?>" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Preferred Time Slot</label>
                        <select class="checkout-input" id="del_time">
                            <option>Morning (9am - 12pm)</option>
                            <option>Afternoon (12pm - 4pm)</option>
                            <option>Evening (4pm - 8pm)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:w-5/12">
            <div class="sticky top-24">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xl">
                    <div class="p-6 md:p-8">
                        <h2 class="text-xl font-bold mb-6">Order Summary</h2>
                        <div class="space-y-6 mb-8">
                            <?php foreach($_SESSION['cart'] as $item): ?>
                            <div class="flex gap-4">
                                <?php 
                                    $dbImage = $item['image'];
                                    $finalImagePath = (strpos($dbImage, 'uploads/') === 0) ? "/" . $dbImage : "/uploads/" . $dbImage;
                                ?>
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
                                    <img alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover" 
                                         src="<?= $finalImagePath ?>" 
                                         onerror="this.src='https://images.unsplash.com/photo-1526047932273-341f2a7631f9?q=80&w=400'"/>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-xs"><?= htmlspecialchars($item['name']) ?></h4>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-[10px] text-slate-400">Qty: <?= $item['qty'] ?></span>
                                        <span class="font-bold text-primary text-sm">₹<?= number_format($item['price'] * $item['qty']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Subtotal</span>
                                <span>₹<?= number_format($baseTotal, 2) ?></span>
                            </div>

                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Shipping <span id="shippingDistanceLabel" class="text-slate-400"></span></span>
                                <span class="font-bold text-primary" id="shippingFee">—</span>
                            </div>

                            <?php if($discountAmount > 0): ?>
                            <div class="flex justify-between text-xs text-green-600 font-bold">
                                <span>Discount</span>
                                <span>- ₹<?= number_format($discountAmount, 2) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between pt-3 border-t">
                                <span class="text-base font-bold">Total Payable</span>
                                <span class="text-xl font-bold text-primary" id="displayTotal">₹<?= number_format($baseTotal - $discountAmount, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" id="placeOrderBtn" class="w-full mt-6 bg-primary text-white font-bold py-4 rounded-xl shadow-lg hover:scale-[1.01] transition-all flex items-center justify-center gap-3">
                    <i class="fab fa-whatsapp text-2xl"></i> Confirm Order
                </button>
            </div>
        </div>
    </div>
</form>
</main>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($googleMapsKey) ?>&libraries=places&callback=initAddressAutocomplete" async defer></script>
<script>
const baseCartItems = <?= json_encode($cartItemsList) ?>;
const baseTotal = <?= $baseTotal ?>;
const discountVal = <?= $discountAmount ?>;
const shippingRatePerKm = <?= (int) $shippingRatePerKm ?>;

let currentShippingFee = 0;
let currentDistanceKm = 0;
let currentDistanceText = '';
let shippingReady = false;
let shippingTimer = null;

function updateTotal() {
    document.getElementById('shippingFee').innerText = shippingReady ? '₹' + currentShippingFee.toLocaleString('en-IN') : '—';
    document.getElementById('shippingDistanceLabel').innerText = shippingReady
        ? `(${currentDistanceText || currentDistanceKm + ' km'})`
        : '';

    const finalTotal = Math.max(0, baseTotal + currentShippingFee - discountVal);
    document.getElementById('displayTotal').innerText = '₹' + finalTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

function setShippingStatus(type, message) {
    const box = document.getElementById('shippingStatus');
    box.classList.remove('hidden', 'bg-green-50', 'border-green-100', 'text-green-800', 'bg-amber-50', 'border-amber-100', 'text-amber-800', 'bg-red-50', 'border-red-100', 'text-red-700');

    if (type === 'loading') {
        box.classList.add('bg-amber-50', 'border-amber-100', 'text-amber-800');
        box.innerHTML = '<span class="inline-flex items-center gap-2"><span class="material-icons-outlined text-sm animate-spin">sync</span> Calculating delivery distance...</span>';
    } else if (type === 'success') {
        box.classList.add('bg-green-50', 'border-green-100', 'text-green-800');
        box.innerHTML = message;
    } else {
        box.classList.add('bg-red-50', 'border-red-100', 'text-red-700');
        box.innerHTML = message;
    }
}

function scheduleShippingCalculation() {
    clearTimeout(shippingTimer);
    shippingTimer = setTimeout(calculateShipping, 600);
}

async function calculateShipping() {
    const addressLine = document.getElementById('cust_address_line').value.trim();
    const city = document.getElementById('cust_city').value.trim();
    const zip = document.getElementById('cust_zip').value.trim();

    if (!addressLine || !city || !zip) {
        shippingReady = false;
        currentShippingFee = 0;
        currentDistanceKm = 0;
        currentDistanceText = '';
        document.getElementById('shippingStatus').classList.add('hidden');
        updateTotal();
        return;
    }

    setShippingStatus('loading', '');

    try {
        const response = await fetch('/actions/calculate_shipping.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ address_line: addressLine, city, zip })
        });
        const data = await response.json();

        if (data.status === 'ok') {
            shippingReady = true;
            currentShippingFee = parseInt(data.shipping_fee, 10) || 0;
            currentDistanceKm = parseFloat(data.distance_km) || 0;
            currentDistanceText = data.distance_text || (currentDistanceKm + ' km');

            setShippingStatus(
                'success',
                `<strong>Delivery distance:</strong> ${currentDistanceText} &nbsp;|&nbsp; <strong>Shipping:</strong> ₹${currentShippingFee} (₹${shippingRatePerKm}/km)`
            );
        } else {
            shippingReady = false;
            currentShippingFee = 0;
            setShippingStatus('error', data.message || 'Could not calculate shipping for this address.');
        }
    } catch (error) {
        shippingReady = false;
        currentShippingFee = 0;
        setShippingStatus('error', 'Unable to calculate shipping right now. Please try again.');
    }

    updateTotal();
}

function initAddressAutocomplete() {
    const input = document.getElementById('cust_address_line');
    if (!input || !window.google?.maps?.places) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: { country: 'in' },
        fields: ['address_components', 'formatted_address', 'geometry']
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.address_components) return;

        let locality = '';
        let postalCode = '';

        place.address_components.forEach((component) => {
            const types = component.types;
            if (types.includes('locality')) {
                locality = component.long_name;
            } else if (types.includes('postal_code')) {
                postalCode = component.long_name;
            } else if (!locality && types.includes('administrative_area_level_2')) {
                locality = component.long_name;
            } else if (!locality && types.includes('sublocality_level_1')) {
                locality = component.long_name;
            }
        });

        if (locality) document.getElementById('cust_city').value = locality;
        if (postalCode) document.getElementById('cust_zip').value = postalCode;

        scheduleShippingCalculation();
    });
}

['cust_address_line', 'cust_city', 'cust_zip'].forEach((id) => {
    document.getElementById(id).addEventListener('input', scheduleShippingCalculation);
    document.getElementById(id).addEventListener('blur', scheduleShippingCalculation);
});

document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('placeOrderBtn');

    if (!shippingReady) {
        await calculateShipping();
        if (!shippingReady) {
            alert('Please enter a valid delivery address so we can calculate shipping.');
            return;
        }
    }

    btn.disabled = true;

    const senderName = document.getElementById('sender_name').value;
    const senderPhone = document.getElementById('sender_phone').value;
    const recipientName = document.getElementById('recipient_name').value;
    const recipientPhone = document.getElementById('recipient_phone').value;
    const addressLine = document.getElementById('cust_address_line').value;
    const city = document.getElementById('cust_city').value;
    const zip = document.getElementById('cust_zip').value;
    const address = `${addressLine}, ${city}, ${zip}`;
    const date = document.getElementById('del_date').value;
    const time = document.getElementById('del_time').value;
    const finalPayable = baseTotal + currentShippingFee - discountVal;

    const orderData = {
        name: senderName,
        phone: senderPhone,
        email: document.getElementById('cust_email').value,
        address: address,
        date: date,
        items: baseCartItems.join('\n'),
        total: finalPayable,
        shipping_fee: currentShippingFee,
        distance_km: currentDistanceKm,
        discount_amount: discountVal,
        coupon_code: "<?= $couponCode ?>",
        csrf_token: "<?= csrf_token() ?>"
    };

    fetch('/actions/submit_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            let msg = `*✨ NEW ORDER ✨*\n\n`;
            msg += `*🛍️ ITEMS:*\n${baseCartItems.join('\n')}\n\n`;
            msg += `*💰 TOTAL: ₹${finalPayable}*\n`;
            msg += `*(Incl. Shipping: ₹${currentShippingFee} for ${currentDistanceText})*\n\n`;
            msg += `*📍 DELIVERY:* ${date} | ${time}\n`;
            msg += `*👤 SENDER:* ${senderName} (${senderPhone})\n`;
            msg += `*🎁 RECIPIENT:* ${recipientName} (${recipientPhone})\n`;
            msg += `*📍 ADDRESS:* ${address}`;

            const whatsappUrl = `https://wa.me/918802004527?text=${encodeURIComponent(msg)}`;
            window.open(whatsappUrl, '_blank');
            window.location.href = '/index.php?order_success=1&oid=' + data.order_id;
        } else {
            alert('Error: ' + data.message);
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred. Please try again.');
        btn.disabled = false;
    });
});

if (document.getElementById('cust_address_line').value.trim()) {
    scheduleShippingCalculation();
} else {
    updateTotal();
}
</script>
</body>
</html>