<?php
/**
 * Regenerate static /sitemap.xml on the server (CLI or browser with key).
 * Usage (SSH): php tools/generate_sitemap.php
 * Or visit: /tools/generate_sitemap.php?key=YOUR_SECRET
 */
define('SKIP_SESSION', true);

$isCli = (PHP_SAPI === 'cli');
$secret = 'saiflower-sitemap-regen';

if (!$isCli) {
    $key = $_GET['key'] ?? '';
    if (!hash_equals($secret, $key)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/sitemap_helper.php';

mysqli_report(MYSQLI_REPORT_OFF);

$xml = build_sitemap_xml_string($conn);
$target = dirname(__DIR__) . '/sitemap.xml';

if (file_put_contents($target, $xml) === false) {
    if ($isCli) {
        fwrite(STDERR, "Failed to write $target\n");
        exit(1);
    }
    http_response_code(500);
    exit('Failed to write sitemap.xml');
}

$count = substr_count($xml, '<url>');
$msg = "Wrote sitemap.xml with {$count} URLs (" . strlen($xml) . " bytes)\n";

if ($isCli) {
    echo $msg;
    exit(0);
}

header('Content-Type: text/plain; charset=utf-8');
echo $msg;
