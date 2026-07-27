<?php
require_once __DIR__ . '/../config.php';

echo "Adding indexes...\n";

$queries = [
    "ALTER TABLE `dynamic_pages` ADD INDEX `idx_slug_status` (`slug`, `status`)",
    "ALTER TABLE `flowers` ADD INDEX `idx_slug` (`slug`)",
    "ALTER TABLE `cakes` ADD INDEX `idx_slug` (`slug`)",
    "ALTER TABLE `gifts` ADD INDEX `idx_slug` (`slug`)",
    "ALTER TABLE `events` ADD INDEX `idx_slug` (`slug`)",
    // index.php optimization
    "ALTER TABLE `homepage_sections` ADD INDEX `idx_status_sort` (`status`, `sort_order`)",
    "ALTER TABLE `homepage_section_items` ADD INDEX `idx_section_sort` (`section_id`, `sort_order`)",
    "ALTER TABLE `reviews` ADD INDEX `idx_status_created` (`status`, `created_at`)"
];

foreach ($queries as $q) {
    try {
        if ($conn->query($q)) {
            echo "Success: $q\n";
        } else {
            echo "Failed or exists: $q - " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        echo "Exception for $q: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
