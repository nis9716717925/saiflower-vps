<?php
require_once __DIR__ . '/config.php';

// Force Cache Refresh
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Canonical URL consolidation: /occasions/{slug} → /{slug}
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
if (preg_match('#^/occasions/([^/]+)/?$#i', $requestPath, $occasionMatch)) {
    header('Location: /' . rawurlencode($occasionMatch[1]), true, 301);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM dynamic_pages WHERE slug = ? AND status = 1 LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

$pageData = null;
if ($result->num_rows > 0) {
    $pageData = $result->fetch_assoc();
} else {
    require_once __DIR__ . '/includes/landing_pages.php';
    $pageData = get_builtin_landing_page_by_slug($slug);
}

if ($pageData === null) {
    // Legacy root-level product slugs → 301 to /{type}/{slug}
    $productTypes = [
        ['table' => 'flowers', 'type' => 'flower'],
        ['table' => 'cakes', 'type' => 'cake'],
        ['table' => 'gifts', 'type' => 'gift'],
        ['table' => 'events', 'type' => 'event'],
    ];
    foreach ($productTypes as $pt) {
        $p_stmt = $conn->prepare("SELECT id FROM {$pt['table']} WHERE slug = ? LIMIT 1");
        $p_stmt->bind_param("s", $slug);
        $p_stmt->execute();
        if ($p_stmt->get_result()->num_rows > 0) {
            header('Location: ' . product_url_by_parts($pt['type'], $slug), true, 301);
            exit;
        }
    }

    require_once __DIR__ . '/includes/occasion_links.php';
    $occasionDestination = resolve_occasion_slug_destination($slug, $conn);
    if ($occasionDestination !== null && $occasionDestination !== '/' . $slug) {
        header('Location: ' . $occasionDestination, true, 302);
        exit;
    }

    // Smart Fallback 1: Strip stray query strings appended to slug by regex
    if (strpos($slug, '&') !== false) {
        $real_slug = explode('&', $slug)[0];
        header("Location: /$real_slug");
        exit;
    }

    // Smart Fallback 2: Handle cases where admin entered plain text instead of URL slug
    $slugified = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug), '-'));
    if ($slug !== $slugified && !empty($slugified)) {
        header("Location: /$slugified");
        exit;
    }

    header("HTTP/1.0 404 Not Found");
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

if (!function_exists('format_content')) {
    function format_content($content) {
        $content = $content ?? '';
        
        // Ensure any escaped HTML entities are converted back to real HTML safely
        $decoded = html_entity_decode($content, ENT_QUOTES | ENT_SUBSTITUTE);
        if (!empty($decoded)) {
            $content = $decoded;
        }
        
        if (strpos($content, '<p>') !== false || preg_match('/<h[1-6]/i', $content) || strpos($content, '<ul') !== false || strpos($content, '<img') !== false || strpos($content, '<span') !== false || strpos($content, '<div') !== false || strpos($content, '<ol') !== false || strpos($content, '<strong') !== false) {
            return $content; // Rich text from WYSIWYG
        }
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $content);
        return nl2br($content);
    }
}

$pageContent = format_content($pageData['content']);

require_once __DIR__ . '/includes/seo_helper.php';

// 1. FETCH THEME SETTINGS
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);

$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#ffffff';
$tCol = $settings['theme_text_color'] ?? '#333333';
$fFam = $settings['theme_font'] ?? "'Playfair Display', serif";

// Maintenance Logic
if (($settings['maintenance_mode'] ?? 0) == 1) {
    header("Location: maintenance.php"); exit;
}

$meta_title = !empty($pageData['meta_title']) ? $pageData['meta_title'] : $pageData['title'];
$meta_desc = !empty($pageData['meta_description']) ? $pageData['meta_description'] : '';
$meta_keys = !empty($pageData['meta_keywords']) ? limit_meta_keywords($pageData['meta_keywords']) : '';

// Thin landing pages: noindex until unique content is added (300+ words)
$isLocationPage = preg_match('/^flower-delivery-in-/i', $slug);
$isOccasionPage = preg_match('/fathers-day|mothers-day|valentine|propose-day|rose-day|chocolate-day|promise-day|hug-day|kiss-day|womens-day|rakhi|diwali|christmas|new-year|birthday|anniversary/i', $slug);
$contentWordCount = str_word_count(strip_tags($pageData['content'] ?? ''));
if (($isLocationPage || $isOccasionPage) && $contentWordCount < 300 && empty($pageData['robots'])) {
    $pageData['robots'] = 'noindex, follow';
}

