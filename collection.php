<?php
/**
 * Collection landing page — high-converting PLP for flower / relation / occasion / collection taxonomy.
 * Query: ?kind=flower|relation|occasion|collection&slug=...
 */
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/collection_landing.php';

$kind = collection_normalize_kind((string) ($_GET['kind'] ?? ''));
$slug = strtolower(trim((string) ($_GET['slug'] ?? ''), '/'));

$collection = ($kind !== '' && $slug !== '') ? collection_get($kind, $slug) : null;
if ($collection === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$settingsQuery = mysqli_query($conn, 'SELECT * FROM settings WHERE id=1');
$settings = mysqli_fetch_assoc($settingsQuery) ?: [];
if (($settings['maintenance_mode'] ?? 0) == 1) {
    header('Location: /maintenance.php');
    exit;
}

$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$wa_num = '8802004527';
$wa_link = 'https://wa.me/918802004527';

$meta = collection_build_meta($collection);
$products = collection_fetch_products($conn, $collection, 40, 36);
$groups = collection_split_product_groups($products);
$related = collection_related_entries($collection, 10);
$cross = collection_cross_kind_links($collection);
$faqs = $collection['faqs'] ?? collection_default_faqs($collection['title']);
$seoContent = collection_build_seo_content($collection);
$cities = collection_city_links();
$popular = collection_popular_searches();
$canonical = 'https://saiflower.com' . $collection['canonical_path'];
$heroImg = $collection['hero_image'] ?: 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=1600&q=80';
$kindLabel = match ($collection['kind']) {
    'flower' => 'Flower Type',
    'relation' => 'Gifts For',
    'occasion' => 'Occasion',
    default => 'Collection',
};
$breadParentHref = match ($collection['kind']) {
    'flower' => '/flowers',
    'relation' => '/collection/best-sellers',
    'occasion' => '/occasion/birthday',
    default => '/collection/best-sellers',
};
$breadParentLabel = match ($collection['kind']) {
    'flower' => 'Flowers',
    'relation' => 'Relations',
    'occasion' => 'Occasions',
    default => 'Collections',
};
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta['keywords']) ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_IN">
    <meta property="og:site_name" content="Sai Flowers">
    <meta property="og:title" content="<?= htmlspecialchars($meta['og_title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta['og_description']) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($heroImg) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($meta['og_title']) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta['og_description']) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($heroImg) ?>">
    <?= collection_json_ld($collection, $products, $faqs) ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/collection-landing.css?v=5">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --cl-primary: <?= htmlspecialchars($pCol) ?>;
            --cl-accent: <?= htmlspecialchars($sCol) ?>;
        }
    </style>
</head>
<body class="cl-page">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<!-- Hero -->
<header class="cl-hero" style="--cl-hero-image: url('<?= htmlspecialchars($heroImg) ?>')">
    <div class="cl-hero__overlay"></div>
    <div class="cl-container cl-hero__inner">
        <nav class="cl-breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li><a href="<?= htmlspecialchars($breadParentHref) ?>"><?= htmlspecialchars($breadParentLabel) ?></a></li>
                <li aria-current="page"><?= htmlspecialchars($collection['title']) ?></li>
            </ol>
        </nav>
        <p class="cl-hero__badge"><?= htmlspecialchars($collection['badge'] ?? $kindLabel) ?></p>
        <h1 class="cl-hero__title"><?= htmlspecialchars($collection['h1']) ?></h1>
        <p class="cl-hero__desc"><?= htmlspecialchars($collection['short_description']) ?></p>
        <div class="cl-hero__actions">
            <a class="cl-btn cl-btn--primary" href="#cl-products"><?= htmlspecialchars($collection['cta_label'] ?? 'Shop Now') ?></a>
            <a class="cl-btn cl-btn--ghost" href="<?= htmlspecialchars($wa_link) ?>" target="_blank" rel="noopener noreferrer">WhatsApp Order</a>
        </div>
        <p class="cl-hero__promise"><i class="fas fa-truck-fast" aria-hidden="true"></i> Same-day delivery across Delhi NCR · Order by 6 PM</p>
    </div>
