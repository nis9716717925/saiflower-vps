<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/shop_merchandising.php';

// 1. FETCH SETTINGS
$settingsQuery = mysqli_query($conn, 'SELECT * FROM settings WHERE id=1');
$settings = mysqli_fetch_assoc($settingsQuery) ?: [];
$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';

// 2. FETCH ACTIVE PROMO
$promo = null;
$promoQ = @mysqli_query($conn, 'SELECT * FROM promo_codes WHERE status=1 AND show_on_flowers=1 ORDER BY created_at DESC LIMIT 1');
if ($promoQ) {
    $promo = mysqli_fetch_assoc($promoQ);
}

// 3. FILTER + FLOWER-FIRST SORT (same shop structure, smarter product order)
$filters = shop_parse_request_filters($_GET);
$sort = isset($_GET['sort']) ? preg_replace('/[^a-z_]/', '', strtolower((string) $_GET['sort'])) : 'bestseller';
if ($sort === '' || $sort === 'new') {
    $sort = ($sort === 'new') ? 'newest' : 'bestseller';
}
$allowedSort = ['bestseller', 'rating', 'trending', 'newest', 'price_low', 'price_high', 'name'];
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'bestseller';
}

$allProducts = shop_fetch_all_active_flowers($conn);
$products = shop_apply_filters_and_sort($allProducts, $filters, $sort);
$total_flowers = count($products);

// 4. CATEGORIES (flower categories first)
$all_categories = [];
$categories_res = @mysqli_query($conn, 'SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC, name ASC');
if (!$categories_res) {
    $categories_res = @mysqli_query($conn, 'SELECT * FROM categories WHERE status = 1 ORDER BY name ASC');
}
if ($categories_res) {
    while ($cat = mysqli_fetch_assoc($categories_res)) {
        $all_categories[] = $cat;
    }
}
$all_categories = shop_order_categories_flower_first($all_categories);

$faqs = mysqli_query($conn, "SELECT * FROM faqs WHERE (page = 'flowers' OR page = 'general') AND status = 1 LIMIT 5");
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

