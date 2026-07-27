<?php
require_once __DIR__ . '/../config.php';

$tables = ['flowers', 'cakes', 'gifts', 'events'];

foreach ($tables as $table) {
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'faqs'");
    if ($check && $check->num_rows > 0) {
        echo "Column 'faqs' already exists in '$table' table.\n";
    } else {
        // Add column
        $alter = $conn->query("ALTER TABLE `$table` ADD COLUMN faqs TEXT DEFAULT NULL");
        if ($alter) {
            echo "Successfully added 'faqs' column to '$table' table.\n";
        } else {
            echo "Error adding column to '$table': " . $conn->error . "\n";
        }
    }
}
?>
