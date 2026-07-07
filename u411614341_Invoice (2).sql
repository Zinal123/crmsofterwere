-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 07, 2026 at 04:20 PM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u411614341_Invoice`
--

-- --------------------------------------------------------

--
-- Table structure for table `addlaser`
--

CREATE TABLE `addlaser` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `modal` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `decription` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank`
--

CREATE TABLE `bank` (
  `id` int(11) NOT NULL,
  `bankholdername` varchar(255) DEFAULT NULL,
  `bankaccountnumber` varchar(255) DEFAULT NULL,
  `bankifsccode` varchar(255) DEFAULT NULL,
  `bankbranchname` varchar(255) DEFAULT NULL,
  `bankname` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank`
--

INSERT INTO `bank` (`id`, `bankholdername`, `bankaccountnumber`, `bankifsccode`, `bankbranchname`, `bankname`, `created_at`, `update_at`) VALUES
(1, 'Oracal Machine Tech', '780305000477', 'ICIC007803', 'Madodhar Branch', 'ICIC Bank', '2023-11-18 05:11:50', '2024-01-03 16:47:55');

-- --------------------------------------------------------

--
-- Table structure for table `cnsthinks`
--

CREATE TABLE `cnsthinks` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `cuttingthinks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cnsthinks`
--

INSERT INTO `cnsthinks` (`id`, `product_id`, `cuttingthinks`, `created_at`, `updated_at`) VALUES
(1, 2, 'vjdnvjd', '2023-05-19 23:10:38', '2023-05-19 23:10:38');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `billinggst` varchar(255) DEFAULT NULL,
  `billingpan` varchar(255) DEFAULT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `saddress` varchar(255) DEFAULT NULL,
  `sphone` varchar(255) DEFAULT NULL,
  `sstate` varchar(255) DEFAULT NULL,
  `shippinggst` varchar(255) DEFAULT NULL,
  `shippingpan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `invoice_id`, `name`, `address`, `phone`, `email`, `state`, `billinggst`, `billingpan`, `sname`, `saddress`, `sphone`, `sstate`, `shippinggst`, `shippingpan`, `created_at`, `updated_at`) VALUES
