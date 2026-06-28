-- MySQL Schema for Sup Tulang ZZ Order Management System
CREATE DATABASE IF NOT EXISTS `restaurant_db`;
USE `restaurant_db`;

-- 1. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `icon` VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Menu Items Table
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `image_path` VARCHAR(255) DEFAULT NULL,
    `is_available` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_type` ENUM('walk-in', 'online') NOT NULL,
    `table_number` INT DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100) DEFAULT NULL,
    `delivery_address` TEXT DEFAULT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `payment_status` ENUM('pending', 'verified', 'failed') DEFAULT 'pending',
    `payment_receipt` VARCHAR(255) DEFAULT NULL,
    `order_status` ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `menu_item_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data (Insert Categories)
INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'Soups', 'soup'),
(2, 'Noodles', 'noodles'),
(3, 'Rice Dishes', 'rice'),
(4, 'Drinks', 'drinks')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `icon`=VALUES(`icon`);

-- Seed Data (Insert Menu Items)
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_available`) VALUES
(1, 1, 'Sup Tulang (Original)', 'Signature mutton bone marrow soup cooked with rich spices. Served with straws to suck out the delicious marrow.', 18.00, 'soup_tulang.jpg', 1),
(2, 1, 'Sup Daging', 'Aromatic beef soup loaded with tender beef chunks, potatoes, carrots, and topped with crispy fried shallots.', 12.00, 'sup_daging.jpg', 1),
(3, 2, 'Mee Rebus Tulang', 'Famous thick and savory sweet potato gravy served with yellow noodles, mutton bone marrow, boiled egg, and lime.', 15.00, 'mee_rebus_tulang.jpg', 1),
(4, 2, 'Mee Goreng Mamak', 'Spicy wok-fried yellow noodles with tofu, potato cubes, fritters, beansprouts, and beef slices.', 9.00, 'mee_goreng_mamak.jpg', 1),
(5, 3, 'Nasi Goreng Kampung', 'Traditional Malay fried rice stir-fried with crispy anchovies, water spinach (kangkung), and hot bird\'s eye chilies.', 8.50, 'nasi_goreng_kampung.jpg', 1),
(6, 4, 'Teh Tarik', 'Hot, frothy pulled black tea sweet milk beverage. Malaysia\'s national drink.', 3.00, 'teh_tarik.jpg', 1),
(7, 4, 'Sirap Bandung', 'Refreshing rose syrup beverage mixed with sweet condensed milk and served over ice.', 3.50, 'sirap_bandung.jpg', 1),
(8, 4, 'Kopi O', 'Classic hot strong black coffee served sweet without milk.', 2.50, 'kopi_o.jpg', 1)
ON DUPLICATE KEY UPDATE `category_id`=VALUES(`category_id`), `name`=VALUES(`name`), `description`=VALUES(`description`), `price`=VALUES(`price`), `image_path`=VALUES(`image_path`);

-- 5. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('staff', 'admin') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data (Insert Users)
-- Default passwords are 'password123' (hash generated using PHP PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'staff1', '$2y$10$xEDdQyDaP4D32dDaerC89eI3L8pBo/9iPPTwlqEQt8ZO1kjCwiiHy', 'staff'),
(2, 'staff2', '$2y$10$xEDdQyDaP4D32dDaerC89eI3L8pBo/9iPPTwlqEQt8ZO1kjCwiiHy', 'staff'),
(3, 'admin1', '$2y$10$xEDdQyDaP4D32dDaerC89eI3L8pBo/9iPPTwlqEQt8ZO1kjCwiiHy', 'admin')
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`), `role`=VALUES(`role`);
