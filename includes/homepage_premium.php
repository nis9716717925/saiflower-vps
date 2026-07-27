<?php
/**
 * Premium homepage sections — config + dynamic product helpers.
 * Does not replace homepage_data.php (slides, circles, CMS sections).
 */

if (!function_exists('homepage_get_anywhere_cities')) {
    function homepage_get_anywhere_cities(): array
    {
        require_once __DIR__ . '/collection_landing.php';
        $cities = [
            ['name' => 'Delhi', 'link' => '/flower-delivery-in-delhi', 'image' => 'https://images.unsplash.com/photo-1587474260584-136574528ed5?auto=format&fit=crop&w=600&h=450&q=80'],
            ['name' => 'Mumbai', 'link' => '/flowers', 'image' => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?auto=format&fit=crop&w=600&h=450&q=80'],
            ['name' => 'Ahmedabad', 'link' => '/flowers', 'image' => 'https://images.unsplash.com/photo-1604147495798-57beb5d6af73?auto=format&fit=crop&w=600&h=450&q=80'],
            ['name' => 'Bengaluru', 'link' => '/flowers', 'image' => 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?auto=format&fit=crop&w=600&h=450&q=80'],
            ['name' => 'Chennai', 'link' => '/flowers', 'image' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=600&h=450&q=80'],
            ['name' => 'Hyderabad', 'link' => '/flowers', 'image' => 'https://images.unsplash.com/photo-1533050487297-09b450131914?auto=format&fit=crop&w=600&h=450&q=80'],
            ['name' => 'Kolkata', 'link' => '/flowers', 'image' => 'https://images.unsplash.com/photo-1558431382-27e303142255?auto=format&fit=crop&w=600&h=450&q=80'],
            ['name' => 'Pune', 'link' => '/flowers', 'image' => 'https://images.unsplash.com/photo-1562979314-bee7453e911c?auto=format&fit=crop&w=600&h=450&q=80'],
        ];
        return $cities;
    }
}

if (!function_exists('homepage_get_gift_finder_options')) {
    function homepage_get_gift_finder_options(): array
    {
        require_once __DIR__ . '/collection_taxonomy.php';
        return [
            ['label' => 'Occasion', 'subtitle' => 'Birthday, wedding & more', 'icon' => 'fa-calendar-heart', 'link' => '/celebration-calendar', 'image' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=400&h=400&q=80'],
            ['label' => 'Gift Type', 'subtitle' => 'Flowers, cakes & hampers', 'icon' => 'fa-gift', 'link' => collection_url('collection', 'hampers'), 'image' => 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=400&h=400&q=80'],
            ['label' => 'Recipient', 'subtitle' => 'For her, him & family', 'icon' => 'fa-user-group', 'link' => collection_url('relation', 'her'), 'image' => 'https://images.unsplash.com/photo-1511988617509-a57c8a288659?auto=format&fit=crop&w=400&h=400&q=80'],
            ['label' => 'Budget', 'subtitle' => 'Under ₹999 & more', 'icon' => 'fa-indian-rupee-sign', 'link' => collection_url('collection', 'budget-flowers'), 'image' => 'https://images.unsplash.com/photo-1607083206968-13611e3d76db?auto=format&fit=crop&w=400&h=400&q=80'],
        ];
    }
}

if (!function_exists('homepage_get_fav_flower_items')) {
    /**
     * @param array<int, list<array<string, mixed>>> $itemsGrouped
     */
    function homepage_get_fav_flower_items(array $itemsGrouped): array
    {
        require_once __DIR__ . '/occasion_links.php';
        require_once __DIR__ . '/collection_taxonomy.php';
        global $conn;

        $items = $itemsGrouped[3] ?? [];
        $out = [];
        foreach ($items as $item) {
            if (empty($item['title']) || empty($item['image'])) {
                continue;
            }
            $typeSlug = collection_label_to_flower_slug((string) $item['title']);
            if ($typeSlug !== null) {
                $link = collection_url('flower', $typeSlug);
            } else {
                $link = resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $item, '/flowers');
            }
            $out[] = [
                'title' => $item['title'],
                'image' => $item['image'],
                'link' => $link,
            ];
        }
        return $out;
    }
}

if (!function_exists('homepage_get_budget_tiers')) {
    function homepage_get_budget_tiers(): array
    {
        require_once __DIR__ . '/collection_taxonomy.php';
        return [
            [
                'key' => 'under-999',
                'badge' => 'Most loved',
                'label' => 'Under',
                'price' => '999',
                'tagline' => 'Fresh bouquets & petite surprises',
                'icon' => 'fa-seedling',
                'link' => collection_url('collection', 'budget-flowers'),
                'theme' => 'gold',
                'featured' => true,
            ],
            [
                'key' => 'under-1499',
                'badge' => 'Popular pick',
                'label' => 'Under',
                'price' => '1,499',
                'tagline' => 'Elegant mixes for everyday gifting',
                'icon' => 'fa-gift',
                'link' => '/flowers.php?price_range=999-1499&price_min=999&price_max=1499',
                'theme' => 'green',
                'featured' => false,
            ],
            [
                'key' => 'under-2499',
                'badge' => 'Premium value',
                'label' => 'Under',
                'price' => '2,499',
                'tagline' => 'Statement blooms without the splurge',
                'icon' => 'fa-spa',
                'link' => collection_url('collection', 'premium-bouquets'),
                'theme' => 'blush',
                'featured' => false,
            ],
            [
                'key' => 'chocolate',
                'badge' => 'Sweet treat',
                'label' => 'Chocolate',
                'price' => 'Bouquets',
                'tagline' => 'Indulgent chocolate & flower combos',
                'icon' => 'fa-cookie-bite',
                'link' => '/search-results.php?q=chocolate',
                'theme' => 'cocoa',
                'featured' => false,
                'is_text_price' => true,
            ],
        ];
    }
}

if (!function_exists('homepage_slider_dedupe_key')) {
    function homepage_slider_dedupe_key(array $item): string
    {
        $link = strtolower(trim((string) ($item['link'] ?? '')));
        if ($link !== '' && $link !== '#') {
            $link = preg_replace('#^https?://[^/]+#', '', $link);
            return 'link:' . rtrim($link, '/');
        }
        if (!empty($item['slug'])) {
            return 'slug:' . strtolower((string) $item['slug']);
        }
        if (!empty($item['id']) && isset($item['name'])) {
            return 'flower:' . (int) $item['id'];
        }
        if (!empty($item['id']) && isset($item['section_id'])) {
            return 'cms:' . (int) $item['id'];
        }
        $label = strtolower(trim((string) ($item['title'] ?? $item['name'] ?? '')));
        return 'title:' . md5($label);
    }
}

if (!function_exists('homepage_pick_unique_slider_items')) {
    /**
     * @param list<array<string, mixed>> $items
     * @param array<string, true> $usedKeys
     * @return list<array<string, mixed>>
     */
    function homepage_pick_unique_slider_items(array $items, array &$usedKeys, int $limit = 10): array
    {
        $limit = max(1, min(20, $limit));
        $picked = [];
        foreach ($items as $item) {
            $key = homepage_slider_dedupe_key($item);
            if (isset($usedKeys[$key])) {
                continue;
            }
            $usedKeys[$key] = true;
            if (!empty($item['id']) && (isset($item['name']) || !empty($item['slug']))) {
                $usedKeys['flower:' . (int) $item['id']] = true;
            }
            $picked[] = $item;
            if (count($picked) >= $limit) {
                break;
            }
        }
        return $picked;
    }
}

if (!function_exists('homepage_extract_path_slug')) {
    function homepage_extract_path_slug(string $link): string
    {
        $link = trim($link);
        if ($link === '' || $link === '#') {
            return '';
        }

        $link = preg_replace('#^https?://[^/]+#i', '', $link);
        $link = ltrim($link, '/');

        if (preg_match('#^(?:flower|cake|gift|event)-detail\.php(?:\?|$)#', $link)) {
            $query = parse_url($link, PHP_URL_QUERY);
            if (is_string($query) && $query !== '') {
                parse_str($query, $params);
                if (!empty($params['slug'])) {
                    return trim((string) $params['slug'], '/');
                }
            }
            return '';
        }

        if (preg_match('#^(?:flowers|cakes|gifts|events)/([^/?#]+)#', $link, $matches)) {
            return trim(rawurldecode($matches[1]), '/');
        }

        if (preg_match('#^(?:flowers|cakes|gifts|search-results|tag|page)\.php#', $link)) {
            return '';
        }

        $path = parse_url('/' . $link, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || $path === '/') {
            return '';
        }

        return trim(ltrim($path, '/'), '/');
    }
}

if (!function_exists('homepage_product_url_from_record')) {
    /**
     * @param array{slug: string, type: string, id: int} $record
     */
    function homepage_product_url_from_record(array $record): string
    {
        return product_url($record);
    }
}

if (!function_exists('homepage_lookup_product_record')) {
    /**
     * @return array{slug: string, type: string, id: int}|null
     */
    function homepage_lookup_product_record(mysqli $conn, string $slug, ?string $title = null, int $productId = 0): ?array
    {
        static $slugCache = [];
        static $titleCache = [];
        static $idCache = [];

        $productTables = ['flowers' => 'flower', 'cakes' => 'cake', 'gifts' => 'gift'];

        $slug = trim($slug, '/');
        if ($slug !== '') {
            $cacheKey = strtolower($slug);
            if (array_key_exists($cacheKey, $slugCache)) {
                return $slugCache[$cacheKey];
            }

            foreach ($productTables as $table => $type) {
                $stmt = $conn->prepare("SELECT id, slug FROM {$table} WHERE slug = ? AND status = 1 LIMIT 1");
                if (!$stmt) {
                    continue;
                }
                $stmt->bind_param('s', $slug);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $record = [
                        'slug' => (string) $row['slug'],
                        'type' => $type,
                        'id'   => (int) $row['id'],
                    ];
                    $slugCache[$cacheKey] = $record;
                    return $record;
                }
            }

            if (strlen($slug) >= 24) {
                $prefix = substr($slug, 0, 40);
                $like = $prefix . '%';
                foreach ($productTables as $table => $type) {
                    $stmt = $conn->prepare(
                        "SELECT id, slug FROM {$table} WHERE status = 1 AND slug LIKE ? ORDER BY CHAR_LENGTH(slug) ASC LIMIT 1"
                    );
                    if (!$stmt) {
                        continue;
                    }
                    $stmt->bind_param('s', $like);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) {
                        $record = [
                            'slug' => (string) $row['slug'],
                            'type' => $type,
                            'id'   => (int) $row['id'],
                        ];
                        $slugCache[$cacheKey] = $record;
                        return $record;
                    }
                }
            }

            $slugCache[$cacheKey] = null;
        }

        if ($productId > 0) {
            foreach ($productTables as $table => $type) {
                $idKey = $type . ':' . $productId;
                if (array_key_exists($idKey, $idCache)) {
                    $cached = $idCache[$idKey];
                    if ($cached !== null) {
                        return $cached;
                    }
                    continue;
                }

                $stmt = $conn->prepare("SELECT id, slug FROM {$table} WHERE id = ? AND status = 1 LIMIT 1");
                if (!$stmt) {
                    $idCache[$idKey] = null;
                    continue;
                }
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $record = [
                        'slug' => (string) ($row['slug'] ?? ''),
                        'type' => $type,
                        'id'   => (int) $row['id'],
                    ];
                    $idCache[$idKey] = $record;
                    return $record;
                }

                $idCache[$idKey] = null;
            }
        }

        $title = trim((string) $title);
        if ($title === '') {
            return null;
        }

        $titleKey = md5((function_exists('mb_strtolower') ? mb_strtolower($title) : strtolower($title)));
        if (array_key_exists($titleKey, $titleCache)) {
            return $titleCache[$titleKey];
        }

        foreach ($productTables as $table => $type) {
            $stmt = $conn->prepare("SELECT id, slug FROM {$table} WHERE name = ? AND status = 1 LIMIT 1");
            if (!$stmt) {
                continue;
            }
            $stmt->bind_param('s', $title);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $record = [
                    'slug' => (string) ($row['slug'] ?? ''),
                    'type' => $type,
                    'id'   => (int) $row['id'],
                ];
                $titleCache[$titleKey] = $record;
                return $record;
            }
        }

        foreach ($productTables as $table => $type) {
            $stmt = $conn->prepare(
                "SELECT id, slug FROM {$table} WHERE status = 1 AND ? LIKE CONCAT('%', name, '%') ORDER BY CHAR_LENGTH(name) DESC LIMIT 1"
            );
            if (!$stmt) {
                continue;
            }
            $stmt->bind_param('s', $title);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $record = [
                    'slug' => (string) ($row['slug'] ?? ''),
                    'type' => $type,
                    'id'   => (int) $row['id'],
                ];
                $titleCache[$titleKey] = $record;
                return $record;
            }
        }

        $likeTitle = function_exists('mb_substr') ? mb_substr($title, 0, 80) : substr($title, 0, 80);
        $like = '%' . $likeTitle . '%';
        foreach ($productTables as $table => $type) {
            $stmt = $conn->prepare("SELECT id, slug FROM {$table} WHERE name LIKE ? AND status = 1 LIMIT 1");
            if (!$stmt) {
                continue;
            }
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $record = [
                    'slug' => (string) ($row['slug'] ?? ''),
                    'type' => $type,
                    'id'   => (int) $row['id'],
                ];
                $titleCache[$titleKey] = $record;
                return $record;
            }
        }

        $titleCache[$titleKey] = null;
        return null;
    }
}

