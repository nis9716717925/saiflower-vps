<?php
/**
 * Personalised gifts landing — Available Soon + bouquet recommendations.
 * Query: ?slug= (empty = hub)
 */
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/personalized_taxonomy.php';
require_once __DIR__ . '/includes/category_recommend.php';

$slug = strtolower(trim((string) ($_GET['slug'] ?? ''), '/'));
$entry = personalized_get($slug);
if ($entry === null) {
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

$children = personalized_list(false);
$recommendProducts = category_fetch_recommend_bouquets(
    $conn,
    12,
    $entry['bouquet_keywords'] ?? null
);
$canonical = 'https://saiflower.com' . $entry['canonical_path'];
$hero = $entry['hero'] ?: 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?auto=format&fit=crop&w=1600&q=80';
$title = $entry['title'] . ' | Sai Flowers';
$description = $entry['short'];
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($entry['h1']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($hero) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/category-page.css?v=1">
    <style>:root { --cat-primary: <?= htmlspecialchars($pCol) ?>; --cat-accent: <?= htmlspecialchars($sCol) ?>; }</style>
</head>
<body class="cat-page">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="cat-hero" style="--cat-hero-image: url('<?= htmlspecialchars($hero) ?>')">
    <div class="cat-wrap cat-hero__inner">
        <nav class="cat-crumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <?php if ($slug !== ''): ?>
                <li><a href="/personalized">Personalised</a></li>
                <?php endif; ?>
                <li aria-current="page"><?= htmlspecialchars($entry['title']) ?></li>
            </ol>
        </nav>
        <p class="cat-badge"><?= htmlspecialchars($entry['badge'] ?? 'Personalised') ?></p>
        <h1><?= htmlspecialchars($entry['h1']) ?></h1>
        <p><?= htmlspecialchars($entry['short']) ?></p>
    </div>
</header>

<main>
    <div class="cat-wrap" style="padding-top:1.25rem">
        <div class="cat-status cat-status--<?= htmlspecialchars($entry['status'] ?? 'available_soon') ?>" role="status">
            <span class="cat-status__pill"><i class="fas fa-clock" aria-hidden="true"></i> <?= htmlspecialchars($entry['status_label'] ?? 'Available Soon') ?></span>
            <p class="cat-status__msg"><?= htmlspecialchars($entry['recommend_line'] ?? 'We’re preparing personalised gifts. Fresh bouquets are ready today.') ?></p>
            <div class="cat-status__actions">
                <a class="cat-btn cat-btn--accent" href="#cat-rec-title">See bouquet recommendations</a>
                <a class="cat-btn cat-btn--primary" href="/gifts">Shop gifts</a>
                <a class="cat-btn cat-btn--ghost" href="/flowers">Shop flowers</a>
            </div>
        </div>

        <?php if ($children !== []): ?>
        <div class="cat-chips" aria-label="Personalised categories">
            <a class="cat-chip<?= $slug === '' ? ' is-active' : '' ?>" href="/personalized">All Personalised</a>
            <?php foreach ($children as $child): ?>
            <a class="cat-chip<?= (($child['slug'] ?? '') === $slug) ? ' is-active' : '' ?>"
               href="<?= htmlspecialchars($child['canonical_path']) ?>"><?= htmlspecialchars($child['title']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php
    $pageKey = 'personalized';
    $recommendTitle = $entry['recommend_line'] ? 'Recommendation' : null;
    if (!empty($entry['recommend_line'])) {
        $recommendTitle = 'You may also check these bouquets';
        $recommendSub = $entry['recommend_line'];
    }
    include __DIR__ . '/partials/category/bouquet_recommendations.php';
    ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
