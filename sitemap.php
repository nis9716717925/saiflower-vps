<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/sitemap_helper.php';

// 1. THEME SETTINGS
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);
$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#ffffff';
$tCol = $settings['theme_text_color'] ?? '#333333';
$fFam = $settings['theme_font'] ?? "'Plus Jakarta Sans', sans-serif";

if (($settings['maintenance_mode'] ?? 0) == 1) { header("Location: maintenance.php"); exit; }

$customPages = get_sitemap_custom_page_entries($conn);
$customGroups = sitemap_group_custom_pages($customPages);
$blogPages = get_sitemap_blog_entries($conn);
$eventPages = get_sitemap_event_entries($conn);
$galleryPages = get_sitemap_gallery_entries($conn);
$flowerProducts = get_sitemap_product_entries($conn, 'flower', 'flowers');
$cakeProducts = get_sitemap_product_entries($conn, 'cake', 'cakes');
$giftProducts = get_sitemap_product_entries($conn, 'gift', 'gifts');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/partials/tailwind_config.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap | Sai Flowers</title>
    <meta name="description" content="Explore the full structure of Sai Flowers website. Find all our pages, products, and categories easily.">
    <?php set_page_canonical_url(sitemap_base_url() . '/sitemap'); echo render_canonical_link(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Sitemap', 'item' => '/sitemap']]);
    ?>

    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <style>
        :root {
            --primary: <?= $pCol ?>;
            --accent: <?= $sCol ?>;
            --bg-site: <?= $bgColor ?>;
            --text-main: <?= $tCol ?>;
            --font-main: <?= $fFam ?>;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-site); color: var(--text-main); margin: 0; }
        h1, h2, h3 { font-family: var(--font-main); }
        .sitemap-section { margin-bottom: 40px; }
        .sitemap-link {
            display: block;
            padding: 8px 0;
            color: #555;
            text-decoration: none;
            transition: 0.3s;
            border-bottom: 1px dashed #eee;
        }
        .sitemap-link:hover { color: var(--primary); padding-left: 5px; }
        .section-title {
            color: var(--primary);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .section-count {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            margin-left: 0.5rem;
        }
        .custom-scroll {
            max-height: 420px;
            overflow-y: auto;
            padding-right: 4px;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="bg-slate-50 py-12">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold text-slate-800 mb-4">Sitemap</h1>
        <p class="text-slate-500">Overview of all pages on our website — updated automatically from the database.</p>
        <p class="text-slate-400 text-sm mt-2">
            XML sitemap for search engines:
            <a href="/sitemap.xml" class="text-primary hover:underline">/sitemap.xml</a>
        </p>
    </div>
</div>

<div class="container mx-auto px-6 py-12">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-12">

        <div class="sitemap-section">
            <h2 class="section-title"><i class="fas fa-home mr-2"></i> Main Pages</h2>
            <?php sitemap_render_link_list(array_map(static function ($p) {
                return ['title' => $p['title'], 'path' => $p['path']];
            }, array_filter(get_sitemap_static_entries(), static function ($p) {
                return !in_array($p['path'], ['/privacy', '/terms', '/refund-policy', '/delivery-policy', '/grievnce', '/legal', '/sitemap'], true);
            }))); ?>
        </div>

        <div class="sitemap-section">
            <h2 class="section-title"><i class="fas fa-gavel mr-2"></i> Legal</h2>
            <?php sitemap_render_link_list(array_map(static function ($p) {
                return ['title' => $p['title'], 'path' => $p['path']];
            }, array_filter(get_sitemap_static_entries(), static function ($p) {
                return in_array($p['path'], ['/privacy', '/terms', '/refund-policy', '/delivery-policy', '/grievnce', '/legal'], true);
            }))); ?>
        </div>

        <div class="sitemap-section">
            <h2 class="section-title">
                <i class="fas fa-map-marker-alt mr-2"></i> Flower Delivery by Area
                <span class="section-count">(<?= count($customGroups['location']) ?>)</span>
            </h2>
            <div class="custom-scroll">
                <?php sitemap_render_link_list($customGroups['location']); ?>
            </div>
        </div>

        <div class="sitemap-section">
            <h2 class="section-title">
                <i class="fas fa-gift mr-2"></i> Occasions &amp; Seasonal
                <span class="section-count">(<?= count($customGroups['occasion']) ?>)</span>
            </h2>
            <div class="custom-scroll">
                <?php sitemap_render_link_list($customGroups['occasion']); ?>
            </div>
        </div>

        <div class="sitemap-section">
            <h2 class="section-title">
                <i class="fas fa-file-alt mr-2"></i> Guides &amp; Landing Pages
                <span class="section-count">(<?= count($customGroups['landing']) ?>)</span>
            </h2>
            <div class="custom-scroll">
                <?php sitemap_render_link_list($customGroups['landing']); ?>
            </div>
        </div>

        <div class="sitemap-section">
            <h2 class="section-title">
                <i class="fas fa-pen-nib mr-2"></i> Blog
                <span class="section-count">(<?= count($blogPages) ?>)</span>
            </h2>
            <div class="custom-scroll">
                <?php sitemap_render_link_list($blogPages); ?>
            </div>
        </div>

        <div class="sitemap-section">
            <h2 class="section-title">
                <i class="fas fa-calendar-check mr-2"></i> Events
                <span class="section-count">(<?= count($eventPages) ?>)</span>
            </h2>
            <div class="custom-scroll">
                <?php sitemap_render_link_list($eventPages); ?>
            </div>
        </div>

        <div class="sitemap-section">
            <h2 class="section-title">
                <i class="fas fa-images mr-2"></i> Gallery
                <span class="section-count">(<?= count($galleryPages) ?>)</span>
            </h2>
            <div class="custom-scroll">
                <?php sitemap_render_link_list($galleryPages); ?>
            </div>
        </div>

        <div class="sitemap-section md:col-span-2 lg:col-span-3">
            <h2 class="section-title"><i class="fas fa-leaf mr-2"></i> Our Collections</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-bold text-slate-700 mb-4 uppercase text-sm tracking-wider">
                        Fresh Flowers <span class="section-count">(<?= count($flowerProducts) ?>)</span>
                    </h3>
                    <div class="custom-scroll">
                        <?php sitemap_render_link_list($flowerProducts); ?>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-slate-700 mb-4 uppercase text-sm tracking-wider">
                        Cakes <span class="section-count">(<?= count($cakeProducts) ?>)</span>
                    </h3>
                    <div class="custom-scroll">
                        <?php sitemap_render_link_list($cakeProducts); ?>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-slate-700 mb-4 uppercase text-sm tracking-wider">
                        Gifts <span class="section-count">(<?= count($giftProducts) ?>)</span>
                    </h3>
                    <div class="custom-scroll">
                        <?php sitemap_render_link_list($giftProducts); ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
