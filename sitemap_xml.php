<?php
/**
 * XML sitemap endpoint for Google Search Console.
 * Served at /sitemap.xml via .htaccess rewrite.
 */
define('SKIP_SESSION', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/sitemap_helper.php';

// Avoid mysqli exceptions aborting mid-stream (PHP 8+ default).
mysqli_report(MYSQLI_REPORT_OFF);

render_sitemap_xml($conn);