if ($isLocationPage) {
    require_once __DIR__ . '/includes/location_landing.php';
    $pageData = location_landing_apply_defaults($pageData, $slug);
    $locationMeta = location_landing_by_slug($slug);
    $locationArea = $locationMeta['area'] ?? $pageData['title'];
    $locationRegion = $locationMeta['region'] ?? 'Delhi NCR';
    $nearbyLocationLinks = location_landing_nearby_links($slug, $conn);
    if ($locationMeta) {
        if (!str_contains($meta_title, 'Same Day')) {
            $meta_title = location_landing_meta_title($locationMeta);
        }
        if ($meta_desc === '' || strlen($meta_desc) < 80) {
            $meta_desc = location_landing_meta_description($locationMeta);
        }
    }
} else {
    $locationMeta = null;
    $locationArea = '';
    $locationRegion = '';
    $nearbyLocationLinks = [];
}
$page_h1 = !empty($pageData['h1']) ? $pageData['h1'] : $pageData['title'];
$occasion_label = !empty($pageData['occasion_label']) ? $pageData['occasion_label'] : $pageData['title'];
$hero_img = !empty($pageData['hero_image']) ? '/uploads/' . $pageData['hero_image'] : 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?q=80&w=1400';
$hero_img_alt = $pageData['hero_image_alt'] ?? $page_h1;
if (!empty($pageData['hero_image_web'])) {
    $hero_img_web = $pageData['hero_image_web'];
} elseif (!empty($pageData['hero_image'])) {
    $hero_img_web = (strpos($pageData['hero_image'], '/') === 0) ? $pageData['hero_image'] : '/uploads/' . $pageData['hero_image'];
} else {
    $hero_img_web = '';
}
$canonical_url = get_page_canonical_url($pageData);
$products_section_heading = $pageData['products_section_heading'] ?? ('Shop ' . $occasion_label . ' Flowers, Cakes & Gifts');
$products_section_id = preg_replace('/[^a-z0-9]+/', '-', strtolower($pageData['slug'] ?? 'occasion')) . '-gifts';
$faq_section_heading = $pageData['faq_section_heading'] ?? ('Frequently Asked Questions');

$layout_type = $pageData['layout_type'] ?? 'event_info';
$page_tag = $pageData['page_tag'] ?? '';
$extra_images = !empty($pageData['extra_images']) ? json_decode($pageData['extra_images'], true) : [];

// If Product Showcase, fetch tagged BOUQUETS only (never car / first-night décor)
$tag_results = [];
if ($layout_type === 'product_showcase' && !empty($page_tag)) {
    require_once __DIR__ . '/includes/landing_page_sliders.php';
    require_once __DIR__ . '/includes/collection_landing.php';

    $p1 = "%," . $page_tag . ",%";
    $p2 = $page_tag . ",%";
    $p3 = "%," . $page_tag;
    $p4 = $page_tag;
    $bouquetSqlExtra = ' AND (' . landing_bouquet_sql_name_filter() . ')';

    $stmt1 = $conn->prepare(
        "SELECT id, name, slug, image, price, original_price, rating, tag, 'flower' as type
         FROM flowers
         WHERE status = 1 AND (tag LIKE ? OR tag LIKE ? OR tag LIKE ? OR tag = ?){$bouquetSqlExtra}
         ORDER BY rating DESC, id DESC
         LIMIT 80"
    );
    if ($stmt1) {
        $stmt1->bind_param("ssss", $p1, $p2, $p3, $p4);
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        while ($row = $res1->fetch_assoc()) {
            if (landing_is_bouquet_product($row)) {
                $tag_results[] = $row;
            }
        }
    }

    // Always pad to 30–40 bouquets so custom landings are never empty
    if (count($tag_results) < 36) {
        $excludeIds = array_map(static fn($r) => (int) $r['id'], $tag_results);
        $fill = collection_fetch_bouquet_backfill($conn, 40 - count($tag_results), $excludeIds);
        foreach ($fill as $row) {
            $row['type'] = 'flower';
            $tag_results[] = $row;
            if (count($tag_results) >= 40) {
                break;
            }
        }
    }
    $tag_results = array_slice($tag_results, 0, 40);
}

