<?php
require_once __DIR__ . '/../config.php';

$tables = ['homepage_section_items', 'homepage_circles', 'homepage_slides'];

foreach ($tables as $table) {
    echo "--- $table ---\n";
    $query = $conn->query("SELECT id, link FROM $table WHERE link IS NOT NULL AND link != ''");
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $link = $row['link'];
            $id = $row['id'];
            echo "ID: $id | Link: $link\n";
        }
    }
}
?>
