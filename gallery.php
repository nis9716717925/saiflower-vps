<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/category_recommend.php';

$settingsQuery = mysqli_query($conn, 'SELECT * FROM settings WHERE id=1');
$settings = mysqli_fetch_assoc($settingsQuery) ?: [];
if (($settings['maintenance_mode'] ?? 0) == 1) {
    header('Location: /maintenance.php');
    exit;
}
$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';

$galleryItems = [];
$uniqueTags = [];
$result = @mysqli_query($conn, 'SELECT id, title, tag, image FROM gallery WHERE status = 1 ORDER BY id DESC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $galleryItems[] = $row;
        $tag = trim((string) ($row['tag'] ?? ''));
        if ($tag !== '' && !in_array($tag, $uniqueTags, true)) {
            $uniqueTags[] = $tag;
        }
    }
}

$stock = category_stock_summary($galleryItems, 'gallery looks');
if ($stock['status'] === 'in_stock') {
    // Gallery isn't purchasable inventory — treat as visual lookbook; still recommend bouquets.
    $stock['message'] = '';
}
$recommendProducts = category_fetch_recommend_bouquets($conn, 12);
$hero = 'https://images.unsplash.com/photo-1487530811176-3780da8112fd?auto=format&fit=crop&w=1600&q=80';
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/includes/seo_helper.php'; ?>
    <?= render_seo('gallery.php'); ?>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/category-page.css?v=1">
    <style>:root { --cat-primary: <?= htmlspecialchars($pCol) ?>; --cat-accent: <?= htmlspecialchars($sCol) ?>; }</style>
</head>
<body class="cat-page">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="cat-hero" style="--cat-hero-image: url('<?= htmlspecialchars($hero) ?>')">
    <div class="cat-wrap cat-hero__inner">
        <nav class="cat-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page">Gallery</li></ol></nav>
        <p class="cat-badge">Lookbook</p>
        <h1>Floral Gallery</h1>
        <p>Real arrangements from Sai Flowers — get inspired, then order a matching bouquet for same-day delivery.</p>
    </div>
</header>

<main>
    <div class="cat-wrap" style="padding-top:1.25rem">
        <?php if (count($galleryItems) === 0): ?>
        <div class="cat-status cat-status--available_soon" role="status">
            <span class="cat-status__pill">Available Soon</span>
            <p class="cat-status__msg">New gallery photos are being curated. Explore ready-to-order bouquets below.</p>
            <div class="cat-status__actions">
                <a class="cat-btn cat-btn--accent" href="#cat-rec-title">See recommendations</a>
                <a class="cat-btn cat-btn--primary" href="/flowers">Shop flowers</a>
            </div>
        </div>
        <?php else: ?>
        <div class="cat-chips" id="galleryFilters" aria-label="Filter gallery">
            <button type="button" class="cat-chip is-active" data-filter="all">All</button>
            <?php foreach ($uniqueTags as $tag): ?>
            <button type="button" class="cat-chip" data-filter="<?= htmlspecialchars(strtolower($tag)) ?>"><?= htmlspecialchars($tag) ?></button>
            <?php endforeach; ?>
        </div>

        <section class="cat-section" aria-labelledby="gallery-title">
            <div class="cat-section__head">
                <h2 id="gallery-title">Inspiration board</h2>
                <p>Tap a look you love — then shop the bouquet recommendations.</p>
            </div>
            <div class="cat-grid" id="galleryGrid" role="list">
                <?php foreach ($galleryItems as $item):
                    $img = get_image_url($item['image'] ?? '', 'gallery');
                    $tag = strtolower(trim((string) ($item['tag'] ?? '')));
                    $href = '/gallery-detail?id=' . (int) $item['id'];
                ?>
                <a class="cat-card gallery-item" href="<?= htmlspecialchars($href) ?>" data-tag="<?= htmlspecialchars($tag) ?>" role="listitem">
                    <span class="cat-card__media">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['title'] ?? 'Gallery') ?>" width="320" height="320" loading="lazy" decoding="async">
                    </span>
                    <span class="cat-card__body">
                        <span class="cat-card__name"><?= htmlspecialchars($item['title'] ?? 'Floral look') ?></span>
                        <?php if (!empty($item['tag'])): ?>
                        <span style="font-size:0.72rem;color:#6a6258;font-weight:700"><?= htmlspecialchars($item['tag']) ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <?php
    $pageKey = 'gallery';
    include __DIR__ . '/partials/category/bouquet_recommendations.php';
    ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
document.getElementById('galleryFilters')?.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-filter]');
  if (!btn) return;
  const filter = btn.getAttribute('data-filter');
  document.querySelectorAll('#galleryFilters .cat-chip').forEach((el) => el.classList.toggle('is-active', el === btn));
  document.querySelectorAll('#galleryGrid .gallery-item').forEach((card) => {
    const tag = card.getAttribute('data-tag') || '';
    card.style.display = (filter === 'all' || tag === filter) ? '' : 'none';
  });
});
</script>
</body>
</html>
