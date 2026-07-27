<?php
/**
 * Shop merchandising — flower-first product classification, scoring, filters, sections.
 */

require_once __DIR__ . '/url_helper.php';
require_once __DIR__ . '/collection_taxonomy.php';

if (!function_exists('shop_is_decoration_product')) {
    function shop_is_decoration_product(array $row): bool
    {
        $hay = mb_strtolower(trim(($row['name'] ?? '') . ' ' . ($row['tag'] ?? '')));
        if ($hay === '') {
            return false;
        }
        $patterns = [
            '/\bcar\s*decor/',
            '/\bwedding\s*decor/',
            '/\bfirst\s*night/',
            '/\broom\s*decor/',
            '/\bstage\s*decor/',
            '/\bevent\s*decor/',
            '/\bdecoration(s)?\b/',
            '/\bdecor\b/',
            '/\bvenue\b/',
            '/\bworkshop\b/',
            '/\bmandap\b/',
            '/\bstage\b/',
            '/\bbackdrop\b/',
            '/\bgarland\s*install/',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $hay)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('shop_is_floral_product')) {
    /** True for bouquets, baskets, boxes, plants, and general flower gifts (not décor services). */
    function shop_is_floral_product(array $row): bool
    {
        if (shop_is_decoration_product($row)) {
            return false;
        }
        $hay = mb_strtolower(trim(($row['name'] ?? '') . ' ' . ($row['tag'] ?? '')));
        $positive = [
            'bouquet', 'flower', 'rose', 'lily', 'lilies', 'orchid', 'carnation',
            'gerbera', 'tulip', 'sunflower', 'basket', 'box', 'arrangement',
            'bloom', 'mixed', 'plant', 'hand bouquet', 'bunch',
        ];
        foreach ($positive as $kw) {
            if (str_contains($hay, $kw)) {
                return true;
            }
        }
        // Default: non-decoration items in flowers table are treated as floral merchandise
        return true;
    }
}

if (!function_exists('shop_price_band_score')) {
    /** Higher = better conversion sweet-spot for Indian gifting. */
    function shop_price_band_score(float $price): int
    {
        if ($price >= 499 && $price <= 799) {
            return 100;
        }
        if ($price > 799 && $price <= 1299) {
            return 95;
        }
        if ($price > 1299 && $price <= 1999) {
            return 88;
        }
        if ($price > 1999 && $price < 2500) {
            return 70;
        }
        if ($price >= 2500 && $price < 4000) {
            return 45;
        }
        if ($price >= 4000) {
            return 25;
        }
        if ($price > 0 && $price < 499) {
            return 60; // budget still useful
        }
        return 40;
    }
}

if (!function_exists('shop_product_score')) {
    /**
     * Default merchandising score. Decorations always score far below floral.
     */
    function shop_product_score(array $row): float
    {
        $price = (float) ($row['price'] ?? 0);
        $rating = (float) ($row['rating'] ?? 0);
        $id = (int) ($row['id'] ?? 0);
        $hay = mb_strtolower(($row['name'] ?? '') . ' ' . ($row['tag'] ?? ''));

        if (shop_is_decoration_product($row)) {
            // Sink decorations; within décor, still prefer rated/newer
            return 5 + min(10, $rating) + ($id % 7) * 0.01;
        }

        $score = 200 + shop_price_band_score($price);

        // Rating / “best selling” proxies
        $score += min(40, $rating * 8);

        if (str_contains($hay, 'best seller') || str_contains($hay, 'bestseller') || str_contains($hay, 'best-selling')) {
            $score += 35;
        }
        if (str_contains($hay, 'trending') || str_contains($hay, 'popular') || str_contains($hay, 'most loved')) {
            $score += 28;
        }
        if (str_contains($hay, 'premium') || str_contains($hay, 'designer')) {
            $score += 12;
        }
        if (str_contains($hay, 'luxury') || str_contains($hay, 'luxe')) {
            $score += 8; // after sweet-spot, not at top by default
        }
        if (str_contains($hay, 'bouquet') || str_contains($hay, 'rose')) {
            $score += 15;
        }
        if (str_contains($hay, 'basket') || str_contains($hay, 'box')) {
            $score += 10;
        }

        // Mild newness boost (higher ids ≈ newer)
        $score += min(20, ($id % 1000) / 50);

        // In stock boost
        if (!isset($row['in_stock']) || (int) $row['in_stock'] === 1) {
            $score += 15;
        } else {
            $score -= 80;
        }

        return $score;
    }
}

if (!function_exists('shop_sort_products')) {
    /**
     * @param list<array<string, mixed>> $products
     * @return list<array<string, mixed>>
     */
    function shop_sort_products(array $products, string $sort = 'bestseller'): array
    {
        $decor = [];
        $floral = [];
        foreach ($products as $p) {
            if (shop_is_decoration_product($p)) {
                $decor[] = $p;
            } else {
                $floral[] = $p;
            }
        }

        $cmpScore = static function ($a, $b): int {
            $sa = shop_product_score($a);
            $sb = shop_product_score($b);
            if ($sa === $sb) {
                return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
            }
            return $sb <=> $sa;
        };

        $cmpRating = static function ($a, $b): int {
            $ra = (float) ($a['rating'] ?? 0);
            $rb = (float) ($b['rating'] ?? 0);
            if ($ra === $rb) {
                return shop_product_score($b) <=> shop_product_score($a);
            }
            return $rb <=> $ra;
        };

        switch ($sort) {
            case 'price_low':
                usort($floral, static fn($a, $b) => ((float) $a['price'] <=> (float) $b['price']) ?: ((int) $b['id'] <=> (int) $a['id']));
                usort($decor, static fn($a, $b) => ((float) $a['price'] <=> (float) $b['price']));
                break;
            case 'price_high':
                usort($floral, static fn($a, $b) => ((float) $b['price'] <=> (float) $a['price']) ?: ((int) $b['id'] <=> (int) $a['id']));
                usort($decor, static fn($a, $b) => ((float) $b['price'] <=> (float) $a['price']));
                break;
            case 'name':
                usort($floral, static fn($a, $b) => strcasecmp((string) $a['name'], (string) $b['name']));
                usort($decor, static fn($a, $b) => strcasecmp((string) $a['name'], (string) $b['name']));
                break;
            case 'rating':
                usort($floral, $cmpRating);
                usort($decor, $cmpRating);
                break;
            case 'newest':
            case 'new':
                usort($floral, static fn($a, $b) => ((int) $b['id'] <=> (int) $a['id']));
                usort($decor, static fn($a, $b) => ((int) $b['id'] <=> (int) $a['id']));
                break;
            case 'trending':
                usort($floral, static function ($a, $b) {
                    $ha = mb_strtolower(($a['name'] ?? '') . ' ' . ($a['tag'] ?? ''));
                    $hb = mb_strtolower(($b['name'] ?? '') . ' ' . ($b['tag'] ?? ''));
                    $ta = (str_contains($ha, 'trend') || str_contains($ha, 'popular') ? 30 : 0) + shop_product_score($a);
                    $tb = (str_contains($hb, 'trend') || str_contains($hb, 'popular') ? 30 : 0) + shop_product_score($b);
                    return $tb <=> $ta;
                });
                usort($decor, $cmpScore);
                break;
            case 'bestseller':
            default:
                usort($floral, $cmpScore);
                usort($decor, $cmpScore);
                break;
        }

        // Decorations always after floral unless sorting only decorations via filter
        return array_merge($floral, $decor);
    }
}

if (!function_exists('shop_product_matches_filters')) {
    function shop_product_matches_filters(array $row, array $filters): bool
    {
        $name = mb_strtolower((string) ($row['name'] ?? ''));
        $tag = mb_strtolower((string) ($row['tag'] ?? ''));
        $hay = $name . ' ' . $tag;
        $price = (float) ($row['price'] ?? 0);
        $rating = (float) ($row['rating'] ?? 0);

        if (!empty($filters['price_min']) && $price < (float) $filters['price_min']) {
            return false;
        }
        if (!empty($filters['price_max']) && $price > (float) $filters['price_max']) {
            return false;
        }

        if (!empty($filters['category'])) {
            $catId = (int) $filters['category'];
            $cids = (string) ($row['category_ids'] ?? '');
            if ($catId > 0 && !str_contains($cids, ',' . $catId . ',')) {
                return false;
            }
        }

        if (!empty($filters['flower_type'])) {
            $map = [
                'roses' => ['rose', 'roses'],
                'lilies' => ['lily', 'lilies'],
                'sunflowers' => ['sunflower'],
                'orchids' => ['orchid'],
                'carnations' => ['carnation'],
                'gerberas' => ['gerbera'],
                'tulips' => ['tulip'],
                'mixed' => ['mixed', 'assorted'],
            ];
            $key = $filters['flower_type'];
            $kws = $map[$key] ?? [$key];
            $ok = false;
            foreach ($kws as $kw) {
                if (str_contains($hay, $kw)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                return false;
            }
        }

        if (!empty($filters['occasion'])) {
            $occ = str_replace('-', ' ', mb_strtolower((string) $filters['occasion']));
            if (!str_contains($hay, $occ) && !str_contains($hay, str_replace(' ', '', $occ))) {
                // Allow category/tag loose match words
                $parts = preg_split('/\s+/', $occ) ?: [];
                $hit = false;
                foreach ($parts as $part) {
                    if (strlen($part) > 3 && str_contains($hay, $part)) {
                        $hit = true;
                        break;
                    }
                }
                if (!$hit) {
                    return false;
                }
            }
        }

        if (!empty($filters['relation'])) {
            $rel = mb_strtolower((string) $filters['relation']);
            $aliases = [
                'mother' => ['mother', 'mom', 'mummy'],
                'father' => ['father', 'dad'],
                'wife' => ['wife'],
                'husband' => ['husband'],
                'girlfriend' => ['girlfriend', 'romantic', 'love'],
                'boyfriend' => ['boyfriend'],
                'her' => ['her', 'wife', 'girlfriend', 'mom', 'mother'],
                'him' => ['him', 'husband', 'boyfriend', 'dad', 'father'],
            ];
            $kws = $aliases[$rel] ?? [$rel];
            $ok = false;
            foreach ($kws as $kw) {
                if (str_contains($hay, $kw)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                return false;
            }
        }

        if (!empty($filters['color'])) {
            $color = mb_strtolower((string) $filters['color']);
            if (!str_contains($hay, $color)) {
                return false;
            }
        }

        if (!empty($filters['delivery'])) {
            if ($filters['delivery'] === 'same_day') {
                $same = !isset($row['delivery_sameday']) || (int) $row['delivery_sameday'] === 1;
                if (!$same && !str_contains($hay, 'same day') && !str_contains($hay, 'express')) {
                    return false;
                }
            }
            if ($filters['delivery'] === 'midnight') {
                if (!str_contains($hay, 'midnight') && empty($row['delivery_midnight'])) {
                    return false;
                }
            }
        }

        if (!empty($filters['badge'])) {
            $b = $filters['badge'];
            if ($b === 'premium' && !str_contains($hay, 'premium') && !str_contains($hay, 'designer') && $price < 1999) {
                return false;
            }
            if ($b === 'bestseller' && !str_contains($hay, 'best') && ($rating < 4.5)) {
                // allow high-rated as proxy
                if ($rating < 4.2) {
                    return false;
                }
            }
            if ($b === 'trending' && !str_contains($hay, 'trend') && !str_contains($hay, 'popular') && $rating < 4.3) {
                return false;
            }
        }

        if (!empty($filters['rating_min']) && $rating < (float) $filters['rating_min']) {
            return false;
        }

        if (isset($filters['in_stock']) && $filters['in_stock'] !== '' && $filters['in_stock'] !== null) {
            $want = (int) $filters['in_stock'];
            $have = isset($row['in_stock']) ? (int) $row['in_stock'] : 1;
            if ($have !== $want) {
                return false;
            }
        }

        if (!empty($filters['product_group'])) {
            if ($filters['product_group'] === 'flowers' && shop_is_decoration_product($row)) {
                return false;
            }
            if ($filters['product_group'] === 'decorations' && !shop_is_decoration_product($row)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('shop_fetch_all_active_flowers')) {
    /**
     * @return list<array<string, mixed>>
     */
    function shop_fetch_all_active_flowers(mysqli $conn): array
    {
        $rows = [];
        $res = $conn->query('SELECT * FROM flowers WHERE status = 1');
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}

if (!function_exists('shop_apply_filters_and_sort')) {
    /**
     * @param list<array<string, mixed>> $products
     * @return list<array<string, mixed>>
     */
    function shop_apply_filters_and_sort(array $products, array $filters, string $sort): array
    {
        $out = array_values(array_filter($products, static fn($p) => shop_product_matches_filters($p, $filters)));
        return shop_sort_products($out, $sort);
    }
}

if (!function_exists('shop_build_sections')) {
    /**
     * Browse-mode collection rails. Decorations always last.
     *
     * @param list<array<string, mixed>> $products already filtered+sorted catalog
     * @return list<array{key:string,title:string,subtitle:string,items:list,href:string}>
     */
    function shop_build_sections(array $products): array
    {
        $floral = array_values(array_filter($products, static fn($p) => !shop_is_decoration_product($p)));
        $decor = array_values(array_filter($products, 'shop_is_decoration_product'));

        $byRating = $floral;
        usort($byRating, static fn($a, $b) => ((float) ($b['rating'] ?? 0) <=> (float) ($a['rating'] ?? 0)));

        $byNew = $floral;
        usort($byNew, static fn($a, $b) => ((int) $b['id'] <=> (int) $a['id']));

        $under = static function (array $list, float $max) {
            return array_values(array_filter($list, static fn($p) => (float) $p['price'] <= $max));
        };
        $between = static function (array $list, float $min, float $max) {
            return array_values(array_filter($list, static fn($p) => (float) $p['price'] >= $min && (float) $p['price'] <= $max));
        };
        $premium = array_values(array_filter($floral, static function ($p) {
            $hay = mb_strtolower(($p['name'] ?? '') . ' ' . ($p['tag'] ?? ''));
            $price = (float) ($p['price'] ?? 0);
            return $price >= 1999 || str_contains($hay, 'premium') || str_contains($hay, 'designer');
        }));
        $luxury = array_values(array_filter($floral, static function ($p) {
            $hay = mb_strtolower(($p['name'] ?? '') . ' ' . ($p['tag'] ?? ''));
            $price = (float) ($p['price'] ?? 0);
            return $price >= 2499 || str_contains($hay, 'luxury') || str_contains($hay, 'luxe');
        }));
        $budget = $under($floral, 999);
        $seasonal = array_values(array_filter($floral, static function ($p) {
            $hay = mb_strtolower(($p['name'] ?? '') . ' ' . ($p['tag'] ?? ''));
            return str_contains($hay, 'season') || str_contains($hay, 'tulip') || str_contains($hay, 'sunflower') || str_contains($hay, 'festival');
        }));

        $take = static fn(array $list, int $n) => array_slice($list, 0, $n);

        $defs = [
            ['key' => 'best-selling', 'title' => 'Best Selling Flowers', 'subtitle' => 'Customer favourites — always in demand', 'items' => $take($floral, 10), 'href' => '/collection/best-sellers'],
            ['key' => 'trending', 'title' => 'Trending This Week', 'subtitle' => 'What Delhi NCR is ordering right now', 'items' => $take(shop_sort_products($floral, 'trending'), 10), 'href' => '/collection/trending-flowers'],
            ['key' => 'most-loved', 'title' => 'Most Loved Bouquets', 'subtitle' => 'Highest rated arrangements from our studio', 'items' => $take($byRating, 10), 'href' => '/flowers.php?sort=rating'],
            ['key' => 'under-999', 'title' => 'Flowers Under ₹999', 'subtitle' => 'Thoughtful blooms without the stretch', 'items' => $take($budget, 10), 'href' => '/collection/budget-flowers'],
            ['key' => 'under-1499', 'title' => 'Flowers Under ₹1499', 'subtitle' => 'The sweet spot for birthdays & thank-yous', 'items' => $take($under($floral, 1499), 10), 'href' => '/flowers.php?price_min=0&price_max=1499&sort=bestseller'],
            ['key' => 'under-1999', 'title' => 'Flowers Under ₹1999', 'subtitle' => 'Premium feel, popular price', 'items' => $take($under($floral, 1999), 10), 'href' => '/flowers.php?price_min=0&price_max=1999&sort=bestseller'],
            ['key' => 'new', 'title' => 'New Arrivals', 'subtitle' => 'Fresh from our florist studio', 'items' => $take($byNew, 10), 'href' => '/collection/new-arrivals'],
            ['key' => 'budget', 'title' => 'Budget Friendly Picks', 'subtitle' => '₹499–₹1299 bestsellers', 'items' => $take($between($floral, 499, 1299), 10), 'href' => '/flowers.php?price_min=499&price_max=1299&sort=bestseller'],
            ['key' => 'premium', 'title' => 'Premium Collection', 'subtitle' => 'Designer bouquets for special moments', 'items' => $take(shop_sort_products($premium, 'bestseller'), 10), 'href' => '/collection/premium-bouquets'],
            ['key' => 'luxury', 'title' => 'Luxury Collection', 'subtitle' => 'Statement blooms for unforgettable gifting', 'items' => $take(shop_sort_products($luxury, 'bestseller'), 10), 'href' => '/collection/luxury-flowers'],
            ['key' => 'seasonal', 'title' => 'Seasonal Flowers', 'subtitle' => 'In-season stems and festive favourites', 'items' => $take($seasonal ?: $take($floral, 8), 10), 'href' => '/occasion/festivals'],
            ['key' => 'decorations', 'title' => 'Decoration Services', 'subtitle' => 'Car, wedding, room & event floral décor', 'items' => $take($decor, 12), 'href' => '/flowers.php?product_group=decorations&sort=bestseller'],
        ];

        // Drop empty rails (except we already fall back seasonal). Still skip truly empty.
        return array_values(array_filter($defs, static fn($s) => count($s['items']) > 0));
    }
}

if (!function_exists('shop_parse_request_filters')) {
    function shop_parse_request_filters(array $get): array
    {
        return [
            'price_min' => isset($get['price_min']) && $get['price_min'] !== '' ? (int) $get['price_min'] : null,
            'price_max' => isset($get['price_max']) && $get['price_max'] !== '' ? (int) $get['price_max'] : null,
            'category' => isset($get['category']) && $get['category'] !== '' ? (int) $get['category'] : null,
            'flower_type' => isset($get['flower_type']) ? preg_replace('/[^a-z\-]/', '', strtolower((string) $get['flower_type'])) : '',
            'occasion' => isset($get['occasion']) ? preg_replace('/[^a-z\-]/', '', strtolower((string) $get['occasion'])) : '',
            'relation' => isset($get['relation']) ? preg_replace('/[^a-z\-]/', '', strtolower((string) $get['relation'])) : '',
            'color' => isset($get['color']) ? preg_replace('/[^a-z]/', '', strtolower((string) $get['color'])) : '',
            'delivery' => isset($get['delivery']) ? preg_replace('/[^a-z_]/', '', strtolower((string) $get['delivery'])) : '',
            'badge' => isset($get['badge']) ? preg_replace('/[^a-z]/', '', strtolower((string) $get['badge'])) : '',
            'rating_min' => isset($get['rating_min']) && $get['rating_min'] !== '' ? (float) $get['rating_min'] : null,
            'in_stock' => array_key_exists('in_stock', $get) && $get['in_stock'] !== '' ? (int) $get['in_stock'] : null,
            'product_group' => isset($get['product_group']) ? preg_replace('/[^a-z]/', '', strtolower((string) $get['product_group'])) : '',
        ];
    }
}

if (!function_exists('shop_has_active_filters')) {
    function shop_has_active_filters(array $filters): bool
    {
        foreach ($filters as $v) {
            if ($v === null || $v === '') {
                continue;
            }
            return true;
        }
        return false;
    }
}

if (!function_exists('shop_review_count_estimate')) {
    function shop_review_count_estimate(array $row): int
    {
        if (!empty($row['review_count'])) {
            return (int) $row['review_count'];
        }
        $rating = (float) ($row['rating'] ?? 4.5);
        $id = (int) ($row['id'] ?? 1);
        // Stable pseudo count for UI when DB has no review_count column
        return 12 + (($id * 7) % 180) + (int) round($rating * 3);
    }
}

if (!function_exists('shop_discount_percent')) {
    function shop_discount_percent(array $row): int
    {
        $price = (float) (function_exists('apply_surge_pricing') ? apply_surge_pricing($row['price'], 'flower') : $row['price']);
        $original = (float) ($row['original_price'] ?? 0);
        if ($original > $price && $original > 0) {
            return (int) round((($original - $price) / $original) * 100);
        }
        return 0;
    }
}

if (!function_exists('shop_order_categories_flower_first')) {
    /**
     * @param list<array<string, mixed>> $categories
     * @return list<array<string, mixed>>
     */
    function shop_order_categories_flower_first(array $categories): array
    {
        $decor = [];
        $floral = [];
        foreach ($categories as $cat) {
            $n = mb_strtolower((string) ($cat['name'] ?? ''));
            if (preg_match('/decor|car|wedding décor|wedding decor|event|stage|first night|room decor/i', $n)) {
                $decor[] = $cat;
            } else {
                $floral[] = $cat;
            }
        }
        return array_merge($floral, $decor);
    }
}

if (!function_exists('shop_quick_filter_chips')) {
    /**
     * @return list<array{label:string,href:string,active:bool}>
     */
    function shop_quick_filter_chips(array $filters, string $sort): array
    {
        $mk = static function (string $label, array $params) use ($filters, $sort): array {
            $q = array_merge(['sort' => $sort], $params);
            // Drop empties
            $q = array_filter($q, static fn($v) => $v !== null && $v !== '');
            $active = true;
            foreach ($params as $k => $v) {
                $cur = $filters[$k] ?? ($k === 'sort' ? $sort : null);
                if ((string) $cur !== (string) $v) {
                    $active = false;
                    break;
                }
            }
            return [
                'label' => $label,
                'href' => '/flowers.php?' . http_build_query($q),
                'active' => $active,
            ];
        };

        return [
            $mk('All Flowers', ['product_group' => 'flowers']),
            $mk('Same Day', ['delivery' => 'same_day']),
            $mk('Under ₹999', ['price_min' => 0, 'price_max' => 999]),
            $mk('₹799–₹1299', ['price_min' => 799, 'price_max' => 1299]),
            $mk('Roses', ['flower_type' => 'roses']),
            $mk('Premium', ['badge' => 'premium']),
            $mk('Best Sellers', ['badge' => 'bestseller', 'sort' => 'bestseller']),
            $mk('New', ['sort' => 'newest']),
            $mk('Decorations', ['product_group' => 'decorations']),
        ];
    }
}