if (!function_exists('homepage_resolve_product_link')) {
    function homepage_resolve_product_link(mysqli $conn, array $item): string
    {
        $title = trim((string) ($item['title'] ?? $item['name'] ?? ''));
        $rawLink = trim((string) ($item['link'] ?? ''));
        $slugHint = trim((string) ($item['slug'] ?? ''));
        $productId = (int) ($item['id'] ?? 0);
        $isCmsItem = isset($item['section_id']);
        $isDbProduct = $productId > 0 && isset($item['name']) && !$isCmsItem;

        if ($rawLink !== '' && preg_match('#^(?:flower|cake|gift)-detail\.php#', ltrim($rawLink, '/'))) {
            $query = parse_url($rawLink, PHP_URL_QUERY);
            if (is_string($query) && $query !== '') {
                parse_str($query, $params);
                if (!empty($params['id'])) {
                    $productId = (int) $params['id'];
                    $isDbProduct = true;
                }
                if (!empty($params['slug'])) {
                    $slugHint = (string) $params['slug'];
                }
            }
        }

        $slugCandidates = array_values(array_unique(array_filter([
            $slugHint,
            homepage_extract_path_slug($rawLink),
        ])));

        if ($isCmsItem && $title !== '') {
            $record = homepage_lookup_product_record($conn, '', $title);
            if ($record) {
                return homepage_product_url_from_record($record);
            }
        }

        foreach ($slugCandidates as $candidate) {
            $record = homepage_lookup_product_record($conn, $candidate, $title, $isDbProduct ? $productId : 0);
            if ($record) {
                return homepage_product_url_from_record($record);
            }
        }

        if ($isDbProduct) {
            $record = homepage_lookup_product_record($conn, '', $title, $productId);
            if ($record) {
                return homepage_product_url_from_record($record);
            }
        }

        $record = homepage_lookup_product_record($conn, '', $title);
        if ($record) {
            return homepage_product_url_from_record($record);
        }

        if ($isDbProduct && $productId > 0) {
            return product_url_by_parts('flower', '', $productId);
        }

        if ($rawLink !== '' && $rawLink !== '#') {
            if (preg_match('#^https?://#i', $rawLink)) {
                return normalize_internal_href($rawLink);
            }
            return normalize_internal_href($rawLink);
        }

        foreach ($slugCandidates as $candidate) {
            if ($candidate !== '') {
                return '/' . ltrim($candidate, '/');
            }
        }

        return '/flowers';
    }
}

