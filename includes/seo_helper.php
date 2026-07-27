<?php
// includes/seo_helper.php

if (!function_exists('canonical_mark_rendered')) {
    function canonical_mark_rendered(): void
    {
        $GLOBALS['_canonical_rendered'] = true;
    }
}

if (!function_exists('canonical_was_rendered')) {
    function canonical_was_rendered(): bool
    {
        return !empty($GLOBALS['_canonical_rendered']);
    }
}

if (!function_exists('set_page_canonical_url')) {
    function set_page_canonical_url(string $url): void
    {
        $GLOBALS['page_canonical_url'] = $url;
    }
}

if (!function_exists('normalize_canonical_path')) {
    function normalize_canonical_path(string $path): string
    {
        if (preg_match('#/index\.php$#i', $path)) {
            $path = preg_replace('#/index\.php$#i', '/', $path);
        }

        if (preg_match('#^(.+)\.php$#i', $path, $matches) && stripos($path, '/admin/') === false) {
            $path = $matches[1];
        }

        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
    }
}

if (!function_exists('build_canonical_query_string')) {
    function build_canonical_query_string(?string $query): string
    {
        if (empty($query)) {
            return '';
        }

        parse_str($query, $params);

        $ignore = [
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
            'fbclid', 'gclid', 'ref', 'mc_cid', 'mc_eid',
        ];
        $allowed = ['id', 'slug', 'name', 'q', 'tag', 'page'];

        $filtered = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $ignore, true)) {
                continue;
            }
            if (in_array($key, $allowed, true) && $value !== '' && $value !== null) {
                $filtered[$key] = $value;
            }
        }

        if ($filtered === []) {
            return '';
        }

        return http_build_query($filtered);
    }
}

if (!function_exists('get_self_canonical_url')) {
    /**
     * Absolute self-referencing canonical URL for the current (or overridden) request.
     */
    function get_self_canonical_url(?string $overridePath = null): string
    {
        if (!empty($GLOBALS['page_canonical_url'])) {
            return $GLOBALS['page_canonical_url'];
        }

        if ($overridePath !== null) {
            $path = $overridePath[0] === '/' ? $overridePath : '/' . $overridePath;
            return seo_site_base_url() . normalize_canonical_path($path);
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = normalize_canonical_path($path);
        $query = build_canonical_query_string(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY));

        $url = seo_site_base_url() . $path;
        if ($query !== '') {
            $url .= '?' . $query;
        }

        return $url;
    }
}

if (!function_exists('get_product_canonical_url')) {
    function get_product_canonical_url(string $type, array $product): string
    {
        return seo_site_base_url() . product_url([
            'type' => $type,
            'slug' => $product['slug'] ?? '',
            'id' => (int) ($product['id'] ?? 0),
        ]);
    }
}

if (!function_exists('get_blog_canonical_url')) {
    function get_blog_canonical_url(array $blog): string
    {
        $slug = trim($blog['slug'] ?? '');
        if ($slug !== '') {
            return seo_site_base_url() . '/blog/' . rawurlencode($slug);
        }

        return seo_site_base_url() . '/blog-detail?id=' . (int) ($blog['id'] ?? 0);
    }
}

if (!function_exists('render_canonical_link')) {
    function render_canonical_link(?string $url = null): string
    {
        if (canonical_was_rendered()) {
            return '';
        }

        $url = $url ?? get_self_canonical_url();
        canonical_mark_rendered();

        return '<link rel="canonical" href="' . htmlspecialchars($url) . '">' . "\n";
    }
}

if (!function_exists('render_seo_theme_extras')) {
    function render_seo_theme_extras(): string
    {
        global $conn;

        $ver = time();
        $favUrl = '/favicon.png';
        $favType = 'image/png';

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/favicon.png')) {
            $favUrl = '/favicon.png';
            $favType = 'image/png';
        }

        $css_override = '';
        $anim_script = '';

        if ($conn) {
            $t_q = $conn->query("SELECT * FROM settings WHERE id=1");
            if ($t_q && $t_row = $t_q->fetch_assoc()) {
                $primary = $t_row['theme_primary'] ?? '#2f6f4e';
                $accent = $t_row['theme_secondary'] ?? '#d4af37';
                $bg = $t_row['theme_bg_color'] ?? '#ffffff';
                $text = $t_row['theme_text_color'] ?? '#2c3e50';
                $font = $t_row['theme_font'] ?? 'Lato';
                $anim = $t_row['theme_animation'] ?? 'none';

                $css_override = "
            <style>
                :root {
                    --primary: {$primary} !important;
                    --primary-dark: {$primary} !important;
                    --accent: {$accent} !important;
                    --bg-body: {$bg} !important;
                    --text-main: {$text} !important;
                    --nav-bg: {$bg} !important;
                    --nav-text: {$text} !important;
                }
                body, h1, h2, h3, h4, .nav-links a { font-family: '{$font}', sans-serif !important; }
                .site-footer { background-color: {$primary} !important; }
            </style>";

                if ($anim !== 'none') {
                    $anim_script = "
                <script>const THEME_ANIMATION = '{$anim}';</script>
                <script defer src='/assets/js/theme-effects.js'></script>";
                }
            }
        }

        return '
    <link rel="icon" type="' . $favType . '" href="' . $favUrl . '?v=' . $ver . '">
    <link rel="apple-touch-icon" href="' . $favUrl . '?v=' . $ver . '">
    ' . $css_override . $anim_script;
    }
}

