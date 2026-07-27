<?php
require dirname(__DIR__) . '/includes/shop_merchandising.php';

$failures = 0;
function assert_true(bool $ok, string $msg): void
{
    global $failures;
    echo ($ok ? 'OK  ' : 'FAIL ') . $msg . PHP_EOL;
    if (!$ok) {
        $failures++;
    }
}

$bouquet = ['id' => 10, 'name' => 'Red Rose Hand Bouquet', 'tag' => 'rose,bestseller', 'price' => 899, 'rating' => 4.8, 'in_stock' => 1];
$premium = ['id' => 11, 'name' => 'Luxury Orchid Bouquet', 'tag' => 'luxury', 'price' => 4200, 'rating' => 4.9, 'in_stock' => 1];
$decor = ['id' => 12, 'name' => 'Car Decoration Package', 'tag' => 'car decor', 'price' => 1500, 'rating' => 5.0, 'in_stock' => 1];
$weddingDecor = ['id' => 13, 'name' => 'Wedding Stage Decoration', 'tag' => 'wedding decor', 'price' => 999, 'rating' => 4.9, 'in_stock' => 1];

assert_true(shop_is_floral_product($bouquet), 'bouquet is floral');
assert_true(shop_is_decoration_product($decor), 'car decor detected');
assert_true(shop_is_decoration_product($weddingDecor), 'wedding decor detected');
assert_true(!shop_is_decoration_product($bouquet), 'bouquet not decor');
assert_true(shop_product_score($bouquet) > shop_product_score($premium), 'sweet-spot beats ultra luxury');
assert_true(shop_product_score($bouquet) > shop_product_score($decor), 'bouquet beats decor score');

$sorted = shop_sort_products([$decor, $premium, $bouquet, $weddingDecor], 'bestseller');
assert_true(!shop_is_decoration_product($sorted[0]), 'first item is floral');
assert_true(shop_is_decoration_product($sorted[count($sorted) - 1]), 'last item is decor');

$filtered = shop_apply_filters_and_sort(
    [$bouquet, $premium, $decor],
    shop_parse_request_filters(['flower_type' => 'roses']),
    'bestseller'
);
assert_true(count($filtered) === 1 && str_contains(strtolower($filtered[0]['name']), 'rose'), 'rose filter works');

$sections = shop_build_sections([$bouquet, $premium, $decor, $weddingDecor]);
$keys = array_column($sections, 'key');
assert_true(in_array('best-selling', $keys, true), 'has best-selling section');
$last = $sections[count($sections) - 1]['key'] ?? '';
assert_true($last === 'decorations', 'decorations section is last');

echo PHP_EOL . ($failures === 0 ? "ALL CHECKS PASSED\n" : "{$failures} FAILED\n");
exit($failures === 0 ? 0 : 1);