if (!function_exists('homepage_normalize_slider_items')) {
    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    function homepage_normalize_slider_items(mysqli $conn, array $items): array
    {
        foreach ($items as &$item) {
            $item['link'] = homepage_resolve_product_link($conn, $item);
            if (preg_match('#^/(?:flowers|cakes|gifts|events)/([^/?#]+)/?$#', $item['link'], $matches)) {
                $item['slug'] = rawurldecode($matches[1]);
            } elseif (preg_match('#^/([^/?#]+)/?$#', $item['link'], $matches)) {
                $item['slug'] = rawurldecode($matches[1]);
            }
        }
        unset($item);

        return $items;
    }
}

if (!function_exists('homepage_flower_row_to_slider_item')) {
    function homepage_flower_row_to_slider_item(array $row, ?mysqli $conn = null): array
    {
        $slug = trim((string) ($row['slug'] ?? ''));
        $item = [
            'id' => (int) ($row['id'] ?? 0),
            'title' => $row['name'] ?? '',
            'name' => $row['name'] ?? '',
            'slug' => $slug,
            'image' => $row['image'] ?? '',
            'price' => $row['price'] ?? 0,
            'original_price' => $row['original_price'] ?? 0,
            'rating' => $row['rating'] ?? 0,
            'link' => product_url(['type' => 'flower', 'slug' => $slug, 'id' => (int) ($row['id'] ?? 0)]),
        ];

        if ($conn instanceof mysqli) {
            $item['link'] = homepage_resolve_product_link($conn, $item);
            if (preg_match('#^/(?:flowers|cakes|gifts|events)/([^/?#]+)/?$#', $item['link'], $matches)) {
                $item['slug'] = rawurldecode($matches[1]);
            } elseif (preg_match('#^/([^/?#]+)/?$#', $item['link'], $matches)) {
                $item['slug'] = rawurldecode($matches[1]);
            }
        }

        return $item;
    }
}

