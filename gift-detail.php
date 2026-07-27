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

enforce_canonical_product_url('gift', $gift);
set_page_canonical_url(get_product_canonical_url('gift', $gift));

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
$finalImg = get_image_url($gift['image'] ?? '', 'gifts');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/partials/tailwind_config.php"; ?>
    <?php 
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_product_json_ld($gift, 'gift');
    echo generate_simple_breadcrumb_json_ld([
        ['name' => 'Gifts', 'item' => 'gifts.php'],
        ['name' => $gift['name'], 'item' => product_url(['type' => 'gift', 'slug' => $gift['slug'] ?? '', 'id' => $gift['id']])]
    ]);
    ?>
    <meta charset="UTF-8">

    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png" type="image/x-icon">

    <title><?= htmlspecialchars($page_title) ?></title>
    <?= render_canonical_link() ?>
    <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/assets/css/product-detail-premium.css" />
    <link rel="preload" href="<?= htmlspecialchars($finalImg) ?>" as="image" fetchpriority="high">
    <script defer src="/assets/js/product-detail-premium.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>
    <style>
        :root {
            --primary: <?= htmlspecialchars($pCol) ?>;
            --accent: <?= htmlspecialchars($sCol) ?>;
            --bg-site: <?= htmlspecialchars($bgColor) ?>;
            --text-main: <?= htmlspecialchars($tCol) ?>;
            --font-main: <?= htmlspecialchars($fFam) ?>;
        }
        body.product-detail-page { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-site); color: var(--text-main); margin: 0; line-height: 1.6; }
        h1, h2, h3 { font-family: var(--font-main); }
        .swiper-pagination-bullet-active { background: var(--primary) !important; }
    </style>
