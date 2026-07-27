-- Local preview database (verification only — not used in production)
CREATE DATABASE IF NOT EXISTS `u977002836_Saiflower999` CHARACTER SET utf8mb4;
CREATE USER IF NOT EXISTS 'u977002836_Saiflower999'@'localhost' IDENTIFIED BY 'Saiflower999';
GRANT ALL PRIVILEGES ON `u977002836_Saiflower999`.* TO 'u977002836_Saiflower999'@'localhost';
FLUSH PRIVILEGES;

USE `u977002836_Saiflower999`;

CREATE TABLE IF NOT EXISTS settings (
  id INT PRIMARY KEY,
  site_title VARCHAR(255) DEFAULT 'Sai Flower',
  whatsapp VARCHAR(50) DEFAULT '8802004527',
  phone VARCHAR(50) DEFAULT '8802004527',
  email VARCHAR(120) DEFAULT 'info@saiflowers.com',
  footer_about TEXT,
  theme_primary VARCHAR(20) DEFAULT '#2f6f4e',
  theme_secondary VARCHAR(20) DEFAULT '#d4af37',
  theme_bg_color VARCHAR(20) DEFAULT '#ffffff',
  theme_text_color VARCHAR(20) DEFAULT '#2c3e50',
  theme_font VARCHAR(60) DEFAULT 'Inter',
  theme_animation VARCHAR(30) DEFAULT 'none'
);
INSERT IGNORE INTO settings (id, footer_about) VALUES (1, 'Handcrafting beautiful moments since 1998.');

CREATE TABLE IF NOT EXISTS flowers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  slug VARCHAR(255),
  image VARCHAR(255),
  price DECIMAL(10,2) DEFAULT 0,
  original_price DECIMAL(10,2) DEFAULT 0,
  rating DECIMAL(3,1) DEFAULT 0,
  in_stock TINYINT DEFAULT 1,
  tag VARCHAR(255) DEFAULT '',
  status TINYINT DEFAULT 1,
  category_ids VARCHAR(255) DEFAULT ''
);

INSERT INTO flowers (name, slug, image, price, original_price, rating, tag, category_ids) VALUES
('Premium Red Rose Bouquet', 'premium-red-rose-bouquet-luxury-romantic-fresh-flower-arrangement', 'uploads/sections/img_69b00d7d6b073_img69a6aa4b1d253WhatsAppImage20260303at23112PM.webp', 999, 1599, 4.8, 'same day,love', ',3,'),
('Elegant White Rose Bouquet', 'elegant-white-rose-bouquet-premium-fresh-white-roses-luxury-flower-arrangement', 'uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp', 699, 999, 4.7, 'same day', ',3,'),
('Sage Sunflower Bloom Luxe Bouquet', 'sage-sunflower-bloom-luxe-bouquet', 'uploads/sections/img_69c123f37096e_Screenshot20260313001120SamsungNotes.webp', 2199, 2999, 4.9, 'birthday', ',3,'),
('Ocean Blue Orchid Grand Bouquet', 'ocean-blue-orchid-grand-bouquet', 'uploads/sections/img_69c1251e2fe60_Screenshot20260313000251SamsungNotes.webp', 5199, 9899, 4.9, 'anniversary', ',4,'),
('Sweet Pink Tulip Bloom Bouquet', 'sweet-pink-tulip-bloom-bouquet', 'uploads/sections/img_69c12599c780c_Screenshot20260316171527SamsungNotes.webp', 3999, 8999, 4.6, 'wedding', ',4,'),
('Midnight White Lily Grace Bouquet', 'midnight-white-lily-grace-bouquet', 'uploads/sections/img_69c12a490d272_img69b555470c87eWhatsAppImage20260314at34432PM.webp', 2399, 3599, 4.8, 'midnight express', ',4,'),
('Mint White Lily Elegance Bouquet', 'mint-white-lily-elegance-bouquet', 'uploads/sections/img_69c12c0007f57_img69b55761a78f2WhatsAppImage20260314at34904PM.webp', 2199, 3299, 4.7, 'same day', ',3,'),
('Pink Blush Baby Rose Bouquet', 'pink-blush-baby-rose-bouquet', 'uploads/sections/img_69c12ca4d6812_img69b5447deae89WhatsAppImage20260314at31518PM.webp', 2199, 2999, 4.5, 'birthday', ',3,'),
('Luxury 100 Red Roses Bouquet', 'luxury-100-red-roses-bouquet-premium-grand-romantic-flower-arrangement', 'uploads/sections/img_69c127d60bae3_img69a6d18fd207dChatGPTImageMar32026054446PM.webp', 9999, 12999, 5.0, 'anniversary,love', ',4,'),
('White Daisy Bouquet with Red Rose', 'white-daisy-bouquet-with-red-rose-elegant-romantic-flower-arrangement', 'uploads/sections/img_69c12c6960068_img69a6d49308275ChatGPTImageMar32026055603PM.webp', 2799, 4500, 4.6, 'same day express', ',3,'),
('Chic 8 Red Rose Bouquet', 'chic-8-red-rose-bouquet-in-contrast-crimson-white-wrap', 'uploads/sections/img_69b00371393da_img69a2e6bd0f223WhatsAppImage20260228at62503PM1.webp', 999, 1299, 4.7, 'same day', ',3,'),
('Premium Purple Orchid Bouquet', 'premium-purple-orchid-bouquet-luxury-fresh-flower-arrangement', 'uploads/sections/img_69b0083c48b2a_img69a6abb280a5fWhatsAppImage20260303at23509PM.webp', 1899, 2899, 4.8, 'orchid', ',4,');

CREATE TABLE IF NOT EXISTS cakes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255), slug VARCHAR(255), image VARCHAR(255),
  price DECIMAL(10,2) DEFAULT 0, status TINYINT DEFAULT 1
);

CREATE TABLE IF NOT EXISTS gifts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255), slug VARCHAR(255), image VARCHAR(255),
  price DECIMAL(10,2) DEFAULT 0, status TINYINT DEFAULT 1
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255), status TINYINT DEFAULT 1, sort_order INT DEFAULT 0
);
INSERT IGNORE INTO categories (id, name) VALUES (3, 'Birthday'), (4, 'Anniversary');

CREATE TABLE IF NOT EXISTS dynamic_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255), slug VARCHAR(255), status TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS seo_meta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_identifier VARCHAR(255), title VARCHAR(255), description TEXT, keywords TEXT
);
