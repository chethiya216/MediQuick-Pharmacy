-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 10, 2026 at 07:20 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mediquick_pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `customer_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(2, 2, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(3, 3, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(4, 4, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(5, 5, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(6, 6, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(7, 7, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(8, 8, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(9, 9, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(10, 10, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(11, 11, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(12, 12, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(13, 13, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(14, 14, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(15, 15, '2026-08-27 16:23:46', '2026-08-27 08:55:58'),
(16, 0, '2026-09-09 19:28:36', '2026-09-09 19:28:36');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_item_id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_item_id`),
  UNIQUE KEY `uq_cart_product` (`cart_id`,`product_id`),
  KEY `fk_ci_product` (`product_id`),
  KEY `idx_cart_items_cart` (`cart_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(2, 1, 10, 2, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(3, 2, 2, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(4, 3, 5, 2, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(5, 4, 13, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(6, 5, 7, 3, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(7, 6, 11, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(8, 7, 12, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(9, 8, 4, 2, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(10, 9, 6, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(11, 10, 2, 2, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(12, 11, 14, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(13, 12, 10, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(14, 13, 1, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46'),
(15, 14, 3, 1, '2026-08-27 16:23:46', '2026-08-27 16:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Pain Relief & Analgesics', 'Medications for relieving pain and inflammation', 'inactive', '2026-08-27 16:23:47', '2026-08-30 11:59:56'),
(2, 'Antibiotics & Anti-infectives', 'Prescription products to treat bacterial infections', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(3, 'Cardiovascular Health', 'Medications for heart health, cholesterol, and blood pressure', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(4, 'Vitamins & Supplements', 'Dietary, vitamin, and mineral supplements', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(5, 'Respiratory & Allergy', 'Antihistamines, decongestants, and asthma treatments', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(6, 'Gastrointestinal Health', 'Treatments for digestion, acid reflux, and nausea', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(7, 'Dermatological Care', 'Topical creams, ointments, and skin treatments', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(8, 'Diabetes Management', 'Insulin, test strips, and blood sugar control products', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(9, 'Mental Health & Neurology', 'Antidepressants, anti-anxiety, and neurological care', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(10, 'First Aid & Wound Care', 'Bandages, antiseptics, and wound management supplies', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(11, 'Eye & Ear Care', 'Drops, washes, and treatments for vision and hearing care', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(12, 'Baby & Maternal Health', 'Infant formulas, prenatal vitamins, and care items', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(13, 'Oral Care', 'Toothpastes, mouthwashes, and oral pain relievers', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(14, 'Cold, Flu & Cough', 'Over-the-counter cough syrups and symptom relief', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(15, 'Endocrine & Thyroid', 'Hormone balance and thyroid management therapies', 'active', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(16, 'Test 1', 'dsadd', 'active', '2026-08-30 10:59:53', '2026-08-30 10:59:53'),
(17, 'Test 2', 'dwaddawdaw', 'active', '2026-08-30 11:01:13', '2026-08-30 11:01:13'),
(18, 'Test 3', 'dwadawwd', 'active', '2026-08-30 11:02:13', '2026-08-30 11:02:13'),
(19, 'Test p', 'dwad', 'inactive', '2026-08-30 11:07:49', '2026-08-30 12:01:32'),
(20, 'Test pathh', 'dwad', 'active', '2026-08-30 11:08:32', '2026-08-30 11:08:32'),
(22, 'Sinus', 'this is an edit', 'active', '2026-09-06 17:40:54', '2026-09-06 17:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `received_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_contact_customer` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `customer_id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `received_at`) VALUES
(1, 1, 'John Doe', 'john.doe@example.com', '', 'Order Status Inquiry', 'Hello, I placed order #1024 yesterday and wanted to check when it will be shipped. Thank you!', 'unread', '2026-09-08 03:45:00'),
(2, 2, 'Sarah Smith', 'sarah.smith@example.com', '', 'Damaged Item Received', 'Hi Team, my recent package arrived with a broken outer seal. Can I request a replacement or return?', 'read', '2026-09-05 09:00:22'),
(3, 3, 'Michael Brown', 'mbrown99@example.com', '', 'Account Password Reset', 'I am having trouble resetting my password via email. Could support please assist?', 'replied', '2026-08-28 06:15:10'),
(4, 3, 'Alice Johnson', 'alice.j@gmail.com', '', 'Question About Product Availability', 'Hi, will the wireless mechanical keyboard be back in stock anytime soon?', 'unread', '2026-09-09 12:50:05'),
(5, 2, 'David Wilson', 'dwilson_tech@yahoo.com', '', 'Partnership Request', 'Good day! I represent a local logistics company and would love to discuss potential business collaboration.', 'read', '2026-09-01 10:35:40'),
(6, 1, 'Emily Davis', 'emily.davis@hotmail.com', '', 'Bulk Order Discount', 'Hello, I am looking to purchase 50 units for a company event. Do you offer bulk discounts?', 'replied', '2026-08-20 04:30:00'),
(7, NULL, 'ChethiyaLakshanICBT', 'chethiyalakshanicbt@gmail.com', '22121212', 'dcsd', 'dsads', 'unread', '2026-09-10 07:18:23'),
(8, NULL, 'ChethiyaLakshanICBT', 'chethiyalakshanicbt@gmail.com', '22121212', 'dcsd', 'dsad', 'unread', '2026-09-10 07:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `status` enum('active','inactive','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `remember_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `address`, `date_of_birth`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Doe', 'john.doe@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f1', '555-0101', '123 Elm St, Springfield', '1985-04-12', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(2, 'Jane', 'Smith', 'jane.smith@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f2', '555-0102', '456 Oak Ave, Metropolis', '1990-08-22', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(3, 'Robert', 'Johnson', 'robert.j@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f3', '555-0103', '789 Pine Rd, Gotham', '1978-11-05', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(4, 'Emily', 'Davis', 'emily.davis@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f4', '555-0104', '101 Maple St, Star City', '1995-02-14', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(5, 'Michael', 'Wilson', 'm.wilson@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f5', '555-0111', '202 Birch Ln, Central City', '1982-06-30', 'inactive', NULL, '2026-08-27 08:55:57', '2026-09-06 11:04:49'),
(6, 'Sarah', 'Brown', 'sbrown@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f6', '555-0106', '303 Cedar Blvd, Coast City', '1988-12-19', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(7, 'David', 'Miller', 'dmiller@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f7', '555-0107', '404 Walnut St, Bludhaven', '1970-01-25', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(8, 'Jessica', 'Taylor', 'jtaylor@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f8', '555-0108', '505 Ash Dr, Keystone', '1993-09-08', 'banned', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(9, 'James', 'Anderson', 'j.anderson@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f9', '555-0109', '606 Spruce Ct, Smallville', '1965-03-17', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(10, 'Amanda', 'Thomas', 'athomas@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f10', '555-0110', '707 Vine St, Midvale', '2000-07-04', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(11, 'Daniel', 'Jackson', 'djackson@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f11', '555-0111', '808 Poplar Ave, Freeland', '1989-10-10', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(13, 'Christopher', 'Harris', 'charris@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f13', '555-0113', '111 Cypress Way, Opal City', '1984-09-30', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(14, 'Megan', 'Martin', 'mmartin@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f14', '555-0114', '222 Magnolia St, Hub City', '1997-11-12', 'active', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(15, 'Kevin', 'Thompson', 'kthompson@email.com', '$2b$12$eImiTXuWVxfM37uY4JANjO5E5sD.67f15', '555-0115', '333 Redwood Ln, Midway City', '1976-08-01', 'inactive', NULL, '2026-08-27 08:55:57', '2026-08-27 16:23:47'),
(16, 'Chethiya', 'Lakshan', 'che@gmail.com', '$2y$10$dUXrn4ond8Mvxv8iHwE..ODFH/478H7S31NcFI8x8mEugbpXSkuZ.', '0779872446', 'Majuwana road, Gonapinuwala', '2026-09-08', 'active', NULL, '2026-08-27 10:02:19', '2026-09-08 13:19:28'),
(18, 'Chami', 'Gamage', 'chami@gmail.com', '$2y$10$mFjymgd2wg6cPUwJQmPoyuNL12MWIOB68ytJZMzuuZlFKCJHMQy3a', '0779872446', 'Majuwana', NULL, 'active', NULL, '2026-09-09 09:19:08', '2026-09-09 09:19:08'),
(19, 'Tester', 'Anonymous', 'tester@gmail.com', '$2y$10$N6xb1D5S/X07H5ElmIjgHu.xgVvMghCzRanWmmdhYyMHuAkH2IK3C', '1212121', 'Majuwana', '2026-09-09', 'active', NULL, '2026-09-09 09:36:01', '2026-09-09 09:36:01'),
(20, 'Kavindu', 'Mihiranga', 'kavi@gmail.com', '$2y$10$5i2Z8tyP3MPI6BmVU21vr.08ZLYDj5XMV7VCcr/xIXt1hysBzz6Gq', '07798989766', 'Majuwana', '2026-09-10', 'active', 'ad84015866653ade9270dd5c015acec50443f4adc07ab607cd9b46ab683c71a2', '2026-09-09 09:37:31', '2026-09-09 10:07:56');

-- --------------------------------------------------------

--
-- Stand-in structure for view `customer_ages`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `customer_ages`;
CREATE TABLE IF NOT EXISTS `customer_ages` (
`age` bigint
,`customer_id` int
,`date_of_birth` date
,`first_name` varchar(50)
,`last_name` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `prescription_id` int DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_customer` (`customer_id`),
  KEY `idx_orders_prescription` (`prescription_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `prescription_id`, `order_date`, `status`, `subtotal`, `tax_amount`, `shipping_fee`, `total_amount`, `updated_at`) VALUES
(1, 1, 1, '2026-08-27 08:55:58', 'delivered', 24.49, 1.96, 5.00, 31.45, '2026-08-27 16:23:47'),
(2, 2, 2, '2026-08-27 08:55:58', 'delivered', 12.00, 0.96, 5.00, 17.96, '2026-08-27 16:23:47'),
(3, 3, 3, '2026-08-27 08:55:58', 'delivered', 28.50, 2.28, 5.00, 35.78, '2026-08-27 16:23:47'),
(4, 4, NULL, '2026-08-27 08:55:58', 'delivered', 19.95, 1.60, 0.00, 21.55, '2026-08-27 16:23:47'),
(5, 6, 6, '2026-08-27 08:55:58', 'shipped', 15.50, 1.24, 5.00, 21.74, '2026-08-27 16:23:47'),
(6, 7, 7, '2026-08-27 08:55:58', 'shipped', 20.75, 1.66, 5.00, 27.41, '2026-08-27 16:23:47'),
(7, 9, 9, '2026-08-27 08:55:58', 'confirmed', 22.00, 1.76, 5.00, 28.76, '2026-08-27 16:23:47'),
(8, 10, 10, '2026-08-27 08:55:58', 'confirmed', 24.79, 1.98, 5.00, 31.77, '2026-08-27 16:23:47'),
(9, 12, 12, '2026-08-27 08:55:58', 'pending', 12.00, 0.96, 5.00, 17.96, '2026-08-27 16:23:47'),
(10, 13, 13, '2026-08-27 08:55:58', 'pending', 11.75, 0.94, 5.00, 17.69, '2026-08-27 16:23:47'),
(11, 1, NULL, '2026-08-27 08:55:58', 'delivered', 14.25, 1.14, 0.00, 15.39, '2026-08-27 16:23:47'),
(12, 2, NULL, '2026-08-27 08:55:58', 'delivered', 18.00, 1.44, 0.00, 19.44, '2026-08-27 16:23:47'),
(13, 4, NULL, '2026-08-27 08:55:58', 'delivered', 26.98, 2.16, 5.00, 34.14, '2026-08-27 16:23:47'),
(14, 6, NULL, '2026-08-27 08:55:58', 'cancelled', 6.50, 0.52, 5.00, 12.02, '2026-08-27 16:23:47'),
(15, 15, 15, '2026-08-27 08:55:58', 'pending', 16.80, 1.34, 5.00, 23.14, '2026-08-27 16:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price_at_purchase` decimal(10,2) NOT NULL,
  `item_subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_item_id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price_at_purchase`, `item_subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 15.50, 15.50, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(2, 1, 2, 1, 8.99, 8.99, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(3, 2, 3, 1, 12.00, 12.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(4, 3, 8, 1, 11.75, 11.75, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(5, 3, 15, 1, 16.75, 16.75, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(6, 4, 4, 1, 19.95, 19.95, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(7, 5, 1, 1, 15.50, 15.50, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(8, 6, 3, 1, 12.00, 12.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(9, 6, 11, 1, 8.75, 8.75, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(10, 7, 9, 1, 22.00, 22.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(11, 8, 5, 1, 14.25, 14.25, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(12, 8, 14, 1, 10.54, 10.54, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(13, 9, 3, 1, 12.00, 12.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(14, 10, 8, 1, 11.75, 11.75, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(15, 11, 5, 1, 14.25, 14.25, '2026-08-27 16:23:47', '2026-08-27 16:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` enum('card','cash','bank_transfer','mobile_wallet') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount`, `transaction_reference`, `payment_status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'card', 31.45, 'TXN-20240501-001', 'completed', '2024-05-01 05:30:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(2, 2, 'mobile_wallet', 17.96, 'TXN-20240502-002', 'completed', '2024-05-02 06:00:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(3, 3, 'card', 35.78, 'TXN-20240503-003', 'completed', '2024-05-03 08:45:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(4, 4, 'bank_transfer', 21.55, 'TXN-20240505-004', 'completed', '2024-05-05 04:30:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(5, 5, 'card', 21.74, 'TXN-20240507-005', 'completed', '2024-05-07 11:00:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(6, 6, 'card', 27.41, 'TXN-20240508-006', 'completed', '2024-05-08 06:45:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(7, 7, 'mobile_wallet', 28.76, 'TXN-20240511-007', 'completed', '2024-05-11 10:15:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(8, 8, 'card', 31.77, 'TXN-20240512-008', 'completed', '2024-05-12 04:45:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(9, 9, 'card', 17.96, 'TXN-20240515-009', 'pending', NULL, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(10, 10, 'cash', 17.69, 'TXN-20240516-010', 'pending', NULL, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(11, 11, 'card', 15.39, 'TXN-20240517-011', 'completed', '2024-05-17 04:00:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(12, 12, 'mobile_wallet', 19.44, 'TXN-20240518-012', 'completed', '2024-05-18 08:50:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(13, 13, 'card', 34.14, 'TXN-20240519-013', 'completed', '2024-05-19 10:35:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(14, 14, 'card', 12.02, 'TXN-20240520-014', 'refunded', '2024-05-20 05:30:00', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(15, 15, 'bank_transfer', 23.14, 'TXN-20240521-015', 'pending', NULL, '2026-08-27 16:23:47', '2026-08-27 16:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `prescription_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `staff_id` int DEFAULT NULL,
  `doctor_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doctor_license_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_date` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','verified','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`prescription_id`),
  KEY `fk_presc_staff` (`staff_id`),
  KEY `idx_prescriptions_customer` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`prescription_id`, `customer_id`, `staff_id`, `doctor_name`, `doctor_license_no`, `file_path`, `issue_date`, `upload_date`, `verified_date`, `rejection_reason`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Dr. Gregory House', 'MD-10029', '/uploads/prescriptions/p1.pdf', '2024-05-01', '2026-08-27 08:55:58', '2024-05-01 05:00:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(2, 2, 4, 'Dr. Meredith Grey', 'MD-20045', '/uploads/prescriptions/p2.pdf', '2024-05-02', '2026-08-27 08:55:58', '2024-05-02 05:45:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(3, 3, 3, 'Dr. Stephen Strange', 'MD-30012', '/uploads/prescriptions/p3.pdf', '2024-05-03', '2026-08-27 08:55:58', '2026-08-30 08:18:12', 'sdsadsdsa', 'rejected', '2026-08-29 19:38:49', '2026-08-30 08:18:12'),
(4, 4, 7, 'Dr. John Watson', 'MD-40088', '/uploads/prescriptions/p4.pdf', '2024-05-05', '2026-08-27 08:55:58', '2024-05-05 04:15:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(5, 5, NULL, 'Dr. Michaela Stone', 'MD-50067', '/uploads/prescriptions/p5.pdf', '2024-05-06', '2026-08-27 08:55:58', NULL, 'Illegible signature', 'rejected', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(6, 6, 8, 'Dr. Leonard McCoy', 'MD-60033', '/uploads/prescriptions/p6.pdf', '2024-05-07', '2026-08-27 08:55:58', '2024-05-07 10:50:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(7, 7, 10, 'Dr. Shaun Murphy', 'MD-70019', '/uploads/prescriptions/p7.pdf', '2024-05-08', '2026-08-27 08:55:58', '2024-05-08 06:30:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(8, 8, NULL, 'Dr. Martin Ellingham', 'MD-80054', '/uploads/prescriptions/p8.pdf', '2024-05-10', '2026-08-27 08:55:58', '2026-08-30 08:16:05', 'sdsads', 'rejected', '2026-08-29 19:38:49', '2026-08-30 08:16:05'),
(9, 9, 11, 'Dr. Doogie Howser', 'MD-90081', '/uploads/prescriptions/p9.pdf', '2024-05-11', '2026-08-27 08:55:58', '2024-05-11 10:00:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(10, 10, 14, 'Dr. Beverly Crusher', 'MD-11022', '/uploads/prescriptions/p10.pdf', '2024-05-12', '2026-08-27 08:55:58', '2024-05-12 04:30:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(11, 11, NULL, 'Dr. Sanjay Gupta', 'MD-12044', '/uploads/prescriptions/p11.pdf', '2024-05-14', '2026-08-27 08:55:58', '2026-08-30 08:17:09', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-30 08:17:09'),
(12, 12, 4, 'Dr. Allison Cameron', 'MD-13099', '/uploads/prescriptions/p12.pdf', '2024-05-15', '2026-08-27 08:55:58', '2024-05-15 07:40:00', NULL, 'verified', '2026-08-29 19:38:49', '2026-08-27 16:23:47'),
(13, 13, 7, 'Dr. Robert Chase', 'MD-14055', '/uploads/prescriptions/p13.pdf', '2024-05-16', '2026-08-27 08:55:58', '2026-09-06 11:22:22', NULL, 'verified', '2026-08-29 19:38:49', '2026-09-06 11:22:22'),
(14, 14, NULL, 'Dr. Eric Foreman', 'MD-15077', 'uploads/prescriptions/p14.jpg', '2024-05-18', '2026-08-27 08:55:58', '2026-09-06 11:22:06', 'this is why', 'rejected', '2026-08-29 19:38:49', '2026-09-06 11:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

DROP TABLE IF EXISTS `prescription_items`;
CREATE TABLE IF NOT EXISTS `prescription_items` (
  `prescription_item_id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `product_id` int NOT NULL,
  `prescribed_quantity` int NOT NULL,
  `prescribed_dosage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`prescription_item_id`),
  KEY `fk_pi_product` (`product_id`),
  KEY `idx_prescription_items_prescription` (`prescription_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescription_items`
--

INSERT INTO `prescription_items` (`prescription_item_id`, `prescription_id`, `product_id`, `prescribed_quantity`, `prescribed_dosage`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 30, '1 capsule every 8 hours for 10 days', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(2, 2, 3, 30, '1 tablet daily in the morning', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(3, 3, 8, 60, '1 tablet twice daily with meals', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(4, 4, 9, 30, '1 tablet daily at bedtime', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(5, 5, 15, 30, '1 tablet daily on an empty stomach', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(6, 6, 1, 21, '1 capsule 3 times daily for 7 days', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(7, 7, 3, 90, '1 tablet daily for 90 days', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(8, 8, 8, 60, '1 tablet twice daily', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(9, 9, 9, 30, '1 tablet daily', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(10, 10, 15, 30, '1 tablet daily', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(11, 11, 1, 30, '1 capsule every 8 hours', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(12, 12, 3, 30, '1 tablet daily', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(13, 13, 8, 60, '1 tablet twice daily', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(14, 14, 9, 30, '1 tablet daily', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(15, 15, 15, 60, '1 tablet daily for 60 days', '2026-08-27 16:23:47', '2026-08-27 16:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `description` text,
  `sku` varchar(100) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `dosage_form` varchar(50) NOT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_percent` decimal(5,2) DEFAULT '0.00',
  `requires_prescription` tinyint(1) DEFAULT '0',
  `reorder_level` int DEFAULT '0',
  `product_image` varchar(255) DEFAULT NULL,
  `status` enum('active','draft','archived') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `fk_products_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `generic_name`, `description`, `sku`, `barcode`, `category_id`, `dosage_form`, `strength`, `unit_price`, `discount_percent`, `requires_prescription`, `reorder_level`, `product_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Panadol Extra', 'Paracetamol / Caffeine', 'Fast and effective relief for severe headache, toothache, and joint pain.', 'PAN-EXT-500', '8901234567011', 1, 'tablet', '500mg / 65mg', 15.00, 0.00, 0, 100, 'uploads/products/panadol-extra.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(2, 'Amoxil', 'Amoxicillin', 'Broad-spectrum antibiotic used to treat bacterial infections of the chest, ears, and throat.', 'AMX-CAP-250', '8901234567028', 2, 'capsule', '250mg', 45.50, 5.00, 1, 50, 'uploads/products/amoxil-250.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(3, 'Neurobion Forte', 'Vitamin B1, B6, B12', 'Vitamin B-complex supplement to support nerve health and energy metabolism.', 'NEU-FOR-100', '8901234567035', 3, 'tablet', '100mg', 22.00, 0.00, 0, 30, 'uploads/products/neurobion.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(4, 'Cetaphil Gentle Cleanser', 'Cetyl / Stearyl Alcohol', 'Dermatologist recommended daily soothing cleanser for sensitive and dry skin.', 'CET-CLN-250ML', '8901234567042', 4, 'cream', '250ml', 1250.00, 10.00, 0, 15, 'uploads/products/cetaphil-cleanser.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(5, 'Augmentin 625 Duo', 'Amoxicillin / Clavulanate Potassium', 'High-potency antibacterial combination for severe respiratory and skin infections.', 'AUG-TAB-625', '8901234567059', 2, 'tablet', '625mg', 120.00, 0.00, 1, 40, 'uploads/products/augmentin-625.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(6, 'Benadryl Cough Syrup', 'Diphenhydramine HCl', 'Provides effective relief from dry cough, chest congestion, and allergy symptoms.', 'BEN-SYR-100ML', '8901234567066', 1, 'syrup', '100ml', 185.00, 2.50, 0, 25, 'uploads/products/benadryl-syrup.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(7, 'Seven Seas Cod Liver Oil', 'Omega-3 / Vitamin A & D', 'Daily dietary supplement rich in Omega-3 fatty acids for heart and brain development.', 'SEV-CAP-500', '8901234567073', 3, 'capsule', '500mg', 850.00, 0.00, 0, 20, 'uploads/products/seven-seas.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(8, 'Voltaren Emulgel', 'Diclofenac Diethylamine', 'Topical anti-inflammatory gel for targeted relief from muscle pain and joint inflammation.', 'VOL-GEL-50G', '8901234567080', 1, 'cream', '50g', 340.00, 5.00, 0, 15, 'uploads/products/voltaren-gel.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(9, 'Insulin Humalog', 'Insulin Lispro', 'Fast-acting human insulin analog used to control high blood sugar in patients with diabetes.', 'INS-INJ-100U', '8901234567097', 1, 'injection', '100 IU/ml', 1450.00, 0.00, 1, 10, 'uploads/products/humalog-injection.jpg', 'draft', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(10, 'Disprin Soluble', 'Aspirin', 'Fast-dissolving aspirin tablets for immediate relief of migraine and fever.', 'DIS-SOL-300', '8901234567103', 1, 'tablet', '300mg', 8.00, 0.00, 0, 150, 'uploads/products/disprin.jpg', 'active', '2026-08-28 17:29:15', '2026-08-28 17:29:15'),
(11, 'dawdw', NULL, NULL, '34534gdgd', NULL, 3, 'cream', NULL, 12.00, 0.00, 0, 0, NULL, 'active', '2026-08-29 08:16:07', '2026-08-29 08:16:07'),
(12, 'test1', NULL, NULL, 'test', NULL, 2, 'capsule', NULL, 12222.00, 0.00, 0, 0, NULL, 'active', '2026-08-29 08:47:42', '2026-08-29 08:47:42'),
(13, 'dwadtttttttttttttttt', NULL, NULL, 'ssssssssss', NULL, 4, 'tablet', NULL, 1000.00, 0.00, 0, 0, NULL, 'active', '2026-08-29 09:29:32', '2026-08-29 09:29:32'),
(14, 'dsdad', 'sdasdsad', 'dsdsadsd', 'dsasdad', 'dsdasd', 3, 'cream', NULL, 1111.00, 10.00, 0, 0, NULL, 'active', '2026-08-29 09:50:12', '2026-08-29 09:50:12'),
(15, 'test 2', 'sdasd', 'sdasd', 'sdsda', 'dsads', 8, 'cream', '111', 11111.00, 10.00, 1, 110, 'uploads/products/prod_ebbc1ba31dd45f16.jpg', 'active', '2026-08-29 11:46:56', '2026-08-29 11:46:56'),
(16, 'Candid B', 'adawsd', 'dsadas', 'sdda', 'dssad', 13, 'cream', '200', 550.00, 6.00, 1, 10, 'uploads/products/prod_2c9cc80536bb2b49.png', 'active', '2026-08-29 12:45:48', '2026-08-29 12:45:48'),
(17, 'HP099', 'dsdad', 'dsdasd', 'sdsd', 'dsdas', 14, 'tablet', '111', 11111.00, 10.00, 1, 1, 'uploads/products/prod_f27c84778726ad76.png', 'active', '2026-08-29 12:47:12', '2026-08-29 12:47:12'),
(19, 'dwadawd', 'sdads', 'dsadasd', 'sdads', 'dsadsad', 2, 'injection', '1111', 11111.00, 10.00, 1, 10, 'uploads/products/prod_a01ba6b3207a1bed.jpg', 'active', '2026-08-29 13:09:55', '2026-08-29 13:09:55'),
(20, 'Test Image Edit', 'fesfsef', 'sdsadsad', 'dsdsad', 'sdsad', 11, 'injection', '11111', 1111.00, 10.00, 1, 10, 'uploads/products/prod_4d5eea6b0132e1b5.jpg', 'active', '2026-08-29 13:36:04', '2026-08-29 18:57:10');

-- --------------------------------------------------------

--
-- Table structure for table `product_batches`
--

DROP TABLE IF EXISTS `product_batches`;
CREATE TABLE IF NOT EXISTS `product_batches` (
  `batch_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `batch_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_quantity` int NOT NULL DEFAULT '0',
  `quantity_on_hand` int NOT NULL DEFAULT '0',
  `purchase_price` decimal(10,2) DEFAULT '0.00',
  `selling_price` decimal(10,2) DEFAULT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `received_date` date NOT NULL,
  `invoice_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','expired','recalled','depleted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`batch_id`),
  KEY `product_id` (`product_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_batches`
--

INSERT INTO `product_batches` (`batch_id`, `product_id`, `supplier_id`, `batch_number`, `initial_quantity`, `quantity_on_hand`, `purchase_price`, `selling_price`, `manufacture_date`, `expiry_date`, `received_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'BN-PAN-2025-01', 500, 320, 9.50, 15.00, '2025-01-10', '2027-01-10', '2025-01-20', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(2, 1, 2, 'BN-PAN-2025-02', 500, 500, 9.80, 15.00, '2025-03-01', '2027-03-01', '2025-03-15', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(3, 2, 2, 'BN-AMX-2024-09', 200, 85, 30.00, 45.50, '2024-09-01', '2026-09-01', '2024-09-10', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(4, 3, 3, 'BN-NEU-2025-04', 150, 110, 14.00, 22.00, '2025-04-12', '2027-04-12', '2025-04-25', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(5, 4, 1, 'BN-CET-2025-02', 50, 28, 900.00, 1250.00, '2025-02-01', '2028-02-01', '2025-02-20', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(6, 5, 2, 'BN-AUG-2024-11', 100, 42, 85.00, 120.00, '2024-11-05', '2026-11-05', '2024-11-18', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(7, 6, 3, 'BN-BEN-2025-05', 80, 60, 130.00, 185.00, '2025-05-01', '2027-05-01', '2025-05-10', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(8, 7, 1, 'BN-SEV-2024-06', 60, 0, 600.00, 850.00, '2024-06-01', '2026-06-01', '2024-06-15', 'depleted', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(9, 8, 3, 'BN-VOL-2025-01', 40, 18, 230.00, 340.00, '2025-01-15', '2027-01-15', '2025-02-01', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(10, 9, 2, 'BN-INS-2025-06', 30, 30, 1050.00, 1450.00, '2025-06-01', '2026-12-01', '2025-06-10', 'active', '2026-08-28 18:25:04', '2026-08-28 18:25:04'),
(11, 10, 1, 'BN-DIS-2023-01', 300, 45, 5.00, 8.00, '2023-01-01', '2025-01-01', '2023-01-15', 'expired', '2026-08-28 18:25:04', '2026-08-28 18:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `po_id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `staff_id` int DEFAULT NULL,
  `order_date` date NOT NULL,
  `expected_date` date DEFAULT NULL,
  `status` enum('pending','ordered','received','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`po_id`),
  KEY `fk_po_staff` (`staff_id`),
  KEY `idx_po_supplier` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`po_id`, `supplier_id`, `staff_id`, `order_date`, `expected_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2024-01-05', '2024-01-12', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(2, 2, 2, '2024-01-15', '2024-01-22', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(3, 3, 3, '2024-02-01', '2024-02-08', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(4, 4, 1, '2024-02-15', '2024-02-22', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(5, 5, 6, '2024-03-01', '2024-03-10', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(6, 6, 2, '2024-03-15', '2024-03-22', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(7, 7, 9, '2024-04-01', '2024-04-08', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(8, 8, 3, '2024-04-15', '2024-04-22', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(9, 9, 13, '2024-05-01', '2024-05-08', 'received', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(10, 10, 6, '2024-05-15', '2024-05-22', 'ordered', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(11, 11, 2, '2024-06-01', '2024-06-08', 'ordered', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(12, 12, 1, '2024-06-10', '2024-06-17', 'pending', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(13, 13, 9, '2024-06-12', '2024-06-19', 'pending', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(14, 14, 13, '2024-06-14', '2024-06-21', 'pending', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(15, 15, 6, '2024-06-15', '2024-06-22', 'cancelled', '2026-08-27 16:23:47', '2026-08-27 16:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `po_item_id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity_ordered` int NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`po_item_id`),
  KEY `idx_poi_po` (`po_id`),
  KEY `idx_poi_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`po_item_id`, `po_id`, `product_id`, `quantity_ordered`, `unit_cost`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 200, 8.50, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(2, 1, 2, 500, 4.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(3, 2, 3, 150, 6.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(4, 3, 4, 300, 10.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(5, 4, 5, 250, 7.50, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(6, 5, 6, 200, 9.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(7, 6, 7, 100, 3.25, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(8, 7, 8, 400, 5.50, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(9, 8, 9, 100, 11.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(10, 9, 10, 350, 2.50, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(11, 10, 11, 150, 4.75, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(12, 11, 12, 180, 12.50, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(13, 12, 13, 250, 3.80, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(14, 13, 14, 300, 5.00, '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(15, 14, 15, 200, 8.40, '2026-08-27 16:23:47', '2026-08-27 16:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','pharmacist','superadmin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remember_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `email`, `password_hash`, `role`, `status`, `hire_date`, `created_at`, `updated_at`, `remember_token`) VALUES
(1, 'Alice', 'Green', 'alice.green@mediquick.com', '$2b$12$hash1', '', 'active', '2020-01-15', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(2, 'Bob', 'Baker', 'bob.baker@mediquick.com', '$2b$12$hash2', 'admin', 'active', '2021-03-01', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(3, 'Charlie', 'Clark', 'charlie.clark@mediquick.com', '$2b$12$hash3', 'pharmacist', 'active', '2021-06-15', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(4, 'Diana', 'Prince', 'diana.prince@mediquick.com', '$2b$12$hash4', 'pharmacist', 'active', '2022-02-10', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(5, 'Edward', 'Nygma', 'edward.n@mediquick.com', '$2b$12$hash5', 'pharmacist', 'inactive', '2020-09-01', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(6, 'Fiona', 'Gallagher', 'fiona.g@mediquick.com', '$2b$12$hash6', 'admin', 'active', '2022-07-20', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(7, 'George', 'Stacy', 'george.s@mediquick.com', '$2b$12$hash7', 'pharmacist', 'active', '2023-01-05', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(8, 'Hannah', 'Abbott', 'hannah.a@mediquick.com', '$2b$12$hash8', 'pharmacist', 'active', '2023-04-12', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(9, 'Ian', 'Malcolm', 'ian.m@mediquick.com', '$2b$12$hash9', 'admin', 'active', '2023-08-18', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(10, 'Julia', 'Roberts', 'julia.r@mediquick.com', '$2b$12$hash10', 'pharmacist', 'active', '2023-11-01', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(11, 'Kyle', 'Rayner', 'kyle.r@mediquick.com', '$2b$12$hash11', 'pharmacist', 'active', '2024-01-10', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(12, 'Linda', 'Danvers', 'linda.d@mediquick.com', '$2b$12$hash12', 'pharmacist', 'inactive', '2021-11-15', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(13, 'Mark', 'Grayson', 'mark.g@mediquick.com', '$2b$12$hash13', 'admin', 'active', '2024-03-01', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(14, 'Nina', 'Sayers', 'nina.s@mediquick.com', '$2b$12$hash14', 'pharmacist', 'active', '2024-05-15', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(15, 'Oliver', 'Queen', 'oliver.q@mediquick.com', '$2b$12$hash15', '', 'active', '2019-12-01', '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(21, 'Thaveesha', 'ssa', 'thavee@gmail.com', '$2y$10$v8nOOAhx5IIaNpSKb5YuWu/J8LaEhkblSCTZXnUcA198jJ4m2rSCa', 'admin', 'active', NULL, '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL),
(22, 'Chethiya', 'Lakshan', 'chethi@gmail.com', '$2y$10$Hw1jeApiFoo7UGNhuHAaX.8ouZLZq2BrNC82ydF890Mt/tAGkeNTi', 'superadmin', 'active', NULL, '2026-08-27 16:23:47', '2026-09-09 10:25:16', NULL),
(23, 'Malin', 'Wathsara', 'mali@gmail.com', '$2y$10$Vx8JWvbOxzUuEzEZSSH92urGh.Xox8K3ObpgiKM/mlbuKNVxnq/n6', 'admin', 'active', NULL, '2026-08-27 16:23:47', '2026-08-27 16:23:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `supplier_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `name`, `contact_person`, `address`, `phone`, `email`, `created_at`, `updated_at`) VALUES
(1, 'PharmaSupply Co.', 'Arthur Pendelton', '10 Industrial Pkwy, Newark, NJ', '800-555-0191', 'orders@pharmasupply.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(2, 'MediGlobal Inc.', 'Beatrice Vance', '500 Logistics Way, Chicago, IL', '800-555-0192', 'contact@mediglobal.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(3, 'Apex Healthcare Supply', 'Charles Xavier', '88 Science Park, Boston, MA', '800-555-0193', 'sales@apexhealth.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(4, 'BioMed Pharma', 'Donna Troy', '42 Biotech Ave, San Diego, CA', '800-555-0194', 'info@biomedpharma.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(5, 'CureAll Wholesalers', 'Ethan Hunt', '12 Supply Rd, Atlanta, GA', '800-555-0195', 'support@cureall.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(6, 'Vitality Labs', 'Felicity Smoak', '77 Innovation Way, Austin, TX', '800-555-0196', 'contact@vitalitylabs.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(7, 'Global OTC Distributors', 'Gideon Cross', '300 Commerce Blvd, Dallas, TX', '800-555-0197', 'sales@globalotc.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(8, 'NextGen Pharmaceuticals', 'Harvey Dent', '15 Pharma St, Philadelphia, PA', '800-555-0198', 'orders@nextgenpharma.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(9, 'Horizon Medical Direct', 'Iris West', '90 Skyline Dr, Seattle, WA', '800-555-0199', 'info@horizonmed.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(10, 'Pinnacle Health Imports', 'Jack Reacher', '405 Port Rd, Miami, FL', '800-555-0200', 'service@pinnaclehealth.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(11, 'United Care Supplies', 'Kara Zor-El', '60 National Ave, Denver, CO', '800-555-0201', 'supply@unitedcare.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(12, 'OmniMed Trading', 'Lex Luthor', '1 Corporate Center, Metropolis, IL', '800-555-0202', 'contact@omnimed.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(13, 'Sovereign Labs', 'Moira Queen', '80 Heritage Way, Star City, CA', '800-555-0203', 'orders@sovereignlabs.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(14, 'Zenith Pharma Corp', 'Norman Osborn', '99 Oscorp Plaza, New York, NY', '800-555-0204', 'sales@zenithpharma.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47'),
(15, 'Reliant Medical Logistics', 'Pamela Isley', '55 Green St, Gotham, NY', '800-555-0205', 'support@reliantmed.com', '2026-08-27 16:23:47', '2026-08-27 16:23:47');

-- --------------------------------------------------------

--
-- Structure for view `customer_ages`
--
DROP TABLE IF EXISTS `customer_ages`;

DROP VIEW IF EXISTS `customer_ages`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `customer_ages`  AS SELECT `customers`.`customer_id` AS `customer_id`, `customers`.`first_name` AS `first_name`, `customers`.`last_name` AS `last_name`, `customers`.`date_of_birth` AS `date_of_birth`, timestampdiff(YEAR,`customers`.`date_of_birth`,curdate()) AS `age` FROM `customers` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `fk_contact_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_batches`
--
ALTER TABLE `product_batches`
  ADD CONSTRAINT `fk_batches_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_batches_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
