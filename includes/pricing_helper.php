<?php
// includes/pricing_helper.php

// 1. Auto-Migration: Ensure the global_pricing table exists
function init_global_pricing_table($conn) {
    $checkTable = $conn->query("SHOW TABLES LIKE 'global_pricing'");
    if ($checkTable && $checkTable->num_rows == 0) {
        $conn->query("CREATE TABLE `global_pricing` (
            `id` int(11) NOT NULL PRIMARY KEY,
            `surge_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
            `flower_surge` decimal(5,2) NOT NULL DEFAULT '0.00',
            `cake_surge` decimal(5,2) NOT NULL DEFAULT '0.00',
            `gift_surge` decimal(5,2) NOT NULL DEFAULT '0.00'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $conn->query("INSERT IGNORE INTO `global_pricing` (id, surge_percentage, flower_surge, cake_surge, gift_surge) VALUES (1, 0.00, 0.00, 0.00, 0.00)");
    }
    
    // Safety check for existing table to add new columns if they don't exist
    $res = $conn->query("SHOW COLUMNS FROM global_pricing LIKE 'flower_surge'");
    if ($res && $res->num_rows == 0) {
        $conn->query("ALTER TABLE global_pricing ADD COLUMN flower_surge DECIMAL(5,2) NOT NULL DEFAULT '0.00'");
        $conn->query("ALTER TABLE global_pricing ADD COLUMN cake_surge DECIMAL(5,2) NOT NULL DEFAULT '0.00'");
        $conn->query("ALTER TABLE global_pricing ADD COLUMN gift_surge DECIMAL(5,2) NOT NULL DEFAULT '0.00'");
    }

    // Create log table if missing
    $conn->query("CREATE TABLE IF NOT EXISTS `pricing_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `category` varchar(50) NOT NULL,
        `percentage` decimal(5,2) NOT NULL,
        `action` varchar(50) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Ensure the table is ready
if (isset($conn)) {
    init_global_pricing_table($conn);
    
    global $global_surges;
    $global_surges = [
        'all'    => 1.00,
        'flower' => 1.00,
        'cake'   => 1.00,
        'gift'   => 1.00,
        'addon'  => 1.00
    ];

    $res = $conn->query("SELECT * FROM global_pricing WHERE id=1 LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $global_surges['all']    = 1 + ((float)$row['surge_percentage'] / 100);
        $global_surges['flower'] = 1 + ((float)$row['flower_surge'] / 100);
        $global_surges['cake']   = 1 + ((float)$row['cake_surge'] / 100);
        $global_surges['gift']   = 1 + ((float)$row['gift_surge'] / 100);
        // Addons currently don't have a separate surge in DB, but we could add it later
    }
}

/**
 * Applies the current surge percentage to any given price based on category.
 * 
 * @param float|int|string $basePrice The original price from the database.
 * @param string $category The category (flower, cake, gift, addon, all).
 * @return float The final price multiplied by the active global surge.
 */
function apply_surge_pricing($basePrice, $category = 'all') {
    global $global_surges;
    
    $multiplier = 1.00;
    
    // 1. If we have a specific category active (> 0% surge), use it
    if ($category !== 'all' && isset($global_surges[$category]) && (float)$global_surges[$category] > 1.00) {
        $multiplier = $global_surges[$category];
    } 
    // 2. Otherwise use the global 'all' surge
    elseif (isset($global_surges['all'])) {
        $multiplier = $global_surges['all'];
    }
    
    $price = (float)$basePrice;
    $finalPrice = $price * $multiplier;
    
    // Return rounded up to nearest whole number
    return ceil($finalPrice);
}
?>