</header>

<!-- Trust badges -->
<section class="cl-trust" aria-label="Trust badges">
    <div class="cl-container cl-trust__row">
        <div class="cl-trust__item"><i class="fas fa-leaf" aria-hidden="true"></i><span>Freshness Guaranteed</span></div>
        <div class="cl-trust__item"><i class="fas fa-bolt" aria-hidden="true"></i><span>Same-Day Delivery</span></div>
        <div class="cl-trust__item"><i class="fas fa-lock" aria-hidden="true"></i><span>Secure Checkout</span></div>
        <div class="cl-trust__item"><i class="fas fa-star" aria-hidden="true"></i><span>4.8★ Rated</span></div>
        <div class="cl-trust__item"><i class="fas fa-rotate-left" aria-hidden="true"></i><span>Easy Replacements</span></div>
    </div>
</section>

<main id="main-content">
    <!-- Product grid -->
    <div id="cl-products">
    <?php
    $as_grid = true;
    $slider_id = 'cl-main-grid';
    $slider_title = 'Shop ' . $collection['title'];
    $slider_sub = count($products) . ' fresh bouquets · décor services never shown here';
    $products = $groups['all'];
    include __DIR__ . '/partials/collection/product_rail.php';
    ?>
    </div>

    <?php if (count($groups['featured']) > 0): ?>
    <?php
    $as_grid = false;
    $slider_id = 'cl-featured';
    $slider_title = 'Featured ' . $collection['title'];
    $slider_sub = 'Handpicked highlights from this collection';
    $products = $groups['featured'];
    include __DIR__ . '/partials/collection/product_rail.php';
    ?>
    <?php endif; ?>

    <?php if (count($groups['bestsellers']) > 0): ?>
    <?php
    $as_grid = false;
    $slider_id = 'cl-bestsellers';
    $slider_title = 'Best Sellers';
    $slider_sub = 'Highest-rated picks customers reorder';
    $products = $groups['bestsellers'];
    include __DIR__ . '/partials/collection/product_rail.php';
    ?>
    <?php endif; ?>

    <?php if (count($groups['recent']) > 0): ?>
    <?php
    $as_grid = false;
    $slider_id = 'cl-recent';
    $slider_title = 'Recently Added';
    $slider_sub = 'Fresh arrivals in this category';
    $products = $groups['recent'];
    include __DIR__ . '/partials/collection/product_rail.php';
    ?>
    <?php endif; ?>

    <?php if (count($groups['sameday']) > 0): ?>
    <?php
    $as_grid = false;
    $slider_id = 'cl-sameday';
    $slider_title = 'Same Day Delivery Picks';
    $slider_sub = 'Need it today? These ship fast across Delhi NCR';
    $products = $groups['sameday'];
    include __DIR__ . '/partials/collection/product_rail.php';
    ?>
    <?php endif; ?>

    <!-- Similar / related collections -->
    <section class="cl-section" aria-labelledby="cl-related-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-related-title" class="cl-section__title">Related Collections</h2>
                <p class="cl-section__sub">Keep exploring — more ways to find the perfect gift</p>
            </div>
            <div class="cl-chip-grid">
                <?php foreach ($related as $rel): ?>
                <a class="cl-chip" href="<?= htmlspecialchars($rel['canonical_path']) ?>"><?= htmlspecialchars($rel['title']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cl-section cl-section--muted" aria-labelledby="cl-cats-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-cats-title" class="cl-section__title">Related Categories</h2>
            </div>
            <div class="cl-link-columns">
                <div>
                    <h3>Flower Types</h3>
                    <?php foreach ($cross['flowers'] as $item): ?>
                    <a href="<?= htmlspecialchars($item['canonical_path']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                    <?php endforeach; ?>
                </div>
                <div>
                    <h3>Occasions</h3>
                    <?php foreach ($cross['occasions'] as $item): ?>
                    <a href="<?= htmlspecialchars($item['canonical_path']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                    <?php endforeach; ?>
                </div>
                <div>
                    <h3>For Someone</h3>
                    <?php foreach ($cross['relations'] as $item): ?>
                    <a href="<?= htmlspecialchars($item['canonical_path']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                    <?php endforeach; ?>
                </div>
                <div>
                    <h3>Collections</h3>
                    <?php foreach ($cross['collections'] as $item): ?>
                    <a href="<?= htmlspecialchars($item['canonical_path']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Why choose us -->
    <section class="cl-section" aria-labelledby="cl-why-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-why-title" class="cl-section__title">Why Choose Sai Flowers</h2>
                <p class="cl-section__sub">Premium floristry with marketplace convenience</p>
            </div>
            <div class="cl-why-grid">
                <article class="cl-why-card"><i class="fas fa-spa" aria-hidden="true"></i><h3>Artisan Florists</h3><p>Every bouquet styled by hand in our Delhi studio since 1998.</p></article>
                <article class="cl-why-card"><i class="fas fa-truck" aria-hidden="true"></i><h3>Reliable Delivery</h3><p>Same-day slots across Delhi NCR with live order support.</p></article>
                <article class="cl-why-card"><i class="fas fa-shield-halved" aria-hidden="true"></i><h3>Secure Payments</h3><p>Encrypted checkout with UPI, cards and trusted wallets.</p></article>
                <article class="cl-why-card"><i class="fas fa-heart" aria-hidden="true"></i><h3>Loved by Thousands</h3><p>4.8★ average from customers who gift with us again.</p></article>
            </div>
        </div>
    </section>

    <!-- Reviews -->
    <section class="cl-section cl-section--muted" aria-labelledby="cl-reviews-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-reviews-title" class="cl-section__title">Customer Reviews</h2>
                <p class="cl-section__sub">Real words from people who sent love with Sai Flowers</p>
            </div>
            <div class="cl-reviews">
                <blockquote class="cl-review"><p>“The rose bouquet arrived looking even better than the photos. Packaging was gorgeous.”</p><footer>— Ananya, Delhi</footer></blockquote>
                <blockquote class="cl-review"><p>“Same-day birthday delivery to Noida saved me. Mum loved the mixed flowers.”</p><footer>— Rohan, Gurgaon</footer></blockquote>
                <blockquote class="cl-review"><p>“Premium quality without the drama. WhatsApp support was quick and kind.”</p><footer>— Meera, Noida</footer></blockquote>
            </div>
            <div class="cl-google-reviews">
                <p><i class="fab fa-google" aria-hidden="true"></i> <strong>Google Reviews</strong> — Rated 4.8 / 5 by happy customers across Delhi NCR.</p>
                <a class="cl-btn cl-btn--ghost" href="/review.php">Read reviews</a>
            </div>
        </div>
    </section>

    <!-- Delivery info -->
    <section class="cl-section" aria-labelledby="cl-delivery-title">
        <div class="cl-container cl-split">
            <div>
                <h2 id="cl-delivery-title" class="cl-section__title">Delivery Information</h2>
                <ul class="cl-bullets">
                    <li>Same-day delivery available across Delhi NCR (order by 6 PM)</li>
                    <li>Scheduled date &amp; time slots at checkout</li>
                    <li>Midnight delivery in select pin codes on special occasions</li>
                    <li>Carefully packed to protect fresh blooms in transit</li>
                </ul>
                <p><a href="/delivery-policy">Read full delivery policy →</a></p>
            </div>
            <div>
                <h2 class="cl-section__title">Return Policy</h2>
                <ul class="cl-bullets">
                    <li>Report damaged or incorrect orders promptly with photos</li>
                    <li>Eligible replacements arranged as per our refund policy</li>
                    <li>Perishable products cannot be returned after acceptance</li>
                </ul>
                <p><a href="/refund-policy">Read refund policy →</a></p>
            </div>
        </div>
    </section>

    <!-- City links -->
    <section class="cl-section cl-section--muted" aria-labelledby="cl-cities-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-cities-title" class="cl-section__title">Flower Delivery Near You</h2>
            </div>
            <div class="cl-chip-grid">
                <?php foreach ($cities as $city): ?>
                <a class="cl-chip" href="<?= htmlspecialchars($city['href']) ?>"><?= htmlspecialchars($city['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Popular searches -->
    <section class="cl-section" aria-labelledby="cl-popular-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-popular-title" class="cl-section__title">Popular Searches</h2>
            </div>
            <div class="cl-chip-grid">
                <?php foreach ($popular as $ps): ?>
                <a class="cl-chip cl-chip--outline" href="<?= htmlspecialchars($ps['href']) ?>"><?= htmlspecialchars($ps['label']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Payment + secure checkout -->
    <section class="cl-secure" aria-label="Secure checkout">
        <div class="cl-container cl-secure__inner">
            <p><i class="fas fa-shield-halved" aria-hidden="true"></i> Secure checkout · SSL encrypted</p>
            <div class="cl-pay-icons" aria-label="Payment methods">
                <span>UPI</span><span>Visa</span><span>Mastercard</span><span>RuPay</span><span>NetBanking</span>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="cl-section" id="faq" aria-labelledby="cl-faq-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-faq-title" class="cl-section__title"><?= htmlspecialchars($collection['title']) ?> — FAQs</h2>
            </div>
            <div class="cl-faq">
                <?php foreach ($faqs as $i => $faq): ?>
                <details class="cl-faq__item"<?= $i === 0 ? ' open' : '' ?>>
                    <summary><?= htmlspecialchars($faq['q'] ?? $faq['question'] ?? '') ?></summary>
                    <p><?= htmlspecialchars($faq['a'] ?? $faq['answer'] ?? '') ?></p>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SEO content -->
    <section class="cl-section cl-seo" aria-label="About this collection">
        <div class="cl-container cl-seo__content">
            <?= $seoContent ?>
        </div>
    </section>

    <!-- Recently viewed (client) -->
    <section class="cl-section" id="clRecentlyViewed" hidden aria-labelledby="cl-rv-title">
        <div class="cl-container">
            <div class="cl-section__head">
                <h2 id="cl-rv-title" class="cl-section__title">Recently Viewed</h2>
            </div>
            <div class="cl-chip-grid" id="clRecentlyViewedList"></div>
        </div>
    </section>

    <!-- Footer CTA -->
    <section class="cl-final-cta" aria-label="Shop this collection">
        <div class="cl-container cl-final-cta__inner">
            <h2>Ready to send <?= htmlspecialchars($collection['title']) ?>?</h2>
            <p>Same-day delivery · Fresh blooms · Secure checkout</p>
            <div class="cl-hero__actions">
                <a class="cl-btn cl-btn--primary" href="#cl-products">Browse products</a>
                <a class="cl-btn cl-btn--ghost" href="<?= htmlspecialchars($wa_link) ?>" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
            </div>
        </div>
    </section>
</main>

<!-- Floating WhatsApp — desktop only (mobile uses footer bottom nav) -->
<a class="cl-wa-float" href="<?= htmlspecialchars($wa_link) ?>?text=<?= rawurlencode('Hi Sai Flowers, I am browsing ' . $collection['title']) ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp Sai Flowers">
    <i class="fab fa-whatsapp" aria-hidden="true"></i>
</a>

<!-- Sticky mobile shop CTA (no WhatsApp — footer already has it) -->
<div class="cl-mobile-cta" role="region" aria-label="Shop this collection">
    <a href="#cl-products">Shop <?= htmlspecialchars($collection['title']) ?></a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="/assets/js/collection-landing.js?v=1" defer></script>
<script>
(() => {
  const key = 'sf_recent_collections';
  const entry = { title: <?= json_encode($collection['title']) ?>, href: <?= json_encode($collection['canonical_path']) ?> };
  try {
    const prev = JSON.parse(localStorage.getItem(key) || '[]').filter(x => x.href !== entry.href);
    prev.unshift(entry);
    localStorage.setItem(key, JSON.stringify(prev.slice(0, 8)));
    const list = document.getElementById('clRecentlyViewedList');
    const wrap = document.getElementById('clRecentlyViewed');
    if (list && prev.length) {
      list.innerHTML = prev.map(x => `<a class="cl-chip" href="${x.href}">${x.title}</a>`).join('');
      wrap.hidden = false;
    }
  } catch (e) {}
})();
</script>
</body>
</html>
