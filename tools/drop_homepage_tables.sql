-- Optional: run once after deploying static homepage to remove unused CMS tables.
-- Backup your database before executing.

DROP TABLE IF EXISTS `homepage_section_items`;
DROP TABLE IF EXISTS `homepage_sections`;
DROP TABLE IF EXISTS `homepage_circles`;
DROP TABLE IF EXISTS `homepage_slides`;