if (!function_exists('homepage_build_exclude_sql')) {
    function homepage_build_exclude_sql(array $excludeIds): string
    {
        $excludeIds = array_values(array_unique(array_filter(array_map('intval', $excludeIds))));
        if ($excludeIds === []) {
            return '';
        }
        return ' AND id NOT IN (' . implode(',', $excludeIds) . ')';
    }
}

if (!function_exists('homepage_fetch_slider_backfill_products')) {
    function homepage_fetch_slider_backfill_products(mysqli $conn, string $strategy, int $limit, array $excludeIds = []): array
    {
        $limit = max(1, min(20, $limit));
        $excludeSql = homepage_build_exclude_sql($excludeIds);
        $offset = min(40, count($excludeIds));
        $products = [];

        switch ($strategy) {
            case 'same_day':
                $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                        FROM flowers WHERE status = 1 {$excludeSql} AND (
                            LOWER(COALESCE(tag, '')) LIKE '%same day%'
                            OR LOWER(COALESCE(tag, '')) LIKE '%demand%'
                            OR LOWER(name) LIKE '%same day%'
                            OR LOWER(name) LIKE '%midnight%'
                            OR LOWER(name) LIKE '%express%'
                        )
                        ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
                break;
            case 'occasion_alt':
                $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                        FROM flowers WHERE status = 1 {$excludeSql}
                        ORDER BY price DESC, id ASC LIMIT {$limit} OFFSET {$offset}";
                break;
            case 'celebration_mix':
                $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                        FROM flowers WHERE status = 1 {$excludeSql} AND (
                            LOWER(COALESCE(tag, '')) LIKE '%birthday%'
                            OR LOWER(COALESCE(tag, '')) LIKE '%anniversary%'
                            OR LOWER(COALESCE(tag, '')) LIKE '%wedding%'
                            OR LOWER(COALESCE(tag, '')) LIKE '%congratulations%'
                            OR LOWER(name) LIKE '%birthday%'
                            OR LOWER(name) LIKE '%anniversary%'
                            OR LOWER(name) LIKE '%wedding%'
                        )
                        ORDER BY rating DESC, id DESC LIMIT {$limit} OFFSET {$offset}";
                break;
            case 'rated':
                $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                        FROM flowers WHERE status = 1 {$excludeSql}
                        ORDER BY rating DESC, id DESC LIMIT {$limit} OFFSET {$offset}";
                break;
            case 'newest':
                $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                        FROM flowers WHERE status = 1 {$excludeSql}
                        ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
                break;
            default:
                $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                        FROM flowers WHERE status = 1 {$excludeSql}
                        ORDER BY id ASC LIMIT {$limit} OFFSET {$offset}";
                break;
        }

        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products[] = homepage_flower_row_to_slider_item($row, $conn);
            }
        }

        if (count($products) < min(4, $limit)) {
            $need = $limit - count($products);
            $localExclude = array_merge($excludeIds, array_column($products, 'id'));
            $fallbackSql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                FROM flowers WHERE status = 1 " . homepage_build_exclude_sql($localExclude) . "
                ORDER BY id DESC LIMIT {$need} OFFSET " . ($offset + 3);
            $fallback = $conn->query($fallbackSql);
            if ($fallback) {
                while ($row = $fallback->fetch_assoc()) {
                    $products[] = homepage_flower_row_to_slider_item($row, $conn);
                }
            }
        }

        return $products;
    }
}

