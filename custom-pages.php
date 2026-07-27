<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/sitemap_helper.php';

$settingsQuery = mysqli_query($conn, 'SELECT * FROM settings WHERE id=1');
$settings = mysqli_fetch_assoc($settingsQuery);
$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#ffffff';
$tCol = $settings['theme_text_color'] ?? '#333333';
$fFam = $settings['theme_font'] ?? "'Plus Jakarta Sans', sans-serif";

if (($settings['maintenance_mode'] ?? 0) == 1) {
    header('Location: maintenance.php');
    exit;
}

$customPages = get_sitemap_custom_page_entries($conn);
$customGroups = sitemap_group_custom_pages($customPages);
$totalPages = count($customPages);

$groupMeta = [
    'location' => [
        'title' => 'Flower Delivery by Area',
        'icon'  => 'fa-map-marker-alt',
        'desc'  => 'Local flower delivery pages across Delhi NCR neighbourhoods.',
    ],
    'occasion' => [
        'title' => 'Occasions & Seasonal',
        'icon'  => 'fa-gift',
        'desc'  => 'Birthday, anniversary, festival, and seasonal flower pages.',
    ],
    'landing' => [
        'title' => 'Guides & Landing Pages',
        'icon'  => 'fa-file-alt',
        'desc'  => 'Service, florist, bouquet, and keyword landing pages.',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Pages | Sai Flower</title>
    <meta name="description" content="Browse all Sai Flower custom pages — flower delivery, bouquets, occasions, and local florist pages across Delhi NCR. Updated automatically.">
    <?php
    set_page_canonical_url(sitemap_base_url() . '/custom-pages');
    echo render_canonical_link();
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Custom Pages', 'item' => '/custom-pages']]);
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <style>
        :root {
            --primary: <?= htmlspecialchars($pCol) ?>;
            --accent: <?= htmlspecialchars($sCol) ?>;
            --bg-site: <?= htmlspecialchars($bgColor) ?>;
            --text-main: <?= htmlspecialchars($tCol) ?>;
            --font-main: <?= $fFam ?>;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-site);
            color: var(--text-main);
            margin: 0;
        }
        h1, h2, h3 { font-family: var(--font-main); }
        .cp-link {
            display: block;
            padding: 10px 0;
            color: #555;
            text-decoration: none;
            transition: 0.25s;
            border-bottom: 1px dashed #eee;
            font-size: 0.95rem;
        }
        .cp-link:hover {
            color: var(--primary);
            padding-left: 6px;
        }
        .section-title {
            color: var(--primary);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 10px;
            margin-bottom: 12px;
            font-size: 1.35rem;
            font-weight: 700;
        }
        .section-count {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            margin-left: 0.4rem;
        }
        .cp-scroll {
            max-height: 480px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .cp-search {
            max-width: 520px;
            margin: 0 auto;
        }
        .cp-search input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            padding: 12px 18px 12px 44px;
            font-size: 0.95rem;
            outline: none;
            background: #fff;
        }
        .cp-search input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 18%, transparent);
        }
        .cp-search-wrap { position: relative; }
        .cp-search-wrap i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .cp-empty {
            display: none;
            text-align: center;
            color: #94a3b8;
            padding: 2rem 0;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="bg-slate-50 py-12">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold text-slate-800 mb-4">Custom Pages</h1>
        <p class="text-slate-500 max-w-2xl mx-auto">
            All Sai Flower custom pages in one place — flower delivery, bouquets, occasions, and local florist pages.
            New pages appear here automatically when published.
        </p>
        <p class="text-slate-400 text-sm mt-3">
            <span class="font-semibold text-slate-600"><?= (int) $totalPages ?></span> active page<?= $totalPages === 1 ? '' : 's' ?>
        </p>
        <div class="cp-search mt-8">
            <div class="cp-search-wrap">
                <i class="fas fa-search"></i>
                <input type="search" id="cpSearch" placeholder="Search custom pages..." autocomplete="off" aria-label="Search custom pages">
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-6 py-12">
    <?php if ($totalPages === 0): ?>
        <p class="text-center text-slate-400 py-16">No custom pages published yet.</p>
    <?php else: ?>
        <div id="cpGroups" class="grid md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php foreach ($groupMeta as $key => $meta): ?>
                <?php $items = $customGroups[$key] ?? []; ?>
                <div class="cp-group" data-group="<?= htmlspecialchars($key) ?>">
                    <h2 class="section-title">
                        <i class="fas <?= htmlspecialchars($meta['icon']) ?> mr-2"></i>
                        <?= htmlspecialchars($meta['title']) ?>
                        <span class="section-count">(<?= count($items) ?>)</span>
                    </h2>
                    <p class="text-slate-400 text-sm mb-4"><?= htmlspecialchars($meta['desc']) ?></p>
                    <div class="cp-scroll">
                        <?php if (count($items) === 0): ?>
                            <p class="text-slate-400 text-sm">No pages in this section yet.</p>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <a
                                    href="<?= htmlspecialchars($item['path']) ?>"
                                    class="cp-link"
                                    data-title="<?= htmlspecialchars(strtolower($item['title'])) ?>"
                                ><?= htmlspecialchars($item['title']) ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p id="cpEmpty" class="cp-empty">No custom pages match your search.</p>
    <?php endif; ?>

    <div class="mt-12 text-center text-sm text-slate-400">
        Looking for the full site map?
        <a href="/sitemap" class="text-primary hover:underline">View Full Sitemap</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
    var input = document.getElementById('cpSearch');
    if (!input) return;

    var empty = document.getElementById('cpEmpty');
    var groups = document.querySelectorAll('.cp-group');

    input.addEventListener('input', function () {
        var q = (input.value || '').trim().toLowerCase();
        var anyVisible = false;

        groups.forEach(function (group) {
            var links = group.querySelectorAll('.cp-link');
            var groupVisible = false;

            links.forEach(function (link) {
                var title = link.getAttribute('data-title') || '';
                var show = !q || title.indexOf(q) !== -1;
                link.style.display = show ? '' : 'none';
                if (show) groupVisible = true;
            });

            group.style.display = groupVisible || links.length === 0 ? '' : 'none';
            if (groupVisible) anyVisible = true;
        });

        if (empty) {
            empty.style.display = anyVisible || !q ? 'none' : 'block';
        }
    });
})();
</script>

</body>
</html>
