<?php
/**
 * Location landing page helpers — registry, trust copy, nearby cross-links.
 */

if (!function_exists('location_landing_parse_nearby')) {
    function location_landing_parse_nearby(string $nearby): array
    {
        $parts = preg_split('/\s*,\s*|\s+and\s+/i', $nearby);
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $out[] = $part;
            }
        }
        return $out;
    }
}

if (!function_exists('location_landing_slug_from_area')) {
    function location_landing_slug_from_area(string $area): string
    {
        return 'flower-delivery-in-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($area)), '-');
    }
}

if (!function_exists('location_landing_registry')) {
    /**
     * @return array<string, array{area: string, local: string, nearby: string, region: string}>
     */
    function location_landing_registry(): array
    {
        static $registry = null;
        if ($registry !== null) {
            return $registry;
        }

        $entries = [
            ['GK 1', 'Greater Kailash Part 1', 'GK 2, Kailash Colony, and Nehru Enclave', 'South Delhi'],
            ['GK 2', 'Greater Kailash Part 2', 'GK 1, Chittaranjan Park, and Alaknanda', 'South Delhi'],
            ['Hauz Khas', 'Hauz Khas', 'Green Park, Safdarjung Enclave, and IIT Delhi', 'South Delhi'],
            ['Green Park', 'Green Park', 'Hauz Khas, Gulmohar Park, and Yusuf Sarai', 'South Delhi'],
            ['Saket', 'Saket', 'Malviya Nagar, Hauz Khas, and Pushp Vihar', 'South Delhi'],
            ['Malviya Nagar', 'Malviya Nagar', 'Saket, Hauz Khas, and Begumpur', 'South Delhi'],
            ['Vasant Kunj', 'Vasant Kunj', 'Munirka, Mahipalpur, and Rangpuri', 'South Delhi'],
            ['Mehrauli', 'Mehrauli', 'Chattarpur, Vasant Kunj, and Qutub Minar area', 'South Delhi'],
            ['Chattarpur', 'Chattarpur', 'Mehrauli, Vasant Kunj, and Chhatarpur Extension', 'South Delhi'],
            ['CR Park', 'Chittaranjan Park', 'Kalkaji, Nehru Place, and GK 2', 'South Delhi'],
            ['Kalkaji', 'Kalkaji', 'Nehru Place, CR Park, and Okhla', 'South Delhi'],
            ['Nehru Place', 'Nehru Place', 'Kalkaji, CR Park, and East of Kailash', 'South Delhi'],
            ['Jor Bagh', 'Jor Bagh', 'Lodhi Road, Safdarjung, and INA Colony', 'South Delhi'],
            ['Lodhi Road', 'Lodhi Road', 'Jor Bagh, Safdarjung, and Khan Market', 'South Delhi'],
            ['Safdarjung', 'Safdarjung', 'AIIMS, Green Park, and Hauz Khas', 'South Delhi'],
            ['AIIMS', 'AIIMS', 'Safdarjung, Green Park, and Ansari Nagar', 'South Delhi'],
            ['Panchsheel', 'Panchsheel Park', 'Chirag Delhi, Sheikh Sarai, and Hauz Khas', 'South Delhi'],
            ['Gulmohar Park', 'Gulmohar Park', 'Green Park, Yusuf Sarai, and Hauz Khas', 'South Delhi'],
            ['SDA', 'Safdarjung Development Area', 'Hauz Khas, Green Park, and IIT Delhi', 'South Delhi'],
            ['Lajpat Nagar', 'Lajpat Nagar', 'Amar Colony, Jungpura, and Kotla Mubarakpur', 'South Delhi'],
            ['Greater Kailash', 'Greater Kailash', 'Kailash Colony, Chittaranjan Park, and East of Kailash', 'South Delhi'],
            ['Connaught Place', 'Connaught Place', 'Barakhamba Road, Janpath, and Mandi House', 'Central Delhi'],
            ['Karol Bagh', 'Karol Bagh', 'Paharganj, Rajendra Place, and Patel Nagar', 'Central Delhi'],
            ['Dwarka', 'Dwarka', 'Janakpuri, Uttam Nagar, and Palam', 'West Delhi'],
            ['Rohini', 'Rohini', 'Pitampura, Burari, and Prashant Vihar', 'North West Delhi'],
            ['Sector 18 Noida', 'Sector 18 Noida', 'Sector 16, Atta Market, and Botanical Garden', 'Noida'],
            ['Sector 62 Noida', 'Sector 62 Noida', 'Sector 60, Fortis Hospital area, and Noida City Centre', 'Noida'],
            ['Gurgaon', 'Gurgaon', 'DLF Phase 1, Cyber City, and Sohna Road', 'Gurgaon'],
        ];

        $registry = [];
        foreach ($entries as [$area, $local, $nearby, $region]) {
            $slug = location_landing_slug_from_area($area);
            $registry[$slug] = [
                'area' => $area,
                'local' => $local,
                'nearby' => $nearby,
                'region' => $region,
            ];
        }

        return $registry;
    }
}

if (!function_exists('location_landing_by_slug')) {
    function location_landing_by_slug(string $slug): ?array
    {
        $slug = trim($slug, '/');
        $registry = location_landing_registry();
        return $registry[$slug] ?? null;
    }
}