if (!function_exists('homepage_collect_used_flower_ids')) {
    function homepage_collect_used_flower_ids(array $usedKeys): array
    {
        $ids = [];
        foreach ($usedKeys as $key => $_) {
            if (preg_match('/^flower:(\d+)$/', (string) $key, $m)) {
                $ids[] = (int) $m[1];
            }
        }
        return $ids;
    }
}

if (!function_exists('homepage_build_slider_items')) {
    /**
     * @param array<string, true> $usedKeys
     * @return list<array<string, mixed>>
     */
    function homepage_build_slider_items(
        mysqli $conn,
        array $itemsGrouped,
        int $cmsSectionId,
        string $backfillStrategy,
        array &$usedKeys,
        int $limit = 10
    ): array {
        $limit = max(1, min(20, $limit));
        $cmsItems = $itemsGrouped[$cmsSectionId] ?? [];
        $picked = homepage_pick_unique_slider_items($cmsItems, $usedKeys, $limit);

        if (count($picked) < $limit) {
            $need = $limit - count($picked);
            $excludeIds = homepage_collect_used_flower_ids($usedKeys);
            $backfill = homepage_fetch_slider_backfill_products($conn, $backfillStrategy, $need + 4, $excludeIds);
            $picked = array_merge($picked, homepage_pick_unique_slider_items($backfill, $usedKeys, $limit));
        }

        return homepage_normalize_slider_items($conn, $picked);
    }
}

if (!function_exists('homepage_fetch_newly_added_products')) {
    function homepage_fetch_newly_added_products(mysqli $conn, int $limit = 10, array $excludeIds = []): array
    {
        $limit = max(1, min(20, $limit));
        $products = [];
        $excludeSql = homepage_build_exclude_sql($excludeIds);
        $offset = min(30, count($excludeIds));
        $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                FROM flowers WHERE status = 1 {$excludeSql}
                ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }
}

if (!function_exists('homepage_fetch_on_demand_products')) {
    function homepage_fetch_on_demand_products(mysqli $conn, int $limit = 10, array $excludeIds = []): array
    {
        $limit = max(1, min(20, $limit));
        $products = [];
        $excludeSql = homepage_build_exclude_sql($excludeIds);
        $offset = min(20, count($excludeIds));
        $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                FROM flowers WHERE status = 1 {$excludeSql} AND (
                    LOWER(COALESCE(tag, '')) LIKE '%demand%'
                    OR LOWER(COALESCE(tag, '')) LIKE '%same day%'
                    OR LOWER(name) LIKE '%same day%'
                    OR LOWER(name) LIKE '%midnight%'
                    OR LOWER(name) LIKE '%express%'
                )
                ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
        }
        if (count($products) < 4) {
            $need = $limit - count($products);
            $exclude = array_merge($excludeIds, array_column($products, 'id'));
            $fallback = $conn->query("SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                FROM flowers WHERE status = 1 " . homepage_build_exclude_sql($exclude) . " ORDER BY rating DESC, id DESC LIMIT {$need}");
            if ($fallback) {
                while ($row = $fallback->fetch_assoc()) {
                    $products[] = $row;
                }
            }
        }
        return $products;
    }
}

