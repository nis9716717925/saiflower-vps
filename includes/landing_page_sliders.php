<?php
/**
 * Product carousel sliders for occasion landing pages (lightweight, no homepage_premium).
 */

require_once __DIR__ . '/url_helper.php';
require_once __DIR__ . '/seo_helper.php';

if (!function_exists('landing_format_price')) {
    function landing_format_price($price): string
    {
        return number_format((float) $price);
    }
}

if (!function_exists('landing_exclude_ids_sql')) {
    function landing_exclude_ids_sql(array $excludeIds): string
    {
        $excludeIds = array_values(array_unique(array_filter(array_map('intval', $excludeIds))));
        if ($excludeIds === []) {
            return '';
        }
        return ' AND id NOT IN (' . implode(',', $excludeIds) . ')';
    }
}

if (!function_exists('landing_tag_sql_fragment')) {
    function landing_tag_sql_fragment(): string
    {
        return 'tag LIKE ? OR tag LIKE ? OR tag LIKE ? OR tag = ?';
    }
}

if (!function_exists('landing_bind_tag_params')) {
    function landing_bind_tag_params(string $tag): array
    {
        return ["%,{$tag},%", "{$tag},%", "%,{$tag}", $tag];
    }
}

if (!function_exists('landing_bouquet_sql_name_filter')) {
    /** SQL fragment: product title must be a floral bouquet. */
    function landing_bouquet_sql_name_filter(): string
    {
        return "LOWER(name) LIKE '%bouquet%'";
    }
}

if (!function_exists('landing_is_bouquet_product')) {
    /**
     * True only for sellable flower bouquets.
     * Excludes car decor, first-night, wedding/event décor, workshops, etc.
     */
    function landing_is_bouquet_product(array $row): bool
    {
        $name = mb_strtolower(trim($row['name'] ?? ($row['title'] ?? '')));
        $tag = mb_strtolower(trim($row['tag'] ?? ''));
        $hay = $name . ' ' . $tag;

        if ($name === '') {
            return false;
        }

        $blockedPatterns = [
            '/\bcar\b/',
            '/\bevent\b/',
            '/\bdecor\b/',
            '/\bdecoration\b/',
            '/\bworkshop\b/',
            '/\bvenue\b/',
            '/\bpackage\b/',
            '/\bfirst\s*night\b/',
            '/\broom\s*decor/',
            '/\bstage\b/',
            '/\bmandap\b/',
            '/\bbackdrop\b/',
            '/\bwedding\s*decor/',
            '/\bgarland\s*install/',
        ];
        foreach ($blockedPatterns as $pattern) {
            if (preg_match($pattern, $hay)) {
                return false;
            }
        }

        // Must be a bouquet / hand bouquet / basket bouquet style floral gift
        if (str_contains($name, 'bouquet')) {
            return true;
        }
        if (str_contains($name, 'flower basket') || str_contains($name, 'flower box')) {
            return true;
        }

        return false;
    }
}

if (!function_exists('landing_filter_bouquet_items')) {
    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    function landing_filter_bouquet_items(array $items): array
    {
        return array_values(array_filter($items, 'landing_is_bouquet_product'));
    }
}

