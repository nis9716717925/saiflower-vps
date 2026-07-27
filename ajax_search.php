<?php
/**
 * Live search suggestions — products + celebration/collection shortcuts.
 */
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/collection_taxonomy.php';
require_once __DIR__ . '/includes/celebrations_calendar_data.php';
require_once __DIR__ . '/includes/occasion_links.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$results = [];
$queryLen = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);

if ($query !== '' && $queryLen >= 1) {
    $param = '%' . $query . '%';
    $prefix = $query . '%';
    $qLower = function_exists('mb_strtolower') ? mb_strtolower($query) : strtolower($query);
    $seenHref = [];

    $likeTables = [
        ['flowers', 'flower'],
        ['cakes', 'cake'],
        ['gifts', 'gift'],
    ];

    foreach ($likeTables as [$table, $type]) {
        $sql = "SELECT id, name, slug, image
                FROM {$table}
                WHERE name LIKE ? OR IFNULL(slug, '') LIKE ?
                ORDER BY
                  CASE
                    WHEN name LIKE ? THEN 0
                    WHEN IFNULL(slug, '') LIKE ? THEN 1
                    ELSE 2
                  END,
                  name ASC
                LIMIT 4";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            continue;
        }
        $stmt->bind_param('ssss', $param, $param, $prefix, $prefix);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['type'] = $type;
            $results[] = $row;
        }
        $stmt->close();
    }

    foreach (collection_list() as $entry) {
        $hay = function_exists('mb_strtolower')
            ? mb_strtolower(($entry['title'] ?? '') . ' ' . ($entry['slug'] ?? '') . ' ' . ($entry['h1'] ?? ''))
            : strtolower(($entry['title'] ?? '') . ' ' . ($entry['slug'] ?? '') . ' ' . ($entry['h1'] ?? ''));
        $found = function_exists('mb_strpos') ? (mb_strpos($hay, $qLower) !== false) : (strpos($hay, $qLower) !== false);
        if ($hay === '' || !$found) {
            continue;
        }
        $href = (string) ($entry['canonical_path'] ?? '');
        if ($href === '' || isset($seenHref[$href])) {
            continue;
        }
        $seenHref[$href] = true;
        $results[] = [
            'id' => 0,
            'name' => $entry['title'] ?? $entry['h1'] ?? 'Collection',
            'slug' => $entry['slug'] ?? '',
            'image' => $entry['hero_image'] ?? '/uploads/logo_transparent.png',
            'type' => 'collection',
            'link' => $href,
            'badge' => ucfirst((string) ($entry['kind'] ?? 'shop')),
        ];
        if (count(array_filter($results, static fn ($r) => ($r['type'] ?? '') === 'collection')) >= 3) {
            break;
        }
    }

    foreach (celebrations_calendar_get_items() as $item) {
        $hay = function_exists('mb_strtolower')
            ? mb_strtolower(($item['title'] ?? '') . ' ' . ($item['slug'] ?? '') . ' ' . ($item['date'] ?? ''))
            : strtolower(($item['title'] ?? '') . ' ' . ($item['slug'] ?? '') . ' ' . ($item['date'] ?? ''));
        $found = function_exists('mb_strpos') ? (mb_strpos($hay, $qLower) !== false) : (strpos($hay, $qLower) !== false);
        if (!$found) {
            continue;
        }
        $href = celebrations_calendar_href($item, $conn instanceof mysqli ? $conn : null);
        if (isset($seenHref[$href])) {
            continue;
        }
        $seenHref[$href] = true;
        $results[] = [
            'id' => 0,
            'name' => $item['title'] ?? 'Celebration',
            'slug' => $item['slug'] ?? '',
            'image' => $item['image'] ?? '/uploads/logo_transparent.png',
            'type' => 'occasion',
            'link' => $href,
            'badge' => $item['date'] ?? 'Celebration',
        ];
        if (count(array_filter($results, static fn ($r) => ($r['type'] ?? '') === 'occasion')) >= 3) {
            break;
        }
    }

    if (preg_match('/celebrat|calendar|festiv|occasion/i', $query)) {
        $href = '/celebration-calendar';
        if (!isset($seenHref[$href])) {
            array_unshift($results, [
                'id' => 0,
                'name' => 'Celebrations Calendar',
                'slug' => 'celebration-calendar',
                'image' => '/celebrations/valentines-day.jpg',
                'type' => 'page',
                'link' => $href,
                'badge' => 'Guide',
            ]);
        }
    }

    foreach ($results as &$item) {
        if (empty($item['link'])) {
            if (in_array($item['type'], ['flower', 'cake', 'gift', 'event'], true)) {
                $item['link'] = product_url($item);
            } else {
                $item['link'] = '/search-results?q=' . rawurlencode($query);
            }
        }

        $img = (string) ($item['image'] ?? '');
        if ($img === '') {
            $item['image'] = '/uploads/logo_transparent.png';
        } elseif (!(str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/'))) {
            if (str_starts_with($img, 'uploads/')) {
                $item['image'] = '/' . $img;
            } else {
                $item['image'] = '/uploads/' . ltrim($img, '/');
            }
        }

        $item['name'] = (string) ($item['name'] ?? 'Product');
        $item['type'] = (string) ($item['type'] ?? 'product');
        if (empty($item['badge'])) {
            $item['badge'] = ucfirst($item['type']);
        }
    }
    unset($item);
}

echo json_encode([
    'success' => true,
    'query' => $query,
    'results' => array_values(array_slice($results, 0, 10)),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
