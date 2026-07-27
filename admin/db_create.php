<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once __DIR__ . '/auth_check.php';

echo "<h2>Database Update</h2>";

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM dynamic_pages LIKE 'short_description'");
if ($check && $check->num_rows > 0) {
    echo "<p style='color:green;'>Column 'short_description' already exists in 'dynamic_pages' table!</p>";
} else {
    // Add column
    $alter = $conn->query("ALTER TABLE dynamic_pages ADD COLUMN short_description TEXT AFTER title");
    if ($alter) {
        echo "<p style='color:green;'>Successfully added 'short_description' column to 'dynamic_pages' table!</p>";
    } else {
        echo "<p style='color:red;'>Error adding column: " . $conn->error . "</p>";
    }
}

echo "<br><a href='pages.php'>Go back to pages</a>";
?>