(1, NULL, 'bgr', 'grhrt', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-17 11:00:24', '2023-11-17 11:00:24'),
(2, NULL, 'bgr', 'grhrt', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-17 11:01:41', '2023-11-17 11:01:41'),
(3, NULL, 'bgr', 'grhrt', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-17 11:02:19', '2023-11-17 11:02:19'),
(4, 6, 'bgbg', 'bgnhn', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-18 06:04:03', '2023-11-18 06:04:03'),
(5, 7, 'bgbg', 'jnj', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-18 07:20:27', '2023-11-18 07:20:27'),
(6, 1, 'bgbg', 'jnj', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-18 07:43:13', '2023-11-18 07:43:13'),
(7, 2, 'bgbg', 'jnj', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-18 07:45:38', '2023-11-18 07:45:38'),
(8, 3, 'vfdb', 'bfb', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-18 07:46:07', '2023-11-18 07:46:07'),
(9, 4, 'bfb', 'bgb', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-18 07:48:15', '2023-11-18 07:48:15'),
(10, 5, 'vfb', 'bgb', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-18 07:55:49', '2023-11-18 07:55:49'),
(11, 6, 'bgfb', 'gfnfn', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-19 05:49:21', '2023-11-19 05:49:21'),
(12, 7, 'vdvf', 'fbdfb', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-19 05:56:49', '2023-11-19 05:56:49'),
(13, 8, 'mlml', 'ml', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-19 06:00:53', '2023-11-19 06:00:53'),
(14, 9, 'vfbfb', 'bgntyn', '1234567890', NULL, NULL, 'nhtnhtn', 'nhtnhtn', 'vfbfb', 'bgntyn', '1234567890', 'nhnthn', 'nhtnhtn', 'nhtnhtn', '2023-12-25 11:52:45', '2023-12-25 11:52:45'),
(15, 10, 'vfbfb', 'bgntyn', '1234567890', NULL, NULL, 'nhtnhtn', 'nhtnhtn', 'vfbfb', 'bgntyn', '1234567890', 'nhnthn', 'nhtnhtn', 'nhtnhtn', '2023-12-25 11:55:01', '2023-12-25 11:55:01'),
(16, 11, 'vfbfb', 'bgntyn', '1234567890', NULL, NULL, 'nhtnhtn', 'nhtnhtn', 'vfbfb', 'bgntyn', '1234567890', 'nhnthn', 'nhtnhtn', 'nhtnhtn', '2023-12-25 11:56:35', '2023-12-25 11:56:35'),
(17, 12, 'UTKARSH MART', 'Sai garden,shopping centre,opp G.I.D.C  Bardoli Road,Kabilpour navsari-396424', '9924912879', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'UTKARSH MART', 'Sai garden,shopping centre,opp G.I.D.C  Bardoli Road,Kabilpour navsari-396424', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2023-12-25 12:40:03', '2023-12-25 12:40:03'),
(18, 13, 'UTKARSH MART', 'Sai garden,shopping centre,opp G.I.D.C  Bardoli Road,Kabilpour ', '9924912879', NULL, NULL, '24AENPV1210N1ZA', NULL, 'UTKARSH MART', 'Sai garden,shopping centre,opp G.I.D.C  Bardoli Road,Kabilpour ', '9924912879', 'Gujarat', '24AENPV1210N1ZA', NULL, '2023-12-25 12:40:17', '2023-12-25 18:24:47'),
(19, 14, 'UTKARSH MART', 'Sai garden,shopping centre,opp G.I.D.C  Bardoli Road,Kabilpour ', '9924912879', NULL, NULL, '24AENPV1210N1ZA', NULL, 'UTKARSH MART', 'Sai garden,shopping centre,opp G.I.D.C  Bardoli Road,Kabilpour navsari-396424', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2023-12-25 12:47:14', '2023-12-25 18:24:30'),
(20, 15, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:08', '2023-12-31 02:34:08'),
(21, 16, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:18', '2023-12-31 02:34:18'),
(22, 17, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:19', '2023-12-31 02:34:19'),
(23, 18, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:19', '2023-12-31 02:34:19'),
(24, 19, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:19', '2023-12-31 02:34:19'),
(25, 20, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:19', '2023-12-31 02:34:19'),
(26, 21, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:20', '2023-12-31 02:34:20'),
(27, 22, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:20', '2023-12-31 02:34:20'),
(28, 23, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:20', '2023-12-31 02:34:20'),
(29, 24, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:21', '2023-12-31 02:34:21'),
(30, 25, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:21', '2023-12-31 02:34:21'),
(31, 26, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:21', '2023-12-31 02:34:21'),
(32, 27, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:37', '2023-12-31 02:34:37'),
(33, 28, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:37', '2023-12-31 02:34:37'),
(34, 29, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:37', '2023-12-31 02:34:37'),
(35, 30, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:38', '2023-12-31 02:34:38'),
(36, 31, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:38', '2023-12-31 02:34:38'),
(37, 32, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:40', '2023-12-31 02:34:40'),
(38, 33, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:40', '2023-12-31 02:34:40'),
(39, 34, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:40', '2023-12-31 02:34:40'),
(40, 35, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:41', '2023-12-31 02:34:41'),
(41, 36, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:41', '2023-12-31 02:34:41'),
(42, 37, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:41', '2023-12-31 02:34:41'),
(43, 38, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:41', '2023-12-31 02:34:41'),
(44, 39, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:41', '2023-12-31 02:34:41'),
(45, 40, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:42', '2023-12-31 02:34:42'),
(46, 41, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:42', '2023-12-31 02:34:42'),
(47, 42, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:42', '2023-12-31 02:34:42'),
(48, 43, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:42', '2023-12-31 02:34:42'),
(49, 44, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:42', '2023-12-31 02:34:42'),
(50, 45, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:43', '2023-12-31 02:34:43'),
(51, 46, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:43', '2023-12-31 02:34:43'),
(52, 47, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:55', '2023-12-31 02:34:55'),
(53, 48, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:55', '2023-12-31 02:34:55'),
(54, 49, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:55', '2023-12-31 02:34:55'),
(55, 50, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:55', '2023-12-31 02:34:55'),
(56, 51, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:55', '2023-12-31 02:34:55'),
(57, 52, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:56', '2023-12-31 02:34:56'),
(58, 53, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:56', '2023-12-31 02:34:56'),
(59, 54, 'Ghh', 'Ghh', '1234567890', NULL, NULL, 'Ghfivfhkolgfd', 'Ghfivfhkolgfd', 'Ghh', 'Ghh', '1234567890', 'Fgh', 'Ghfivfhkolgfd', 'Ghh', '2023-12-31 02:34:56', '2023-12-31 02:34:56'),
(60, 55, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:23', '2023-12-31 04:33:23'),
(61, 56, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:30', '2023-12-31 04:33:30'),
(62, 57, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:33', '2023-12-31 04:33:33'),
(63, 58, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:36', '2023-12-31 04:33:36'),
(64, 59, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:39', '2023-12-31 04:33:39'),
(65, 60, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:39', '2023-12-31 04:33:39'),
(66, 61, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:40', '2023-12-31 04:33:40'),
(67, 62, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:40', '2023-12-31 04:33:40'),
(68, 63, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:41', '2023-12-31 04:33:41'),
(69, 64, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:41', '2023-12-31 04:33:41'),
(70, 65, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:41', '2023-12-31 04:33:41'),
(71, 66, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:42', '2023-12-31 04:33:42'),
(72, 67, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:42', '2023-12-31 04:33:42'),
(73, 68, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:42', '2023-12-31 04:33:42'),
(74, 69, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:42', '2023-12-31 04:33:42'),
(75, 70, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:42', '2023-12-31 04:33:42'),
(76, 71, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:49', '2023-12-31 04:33:49'),
(77, 72, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:49', '2023-12-31 04:33:49'),
(78, 73, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:49', '2023-12-31 04:33:49'),
(79, 74, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:49', '2023-12-31 04:33:49'),
(80, 75, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:49', '2023-12-31 04:33:49'),
(81, 76, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:49', '2023-12-31 04:33:49'),
(82, 77, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:50', '2023-12-31 04:33:50'),
(83, 78, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:50', '2023-12-31 04:33:50'),
(84, 79, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:50', '2023-12-31 04:33:50'),
(85, 80, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:33:50', '2023-12-31 04:33:50'),
(86, 81, '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07383658311', NULL, NULL, '.313231353535', ';knkjhklhlkhll', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '812, WAGHODIA GIDC ESTATE, SBI BANK ROAD 1, GATE NO 1.', '07096487806', 'Gujarat', 'jhjhjhjhvjhvkjh.2333', '335435435435435', '2023-12-31 04:36:32', '2023-12-31 04:36:32'),
(87, 82, 'vfbfdvdfbdfb', 'dfbfd', '1234567890', NULL, NULL, 'bfdb', 'bfdb', 'vfbfdvdfbdfb', 'dfbfd', '1234567890', 'bfbb', 'bfdb', 'bfdbd', '2023-12-31 05:00:56', '2023-12-31 05:00:56'),
(88, 83, 'oracal machine tech', 'vfv', '1234567890', NULL, NULL, 'bgfbgntrnyt1244', 'bgfbgntrnyt1244', 'oracal machine tech', 'vfv', '1234567890', 'Gujarat', 'bgfbgntrnyt1244', 'bgfbgntrnyt1244', '2023-12-31 05:08:05', '2023-12-31 05:08:05'),
(89, 84, 'test', 'bdbg', '1234567890', NULL, NULL, 'bgfbgntrnyt1244', 'bgfbgntrnyt1244', 'test', 'bdbg', '1234567890', 'Gujarat', 'bgfbgntrnyt1244', 'bgfbgntrnyt1244', '2024-01-04 16:12:10', '2024-01-04 16:12:10'),
(90, 85, 'Orcle machine tech', 'gjagfjweglfjgewjfgljewgflewg', '9924912879', NULL, NULL, 'nhtnhtn', 'nhtnhtn', 'Orcle machine tech', 'gjagfjweglfjgewjfgljewgflewg', '9924912879', 'Gujarat', 'nhtnhtn', 'bgfbgntrnyt1244', '2024-01-06 02:55:05', '2024-01-06 02:55:05'),
(91, 86, 'Rahi engineering', 'por gidc', '9924912879', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'Rahi engineering', 'por gidc', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2024-01-08 01:38:30', '2024-01-08 01:38:30'),
(92, 87, 'Rahi engineering', 'por gidc', '9924912879', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'Rahi engineering', 'por gidc', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2024-01-08 01:40:11', '2024-01-08 01:40:11'),
(93, 88, 'Rahi engineering', 'por gidc', '9924912879', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'Rahi engineering', 'por gidc', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2024-01-08 01:40:35', '2024-01-08 01:40:35'),
(94, 89, 'Rahi engineering', 'por gidc', '9924912879', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'Rahi engineering', 'por gidc', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2024-01-08 01:40:56', '2024-01-08 01:40:56'),
(95, 90, 'Rahi engineering', 'por gidc', '9924912879', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'Rahi engineering', 'por gidc', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2024-01-08 01:41:50', '2024-01-08 01:41:50'),
(96, 91, 'Rahi engineering', 'por gidc', '9924912879', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'Rahi engineering', 'por gidc', '9924912879', 'Gujarat', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2024-01-08 01:42:11', '2024-01-08 01:42:11'),
(97, 92, 'cdfsd', 'hghngfnymhm', '1234567890', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'vfdb', 'nfg gf', '1234567890', 'nhnh', '24AENPV1210N1ZA', 'bgfbgntrnyt1244', '2024-04-14 10:52:57', '2024-04-14 10:52:57'),
(98, 93, 'bgbg', 'vldvmlsdv', '1234567890', NULL, NULL, '24AENPV1210N1ZA', 'bgfbgntrnyt1244', 'bgbg', 'vldvmlsdv', '1234567890', 'Gujarat', '24AENPV1210N1ZA', 'nthyntyn', '2024-05-12 12:36:30', '2024-05-12 12:36:30');

-- --------------------------------------------------------

--
-- Table structure for table `cuttingway`
--

CREATE TABLE `cuttingway` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `cuttingway` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cuttingway`
--

INSERT INTO `cuttingway` (`id`, `product_id`, `cuttingway`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, '2023-05-19 23:00:39', '2023-05-19 23:00:39'),
(2, 2, NULL, '2023-05-19 23:09:40', '2023-05-19 23:09:40'),
(3, 2, NULL, '2023-05-19 23:09:40', '2023-05-19 23:09:40');

-- --------------------------------------------------------

--
-- Table structure for table `fours`
--

CREATE TABLE `fours` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `modal` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fours`
--

INSERT INTO `fours` (`id`, `product_id`, `modal`, `company`, `logo`, `image`, `description`, `created_at`, `updated_at`) VALUES
(1, 2, 'grfg', 'terg', 'C:\\xampp\\tmp\\php3BD.tmp', 'C:\\xampp\\tmp\\php3BE.tmp', '<p>hgfhfg</p>', '2023-05-07 06:31:35', '2023-05-07 06:31:35');

-- --------------------------------------------------------

--
-- Table structure for table `gear`
--

CREATE TABLE `gear` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gear`
--

INSERT INTO `gear` (`id`, `product_id`, `companyname`, `image`, `created_at`, `updated_at`) VALUES
(1, 2, 'test', 'C:\\xampp\\tmp\\phpCEBD.tmp', '2023-05-20 06:49:02', '2023-05-20 06:49:02'),
(2, 1, 'test', NULL, '2023-12-01 10:24:41', '2023-12-01 10:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `invetry`
--

CREATE TABLE `invetry` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `vandername` varchar(255) DEFAULT NULL,
  `rate` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invetry`
--

INSERT INTO `invetry` (`id`, `product_id`, `quantity`, `vandername`, `rate`, `created_at`, `updated_at`) VALUES
(1, 2, 12, 'fdsf', '370', '2024-11-27 10:46:20', '2024-11-27 10:46:20');

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `invoice_id` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `bankname` varchar(255) DEFAULT NULL,
  `accountholder` varchar(255) DEFAULT NULL,
  `bankaccountnumber` varchar(255) DEFAULT NULL,
  `bankifsccode` varchar(255) DEFAULT NULL,
  `bankbranchname` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `totalamountbeforetax` varchar(255) DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `amountwithtax` varchar(255) DEFAULT NULL,
  `paycondition` varchar(255) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `placesupply` varchar(255) NOT NULL,
  `paidamount` varchar(255) DEFAULT NULL,
  `remaining_amount` varchar(255) DEFAULT NULL,
  `challanno` varchar(255) DEFAULT NULL,
  `ewaybillno` varchar(255) DEFAULT NULL,
  `despatchthrough` varchar(255) DEFAULT NULL,
  `TransportVehicleNo` varchar(255) DEFAULT NULL,
  `pono` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoiceproduct`
--

CREATE TABLE `invoiceproduct` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `product_name` int(11) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `hsn` varchar(255) DEFAULT NULL,
  `rate` varchar(255) DEFAULT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `total` varchar(255) DEFAULT NULL,
  `gst` varchar(255) DEFAULT NULL,
  `gstamount` varchar(255) DEFAULT NULL,
  `totalamount` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `motor`
--

CREATE TABLE `motor` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motor`
--

INSERT INTO `motor` (`id`, `product_id`, `companyname`, `image`, `created_at`, `updated_at`) VALUES
(1, 2, 'fdfd', 'C:\\xampp\\tmp\\php96BE.tmp', '2023-05-20 06:21:29', '2023-05-20 06:21:29'),
(2, 1, 'frg', NULL, '2023-12-01 10:24:22', '2023-12-01 10:24:22'),
(3, 1, 'hfhf', NULL, '2023-12-01 10:26:51', '2023-12-01 10:26:51'),
(4, 1, 'hfhf', NULL, '2023-12-01 10:34:49', '2023-12-01 10:34:49');

-- --------------------------------------------------------

--
-- Table structure for table `paidamount`
--

CREATE TABLE `paidamount` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `paidAmount` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `power`
--

CREATE TABLE `power` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `modal` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `power`
--

INSERT INTO `power` (`id`, `product_id`, `company`, `modal`, `image`, `logo`, `description`, `created_at`, `updated_at`) VALUES
(1, 2, 'fgr', 'gregreh', 'C:\\xampp\\tmp\\phpA76B.tmp', 'C:\\xampp\\tmp\\phpA75A.tmp', '<p>gethth</p>', '2023-05-07 11:21:43', '2023-05-07 11:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `rate` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `make` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `rate`, `unit`, `make`, `created_at`, `updated_at`) VALUES
(1, 'Fiber Laser cutting', '12', 'kg', '0', '2023-09-23 07:42:13', '2023-11-05 15:42:35'),
(2, 'Co2  Laser Cutting', '34', 'gr', '0', '2023-09-23 07:42:41', '2023-11-05 15:42:40'),
(7, 'SMPS siemens 6EP13333BA10', '12', 'kg', '990123', '2023-12-25 18:02:58', '2024-04-14 10:51:07'),
(8, 'Raytools Original Window 37*7', '850', NULL, '90019090', '2024-01-08 01:25:01', '2024-01-08 01:25:01'),
(9, 'Ceramic Ring', '750', NULL, '84669390', '2024-01-08 01:26:51', '2024-01-08 01:26:51');

-- --------------------------------------------------------

--
-- Table structure for table `quationform`
--

CREATE TABLE `quationform` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `clientname` varchar(255) DEFAULT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `gstno` varchar(255) DEFAULT NULL,
  `companyaddress` varchar(255) DEFAULT NULL,
  `bank` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `reminderdate` date DEFAULT NULL,
  `softweredetails` int(11) DEFAULT NULL,
  `lasercutting` int(11) DEFAULT NULL,
  `focus` int(11) DEFAULT NULL,
  `power` int(11) DEFAULT NULL,
  `inputpower` varchar(255) DEFAULT NULL,
  `cuttingway` int(11) DEFAULT NULL,
  `cncspan` varchar(255) DEFAULT NULL,
  `cnslenght` varchar(255) DEFAULT NULL,
  `cuttingrang` varchar(255) DEFAULT NULL,
  `liftingheight` varchar(255) DEFAULT NULL,
  `headquantity` varchar(255) DEFAULT NULL,
  `cuttingthickess` int(11) DEFAULT NULL,
  `strokespeed` varchar(255) DEFAULT NULL,
  `cuttingspeed` varchar(255) DEFAULT NULL,
  `drive` varchar(255) DEFAULT NULL,
  `motor` int(11) DEFAULT NULL,
  `motortype` int(11) DEFAULT NULL,
  `gearbox` int(11) DEFAULT NULL,
  `rack` int(11) DEFAULT NULL,
  `software` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description1` text DEFAULT NULL,
  `description2` text DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `amount1` varchar(255) DEFAULT NULL,
  `amount2` varchar(255) DEFAULT NULL,
  `optionparthyscope` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quationform`
--

INSERT INTO `quationform` (`id`, `product_id`, `clientname`, `companyname`, `gstno`, `companyaddress`, `bank`, `email`, `phone`, `date`, `reminderdate`, `softweredetails`, `lasercutting`, `focus`, `power`, `inputpower`, `cuttingway`, `cncspan`, `cnslenght`, `cuttingrang`, `liftingheight`, `headquantity`, `cuttingthickess`, `strokespeed`, `cuttingspeed`, `drive`, `motor`, `motortype`, `gearbox`, `rack`, `software`, `description`, `description1`, `description2`, `amount`, `amount1`, `amount2`, `optionparthyscope`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 'df', NULL, NULL, NULL, NULL, 'zinal@fmail.com', NULL, '2023-05-01', '2023-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-05-27 09:05:32', '2023-05-27 09:05:32'),
(2, NULL, 'test12', 'test', '12345678bcts', 'ndjvjhvkhv', 1, 'admin@gmail.com', 1234567890, '2023-12-08', '2023-12-20', 10, NULL, 0, 0, 'juykykyt', 0, 'jtykyuk', 'kyukyuky', 'kykyukuy', '200', '200', 0, 'kuykuyk', 'kuyku', 'kyukuy', 3, 3, 2, 0, 0, 'kukuyk', 'kuykuyk', 'kuykyuk', '12000', '12000', '1200', NULL, NULL, '2023-12-06 10:25:30', '2023-12-06 10:25:30');

-- --------------------------------------------------------

--
-- Table structure for table `rack`
--

CREATE TABLE `rack` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rack`
--

INSERT INTO `rack` (`id`, `product_id`, `companyname`, `image`, `created_at`, `updated_at`) VALUES
(1, 2, 'test', NULL, '2023-05-20 06:49:11', '2023-05-20 06:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `softerwere1`
--

CREATE TABLE `softerwere1` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `softerwere1`
--

INSERT INTO `softerwere1` (`id`, `product_id`, `companyname`, `image`, `created_at`, `updated_at`) VALUES
(1, 2, 'test', 'C:\\xampp\\tmp\\php9A8E.tmp', '2023-05-20 06:53:11', '2023-05-20 06:53:11');

-- --------------------------------------------------------

--
-- Table structure for table `softwaredetails`
--

CREATE TABLE `softwaredetails` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `modal` varchar(255) DEFAULT NULL,
  `logo` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `softwaredetails`
--

INSERT INTO `softwaredetails` (`id`, `product_id`, `company`, `modal`, `logo`, `image`, `description`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'grfg', 'C:\\xampp\\tmp\\php79F1.tmp', 'C:\\xampp\\tmp\\php79F2.tmp', '<p>vdvsd</p>', '2023-04-30 08:56:54', '2023-04-30 08:56:54'),
(2, 2, 'terg', 'grfg', 'C:\\xampp\\tmp\\phpCC44.tmp', 'C:\\xampp\\tmp\\phpCC55.tmp', '<p>fdfd</p>', '2023-04-30 08:59:27', '2023-04-30 08:59:27'),
(3, 2, 'terg', 'grfg', 'C:\\xampp\\tmp\\phpF219.tmp', 'C:\\xampp\\tmp\\phpF21A.tmp', '<p>fdfd</p>', '2023-04-30 09:00:42', '2023-04-30 09:00:42'),
(4, 2, 'terg', 'grfg', 'C:\\xampp\\tmp\\phpB8E5.tmp', 'C:\\xampp\\tmp\\phpB8E6.tmp', '<p>fdfd</p>', '2023-04-30 09:01:33', '2023-04-30 09:01:33'),
(5, 2, 'terg', 'grfg', 'C:\\xampp\\tmp\\php222C.tmp', 'C:\\xampp\\tmp\\php222D.tmp', '<p>fdv</p>', '2023-04-30 09:03:05', '2023-04-30 09:03:05'),
(6, 2, 'terg', 'grfg', 'C:\\xampp\\tmp\\php2F15.tmp', 'C:\\xampp\\tmp\\php2F16.tmp', '<p>bhfdngf</p>', '2023-04-30 09:05:19', '2023-04-30 09:05:19'),
(7, 2, 'terg', 'grfg', 'C:\\xampp\\tmp\\php5180.tmp', 'C:\\xampp\\tmp\\php5181.tmp', '<p>vfhfjgmhgm<br />\r\nhtjtj</p>', '2023-04-30 09:09:50', '2023-04-30 09:09:50'),
(8, 2, 'terg', 'grfg', 'C:\\xampp\\tmp\\php6B4.tmp', 'C:\\xampp\\tmp\\php6B5.tmp', '<p>vfhfjgmhgm<br />\r\nhtjtj</p>', '2023-04-30 09:11:42', '2023-04-30 09:11:42'),
(9, 2, 'terg', 'defeg', 'C:\\xampp\\tmp\\php7854.tmp', 'C:\\xampp\\tmp\\php7864.tmp', '<p>fdgd</p>', '2023-05-07 06:12:25', '2023-05-07 06:12:25'),
(10, 1, 'bbb', 'bgb', 'IMG_20231024_212524 (1).jpg', NULL, NULL, '2023-11-29 11:11:31', '2023-11-29 11:11:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'meet', 'admin@gmail.com', '$2y$10$pjzyzq/undP6KmOp/wXnBOTBm9R8nYvkzOZ8mF7eE/O2WB.cjvPdy', '1698592121.jpeg', '2023-10-29 09:38:41', '2023-10-29 09:38:41'),
(2, 'Meet Patel', 'patelmeet23599@gmail.com', '$2y$10$Ky3ShnQ3naC09dPLJo.JmurEpI6FL0izozpAFzCwUqdC43ITSBdn2', NULL, '2024-10-17 15:33:56', '2024-10-17 15:33:56'),
(3, 'Meet Patel', 'patelmeet23599@oksbi', '$2y$10$N4Opkw5DfiWM1l.BYFdAaOwBnIGMPs2YCGz5qqiotBLb0uMFgUYly', NULL, '2026-03-29 08:34:41', '2026-03-29 08:34:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addlaser`
--
ALTER TABLE `addlaser`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cnsthinks`
--
ALTER TABLE `cnsthinks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cuttingway`
--
ALTER TABLE `cuttingway`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fours`
--
ALTER TABLE `fours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gear`
--
ALTER TABLE `gear`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invetry`
--
ALTER TABLE `invetry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoiceproduct`
--
ALTER TABLE `invoiceproduct`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `motor`
--
ALTER TABLE `motor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paidamount`
--
ALTER TABLE `paidamount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `power`
--
ALTER TABLE `power`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quationform`
--
ALTER TABLE `quationform`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rack`
--
ALTER TABLE `rack`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `softerwere1`
--
ALTER TABLE `softerwere1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `softwaredetails`
--
ALTER TABLE `softwaredetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addlaser`
--
ALTER TABLE `addlaser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cnsthinks`
--
ALTER TABLE `cnsthinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `cuttingway`
--
ALTER TABLE `cuttingway`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fours`
--
ALTER TABLE `fours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gear`
--
ALTER TABLE `gear`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invetry`
--
ALTER TABLE `invetry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoiceproduct`
--
ALTER TABLE `invoiceproduct`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `motor`
--
ALTER TABLE `motor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `paidamount`
--
ALTER TABLE `paidamount`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `power`
--
ALTER TABLE `power`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `quationform`
--
ALTER TABLE `quationform`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `softwaredetails`
--
ALTER TABLE `softwaredetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
