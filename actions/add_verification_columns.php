<?php
// actions/add_verification_columns.php
require_once __DIR__ . '/../config.php';

// 1. Add is_verified column
$sql1 = "ALTER TABLE customers ADD COLUMN is_verified TINYINT(1) DEFAULT 0";
if ($conn->query($sql1) === TRUE) {
    echo "Column 'is_verified' added successfully.<br>";
} else {
    echo "Error adding 'is_verified': " . $conn->error . "<br>"; // Likely duplicates if run twice
}

// 2. Add verification_token column
$sql2 = "ALTER TABLE customers ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL";
if ($conn->query($sql2) === TRUE) {
    echo "Column 'verification_token' added successfully.<br>";
} else {
    echo "Error adding 'verification_token': " . $conn->error . "<br>";
}

// 3. Mark existing users as verified (Optional but recommended)
$sql3 = "UPDATE customers SET is_verified = 1 WHERE is_verified = 0 AND verification_token IS NULL";
if ($conn->query($sql3) === TRUE) {
    echo "Existing users marked as verified.<br>";
} else {
    echo "Error updating existing users: " . $conn->error . "<br>";
}

echo "Database migration completed.";
?>
