<?php
/**
 * Smoke-check collection landing routing helpers and URL map consistency.
 */
require dirname(__DIR__) . '/includes/collection_taxonomy.php';
require dirname(__DIR__) . '/includes/occasion_links.php';

$failures = 0;
function assert_true(bool $cond, string $msg): void
{
    global $failures;
    if ($cond) {
        echo "OK  {$msg}\n";
    } else {
        echo "FAIL {$msg}\n";
        $failures++;
    }
}

assert_true(collection_is_flower_type_slug('roses'), 'roses is flower type');
assert_true(!collection_is_flower_type_slug('red-rose-hand-bouquet'), 'random product not flower type');
assert_true(collection_get('flower', 'roses') !== null, 'get roses');
assert_true(collection_get('relation', 'mother') !== null, 'get mother');
assert_true(collection_get('occasion', 'birthday') !== null, 'get birthday');
assert_true(collection_get('collection', 'best-sellers') !== null, 'get best-sellers');
assert_true(collection_url('flower', 'roses') === '/flowers/roses', 'roses url');
assert_true(collection_url('relation', 'wife') === '/relation/wife', 'wife url');
assert_true(collection_url('occasion', 'anniversary') === '/occasion/anniversary', 'anniversary url');
assert_true(flower_type_link_for_label('Red Roses') === '/flowers/roses', 'fav flower label');
assert_true(resolve_occasion_slug_destination('mothers-day') === '/occasion/mothers-day', 'mothers-day map');
assert_true(resolve_occasion_slug_destination('valentines-day') === '/occasion/valentines-day', 'valentines map');

$expectedMin = 50;
assert_true(count(collection_list()) >= $expectedMin, 'at least 50 landings');

echo PHP_EOL . ($failures === 0 ? "ALL CHECKS PASSED\n" : "{$failures} CHECK(S) FAILED\n");
exit($failures === 0 ? 0 : 1);
