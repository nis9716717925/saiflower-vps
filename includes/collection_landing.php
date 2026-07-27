<?php
/**
 * Collection landing engine — product filtering, SEO meta, related links, schemas.
 */

require_once __DIR__ . '/collection_taxonomy.php';
require_once __DIR__ . '/url_helper.php';

if (!function_exists('collection_resolve_category_ids')) {
    /**
     * @param array<string, mixed> $filter
     * @return list<int>
     */
    function collection_resolve_category_ids(mysqli $conn, array $filter): array
    {
        $ids = [];
        if (!empty($filter['category_ids']) && is_array($filter['category_ids'])) {
            foreach ($filter['category_ids'] as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        if (!empty($filter['fallback_category_id'])) {
            $ids[] = (int) $filter['fallback_category_id'];
        }
        if (!empty($filter['category_names']) && is_array($filter['category_names'])) {
            foreach ($filter['category_names'] as $name) {
                $like = '%' . mb_strtolower((string) $name) . '%';
                $stmt = $conn->prepare('SELECT id FROM categories WHERE status = 1 AND LOWER(name) LIKE ? ORDER BY sort_order ASC LIMIT 1');
                if ($stmt) {
                    $stmt->bind_param('s', $like);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) {
                        $ids[] = (int) $row['id'];
                    }
                }
            }
        }
        return array_values(array_unique(array_filter($ids)));
    }
}

if (!function_exists('collection_build_relevance_sql')) {
    /**
     * Build WHERE fragment + bind params for tag/name/category matching.
     *
     * @param array<string, mixed> $filter
     * @return array{sql:string,types:string,params:list<mixed>}
     */
    function collection_build_relevance_sql(mysqli $conn, array $filter, string $table): array
    {
        $parts = [];
        $types = '';
        $params = [];
        $match = ($filter['match'] ?? 'any') === 'all' ? ' AND ' : ' OR ';

        $tags = array_values(array_filter(array_map('strval', $filter['tags'] ?? [])));
        $keywords = array_values(array_filter(array_map('strval', $filter['name_keywords'] ?? [])));

        foreach ($tags as $tag) {
            $tag = mb_strtolower(trim($tag));
            if ($tag === '') {
                continue;
            }
            $parts[] = '(LOWER(IFNULL(tag,\'\')) LIKE ? OR LOWER(IFNULL(tag,\'\')) LIKE ? OR LOWER(IFNULL(tag,\'\')) LIKE ? OR LOWER(IFNULL(tag,\'\')) = ?)';
            $types .= 'ssss';
            $params[] = '%,' . $tag . ',%';
            $params[] = $tag . ',%';
            $params[] = '%,' . $tag;
            $params[] = $tag;
            // also loose contains
            $parts[] = 'LOWER(IFNULL(tag,\'\')) LIKE ?';
            $types .= 's';
            $params[] = '%' . $tag . '%';
        }

        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw));
            if ($kw === '') {
                continue;
            }
            $parts[] = 'LOWER(name) LIKE ?';
            $types .= 's';
            $params[] = '%' . $kw . '%';
        }

        if ($table === 'flowers') {
            $catIds = collection_resolve_category_ids($conn, $filter);
            foreach ($catIds as $catId) {
                $parts[] = 'category_ids LIKE ?';
                $types .= 's';
                $params[] = '%,' . $catId . ',%';
            }
        }

        if ($parts === []) {
            // Broad collections (best-sellers / new arrivals): no keyword filter
            return ['sql' => '1=1', 'types' => '', 'params' => []];
        }

        return [
            'sql' => '(' . implode($match, $parts) . ')',
            'types' => $types,
            'params' => $params,
        ];
    }
}

if (!function_exists('collection_order_sql')) {
    function collection_order_sql(?string $sort): string
    {
        return match ($sort) {
            'price_low' => 'price ASC, id DESC',
            'price_high' => 'price DESC, id DESC',
            'rating' => 'rating DESC, id DESC',
            'name' => 'name ASC',
            'newest', 'new' => 'id DESC',
            default => 'rating DESC, id DESC',
        };
    }
}

