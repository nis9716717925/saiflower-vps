<?php
require dirname(__DIR__) . '/includes/landing_page_sliders.php';
require dirname(__DIR__) . '/includes/collection_landing.php';

$failures = 0;
function assert_true(bool $ok, string $msg): void
{
    global $failures;
    echo ($ok ? 'OK  ' : 'FAIL ') . $msg . PHP_EOL;
    if (!$ok) {
        $failures++;
    }
}

assert_true(landing_is_bouquet_product(['name' => 'Red Rose Hand Bouquet', 'tag' => 'rose']), 'rose bouquet ok');
assert_true(landing_is_bouquet_product(['name' => 'Mixed Flower Basket', 'tag' => '']), 'flower basket ok');
assert_true(!landing_is_bouquet_product(['name' => 'Car Decorations Premium', 'tag' => 'car']), 'car blocked');
assert_true(!landing_is_bouquet_product(['name' => 'First Night Room Decor', 'tag' => 'first night']), 'first night blocked');
assert_true(!landing_is_bouquet_product(['name' => 'Wedding Stage Decoration', 'tag' => 'decor']), 'stage blocked');
assert_true(!landing_is_bouquet_product(['name' => 'Luxury Bouquet Car Decor Pack', 'tag' => '']), 'bouquet+car blocked');

// DB-backed fill check when config available
$config = dirname(__DIR__) . '/config.php';
if (is_file($config)) {
    require $config;
    if (isset($conn) && $conn instanceof mysqli) {
        $fake = [
            'kind' => 'relation',
            'slug' => 'colleagues',
            'title' => 'Colleagues',
            'filter' => [
                'tables' => ['flowers'],
                'name_keywords' => ['zzz-no-match-keyword'],
                'tags' => ['zzz-no-match-tag'],
                'match' => 'any',
            ],
        ];
        $items = collection_fetch_products($conn, $fake, 40, 36);
        assert_true(count($items) >= 30, 'sparse landing padded to >=30 (got ' . count($items) . ')');
        $allBouquets = true;
        foreach ($items as $item) {
            if (!landing_is_bouquet_product($item)) {
                $allBouquets = false;
                echo '  non-bouquet: ' . ($item['name'] ?? '') . PHP_EOL;
                break;
            }
        }
        assert_true($allBouquets, 'every filled item is a bouquet');
    } else {
        echo "SKIP DB pad check (no mysqli conn)\n";
    }
}

echo PHP_EOL . ($failures === 0 ? "ALL CHECKS PASSED\n" : "{$failures} FAILED\n");
exit($failures === 0 ? 0 : 1);
