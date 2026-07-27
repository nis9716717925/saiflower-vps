<?php
/**
 * Routes /flowers/{slug} to either a flower-type collection landing or a product PDP.
 */
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug'], '/') : '';
if ($slug === '') {
    header('Location: /flowers', true, 302);
    exit;
}

require_once __DIR__ . '/includes/collection_taxonomy.php';

if (collection_is_flower_type_slug($slug)) {
    $_GET['kind'] = 'flower';
    $_GET['slug'] = strtolower($slug);
    require __DIR__ . '/collection.php';
    exit;
}

require __DIR__ . '/flower-detail.php';
