<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/category_recommend.php';

$settingsQuery = mysqli_query($conn, 'SELECT * FROM settings WHERE id=1');
$settings = mysqli_fetch_assoc($settingsQuery) ?: [];
$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';

$where = 'WHERE status = 1';
if (isset($_GET['price_min']) && $_GET['price_min'] !== '') {
    $where .= ' AND price >= ' . (int) $_GET['price_min'];
}
if (isset($_GET['price_max']) && $_GET['price_max'] !== '') {
    $where .= ' AND price <= ' . (int) $_GET['price_max'];
}
$sort = $_GET['sort'] ?? 'new';
$orderBy = match ($sort) {
    'price_low' => 'ORDER BY price ASC',
    'price_high' => 'ORDER BY price DESC',
    'name' => 'ORDER BY name ASC',
    default => 'ORDER BY id DESC',
};

$products = [];
$res = @mysqli_query($conn, "SELECT * FROM cakes $where $orderBy");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $products[] = $row;
    }
}
$stock = category_stock_summary($products, 'cakes');
$recommendProducts = category_fetch_recommend_bouquets($conn, 12, ['birthday', 'celebration', 'rose']);
$hero = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1600&q=80';
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/includes/seo_helper.php'; ?>
    <?= render_seo('cakes.php'); ?>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/category-page.css?v=1">
    <style>:root { --cat-primary: <?= htmlspecialchars($pCol) ?>; --cat-accent: <?= htmlspecialchars($sCol) ?>; }</style>
</head>
<body class="cat-page">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="cat-hero" style="--cat-hero-image: url('<?= htmlspecialchars($hero) ?>')">
    <div class="cat-wrap cat-hero__inner">
        <nav class="cat-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page">Cakes</li></ol></nav>
        <p class="cat-badge">Cakes</p>
        <h1>Celebration Cakes</h1>
        <p>Birthday, anniversary and party cakes — when stock is limited, our flower bouquets are always ready for same-day gifting.</p>
    </div>
</header>

<main>
    <div class="cat-wrap" style="padding-top:1.25rem">
        <?php if ($stock['status'] !== 'in_stock'): ?>
        <div class="cat-status cat-status--<?= htmlspecialchars($stock['status']) ?>" role="status">
            <span class="cat-status__pill"><?= htmlspecialchars($stock['label']) ?></span>
            <p class="cat-status__msg"><?= htmlspecialchars($stock['message']) ?></p>
            <div class="cat-status__actions">
                <a class="cat-btn cat-btn--accent" href="#cat-rec-title">See bouquet recommendations</a>
                <a class="cat-btn cat-btn--primary" href="/flowers">Shop flowers</a>
            </div>
        </div>
        <?php endif; ?>

        <section class="cat-section" aria-labelledby="cakes-grid-title">
            <div class="cat-section__head" style="display:flex;flex-wrap:wrap;align-items:end;justify-content:space-between;gap:0.75rem">
                <div>
                    <h2 id="cakes-grid-title"><?= $stock['total'] > 0 ? 'Cake collection' : 'Cake menu' ?></h2>
                    <p><?= (int) $stock['in_stock'] ?> available<?= $stock['total'] > $stock['in_stock'] ? ' · ' . (int) ($stock['total'] - $stock['in_stock']) . ' out of stock' : '' ?></p>
                </div>
                <form method="get" action="/cakes">
                    <select name="sort" onchange="this.form.submit()" class="cat-chip" style="cursor:pointer;appearance:auto">
                        <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High</option>
                    </select>
                </form>
            </div>

            <?php if ($stock['total'] === 0): ?>
            <div class="cat-empty-panel">
                <h3 class="cat-serif">Available Soon</h3>
                <p>We’re preparing a fresh cake menu. Meanwhile, surprise them with a handcrafted bouquet.</p>
                <a class="cat-btn cat-btn--primary" href="#cat-rec-title">View recommendations</a>
            </div>
            <?php else: ?>
            <div class="cat-grid" role="list">
                <?php foreach ($products as $c):
                    $inStock = category_product_is_in_stock($c);
                    $link = product_url(['type' => 'cake', 'slug' => $c['slug'] ?? '', 'id' => (int) $c['id']]);
                    $img = get_image_url($c['image'] ?? '', 'cakes');
                    $price = function_exists('apply_surge_pricing') ? apply_surge_pricing($c['price'], 'cake') : (float) $c['price'];
                ?>
                <a class="cat-card" href="<?= htmlspecialchars($link) ?>" role="listitem">
                    <span class="cat-card__media">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($c['name'] ?? 'Cake') ?>" width="320" height="320" loading="lazy" decoding="async"
                             onerror="this.src='https://placehold.co/400x400?text=Cake'">
                        <?php if (!$inStock): ?><span class="cat-card__stock">Out of Stock</span><?php endif; ?>
                    </span>
                    <span class="cat-card__body">
                        <span class="cat-card__name"><?= htmlspecialchars($c['name'] ?? 'Cake') ?></span>
                        <span class="cat-card__price">₹<?= number_format((float) $price) ?></span>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>

    <?php
    $pageKey = 'cakes';
    include __DIR__ . '/partials/category/bouquet_recommendations.php';
    ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
