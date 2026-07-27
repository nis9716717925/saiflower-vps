<?php
/**
 * Celebrations Calendar landing — full-year occasion guide with working gift links.
 */
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/celebrations_calendar_data.php';
require_once __DIR__ . '/includes/occasion_links.php';

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

$items = celebrations_calendar_get_items();
$months = celebrations_calendar_group_by_month($items);
$upcoming = celebrations_calendar_upcoming($items, 6);
$db = ($conn instanceof mysqli) ? $conn : null;

$canonical = 'https://saiflower.com/celebration-calendar';
$title = 'Celebrations Calendar 2026 | Flower Gifting Dates — Sai Flowers';
$description = 'Plan flower gifts for every celebration — Valentine’s, Mother’s Day, festivals, Raksha Bandhan & more. Shop same-day bouquets for Delhi NCR from Sai Flowers.';
$heroImg = '/celebrations/valentines-day.jpg';
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_IN">
    <meta property="og:site_name" content="Sai Flowers">
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:image" content="https://saiflower.com<?= htmlspecialchars($heroImg) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/celebration-calendar-page.css?v=1">
    <style>
        :root {
            --cc-primary: <?= htmlspecialchars($pCol) ?>;
            --cc-accent: <?= htmlspecialchars($sCol) ?>;
        }
    </style>
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'Celebrations Calendar',
        'description' => $description,
        'url' => $canonical,
        'isPartOf' => ['@type' => 'WebSite', 'name' => 'Sai Flowers', 'url' => 'https://saiflower.com/'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => array_map(static function ($item, $i) use ($db) {
                return [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $item['title'],
                    'url' => 'https://saiflower.com' . celebrations_calendar_href($item, $db),
                ];
            }, $items, array_keys($items)),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
</head>
<body class="cc-page">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="cc-hero" style="--cc-hero-image: url('<?= htmlspecialchars($heroImg) ?>')">
    <div class="cc-hero__shade"></div>
    <div class="cc-wrap cc-hero__inner">
        <nav class="cc-crumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li aria-current="page">Celebrations Calendar</li>
            </ol>
        </nav>
        <p class="cc-kicker">Sai Flowers · Delhi NCR</p>
        <h1 class="cc-hero__title">Celebrations Calendar</h1>
        <p class="cc-hero__lead">Never miss a moment — browse the full year of gifting days and open the matching flower collection in one tap.</p>
        <div class="cc-hero__actions">
            <a class="cc-btn cc-btn--accent" href="#cc-months">Browse by month</a>
            <a class="cc-btn cc-btn--ghost" href="/flowers">Shop all flowers</a>
            <a class="cc-btn cc-btn--ghost" href="/checkout">Checkout</a>
        </div>
    </div>
</header>

<?php if ($upcoming !== []): ?>
<section class="cc-upcoming" aria-labelledby="cc-up-title">
    <div class="cc-wrap">
        <div class="cc-head">
            <h2 id="cc-up-title">Coming up next</h2>
            <p>Shop early for the celebrations closest on the calendar.</p>
        </div>
        <div class="cc-upcoming__grid">
            <?php foreach ($upcoming as $item): ?>
            <a class="cc-card cc-card--feature" href="<?= htmlspecialchars(celebrations_calendar_href($item, $db)) ?>">
                <span class="cc-card__media">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?> flowers" width="400" height="480" loading="eager" decoding="async">
                </span>
                <span class="cc-card__body">
                    <span class="cc-card__date"><?= htmlspecialchars($item['date']) ?></span>
                    <span class="cc-card__title"><?= htmlspecialchars($item['title']) ?></span>
                    <span class="cc-card__cta">Shop gifts <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<main id="cc-months" class="cc-main">
    <div class="cc-wrap">
        <div class="cc-head">
            <h2>Full year at a glance</h2>
            <p><?= count($items) ?> celebration days with curated flower, gift &amp; relation landings.</p>
        </div>

        <div class="cc-month-nav" role="navigation" aria-label="Jump to month">
            <?php foreach ($months as $group): ?>
            <a href="#month-<?= htmlspecialchars(strtolower($group['month'])) ?>"><?= htmlspecialchars(substr($group['month'], 0, 3)) ?></a>
            <?php endforeach; ?>
        </div>

        <?php foreach ($months as $group): ?>
        <section class="cc-month" id="month-<?= htmlspecialchars(strtolower($group['month'])) ?>" aria-labelledby="h-<?= htmlspecialchars(strtolower($group['month'])) ?>">
            <h3 id="h-<?= htmlspecialchars(strtolower($group['month'])) ?>"><?= htmlspecialchars($group['month']) ?></h3>
            <div class="cc-grid">
                <?php foreach ($group['items'] as $item): ?>
                <a class="cc-card" href="<?= htmlspecialchars(celebrations_calendar_href($item, $db)) ?>">
                    <span class="cc-card__media">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?> celebration gifts" width="320" height="400" loading="lazy" decoding="async">
                    </span>
                    <span class="cc-card__body">
                        <span class="cc-card__date"><?= htmlspecialchars($item['date']) ?></span>
                        <span class="cc-card__title"><?= htmlspecialchars($item['title']) ?></span>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </div>
</main>

<section class="cc-help" aria-labelledby="cc-help-title">
    <div class="cc-wrap cc-help__inner">
        <div>
            <h2 id="cc-help-title">Need help picking?</h2>
            <p>Tell us the date and who you’re gifting — our florists will suggest the right bouquet for Delhi NCR same-day delivery.</p>
        </div>
        <div class="cc-help__actions">
            <a class="cc-btn cc-btn--accent" href="/flowers">Shop flowers</a>
            <a class="cc-btn cc-btn--dark" href="<?= htmlspecialchars($wa_link) ?>" target="_blank" rel="noopener noreferrer">WhatsApp florist</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