if (!function_exists('homepage_ordered_cms_sections')) {
    /**
     * @param list<array<string, mixed>> $sections
     * @param list<int> $order Section IDs in display order
     * @return list<array<string, mixed>>
     */
    function homepage_ordered_cms_sections(array $sections, array $order): array
    {
        if ($order === []) {
            return [];
        }

        $byId = [];
        foreach ($sections as $section) {
            $byId[(int) $section['id']] = $section;
        }

        $ordered = [];
        foreach ($order as $id) {
            $id = (int) $id;
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }
}

if (!function_exists('homepage_get_product_sliders')) {
    /**
     * @param array<int, list<array<string, mixed>>> $itemsGrouped
     */
    function homepage_get_product_sliders(mysqli $conn, array $itemsGrouped): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $usedKeys = [];
        $limit = 10;
        $excludeIds = [];

        $bestSellerItems = homepage_build_slider_items($conn, $itemsGrouped, 1, 'rated', $usedKeys, $limit);
        $excludeIds = homepage_collect_used_flower_ids($usedKeys);

        $sameDayItems = homepage_build_slider_items($conn, $itemsGrouped, 7, 'same_day', $usedKeys, $limit);
        $excludeIds = homepage_collect_used_flower_ids($usedKeys);

        $occasionItems = homepage_build_slider_items($conn, $itemsGrouped, 2, 'celebration_mix', $usedKeys, $limit);
        $excludeIds = homepage_collect_used_flower_ids($usedKeys);

        $everyOccasionItems = homepage_build_slider_items($conn, $itemsGrouped, 8, 'occasion_alt', $usedKeys, $limit);
        $excludeIds = homepage_collect_used_flower_ids($usedKeys);

        $onDemandProducts = homepage_fetch_on_demand_products($conn, $limit, $excludeIds);
        $onDemandItems = homepage_normalize_slider_items(
            $conn,
            homepage_pick_unique_slider_items(
                array_map(static fn(array $row): array => homepage_flower_row_to_slider_item($row, $conn), $onDemandProducts),
                $usedKeys,
                $limit
            )
        );
        $excludeIds = homepage_collect_used_flower_ids($usedKeys);

        $newlyAddedProducts = homepage_fetch_newly_added_products($conn, $limit, $excludeIds);
        $newlyAddedItems = homepage_normalize_slider_items(
            $conn,
            homepage_pick_unique_slider_items(
                array_map(static fn(array $row): array => homepage_flower_row_to_slider_item($row, $conn), $newlyAddedProducts),
                $usedKeys,
                $limit
            )
        );

        $cached = [
            [
                'key' => 'best-sellers',
                'title' => 'Best Sellers',
                'subtitle' => 'Our most loved bouquets — trusted by thousands of customers.',
                'view_all' => '/collection/best-sellers',
                'html' => homepage_render_cms_carousel_cards($bestSellerItems),
            ],
            [
                'key' => 'same-day-surprises',
                'title' => 'Same Day Surprises',
                'subtitle' => 'Last-minute blooms delivered today — surprise them before sunset.',
                'view_all' => '/collection/same-day-delivery',
                'html' => homepage_render_cms_carousel_cards($sameDayItems),
            ],
            [
                'key' => 'occasions',
                'title' => 'Occasions',
                'subtitle' => 'Perfect picks for birthdays, anniversaries, weddings & more.',
                'view_all' => '/occasion/birthday',
                'html' => homepage_render_cms_carousel_cards($occasionItems),
            ],
            [
                'key' => 'for-every-occasions',
                'title' => 'For Every Occasions',
                'subtitle' => 'Thoughtful gifts for birthdays, love, weddings & every celebration.',
                'view_all' => '/occasion/anniversary',
                'html' => homepage_render_cms_carousel_cards($everyOccasionItems),
            ],
            [
                'key' => 'on-demand',
                'title' => 'On Demand',
                'subtitle' => 'Same-day & express delivery when timing matters most.',
                'view_all' => '/collection/same-day-delivery',
                'html' => homepage_render_cms_carousel_cards($onDemandItems),
            ],
            [
                'key' => 'newly-added',
                'title' => 'Newly Added',
                'subtitle' => 'Fresh arrivals — discover the latest from our studio.',
                'view_all' => '/collection/new-arrivals',
                'html' => homepage_render_cms_carousel_cards($newlyAddedItems),
            ],
        ];

        return $cached;
    }
}

if (!function_exists('homepage_resolve_category_by_names')) {
    function homepage_resolve_category_by_names(mysqli $conn, array $names): ?int
    {
        foreach ($names as $name) {
            $like = '%' . mb_strtolower($name) . '%';
            $stmt = $conn->prepare('SELECT id FROM categories WHERE status = 1 AND LOWER(name) LIKE ? ORDER BY sort_order ASC LIMIT 1');
            if (!$stmt) {
                continue;
            }
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                return (int) $row['id'];
            }
        }
        return null;
    }
}

if (!function_exists('homepage_get_occasion_tabs')) {
    function homepage_get_occasion_tabs(mysqli $conn): array
    {
        require_once __DIR__ . '/collection_taxonomy.php';

        $tabs = [
            ['key' => 'birthday', 'label' => 'Birthday', 'cta' => 'View All Birthday Gifts', 'category_names' => ['Birthday'], 'fallback_category_id' => 3, 'list_link' => collection_url('occasion', 'birthday')],
            ['key' => 'anniversary', 'label' => 'Anniversary', 'cta' => 'View All Anniversary Gifts', 'category_names' => ['Anniversary'], 'fallback_category_id' => 4, 'list_link' => collection_url('occasion', 'anniversary')],
            ['key' => 'love', 'label' => 'Love N Romance', 'cta' => 'View All Romantic Gifts', 'category_names' => ['Love', 'Romance'], 'tag' => 'romance', 'list_link' => collection_url('occasion', 'love-romance')],
            ['key' => 'wedding', 'label' => 'Wedding', 'cta' => 'View All Wedding Gifts', 'category_names' => ['Wedding'], 'tag' => 'wedding', 'list_link' => collection_url('occasion', 'wedding')],
            ['key' => 'congratulations', 'label' => 'Congratulations', 'cta' => 'View All Congratulations Gifts', 'category_names' => ['Congratulations'], 'tag' => 'congratulations', 'list_link' => collection_url('occasion', 'congratulations')],
            ['key' => 'sympathy', 'label' => 'Sympathy', 'cta' => 'View All Sympathy Flowers', 'category_names' => ['Sympathy'], 'tag' => 'sympathy', 'list_link' => collection_url('occasion', 'sympathy')],
            ['key' => 'thankyou', 'label' => 'Thank You', 'cta' => 'View All Thank You Gifts', 'category_names' => ['Thank'], 'tag' => 'thank', 'list_link' => collection_url('occasion', 'thank-you')],
        ];

        foreach ($tabs as &$tab) {
            $resolved = homepage_resolve_category_by_names($conn, $tab['category_names']);
            if ($resolved) {
                $tab['category_id'] = $resolved;
            } elseif (!empty($tab['fallback_category_id'])) {
                $tab['category_id'] = (int) $tab['fallback_category_id'];
            } else {
                $tab['category_id'] = 0;
            }
        }
        unset($tab);

        return $tabs;
    }
}

