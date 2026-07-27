<?php
require dirname(__DIR__) . '/includes/collection_taxonomy.php';

$total = collection_list();
echo 'TOTAL=' . count($total) . PHP_EOL;
foreach (['flower', 'relation', 'occasion', 'collection'] as $k) {
    echo strtoupper($k) . '=' . count(collection_list($k)) . PHP_EOL;
}
foreach ($total as $p) {
    echo $p['canonical_path'] . "\t" . $p['title'] . PHP_EOL;
}