$schemaItems = array_slice($products, 0, 36);
$activeCategory = $filters['category'] ?? null;
$hasPriceOrCat = !empty($filters['price_min']) || !empty($filters['price_max']) || !empty($activeCategory);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    
    <?php require_once __DIR__ . '/includes/seo_helper.php'; ?>
    <?= render_seo('flowers.php'); ?>

    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    $currentUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'saiflower.com') . ($_SERVER['REQUEST_URI'] ?? '/flowers');
    echo generate_listing_json_ld($schemaItems, 'flower', $currentUrl);
    ?>

    <style>
        :root { --primary: <?= htmlspecialchars($pCol) ?>; --accent: <?= htmlspecialchars($sCol) ?>; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f7f4ee; }
        .faq-item-box.active .faq-answer { max-height: 500px; padding-bottom: 20px; }
        
        #mobileFilter { transition: transform 0.3s ease-in-out; }
        #mobileFilter.open { transform: translateX(0); }
        #filterOverlay.open { display: block; }

        @media (max-width: 1023px) {
            #mobileFooterLinks,
            #mobileBottomNav,
            #mobileBottomNavSpacer {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-[#f7f4ee] text-slate-900 font-sans antialiased">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<!-- MOBILE HORIZONTAL SCROLLABLE FILTERS -->
<div class="md:hidden bg-white border-b-2 border-slate-200 py-3 px-3 flex items-center overflow-x-auto gap-3 no-scrollbar shadow-[0_4px_12px_rgba(0,0,0,0.08)] relative z-40">
    <button onclick="toggleMobileFilter()" class="flex items-center gap-1.5 bg-slate-100 border-2 border-slate-200 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex-shrink-0 uppercase tracking-tight text-slate-800">
        <span class="material-icons-outlined text-base">tune</span> Filters
    </button>
    
    <div class="h-6 w-[2px] bg-slate-200 flex-shrink-0 rounded-full"></div>

    <button onclick="toggleMobileFilter()" class="flex items-center gap-1 bg-slate-100 border-2 border-slate-200 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex-shrink-0 uppercase tracking-tight text-slate-800">
         Sort
        <span class="material-icons-outlined text-base text-slate-400">expand_more</span>
    </button>
    
    <button onclick="toggleMobileFilter()" class="flex items-center gap-1 bg-slate-100 border-2 border-slate-200 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex-shrink-0 uppercase tracking-tight text-slate-800">
        Price
        <span class="material-icons-outlined text-base text-slate-400">expand_more</span>
    </button>

    <?php if ($hasPriceOrCat): ?>
        <a href="/flowers" class="bg-red-50 text-red-600 border-2 border-red-100 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex items-center gap-1 flex-shrink-0 uppercase tracking-tight">
            <span class="material-icons-outlined text-sm">close</span> Clear
        </a>
    <?php endif; ?>
</div>

<main class="container mx-auto px-2 md:px-4 pt-2 md:pt-8 pb-8 relative flex flex-row gap-4 md:gap-8 justify-center">

    <!-- SIDEBAR -->
    <aside class="sticky top-[80px] md:top-28 z-30 h-[calc(100vh-100px)] md:h-[calc(100vh-120px)] overflow-y-auto w-16 md:w-64 flex-shrink-0 bg-transparent md:bg-white md:rounded-2xl md:border border-slate-100 md:shadow-sm" style="-webkit-overflow-scrolling: touch;">
        
        <div class="md:hidden flex flex-col gap-4 pt-4 pb-32 items-center bg-white/80 backdrop-blur-md rounded-r-2xl border-r-2 border-y-2 border-slate-100 shadow-md">
            <a href="/flowers" class="flex flex-col items-center gap-1 group">
                <div class="w-11 h-11 rounded-full bg-slate-50 border-2 <?= empty($activeCategory) ? 'border-primary' : 'border-transparent shadow-sm' ?> flex items-center justify-center transition-all bg-white">
                    <span class="material-icons-outlined text-base <?= empty($activeCategory) ? 'text-primary' : 'text-slate-400' ?>">all_inclusive</span>
                </div>
                <span class="text-[10px] font-black text-center leading-tight <?= empty($activeCategory) ? 'text-primary' : 'text-slate-500' ?>">All</span>
            </a>
            <?php foreach ($all_categories as $cat): ?>
                <a href="/flowers?category=<?= (int) $cat['id'] ?>" class="flex flex-col items-center gap-1 group">
                    <div class="w-11 h-11 rounded-full bg-slate-50 border-2 <?= ((int) $activeCategory === (int) $cat['id']) ? 'border-primary' : 'border-transparent shadow-sm' ?> flex items-center justify-center overflow-hidden transition-all bg-white">
                        <?php if (!empty($cat['image'])): ?>
                            <img src="/uploads/categories/<?= htmlspecialchars($cat['image']) ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($cat['name']) ?> category">
                        <?php else: ?>
                            <span class="material-icons-outlined text-base <?= ((int) $activeCategory === (int) $cat['id']) ? 'text-primary' : 'text-slate-400' ?>">local_florist</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-[10px] font-black text-center leading-tight <?= ((int) $activeCategory === (int) $cat['id']) ? 'text-primary' : 'text-slate-500' ?>"><?= htmlspecialchars($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="hidden md:block space-y-6">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-6 text-slate-900 border-b pb-2">Categories</h2>
                <div class="grid grid-cols-2 lg:grid-cols-2 gap-4">
                    <a href="/flowers" class="flex flex-col items-center gap-2 group">
                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center border-2 <?= empty($activeCategory) ? 'border-primary' : 'border-transparent' ?> group-hover:border-primary transition-all overflow-hidden">
                            <span class="material-icons-outlined text-2xl <?= empty($activeCategory) ? 'text-primary' : 'text-slate-400' ?> group-hover:text-primary">all_inclusive</span>
                        </div>
                        <span class="text-xs font-black <?= empty($activeCategory) ? 'text-primary' : 'text-slate-600' ?> group-hover:text-primary">All</span>
                    </a>
                    <?php foreach ($all_categories as $cat): ?>
                        <a href="/flowers?category=<?= (int) $cat['id'] ?>" class="flex flex-col items-center gap-2 group">
                            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center border-2 <?= ((int) $activeCategory === (int) $cat['id']) ? 'border-primary' : 'border-transparent' ?> group-hover:border-primary transition-all overflow-hidden">
                                <?php if (!empty($cat['image'])): ?>
                                    <img src="/uploads/categories/<?= htmlspecialchars($cat['image']) ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($cat['name']) ?> category">
                                <?php else: ?>
                                    <span class="material-icons-outlined text-2xl <?= ((int) $activeCategory === (int) $cat['id']) ? 'text-primary' : 'text-slate-400' ?> group-hover:text-primary">local_florist</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-black text-center <?= ((int) $activeCategory === (int) $cat['id']) ? 'text-primary' : 'text-slate-600' ?> group-hover:text-primary"><?= htmlspecialchars($cat['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="p-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold">Filters</h2>
                    <?php if (!empty($filters['price_min']) || !empty($filters['price_max'])): ?>
                        <a href="/flowers" class="text-xs bg-red-50 text-red-500 px-2 py-1 rounded-md font-bold hover:bg-red-100 transition-colors">Clear All</a>
                    <?php endif; ?>
                </div>
                
                <form action="/flowers" method="GET" id="desktopFilterForm">
                     <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                     <?php if (!empty($activeCategory)): ?>
                     <input type="hidden" name="category" value="<?= (int) $activeCategory ?>">
                     <?php endif; ?>

                    <div class="mb-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 text-slate-400">Price Range</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="0-500" class="text-primary focus:ring-primary border-slate-300"
                                       onchange="setPrice(0, 500); this.form.submit()"
                                       <?= ((int)($filters['price_max'] ?? 0) === 500) ? 'checked' : '' ?>>
                                <span class="text-sm text-slate-600 group-hover:text-primary transition-colors">Under ₹500</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="500-1000" class="text-primary focus:ring-primary border-slate-300"
                                       onchange="setPrice(500, 1000); this.form.submit()"
                                       <?= ((int)($filters['price_min'] ?? 0) === 500 && (int)($filters['price_max'] ?? 0) === 1000) ? 'checked' : '' ?>>
                                <span class="text-sm text-slate-600 group-hover:text-primary transition-colors">₹500 - ₹1000</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="1000-2000" class="text-primary focus:ring-primary border-slate-300"
                                       onchange="setPrice(1000, 2000); this.form.submit()"
                                       <?= ((int)($filters['price_min'] ?? 0) === 1000 && (int)($filters['price_max'] ?? 0) === 2000) ? 'checked' : '' ?>>
                                <span class="text-sm text-slate-600 group-hover:text-primary transition-colors">₹1000 - ₹2000</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="2000+" class="text-primary focus:ring-primary border-slate-300"
                                       onchange="setPrice(2000, ''); this.form.submit()"
                                       <?= ((int)($filters['price_min'] ?? 0) === 2000 && empty($filters['price_max'])) ? 'checked' : '' ?>>
                                <span class="text-sm text-slate-600 group-hover:text-primary transition-colors">Over ₹2000</span>
                            </label>
                        </div>
                        
                        <input type="hidden" name="price_min" id="d_min" value="<?= htmlspecialchars((string) ($filters['price_min'] ?? '')) ?>">
                        <input type="hidden" name="price_max" id="d_max" value="<?= htmlspecialchars((string) ($filters['price_max'] ?? '')) ?>">
                    </div>
                </form>
            </div>
        </div>
    </aside>

    <section class="flex-1 min-w-0" id="product-grid">
            <div class="flex flex-row items-center justify-between gap-4 mb-6 hidden md:flex">
                <div>
                    <h1 class="text-3xl font-bold mb-1 text-slate-900">Shop All Flowers</h1>
                    <p class="text-slate-500 text-sm">Found <?= (int) $total_flowers ?> bouquets · Flowers first, décor last</p>
                </div>
                <form action="/flowers" method="GET">
                    <?php if (!empty($activeCategory)): ?><input type="hidden" name="category" value="<?= (int) $activeCategory ?>"><?php endif; ?>
                    <?php if (!empty($filters['price_min'])): ?><input type="hidden" name="price_min" value="<?= (int) $filters['price_min'] ?>"><?php endif; ?>
                    <?php if (!empty($filters['price_max'])): ?><input type="hidden" name="price_max" value="<?= (int) $filters['price_max'] ?>"><?php endif; ?>
                    <select name="sort" onchange="this.form.submit()" class="bg-white border-slate-200 rounded-lg text-sm font-semibold focus:ring-primary py-2 pl-3 pr-8 shadow-sm cursor-pointer">
                        <option value="bestseller" <?= $sort === 'bestseller' ? 'selected' : '' ?>>Best Selling</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High</option>
                    </select>
                </form>
            </div>

            <?php if ($total_flowers > 0): ?>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6">
                <?php foreach ($products as $f): ?>
                    <div class="group bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-primary/20 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                        <?php 
                            $p_link = product_url(['type' => 'flower', 'slug' => $f['slug'] ?? '', 'id' => $f['id']]); 
                            $finalImagePath = get_image_url($f['image'], 'flowers');
                            $isDecor = shop_is_decoration_product($f);
                        ?>
                        <div class="relative overflow-hidden aspect-[4/5] bg-slate-100 group-hover:opacity-90 transition-opacity">
                            <button onclick="toggleWishlist(this, <?= (int) $f['id'] ?>, 'flower')" class="absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 shadow-sm transition-colors z-10 group/wishlist">
                                <span class="material-icons-outlined text-lg">favorite_border</span>
                            </button>
                            
                            <a href="<?= htmlspecialchars($p_link) ?>">
                                <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                                     src="<?= htmlspecialchars($finalImagePath) ?>" width="400" height="500" loading="lazy" decoding="async"
                                     alt="<?= htmlspecialchars($f['image_alt'] ?? $f['name']) ?>">
                                <?php if ($isDecor): ?>
                                    <div class="absolute top-3 left-3 bg-slate-800/90 backdrop-blur text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">Decor</div>
                                <?php elseif (isset($f['in_stock']) && !$f['in_stock']): ?>
                                    <div class="absolute top-3 left-3 bg-red-500/90 backdrop-blur text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">Out of Stock</div>
                                <?php endif; ?>
                            </a>
                            <?php if (!isset($f['in_stock']) || $f['in_stock']): ?>
                            <form action="/cart" method="POST" class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity translate-y-2 group-hover:translate-y-0 duration-300">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="product_id" value="<?= (int) $f['id'] ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($f['name']) ?>">
                                <input type="hidden" name="price" value="<?= apply_surge_pricing($f['price'], 'flower') ?>">
                                <input type="hidden" name="image" value="<?= htmlspecialchars($f['image']) ?>">
                                <input type="hidden" name="category" value="flower">
                                <input type="hidden" name="add_to_cart" value="1">
                                <button type="submit" class="w-10 h-10 bg-white hover:bg-primary hover:text-white text-slate-900 rounded-full shadow-lg flex items-center justify-center transition-colors">
                                    <span class="material-icons-outlined text-lg">add_shopping_cart</span>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-3 md:p-5 flex flex-col flex-grow">
                            <h3 class="font-bold text-sm md:text-base mb-1 text-slate-900 group-hover:text-primary transition-colors line-clamp-2 h-10 md:h-12">
                                <a href="<?= htmlspecialchars($p_link) ?>"><?= htmlspecialchars($f['name']) ?></a>
                            </h3>

                            <div class="flex items-center gap-1 text-yellow-400 text-xs mb-2">
                                <i class="fas fa-star"></i>
                                <span class="text-slate-500 font-bold ml-1"><?= number_format((float) ($f['rating'] ?? 5.0), 1) ?></span>
                            </div>
                            
                            <div class="flex flex-col gap-1 mb-2 text-[10px] font-medium text-slate-500">
                                <?php if (!isset($f['delivery_sameday']) || $f['delivery_sameday'] == 1): ?>
                                    <div class="flex items-center gap-1 text-green-600"><span class="material-icons-outlined text-[12px]">local_shipping</span> Same Day Delivery</div>
                                <?php elseif (isset($f['delivery_nextday']) && $f['delivery_nextday'] == 1): ?>
                                    <div class="flex items-center gap-1 text-blue-600"><span class="material-icons-outlined text-[12px]">event_available</span> Next Day Delivery</div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-wrap items-center gap-1 mb-2 mt-auto min-w-0">
                            <?php if (isset($f['original_price']) && $f['original_price'] > $f['price']): 
                                    $surgedP = apply_surge_pricing($f['price'], 'flower');
                                    $disc = round((($f['original_price'] - $surgedP) / $f['original_price']) * 100);
                            ?>
                                <p class="font-bold text-slate-400 text-xs line-through shrink-0">₹<?= number_format($f['original_price']) ?></p>
                                <p class="font-bold text-primary text-lg shrink-0">₹<?= number_format($surgedP) ?></p>
                                <span class="bg-red-50 text-red-500 text-[10px] font-bold px-1.5 py-0.5 rounded border border-red-100 shrink-0"><?= $disc ?>% OFF</span>
                            <?php else: ?>
                                <p class="font-bold text-primary text-lg">₹<?= number_format(apply_surge_pricing($f['price'], 'flower')) ?></p>
                            <?php endif; ?>
                            </div>
                            
                            <div class="mt-auto md:hidden">
                                <?php if (!isset($f['in_stock']) || $f['in_stock']): ?>
                                    <form action="/cart" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="product_id" value="<?= (int) $f['id'] ?>">
                                        <input type="hidden" name="name" value="<?= htmlspecialchars($f['name']) ?>">
                                        <input type="hidden" name="price" value="<?= apply_surge_pricing($f['price'], 'flower') ?>">
                                        <input type="hidden" name="image" value="<?= htmlspecialchars($f['image']) ?>">
                                        <input type="hidden" name="category" value="flower">
                                        <input type="hidden" name="add_to_cart" value="1">
                                        <button type="submit" class="w-full bg-[#d4af37] text-white font-bold text-xs py-2.5 rounded-[8px] shadow-md hover:bg-[#c5a028] hover:shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                                            Buy Now
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button disabled class="w-full bg-slate-100 text-slate-400 font-bold text-xs py-3 rounded-xl cursor-not-allowed">Sold Out</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                    <span class="material-icons-outlined text-4xl text-slate-300 mb-4">search_off</span>
                    <h3 class="text-xl font-bold text-slate-800">No flowers found</h3>
                    <p class="text-slate-500 mt-2">Try adjusting your filters.</p>
                    <a href="/flowers" class="inline-block mt-6 px-6 py-2 bg-primary text-white rounded-full font-bold text-sm">Clear Filters</a>
                </div>
            <?php endif; ?>
        </section>
</main>

    <?php if ($faqs && mysqli_num_rows($faqs) > 0): ?>
    <section class="mt-8 md:mt-20 border-t border-slate-100 pt-10 md:pt-16 pb-10 container mx-auto">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-2xl font-bold text-center mb-10 text-slate-900">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <?php while ($f_item = mysqli_fetch_assoc($faqs)): ?>
                <div class="faq-item-box bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-primary/30 transition-colors">
                    <button onclick="this.parentElement.classList.toggle('active')" class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-800 bg-transparent cursor-pointer">
                        <span><?= htmlspecialchars($f_item['question']) ?></span>
                        <span class="material-icons-outlined text-slate-400 transition-transform">expand_more</span>
                    </button>
                    <div class="faq-answer max-h-0 overflow-hidden px-5 text-sm text-slate-600 transition-all duration-300 bg-slate-50/50">
                        <div class="pb-5 pt-2 border-t border-slate-100"><?= nl2br(htmlspecialchars($f_item['answer'])) ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<div id="filterOverlay" onclick="toggleMobileFilter()" class="fixed inset-0 bg-black/50 z-40 hidden backdrop-blur-sm transition-opacity"></div>
<aside id="mobileFilter" class="fixed top-0 left-0 h-full w-[85%] max-w-xs bg-white z-50 transform -translate-x-full shadow-2xl p-6 flex flex-col">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl font-bold text-slate-900">Filter Flowers</h2>
        <button onclick="toggleMobileFilter()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-red-50 hover:text-red-500 transition-colors">
            <span class="material-icons-outlined text-lg">close</span>
        </button>
    </div>
    <form action="/flowers" method="GET" class="flex-1 flex flex-col">
        <?php if (!empty($activeCategory)): ?><input type="hidden" name="category" value="<?= (int) $activeCategory ?>"><?php endif; ?>
        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 text-slate-400">Sort By</h3>
        <div class="space-y-3 mb-8">
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="sort" value="bestseller" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" <?= $sort === 'bestseller' ? 'checked' : '' ?> onchange="this.form.submit()">
                <span class="font-bold text-slate-700">Best Selling</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="sort" value="newest" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" <?= $sort === 'newest' ? 'checked' : '' ?> onchange="this.form.submit()">
                <span class="font-bold text-slate-700">Newest First</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="sort" value="price_low" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" <?= $sort === 'price_low' ? 'checked' : '' ?> onchange="this.form.submit()">
                <span class="font-bold text-slate-700">Price: Low to High</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="sort" value="price_high" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" <?= $sort === 'price_high' ? 'checked' : '' ?> onchange="this.form.submit()">
                <span class="font-bold text-slate-700">Price: High to Low</span>
            </label>
        </div>

        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 text-slate-400">Price Range</h3>
        <div class="space-y-4 mb-8">
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="m_price" value="0-500" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" onchange="setMobilePrice(0, 500)"
                       <?= ((int)($filters['price_max'] ?? 0) === 500) ? 'checked' : '' ?>>
                <span class="font-bold text-slate-700">Under ₹500</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="m_price" value="500-1000" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" onchange="setMobilePrice(500, 1000)"
                       <?= ((int)($filters['price_min'] ?? 0) === 500 && (int)($filters['price_max'] ?? 0) === 1000) ? 'checked' : '' ?>>
                <span class="font-bold text-slate-700">₹500 - ₹1000</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="m_price" value="1000-2000" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" onchange="setMobilePrice(1000, 2000)"
                       <?= ((int)($filters['price_min'] ?? 0) === 1000) ? 'checked' : '' ?>>
                <span class="font-bold text-slate-700">₹1000 - ₹2000</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary">
                <input type="radio" name="m_price" value="2000+" class="text-primary focus:ring-primary border-slate-300 w-5 h-5" onchange="setMobilePrice(2000, '')"
                       <?= ((int)($filters['price_min'] ?? 0) === 2000) ? 'checked' : '' ?>>
                <span class="font-bold text-slate-700">Over ₹2000</span>
            </label>
        </div>

        <input type="hidden" name="price_min" id="m_min" value="<?= htmlspecialchars((string) ($filters['price_min'] ?? '')) ?>">
        <input type="hidden" name="price_max" id="m_max" value="<?= htmlspecialchars((string) ($filters['price_max'] ?? '')) ?>">

        <div class="mt-auto space-y-3">
             <button type="submit" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/30 active:scale-95 transition-transform">
                Apply Filters
            </button>
            <a href="/flowers" class="block w-full text-center text-slate-500 font-bold py-3 hover:text-red-500">Reset All</a>
        </div>
    </form>
</aside>

<script>
    function toggleMobileFilter() {
        const drawer = document.getElementById('mobileFilter');
        const overlay = document.getElementById('filterOverlay');
        
        if (drawer.classList.contains('active')) {
            drawer.classList.remove('active', 'translate-x-0');
            drawer.classList.add('-translate-x-full'); 
            overlay.classList.add('hidden');
        } else {
            drawer.classList.add('active', 'translate-x-0');
            drawer.classList.remove('-translate-x-full'); 
            overlay.classList.remove('hidden');
        }
    }

    function setPrice(min, max) {
        document.getElementById('d_min').value = min;
        document.getElementById('d_max').value = max;
    }

    function setMobilePrice(min, max) {
        document.getElementById('m_min').value = min;
        document.getElementById('m_max').value = max;
    }

    function toggleWishlist(btn, productId, type) {
        fetch('/actions/toggle_wishlist.php', {
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
                    btn.classList.remove('text-gray-400');
                } else {
                    icon.textContent = 'favorite_border';
                    btn.classList.remove('text-red-500');
                    btn.classList.add('text-gray-400');
                }
            } else if(data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }

    document.querySelectorAll('.faq-item-box button').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.querySelector('.material-icons-outlined').classList.toggle('rotate-180');
        });
    });
</script>

</body>
</html>