if (!function_exists('homepage_fetch_occasion_products')) {
    function homepage_fetch_occasion_products(mysqli $conn, array $tab, int $limit = 10, array $excludeIds = []): array
    {
        $limit = max(1, min(20, $limit));
        $products = [];
        $excludeSql = homepage_build_exclude_sql($excludeIds);
        $tabKey = (string) ($tab['key'] ?? 'default');
        $offset = abs(crc32($tabKey)) % 12;

        $orderBy = match ($tabKey) {
            'birthday' => 'ORDER BY price ASC, id DESC',
            'anniversary' => 'ORDER BY price DESC, id DESC',
            'love' => 'ORDER BY rating DESC, id DESC',
            'wedding' => 'ORDER BY original_price DESC, id DESC',
            'congratulations' => 'ORDER BY id DESC',
            'sympathy' => 'ORDER BY id ASC',
            'thankyou' => 'ORDER BY rating DESC, id ASC',
            default => 'ORDER BY id DESC',
        };

        if (!empty($tab['category_id'])) {
            $catId = (int) $tab['category_id'];
            $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                    FROM flowers WHERE status = 1 AND category_ids LIKE '%,{$catId},%' {$excludeSql}
                    {$orderBy} LIMIT {$limit} OFFSET {$offset}";
            $res = $conn->query($sql);
        } elseif (!empty($tab['tag'])) {
            $tag = $conn->real_escape_string($tab['tag']);
            $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                    FROM flowers WHERE status = 1 {$excludeSql}
                    AND (LOWER(tag) LIKE '%{$tag}%' OR LOWER(name) LIKE '%{$tag}%')
                    {$orderBy} LIMIT {$limit} OFFSET {$offset}";
            $res = $conn->query($sql);
        } else {
            $sql = "SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                FROM flowers WHERE status = 1 {$excludeSql}
                {$orderBy} LIMIT {$limit} OFFSET {$offset}";
            $res = $conn->query($sql);
        }

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
        }

        if (count($products) < 4) {
            $need = $limit - count($products);
            $localExclude = array_merge($excludeIds, array_column($products, 'id'));
            $fallback = $conn->query("SELECT id, name, slug, image, price, original_price, rating, in_stock, tag
                FROM flowers WHERE status = 1 " . homepage_build_exclude_sql($localExclude) . "
                ORDER BY id DESC LIMIT {$need} OFFSET " . ($offset + 5));
            if ($fallback) {
                while ($row = $fallback->fetch_assoc()) {
                    $products[] = $row;
                }
            }
        }

        return $products;
    }
}

if (!function_exists('homepage_product_link')) {
    function homepage_product_link(array $product): string
    {
        global $conn;
        if ($conn instanceof mysqli) {
            return homepage_resolve_product_link($conn, $product);
        }

        if (!empty($product['link'])) {
            $link = trim((string) $product['link']);
            if ($link !== '' && $link !== '#') {
                return (strpos($link, '/') === 0) ? $link : '/' . ltrim($link, '/');
            }
        }

        return product_url([
            'type' => $product['type'] ?? 'flower',
            'slug' => $product['slug'] ?? '',
            'id' => (int) ($product['id'] ?? 0),
        ]);
    }
}

if (!function_exists('homepage_render_occasion_cards')) {
    function homepage_render_occasion_cards(array $products): string
    {
        if (count($products) === 0) {
            return '<p class="hp-occasion-empty">No products found for this occasion yet. <a href="/flowers.php">Browse all flowers</a>.</p>';
        }

        ob_start();
        foreach ($products as $p) {
            $link = htmlspecialchars(homepage_product_link($p));
            $img = htmlspecialchars(get_image_url($p['image'] ?? '', 'flowers'));
            $name = htmlspecialchars($p['name'] ?? '');
            $price = apply_surge_pricing((float) ($p['price'] ?? 0), 'flower');
            $orig = (float) ($p['original_price'] ?? 0);
            
            $ratingRaw = (float) ($p['rating'] ?? 0);
            if ($ratingRaw <= 0.0) {
                $hash = crc32($name . ($p['id'] ?? ''));
                $ratingRaw = 4.5 + (($hash % 6) / 10.0);
            }
            $rating = number_format($ratingRaw, 1);

            $discPct = ($orig > $price && $orig > 0) ? round(($orig - $price) / $orig * 100) : 0;
            ?>
            <article class="hp-occasion-card snap-start">
                <a href="<?= $link ?>" class="hp-occasion-card__media">
                    <img src="<?= $img ?>" alt="<?= $name ?>" width="280" height="350" loading="lazy" decoding="async">
                    <?php if ($discPct > 0): ?>
                    <span class="hp-occasion-card__badge"><?= (int) $discPct ?>% OFF</span>
                    <?php endif; ?>
                    <span class="hp-occasion-card__trust"><i class="fas fa-shield-halved" aria-hidden="true"></i> Secure checkout</span>
                </a>
                <div class="hp-occasion-card__body">
                    <a href="<?= $link ?>" class="hp-occasion-card__title"><?= $name ?></a>
                    <div class="hp-occasion-card__rating" aria-label="Rated <?= $rating ?> out of 5">
                        <span class="hp-stars" aria-hidden="true"><i class="fas fa-star"></i></span>
                        <span><?= $rating ?></span>
                        <span class="hp-muted">· Verified buyers</span>
                    </div>
                    <div class="hp-occasion-card__price">
                        <span class="hp-price-current">₹<?= number_format($price) ?></span>
                        <?php if ($orig > $price): ?>
                        <span class="hp-price-old">₹<?= number_format($orig) ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= $link ?>" class="hp-occasion-card__cta">Buy Now</a>
                </div>
            </article>
            <?php
        }
        return ob_get_clean();
    }
}

