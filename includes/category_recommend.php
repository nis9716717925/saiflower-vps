<?php
/**
 * Shared helpers for category pages: stock status + bouquet recommendations.
 */

require_once __DIR__ . '/landing_page_sliders.php';
require_once __DIR__ . '/url_helper.php';

if (!function_exists('category_product_is_in_stock')) {
    function category_product_is_in_stock(array $row): bool
    {
        if (array_key_exists('in_stock', $row)) {
            return (int) $row['in_stock'] === 1;
        }
        if (array_key_exists('stock', $row)) {
            return (int) $row['stock'] > 0;
        }
        if (array_key_exists('quantity', $row)) {
            return (int) $row['quantity'] > 0;
        }
        return true;
    }
}

if (!function_exists('category_stock_summary')) {
    /**
     * @param list<array<string, mixed>> $items
     * @return array{status:string,label:string,message:string,in_stock:int,total:int}
     */
    function category_stock_summary(array $items, string $label = 'items'): array
    {
        $total = count($items);
        if ($total === 0) {
            return [
                'status' => 'available_soon',
                'label' => 'Available Soon',
                'message' => 'We’re refreshing this collection. Fresh flower bouquets are ready to order now.',
                'in_stock' => 0,
                'total' => 0,
            ];
        }

        $inStock = 0;
        foreach ($items as $row) {
            if (category_product_is_in_stock($row)) {
                $inStock++;
            }
        }

        if ($inStock === 0) {
            return [
                'status' => 'out_of_stock',
                'label' => 'Out of Stock',
                'message' => 'These ' . $label . ' are currently out of stock. Explore handcrafted bouquets while we restock.',
                'in_stock' => 0,
                'total' => $total,
            ];
        }

        if ($inStock < $total) {
            return [
                'status' => 'limited',
                'label' => 'Limited Stock',
                'message' => $inStock . ' of ' . $total . ' ' . $label . ' available — grab yours, or browse flower favourites below.',
                'in_stock' => $inStock,
                'total' => $total,
            ];
        }

        return [
            'status' => 'in_stock',
            'label' => 'In Stock',
            'message' => '',
            'in_stock' => $inStock,
            'total' => $total,
        ];
    }
}

if (!function_exists('category_fetch_recommend_bouquets')) {
    /**
     * @param list<string>|null $keywords
     * @return list<array<string, mixed>>
     */
    function category_fetch_recommend_bouquets(mysqli $conn, int $limit = 12, ?array $keywords = null): array
    {
        $extra = null;
        if ($keywords !== null && $keywords !== []) {
            $parts = [];
            foreach ($keywords as $kw) {
                $kw = preg_replace('/[^a-z0-9\\s\\-]/i', '', (string) $kw);
                $kw = trim($kw);
                if ($kw === '') {
                    continue;
                }
                $safe = mysqli_real_escape_string($conn, $kw);
                $parts[] = "name LIKE '%{$safe}%'";
            }
            if ($parts !== []) {
                $extra = '(' . implode(' OR ', $parts) . ')';
            }
        }

        $items = landing_fetch_bouquets($conn, null, $limit, 'rating DESC, id DESC', [], $extra);
        if (count($items) < max(6, (int) floor($limit / 2))) {
            $more = landing_fetch_bouquets($conn, null, $limit, 'rating DESC, id DESC');
            $seen = [];
            foreach ($items as $row) {
                $seen[(int) ($row['id'] ?? 0)] = true;
            }
            foreach ($more as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id && isset($seen[$id])) {
                    continue;
                }
                $items[] = $row;
                $seen[$id] = true;
                if (count($items) >= $limit) {
                    break;
                }
            }
        }

        foreach ($items as &$row) {
            $row['type'] = 'flower';
        }
        unset($row);

        return array_slice($items, 0, $limit);
    }
}

if (!function_exists('category_recommend_copy')) {
    /**
     * @return array{title:string,sub:string}
     */
    function category_recommend_copy(string $pageKey = 'general'): array
    {
        $map = [
            'cakes' => [
                'title' => 'You may also love these flower bouquets',
                'sub' => 'Fresh blooms pair perfectly with celebrations — same-day delivery across Delhi NCR.',
            ],
            'gifts' => [
                'title' => 'Recommendation: go for a fresh bouquet',
                'sub' => 'Thoughtful, ready-to-gift flowers while we expand the gift range.',
            ],
            'gallery' => [
                'title' => 'Inspired? Shop these bouquet looks',
                'sub' => 'Turn gallery favourites into real gifts — handcrafted and delivered today.',
            ],
            'events' => [
                'title' => 'Also check these celebration bouquets',
                'sub' => 'Need flowers for the occasion while you explore events? Start here.',
            ],
            'personalized' => [
                'title' => 'You may also check these bouquets',
                'sub' => 'Personalised keepsakes are coming — flowers make a beautiful gift right now.',
            ],
            'general' => [
                'title' => 'You may also love these bouquets',
                'sub' => 'Handpicked flower arrangements ready for same-day delivery.',
            ],
        ];

        return $map[$pageKey] ?? $map['general'];
    }
}
