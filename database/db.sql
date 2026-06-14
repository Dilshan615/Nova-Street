-- Nova Street Database Dump
-- Host: localhost
-- Database: nova_street

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nova_street`
--
CREATE DATABASE IF NOT EXISTS `nova_street` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `nova_street`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) UNIQUE NOT NULL,
  `slug` VARCHAR(50) UNIQUE NOT NULL,
  `description` VARCHAR(255) NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image_url`) VALUES
(1, 'Women\'s Collection', 'women', 'Elegant dresses, tops, and more.', 'assets/img/hero.png'),
(2, 'Men\'s Collection', 'men', 'Sophisticated suits and casual wear.', 'assets/img/men.png'),
(3, 'Kids World', 'kids', 'Playful designs for little ones.', 'assets/img/summer.png'),
(4, 'Luxury Accents', 'accessories', 'Premium details to complete your look.', 'assets/img/accessories.png'),
(5, 'Summer Essence', 'summer', 'Minimalist styling for hot days.', 'assets/img/summer.png'),
(6, 'Arctic Comfort', 'winter', 'Premium warmth and insulation.', 'assets/img/winter.png')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `category_slug` VARCHAR(50) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `description` TEXT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `color` VARCHAR(50) NULL,
  `size` VARCHAR(50) NULL,
  `qty_in_stock` INT DEFAULT 10,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_slug`, `price`, `description`, `image_url`, `color`, `size`, `qty_in_stock`) VALUES
(1, 'Linen Summer Dress', 'women', 120.00, 'Minimalist light cotton wear.', 'assets/img/summer.png', 'Light White', 'S, M, L', 10),
(2, 'Slim Fit Blazer', 'men', 250.00, 'Professional charcoal wool.', 'assets/img/men.png', 'Midnight Blue', 'M, L, XL', 10),
(3, 'Silk Minimalist Top', 'women', 85.00, 'Smooth Mulberry crepe silk top.', 'assets/img/hero.png', 'Cream', 'XS, S, M', 10),
(4, 'Wool Blend Coat', 'winter', 400.00, 'Premium cashmere and wool insulation.', 'assets/img/winter.png', 'Charcoal', 'M, L', 10),
(5, 'Leather Crossbody Bag', 'accessories', 180.00, 'Hand-crafted tan leather bag.', 'assets/img/accessories.png', 'Tan', 'One Size', 10),
(6, 'Cotton Relaxed Pants', 'men', 95.00, 'Relaxed fit organic cotton trousers.', 'assets/img/summer.png', 'Olive', 'S, M, L', 10)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `contact` VARCHAR(20) NOT NULL,
  `gender` VARCHAR(15) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `district` VARCHAR(100) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(30) UNIQUE NOT NULL,
  `items_json` TEXT NOT NULL,
  `user_id` INT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `promo_code` VARCHAR(50) DEFAULT NULL,
  `payment_status` ENUM('pending', 'paid') DEFAULT 'pending',
  `delivery_status` ENUM('processing', 'shipped', 'delivered') DEFAULT 'processing',
  `estimated_delivery_days` INT DEFAULT 3,
  `delivery_district` VARCHAR(100) DEFAULT NULL,
  `delivery_address` TEXT DEFAULT NULL,
  `payhere_payment_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE IF NOT EXISTS `promo_codes` (
  `promo_id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) UNIQUE NOT NULL,
  `type` ENUM('percentage', 'fixed', 'free_shipping') NOT NULL DEFAULT 'percentage',
  `value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`code`, `type`, `value`, `status`) VALUES
('NOVA10', 'percentage', 10.00, 'active'),
('NOVA20', 'percentage', 20.00, 'active'),
('NOVA30', 'percentage', 30.00, 'active'),
('FREESHIP', 'free_shipping', 0.00, 'active')
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_rates`
--

CREATE TABLE IF NOT EXISTS `shipping_rates` (
  `rate_id` INT AUTO_INCREMENT PRIMARY KEY,
  `district` VARCHAR(100) UNIQUE NOT NULL,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `delivery_days` INT NOT NULL DEFAULT 3,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `shipping_rates`
--

INSERT INTO `shipping_rates` (`district`, `shipping_fee`, `delivery_days`) VALUES
('Colombo', 200.00, 1),
('Kandy', 400.00, 2),
('Galle', 350.00, 2),
('Jaffna', 600.00, 4),
('Other', 500.00, 5)
ON DUPLICATE KEY UPDATE `district` = VALUES(`district`);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'Nova Street'),
('contact_email', 'support@novastreet.com'),
('contact_phone', '+94 11 234 5678'),
('store_address', 'No. 88, Galle Road, Colombo 03'),
('payhere_merchant_id', '1222410'),
('payhere_secret', 'NDI0MTczNTE5NzQyNTk5OTQ4ODczNzA3NzI1ODc3NjQyNDcyMzM='),
('smtp_username', 'dilshan0763126293@gmail.com'),
('smtp_password', 'heqi qcfe bstk ijez')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` INT NOT NULL,
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  UNIQUE KEY (`product_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
