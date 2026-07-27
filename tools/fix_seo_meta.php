<?php
/**
 * One-time fix for corrupted seo_meta rows flagged in the technical SEO audit.
 * Run from CLI: php tools/fix_seo_meta.php
 */
require_once __DIR__ . '/../config.php';

$fixes = [
    'flowers.php' => [
        'title' => 'Fresh Flowers & Bouquets Online | Sai Flower Delhi',
        'description' => 'Order fresh flower bouquets online from Sai Flower. Same-day delivery in Delhi. Roses, orchids, wedding & event flowers.',
        'keywords' => 'fresh flowers Delhi, flower delivery, bouquets online, same day delivery, Sai Flower',
    ],
    'contact.php' => [
        'title' => 'Contact Sai Flower | Flower Delivery Delhi | +91 88020 04527',
        'description' => 'Get in touch with Sai Flower for flower delivery in Delhi NCR. Call +91 88020 04527, WhatsApp us, or visit our Lodhi Road shop.',
        'keywords' => 'contact Sai Flower, flower delivery Delhi, florist phone number',
    ],
];

foreach ($fixes as $pageId => $seo) {
    $check = $conn->prepare('SELECT id FROM seo_meta WHERE page_identifier = ? LIMIT 1');
    $check->bind_param('s', $pageId);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if ($existing) {
        $stmt = $conn->prepare('UPDATE seo_meta SET title = ?, description = ?, keywords = ? WHERE page_identifier = ?');
        $stmt->bind_param('ssss', $seo['title'], $seo['description'], $seo['keywords'], $pageId);
        $stmt->execute();
        echo "Updated seo_meta for {$pageId}\n";
    } else {
        $stmt = $conn->prepare('INSERT INTO seo_meta (page_identifier, title, description, keywords) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $pageId, $seo['title'], $seo['description'], $seo['keywords']);
        $stmt->execute();
        echo "Inserted seo_meta for {$pageId}\n";
    }
}

echo "Done.\n";