if (!function_exists('is_seo_spam_content')) {
    function is_seo_spam_content(string $text): bool
    {
        $text = strtolower($text);

        return (bool) preg_match(
            '/hell is commonly|sai baba mandir|place of torment|lorem ipsum|click here to buy|viagra|casino/i',
            $text
        );
    }
}

if (!function_exists('sanitize_seo_field')) {
    function sanitize_seo_field(string $value, string $fallback): string
    {
        $value = trim($value);

        return ($value === '' || is_seo_spam_content($value)) ? $fallback : $value;
    }
}

if (!function_exists('get_page_seo_overrides')) {
    /**
     * Authoritative SEO values for pages with known bad or spam DB entries.
     */
    function get_page_seo_overrides(): array
    {
        return [
            'flowers.php' => [
                'title' => 'Fresh Flowers & Bouquets Online | Sai Flower Delhi',
                'description' => 'Order fresh flower bouquets online from Sai Flower. Same-day delivery in Delhi. Roses, orchids, wedding & event flowers.',
                'keywords' => 'fresh flowers Delhi, flower delivery, bouquets online, same day delivery, Sai Flower',
            ],
            'contact.php' => [
                'title' => 'Contact Sai Flower | Flower Delivery Delhi | +91 88020 04527',
                'description' => 'Get in touch with Sai Flower for flower delivery in Delhi NCR. Call +91 88020 04527, WhatsApp us, or visit our Lodhi Road shop.',
                'keywords' => 'contact Sai Flower, flower delivery Delhi, florist phone number',
            ],
        ];
    }
}

if (!function_exists('limit_meta_keywords')) {
    function limit_meta_keywords(string $keywords, int $max = 5): string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $keywords))));
        if (count($parts) <= $max) {
            return implode(', ', $parts);
        }

        return implode(', ', array_slice($parts, 0, $max));
    }
}

if (!function_exists('render_social_meta_tags')) {
    /**
     * Open Graph and Twitter Card tags for static catalog pages.
     */
    function render_social_meta_tags(string $title, string $description, ?string $url = null, ?string $image = null): string
    {
        $url = $url ?? get_self_canonical_url();
        $image = seo_absolute_image_url($image);
        $siteName = 'Sai Flower';

        $html = '<meta property="og:type" content="website">' . "\n";
        $html .= '<meta property="og:site_name" content="' . htmlspecialchars($siteName) . '">' . "\n";
        $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
        $html .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . "\n";
        $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
        $html .= '<meta property="og:image:alt" content="' . htmlspecialchars($title) . '">' . "\n";
        $html .= '<meta property="og:locale" content="en_IN">' . "\n";
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . "\n";
        $html .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";

        return $html;
    }
}

function render_seo($page_identifier, array $options = [])
{
    global $conn;

    $includeTitle = $options['title'] ?? true;
    $includeCanonical = $options['canonical'] ?? true;
    $includeTheme = $options['theme'] ?? true;
    $includeSocial = $options['social'] ?? true;

    $seo = [
        'title' => 'Sai Flower | Premium Flowers & Events',
        'description' => 'Sai Flower offers fresh flower delivery and event decoration services in New Delhi.',
        'keywords' => 'florist, flowers, wedding decor, delhi, Sai Flower',
    ];

    $overrides = get_page_seo_overrides();

    // Hardcoded overrides take precedence — never read spam from DB for these pages
    if (isset($overrides[$page_identifier])) {
        $seo = array_merge($seo, $overrides[$page_identifier]);
    } elseif ($conn) {
        try {
            $stmt = $conn->prepare("SELECT title, description, keywords FROM seo_meta WHERE page_identifier = ?");
            if ($stmt) {
                $stmt->bind_param("s", $page_identifier);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if (!empty($row['title'])) {
                        $seo['title'] = sanitize_seo_field($row['title'], $seo['title']);
                    }
                    if (!empty($row['description'])) {
                        $seo['description'] = sanitize_seo_field($row['description'], $seo['description']);
                    }
                    if (!empty($row['keywords'])) {
                        $seo['keywords'] = sanitize_seo_field($row['keywords'], $seo['keywords']);
                    }
                }
            }
        } catch (Exception $e) {
        }
    }

    $seo['title'] = sanitize_seo_field($seo['title'], 'Sai Flower | Premium Flowers & Events');
    $seo['description'] = sanitize_seo_field($seo['description'], 'Sai Flower offers fresh flower delivery in New Delhi.');

    $seo['keywords'] = limit_meta_keywords($seo['keywords']);

    $out = '';

    if ($includeTitle) {
        $out .= '
    <title>' . htmlspecialchars($seo['title']) . '</title>
    <meta name="description" content="' . htmlspecialchars($seo['description']) . '">
    <meta name="keywords" content="' . htmlspecialchars($seo['keywords']) . '">';
    }

    if ($includeCanonical) {
        $out .= render_canonical_link();
    }

    if ($includeSocial) {
        $out .= render_social_meta_tags($seo['title'], $seo['description']);
    }

    if ($includeTheme) {
        $out .= render_seo_theme_extras();
    }

    return $out;
}