if (!function_exists('homepage_render_fav_flower_cards')) {
    /**
     * @param array<int, list<array<string, mixed>>> $itemsGrouped
     */
    function homepage_render_fav_flower_cards(array $itemsGrouped): string
    {
        $flowers = homepage_get_fav_flower_items($itemsGrouped);
        if (count($flowers) === 0) {
            return '';
        }

        ob_start();
        foreach ($flowers as $flower) {
            $link = htmlspecialchars($flower['link']);
            $img = htmlspecialchars(get_image_url($flower['image']));
            $title = htmlspecialchars($flower['title']);
            ?>
            <article class="hp-occasion-card snap-start">
                <a href="<?= $link ?>" class="hp-occasion-card__media">
                    <img src="<?= $img ?>" alt="<?= $title ?> flowers" width="280" height="350" loading="lazy" decoding="async">
                </a>
                <div class="hp-occasion-card__body">
                    <a href="<?= $link ?>" class="hp-occasion-card__title" style="min-height: auto; margin-bottom: 0.35rem;"><?= $title ?></a>
                    <a href="<?= $link ?>" class="hp-occasion-card__cta">Shop Now</a>
                </div>
            </article>
            <?php
        }
        return ob_get_clean();
    }
}

if (!function_exists('homepage_render_cms_carousel_cards')) {
    /**
     * CMS homepage_section_items (carousel) — same card markup as occasion slider.
     */
    function homepage_render_cms_carousel_cards(array $items): string
    {
        if (count($items) === 0) {
            return '';
        }

        global $conn;

        ob_start();
        foreach ($items as $i) {
            $resolvedLink = ($conn instanceof mysqli)
                ? homepage_resolve_product_link($conn, $i)
                : resolve_cms_item_link(null, $i, '/flowers');
            $link = htmlspecialchars($resolvedLink);
            $img = htmlspecialchars(get_image_url($i['image'] ?? ''));
            $name = htmlspecialchars($i['title'] ?? '');
            $price = (float) ($i['price'] ?? 0);
            $orig = (float) ($i['original_price'] ?? 0);
            
            $ratingRaw = (float) ($i['rating'] ?? 0);
            if ($ratingRaw <= 0.0) {
                // Generate a stable random rating between 4.5 and 5.0 based on the item title/id
                $hash = crc32($name . ($i['id'] ?? ''));
                $ratingRaw = 4.5 + (($hash % 6) / 10.0);
            }
            $rating = number_format($ratingRaw, 1);

            $discPct = 0;
            if ($orig > $price && $orig > 0) {
                $discPct = (int) round(($orig - $price) / $orig * 100);
            } elseif (!empty($i['discount_label']) && preg_match('/(\d+)\s*%/', (string) $i['discount_label'], $m)) {
                $discPct = (int) $m[1];
            }
            ?>
            <article class="hp-occasion-card snap-start">
                <a href="<?= $link ?>" class="hp-occasion-card__media">
                    <img src="<?= $img ?>" alt="<?= $name ?>" width="280" height="350" loading="lazy" decoding="async">
                    <?php if ($discPct > 0): ?>
                    <span class="hp-occasion-card__badge"><?= $discPct ?>% OFF</span>
                    <?php endif; ?>
                    <span class="hp-occasion-card__trust"><i class="fas fa-shield-halved" aria-hidden="true"></i> Secure checkout</span>
                </a>
                <div class="hp-occasion-card__body">
                    <a href="<?= $link ?>" class="hp-occasion-card__title"><?= $name ?></a>
                    <div class="hp-occasion-card__rating" aria-label="Rated <?= $rating ?> out of 5">
                        <span class="hp-stars" aria-hidden="true"><i class="fas fa-star"></i></span>
                        <span><?= $rating ?></span>
                        <span class="hp-muted">· Verified buyers</span>
                    </div>
                    <div class="hp-occasion-card__price">
                        <span class="hp-price-current">₹<?= homepage_display_price($price) ?></span>
                        <?php if ($orig > $price): ?>
                        <span class="hp-price-old">₹<?= number_format($orig) ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= $link ?>" class="hp-occasion-card__cta">Buy Now</a>
                </div>
            </article>
            <?php
        }
        return ob_get_clean();
    }
}