</head>
<body class="product-detail-page">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="pd-container pd-page">
    <nav class="pd-breadcrumb" aria-label="Breadcrumb">
        <a href="/index.php">Home</a>
        <span aria-hidden="true"> / </span>
        <a href="/gifts.php">Gifts</a>
        <span aria-hidden="true"> / </span>
        <span class="pd-bc-current"><?= htmlspecialchars($gift['name']) ?></span>
    </nav>

    <div class="pd-grid">
        <div class="pd-gallery-col pd-gallery-sticky">
            <div class="pd-media-wrap is-zoomable" id="mediaContainer">
                <span class="pd-zoom-hint">Tap to enlarge</span>
                <button type="button" onclick="toggleWishlist(this, <?= $id ?>, 'gift')" class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-md transition-colors z-20 hover:bg-red-50 <?= $is_wishlisted ? 'text-red-500' : 'text-slate-400' ?>" aria-label="Wishlist">
                    <span class="material-icons-outlined text-2xl"><?= $is_wishlisted ? 'favorite' : 'favorite_border' ?></span>
                </button>
                <?php 
                if(!empty($gift['model_3d'])): ?>
                    <model-viewer id="mainModel" 
                        src="/<?= htmlspecialchars($gift['model_3d']) ?>" 
                        auto-rotate camera-controls ar shadow-intensity="1" 
                        tone-mapping="commerce" exposure="1"
                        loading="lazy"
                        class="visible-media">
                    </model-viewer>
                <?php endif; ?>

                <img src="<?= htmlspecialchars($finalImg) ?>" id="mainView" width="800" height="800" fetchpriority="high"
                     class="<?= !empty($gift['model_3d']) ? 'hidden-media' : 'visible-media' ?>" 
                     alt="<?= htmlspecialchars($gift['image_alt'] ?? $gift['name']) ?>" 
                     onerror="this.src='https://placehold.co/600x600?text=Gift'">
            </div>
            
            <div class="pd-thumbs" role="list">
                <?php
                $galleryCount = 0;
                $maxGallery = 2;

                if(!empty($gift['model_3d'])): ?>
                <div role="listitem" class="pd-thumb <?= !empty($gift['model_3d']) ? 'is-active' : '' ?> w-20 h-20 rounded-xl overflow-hidden cursor-pointer border-2 border-primary flex flex-col items-center justify-center bg-slate-50 shrink-0" 
                     onclick="switchMedia('3d', this)">
                    <span class="material-icons-outlined text-primary" style="font-size: 24px;">view_in_ar</span>
                    <span class="text-[9px] font-black uppercase text-primary">3D View</span>
                </div>
                <?php endif; ?>

                <div role="listitem" class="pd-thumb <?= empty($gift['model_3d']) ? 'is-active' : '' ?> w-20 h-20 rounded-xl overflow-hidden cursor-pointer border-2 <?= empty($gift['model_3d']) ? 'border-primary' : 'border-transparent' ?> hover:border-slate-300 shrink-0" 
                     onclick="switchMedia('image', this, '<?= htmlspecialchars($finalImg, ENT_QUOTES) ?>')">
                    <img src="<?= htmlspecialchars($finalImg) ?>" class="w-full h-full object-cover" width="80" height="80" loading="lazy" decoding="async" alt="<?= htmlspecialchars($gift['name']) ?>">
                </div>
                <?php
                if($galleryCount < $maxGallery && !empty($gift['images_gallery'])) {
                    $gallery = json_decode($gift['images_gallery'], true);
                    if($gallery) {
                        foreach($gallery as $gPath):
                            if ($galleryCount >= $maxGallery) break;
                            $fullGPath = get_image_url($gPath, 'gifts');
                ?>
                <div role="listitem" class="pd-thumb w-20 h-20 rounded-xl overflow-hidden cursor-pointer border-2 border-transparent hover:border-slate-200 shrink-0" 
                     onclick="switchMedia('image', this, '<?= htmlspecialchars($fullGPath, ENT_QUOTES) ?>')">
                    <img src="<?= htmlspecialchars($fullGPath) ?>" class="w-full h-full object-cover" width="80" height="80" loading="lazy" decoding="async" alt="<?= htmlspecialchars($gift['name']) ?> gallery">
                </div>
                <?php 
                            $galleryCount++;
                        endforeach;
                    }
                }
                ?>
            </div>
        </div>

        <div class="pd-buy-col pd-buy-sticky product-details">
            <div class="pd-badge-row">
                <?php if ($in_stock): ?>
                    <span class="pd-badge">Curated gift</span>
                    <?php if(!empty($gift['original_price']) && $gift['original_price'] > $gift['price']): ?>
                        <span class="pd-badge pd-badge-sale">Limited offer</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="pd-badge" style="background:#fee2e2;color:#991b1b;">Out of stock</span>
                <?php endif; ?>
            </div>

            <h1 class="pd-title"><?= htmlspecialchars($gift['name']) ?></h1>
            
            <div class="pd-rating-row">
                <span class="pd-stars" aria-hidden="true"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-stroke"></i></span>
                <span class="pd-rating-val"><?= number_format($gift['rating'] ?? 5.0, 1) ?></span>
                <span class="pd-rating-meta">120+ verified reviews</span>
                <span class="pd-rating-highlight">Top-rated gifting pick</span>
            </div>

            <?php if(!empty($gift['tag'])): 
                $tags = array_filter(explode(',', $gift['tag']));
                if(count($tags) > 0):
            ?>
            <div class="flex flex-wrap gap-2 mb-1">
                <?php foreach($tags as $t): ?>
                    <a href="/tag.php?name=<?= urlencode(trim($t)) ?>" class="bg-slate-100 text-slate-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide border border-slate-200 hover:bg-primary hover:text-white transition-colors cursor-pointer"><?= htmlspecialchars(trim($t)) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; endif; ?>

            <div class="pd-delivery-chips">
                <?php if(!isset($gift['delivery_sameday']) || $gift['delivery_sameday'] == 1): ?>
                    <div class="pd-chip pd-chip--green"><span class="material-icons-outlined text-sm">local_shipping</span> Same-day delivery available</div>
                <?php elseif(isset($gift['delivery_nextday']) && $gift['delivery_nextday'] == 1): ?>
                    <div class="pd-chip pd-chip--blue"><span class="material-icons-outlined text-sm">event_available</span> Next-day delivery</div>
                <?php endif; ?>
            </div>

            <div class="pd-price-block price-display" id="priceDisplayBlock">
                <?php if(!$in_stock): ?>
                    <span class="text-red-600 font-bold">Currently unavailable</span>
                <?php elseif(!empty($gift['original_price']) && $gift['original_price'] > $gift['price']): 
                    $surgedP = apply_surge_pricing($gift['price'], 'gift');
                    $disc = round(($gift['original_price'] - $surgedP) / $gift['original_price'] * 100); ?>
                    <span class="pd-price-old price-old">₹<?= number_format($gift['original_price']) ?></span>
                    <span class="pd-price-current price-current">₹<?= number_format($surgedP) ?></span>
                    <span class="pd-discount-pill discount-badge"><?= $disc ?>% OFF</span>
                <?php else: ?>
                    <span id="priceText" class="pd-price-current price-current">₹<?= number_format(apply_surge_pricing($gift['price'], 'gift')) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($in_stock): ?>
            <div class="pd-stock-row">
                <span class="pd-stock-ok"><i class="fas fa-circle-check" aria-hidden="true"></i> In stock — ready to dispatch</span>
                <span class="pd-urgency">Order in the next few hours for same-day priority</span>
            </div>
            <?php endif; ?>

            <div class="mb-2">
                <div id="descShort" class="pd-desc">
                    <?= nl2br(htmlspecialchars(mb_strimwidth($gift['description'], 0, 220, "..."))) ?>
                </div>
                <div id="descFull" class="pd-desc hidden">
                    <?= nl2br(htmlspecialchars($gift['description'])) ?>
                </div>
                <?php if(strlen($gift['description']) > 220): ?>
                <button type="button" onclick="toggleDescription()" id="readMoreBtn" class="pd-readmore">Read more</button>
                <?php endif; ?>
            </div>

            <?php include __DIR__ . '/partials/product_detail_trust.php'; ?>

            <?php if(!$in_stock): ?>
                <div class="bg-red-50 border border-red-100 p-6 rounded-3xl mb-2">
                    <p class="text-red-600 font-bold flex items-center gap-2">
                        <span class="material-icons-outlined">info</span>
                        This item is currently out of stock. We'll be back soon!
                    </p>
                </div>
            <?php endif; ?>

            <div class="pd-buy-card buy-action-card <?= !$in_stock ? 'opacity-60 grayscale' : '' ?>">
                <form id="productPurchaseForm" action="/cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($gift['name']) ?>">
                    <input type="hidden" name="price" id="formPrice" value="<?= apply_surge_pricing($gift['price'], 'gift') ?>">
                    <input type="hidden" name="image" value="<?= $gift['image'] ?>">
                    <input type="hidden" name="category" value="gift">
                    <?php csrf_field(); ?>

                    <div class="flex flex-col gap-6">
                        <?php
                        $vars = mysqli_query($conn, "SELECT * FROM gift_variants WHERE gift_id = $id");
                        if(mysqli_num_rows($vars) > 0): ?>
                        <div>
                            <label class="pd-variant-label">Choose option</label>
                            <div class="pd-variant-btns" id="variantButtons">
                                <button type="button" class="variant-btn pd-variant-btn active" data-price="<?= apply_surge_pricing($gift['price'], 'gift') ?>" data-orig-price="<?= $gift['original_price'] ?? '' ?>" onclick="setVariant(this)" <?= !$in_stock ? 'disabled' : '' ?>>Standard</button>
                                <?php while($v = mysqli_fetch_assoc($vars)): ?>
                                <button type="button" class="variant-btn pd-variant-btn" data-price="<?= apply_surge_pricing($v['price'], 'gift') ?>" data-orig-price="<?= $v['original_price'] ?? '' ?>" onclick="setVariant(this)" <?= !$in_stock ? 'disabled' : '' ?>><?= htmlspecialchars($v['name']) ?></button>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="pd-qty-row">
                            <div class="quantity-control pd-qty">
                                <button type="button" class="qty-btn" onclick="adjQty(-1)" <?= !$in_stock ? 'disabled' : '' ?> aria-label="Decrease quantity"><i class="fas fa-minus text-xs"></i></button>
                                <input type="number" name="quantity" id="pQty" value="1" min="1" readonly class="w-12 text-center bg-transparent border-none font-bold text-lg focus:ring-0 shadow-none" aria-live="polite">
                                <button type="button" class="qty-btn" onclick="adjQty(1)" <?= !$in_stock ? 'disabled' : '' ?> aria-label="Increase quantity"><i class="fas fa-plus text-xs"></i></button>
                            </div>
                        </div>

                        <div class="pd-cta-row pd-cta-row--main">
                            <?php if($in_stock): ?>
                                <button type="button" id="pdBtnAddCart" class="pd-btn pd-btn--outline" onclick="pdSubmitPurchase('add')"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Add to Cart</button>
                                <button type="button" id="pdBtnBuyNow" class="pd-btn pd-btn--primary" onclick="pdSubmitPurchase('buy')"><i class="fas fa-bolt" aria-hidden="true"></i> Buy Now</button>
                            <?php else: ?>
                                <button type="button" id="pdBtnAddCart" class="pd-btn pd-btn--outline" disabled>Sold Out</button>
                                <button type="button" id="pdBtnBuyNow" class="pd-btn pd-btn--primary" disabled>Sold Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="content-visibility-auto">
        <h2 class="pd-section-title">You May Also Like</h2>
        <div class="swiper swiper-recommendations">
            <div class="swiper-wrapper">
                <?php 
                $recQ = mysqli_query($conn, "SELECT * FROM gifts WHERE id != $id AND status = 1 ORDER BY RAND() LIMIT 10");
                while($rec = mysqli_fetch_assoc($recQ)):
                    $recImg = (strpos($rec['image'], 'uploads/') === 0) ? "/" . $rec['image'] : "/uploads/" . $rec['image'];
                    $recLink = product_url(['type' => 'gift', 'slug' => $rec['slug'] ?? '', 'id' => $rec['id']]);
                ?>
                <div class="swiper-slide">
                    <a href="<?= $recLink ?>" class="pd-rec-card group block rounded-3xl overflow-hidden">
                        <div class="aspect-[4/5] overflow-hidden">
                            <img src="<?= $recImg ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" width="400" height="500" loading="lazy" decoding="async" alt="<?= htmlspecialchars($rec['name']) ?> gift" onerror="this.src='https://placehold.co/400x500?text=Gift'">
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($rec['name']) ?></h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-primary font-bold">₹<?= number_format(apply_surge_pricing($rec['price'], 'gift')) ?></span>
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
    
    <?php if(!empty($gift['faqs'])): 
        $faqs = json_decode($gift['faqs'], true);
        if(is_array($faqs) && count($faqs) > 0):
    ?>
    <section class="pd-faq-wrap mt-12 mb-12 content-visibility-auto">
        <h2 class="pd-faq-title">Frequently Asked Questions</h2>
        <div>
            <?php foreach($faqs as $index => $faq): ?>
            <details class="pd-faq-item group">
                <summary>
                    <span class="text-base md:text-lg pr-2"><?= htmlspecialchars($faq['question']) ?></span>
                    <span class="pd-faq-chevron"><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                </summary>
                <div class="pd-faq-body">
                    <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
