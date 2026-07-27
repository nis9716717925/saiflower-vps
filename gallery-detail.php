<?php
require_once __DIR__ . '/config.php';

// 1. THEME INTELLIGENCE
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);

$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#ffffff';
$tCol = $settings['theme_text_color'] ?? '#333333';
$fFam = $settings['theme_font'] ?? "'Plus Jakarta Sans', sans-serif";

// 2. GET GIFT DATA (By Slug or ID)
$gift = null;
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    $stmt = $conn->prepare("SELECT * FROM gifts WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $gift = $stmt->get_result()->fetch_assoc();
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM gifts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $gift = $stmt->get_result()->fetch_assoc();
}

if (!$gift) { header("Location: gifts.php"); exit; }

$id = $gift['id'];
$in_stock = (bool)$gift['in_stock']; // Stock Status Logic
$page_title = $gift['meta_title'] ?: $gift['name'] . ' | Sai Gifts';
$meta_desc = $gift['meta_description'] ?: mb_strimwidth(strip_tags($gift['description']), 0, 160, "...");

if (($settings['maintenance_mode'] ?? 0) == 1) { header("Location: maintenance.php"); exit; }

// Wishlist Check
$is_wishlisted = false;
if(isset($_SESSION['customer_id'])) {
    $uid = $_SESSION['customer_id'];
    $wQ = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id = $uid AND product_id = $id AND type = 'gift'");
    if($wQ && mysqli_num_rows($wQ) > 0) $is_wishlisted = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([
        ['name' => 'Gallery', 'item' => 'gallery.php'],
        ['name' => $gift['name'], 'item' => 'gallery-detail.php?id=' . $gift['id']]
    ]);
    ?>
    <meta charset="UTF-8">
    <?= render_canonical_link() ?>

    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png" type="image/x-icon">

    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>
    
    <style>
        :root {
            --primary: <?= $pCol ?>;
            --accent: <?= $sCol ?>;
            --bg-site: <?= $bgColor ?>;
            --text-main: <?= $tCol ?>;
            --font-main: <?= $fFam ?>;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-site); color: var(--text-main); margin: 0; line-height: 1.6; }
        h1, h2, h3, .price-display { font-family: var(--font-main); }
        .container { max-width: 1200px; margin: auto; padding: 0 20px; }
        
        .product-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 60px; padding: 40px 0; }
        
        .gallery-sticky { position: sticky; top: 100px; }
        .main-image-container { 
            border-radius: 30px; 
            overflow: hidden; 
            background: #fdfdfd; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
            height: 550px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #mainModel, #mainView { 
            width: 100%; 
            height: 100%; 
            position: absolute; 
            top: 0; 
            left: 0; 
            object-fit: cover;
            transition: opacity 0.3s ease;
        }

        .hidden-media { opacity: 0 !important; pointer-events: none; z-index: 0; visibility: hidden; }
        .visible-media { opacity: 1 !important; pointer-events: auto; z-index: 10; visibility: visible; }

        .badge { display: inline-block; padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .badge-premium { background: rgba(var(--primary-rgb), 0.1); color: var(--primary); }
        .price-display { font-size: 2.2rem; font-weight: 800; color: var(--primary); margin: 15px 0; display: flex; align-items: center; gap: 15px; }
        .price-old { font-size: 1.2rem; color: #bbb; text-decoration: line-through; font-weight: 400; }
        .buy-action-card { background: white; border-radius: 24px; padding: 30px; border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin: 30px 0; }
        .btn-main { background: var(--primary); color: white; padding: 18px 30px; border-radius: 50px; font-weight: 700; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; border:none; cursor:pointer;}
        .btn-main:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .btn-disabled { background: #cbd5e1; color: #64748b; cursor: not-allowed; }
        .quantity-control { display: flex; align-items: center; border: 1.5px solid #eee; border-radius: 50px; padding: 5px; width: fit-content; background: #fafafa; }
        .qty-btn { width: 35px; height: 35px; border-radius: 50%; border: none; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

        .swiper-pagination-bullet-active { background: var(--primary) !important; }

        @media (max-width: 991px) {
            .product-grid { grid-template-columns: 1fr; gap: 30px; }
            .gallery-sticky { position: static; }
            .main-image-container { height: 400px; border-radius: 20px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <div class="py-6 text-xs text-slate-400 font-medium uppercase tracking-wider">
        <a href="/index.php" class="hover:text-primary">Home</a> / 
        <a href="/gifts.php" class="hover:text-primary">Gifts</a> / 
        <span class="text-slate-600"><?= htmlspecialchars($gift['name']) ?></span>
    </div>

    <div class="product-grid">
        <div class="gallery-sticky">
            <div class="main-image-container" id="mediaContainer">
                <button onclick="toggleWishlist(this, <?= $id ?>, 'gift')" class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-md transition-colors z-20 hover:bg-red-50 <?= $is_wishlisted ? 'text-red-500' : 'text-slate-400' ?>">
                    <span class="material-icons-outlined text-2xl"><?= $is_wishlisted ? 'favorite' : 'favorite_border' ?></span>
                </button>
                <?php 
                $dbImg = $gift['image'];
                $finalImg = get_image_url($dbImg, 'gifts');
                ?>

                <img src="<?= $finalImg ?>" id="mainView" 
                     class="visible-media" 
                     alt="<?= htmlspecialchars($gift['name']) ?>" 
                     onerror="this.src='https://placehold.co/600x600?text=Gift'">
            </div>
            
            <div class="flex gap-3 mt-4 overflow-x-auto pb-2">
                <div class="w-20 h-20 rounded-xl overflow-hidden cursor-pointer border-2 border-primary hover:border-slate-300 shrink-0" 
                     onclick="switchMedia('image', this, '<?= $finalImg ?>')">
                    <img src="<?= $finalImg ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($gift['name']) ?>">
                </div>
            </div>
        </div>

        <div class="product-details">
            <span class="badge badge-premium">Special Gift</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-800 leading-tight mb-2"><?= htmlspecialchars($gift['name']) ?></h1>
            
            <div class="flex items-center gap-1 text-yellow-400 text-sm mb-4">
                <i class="fas fa-star"></i>
                <span class="text-slate-600 font-bold ml-1 text-lg"><?= number_format($gift['rating'] ?? 5.0, 1) ?></span>
                <span class="text-slate-400 text-xs ml-2">(120+ Reviews)</span>
            </div>

            <?php if(!empty($gift['tag'])): 
                $tags = array_filter(explode(',', $gift['tag']));
                if(count($tags) > 0):
            ?>
            <div class="flex flex-wrap gap-2 mb-4">
                <?php foreach($tags as $t): ?>
                    <a href="/tag.php?name=<?= urlencode(trim($t)) ?>" class="bg-slate-100 text-slate-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide border border-slate-200 hover:bg-primary hover:text-white transition-colors cursor-pointer"><?= htmlspecialchars(trim($t)) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; endif; ?>

            <div class="flex flex-col gap-2 mb-6 text-xs font-bold text-slate-600">
                <?php if(!isset($gift['delivery_sameday']) || $gift['delivery_sameday'] == 1): ?>
                    <div class="flex items-center gap-2 text-green-600 bg-green-50 w-fit px-3 py-1.5 rounded-lg border border-green-100"><span class="material-icons-outlined text-sm">local_shipping</span> Same Day Delivery Available</div>
                <?php elseif(isset($gift['delivery_nextday']) && $gift['delivery_nextday'] == 1): ?>
                    <div class="flex items-center gap-2 text-blue-600 bg-blue-50 w-fit px-3 py-1.5 rounded-lg border border-blue-100"><span class="material-icons-outlined text-sm">event_available</span> Next Day Delivery</div>
                <?php endif; ?>
            </div>

            <div class="price-display" id="priceDisplayBlock">
                <?php if(!$in_stock): ?>
                    <span class="text-red-500">Currently Sold Out</span>
                <?php elseif(!empty($gift['original_price']) && $gift['original_price'] > $gift['price']): 
                    $disc = round(($gift['original_price'] - $gift['price']) / $gift['original_price'] * 100); ?>
                    <span class="price-old">₹<?= number_format($gift['original_price']) ?></span>
                    <span class="price-current">₹<?= number_format($gift['price']) ?></span>
                    <span class="bg-red-500 text-white text-[10px] px-2 py-1 rounded-md discount-badge"><?= $disc ?>% OFF</span>
                <?php else: ?>
                    <span id="priceText" class="price-current">₹<?= number_format($gift['price']) ?></span>
                <?php endif; ?>
            </div>

            <p class="text-slate-500 text-lg mb-8"><?= nl2br(htmlspecialchars(mb_strimwidth($gift['description'], 0, 200, "..."))) ?></p>

            <?php if(!$in_stock): ?>
                <div class="bg-red-50 border border-red-100 p-6 rounded-3xl mb-8">
                    <p class="text-red-600 font-bold flex items-center gap-2">
                        <span class="material-icons-outlined">info</span>
                        This item is currently out of stock. We'll be back soon!
                    </p>
                </div>
            <?php endif; ?>

            <div class="buy-action-card <?= !$in_stock ? 'opacity-60 grayscale' : '' ?>">
                <form action="/cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($gift['name']) ?>">
                    <input type="hidden" name="price" id="formPrice" value="<?= $gift['price'] ?>">
                    <input type="hidden" name="image" value="<?= $gift['image'] ?>">
                    <input type="hidden" name="category" value="gift">
                    <input type="hidden" name="add_to_cart" value="1">
                    <?php csrf_field(); ?>

                    <div class="flex flex-col gap-6">
                        <?php
                        $vars = mysqli_query($conn, "SELECT * FROM gift_variants WHERE gift_id = $id");
                        if(mysqli_num_rows($vars) > 0): ?>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 mb-3">Choose Option</label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="variant-btn active px-4 py-2 border-2 border-primary text-primary rounded-xl font-bold text-sm" data-price="<?= $gift['price'] ?>" data-orig-price="<?= $gift['original_price'] ?>" onclick="setVariant(this)" <?= !$in_stock ? 'disabled' : '' ?>>Standard</button>
                                <?php while($v = mysqli_fetch_assoc($vars)): ?>
                                <button type="button" class="variant-btn px-4 py-2 border-2 border-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:border-slate-300" data-price="<?= $v['price'] ?>" data-orig-price="<?= $v['original_price'] ?>" onclick="setVariant(this)" <?= !$in_stock ? 'disabled' : '' ?>><?= htmlspecialchars($v['name']) ?></button>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center gap-4">
                            <div class="quantity-control">
                                <button type="button" class="qty-btn" onclick="adjQty(-1)" <?= !$in_stock ? 'disabled' : '' ?>><i class="fas fa-minus text-xs"></i></button>
                                <input type="number" name="quantity" id="pQty" value="1" min="1" readonly class="w-12 text-center bg-transparent border-none font-bold text-lg focus:ring-0 shadow-none">
                                <button type="button" class="qty-btn" onclick="adjQty(1)" <?= !$in_stock ? 'disabled' : '' ?>><i class="fas fa-plus text-xs"></i></button>
                            </div>
                            <?php if($in_stock): ?>
                                <button type="submit" class="btn-main">Buy Now</button>
                            <?php else: ?>
                                <button type="button" class="btn-main btn-disabled" disabled>Sold Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="py-16 border-t border-slate-100 mt-10">
        <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-8">You May Also Like</h2>
        <div class="swiper swiper-recommendations">
            <div class="swiper-wrapper">
                <?php 
                $recQ = mysqli_query($conn, "SELECT * FROM gifts WHERE id != $id AND status = 1 ORDER BY RAND() LIMIT 10");
                while($rec = mysqli_fetch_assoc($recQ)):
                    $recImg = (strpos($rec['image'], 'uploads/') === 0) ? "/" . $rec['image'] : "/uploads/" . $rec['image'];
                    $recLink = product_url(['type' => 'gift', 'slug' => $rec['slug'] ?? '', 'id' => $rec['id']]);
                ?>
                <div class="swiper-slide">
                    <a href="<?= $recLink ?>" class="group block bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-500">
                        <div class="aspect-[4/5] overflow-hidden">
                            <img src="<?= $recImg ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="<?= htmlspecialchars($rec['name']) ?> gift" onerror="this.src='https://placehold.co/400x500?text=Gift'">
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($rec['name']) ?></h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-primary font-bold">₹<?= number_format($rec['price']) ?></span>
                                <span class="material-icons-outlined text-slate-300 text-lg group-hover:text-primary transition-colors">shopping_bag</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="swiper-pagination mt-8 relative"></div>
        </div>
    </section>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    new Swiper(".swiper-recommendations", {
        slidesPerView: 1.5,
        spaceBetween: 20,
        pagination: { el: ".swiper-pagination", clickable: true },
        breakpoints: {
            640: { slidesPerView: 2.5 },
            1024: { slidesPerView: 4.5, spaceBetween: 30 }
        }
    });

    function switchMedia(type, element, src = '') {
        const imgViewer = document.getElementById('mainView');
        const modelViewer = document.getElementById('mainModel');

        element.parentElement.querySelectorAll('div').forEach(d => d.classList.remove('border-primary'));
        element.classList.add('border-primary');

        if (type === '3d' && modelViewer) {
            modelViewer.classList.add('visible-media');
            modelViewer.classList.remove('hidden-media');
            imgViewer.classList.add('hidden-media');
            imgViewer.classList.remove('visible-media');
        } else {
            if(modelViewer) {
                modelViewer.classList.add('hidden-media');
                modelViewer.classList.remove('visible-media');
            }
            imgViewer.src = src;
            imgViewer.classList.add('visible-media');
            imgViewer.classList.remove('hidden-media');
        }
    }

    function adjQty(val) {
        let q = document.getElementById('pQty');
        let n = parseInt(q.value) + val;
        if(n > 0) q.value = n;
    }

    function setVariant(btn) {
        document.querySelectorAll('.variant-btn').forEach(b => {
            b.classList.remove('active', 'border-primary', 'text-primary');
            b.classList.add('border-slate-100', 'text-slate-600');
        });
        btn.classList.add('active', 'border-primary', 'text-primary');
        
        let price = parseFloat(btn.getAttribute('data-price')) || 0;
        let origPriceAttr = btn.getAttribute('data-orig-price');
        let origPrice = origPriceAttr && origPriceAttr !== '' ? parseFloat(origPriceAttr) : 0;
        
        let priceBlock = document.getElementById('priceDisplayBlock');
        if(priceBlock) {
            if(origPrice > price) {
                let disc = Math.round(((origPrice - price) / origPrice) * 100);
                priceBlock.innerHTML = `
                    <span class="price-old">₹${origPrice.toLocaleString('en-IN')}</span>
                    <span class="price-current">₹${price.toLocaleString('en-IN')}</span>
                    <span class="bg-red-500 text-white text-[10px] px-2 py-1 rounded-md discount-badge">${disc}% OFF</span>
                `;
            } else {
                priceBlock.innerHTML = `<span id="priceText" class="price-current">₹${price.toLocaleString('en-IN')}</span>`;
            }
        }
        
        if(document.getElementById('formPrice')) {
            document.getElementById('formPrice').value = price;
        }
    }

    function toggleWishlist(btn, productId, type) {
        fetch('actions/toggle_wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, type: type })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const icon = btn.querySelector('.material-icons-outlined');
                if(data.action === 'added') {
                    icon.textContent = 'favorite';
                    btn.classList.add('text-red-500');
                    btn.classList.remove('text-slate-400');
                } else {
                    icon.textContent = 'favorite_border';
                    btn.classList.remove('text-red-500');
                    btn.classList.add('text-slate-400');
                }
            } else if(data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }
</script>
</body>
</html>