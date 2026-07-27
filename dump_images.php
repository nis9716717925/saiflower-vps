<?php
require 'config.php';
$res = $conn->query("SELECT id, name, image FROM flowers WHERE name LIKE '%Canopy%' OR name LIKE '%Aura of Love%'");
$output = "";
while ($row = $res->fetch_assoc()) {
    $output .= "ID: " . $row['id'] . "\nName: " . $row['name'] . "\nImage: " . $row['image'] . "\n\n";
}
file_put_contents('image_debug.txt', $output);
echo "done";
