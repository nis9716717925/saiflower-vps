<?php
/**
 * Occasion / celebration slug → destination URL map.
 * Used by celebrations calendar, page.php fallbacks, and CMS link helpers.
 */

if (!function_exists('get_occasion_slug_destinations')) {
    /**
     * @return array<string, string> slug => path (with leading slash)
     */
    function get_occasion_slug_destinations(): array
    {
        require_once __DIR__ . '/collection_taxonomy.php';

        return [
            'fathers-day'            => collection_url('occasion', 'fathers-day'),
            'bouquet-for-fathers-day'=> collection_url('occasion', 'fathers-day'),
            'fathers-day-flowers'    => collection_url('occasion', 'fathers-day'),
            'fathers-day-flowers-online' => collection_url('occasion', 'fathers-day'),
            'new-years-day'          => collection_url('occasion', 'festivals'),
            'rose-day'               => collection_url('flower', 'roses'),
            'propose-day'            => collection_url('occasion', 'love-romance'),
            'chocolate-day'          => '/gifts',
            'teddy-day'              => '/gifts',
            'promise-day'            => collection_url('occasion', 'love-romance'),
            'hug-day'                => collection_url('occasion', 'love-romance'),
            'kiss-day'               => collection_url('occasion', 'love-romance'),
            'valentines-day'         => collection_url('occasion', 'valentines-day'),
            'womens-day'             => collection_url('relation', 'her'),
            'mothers-day'            => collection_url('occasion', 'mothers-day'),
            'doctors-day'            => collection_url('occasion', 'thank-you'),
            'friendship-day'         => collection_url('relation', 'friends'),
            'raksha-bandhan'         => collection_url('relation', 'sister'),
            'rakhi'                  => collection_url('relation', 'sister'),
            'teachers-day'           => collection_url('occasion', 'thank-you'),
            'grandparents-day'       => collection_url('relation', 'grandmother'),
            'janmashtami'            => collection_url('occasion', 'festivals'),
            'wife-appreciation-day'  => collection_url('relation', 'wife'),
            'karwa-chauth'           => collection_url('occasion', 'festivals'),
            'dhanteras'              => collection_url('occasion', 'festivals'),
            'diwali'                 => collection_url('occasion', 'festivals'),
            'childrens-day'          => collection_url('relation', 'kids'),
            'bhai-dooj'              => collection_url('relation', 'brother'),
            'mens-day'               => collection_url('relation', 'him'),
            'christmas'              => collection_url('occasion', 'festivals'),
            'make-every-birthday-special-with-sai-flower' => '/make-every-birthday-special-with-sai-flower',
            'celebration-calendar'   => '/celebration-calendar',
            'celebrations-calendar'  => '/celebration-calendar',
            'celebrations'           => '/celebration-calendar',
        ];
    }
}

if (!function_exists('resolve_occasion_slug_destination')) {
    /**
     * Resolve a celebration/occasion slug to a working internal URL.
     * Returns null when the slug should be handled by dynamic_pages / built-in landings as-is.
     */
    function resolve_occasion_slug_destination(string $slug, ?mysqli $conn = null): ?string
    {
        $slug = trim($slug, '/');
        if ($slug === '') {
            return null;
        }

        $map = get_occasion_slug_destinations();
        if (isset($map[$slug])) {
            return $map[$slug];
        }

        if ($conn instanceof mysqli) {
            $stmt = $conn->prepare('SELECT slug FROM dynamic_pages WHERE slug = ? AND status = 1 LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $slug);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    return '/' . $slug;
                }
            }
        }

        if (function_exists('get_builtin_landing_page_by_slug')) {
            if (get_builtin_landing_page_by_slug($slug) !== null) {
                return '/' . $slug;
            }
        } elseif (file_exists(__DIR__ . '/landing_pages.php')) {
            require_once __DIR__ . '/landing_pages.php';
            if (get_builtin_landing_page_by_slug($slug) !== null) {
                return '/' . $slug;
            }
        }

        return null;
    }
}

if (!function_exists('celebrations_calendar_href')) {
    function celebrations_calendar_href(array $item, ?mysqli $conn = null): string
    {
        $slug = trim((string) ($item['slug'] ?? ''), '/');
        if ($slug === '') {
            return '/flowers';
        }

        $resolved = resolve_occasion_slug_destination($slug, $conn);
        if ($resolved !== null) {
            return $resolved;
        }

        return '/' . $slug;
    }
}

if (!function_exists('flower_type_browse_links')) {
    /**
     * Homepage “Pick Their Fav Flower” category → collection URL.
     *
     * @return array<string, string>
     */
    function flower_type_browse_links(): array
    {
        require_once __DIR__ . '/collection_taxonomy.php';

        return [
            'carnations' => collection_url('flower', 'carnations'),
            'orchids'    => collection_url('flower', 'orchids'),
            'red roses'  => collection_url('flower', 'roses'),
            'roses'      => collection_url('flower', 'roses'),
            'lilies'     => collection_url('flower', 'lilies'),
            'sunflower'  => collection_url('flower', 'sunflowers'),
            'sunflowers' => collection_url('flower', 'sunflowers'),
            'tulip'      => collection_url('flower', 'tulips'),
            'tulips'     => collection_url('flower', 'tulips'),
            'gerberas'   => collection_url('flower', 'gerberas'),
            'gerbera'    => collection_url('flower', 'gerberas'),
        ];
    }
}

if (!function_exists('flower_type_link_for_label')) {
    function flower_type_link_for_label(string $label): ?string
    {
        require_once __DIR__ . '/collection_taxonomy.php';
        $slug = collection_label_to_flower_slug($label);
        if ($slug !== null) {
            return collection_url('flower', $slug);
        }
        $key = strtolower(trim($label));
        $map = flower_type_browse_links();
        return $map[$key] ?? null;
    }
}