/**
 * Absolute site base URL (https preferred on production host).
 */
function seo_site_base_url(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'saiflower.com';
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https' : 'http';
    if (stripos($host, 'saiflower.com') !== false) {
        $protocol = 'https';
    }
    return $protocol . '://' . $host;
}

/**
 * Canonical URL for a dynamic / landing page (no query strings).
 */
function get_page_canonical_url(array $pageData): string
{
    if (!empty($pageData['seo_canonical'])) {
        return rtrim($pageData['seo_canonical'], '/');
    }
    $slug = trim($pageData['slug'] ?? '');
    if ($slug !== '') {
        return seo_site_base_url() . '/' . rawurlencode($slug);
    }
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return seo_site_base_url() . $path;
}

/**
 * Resolve OG/social image to an absolute URL.
 */
function seo_absolute_image_url(?string $path): string
{
    if (empty($path)) {
        return seo_site_base_url() . '/uploads/logo_transparent.png';
    }
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }
    return seo_site_base_url() . (strpos($path, '/') === 0 ? $path : '/' . $path);
}

/**
 * Meta, canonical, Open Graph, and Twitter Card tags for page.php landing/dynamic pages.
 */
function render_dynamic_page_seo_head(array $pageData, string $meta_title, string $meta_desc, string $meta_keys): string
{
    $canonical = get_page_canonical_url($pageData);
    $robots = $pageData['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    $ogType = $pageData['og_type'] ?? 'website';
    $ogTitle = !empty($pageData['og_title']) ? $pageData['og_title'] : $meta_title;
    $ogDesc = !empty($pageData['og_description']) ? $pageData['og_description'] : $meta_desc;
    $ogImage = seo_absolute_image_url($pageData['og_image'] ?? $pageData['hero_image'] ?? null);
    $ogImageAlt = $pageData['og_image_alt'] ?? $pageData['hero_image_alt'] ?? $meta_title;
    $locale = $pageData['seo_locale'] ?? 'en_IN';

    $html = '';
    $html .= '<meta name="robots" content="' . htmlspecialchars($robots) . '">' . "\n";
    $html .= '<meta name="author" content="Sai Flowers">' . "\n";
    $html .= '<meta name="publisher" content="Sai Flowers">' . "\n";
    if (!canonical_was_rendered()) {
        $html .= render_canonical_link($canonical);
    }
    $html .= '<link rel="alternate" hreflang="en-in" href="' . htmlspecialchars($canonical) . '">' . "\n";
    $html .= '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($canonical) . '">' . "\n";

    $html .= '<meta property="og:type" content="' . htmlspecialchars($ogType) . '">' . "\n";
    $html .= '<meta property="og:site_name" content="Sai Flowers">' . "\n";
    $html .= '<meta property="og:title" content="' . htmlspecialchars($ogTitle) . '">' . "\n";
    $html .= '<meta property="og:description" content="' . htmlspecialchars($ogDesc) . '">' . "\n";
    $html .= '<meta property="og:url" content="' . htmlspecialchars($canonical) . '">' . "\n";
    $html .= '<meta property="og:image" content="' . htmlspecialchars($ogImage) . '">' . "\n";
    $html .= '<meta property="og:image:alt" content="' . htmlspecialchars($ogImageAlt) . '">' . "\n";
    $html .= '<meta property="og:locale" content="' . htmlspecialchars(str_replace('-', '_', $locale)) . '">' . "\n";

    $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $html .= '<meta name="twitter:title" content="' . htmlspecialchars($ogTitle) . '">' . "\n";
    $html .= '<meta name="twitter:description" content="' . htmlspecialchars($ogDesc) . '">' . "\n";
    $html .= '<meta name="twitter:image" content="' . htmlspecialchars($ogImage) . '">' . "\n";
    $html .= '<meta name="twitter:image:alt" content="' . htmlspecialchars($ogImageAlt) . '">' . "\n";

    return $html;
}

/**
 * Descriptive img alt for occasion product cards.
 */
function occasion_product_image_alt(array $item, string $occasionLabel): string
{
    $name = trim($item['name'] ?? 'Gift');
    $type = ucfirst(trim($item['type'] ?? 'product'));
    return $occasionLabel . ' ' . $type . ' — ' . $name . ' | Sai Flowers Delhi';
}

/**
 * SEO-friendly product URL (canonical /{type}/{slug} preferred).
 */
function occasion_product_url(array $item): string
{
    return product_url($item);
}
?>
