<?php
// actions/setup_promo.php

// Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'promo_codes'");
if ($tableCheck->num_rows == 0) {
    // Table doesn't exist, create it
    $sql = "CREATE TABLE `promo_codes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(50) NOT NULL UNIQUE,
        `discount_type` ENUM('fixed', 'percent') NOT NULL DEFAULT 'fixed',
        `discount_value` DECIMAL(10,2) NOT NULL,
        `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
        `description` text,
        `status` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        // Insert sample
        $conn->query("INSERT INTO promo_codes (code, discount_type, discount_value, description) VALUES ('SAVE10', 'percent', 10.00, 'Save 10% on your order!')");
        $conn->query("INSERT INTO promo_codes (code, discount_type, discount_value, description) VALUES ('FLAT50', 'fixed', 50.00, 'Flat ₹50 Off')");
    }
} else {
    // Table exists, check for discount_value column
    $colCheck = $conn->query("SHOW COLUMNS FROM `promo_codes` LIKE 'discount_value'");
    if ($colCheck->num_rows == 0) {
        // Column missing, this might be the old table. Let's update it.
        // We'll rename the old one (backup) and create new, or just ALTER.
        // ALTER is riskier if schema is vastly different. Let's try adding columns.
        $conn->query("ALTER TABLE `promo_codes` ADD COLUMN `discount_type` ENUM('fixed', 'percent') NOT NULL DEFAULT 'fixed' AFTER `code`");
        $conn->query("ALTER TABLE `promo_codes` ADD COLUMN `discount_value` DECIMAL(10,2) NOT NULL AFTER `discount_type`");
        $conn->query("ALTER TABLE `promo_codes` ADD COLUMN `min_order_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `discount_value`");
        
        // Update any existing rows logic if needed, or leave defaults.
    }
}
?>