if (!function_exists('landing_fetch_bouquets')) {
    /**
     * Fetch flower bouquets only (flowers table).
     *
     * @return list<array<string, mixed>>
     */
    function landing_fetch_bouquets(
        mysqli $conn,
        ?string $tag,
        int $limit,
        string $orderBy = 'rating DESC, id DESC',
        array $excludeIds = [],
        ?string $extraWhere = null
    ): array {
        $allowedOrder = [
            'rating DESC, id DESC',
            'id DESC',
            'price DESC, rating DESC',
            'price DESC, id DESC',
        ];
        if (!in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'rating DESC, id DESC';
        }

        $fetchLimit = max($limit * 3, 24);
        $excludeSql = landing_exclude_ids_sql($excludeIds);
        $where = 'status = 1 ' . $excludeSql . ' AND (' . landing_bouquet_sql_name_filter() . ')';
        $types = '';
        $params = [];

        if ($tag !== null && $tag !== '') {
            $where .= ' AND (' . landing_tag_sql_fragment() . ')';
            $types .= 'ssss';
            $params = array_merge($params, landing_bind_tag_params($tag));
        }

        if ($extraWhere) {
            $where .= ' AND (' . $extraWhere . ')';
        }

        $sql = "SELECT id, name, slug, image, price, original_price, rating
                FROM flowers WHERE {$where} ORDER BY {$orderBy} LIMIT {$fetchLimit}";

        $rows = [];
        try {
            if ($types !== '') {
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    return [];
                }
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
            } else {
                $res = $conn->query($sql);
            }

            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    if (!landing_is_bouquet_product($row)) {
                        continue;
                    }
                    $rows[] = landing_row_to_slider_item($row, 'flower');
                    if (count($rows) >= $limit) {
                        break;
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('landing_fetch_bouquets: ' . $e->getMessage());
            return [];
        }

        return $rows;
    }
}

if (!function_exists('landing_fetch_table_products')) {
    /**
     * @return list<array<string, mixed>>
     */
    function landing_fetch_table_products(
        mysqli $conn,
        string $table,
        string $type,
        ?string $tag,
        int $limit,
        string $orderBy = 'rating DESC, id DESC',
        array $excludeIds = [],
        ?string $extraWhere = null
    ): array {
        $allowed = ['flowers' => 'flower', 'cakes' => 'cake', 'gifts' => 'gift'];
        if (!isset($allowed[$table])) {
            return [];
        }

        $allowedOrder = [
            'rating DESC, id DESC',
            'id DESC',
            'price DESC, rating DESC',
            'price DESC, id DESC',
        ];
        if (!in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'rating DESC, id DESC';
        }

        $limit = max(1, min(12, $limit));
        $excludeSql = landing_exclude_ids_sql($excludeIds);
        $where = "status = 1 {$excludeSql}";
        $types = '';
        $params = [];

        if ($tag !== null && $tag !== '') {
            $where .= ' AND (' . landing_tag_sql_fragment() . ')';
            $types .= 'ssss';
            $params = array_merge($params, landing_bind_tag_params($tag));
        }

        if ($extraWhere) {
            $where .= ' AND (' . $extraWhere . ')';
        }

        $sql = "SELECT id, name, slug, image, price, original_price, rating
                FROM {$table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$limit}";

        $rows = [];
        try {
            if ($types !== '') {
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    return [];
                }
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
            } else {
                $res = $conn->query($sql);
            }

            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $rows[] = landing_row_to_slider_item($row, $allowed[$table]);
                }
            }
        } catch (Throwable $e) {
            error_log('landing_fetch_table_products: ' . $e->getMessage());
            return [];
        }

        return $rows;
    }
}

if (!function_exists('landing_row_to_slider_item')) {
    function landing_row_to_slider_item(array $row, string $type): array
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
            'type' => $type,
        ];
        $item['link'] = occasion_product_url($item);

        return $item;
    }
}

