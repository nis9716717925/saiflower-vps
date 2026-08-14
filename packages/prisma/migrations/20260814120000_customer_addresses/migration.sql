-- CreateTable
CREATE TABLE `customer_addresses` (
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `customer_id` INTEGER NOT NULL,
    `recipient_name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NULL,
    `flat_house_no` VARCHAR(100) NOT NULL,
    `apartment_street_locality` VARCHAR(255) NOT NULL,
    `pincode` VARCHAR(10) NOT NULL,
    `address_type` ENUM('Home', 'Work', 'Other') NOT NULL DEFAULT 'Home',
    `is_default` SMALLINT NOT NULL DEFAULT 0,
    `created_at` DATETIME(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
    `updated_at` DATETIME(0) NULL DEFAULT CURRENT_TIMESTAMP(0),

    INDEX `customer_addresses_customer_id_idx`(`customer_id`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- AddForeignKey
ALTER TABLE `customer_addresses` ADD CONSTRAINT `customer_addresses_customer_id_fkey` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