if (!function_exists('collection_product_passes_php_filter')) {
    /**
     * Extra PHP-side relevance for flower-type pages so "rose" does not match "prose".
     */
    function collection_product_passes_php_filter(array $row, array $filter): bool
    {
        $keywords = array_values(array_filter(array_map('strval', $filter['name_keywords'] ?? [])));
        $tags = array_values(array_filter(array_map('strval', $filter['tags'] ?? [])));
        if ($keywords === [] && $tags === [] && empty($filter['category_names']) && empty($filter['category_ids']) && empty($filter['fallback_category_id'])) {
            return true;
        }

        $name = mb_strtolower((string) ($row['name'] ?? ''));
        $tag = mb_strtolower((string) ($row['tag'] ?? ''));
        $hay = $name . ' ' . $tag;

        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw));
            if ($kw !== '' && str_contains($hay, $kw)) {
                return true;
            }
        }
        foreach ($tags as $t) {
            $t = mb_strtolower(trim($t));
            if ($t !== '' && str_contains($hay, $t)) {
                return true;
            }
        }

        // Category-only filters already applied in SQL
        if ($keywords === [] && $tags === []) {
            return true;
        }

        return false;
    }
}

if (!function_exists('collection_fetch_products')) {
    /**
     * Fetch bouquets only for landing pages. Never returns car/first-night décor.
     * Guarantees at least $minFill bouquets (default 36) so pages are never empty.
     *
     * @return list<array<string, mixed>>
     */
    function collection_fetch_products(mysqli $conn, array $collection, int $limit = 40, int $minFill = 36): array
    {
        require_once __DIR__ . '/landing_page_sliders.php';

        $filter = $collection['filter'] ?? [];
        $sort = $filter['sort'] ?? 'rating';
        $orderBy = collection_order_sql($sort);
        $limit = max(30, min(48, $limit));
        $minFill = max(30, min($limit, $minFill));
        $fetchEach = max($limit * 4, 80);

        $priceMin = isset($filter['price_min']) ? (float) $filter['price_min'] : null;
        $priceMax = isset($filter['price_max']) ? (float) $filter['price_max'] : null;

        $results = [];
        $seen = [];

        // Landings always sell flower bouquets only (ignore cakes/gifts/décor tables).
        $rel = collection_build_relevance_sql($conn, $filter, 'flowers');
        $where = 'status = 1 AND (' . landing_bouquet_sql_name_filter() . ') AND (' . $rel['sql'] . ')';
        $types = $rel['types'];
        $params = $rel['params'];

        if ($priceMin !== null) {
            $where .= ' AND price >= ?';
            $types .= 'd';
            $params[] = $priceMin;
        }
        if ($priceMax !== null) {
            $where .= ' AND price <= ?';
            $types .= 'd';
            $params[] = $priceMax;
        }

        $sql = "SELECT id, name, slug, image, price, original_price, rating, IFNULL(tag,'') AS tag
                FROM flowers
                WHERE {$where}
                ORDER BY {$orderBy}
                LIMIT {$fetchEach}";

        try {
            if ($types !== '') {
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($row = $res->fetch_assoc()) {
                        if (!landing_is_bouquet_product($row)) {
                            continue;
                        }
                        if (!collection_product_passes_php_filter($row, $filter)) {
                            continue;
                        }
                        $key = 'flower:' . $row['id'];
                        if (isset($seen[$key])) {
                            continue;
                        }
                        $seen[$key] = true;
                        $row['type'] = 'flower';
                        $results[] = $row;
                    }
                }
            } else {
                $res = $conn->query($sql);
                if ($res) {
                    while ($row = $res->fetch_assoc()) {
                        if (!landing_is_bouquet_product($row)) {
                            continue;
                        }
                        $key = 'flower:' . $row['id'];
                        if (isset($seen[$key])) {
                            continue;
                        }
                        $seen[$key] = true;
                        $row['type'] = 'flower';
                        $results[] = $row;
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('collection_fetch_products: ' . $e->getMessage());
        }

        usort($results, static function ($a, $b) use ($sort) {
            return match ($sort) {
                'price_low' => ((float) $a['price'] <=> (float) $b['price']) ?: ((int) $b['id'] <=> (int) $a['id']),
                'price_high' => ((float) $b['price'] <=> (float) $a['price']) ?: ((int) $b['id'] <=> (int) $a['id']),
                'newest', 'new' => ((int) $b['id'] <=> (int) $a['id']),
                'name' => strcasecmp((string) $a['name'], (string) $b['name']),
                default => ((float) $b['rating'] <=> (float) $a['rating']) ?: ((int) $b['id'] <=> (int) $a['id']),
            };
        });

        // Backfill with popular bouquets so the landing is never empty / sparse.
        if (count($results) < $minFill) {
            $excludeIds = array_map(static fn($r) => (int) $r['id'], $results);
            $need = $minFill - count($results);
            $fill = collection_fetch_bouquet_backfill($conn, $need + 12, $excludeIds);
            foreach ($fill as $row) {
                $key = 'flower:' . $row['id'];
                if (isset($seen[$key])) {
                    continue;
                }
                if (!landing_is_bouquet_product($row)) {
                    continue;
                }
                $seen[$key] = true;
                $row['type'] = 'flower';
                $row['_backfill'] = true;
                $results[] = $row;
                if (count($results) >= $minFill) {
                    break;
                }
            }
        }

        return array_slice($results, 0, $limit);
    }
}

if (!function_exists('collection_fetch_bouquet_backfill')) {
    /**
     * Popular bouquets used to pad sparse landings (never décor).
     *
     * @param list<int> $excludeIds
     * @return list<array<string, mixed>>
     */
    function collection_fetch_bouquet_backfill(mysqli $conn, int $limit = 40, array $excludeIds = []): array
    {
        require_once __DIR__ . '/landing_page_sliders.php';

        $limit = max(1, min(60, $limit));
        $excludeIds = array_values(array_unique(array_filter(array_map('intval', $excludeIds))));
        $excludeSql = $excludeIds === [] ? '' : (' AND id NOT IN (' . implode(',', $excludeIds) . ')');

        // Prefer conversion price band, then high rating
        $sql = "SELECT id, name, slug, image, price, original_price, rating, IFNULL(tag,'') AS tag
                FROM flowers
                WHERE status = 1
                  AND (" . landing_bouquet_sql_name_filter() . ")
                  {$excludeSql}
                ORDER BY
                  CASE
                    WHEN price BETWEEN 499 AND 1999 THEN 0
                    WHEN price BETWEEN 2000 AND 2499 THEN 1
                    ELSE 2
                  END,
                  rating DESC,
                  id DESC
                LIMIT " . ($limit * 3);

        $out = [];
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if (!landing_is_bouquet_product($row)) {
                    continue;
                }
                $out[] = $row;
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return $out;
    }
}

if (!function_exists('collection_split_product_groups')) {
    /**
     * @param list<array<string, mixed>> $products
     * @return array{all:list,featured:list,bestsellers:list,recent:list,sameday:list}
     */
    function collection_split_product_groups(array $products): array
    {
        $all = $products;
        $featured = array_slice($all, 0, 8);
        $bestsellers = $all;
        usort($bestsellers, static fn($a, $b) => ((float) $b['rating'] <=> (float) $a['rating']));
        $bestsellers = array_slice($bestsellers, 0, 8);
        $recent = $all;
        usort($recent, static fn($a, $b) => ((int) $b['id'] <=> (int) $a['id']));
        $recent = array_slice($recent, 0, 8);

        $sameday = array_values(array_filter($all, static function ($p) {
            $hay = mb_strtolower(($p['name'] ?? '') . ' ' . ($p['tag'] ?? ''));
            return str_contains($hay, 'same day') || str_contains($hay, 'express') || str_contains($hay, 'sameday');
        }));
        if (count($sameday) < 4) {
            $sameday = array_slice($featured, 0, 8);
        } else {
            $sameday = array_slice($sameday, 0, 8);
        }

        return [
            'all' => $all,
            'featured' => $featured,
            'bestsellers' => $bestsellers,
            'recent' => $recent,
            'sameday' => $sameday,
        ];
    }
}

if (!function_exists('collection_related_entries')) {
    /**
     * @return list<array<string, mixed>>
     */
    function collection_related_entries(array $collection, int $limit = 8): array
    {
        $out = [];
        foreach ($collection['related'] ?? [] as $ref) {
            $parts = explode(':', (string) $ref, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $item = collection_get($parts[0], $parts[1]);
            if ($item) {
                $out[] = $item;
            }
        }
        if (count($out) < $limit) {
            $kind = $collection['kind'] ?? 'flower';
            foreach (collection_list($kind) as $item) {
                if (($item['slug'] ?? '') === ($collection['slug'] ?? '')) {
                    continue;
                }
                $out[] = $item;
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return array_slice($out, 0, $limit);
    }
}

if (!function_exists('collection_cross_kind_links')) {
    /**
     * @return array{flowers:list,relations:list,occasions:list,collections:list}
     */
    function collection_cross_kind_links(array $collection): array
    {
        $pick = static function (string $kind, int $n) use ($collection): array {
            $items = collection_list($kind);
            $self = ($collection['kind'] ?? '') . ':' . ($collection['slug'] ?? '');
            $items = array_values(array_filter($items, static fn($i) => (($i['kind'] ?? '') . ':' . ($i['slug'] ?? '')) !== $self));
            return array_slice($items, 0, $n);
        };
        return [
            'flowers' => $pick('flower', 8),
            'relations' => $pick('relation', 8),
            'occasions' => $pick('occasion', 8),
            'collections' => $pick('collection', 8),
        ];
    }
}

if (!function_exists('collection_build_seo_content')) {
    function collection_build_seo_content(array $collection): string
    {
        $title = htmlspecialchars($collection['title']);
        $kind = $collection['kind'] ?? 'collection';
        $path = htmlspecialchars($collection['canonical_path'] ?? '/');

        $relatedHtml = '';
        foreach (array_slice(collection_related_entries($collection, 6), 0, 6) as $rel) {
            $relatedHtml .= '<li><a href="' . htmlspecialchars($rel['canonical_path']) . '" title="' . htmlspecialchars($rel['title']) . '">' . htmlspecialchars($rel['title']) . '</a></li>';
        }

        return <<<HTML
<h2>{$title} Online from Sai Flowers</h2>
<p>Discover our curated <strong>{$title}</strong> selection — handcrafted by florists with daily-fresh blooms and delivered across Delhi NCR. Whether you need a last-minute surprise or a planned celebration gift, Sai Flowers has been trusted since 1998 for premium quality and reliable delivery.</p>
<p>Browse the products above, or explore related picks via <a href="{$path}">{$title}</a> and our popular internal collections.</p>
<h3>Why customers choose Sai Flowers</h3>
<ul>
<li><strong>Freshness guarantee</strong> — hand-arranged, gift-ready packaging</li>
<li><strong>Same-day &amp; scheduled delivery</strong> — Delhi, Gurgaon, Noida &amp; NCR</li>
<li><strong>Secure checkout</strong> — UPI, cards &amp; wallets</li>
<li><strong>Personal message cards</strong> — free with every order</li>
</ul>
<h3>Related collections you may love</h3>
<ul>{$relatedHtml}</ul>
<p>Need help choosing? <a href="/contact" title="Contact Sai Flowers">Contact us</a> or WhatsApp <a href="https://wa.me/918802004527" rel="noopener noreferrer" target="_blank">+91 88020 04527</a>. Also see our <a href="/delivery-policy">delivery policy</a> and <a href="/flowers">full flower catalogue</a>.</p>
HTML;
    }
}

if (!function_exists('collection_build_meta')) {
    /**
     * @return array{title:string,description:string,keywords:string,og_title:string,og_description:string}
     */
    function collection_build_meta(array $collection): array
    {
        $label = $collection['title'];
        $title = "{$label} Online Delivery Delhi NCR | Sai Flowers";
        if (strlen($title) > 60) {
            $title = "{$label} Delivery Delhi | Sai Flowers";
        }
        $desc = $collection['short_description'] ?? '';
        if (strlen($desc) < 110) {
            $desc .= " Order online from Sai Flowers — fresh blooms, secure checkout, same-day options in Delhi NCR.";
        }
        $desc = mb_substr(trim($desc), 0, 158);
        $keywords = strtolower($label) . ', ' . strtolower($label) . ' delivery delhi, flower delivery delhi ncr, sai flowers, same day flowers, online florist delhi';
        return [
            'title' => $title,
            'description' => $desc,
            'keywords' => $keywords,
            'og_title' => "{$label} — Same-Day Delivery | Sai Flowers",
            'og_description' => $desc,
        ];
    }
}

if (!function_exists('collection_product_url')) {
    function collection_product_url(array $item): string
    {
        $type = $item['type'] ?? 'flower';
        $slug = $item['slug'] ?? '';
        if ($slug !== '' && function_exists('product_url_by_parts')) {
            return product_url_by_parts($type, $slug);
        }
        return match ($type) {
            'cake' => '/cakes/' . rawurlencode($slug),
            'gift' => '/gifts/' . rawurlencode($slug),
            default => '/flowers/' . rawurlencode($slug),
        };
    }
}

if (!function_exists('collection_image_url')) {
    function collection_image_url(?string $image): string
    {
        $image = trim((string) $image);
        if ($image === '') {
            return 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=600&q=80';
        }
        if (preg_match('#^https?://#i', $image)) {
            return $image;
        }
        if (str_starts_with($image, '/')) {
            return $image;
        }
        if (str_starts_with($image, 'uploads/')) {
            return '/' . $image;
        }
        return '/uploads/' . ltrim($image, '/');
    }
}

if (!function_exists('collection_city_links')) {
    /**
     * @return list<array{name:string,href:string}>
     */
    function collection_city_links(): array
    {
        return [
            ['name' => 'Delhi', 'href' => '/flower-delivery-in-delhi'],
            ['name' => 'Gurgaon', 'href' => '/flower-delivery-in-gurgaon'],
            ['name' => 'Noida', 'href' => '/flower-delivery-in-noida'],
            ['name' => 'Ghaziabad', 'href' => '/flower-delivery-in-ghaziabad'],
            ['name' => 'Faridabad', 'href' => '/flower-delivery-in-faridabad'],
            ['name' => 'Greater Noida', 'href' => '/flower-delivery-in-greater-noida'],
        ];
    }
}

if (!function_exists('collection_popular_searches')) {
    /**
     * @return list<array{label:string,href:string}>
     */
    function collection_popular_searches(): array
    {
        return [
            ['label' => 'Roses', 'href' => '/flowers/roses'],
            ['label' => 'Birthday Flowers', 'href' => '/occasion/birthday'],
            ['label' => 'Anniversary', 'href' => '/occasion/anniversary'],
            ['label' => 'Same Day Delivery', 'href' => '/collection/same-day-delivery'],
            ['label' => 'Luxury Flowers', 'href' => '/collection/luxury-flowers'],
            ['label' => 'For Mother', 'href' => '/relation/mother'],
            ['label' => 'For Wife', 'href' => '/relation/wife'],
            ['label' => 'Orchids', 'href' => '/flowers/orchids'],
            ['label' => 'Budget Under ₹999', 'href' => '/collection/budget-flowers'],
            ['label' => 'New Arrivals', 'href' => '/collection/new-arrivals'],
        ];
    }
}

if (!function_exists('collection_json_ld')) {
    function collection_json_ld(array $collection, array $products, array $faqs): string
    {
        $base = 'https://saiflower.com';
        $url = $base . ($collection['canonical_path'] ?? '/');
        $meta = collection_build_meta($collection);

        $itemList = [];
        foreach (array_slice($products, 0, 24) as $i => $p) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => $base . collection_product_url($p),
                'name' => $p['name'] ?? '',
            ];
        }

        $graph = [
            [
                '@type' => 'Organization',
                'name' => 'Sai Flowers',
                'url' => $base,
                'logo' => $base . '/uploads/logo_transparent.png',
                'telephone' => '+918802004527',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => ucfirst($collection['kind'] ?? 'Shop'), 'item' => $base . '/' . ($collection['kind'] === 'flower' ? 'flowers' : ($collection['kind'] ?? 'collection'))],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $collection['title'], 'item' => $url],
                ],
            ],
            [
                '@type' => 'CollectionPage',
                'name' => $meta['title'],
                'description' => $meta['description'],
                'url' => $url,
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'itemListElement' => $itemList,
                ],
            ],
        ];

        if ($faqs !== []) {
            $faqEntities = [];
            foreach ($faqs as $f) {
                $faqEntities[] = [
                    '@type' => 'Question',
                    'name' => $f['q'] ?? ($f['question'] ?? ''),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $f['a'] ?? ($f['answer'] ?? ''),
                    ],
                ];
            }
            $graph[] = [
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities,
            ];
        }

        $json = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return '<script type="application/ld+json">' . $json . '</script>';
    }
}