if (!function_exists('landing_fill_slider_items')) {
    /**
     * @param list<array<string, mixed>> $primary
     * @param list<array<string, mixed>> $fallback
     * @return list<array<string, mixed>>
     */
    function landing_fill_slider_items(array $primary, array $fallback, int $limit): array
    {
        $seen = [];
        $out = [];
        foreach (array_merge($primary, $fallback) as $item) {
            $key = ($item['type'] ?? 'x') . ':' . (int) ($item['id'] ?? 0);
            if ($key === 'x:0' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
            if (count($out) >= $limit) {
                break;
            }
        }
        return $out;
    }
}

if (!function_exists('landing_render_carousel_cards')) {
    function landing_render_carousel_cards(array $items, string $occasionLabel): string
    {
        if (count($items) === 0) {
            return '';
        }

        ob_start();
        foreach ($items as $i) {
            $link = htmlspecialchars((string) ($i['link'] ?? '/flowers'));
            $folder = ($i['type'] ?? '') === 'cake' ? 'cakes' : (($i['type'] ?? '') === 'gift' ? 'gifts' : 'flowers');
            $img = htmlspecialchars(get_image_url($i['image'] ?? '', $folder));
            $name = htmlspecialchars($i['title'] ?? '');
            $alt = htmlspecialchars(occasion_product_image_alt($i, $occasionLabel));
            $price = (float) ($i['price'] ?? 0);
            $orig = (float) ($i['original_price'] ?? 0);
            $ratingRaw = (float) ($i['rating'] ?? 0);
            if ($ratingRaw <= 0.0) {
                $hash = crc32($name . ($i['id'] ?? ''));
                $ratingRaw = 4.5 + (($hash % 6) / 10.0);
            }
            $rating = number_format($ratingRaw, 1);
            $discPct = ($orig > $price && $orig > 0) ? (int) round(($orig - $price) / $orig * 100) : 0;
            ?>
            <article class="hp-occasion-card snap-start" role="listitem">
                <a href="<?= $link ?>" class="hp-occasion-card__media" title="<?= $name ?>">
                    <img src="<?= $img ?>" alt="<?= $alt ?>" width="280" height="350" loading="lazy" decoding="async">
                    <?php if ($discPct > 0): ?>
                    <span class="hp-occasion-card__badge"><?= $discPct ?>% OFF</span>
                    <?php endif; ?>
                </a>
                <div class="hp-occasion-card__body">
                    <a href="<?= $link ?>" class="hp-occasion-card__title"><?= $name ?></a>
                    <div class="hp-occasion-card__rating" aria-label="Rated <?= $rating ?> out of 5">
                        <span class="hp-stars" aria-hidden="true"><i class="fas fa-star"></i></span>
                        <span><?= $rating ?></span>
                    </div>
                    <div class="hp-occasion-card__price">
                        <span class="hp-price-current">₹<?= landing_format_price($price) ?></span>
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

if (!function_exists('landing_get_bouquet_only_sliders')) {
    /**
     * Father's Day (and similar): flower bouquets only — no cakes, gifts, cars, or event decor.
     *
     * @return list<array{key: string, title: string, subtitle: string, view_all: string, html: string}>
     */
    function landing_get_bouquet_only_sliders(mysqli $conn, array $pageData): array
    {
        $tag = trim((string) ($pageData['page_tag'] ?? ''));
        $occasion = (string) ($pageData['occasion_label'] ?? 'Special Occasion');
        $perSlider = (int) ($pageData['slider_items_per_row'] ?? 8);
        $perSlider = max(4, min(10, $perSlider));
        $minItems = 2;
        $usedFlowerIds = [];

        $collectExclude = static function () use (&$usedFlowerIds): array {
            return $usedFlowerIds;
        };
        $markUsed = static function (array $items) use (&$usedFlowerIds): void {
            foreach ($items as $item) {
                $usedFlowerIds[] = (int) ($item['id'] ?? 0);
            }
        };

        $build = static function (
            string $key,
            string $title,
            string $subtitle,
            string $viewAll,
            array $items
        ) use ($occasion, $perSlider, $minItems, $markUsed): ?array {
            $items = landing_filter_bouquet_items($items);
            $items = array_slice($items, 0, $perSlider);
            if (count($items) < $minItems) {
                return null;
            }
            $markUsed($items);
            $html = landing_render_carousel_cards($items, $occasion);
            if ($html === '') {
                return null;
            }
            return [
                'key' => $key,
                'title' => $title,
                'subtitle' => $subtitle,
                'view_all' => $viewAll,
                'html' => $html,
            ];
        };

        $sliders = [];
        $need = $perSlider + 6;

        $tagged = landing_fetch_bouquets($conn, $tag !== '' ? $tag : null, $need, 'rating DESC, id DESC', $collectExclude());
        $fallback = landing_fetch_bouquets($conn, null, $need, 'rating DESC, id DESC', $collectExclude());
        $allBouquets = landing_fill_slider_items($tagged, $fallback, $need);

        $availablePool = static function (array $pool) use (&$usedFlowerIds): array {
            return array_values(array_filter($pool, static function (array $item) use (&$usedFlowerIds): bool {
                return !in_array((int) ($item['id'] ?? 0), $usedFlowerIds, true);
            }));
        };

        $definitions = [
            [
                'key' => 'fathers-day-bouquets',
                'title' => "Father's Day Flower Bouquets",
                'subtitle' => 'Hand-arranged bouquets for Dad — delivered fresh in Delhi NCR.',
                'view_all' => '/flowers',
                'pick' => static function (array $pool) use ($tagged, $fallback, $perSlider): array {
                    return landing_fill_slider_items($tagged, $fallback, $perSlider);
                },
            ],
            [
                'key' => 'fathers-day-rose-bouquets',
                'title' => 'Rose Bouquets for Father\'s Day',
                'subtitle' => 'Classic red and mixed rose bouquets he will love.',
                'view_all' => '/flowers',
                'pick' => static function (array $pool) use ($perSlider, $availablePool): array {
                    $pool = $availablePool($pool);
                    $roses = array_filter($pool, static function (array $item): bool {
                        $n = mb_strtolower($item['name'] ?? '');
                        return strpos($n, 'rose') !== false && landing_is_bouquet_product($item);
                    });
                    return array_slice(array_values($roses), 0, $perSlider);
                },
            ],
            [
                'key' => 'fathers-day-same-day-bouquets',
                'title' => "Same-Day Father's Day Bouquets",
                'subtitle' => 'Order now for express bouquet delivery today.',
                'view_all' => '/flowers',
                'pick' => static function (array $pool) use ($conn, $tag, $perSlider, $collectExclude): array {
                    $sameDayWhere = "LOWER(COALESCE(tag,'')) LIKE '%same day%' OR LOWER(COALESCE(tag,'')) LIKE '%express%'
                        OR LOWER(name) LIKE '%same day%' OR LOWER(name) LIKE '%express%'";
                    $taggedSd = landing_fetch_bouquets($conn, $tag !== '' ? $tag : null, $perSlider + 4, 'id DESC', $collectExclude(), $sameDayWhere);
                    $fallbackSd = landing_fetch_bouquets($conn, null, $perSlider + 4, 'id DESC', $collectExclude(), $sameDayWhere);
                    return landing_fill_slider_items($taggedSd, $fallbackSd, $perSlider);
                },
            ],
            [
                'key' => 'fathers-day-premium-bouquets',
                'title' => 'Premium Bouquets for Dad',
                'subtitle' => 'Luxury wraps and premium blooms for a grand surprise.',
                'view_all' => '/flowers',
                'pick' => static function (array $pool) use ($conn, $perSlider, $collectExclude, $availablePool): array {
                    $premium = landing_fetch_bouquets($conn, null, $perSlider + 4, 'price DESC, rating DESC', $collectExclude());
                    $fromPool = array_filter($availablePool($pool), static function (array $item): bool {
                        return (float) ($item['price'] ?? 0) >= 999;
                    });
                    return landing_fill_slider_items($premium, array_values($fromPool), $perSlider);
                },
            ],
            [
                'key' => 'fathers-day-best-bouquets',
                'title' => "Best-Selling Bouquets for Father's Day",
                'subtitle' => 'Top-rated bouquets chosen by our customers.',
                'view_all' => '/flowers',
                'pick' => static function (array $pool) use ($conn, $perSlider, $collectExclude, $availablePool): array {
                    $rated = landing_fetch_bouquets($conn, null, $perSlider + 4, 'rating DESC, id DESC', $collectExclude());
                    return landing_fill_slider_items($rated, $availablePool($pool), $perSlider);
                },
            ],
        ];

        foreach ($definitions as $def) {
            $items = $def['pick']($allBouquets);
            $slider = $build($def['key'], $def['title'], $def['subtitle'], $def['view_all'], $items);
            if ($slider) {
                $sliders[] = $slider;
            }
        }

        return array_slice($sliders, 0, 6);
    }
}

if (!function_exists('landing_get_location_sliders')) {
    /**
     * Location pages: product-first carousels (FNP/IGP style).
     *
     * @return list<array{key: string, title: string, subtitle: string, view_all: string, html: string}>
     */
    function landing_get_location_sliders(mysqli $conn, array $pageData): array
    {
        require_once __DIR__ . '/location_landing.php';

        $area = '';
        $slug = trim((string) ($pageData['slug'] ?? ''));
        $meta = location_landing_by_slug($slug);
        if ($meta) {
            $area = $meta['area'];
        }
        $areaLabel = $area !== '' ? $area : 'Delhi NCR';
        $occasion = 'Flower delivery in ' . $areaLabel;
        $perSlider = max(4, min(10, (int) ($pageData['slider_items_per_row'] ?? 8)));
        $usedFlowerIds = [];

        $fetch = static function (mysqli $c, string $order, int $limit, ?string $extraWhere = null) use (&$usedFlowerIds): array {
            $exclude = $usedFlowerIds === [] ? '' : ' AND id NOT IN (' . implode(',', array_map('intval', $usedFlowerIds)) . ')';
            $where = 'status = 1' . $exclude;
            if ($extraWhere) {
                $where .= ' AND (' . $extraWhere . ')';
            }
            $sql = "SELECT id, name, slug, image, price, original_price, rating
                    FROM flowers WHERE {$where} ORDER BY {$order} LIMIT " . ($limit + 4);
            $items = [];
            $res = $c->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $items[] = landing_row_to_slider_item($row, 'flower');
                    $usedFlowerIds[] = (int) $row['id'];
                    if (count($items) >= $limit) {
                        break;
                    }
                }
            }
            return $items;
        };

        $definitions = [
            [
                'key' => 'location-best-bouquets',
                'title' => "Best Bouquets for {$areaLabel}",
                'subtitle' => "Top-rated fresh bouquets delivered in {$areaLabel} and nearby areas.",
                'view_all' => '/flowers',
                'items' => $fetch($conn, 'rating DESC, id DESC', $perSlider, landing_bouquet_sql_name_filter()),
            ],
            [
                'key' => 'location-same-day',
                'title' => "Same-Day Delivery in {$areaLabel}",
                'subtitle' => 'Order now for express flower delivery today.',
                'view_all' => '/tag?name=same%20day',
                'items' => $fetch($conn, 'id DESC', $perSlider, "LOWER(COALESCE(tag,'')) LIKE '%same day%' OR LOWER(name) LIKE '%same day%' OR LOWER(name) LIKE '%express%'"),
            ],
            [
                'key' => 'location-roses',
                'title' => "Rose Bouquets in {$areaLabel}",
                'subtitle' => 'Classic red roses and romantic mixes for every occasion.',
                'view_all' => '/tag?name=rose',
                'items' => $fetch($conn, 'rating DESC, id DESC', $perSlider, "LOWER(name) LIKE '%rose%' AND " . landing_bouquet_sql_name_filter()),
            ],
        ];

        $sliders = [];
        foreach ($definitions as $def) {
            $items = array_slice($def['items'], 0, $perSlider);
            if (count($items) < 2) {
                continue;
            }
            $html = landing_render_carousel_cards($items, $occasion);
            if ($html === '') {
                continue;
            }
            $sliders[] = [
                'key' => $def['key'],
                'title' => $def['title'],
                'subtitle' => $def['subtitle'],
                'view_all' => $def['view_all'],
                'html' => $html,
            ];
        }

        return array_slice($sliders, 0, 3);
    }
}

if (!function_exists('landing_get_occasion_product_sliders')) {
    /**
     * @return list<array{key: string, title: string, subtitle: string, view_all: string, html: string}>
     */
    function landing_get_occasion_product_sliders(mysqli $conn, array $pageData): array
    {
        if (($pageData['slider_mode'] ?? '') === 'bouquets_only') {
            return landing_get_bouquet_only_sliders($conn, $pageData);
        }

        if (($pageData['slider_mode'] ?? '') === 'location') {
            return landing_get_location_sliders($conn, $pageData);
        }

        return [];
    }
}
