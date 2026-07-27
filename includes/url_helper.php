<?php
// includes/url_helper.php

if (!function_exists('get_image_url')) {
    /**
     * Get a robust image URL for any stored path format.
     * 
     * @param string $path The stored image path (e.g., 'flower.jpg', 'uploads/flower.jpg', 'http://...')
     * @param string $default_folder Optional subfolder to check in uploads (e.g., 'flowers/')
     * @return string The valid web-accessible URL
     */
    function get_image_url($path, $default_folder = '') {
        // 1. Handle empty paths
        if (empty($path)) {
            return '/assets/images/placeholder.jpg';
        }

        // 2. Return absolute URLs as-is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Normalize base path to start with /
        $ret = '';
        if (strpos($path, '/') === 0) {
            $ret = $path;
        } elseif (strpos($path, 'uploads/') === 0) {
            $ret = '/' . $path;
        } else {
            $folder_part = !empty($default_folder) ? trim($default_folder, '/') . '/' : '';
            $ret = '/uploads/' . $folder_part . $path;
        }

        // WebP Fallback Check
        // Because $ret always starts with "/", we prepend the document root equivalent correctly
        $systemPath = __DIR__ . '/..' . $ret;
        if (preg_match('/\.(jpg|jpeg|png)$/i', $systemPath)) {
            $webpSystemPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $systemPath);
            if (file_exists($webpSystemPath)) {
                $ret = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $ret);
            }
        }

        // Handle encoding
        return str_replace(' ', '%20', $ret);
    }
}

if (!function_exists('product_type_prefix')) {
    function product_type_prefix(string $type): string
    {
        static $map = [
            'flower' => 'flowers',
            'flowers' => 'flowers',
            'cake' => 'cakes',
            'cakes' => 'cakes',
            'gift' => 'gifts',
            'gifts' => 'gifts',
            'event' => 'events',
            'events' => 'events',
        ];

        return $map[strtolower($type)] ?? 'flowers';
    }
}

if (!function_exists('product_url')) {
    /**
     * Canonical product URL: /flowers/{slug}, /cakes/{slug}, etc.
     */
    function product_url(array $item): string
    {
        $type = $item['type'] ?? 'flower';
        $slug = trim($item['slug'] ?? '');
        $id = (int) ($item['id'] ?? 0);

        if ($slug !== '') {
            return '/' . product_type_prefix($type) . '/' . $slug;
        }

        $legacyPages = [
            'flower' => 'flower-detail',
            'cake' => 'cake-detail',
            'gift' => 'gift-detail',
            'event' => 'event-detail',
        ];
        $page = $legacyPages[$type] ?? 'flower-detail';

        return $id > 0 ? '/' . $page . '?id=' . $id : '/' . product_type_prefix($type);
    }
}

if (!function_exists('product_url_by_parts')) {
    function product_url_by_parts(string $type, string $slug = '', int $id = 0): string
    {
        return product_url(['type' => $type, 'slug' => $slug, 'id' => $id]);
    }
}

if (!function_exists('product_canonical_path')) {
    function product_canonical_path(string $type, string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return '';
        }

        return '/' . product_type_prefix($type) . '/' . $slug;
    }
}

if (!function_exists('enforce_canonical_product_url')) {
    /**
     * 301 redirect legacy product URLs to /{type}/{slug}.
     */
    function enforce_canonical_product_url(string $type, array $product): void
    {
        $slug = trim($product['slug'] ?? '');
        if ($slug === '') {
            return;
        }

        $canonical = product_canonical_path($type, $slug);
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $requestPath = '/' . trim($requestPath, '/');

        if (strcasecmp($requestPath, $canonical) !== 0) {
            header('Location: ' . $canonical, true, 301);
            exit;
        }
    }
}

if (!function_exists('product_canonical_tag')) {
    function product_canonical_tag(string $type, array $product): string
    {
        return render_canonical_link(get_product_canonical_url($type, $product));
    }
}

if (!function_exists('normalize_internal_href')) {
    /**
     * Normalize stored CMS links to site-relative paths.
     */
    function normalize_internal_href(string $href): string
    {
        $href = trim($href);
        if ($href === '' || $href === '#') {
            return '#';
        }

        if (preg_match('#^https?://#i', $href)) {
            $href = preg_replace('#^https?://[^/]*saiflower\.com#i', '', $href);
            if ($href === '') {
                return '/';
            }
        }

        return '/' . ltrim($href, '/');
    }
}

if (!function_exists('resolve_cms_item_link')) {
    /**
     * Resolve a CMS/homepage item image or CTA link to the best destination.
     */
    function resolve_cms_item_link(?mysqli $conn, array $item, string $fallback = '/flowers'): string
    {
        require_once __DIR__ . '/occasion_links.php';

        $title = trim((string) ($item['title'] ?? $item['name'] ?? ''));
        $raw = trim((string) ($item['link'] ?? ''));

        if ($conn instanceof mysqli) {
            if (!function_exists('homepage_resolve_product_link')) {
                require_once __DIR__ . '/homepage_premium.php';
            }
            $resolved = homepage_resolve_product_link($conn, $item);
            if ($resolved !== '' && $resolved !== '#') {
                return $resolved;
            }
        }

        $browseLink = $title !== '' ? flower_type_link_for_label($title) : null;
        if ($browseLink !== null && ($raw === '' || preg_match('#^https?://[^/]*saiflower\.com/tag\.php#i', $raw))) {
            return $browseLink;
        }
        if ($raw !== '' && $raw !== '#') {
            return normalize_internal_href($raw);
        }

        return $fallback;
    }
}
?>
