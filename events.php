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

$eventItems = [];
$uniqueTags = [];
$events = @mysqli_query($conn, 'SELECT * FROM events WHERE status = 1 ORDER BY id DESC');
if ($events) {
    while ($row = mysqli_fetch_assoc($events)) {
        $eventItems[] = $row;
        $tag = trim((string) ($row['tag'] ?? ''));
        if ($tag !== '' && !in_array($tag, $uniqueTags, true)) {
            $uniqueTags[] = $tag;
        }
    }
}

$stock = category_stock_summary($eventItems, 'events');
$recommendProducts = category_fetch_recommend_bouquets($conn, 12, ['wedding', 'stage', 'premium']);
$hero = 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1600&q=80';
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/includes/seo_helper.php'; ?>
    <?= render_seo('events.php'); ?>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/category-page.css?v=1">
    <style>:root { --cat-primary: <?= htmlspecialchars($pCol) ?>; --cat-accent: <?= htmlspecialchars($sCol) ?>; }</style>
</head>
<body class="cat-page">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="cat-hero" style="--cat-hero-image: url('<?= htmlspecialchars($hero) ?>')">
    <div class="cat-wrap cat-hero__inner">
        <nav class="cat-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page">Events</li></ol></nav>
        <p class="cat-badge">Events & Decor</p>
        <h1>Events & Workshops</h1>
        <p>Weddings, stage décor and celebrations — book florists for big moments, or send a bouquet today for smaller ones.</p>
    </div>
</header>

<main>
    <div class="cat-wrap" style="padding-top:1.25rem">
        <?php if (count($eventItems) === 0): ?>
        <div class="cat-status cat-status--available_soon" role="status">
            <span class="cat-status__pill">Available Soon</span>
            <p class="cat-status__msg">Event packages are being updated. For smaller celebrations, these flower bouquets are ready now.</p>
            <div class="cat-status__actions">
                <a class="cat-btn cat-btn--accent" href="#cat-rec-title">See bouquet recommendations</a>
                <a class="cat-btn cat-btn--primary" href="https://wa.me/918802004527?text=Hi%2C%20I%27d%20like%20to%20enquire%20about%20event%20packages">Enquire on WhatsApp</a>
            </div>
        </div>
        <?php else: ?>
        <?php if ($stock['status'] === 'out_of_stock'): ?>
        <div class="cat-status cat-status--out_of_stock" role="status">
            <span class="cat-status__pill">Out of Stock</span>
            <p class="cat-status__msg"><?= htmlspecialchars($stock['message']) ?></p>
        </div>
        <?php endif; ?>

        <div class="cat-chips" id="eventFilters" aria-label="Filter events">
            <button type="button" class="cat-chip is-active" data-filter="all">All</button>
            <?php foreach ($uniqueTags as $tag): ?>
            <button type="button" class="cat-chip" data-filter="<?= htmlspecialchars(strtolower($tag)) ?>"><?= htmlspecialchars($tag) ?></button>
            <?php endforeach; ?>
        </div>

        <section class="cat-section" aria-labelledby="events-title">
            <div class="cat-section__head">
                <h2 id="events-title">Event packages</h2>
                <p>Browse services — then check bouquet recommendations for gifting.</p>
            </div>
            <div class="cat-grid" id="eventsGrid" role="list">
                <?php foreach ($eventItems as $ev):
                    $inStock = category_product_is_in_stock($ev);
                    $img = get_image_url($ev['image'] ?? '', 'events');
                    $tag = strtolower(trim((string) ($ev['tag'] ?? '')));
                    $link = product_url(['type' => 'event', 'slug' => $ev['slug'] ?? '', 'id' => (int) $ev['id']]);
                ?>
                <a class="cat-card event-item" href="<?= htmlspecialchars($link) ?>" data-tag="<?= htmlspecialchars($tag) ?>" role="listitem">
                    <span class="cat-card__media">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($ev['title'] ?? $ev['name'] ?? 'Event') ?>" width="320" height="320" loading="lazy" decoding="async">
                        <?php if (!$inStock): ?><span class="cat-card__stock">Out of Stock</span><?php endif; ?>
                        <?php if ($inStock && empty($ev['slug']) && empty($ev['price'])): ?>
                        <span class="cat-card__stock cat-card__stock--soon">Enquire</span>
                        <?php endif; ?>
                    </span>
                    <span class="cat-card__body">
                        <span class="cat-card__name"><?= htmlspecialchars($ev['title'] ?? $ev['name'] ?? 'Event') ?></span>
                        <?php if (!empty($ev['price'])): ?>
                        <span class="cat-card__price">From ₹<?= number_format((float) $ev['price']) ?></span>
                        <?php else: ?>
                        <span style="font-size:0.78rem;color:#6a6258;font-weight:700">Custom quote</span>
                        <?php endif; ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <?php
    $pageKey = 'events';
    include __DIR__ . '/partials/category/bouquet_recommendations.php';
    ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
document.getElementById('eventFilters')?.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-filter]');
  if (!btn) return;
  const filter = btn.getAttribute('data-filter');
  document.querySelectorAll('#eventFilters .cat-chip').forEach((el) => el.classList.toggle('is-active', el === btn));
  document.querySelectorAll('#eventsGrid .event-item').forEach((card) => {
    const tag = card.getAttribute('data-tag') || '';
    card.style.display = (filter === 'all' || tag === filter) ? '' : 'none';
  });
});
</script>
</body>
</html>
