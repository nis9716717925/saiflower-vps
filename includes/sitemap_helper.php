<?php
/**
 * Central sitemap data for XML (sitemap.xml) and HTML (sitemap.php).
 * XML output is buffered so Google always receives a complete, valid document.
 */

if (!function_exists('sitemap_base_url')) {
    function sitemap_base_url(): string
    {
        return 'https://saiflower.com';
    }
}

if (!function_exists('sitemap_format_lastmod')) {
    function sitemap_format_lastmod(?string $datetime): ?string
    {
        if (empty($datetime)) {
            return null;
        }
        $ts = strtotime($datetime);
        if ($ts === false) {
            return null;
        }

        return gmdate('Y-m-d', $ts);
    }
}

if (!function_exists('sitemap_xml_escape')) {
    function sitemap_xml_escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sitemap_normalize_path')) {
    /**
     * Normalize a path to a leading-slash absolute path (no domain).
     */
    function sitemap_normalize_path(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/';
        }
        if (preg_match('#^https?://#i', $path)) {
            $parts = parse_url($path);
            $path = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        // Drop trailing slash except homepage
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}

if (!function_exists('sitemap_is_indexable_path')) {
    /**
     * Skip non-indexable / low-quality URLs for GSC.
     */
    function sitemap_is_indexable_path(string $path): bool
    {
        if ($path === '') {
            return false;
        }
        // Prefer clean paths; allow gallery-detail?id= as fallback only
        if (strpos($path, '?') !== false && strpos($path, '/gallery-detail?') !== 0 && strpos($path, '/blog-detail?') !== 0) {
            return false;
        }
        // Block sensitive / utility paths if they slip in
        $blocked = ['/admin', '/actions', '/includes', '/partials', '/config', '/login', '/register', '/cart', '/checkout', '/wishlist', '/profile'];
        foreach ($blocked as $prefix) {
            if ($path === $prefix || strpos($path, $prefix . '/') === 0 || strpos($path, $prefix . '?') === 0) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('sitemap_safe_query')) {
    /**
     * @return list<array<string, mixed>>
     */
    function sitemap_safe_query($conn, string $sql): array
    {
        $rows = [];
        if (!$conn instanceof mysqli) {
            return $rows;
        }
        try {
            $res = $conn->query($sql);
            if (!$res) {
                return $rows;
            }
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
            $res->free();
        } catch (Throwable $e) {
            error_log('Sitemap query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
        }

        return $rows;
    }
}

if (!function_exists('get_sitemap_static_entries')) {
    /**
     * Public indexable static routes (extensionless).
     *
     * @return list<array{path: string, title: string, priority: string, changefreq: string}>
     */
    function get_sitemap_static_entries(): array
    {
        return [
            ['path' => '/', 'title' => 'Home', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['path' => '/flowers', 'title' => 'Flowers', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['path' => '/cakes', 'title' => 'Cakes', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['path' => '/gifts', 'title' => 'Gifts', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['path' => '/events', 'title' => 'Events & Decor', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['path' => '/gallery', 'title' => 'Gallery', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['path' => '/blog', 'title' => 'Blog', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['path' => '/about', 'title' => 'About Us', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['path' => '/contact', 'title' => 'Contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['path' => '/celebration-calendar', 'title' => 'Celebrations Calendar', 'priority' => '0.85', 'changefreq' => 'weekly'],
            ['path' => '/personalized', 'title' => 'Personalised Gifts', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['path' => '/faq', 'title' => 'FAQ', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['path' => '/custom-pages', 'title' => 'Custom Pages', 'priority' => '0.5', 'changefreq' => 'weekly'],
            ['path' => '/sitemap', 'title' => 'Sitemap', 'priority' => '0.4', 'changefreq' => 'weekly'],
            ['path' => '/privacy', 'title' => 'Privacy Policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['path' => '/terms', 'title' => 'Terms & Conditions', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['path' => '/refund-policy', 'title' => 'Refund Policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['path' => '/delivery-policy', 'title' => 'Delivery Policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['path' => '/grievnce', 'title' => 'Grievance', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['path' => '/legal', 'title' => 'Legal', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];
    }
}

if (!function_exists('sitemap_classify_custom_page')) {
    function sitemap_classify_custom_page(string $slug): string
    {
        if (preg_match('/^flower-delivery-in-/i', $slug)) {
            return 'location';
        }
        if (preg_match('/fathers-day|mothers-day|valentine|birthday|anniversary|bouquet/i', $slug)) {
            return 'occasion';
        }

        return 'landing';
    }
}

if (!function_exists('get_sitemap_custom_page_entries')) {
    /**
     * Active dynamic_pages + built-in landing pages.
     *
     * @return list<array{title: string, slug: string, path: string, category: string, lastmod: ?string}>
     */
    function get_sitemap_custom_page_entries($conn): array
    {
        require_once __DIR__ . '/landing_pages.php';

        $by_slug = [];

        $rows = sitemap_safe_query(
            $conn,
            'SELECT title, slug, updated_at, created_at FROM dynamic_pages WHERE status = 1 ORDER BY title ASC'
        );
        if (!$rows) {
            $rows = sitemap_safe_query(
                $conn,
                'SELECT title, slug, created_at FROM dynamic_pages WHERE status = 1 ORDER BY title ASC'
            );
        }

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '' || !preg_match('/^[a-z0-9\-]+$/i', $slug)) {
                continue;
            }
            $by_slug[$slug] = [
                'title'    => (string) ($row['title'] ?? $slug),
                'slug'     => $slug,
                'path'     => '/' . $slug,
                'category' => sitemap_classify_custom_page($slug),
                'lastmod'  => sitemap_format_lastmod($row['updated_at'] ?? $row['created_at'] ?? null),
            ];
        }

        try {
            foreach (get_builtin_landing_pages() as $page) {
                $slug = trim($page['slug'] ?? '');
                if ($slug === '' || isset($by_slug[$slug]) || !preg_match('/^[a-z0-9\-]+$/i', $slug)) {
                    continue;
                }
                $by_slug[$slug] = [
                    'title'    => $page['title'],
                    'slug'     => $slug,
                    'path'     => '/' . $slug,
                    'category' => sitemap_classify_custom_page($slug),
                    'lastmod'  => null,
                ];
            }
        } catch (Throwable $e) {
            error_log('Sitemap builtin landing pages failed: ' . $e->getMessage());
        }

        try {
            require_once __DIR__ . '/collection_taxonomy.php';
            foreach (collection_list() as $page) {
                $path = (string) ($page['canonical_path'] ?? '');
                if ($path === '' || isset($by_slug[$path])) {
                    continue;
                }
                $by_slug[$path] = [
                    'title'    => (string) ($page['title'] ?? $path),
                    'slug'     => ltrim($path, '/'),
                    'path'     => $path,
                    'category' => 'landing',
                    'lastmod'  => null,
                ];
            }
        } catch (Throwable $e) {
            error_log('Sitemap collection landings failed: ' . $e->getMessage());
        }

        $pages = array_values($by_slug);
        usort($pages, static function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });

        return $pages;
    }
}

if (!function_exists('get_sitemap_product_entries')) {
    /**
     * @return list<array{title: string, path: string, type: string, lastmod: ?string}>
     */
    function get_sitemap_product_entries($conn, string $type, string $table): array
    {
        $entries = [];
        $allowed = ['flowers', 'cakes', 'gifts'];
        if (!in_array($table, $allowed, true)) {
            return $entries;
        }

        $rows = sitemap_safe_query(
            $conn,
            "SELECT id, name, slug, created_at FROM {$table} WHERE status = 1 ORDER BY name ASC"
        );

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            $path = product_url([
                'type' => $type,
                'slug' => $slug,
                'id'   => (int) ($row['id'] ?? 0),
            ]);
            $path = sitemap_normalize_path($path);
            if (!sitemap_is_indexable_path($path)) {
                continue;
            }
            $entries[] = [
                'title'   => (string) ($row['name'] ?? $slug),
                'path'    => $path,
                'type'    => $type,
                'lastmod' => sitemap_format_lastmod($row['created_at'] ?? null),
            ];
        }

        return $entries;
    }
}

if (!function_exists('get_sitemap_blog_entries')) {
    /**
     * @return list<array{title: string, path: string, lastmod: ?string}>
     */
    function get_sitemap_blog_entries($conn): array
    {
        $entries = [];
        $rows = sitemap_safe_query(
            $conn,
            'SELECT id, title, slug, created_at FROM blogs WHERE status = 1 ORDER BY created_at DESC'
        );

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug !== '' && preg_match('/^[a-z0-9\-]+$/i', $slug)) {
                $path = '/blog/' . $slug;
            } else {
                $path = '/blog-detail?id=' . (int) ($row['id'] ?? 0);
            }
            if (!sitemap_is_indexable_path($path)) {
                continue;
            }
            $entries[] = [
                'title'   => (string) ($row['title'] ?? 'Blog'),
                'path'    => $path,
                'lastmod' => sitemap_format_lastmod($row['created_at'] ?? null),
            ];
        }

        return $entries;
    }
}

if (!function_exists('get_sitemap_event_entries')) {
    /**
     * @return list<array{title: string, path: string, lastmod: ?string}>
     */
    function get_sitemap_event_entries($conn): array
    {
        $entries = [];
        $rows = sitemap_safe_query(
            $conn,
            'SELECT id, title, slug, created_at FROM events WHERE status = 1 ORDER BY title ASC'
        );

        foreach ($rows as $row) {
            $path = product_url([
                'type' => 'event',
                'slug' => trim((string) ($row['slug'] ?? '')),
                'id'   => (int) ($row['id'] ?? 0),
            ]);
            $path = sitemap_normalize_path($path);
            if (!sitemap_is_indexable_path($path)) {
                continue;
            }
            $entries[] = [
                'title'   => (string) ($row['title'] ?? 'Event'),
                'path'    => $path,
                'lastmod' => sitemap_format_lastmod($row['created_at'] ?? null),
            ];
        }

        return $entries;
    }
}

if (!function_exists('get_sitemap_gallery_entries')) {
    /**
     * @return list<array{title: string, path: string, lastmod: ?string}>
     */
    function get_sitemap_gallery_entries($conn): array
    {
        $entries = [];
        $rows = sitemap_safe_query(
            $conn,
            'SELECT id, title, created_at FROM gallery WHERE status = 1 ORDER BY id DESC'
        );

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $path = '/gallery-detail?id=' . $id;
            $entries[] = [
                'title'   => (string) ($row['title'] ?? 'Gallery ' . $id),
                'path'    => $path,
                'lastmod' => sitemap_format_lastmod($row['created_at'] ?? null),
            ];
        }

        return $entries;
    }
}

if (!function_exists('sitemap_collect_url_entries')) {
    /**
     * Build a deduplicated list of sitemap URL entries.
     *
     * @return list<array{path: string, priority: string, changefreq: string, lastmod: ?string}>
     */
    function sitemap_collect_url_entries($conn): array
    {
        /** @var array<string, array{path: string, priority: string, changefreq: string, lastmod: ?string}> $by_path */
        $by_path = [];

        $add = static function (string $path, string $priority, string $changefreq, ?string $lastmod = null) use (&$by_path): void {
            $path = sitemap_normalize_path($path);
            if (!sitemap_is_indexable_path($path)) {
                return;
            }
            if (isset($by_path[$path])) {
                // Keep stronger priority / newer lastmod on duplicates
                $existing = $by_path[$path];
                if ((float) $priority > (float) $existing['priority']) {
                    $existing['priority'] = $priority;
                    $existing['changefreq'] = $changefreq;
                }
                if ($lastmod && (!$existing['lastmod'] || $lastmod > $existing['lastmod'])) {
                    $existing['lastmod'] = $lastmod;
                }
                $by_path[$path] = $existing;
                return;
            }
            $by_path[$path] = [
                'path'       => $path,
                'priority'   => $priority,
                'changefreq' => $changefreq,
                'lastmod'    => $lastmod,
            ];
        };

        try {
            foreach (get_sitemap_static_entries() as $page) {
                $add($page['path'], $page['priority'], $page['changefreq']);
            }
        } catch (Throwable $e) {
            error_log('Sitemap static entries failed: ' . $e->getMessage());
        }

        try {
            foreach (get_sitemap_custom_page_entries($conn) as $page) {
                $add($page['path'], '0.7', 'weekly', $page['lastmod'] ?? null);
            }
        } catch (Throwable $e) {
            error_log('Sitemap custom pages failed: ' . $e->getMessage());
        }

        try {
            foreach (get_sitemap_product_entries($conn, 'flower', 'flowers') as $item) {
                $add($item['path'], '0.7', 'weekly', $item['lastmod'] ?? null);
            }
            foreach (get_sitemap_product_entries($conn, 'cake', 'cakes') as $item) {
                $add($item['path'], '0.7', 'weekly', $item['lastmod'] ?? null);
            }
            foreach (get_sitemap_product_entries($conn, 'gift', 'gifts') as $item) {
                $add($item['path'], '0.7', 'weekly', $item['lastmod'] ?? null);
            }
        } catch (Throwable $e) {
            error_log('Sitemap products failed: ' . $e->getMessage());
        }

        try {
            foreach (get_sitemap_event_entries($conn) as $item) {
                $add($item['path'], '0.6', 'weekly', $item['lastmod'] ?? null);
            }
        } catch (Throwable $e) {
            error_log('Sitemap events failed: ' . $e->getMessage());
        }

        try {
            foreach (get_sitemap_blog_entries($conn) as $item) {
                $add($item['path'], '0.6', 'monthly', $item['lastmod'] ?? null);
            }
        } catch (Throwable $e) {
            error_log('Sitemap blogs failed: ' . $e->getMessage());
        }

        try {
            foreach (get_sitemap_gallery_entries($conn) as $item) {
                $add($item['path'], '0.5', 'monthly', $item['lastmod'] ?? null);
            }
        } catch (Throwable $e) {
            error_log('Sitemap gallery failed: ' . $e->getMessage());
        }

        return array_values($by_path);
    }
}

if (!function_exists('build_sitemap_xml_string')) {
    /**
     * Build a complete valid sitemap XML document.
     */
    function build_sitemap_xml_string($conn): string
    {
        $entries = sitemap_collect_url_entries($conn);
        $base = sitemap_base_url();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($entries as $entry) {
            $loc = $base . $entry['path'];
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . sitemap_xml_escape($loc) . "</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= '    <lastmod>' . sitemap_xml_escape($entry['lastmod']) . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . sitemap_xml_escape($entry['changefreq']) . "</changefreq>\n";
            $xml .= '    <priority>' . sitemap_xml_escape($entry['priority']) . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}

if (!function_exists('render_sitemap_xml_url')) {
    /** @deprecated Prefer build_sitemap_xml_string(); kept for compatibility. */
    function render_sitemap_xml_url(string $path, string $priority = '0.5', string $changefreq = 'weekly', ?string $lastmod = null): void
    {
        $loc = sitemap_base_url() . sitemap_normalize_path($path);
        echo '<url>';
        echo '<loc>' . sitemap_xml_escape($loc) . '</loc>';
        if ($lastmod) {
            echo '<lastmod>' . sitemap_xml_escape($lastmod) . '</lastmod>';
        }
        echo '<changefreq>' . sitemap_xml_escape($changefreq) . '</changefreq>';
        echo '<priority>' . sitemap_xml_escape($priority) . '</priority>';
        echo '</url>';
    }
}

if (!function_exists('render_sitemap_xml')) {
    function render_sitemap_xml($conn): void
    {
        try {
            $xml = build_sitemap_xml_string($conn);
        } catch (Throwable $e) {
            error_log('Sitemap build failed: ' . $e->getMessage());
            // Minimal valid fallback so GSC never gets truncated XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
                . '  <url><loc>' . sitemap_xml_escape(sitemap_base_url() . '/') . '</loc>'
                . '<changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n"
                . '</urlset>';
        }

        if (!headers_sent()) {
            header('Content-Type: application/xml; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: public, max-age=3600');
            header('Content-Length: ' . strlen($xml));
        }

        echo $xml;
    }
}

if (!function_exists('sitemap_group_custom_pages')) {
    /**
     * @param list<array{title: string, slug: string, path: string, category: string}> $pages
     * @return array<string, list<array{title: string, path: string}>>
     */
    function sitemap_group_custom_pages(array $pages): array
    {
        $groups = [
            'location' => [],
            'occasion' => [],
            'landing'  => [],
        ];

        foreach ($pages as $page) {
            $cat = $page['category'] ?? 'landing';
            if (!isset($groups[$cat])) {
                $cat = 'landing';
            }
            $groups[$cat][] = [
                'title' => $page['title'],
                'path'  => $page['path'],
            ];
        }

        return $groups;
    }
}

if (!function_exists('sitemap_render_link_list')) {
    /**
     * @param list<array{title: string, path: string}> $items
     */
    function sitemap_render_link_list(array $items): void
    {
        if (count($items) === 0) {
            echo '<p class="text-slate-400 text-sm">No pages in this section yet.</p>';
            return;
        }
        foreach ($items as $item) {
            echo '<a href="' . htmlspecialchars($item['path']) . '" class="sitemap-link">'
                . htmlspecialchars($item['title']) . '</a>';
        }
    }
}