// Any product-showcase landing without enough bouquets gets a bouquet fill
if ($layout_type === 'product_showcase' && count($tag_results) < 30) {
    require_once __DIR__ . '/includes/collection_landing.php';
    require_once __DIR__ . '/includes/landing_page_sliders.php';
    $excludeIds = array_map(static fn($r) => (int) ($r['id'] ?? 0), $tag_results);
    $fill = collection_fetch_bouquet_backfill($conn, 40 - count($tag_results), $excludeIds);
    foreach ($fill as $row) {
        if (!landing_is_bouquet_product($row)) {
            continue;
        }
        $row['type'] = 'flower';
        $tag_results[] = $row;
        if (count($tag_results) >= 40) {
            break;
        }
    }
}

if ($isLocationPage && count($tag_results) === 0) {
    require_once __DIR__ . '/includes/location_landing.php';
    require_once __DIR__ . '/includes/collection_landing.php';
    $tag_results = collection_fetch_bouquet_backfill($conn, 40, []);
    foreach ($tag_results as &$tr) {
        $tr['type'] = 'flower';
    }
    unset($tr);
}

$landingSliders = [];
if (!empty($pageData['enable_product_sliders'])) {
    try {
        require_once __DIR__ . '/includes/landing_page_sliders.php';
        $landingSliders = landing_get_occasion_product_sliders($conn, $pageData);
    } catch (Throwable $e) {
        error_log('Landing sliders error: ' . $e->getMessage());
        $landingSliders = [];
    }
}
$show_landing_sliders = count($landingSliders) > 0;
$hide_product_grid = !empty($pageData['hide_product_grid']) && $show_landing_sliders;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/partials/tailwind_config.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= htmlspecialchars($meta_title) ?></title>
    <?php if ($meta_desc): ?><meta name="description" content="<?= htmlspecialchars($meta_desc) ?>"><?php endif; ?>
    <?php if ($meta_keys): ?><meta name="keywords" content="<?= htmlspecialchars($meta_keys) ?>"><?php endif; ?>
    <?php
    echo render_dynamic_page_seo_head($pageData, $meta_title, $meta_desc, $meta_keys);
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_custom_page_json_ld($pageData, $tag_results);
    $faqs = !empty($pageData['faqs']) ? json_decode($pageData['faqs'], true) : [];
    ?>
    
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
    <?php if (!empty($pageData['enable_product_sliders'])): ?>
    <link rel="stylesheet" href="/assets/css/homepage-premium.css?v=4" />
    <?php endif; ?>
    <?php if ($isLocationPage): ?>
    <link rel="stylesheet" href="/assets/css/location-landing.css?v=3" />
    <?php endif; ?>

    <style>
        :root {
            --primary: <?= $pCol ?>;
            --accent: <?= $sCol ?>;
            --bg-site: <?= $bgColor ?>;
            --text-main: <?= $tCol ?>;
            --font-main: <?= $fFam ?>;
            --shadow-md: 0 10px 25px rgba(0,0,0,0.08);
        }

        body {
            font-family: var(--font-main), sans-serif;
            margin: 0;
            color: var(--text-main);
            background: var(--bg-site);
            line-height: 1.8;
            overflow-x: hidden;
        }

        h1, h2, h3, h4 { font-family: var(--font-main); }
        .container { max-width: 1200px; margin: auto; padding: 0 20px; }
        .section { padding: 30px 0; }

        /* --- NAVBAR RESET --- */
        nav, .navbar-container {
            position: relative !important;
            width: 100%;
            z-index: 1000;
            background: #ffffff !important;
        }

        /* --- HERO --- */
        .page-header {
            text-align: center; 
            margin-bottom: 10px;
            background: transparent;
            color: var(--primary);
            padding: 20px 0 5px 0;
            border-radius: 0;
        }

        .page-content {
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: var(--shadow-md);
            margin-top: 0; /* Removed overlap */
            position: relative;
            z-index: 10;
            font-size: 1.1rem;
            color: #444;
            min-height: 400px;
        }

        .page-content-wrapper h1, .showcase-text-content h1 { font-size: 2.25rem !important; font-weight: 700 !important; margin: 1rem 0 !important; line-height: 1.2 !important; display: block !important; }
        .page-content-wrapper h2, .showcase-text-content h2 { font-size: 1.8rem !important; font-weight: 700 !important; margin: 1rem 0 !important; line-height: 1.25 !important; display: block !important; }
        .page-content-wrapper h3, .showcase-text-content h3 { font-size: 1.5rem !important; font-weight: 700 !important; margin: 0.8rem 0 !important; line-height: 1.3 !important; display: block !important; }
        .page-content-wrapper h4, .showcase-text-content h4 { font-size: 1.25rem !important; font-weight: 700 !important; margin: 0.8rem 0 !important; line-height: 1.4 !important; display: block !important; }
        .page-content-wrapper h5, .showcase-text-content h5 { font-size: 1.1rem !important; font-weight: 700 !important; margin: 0.5rem 0 !important; line-height: 1.4 !important; display: block !important; }
        .page-content-wrapper h6, .showcase-text-content h6 { font-size: 1rem !important; font-weight: 700 !important; margin: 0.5rem 0 !important; line-height: 1.4 !important; display: block !important; }
        .page-content-wrapper ul, .showcase-text-content ul { list-style-type: disc; padding-left: 2rem; margin-bottom: 1rem; }
        .page-content-wrapper ol, .showcase-text-content ol { list-style-type: decimal; padding-left: 2rem; margin-bottom: 1rem; }
        .page-content-wrapper p, .showcase-text-content p { margin-bottom: 1rem; line-height: 1.8; }
        .page-content-wrapper a, .showcase-text-content a { color: var(--accent); text-decoration: underline; }

        .page-content img { max-width: 100%; height: auto; border-radius: 15px; margin: 20px 0; }
        
        /* Golden Redirection Links */
        .page-content a, .showcase-text a { 
            color: var(--accent); 
            font-weight: 700; 
            text-decoration: none; 
            text-shadow: 0 0 8px var(--accent); 
            border-bottom: 1px dashed var(--accent); 
            transition: all 0.3s ease; 
        }
        .page-content a:hover, .showcase-text a:hover { 
            text-shadow: 0 0 15px var(--accent); 
            border-bottom-style: solid; 
        }

        /* Extra Images Grid (Event Story) */
        .extra-images-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 35px; margin-top: 0; position: relative; z-index: 10; padding-bottom: 50px;}
        .extra-image-container { 
            background: linear-gradient(145deg, var(--accent), #eabe4f); 
            border-radius: 18px; 
            padding: 5px; /* Creates a solid golden frame */
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.2); 
            display: flex; 
            flex-direction: column; 
            transition: all 0.4s ease; 
        }
        .extra-image-container:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 45px rgba(212, 175, 55, 0.4);
        }
        .extra-image { 
            width: 100%; 
            height: 280px; 
            object-fit: cover; 
            border-radius: 13px 13px 0 0; 
            margin: 0; 
            background: white;
        }
        .extra-image-desc-wrapper {
            background: #fffdf8; 
            padding: 20px 15px;
            border-radius: 0 0 13px 13px;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .extra-image-desc {
            font-size: 0.95rem; 
            color: #555; 
            font-style: italic;
            text-align: center; 
            font-family: var(--font-main);
            margin: 0;
            line-height: 1.6;
        }
        
        .event-content-box {
            background: white; padding: 50px; border-radius: 25px; box-shadow: var(--shadow-md); 
            font-size: 1.15rem; line-height: 1.9; color: #444; margin-bottom: 50px;
        }

        /* Product Showcase Layout */
        .showcase-text { background: white; padding: 40px; border-radius: 25px; box-shadow: var(--shadow-md); font-size: 1.05rem; line-height: 1.8; color: #555; margin-bottom: 40px; margin-top: 20px; text-align: left;}
        
        .showcase-text-content {
            display: block;
        }
        .view-more-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 30px;
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: var(--font-main);
        }
        .view-more-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(50, 110, 84, 0.2);
        }

        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 24px; padding-bottom: 40px; margin-top: 0; position: relative; z-index: 10; }
        
        .result-card {
            display: flex; flex-direction: column; text-decoration: none; color: inherit;
            background: transparent; overflow: hidden;
            transition: 0.3s ease; height: 100%;
        }
        .img-wrapper { border-radius: 12px; overflow: hidden; }
        .result-card:hover .img-wrapper img { transform: scale(1.05); }
        .result-card img.prod-img { width: 100%; aspect-ratio: 1/1; object-fit: cover; transition: 0.4s ease; display: block; background: #fdfdfd; }
        .card-content { padding: 12px 4px 0 4px; display: flex; flex-direction: column; flex-grow: 1; }
        .card-title { margin: 0 0 6px 0; font-size: 1.05rem; color: #333; font-weight: 500; font-family: 'Inter', sans-serif; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35; }

        /* MOBILE OPTIMIZATION */
        @media(max-width:768px){
            .section { padding: 25px 0; }
            .page-header { padding: 20px 20px 5px; border-radius: 0; margin-bottom: 10px; overflow: hidden; }
            .page-header h1 { font-size: clamp(1.5rem, 7vw, 2.5rem) !important; white-space: normal; display: block; text-transform: none; }
        .page-breadcrumb { font-size: 0.875rem; color: #64748b; margin-bottom: 1rem; }
        .page-breadcrumb ol { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 0.35rem; justify-content: center; }
        .page-breadcrumb li + li::before { content: '/'; margin-right: 0.35rem; color: #cbd5e1; }
        .page-breadcrumb a { color: var(--primary); text-decoration: none; }
        .page-breadcrumb a:hover { text-decoration: underline; }
        .landing-hero-img { width: 100%; max-width: 900px; height: auto; border-radius: 20px; margin: 0 auto 1.5rem; display: block; box-shadow: var(--shadow-md); }
        .landing-page-sliders { width: 100%; max-width: 1200px; margin: 0 auto 0.5rem; }
        .landing-page-sliders .hp-product-slider-section { padding-top: 0.5rem; padding-bottom: 0.75rem; }
        .showcase-section-title { font-size: 1.5rem; font-weight: 800; color: var(--primary); margin: 0 0 1.25rem; text-align: center; }
            
            /* Tighter, mobile-focused content padding */
            .page-content, .event-content-box { padding: 25px 20px; font-size: 1rem; border-radius: 20px; margin: 0 15px 30px;}
            
            /* Masonry-like tight grid for extra images on mobile */
            .extra-images-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; padding: 0 15px; margin-top: 0; padding-bottom: 30px;}
            .extra-image { height: 180px; border-radius: 10px 10px 0 0; }
            .extra-image-container { padding: 4px; border-radius: 14px; }
            .extra-image-desc-wrapper { padding: 12px 10px; border-radius: 0 0 10px 10px; }
            .extra-image-desc { font-size: 0.85rem; }
            
            /* Product Showcase Mobile Adjustments */
            .showcase-text { margin-left:15px; margin-right:15px; padding: 25px 20px; font-size: 0.95rem; border-radius: 20px; margin-bottom: 25px; margin-top: 10px;}
            .results-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; padding: 0 16px 30px 16px; margin-top: 0;}
            .result-card { border-radius: 0; padding-top: 8px;} 
            .result-card img.prod-img { height: auto; border-radius: 10px; }
            .card-content { padding: 10px 0 0 0; }
            .card-title { font-size: 0.95rem; line-height: 1.25; margin-bottom: 2px; font-weight: 400;}
        }
    </style>
</head>
<body<?= $isLocationPage ? ' class="location-page"' : '' ?>>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="page-header">
    <div class="container" id="pageHeaderContainer" style="overflow: hidden;">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol itemscope itemtype="https://schema.org/BreadcrumbList">
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a itemprop="item" href="/"><span itemprop="name">Home</span></a>
                    <meta itemprop="position" content="1">
                </li>
                <?php if ($isLocationPage): ?>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a itemprop="item" href="/flowers"><span itemprop="name">Flowers</span></a>
                    <meta itemprop="position" content="2">
                </li>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                    <span itemprop="name"><?= htmlspecialchars($page_h1) ?></span>
                    <meta itemprop="item" content="<?= htmlspecialchars($canonical_url) ?>">
                    <meta itemprop="position" content="3">
                </li>
                <?php else: ?>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                    <span itemprop="name"><?= htmlspecialchars($page_h1) ?></span>
                    <meta itemprop="item" content="<?= htmlspecialchars($canonical_url) ?>">
                    <meta itemprop="position" content="2">
                </li>
                <?php endif; ?>
            </ol>
        </nav>
        <h1 id="dynamicPageTitle" style="font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 800; letter-spacing: -0.015em; line-height: 1.15; margin: 0; color: var(--primary); text-transform: none; white-space: normal; display: block; max-width: none;"><?= htmlspecialchars($page_h1) ?></h1>
        <?php if (!empty(trim($pageData['short_description'] ?? ''))): ?>
            <p class="page-intro" style="font-size: 1rem; margin-top: 8px; font-weight: 700; color: #555; line-height: 1.5;"><?= format_content(htmlspecialchars($pageData['short_description'])) ?></p>
        <?php endif; ?>
    </div>
</header>

<script>
    function adjustTitleSize() {
        const title = document.getElementById('dynamicPageTitle');
        const container = document.getElementById('pageHeaderContainer');
        if (!title || !container) return;
        
        // Reset to initial to measure correct unstrained width
        title.style.removeProperty('font-size'); 
        title.style.fontSize = 'clamp(2rem, 5vw, 3.5rem)';
        
        const containerWidth = container.clientWidth - parseInt(window.getComputedStyle(container).paddingLeft || 0) - parseInt(window.getComputedStyle(container).paddingRight || 0);
        const contentWidth = title.scrollWidth; 
        
        if (contentWidth > containerWidth && containerWidth > 0) {
            const currentFontSize = parseFloat(window.getComputedStyle(title).fontSize);
            // 98% of container width to be safe from rounding edge cases and leave slight margin
            const ratio = (containerWidth * 0.98) / contentWidth;
            const newFontSize = Math.floor(currentFontSize * ratio);
            
            title.style.setProperty('font-size', newFontSize + 'px', 'important');
        }
    }
    
    window.addEventListener('resize', adjustTitleSize);
    document.addEventListener('DOMContentLoaded', adjustTitleSize);
    // Execute immediately in case it executes after layout
    adjustTitleSize();
</script>

<main id="main-content" class="section container<?= $isLocationPage ? ' loc-main' : '' ?>" style="padding-top: 0;<?= $isLocationPage ? ' max-width: 100%; margin: 0;' : ' max-width: 1200px; margin: 0 auto;' ?>">
    
    <?php if ($isLocationPage && $layout_type === 'product_showcase'): ?>
        <?php include __DIR__ . '/partials/landing/location_showcase.php'; ?>
    <?php elseif ($show_landing_sliders): ?>
        <?php include __DIR__ . '/partials/landing/product_sliders.php'; ?>
    <?php elseif (!empty($pageData['hero_image']) || !empty($pageData['hero_image_web'])): ?>
        <figure class="landing-hero-figure" style="text-align:center; margin: 0 0 1.5rem;">
            <img src="<?= htmlspecialchars($hero_img_web) ?>"
                 class="landing-hero-img"
                 width="900"
                 height="500"
                 loading="eager"
                 fetchpriority="high"
                 decoding="async"
                 alt="<?= htmlspecialchars($hero_img_alt) ?>">
        </figure>
    <?php endif; ?>

    <?php if ($layout_type === 'event_info'): ?>
        <div class="event-info-wrapper">
            
            <?php if (!empty($extra_images) && count($extra_images) > 0): ?>
                <div class="extra-images-grid">
                    <?php foreach($extra_images as $imgData): 
                        $imgName = is_array($imgData) ? $imgData['image'] : $imgData;
                        $imgDesc = is_array($imgData) ? ($imgData['desc'] ?? '') : '';
                        $imgLink = is_array($imgData) ? ($imgData['link'] ?? '') : '';
                    ?>
                        <div class="extra-image-container">
                            <?php if (!empty($imgLink)): ?>
                            <a href="<?= htmlspecialchars($imgLink) ?>" target="_blank" style="display:block;">
                            <?php endif; ?>
                            
                            <img src="/uploads/<?= htmlspecialchars($imgName) ?>" class="extra-image" alt="<?= htmlspecialchars($imgDesc) ?>">
                            
                            <?php if (!empty($imgLink)): ?>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($imgDesc)): ?>
                                <div class="extra-image-desc-wrapper">
                                    <div class="extra-image-desc">
                                        <?= format_content(htmlspecialchars($imgDesc)) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty(trim($pageData['content']))): ?>
            <div class="event-content-box <?= (empty($extra_images) || count($extra_images) === 0) ? 'page-content' : '' ?>">
                <!-- Debug Raw Info: <?= htmlspecialchars(substr($pageData['content'], 0, 100)) ?> -->
                <div class="page-content-wrapper">
                    <?= $pageContent ?: $pageData['content'] ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
    <?php else: // product_showcase ?>
        <div class="showcase-content">
            <?php if (!$hide_product_grid && !$isLocationPage): ?>
            <h2 id="<?= htmlspecialchars($products_section_id) ?>" class="showcase-section-title"><?= htmlspecialchars($products_section_heading) ?></h2>
            <div class="results-grid" role="list" aria-labelledby="<?= htmlspecialchars($products_section_id) ?>">
                <?php if (count($tag_results) > 0): ?>
                    <?php 
                        $render_index = 0;
                        $midpoint = floor(count($tag_results) / 2);
                    ?>
                    <?php foreach ($tag_results as $item): 
                        if (!empty($pageData['midgrid_image']) && $render_index === (int)$midpoint):
                    ?>
                        <div style="grid-column: 1 / -1; margin: 15px 0 25px 0; border-radius: 20px; overflow:hidden; box-shadow: var(--shadow-md); position: relative;">
                            <img src="/uploads/<?= htmlspecialchars($pageData['midgrid_image']) ?>" 
                                 alt="<?= htmlspecialchars($pageData['midgrid_image_alt'] ?? ($occasion_label . ' promotional banner')) ?>" 
                                 style="width:100%; height:auto; display:block; object-fit: cover;">
                        </div>
                    <?php 
                        endif;
                        $render_index++;
                        
                        $id = $item['id'];
                        $dbImage = $item['image'];
                        $price = $item['price'];
                        $originalPrice = $item['original_price'];

                        if (strpos($dbImage, 'uploads/') === 0) {
                            $finalPath = "/" . $dbImage;
                        } else {
                            $finalPath = "/uploads/" . $dbImage;
                        }

                        $link = function_exists('occasion_product_url') ? occasion_product_url($item) : "flower-detail.php?id=$id";
                        $imgAlt = function_exists('occasion_product_image_alt')
                            ? occasion_product_image_alt($item, $occasion_label)
                            : htmlspecialchars($item['name']);
                    ?>
                    <?php
                        $discount = 0;
                        if ($originalPrice > $price && $originalPrice > 0) {
                            $discount = round((($originalPrice - $price) / $originalPrice) * 100);
                        }
                    ?>
                    <a href="<?= htmlspecialchars($link) ?>" class="result-card group" role="listitem" title="<?= htmlspecialchars($item['name']) ?>">
                        <div class="img-wrapper">
                            <img src="<?= $finalPath ?>" class="prod-img"
                                 width="400"
                                 height="400"
                                 loading="lazy"
                                 decoding="async"
                                 onerror="this.src='https://images.unsplash.com/photo-1519225495806-7d5225a0d16a?q=80&w=800';"
                                 alt="<?= htmlspecialchars($imgAlt) ?>">
                        </div>
                        <div class="card-content">
                            <span class="bg-slate-100 text-slate-500 w-fit px-2 py-1 rounded border border-slate-200 uppercase tracking-widest text-[9px] font-bold mb-2 inline-block"><?= ucfirst($item['type']) ?></span>
                            <h3 class="card-title"><?= htmlspecialchars($item['name']) ?></h3>
                            
                            <div class="flex items-baseline gap-2 mb-1.5">
                                <?php if($originalPrice > $price): ?>
                                    <span class="text-slate-400 line-through text-[0.85rem] font-medium">₹<?= number_format($originalPrice) ?></span>
                                <?php endif; ?>
                                <span class="text-slate-900 font-bold text-[1.05rem]">₹<?= number_format($price) ?></span>
                            </div>
                            
                            <?php if($discount > 0): ?>
                            <div class="text-[0.65rem] font-bold tracking-wide rounded mb-2 w-fit" style="background: #e3f5e3; color: #1e8e3e; padding: 2px 6px;">
                                <?= $discount ?>% OFF
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-auto pt-2 pb-2 w-full">
                                <!--<div class="w-full border border-[var(--accent)] text-[var(--accent)] text-center group-hover:bg-[var(--accent)] group-hover:text-white transition-colors py-1.5 rounded-md font-semibold text-sm">
                                    View Product
                                </div>-->
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php if (!empty($pageData['midgrid_image'])): ?>
                        <div style="grid-column: 1 / -1; margin: 15px 0 25px 0; border-radius: 20px; overflow:hidden; box-shadow: var(--shadow-md); position: relative;">
                            <img src="/uploads/<?= htmlspecialchars($pageData['midgrid_image']) ?>" 
                                 alt="<?= htmlspecialchars($pageData['midgrid_image_alt'] ?? ($occasion_label . ' promotional banner')) ?>" 
                                 style="width:100%; height:auto; display:block; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 0; background: #fff; border-radius: 20px; box-shadow: var(--shadow-md);">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: #eee; margin-bottom: 20px;" aria-hidden="true"></i>
                        <p style="color: #888; font-size: 1.05rem; margin: 0;">Browse our <a href="/flowers" class="text-primary font-semibold hover:underline">flowers</a>, <a href="/cakes" class="text-primary font-semibold hover:underline">cakes</a>, and <a href="/gifts" class="text-primary font-semibold hover:underline">gifts</a><?= $isLocationPage ? ' for delivery in ' . htmlspecialchars($locationArea) . '.' : ' for delivery across Delhi NCR.' ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- View More Button -->
            <div style="text-align: center; margin-top: 20px; margin-bottom: 40px;">
                <a href="/flowers" class="view-more-btn" style="text-decoration: none; display: inline-block;" title="View all flowers<?= $isLocationPage ? ' for ' . htmlspecialchars($locationArea) : '' ?>">View All <?= $isLocationPage ? htmlspecialchars($locationArea) . ' ' : '' ?>Flowers</a>
            </div>
            <?php endif; ?>
            
            <?php if ($isLocationPage && !empty(trim($pageData['short_description'] ?? ''))): ?>
            <p class="loc-intro-below"><?= format_content(htmlspecialchars($pageData['short_description'])) ?></p>
            <?php endif; ?>

            <?php if (!empty(trim($pageData['content']))): ?>
            <article class="showcase-text" id="showcaseContainer" aria-label="<?= htmlspecialchars($occasion_label) ?> gift guide">
                <div class="showcase-text-content" id="showcaseTextContent">
                    <?= $pageContent ?: $pageData['content'] ?>
                </div>
            </article>
            <?php endif; ?>

            <?php if ($isLocationPage): ?>
                <?php include __DIR__ . '/partials/landing/nearby_locations.php'; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($faqs)): ?>
    <section class="faq-section" id="faq" aria-labelledby="faq-heading" style="margin-top: 50px; padding: 40px; background: white; border-radius: 25px; box-shadow: var(--shadow-md);">
        <h2 id="faq-heading" style="color: var(--primary); text-align: center; margin-bottom: 30px; font-weight: 800;"><?= htmlspecialchars($faq_section_heading) ?></h2>
        <div class="faq-list" style="display:flex; flex-direction:column; gap:15px;">
            <?php foreach ($faqs as $index => $faq): ?>
                <div class="faq-item" style="border: 1px solid #eee; border-radius: 12px; overflow: hidden;">
                    <div class="faq-question" style="background: #fafafa; padding: 18px 25px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 700; color: var(--text-main);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'; const icon = this.querySelector('i'); if(this.nextElementSibling.style.display === 'block') { icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-up'); } else { icon.classList.remove('fa-chevron-up'); icon.classList.add('fa-chevron-down'); }">
                        <span><?= htmlspecialchars(stripslashes($faq['question'] ?? '')) ?></span>
                        <i class="fas fa-chevron-down" style="color: var(--primary);"></i>
                    </div>
                    <div class="faq-answer" style="display: none; padding: 20px 25px; background: white; color: #555; line-height: 1.7; border-top: 1px solid #eee;">
                        <?= nl2br(htmlspecialchars(html_entity_decode(stripslashes($faq['answer'] ?? ''), ENT_QUOTES), ENT_QUOTES)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>


<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
    const themeChannel = new BroadcastChannel('theme_sync');
    themeChannel.onmessage = (e) => {
        const d = e.data;
        const root = document.documentElement;
        root.style.setProperty('--primary', d.p);
        root.style.setProperty('--accent', d.s);
        root.style.setProperty('--bg-site', d.bg);
        root.style.setProperty('--text-main', d.t);
        document.body.style.fontFamily = d.f;
        document.querySelectorAll('h1, h2, h3, h4').forEach(h => h.style.fontFamily = d.f);
    };
</script>

</body>
</html>
