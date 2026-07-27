<?php
// actions/install_comments_table.php
require_once __DIR__ . '/../config.php';

if ($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_slug VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('approved', 'pending', 'spam') DEFAULT 'approved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $sql)) {
        echo "Table 'comments' created successfully.";
    } else {
        echo "Error creating table: " . mysqli_error($conn);
    }
} else {
    echo "Database connection failed.";
}
?>
