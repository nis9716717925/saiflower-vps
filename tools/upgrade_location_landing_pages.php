<?php
/**
 * Upgrade existing flower-delivery-in-* landing pages to competitor-grade SEO + copy.
 * Run once: https://saiflower.com/tools/upgrade_location_landing_pages.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/location_landing.php';

$updated = 0;
$skipped = 0;

$res = $conn->query("SELECT id, slug, title, meta_title, meta_description, short_description FROM dynamic_pages WHERE slug LIKE 'flower-delivery-in-%' AND status = 1");
if (!$res) {
    die("Query failed: " . $conn->error . "\n");
}

$update = $conn->prepare(
    'UPDATE dynamic_pages SET title = ?, short_description = ?, meta_title = ?, meta_description = ? WHERE id = ?'
);

while ($row = $res->fetch_assoc()) {
    $slug = $row['slug'];
    $meta = location_landing_by_slug($slug);

    if ($meta === null) {
        $area = ucwords(str_replace('-', ' ', preg_replace('/^flower-delivery-in-/', '', $slug)));
        $meta = ['area' => $area, 'local' => $area];
    }

    $title = 'Flower Delivery in ' . $meta['area'];
    $short_description = strip_tags(location_landing_default_intro($meta));
    $meta_title = location_landing_meta_title($meta);
    $meta_description = location_landing_meta_description($meta);
    $id = (int) $row['id'];

    if (
        ($row['meta_title'] ?? '') === $meta_title
        && ($row['meta_description'] ?? '') === $meta_description
        && ($row['short_description'] ?? '') === $short_description
    ) {
        echo "SKIP  /{$slug}\n";
        $skipped++;
        continue;
    }

    $update->bind_param('ssssi', $title, $short_description, $meta_title, $meta_description, $id);
    if ($update->execute()) {
        echo "OK    /{$slug}\n";
        $updated++;
    } else {
        echo "FAIL  /{$slug} — {$conn->error}\n";
    }
}

echo "\nDone. Updated: {$updated}, Skipped: {$skipped}\n";
