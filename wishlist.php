<?php
// wishlist.php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php?redirect=wishlist.php");
    exit;
}

$user_id = $_SESSION['customer_id'];
$wishlistItems = [];

// Fetch wishlist items (Flowers for now)
try {
    $query = "
        SELECT f.*, w.id as wishlist_id, 'flower' as item_type 
        FROM wishlist w 
        JOIN flowers f ON w.product_id = f.id 
        WHERE w.user_id = $user_id AND w.type = 'flower'
        ORDER BY w.created_at DESC
    ";
    $res = $conn->query($query);
    if($res) {
        while($row = $res->fetch_assoc()) {
            $wishlistItems[] = $row;
        }
    }
} catch (Exception $e) {
    // Ignore error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_canonical_link() ?>
    <title>My Wishlist | Sai Flower</title>
    <meta name="robots" content="noindex, nofollow">
    <?php include 'partials/tailwind_config.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
     
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased">

<?php include 'partials/navbar.php'; ?>

<main class="container mx-auto px-4 py-8 md:py-12 min-h-[60vh]">
    <h1 class="text-3xl font-bold mb-8">My Wishlist</h1>

    <?php if (empty($wishlistItems)): ?>
        <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
            <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 text-red-300">
                <span class="material-icons-outlined text-4xl">favorite_border</span>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Your wishlist is empty</h2>
            <p class="text-slate-500 mb-8">Save items you love to find them easily later.</p>
            <a href="flowers.php" class="inline-block bg-primary text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition transform">
                Explore Flowers
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
            <?php foreach($wishlistItems as $f): 
                $p_link = product_url(['type' => $f['type'] ?? 'flower', 'slug' => $f['slug'] ?? '', 'id' => $f['id']]); 
                $finalImagePath = get_image_url($f['image'], 'flowers');
            ?>
            <div class="group bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-primary/20 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col relative">
                
                <!-- Remove Button -->
                <button onclick="toggleWishlist(this, <?= $f['id'] ?>, 'flower')" class="absolute top-2 right-2 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-red-500 shadow-sm transition-colors z-10 hover:bg-red-50" title="Remove from Wishlist">
                    <span class="material-icons-outlined text-lg">delete_outline</span>
                </button>

                <div class="aspect-[4/5] overflow-hidden bg-slate-100 relative">
                    <a href="<?= $p_link ?>">
                        <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                             src="<?= $finalImagePath ?>" 
                             alt="<?= htmlspecialchars($f['name']) ?>">
                    </a>
                </div>
                
                <div class="p-4 flex flex-col flex-grow">
                    <h3 class="font-bold text-sm mb-1 truncate text-slate-900 group-hover:text-primary transition-colors">
                        <a href="<?= $p_link ?>"><?= htmlspecialchars($f['name']) ?></a>
                    </h3>
                    <div class="mt-auto flex items-center justify-between">
                         <p class="font-bold text-primary">₹<?= number_format($f['price']) ?></p>
                         <form action="cart.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="product_id" value="<?= $f['id'] ?>">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($f['name']) ?>">
                            <input type="hidden" name="price" value="<?= $f['price'] ?>">
                            <input type="hidden" name="image" value="<?= htmlspecialchars($f['image']) ?>">
                            <input type="hidden" name="category" value="flower">
                            <input type="hidden" name="add_to_cart" value="1">
                            <button type="submit" class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                                <span class="material-icons-outlined text-sm">add</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<script>
    function toggleWishlist(btn, productId, type) {
        if(!confirm('Remove this item from your wishlist?')) return;
        
        fetch('actions/toggle_wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, type: type })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success && data.action === 'removed') {
                // Remove the card from DOM
                const card = btn.closest('.group');
                card.remove();
                // Check if empty
                const grid = document.querySelector('.grid');
                if(grid && grid.children.length === 0) {
                    location.reload(); // Reload to show empty state
                }
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>