if (!function_exists('location_landing_nearby_links')) {
    /**
     * @return list<array{label: string, href: string}>
     */
    function location_landing_nearby_links(string $slug, ?mysqli $conn = null): array
    {
        $meta = location_landing_by_slug($slug);
        if ($meta === null) {
            return [];
        }

        $registry = location_landing_registry();
        $labels = location_landing_parse_nearby($meta['nearby']);
        $links = [];

        foreach ($labels as $label) {
            $candidateSlug = location_landing_slug_from_area($label);
            $href = '/' . $candidateSlug;

            if ($conn instanceof mysqli) {
                $stmt = $conn->prepare('SELECT slug FROM dynamic_pages WHERE slug = ? AND status = 1 LIMIT 1');
                if ($stmt) {
                    $stmt->bind_param('s', $candidateSlug);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows === 0) {
                        $href = '/flowers';
                    }
                }
            } elseif (!isset($registry[$candidateSlug])) {
                continue;
            }

            $links[] = [
                'label' => $label,
                'href' => $href,
            ];
        }

        return $links;
    }
}

if (!function_exists('location_landing_default_h1')) {
    function location_landing_default_h1(array $meta): string
    {
        return 'Flower Delivery in ' . ($meta['area'] ?? 'Delhi NCR');
    }
}

if (!function_exists('location_landing_default_intro')) {
    function location_landing_default_intro(array $meta): string
    {
        $area = $meta['area'] ?? 'Delhi NCR';
        return "Order fresh flowers in {$area} with same-day delivery across Delhi NCR. Premium bouquets, roses &amp; gifts from Sai Flower — trusted florists since 1998.";
    }
}

if (!function_exists('location_landing_meta_title')) {
    function location_landing_meta_title(array $meta): string
    {
        $area = $meta['area'] ?? 'Delhi NCR';
        return "Flower Delivery in {$area} | Same Day | Sai Flower";
    }
}

if (!function_exists('location_landing_meta_description')) {
    function location_landing_meta_description(array $meta): string
    {
        $area = $meta['area'] ?? 'Delhi NCR';
        $local = $meta['local'] ?? $area;
        return "Order fresh flower delivery in {$area} with same-day service across {$local}. Roses, bouquets & gifts from Sai Flower. Shop online today.";
    }
}

if (!function_exists('location_landing_apply_defaults')) {
    /**
     * Enrich dynamic_pages row for location landing presentation + SEO.
     *
     * @param array<string, mixed> $pageData
     * @return array<string, mixed>
     */
    function location_landing_apply_defaults(array $pageData, string $slug): array
    {
        $meta = location_landing_by_slug($slug);
        if ($meta === null) {
            return $pageData;
        }

        if (empty($pageData['h1'])) {
            $pageData['h1'] = location_landing_default_h1($meta);
        }
        if (empty(trim($pageData['short_description'] ?? ''))) {
            $pageData['short_description'] = location_landing_default_intro($meta);
        }
        if (empty($pageData['products_section_heading'])) {
            $pageData['products_section_heading'] = 'Popular Bouquets for ' . $meta['area'];
        }
        if (empty($pageData['faq_section_heading'])) {
            $pageData['faq_section_heading'] = 'Flower Delivery in ' . $meta['area'] . ' — FAQs';
        }
        if (!isset($pageData['enable_product_sliders']) || (int) $pageData['enable_product_sliders'] === 0) {
            $pageData['enable_product_sliders'] = 0;
        }
        $pageData['hide_product_grid'] = 0;
        if (empty($pageData['slider_mode'])) {
            $pageData['slider_mode'] = 'location';
        }
        if (empty($pageData['robots'])) {
            $pageData['robots'] = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        }

        return $pageData;
    }
}

if (!function_exists('location_landing_fetch_fallback_products')) {
    /**
     * Top-rated bouquets when tag-based grid is empty on location pages.
     * Never includes car/first-night décor.
     *
     * @return list<array<string, mixed>>
     */
    function location_landing_fetch_fallback_products(mysqli $conn, int $limit = 40): array
    {
        require_once __DIR__ . '/landing_page_sliders.php';
        require_once __DIR__ . '/collection_landing.php';

        $limit = max(30, min(40, $limit));
        return collection_fetch_bouquet_backfill($conn, $limit, []);
    }
}

if (!function_exists('location_product_filter_meta')) {
    /**
     * @return list<string>
     */
    function location_product_filter_meta(array $item): array
    {
        $name = mb_strtolower(trim($item['name'] ?? ''));
        $tag = mb_strtolower(trim($item['tag'] ?? ''));
        $price = (float) ($item['price'] ?? 0);
        $tags = ['all'];

        if (strpos($name, 'rose') !== false) {
            $tags[] = 'rose';
        }
        if (strpos($name, 'orchid') !== false) {
            $tags[] = 'orchid';
        }
        if (strpos($name, 'lily') !== false || strpos($name, 'lilies') !== false) {
            $tags[] = 'lily';
        }
        if (strpos($name, 'sunflower') !== false) {
            $tags[] = 'sunflower';
        }
        if ($price > 0 && $price <= 999) {
            $tags[] = 'under-999';
        }
        if ($price >= 1499) {
            $tags[] = 'premium';
        }
        if (
            strpos($name, 'same day') !== false
            || strpos($name, 'express') !== false
            || strpos($tag, 'same day') !== false
            || strpos($tag, 'express') !== false
        ) {
            $tags[] = 'same-day';
        }

        return array_values(array_unique($tags));
    }
}