$pdStickyInStock = $in_stock;
include __DIR__ . '/partials/product_detail_sticky_zoom.php';
?>

<script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        new Swiper(".swiper-recommendations", {
            slidesPerView: 1.5,
            spaceBetween: 20,
            pagination: { el: ".swiper-pagination", clickable: true },
            breakpoints: {
                640: { slidesPerView: 2.5 },
                1024: { slidesPerView: 4.5, spaceBetween: 30 }
            }
        });
    });

    function switchMedia(type, element, src = '') {
        const imgViewer = document.getElementById('mainView');
        const modelViewer = document.getElementById('mainModel');

        element.parentElement.querySelectorAll('.pd-thumb').forEach(d => {
            d.classList.remove('is-active', 'border-primary');
        });
        element.classList.add('is-active', 'border-primary');

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
            b.classList.remove('active');
        });
        btn.classList.add('active');

        let price = parseFloat(btn.getAttribute('data-price')) || 0;
        let origPriceAttr = btn.getAttribute('data-orig-price');
        let origPrice = origPriceAttr && origPriceAttr !== '' ? parseFloat(origPriceAttr) : 0;

        let priceBlock = document.getElementById('priceDisplayBlock');
        if(priceBlock) {
            if(origPrice > price) {
                let disc = Math.round(((origPrice - price) / origPrice) * 100);
                priceBlock.innerHTML = `
                    <span class="pd-price-old price-old">₹${origPrice.toLocaleString('en-IN')}</span>
                    <span class="pd-price-current price-current">₹${price.toLocaleString('en-IN')}</span>
                    <span class="pd-discount-pill discount-badge">${disc}% OFF</span>
                `;
            } else {
                priceBlock.innerHTML = `<span id="priceText" class="pd-price-current price-current">₹${price.toLocaleString('en-IN')}</span>`;
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

    function toggleDescription() {
        const shortDesc = document.getElementById("descShort");
        const fullDesc = document.getElementById("descFull");
        const btn = document.getElementById("readMoreBtn");
        if (!shortDesc || !fullDesc || !btn) return;

        if(fullDesc.classList.contains("hidden")){
            shortDesc.classList.add("hidden");
            fullDesc.classList.remove("hidden");
            btn.innerText = "Show less";
        } else {
            shortDesc.classList.remove("hidden");
            fullDesc.classList.add("hidden");
            btn.innerText = "Read more";
        }
    }
</script>
</body>
</html>