-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2026 at 06:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thesecond`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_phone` varchar(20) NOT NULL,
  `address_line` text NOT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `receiver_name`, `receiver_phone`, `address_line`, `ward`, `district`, `city`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 'Lý Minh Hà', '0975433036', '169 Đường Trần Hưng Đạo', 'Phường 1', NULL, 'Nha Trang', 1, '2026-07-25 12:49:00', '2026-07-25 12:49:00'),
(2, 1, 'Đỗ Đức Nam', '0985014294', '20 Đường Lê Lợi', 'Phường Bến Nghé', NULL, 'Hải Phòng', 0, '2026-08-07 11:01:07', '2026-08-07 11:01:07'),
(3, 2, 'Bùi Quốc Thắng', '0981693406', '1 Đường Điện Biên Phủ', 'Phường Tân Định', NULL, 'Nha Trang', 1, '2026-07-09 14:15:00', '2026-07-09 14:15:00'),
(4, 2, 'Đỗ Quốc Cường', '0959514846', '168 Đường Cách Mạng Tháng 8', 'Phường 7', NULL, 'Biên Hòa', 0, '2026-06-26 01:12:14', '2026-06-26 01:12:14'),
(5, 3, 'Hồ Hữu Thắng', '0994680443', '221 Đường Điện Biên Phủ', 'Phường Linh Trung', NULL, 'Hải Phòng', 1, '2026-08-02 21:47:24', '2026-08-02 21:47:24'),
(6, 4, 'Dương Minh Quang', '0972148951', '121 Đường Nguyễn Văn Cừ', 'Phường 4', NULL, 'Đà Nẵng', 1, '2026-07-10 00:00:45', '2026-07-10 00:00:45'),
(7, 5, 'Trần Minh Phong', '0991769367', '205 Đường Nguyễn Huệ', 'Phường Linh Trung', NULL, 'Hà Nội', 1, '2026-06-02 09:50:26', '2026-06-02 09:50:26'),
(8, 6, 'Phạm Quốc Hùng', '0928708317', '69 Đường Cách Mạng Tháng 8', 'Phường Tân Định', NULL, 'Biên Hòa', 1, '2026-08-01 22:42:56', '2026-08-01 22:42:56'),
(9, 6, 'Dương Xuân Hải', '0986872774', '127 Đường Nguyễn Văn Cừ', 'Phường Tân Định', NULL, 'Đà Nẵng', 0, '2026-09-03 10:23:08', '2026-09-03 10:23:08'),
(10, 7, 'Võ Anh Cường', '0943455812', '78 Đường Nguyễn Huệ', 'Phường Linh Trung', NULL, 'Cần Thơ', 1, '2026-07-01 00:07:26', '2026-07-01 00:07:26'),
(11, 7, 'Phan Thị Ngân', '0965876036', '200 Đường Điện Biên Phủ', 'Phường 1', NULL, 'Biên Hòa', 0, '2026-08-14 04:42:14', '2026-08-14 04:42:14'),
(12, 8, 'Đỗ Ngọc Vy', '0966889373', '140 Đường Cách Mạng Tháng 8', 'Phường 1', NULL, 'Cần Thơ', 1, '2026-08-15 20:12:21', '2026-08-15 20:12:21'),
(13, 9, 'Hồ Hữu Hải', '0972980699', '14 Đường Lê Lợi', 'Phường Bến Nghé', NULL, 'Biên Hòa', 1, '2026-06-27 16:24:19', '2026-06-27 16:24:19'),
(14, 10, 'Huỳnh Văn Khang', '0965375564', '216 Đường Trần Hưng Đạo', 'Phường 1', NULL, 'Nha Trang', 1, '2026-08-31 15:47:19', '2026-08-31 15:47:19'),
(15, 10, 'Nguyễn Đức Bình', '0953100330', '79 Đường Nguyễn Huệ', 'Phường Bến Nghé', NULL, 'Hà Nội', 0, '2026-06-23 13:05:36', '2026-06-23 13:05:36'),
(16, 11, 'Phan Anh Tuấn', '0945299124', '56 Đường Điện Biên Phủ', 'Phường Tân Định', 'Quận 7', 'TP. Hồ Chí Minh', 1, '2026-08-13 05:03:25', '2026-08-13 05:03:25'),
(17, 12, 'Phan Thị Thảo', '0931491905', '273 Đường Cách Mạng Tháng 8', 'Phường 7', NULL, 'Biên Hòa', 1, '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(18, 13, 'Bùi Văn Kiên', '0967165726', '91 Đường Nguyễn Văn Cừ', 'Phường Linh Trung', NULL, 'Hải Phòng', 1, '2026-07-23 18:40:30', '2026-07-23 18:40:30'),
(19, 14, 'Trịnh Anh Oanh', '0969453147', '125 Đường Cách Mạng Tháng 8', 'Phường Tân Định', NULL, 'Hải Phòng', 1, '2026-08-13 22:13:05', '2026-08-13 22:13:05'),
(20, 14, 'Bùi Văn Phong', '0952735454', '142 Đường Điện Biên Phủ', 'Phường 4', 'Quận Bình Thạnh', 'TP. Hồ Chí Minh', 0, '2026-06-17 22:59:07', '2026-06-17 22:59:07'),
(21, 15, 'Ngô Anh Sơn', '0937770143', '208 Đường Nguyễn Văn Cừ', 'Phường 7', NULL, 'Hà Nội', 1, '2026-08-11 23:45:35', '2026-08-11 23:45:35'),
(22, 16, 'Đinh Thành Ngân', '0985574443', '62 Đường Nguyễn Văn Cừ', 'Phường 7', NULL, 'Hà Nội', 1, '2026-06-24 13:09:55', '2026-06-24 13:09:55'),
(23, 17, 'Huỳnh Minh Hà', '0974989413', '152 Đường Nguyễn Huệ', 'Phường 4', NULL, 'Đà Nẵng', 1, '2026-07-30 00:40:08', '2026-07-30 00:40:08'),
(24, 17, 'Nguyễn Đức Em', '0940084271', '7 Đường Điện Biên Phủ', 'Phường Bến Nghé', NULL, 'Đà Nẵng', 0, '2026-09-02 06:58:11', '2026-09-02 06:58:11'),
(25, 18, 'Huỳnh Văn Khang', '0971167190', '78 Đường Nguyễn Huệ', 'Phường Tân Định', NULL, 'Nha Trang', 1, '2026-07-30 07:58:34', '2026-07-30 07:58:34'),
(26, 19, 'Vũ Thị Sơn', '0969993867', '227 Đường Trần Hưng Đạo', 'Phường Tân Định', NULL, 'Nha Trang', 1, '2026-08-23 14:29:30', '2026-08-23 14:29:30'),
(27, 20, 'Mai Văn Thắng', '0913341232', '283 Đường Lê Lợi', 'Phường 1', NULL, 'Hà Nội', 1, '2026-08-19 15:49:47', '2026-08-19 15:49:47'),
(28, 21, 'Mai Anh Linh', '0903447134', '102 Đường Cách Mạng Tháng 8', 'Phường 4', 'Quận Bình Thạnh', 'TP. Hồ Chí Minh', 1, '2026-06-30 06:20:27', '2026-06-30 06:20:27'),
(29, 22, 'Hoàng Thị Bình', '0924994717', '156 Đường Nguyễn Văn Cừ', 'Phường 7', NULL, 'Cần Thơ', 1, '2026-09-06 12:22:09', '2026-09-06 12:22:09'),
(30, 22, 'Trịnh Anh Oanh', '0919065940', '47 Đường Nguyễn Huệ', 'Phường Tân Định', NULL, 'Biên Hòa', 0, '2026-06-05 08:40:50', '2026-06-05 08:40:50'),
(31, 23, 'Võ Xuân Bình', '0927874296', '252 Đường Lê Lợi', 'Phường 7', NULL, 'Cần Thơ', 1, '2026-08-19 15:02:24', '2026-08-19 15:02:24'),
(32, 23, 'Bùi Thành Trung', '0912567468', '19 Đường Cách Mạng Tháng 8', 'Phường 7', 'Quận 7', 'TP. Hồ Chí Minh', 0, '2026-08-03 02:33:54', '2026-08-03 02:33:54'),
(33, 24, 'Hồ Đức Hải', '0908760385', '256 Đường Nguyễn Văn Cừ', 'Phường 1', NULL, 'Cần Thơ', 1, '2026-07-10 20:39:00', '2026-07-10 20:39:00'),
(34, 25, 'Hoàng Ngọc Oanh', '0971093248', '8 Đường Điện Biên Phủ', 'Phường 1', NULL, 'Cần Thơ', 1, '2026-07-14 23:10:51', '2026-07-14 23:10:51'),
(35, 26, 'Phạm Anh Dũng', '0927484677', '125 Đường Cách Mạng Tháng 8', 'Phường 4', NULL, 'Hải Phòng', 1, '2026-08-14 19:38:25', '2026-08-14 19:38:25'),
(36, 26, 'Phan Xuân Quang', '0921465840', '145 Đường Nguyễn Văn Cừ', 'Phường Tân Định', NULL, 'Đà Nẵng', 0, '2026-09-04 09:53:21', '2026-09-04 09:53:21'),
(37, 27, 'Dương Đức Phong', '0955886753', '123 Đường Điện Biên Phủ', 'Phường 4', NULL, 'Cần Thơ', 1, '2026-08-20 02:21:02', '2026-08-20 02:21:02'),
(38, 27, 'Trần Thành Uyên', '0976627028', '170 Đường Lê Lợi', 'Phường Bến Nghé', NULL, 'Nha Trang', 0, '2026-06-20 16:41:24', '2026-06-20 16:41:24'),
(39, 28, 'Dương Văn Uyên', '0926217459', '204 Đường Nguyễn Văn Cừ', 'Phường 7', 'TP. Thủ Đức', 'TP. Hồ Chí Minh', 1, '2026-09-12 22:51:36', '2026-09-12 22:51:36'),
(40, 29, 'Bùi Anh Kiên', '0980913431', '223 Đường Lê Lợi', 'Phường Linh Trung', NULL, 'Nha Trang', 1, '2026-06-20 20:23:51', '2026-06-20 20:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `chuc_vu` varchar(50) NOT NULL DEFAULT 'admin',
  `trang_thai` enum('Active','Locked') NOT NULL DEFAULT 'Active',
  `ngay_tao` datetime NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `ho_ten`, `email`, `so_dien_thoai`, `mat_khau`, `chuc_vu`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(2, 'Administrator', 'admin@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=1$TnVON2VCU1NNMC5yTFdYOQ$2ysPquNNeLdwLaCEv6HvGXVhliIcQwAYABzxf40C+qk', '', 'Active', '2026-09-14 00:01:56', '2026-09-14 00:01:56'),
(3, 'Administrator 2', 'admin2@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=1$LlpDNDFOU0lHQWxraDlIcQ$kd8CAsTWNu0FFF3wZJVY3tUqqrQGEegINu+7KAX7pwI', '', 'Active', '2026-09-14 00:01:56', '2026-09-14 00:01:56'),
(4, 'Trần Quản Trị', 'moderator1@thesecond.vn', '0909111222', '$argon2id$v=19$m=65536,t=4,p=1$TnVON2VCU1NNMC5yTFdYOQ$2ysPquNNeLdwLaCEv6HvGXVhliIcQwAYABzxf40C+qk', 'moderator', 'Active', '2026-08-26 11:41:30', '2026-07-03 15:18:57'),
(5, 'Lê Hỗ Trợ', 'support1@thesecond.vn', '0909333444', '$argon2id$v=19$m=65536,t=4,p=1$TnVON2VCU1NNMC5yTFdYOQ$2ysPquNNeLdwLaCEv6HvGXVhliIcQwAYABzxf40C+qk', 'support', 'Active', '2026-07-29 11:36:47', '2026-06-06 22:54:14');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-09-08 18:11:54', '2026-09-08 18:11:54'),
(2, 2, '2026-08-01 17:18:50', '2026-08-01 17:18:50'),
(3, 14, '2026-06-30 15:30:44', '2026-06-30 15:30:44'),
(4, 17, '2026-08-25 17:26:35', '2026-08-25 17:26:35'),
(5, 24, '2026-06-14 14:14:13', '2026-06-14 14:14:13'),
(6, 31, '2026-08-31 09:19:50', '2026-08-31 09:19:50'),
(7, 25, '2026-08-26 04:50:20', '2026-08-26 04:50:20'),
(8, 16, '2026-07-30 03:14:43', '2026-07-30 03:14:43'),
(9, 20, '2026-07-24 16:04:19', '2026-07-24 16:04:19'),
(10, 28, '2026-06-12 05:46:51', '2026-06-12 05:46:51'),
(11, 3, '2026-08-08 16:10:10', '2026-08-08 16:10:10'),
(12, 12, '2026-09-07 20:21:40', '2026-09-07 20:21:40'),
(13, 15, '2026-06-15 17:45:12', '2026-06-15 17:45:12'),
(14, 11, '2026-07-31 14:36:19', '2026-07-31 14:36:19'),
(15, 30, '2026-08-29 23:52:33', '2026-08-29 23:52:33'),
(16, 19, '2026-08-28 02:04:16', '2026-08-28 02:04:16'),
(17, 10, '2026-06-08 15:15:44', '2026-06-08 15:15:44'),
(18, 21, '2026-06-12 09:07:36', '2026-06-12 09:07:36'),
(19, 27, '2026-08-11 22:26:30', '2026-08-11 22:26:30'),
(20, 9, '2026-07-27 01:52:33', '2026-07-27 01:52:33'),
(21, 6, '2026-06-16 05:35:32', '2026-06-16 05:35:32'),
(22, 33, '2026-06-18 20:48:43', '2026-06-18 20:48:43'),
(23, 13, '2026-09-07 19:06:26', '2026-09-07 19:06:26'),
(24, 8, '2026-08-14 23:49:05', '2026-08-14 23:49:05'),
(25, 32, '2026-08-30 04:23:33', '2026-08-30 04:23:33'),
(26, 29, '2026-06-09 07:10:46', '2026-06-09 07:10:46'),
(27, 5, '2026-08-27 15:54:39', '2026-08-27 15:54:39'),
(28, 22, '2026-07-07 21:15:34', '2026-07-07 21:15:34'),
(29, 18, '2026-08-02 18:25:23', '2026-08-02 18:25:23'),
(30, 4, '2026-09-01 16:35:30', '2026-09-01 16:35:30'),
(31, 7, '2026-09-06 16:32:27', '2026-09-06 16:32:27'),
(32, 34, '2026-09-19 04:26:58', '2026-09-19 04:26:58');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `cart_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `price` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(3, 2, 12, 2, 12321000.00, '2026-08-01 17:18:50', '2026-08-01 17:18:50'),
(4, 3, 30, 1, 5479000.00, '2026-06-30 15:30:44', '2026-06-30 15:30:44'),
(6, 4, 11, 1, 116000.00, '2026-08-25 17:26:35', '2026-08-25 17:26:35'),
(7, 4, 37, 2, 12720000.00, '2026-08-25 17:26:35', '2026-08-25 17:26:35'),
(8, 5, 42, 1, 636000.00, '2026-06-14 14:14:13', '2026-06-14 14:14:13'),
(9, 5, 32, 2, 13103000.00, '2026-06-14 14:14:13', '2026-06-14 14:14:13'),
(10, 5, 27, 2, 17813000.00, '2026-06-14 14:14:13', '2026-06-14 14:14:13'),
(11, 6, 30, 1, 5479000.00, '2026-08-31 09:19:50', '2026-08-31 09:19:50'),
(12, 6, 49, 2, 4433000.00, '2026-08-31 09:19:50', '2026-08-31 09:19:50'),
(13, 7, 30, 1, 5479000.00, '2026-08-26 04:50:20', '2026-08-26 04:50:20'),
(14, 8, 33, 2, 3806000.00, '2026-07-30 03:14:43', '2026-07-30 03:14:43'),
(15, 8, 25, 1, 15975000.00, '2026-07-30 03:14:43', '2026-07-30 03:14:43'),
(16, 8, 30, 1, 5479000.00, '2026-07-30 03:14:43', '2026-07-30 03:14:43'),
(17, 9, 18, 2, 8885000.00, '2026-07-24 16:04:19', '2026-07-24 16:04:19'),
(18, 9, 28, 2, 1522000.00, '2026-07-24 16:04:19', '2026-07-24 16:04:19'),
(19, 9, 17, 1, 3803000.00, '2026-07-24 16:04:19', '2026-07-24 16:04:19'),
(20, 10, 44, 2, 27301000.00, '2026-06-12 05:46:51', '2026-06-12 05:46:51'),
(22, 11, 42, 1, 636000.00, '2026-08-08 16:10:10', '2026-08-08 16:10:10'),
(23, 12, 36, 2, 4789000.00, '2026-09-07 20:21:40', '2026-09-07 20:21:40'),
(24, 12, 10, 2, 9870000.00, '2026-09-07 20:21:40', '2026-09-07 20:21:40'),
(25, 12, 28, 1, 1522000.00, '2026-09-07 20:21:40', '2026-09-07 20:21:40'),
(26, 13, 30, 2, 5479000.00, '2026-06-15 17:45:12', '2026-06-15 17:45:12'),
(27, 14, 23, 1, 10971000.00, '2026-07-31 14:36:19', '2026-07-31 14:36:19'),
(28, 15, 13, 2, 3472000.00, '2026-08-29 23:52:33', '2026-08-29 23:52:33'),
(29, 16, 41, 1, 756000.00, '2026-08-28 02:04:16', '2026-08-28 02:04:16'),
(30, 17, 30, 1, 5479000.00, '2026-06-08 15:15:44', '2026-06-08 15:15:44'),
(31, 17, 47, 2, 4282000.00, '2026-06-08 15:15:44', '2026-06-08 15:15:44'),
(32, 17, 18, 1, 8885000.00, '2026-06-08 15:15:44', '2026-06-08 15:15:44'),
(33, 18, 48, 1, 5095000.00, '2026-06-12 09:07:36', '2026-06-12 09:07:36'),
(34, 19, 37, 1, 12720000.00, '2026-08-11 22:26:30', '2026-08-11 22:26:30'),
(35, 19, 35, 2, 3067000.00, '2026-08-11 22:26:30', '2026-08-11 22:26:30'),
(36, 19, 24, 2, 3671000.00, '2026-08-11 22:26:30', '2026-08-11 22:26:30'),
(37, 20, 27, 2, 17813000.00, '2026-07-27 01:52:33', '2026-07-27 01:52:33'),
(38, 20, 23, 1, 10971000.00, '2026-07-27 01:52:33', '2026-07-27 01:52:33'),
(39, 20, 28, 2, 1522000.00, '2026-07-27 01:52:33', '2026-07-27 01:52:33'),
(40, 21, 32, 2, 13103000.00, '2026-06-16 05:35:32', '2026-06-16 05:35:32'),
(41, 22, 27, 2, 17813000.00, '2026-06-18 20:48:43', '2026-06-18 20:48:43'),
(42, 22, 41, 2, 756000.00, '2026-06-18 20:48:43', '2026-06-18 20:48:43'),
(43, 23, 21, 1, 14827000.00, '2026-09-07 19:06:26', '2026-09-07 19:06:26'),
(44, 24, 12, 2, 12321000.00, '2026-08-14 23:49:05', '2026-08-14 23:49:05'),
(45, 24, 32, 1, 13103000.00, '2026-08-14 23:49:05', '2026-08-14 23:49:05'),
(47, 32, 46, 1, 15131000.00, '2026-09-19 15:10:53', '2026-09-19 16:46:17'),
(48, 32, 48, 1, 5095000.00, '2026-09-19 16:35:02', '2026-09-19 16:35:02');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Điện thoại', 'dien-thoai', 'Điện thoại thông minh đã qua sử dụng', NULL, 'active', '2026-09-08 07:39:56', '2026-09-08 07:39:56'),
(2, 'Laptop', 'laptop', 'Laptop đã qua sử dụng', NULL, 'active', '2026-09-08 07:39:56', '2026-09-08 07:39:56'),
(3, 'Tablet', 'tablet', 'Máy tính bảng đã qua sử dụng', NULL, 'active', '2026-09-08 07:39:56', '2026-09-08 07:39:56'),
(4, 'Đồng hồ thông minh', 'dong-ho-thong-minh', 'Smartwatch đã qua sử dụng', NULL, 'active', '2026-09-08 07:39:56', '2026-09-08 07:39:56'),
(5, 'Tai nghe', 'tai-nghe', 'Tai nghe và thiết bị âm thanh đã qua sử dụng', NULL, 'active', '2026-09-08 07:39:56', '2026-09-08 07:39:56'),
(6, 'Phụ kiện', 'phu-kien', 'Các loại phụ kiện điện tử', NULL, 'active', '2026-09-08 07:39:56', '2026-09-08 07:39:56'),
(8, 'Máy ảnh', 'may-anh', 'Máy ảnh kỹ thuật số và máy ảnh cơ đã qua sử dụng', NULL, 'active', '2026-06-10 06:20:00', '2026-06-10 06:20:00'),
(9, 'Loa Bluetooth', 'loa-bluetooth', 'Loa di động và loa Bluetooth đã qua sử dụng', NULL, 'active', '2026-08-03 07:41:43', '2026-08-03 07:41:43'),
(10, 'Bàn phím - Chuột', 'ban-phim-chuot', 'Bàn phím, chuột máy tính đã qua sử dụng', NULL, 'active', '2026-06-12 05:32:04', '2026-06-12 05:32:04'),
(11, 'Màn hình máy tính', 'man-hinh-may-tinh', 'Màn hình rời cho PC/laptop đã qua sử dụng', NULL, 'active', '2026-07-28 07:11:34', '2026-07-28 07:11:34');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(30) NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cod','bank_transfer','momo','vnpay') NOT NULL DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipping','delivered','cancelled','returned') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `receiver_name`, `receiver_phone`, `shipping_address`, `subtotal`, `shipping_fee`, `discount`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, '', 'Nguyễn Văn Test', '0901234567', 'TP. Hồ Chí Minh', 16500000.00, 0.00, 0.00, 16500000.00, 'cod', 'pending', 'delivered', 'Test đơn hàng', '2026-09-12 20:51:59', '2026-09-18 21:21:02'),
(2, 8, 'DH20260726162923002', 'Lê Minh Hải', '0975060685', '121 Đường Cách Mạng Tháng 8, TP. Hồ Chí Minh', 3596000.00, 20000.00, 50000.00, 3566000.00, 'momo', 'paid', 'pending', 'Gọi trước khi giao', '2026-07-26 16:29:23', '2026-07-27 03:29:23'),
(3, 7, 'DH20260805103358003', 'Lý Anh Thắng', '0901043289', '271 Đường Cách Mạng Tháng 8, TP. Hồ Chí Minh', 232000.00, 50000.00, 0.00, 282000.00, 'momo', 'paid', 'processing', NULL, '2026-08-05 10:33:58', '2026-08-06 05:33:58'),
(4, 4, 'DH20260717151231004', 'Đặng Quốc An', '0995396218', '185 Đường Lê Lợi, Hải Phòng', 15419000.00, 20000.00, 0.00, 15439000.00, 'cod', 'refunded', 'returned', NULL, '2026-07-17 15:12:31', '2026-07-19 21:12:31'),
(5, 3, 'DH20260815105951005', 'Đoàn Thành Em', '0905852772', '70 Đường Lê Lợi, Biên Hòa', 17080000.00, 20000.00, 0.00, 17100000.00, 'vnpay', 'pending', 'processing', 'Giao giờ hành chính', '2026-08-15 10:59:51', '2026-08-17 10:59:51'),
(6, 3, 'DH20260710013548006', 'Phan Ngọc Mai', '0905415667', '198 Đường Trần Hưng Đạo, Hà Nội', 4556000.00, 30000.00, 0.00, 4586000.00, 'vnpay', 'refunded', 'processing', NULL, '2026-07-10 01:35:48', '2026-07-10 07:35:48'),
(7, 18, 'DH20260617085951007', 'Phạm Thị Loan', '0944796275', '229 Đường Lê Lợi, Biên Hòa', 4789000.00, 30000.00, 0.00, 4819000.00, 'momo', 'refunded', 'processing', NULL, '2026-06-17 08:59:51', '2026-06-19 15:59:51'),
(8, 5, 'DH20260819050436008', 'Đỗ Thành Nam', '0990927557', '40 Đường Nguyễn Huệ, Hải Phòng', 36551000.00, 0.00, 0.00, 36551000.00, 'momo', 'refunded', 'processing', 'Gọi trước khi giao', '2026-08-19 05:04:36', '2026-08-21 04:04:36'),
(9, 16, 'DH20260623084231009', 'Phan Xuân Linh', '0973121727', '45 Đường Trần Hưng Đạo, Biên Hòa', 18451000.00, 30000.00, 50000.00, 18431000.00, 'momo', 'pending', 'delivered', NULL, '2026-06-23 08:42:31', '2026-06-23 12:42:31'),
(10, 19, 'DH20260729135357010', 'Lý Văn Mai', '0989578291', '34 Đường Trần Hưng Đạo, Cần Thơ', 17618000.00, 20000.00, 0.00, 17638000.00, 'vnpay', 'refunded', 'returned', NULL, '2026-07-29 13:53:57', '2026-07-30 10:53:57'),
(11, 5, 'DH20260625160354011', 'Đinh Văn Hải', '0918242253', '178 Đường Trần Hưng Đạo, Nha Trang', 23090000.00, 20000.00, 50000.00, 23060000.00, 'cod', 'failed', 'confirmed', NULL, '2026-06-25 16:03:54', '2026-06-27 09:03:54'),
(12, 18, 'DH20260625152009012', 'Trần Văn Cường', '0909439690', '255 Đường Trần Hưng Đạo, Biên Hòa', 14375000.00, 30000.00, 100000.00, 14305000.00, 'momo', 'refunded', 'confirmed', NULL, '2026-06-25 15:20:09', '2026-06-27 06:20:09'),
(13, 20, 'DH20260828094007013', 'Mai Hữu Loan', '0955625881', '164 Đường Nguyễn Huệ, Cần Thơ', 67878000.00, 20000.00, 0.00, 67898000.00, 'cod', 'failed', 'shipping', 'Giao giờ hành chính', '2026-08-28 09:40:07', '2026-08-28 19:40:07'),
(14, 10, 'DH20260620031030014', 'Đoàn Thành Hà', '0927877470', '47 Đường Cách Mạng Tháng 8, Hải Phòng', 12707000.00, 20000.00, 0.00, 12727000.00, 'vnpay', 'paid', 'confirmed', NULL, '2026-06-20 03:10:30', '2026-06-20 10:10:30'),
(15, 23, 'DH20260610184548015', 'Ngô Hữu Lan', '0945650609', '288 Đường Nguyễn Huệ, Đà Nẵng', 42594000.00, 0.00, 0.00, 42594000.00, 'momo', 'pending', 'shipping', NULL, '2026-06-10 18:45:48', '2026-06-11 01:45:48'),
(16, 29, 'DH20260916023111016', 'Đỗ Hữu Hà', '0998680002', '171 Đường Cách Mạng Tháng 8, Hải Phòng', 22773000.00, 30000.00, 100000.00, 22703000.00, 'vnpay', 'paid', 'delivered', NULL, '2026-09-16 02:31:11', '2026-09-17 14:31:11'),
(17, 9, 'DH20260804002553017', 'Đinh Đức Phong', '0994705516', '299 Đường Trần Hưng Đạo, Nha Trang', 21822000.00, 30000.00, 100000.00, 21752000.00, 'cod', 'refunded', 'processing', NULL, '2026-08-04 00:25:53', '2026-08-05 17:25:53'),
(18, 15, 'DH20260611071956018', 'Trần Xuân Tuấn', '0913075626', '105 Đường Cách Mạng Tháng 8, Hải Phòng', 1272000.00, 20000.00, 0.00, 1292000.00, 'vnpay', 'pending', 'cancelled', 'Giao giờ hành chính', '2026-06-11 07:19:56', '2026-06-13 21:19:56'),
(19, 14, 'DH20260803105026019', 'Lý Minh Hùng', '0948434437', '163 Đường Cách Mạng Tháng 8, Đà Nẵng', 19968000.00, 30000.00, 100000.00, 19898000.00, 'momo', 'failed', 'pending', NULL, '2026-08-03 10:50:26', '2026-08-06 00:50:26'),
(20, 25, 'DH20260816223916020', 'Đỗ Anh Trang', '0947338484', '71 Đường Lê Lợi, Hải Phòng', 17813000.00, 30000.00, 0.00, 17843000.00, 'bank_transfer', 'paid', 'pending', NULL, '2026-08-16 22:39:16', '2026-08-18 19:39:16'),
(21, 15, 'DH20260716130549021', 'Hoàng Văn Sơn', '0926773454', '31 Đường Lê Lợi, Biên Hòa', 6134000.00, 50000.00, 0.00, 6184000.00, 'bank_transfer', 'pending', 'confirmed', NULL, '2026-07-16 13:05:49', '2026-07-16 20:05:49'),
(22, 12, 'DH20260608092121022', 'Mai Thị Phúc', '0954788728', '240 Đường Trần Hưng Đạo, Hà Nội', 17895000.00, 30000.00, 100000.00, 17825000.00, 'cod', 'failed', 'confirmed', NULL, '2026-06-08 09:21:21', '2026-06-10 12:21:21'),
(23, 17, 'DH20260707114502023', 'Bùi Thị Nam', '0912275304', '197 Đường Nguyễn Huệ, Cần Thơ', 23570000.00, 50000.00, 100000.00, 23520000.00, 'momo', 'failed', 'processing', NULL, '2026-07-07 11:45:02', '2026-07-07 13:45:02'),
(24, 1, 'DH20260722025533024', 'Đặng Thị Trang', '0945933272', '233 Đường Trần Hưng Đạo, Cần Thơ', 37020000.00, 50000.00, 100000.00, 36970000.00, 'vnpay', 'paid', 'returned', 'Giao giờ hành chính', '2026-07-22 02:55:33', '2026-07-24 01:55:33'),
(25, 6, 'DH20260911090218025', 'Bùi Đức Oanh', '0913971870', '233 Đường Nguyễn Huệ, Hải Phòng', 16714000.00, 20000.00, 0.00, 16734000.00, 'vnpay', 'refunded', 'delivered', NULL, '2026-09-11 09:02:18', '2026-09-13 19:02:18'),
(26, 30, 'DH20260731041027026', 'Đỗ Thị Sơn', '0901704622', '216 Đường Trần Hưng Đạo, Cần Thơ', 33865000.00, 0.00, 50000.00, 33815000.00, 'vnpay', 'refunded', 'shipping', NULL, '2026-07-31 04:10:27', '2026-07-31 07:10:27'),
(27, 9, 'DH20260807204330027', 'Nguyễn Văn Linh', '0978686337', '178 Đường Nguyễn Huệ, Đà Nẵng', 11406000.00, 20000.00, 0.00, 11426000.00, 'bank_transfer', 'pending', 'pending', NULL, '2026-08-07 20:43:30', '2026-08-08 12:43:30'),
(28, 2, 'DH20260718013510028', 'Trần Minh Uyên', '0906738302', '258 Đường Trần Hưng Đạo, Hà Nội', 6780000.00, 50000.00, 100000.00, 6730000.00, 'momo', 'failed', 'confirmed', 'Gọi trước khi giao', '2026-07-18 01:35:10', '2026-07-19 04:35:10'),
(29, 10, 'DH20260910131221029', 'Lý Văn Mai', '0968566246', '95 Đường Cách Mạng Tháng 8, Hà Nội', 3320000.00, 50000.00, 100000.00, 3270000.00, 'bank_transfer', 'failed', 'returned', 'Giao giờ hành chính', '2026-09-10 13:12:21', '2026-09-11 18:12:21'),
(30, 30, 'DH20260612123249030', 'Phan Thành Trang', '0917518304', '194 Đường Lê Lợi, Nha Trang', 57669000.00, 20000.00, 0.00, 57689000.00, 'cod', 'paid', 'returned', NULL, '2026-06-12 12:32:49', '2026-06-15 12:32:49'),
(31, 14, 'DH20260902100820031', 'Vũ Xuân Tuấn', '0936385467', '274 Đường Trần Hưng Đạo, Đà Nẵng', 19412000.00, 0.00, 50000.00, 19362000.00, 'momo', 'failed', 'delivered', NULL, '2026-09-02 10:08:20', '2026-09-03 13:08:20'),
(32, 30, 'DH20260620113251032', 'Vũ Hữu An', '0948996423', '169 Đường Nguyễn Huệ, Cần Thơ', 29140000.00, 0.00, 100000.00, 29040000.00, 'bank_transfer', 'refunded', 'delivered', 'Gọi trước khi giao', '2026-06-20 11:32:51', '2026-06-23 00:32:51'),
(33, 17, 'DH20260904050821033', 'Trịnh Văn Quang', '0901006231', '78 Đường Lê Lợi, Hà Nội', 12503000.00, 50000.00, 0.00, 12553000.00, 'vnpay', 'failed', 'pending', NULL, '2026-09-04 05:08:21', '2026-09-06 20:08:21'),
(34, 31, 'DH20260904013358034', 'Đinh Ngọc Sơn', '0940862118', '77 Đường Nguyễn Huệ, Hà Nội', 11406000.00, 0.00, 0.00, 11406000.00, 'momo', 'failed', 'processing', NULL, '2026-09-04 01:33:58', '2026-09-04 04:33:58'),
(35, 6, 'DH20260812183246035', 'Lê Quốc Nam', '0968700217', '223 Đường Trần Hưng Đạo, Hải Phòng', 7481000.00, 0.00, 0.00, 7481000.00, 'cod', 'pending', 'confirmed', 'Giao giờ hành chính', '2026-08-12 18:32:46', '2026-08-14 22:32:46'),
(36, 31, 'DH20260723232340036', 'Nguyễn Minh Thảo', '0926142939', '200 Đường Trần Hưng Đạo, Cần Thơ', 1798000.00, 20000.00, 0.00, 1818000.00, 'bank_transfer', 'pending', 'delivered', 'Gọi trước khi giao', '2026-07-23 23:23:40', '2026-07-24 05:23:40'),
(37, 34, 'DH2026091909254621505D', 'bé tí teo', '0423754759', '123 Điện Biên Phủ', 636000.00, 0.00, 0.00, 636000.00, 'cod', 'pending', 'cancelled', NULL, '2026-09-19 07:25:46', '2026-09-19 14:59:57'),
(38, 34, 'DH20260919093415ECA5A6', 'bé tí teo', '0423754759', '123 Điện Biên Phủ', 4433000.00, 0.00, 0.00, 4433000.00, 'bank_transfer', 'pending', 'cancelled', NULL, '2026-09-19 07:34:15', '2026-09-19 15:00:03'),
(39, 34, 'DH20260919171039AA97FC', 'bé tí teo', '0423754759', '123 Điện Biên Phủ', 10890000.00, 0.00, 0.00, 10890000.00, 'cod', 'pending', 'pending', NULL, '2026-09-19 15:10:39', '2026-09-19 15:10:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `sku`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, NULL, 'iPhone 13 Pro Max 128GB', NULL, 1, 16500000.00, 16500000.00),
(2, 2, 26, 'Chuột Logitech MX Master 3', 'SP-20260701080744-0A3E', 2, 1798000.00, 3596000.00),
(3, 3, 11, 'Cáp sạc USB-C 1m', 'SP-20260629142415-25E6', 2, 116000.00, 232000.00),
(4, 4, 48, 'Màn hình LG UltraWide 29 inch', 'SP-20260731052018-1204', 1, 5095000.00, 5095000.00),
(5, 4, 38, 'Loa Marshall Emberton', 'SP-20260619190755-1F38', 2, 3426000.00, 6852000.00),
(6, 4, 13, 'Loa Marshall Emberton', 'SP-20260720215431-0481', 1, 3472000.00, 3472000.00),
(7, 5, 10, 'IPhone X', 'SP-20260915191528-5579FD', 1, 9870000.00, 9870000.00),
(8, 5, 31, 'AirPods 3', 'SP-20260606080839-04DB', 2, 3605000.00, 7210000.00),
(9, 6, 28, 'Chuột Logitech MX Master 3', 'SP-20260817152754-100D', 2, 1522000.00, 3044000.00),
(10, 6, 41, 'Sạc dự phòng Anker 20000mAh', 'SP-20260712152604-22C4', 2, 756000.00, 1512000.00),
(11, 7, 36, 'Màn hình LG UltraWide 29 inch', 'SP-20260912083426-20F1', 1, 4789000.00, 4789000.00),
(12, 8, 19, 'iPad Gen 9', 'SP-20260728151851-1E7F', 2, 8898000.00, 17796000.00),
(13, 8, 10, 'IPhone X', 'SP-20260915191528-5579FD', 1, 9870000.00, 9870000.00),
(14, 8, 18, 'iPad Gen 9', 'SP-20260608234321-201E', 1, 8885000.00, 8885000.00),
(15, 9, 20, 'Cáp sạc USB-C 1m', 'SP-20260721053402-16CB', 2, 82000.00, 164000.00),
(16, 9, 40, 'Samsung Galaxy S23 Ultra', 'SP-20260820023025-1EF9', 1, 17015000.00, 17015000.00),
(17, 9, 42, 'Sạc dự phòng Anker 20000mAh', 'SP-20260715055839-0596', 2, 636000.00, 1272000.00),
(18, 10, 49, 'Samsung Galaxy Watch 5', 'SP-20260627233337-166E', 1, 4433000.00, 4433000.00),
(19, 10, 20, 'Cáp sạc USB-C 1m', 'SP-20260721053402-16CB', 1, 82000.00, 82000.00),
(20, 10, 32, 'iPhone 13', 'SP-20260611121149-1ED9', 1, 13103000.00, 13103000.00),
(21, 11, 13, 'Loa Marshall Emberton', 'SP-20260720215431-0481', 2, 3472000.00, 6944000.00),
(22, 11, 37, 'HP Pavilion 14', 'SP-20260916092430-1F6E', 1, 12720000.00, 12720000.00),
(23, 11, 38, 'Loa Marshall Emberton', 'SP-20260619190755-1F38', 1, 3426000.00, 3426000.00),
(24, 12, 43, 'Sony WH-1000XM4', 'SP-20260808115315-0F84', 1, 5703000.00, 5703000.00),
(25, 12, 14, 'iPad Gen 9', 'SP-20260807102709-170D', 1, 7916000.00, 7916000.00),
(26, 12, 41, 'Sạc dự phòng Anker 20000mAh', 'SP-20260712152604-22C4', 1, 756000.00, 756000.00),
(27, 13, 12, 'iPad Air 5', 'SP-20260814144708-1351', 2, 12321000.00, 24642000.00),
(28, 13, 19, 'iPad Gen 9', 'SP-20260728151851-1E7F', 2, 8898000.00, 17796000.00),
(29, 13, 37, 'HP Pavilion 14', 'SP-20260916092430-1F6E', 2, 12720000.00, 25440000.00),
(30, 14, 33, 'Loa Marshall Emberton', 'SP-20260810151326-0AE2', 2, 3806000.00, 7612000.00),
(31, 14, 48, 'Màn hình LG UltraWide 29 inch', 'SP-20260731052018-1204', 1, 5095000.00, 5095000.00),
(32, 15, 40, 'Samsung Galaxy S23 Ultra', 'SP-20260820023025-1EF9', 2, 17015000.00, 34030000.00),
(33, 15, 47, 'AirPods 3', 'SP-20260908194224-19FF', 2, 4282000.00, 8564000.00),
(34, 16, 48, 'Màn hình LG UltraWide 29 inch', 'SP-20260731052018-1204', 1, 5095000.00, 5095000.00),
(35, 16, 16, 'Sony WH-1000XM4', 'SP-20260817043518-046C', 2, 5036000.00, 10072000.00),
(36, 16, 17, 'Loa Marshall Emberton', 'SP-20260716002545-1A74', 2, 3803000.00, 7606000.00),
(37, 17, 34, 'Samsung Galaxy S22', 'SP-20260607091021-228F', 2, 10911000.00, 21822000.00),
(38, 18, 42, 'Sạc dự phòng Anker 20000mAh', 'SP-20260715055839-0596', 2, 636000.00, 1272000.00),
(39, 19, 33, 'Loa Marshall Emberton', 'SP-20260810151326-0AE2', 2, 3806000.00, 7612000.00),
(40, 19, 29, 'Apple Watch Series 7', 'SP-20260805021555-0BF7', 2, 6096000.00, 12192000.00),
(41, 19, 20, 'Cáp sạc USB-C 1m', 'SP-20260721053402-16CB', 2, 82000.00, 164000.00),
(42, 20, 27, 'Samsung Galaxy S23 Ultra', 'SP-20260826101853-1304', 1, 17813000.00, 17813000.00),
(43, 21, 35, 'Loa Marshall Emberton', 'SP-20260617003039-0D66', 2, 3067000.00, 6134000.00),
(44, 22, 20, 'Cáp sạc USB-C 1m', 'SP-20260721053402-16CB', 1, 82000.00, 82000.00),
(45, 22, 27, 'Samsung Galaxy S23 Ultra', 'SP-20260826101853-1304', 1, 17813000.00, 17813000.00),
(46, 23, 30, 'Apple Watch SE', 'SP-20260804031154-0416', 1, 5479000.00, 5479000.00),
(47, 23, 27, 'Samsung Galaxy S23 Ultra', 'SP-20260826101853-1304', 1, 17813000.00, 17813000.00),
(48, 23, 45, 'Ốp lưng iPhone silicon', 'SP-20260731202007-0957', 2, 139000.00, 278000.00),
(49, 24, 24, 'Loa Marshall Emberton', 'SP-20260819144747-0CDA', 2, 3671000.00, 7342000.00),
(50, 24, 12, 'iPad Air 5', 'SP-20260814144708-1351', 2, 12321000.00, 24642000.00),
(51, 24, 16, 'Sony WH-1000XM4', 'SP-20260817043518-046C', 1, 5036000.00, 5036000.00),
(52, 25, 32, 'iPhone 13', 'SP-20260611121149-1ED9', 1, 13103000.00, 13103000.00),
(53, 25, 13, 'Loa Marshall Emberton', 'SP-20260720215431-0481', 1, 3472000.00, 3472000.00),
(54, 25, 45, 'Ốp lưng iPhone silicon', 'SP-20260731202007-0957', 1, 139000.00, 139000.00),
(55, 26, 25, 'MacBook Air M1', 'SP-20260913170855-1CC7', 1, 15975000.00, 15975000.00),
(56, 26, 9, 'IPhone 15 Pro', 'SP-20260914092659-3411D7', 1, 17890000.00, 17890000.00),
(57, 27, 43, 'Sony WH-1000XM4', 'SP-20260808115315-0F84', 2, 5703000.00, 11406000.00),
(58, 28, 15, 'Garmin Forerunner 245', 'SP-20260903035446-1316', 1, 6780000.00, 6780000.00),
(59, 29, 28, 'Chuột Logitech MX Master 3', 'SP-20260817152754-100D', 1, 1522000.00, 1522000.00),
(60, 29, 26, 'Chuột Logitech MX Master 3', 'SP-20260701080744-0A3E', 1, 1798000.00, 1798000.00),
(61, 30, 35, 'Loa Marshall Emberton', 'SP-20260617003039-0D66', 1, 3067000.00, 3067000.00),
(62, 30, 44, 'MacBook Pro 13 M2', 'SP-20260607065220-25DB', 2, 27301000.00, 54602000.00),
(63, 31, 28, 'Chuột Logitech MX Master 3', 'SP-20260817152754-100D', 1, 1522000.00, 1522000.00),
(64, 31, 9, 'IPhone 15 Pro', 'SP-20260914092659-3411D7', 1, 17890000.00, 17890000.00),
(65, 32, 22, 'Bàn phím cơ Logitech G Pro', 'SP-20260811214401-14AF', 2, 1467000.00, 2934000.00),
(66, 32, 32, 'iPhone 13', 'SP-20260611121149-1ED9', 2, 13103000.00, 26206000.00),
(67, 33, 19, 'iPad Gen 9', 'SP-20260728151851-1E7F', 1, 8898000.00, 8898000.00),
(68, 33, 31, 'AirPods 3', 'SP-20260606080839-04DB', 1, 3605000.00, 3605000.00),
(69, 34, 43, 'Sony WH-1000XM4', 'SP-20260808115315-0F84', 2, 5703000.00, 11406000.00),
(70, 35, 45, 'Ốp lưng iPhone silicon', 'SP-20260731202007-0957', 1, 139000.00, 139000.00),
(71, 35, 24, 'Loa Marshall Emberton', 'SP-20260819144747-0CDA', 2, 3671000.00, 7342000.00),
(72, 36, 26, 'Chuột Logitech MX Master 3', 'SP-20260701080744-0A3E', 1, 1798000.00, 1798000.00),
(73, 37, 42, 'Sạc dự phòng Anker 20000mAh', 'SP-20260715055839-0596', 1, 636000.00, 636000.00),
(74, 38, 49, 'Samsung Galaxy Watch 5', 'SP-20260627233337-166E', 1, 4433000.00, 4433000.00),
(75, 39, 52, 'Canon EOS M50', 'SP-20260917201245-FF8797', 1, 10890000.00, 10890000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `transaction_code` varchar(100) DEFAULT NULL,
  `payment_method` enum('cod','bank_transfer','momo','vnpay') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `transaction_code`, `payment_method`, `amount`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'TXN0002156304', 'momo', 3566000.00, 'success', '2026-07-27 03:29:23', '2026-07-26 16:29:23', '2026-07-27 03:29:23'),
(2, 3, 'TXN0003202430', 'momo', 282000.00, 'success', '2026-08-06 05:33:58', '2026-08-05 10:33:58', '2026-08-06 05:33:58'),
(3, 4, NULL, 'cod', 15439000.00, 'refunded', NULL, '2026-07-17 15:12:31', '2026-07-19 21:12:31'),
(4, 5, 'TXN0005558320', 'vnpay', 17100000.00, 'pending', NULL, '2026-08-15 10:59:51', '2026-08-17 10:59:51'),
(5, 6, 'TXN0006343420', 'vnpay', 4586000.00, 'refunded', NULL, '2026-07-10 01:35:48', '2026-07-10 07:35:48'),
(6, 7, 'TXN0007979414', 'momo', 4819000.00, 'refunded', NULL, '2026-06-17 08:59:51', '2026-06-19 15:59:51'),
(7, 8, 'TXN0008179710', 'momo', 36551000.00, 'refunded', NULL, '2026-08-19 05:04:36', '2026-08-21 04:04:36'),
(8, 9, 'TXN0009457137', 'momo', 18431000.00, 'pending', NULL, '2026-06-23 08:42:31', '2026-06-23 12:42:31'),
(9, 10, 'TXN0010734565', 'vnpay', 17638000.00, 'refunded', NULL, '2026-07-29 13:53:57', '2026-07-30 10:53:57'),
(10, 11, NULL, 'cod', 23060000.00, 'failed', NULL, '2026-06-25 16:03:54', '2026-06-27 09:03:54'),
(11, 12, 'TXN0012906360', 'momo', 14305000.00, 'refunded', NULL, '2026-06-25 15:20:09', '2026-06-27 06:20:09'),
(12, 13, NULL, 'cod', 67898000.00, 'failed', NULL, '2026-08-28 09:40:07', '2026-08-28 19:40:07'),
(13, 14, 'TXN0014743684', 'vnpay', 12727000.00, 'success', '2026-06-20 10:10:30', '2026-06-20 03:10:30', '2026-06-20 10:10:30'),
(14, 15, 'TXN0015724540', 'momo', 42594000.00, 'pending', NULL, '2026-06-10 18:45:48', '2026-06-11 01:45:48'),
(15, 16, 'TXN0016515973', 'vnpay', 22703000.00, 'success', '2026-09-17 14:31:11', '2026-09-16 02:31:11', '2026-09-17 14:31:11'),
(16, 17, NULL, 'cod', 21752000.00, 'refunded', NULL, '2026-08-04 00:25:53', '2026-08-05 17:25:53'),
(17, 18, 'TXN0018911131', 'vnpay', 1292000.00, 'pending', NULL, '2026-06-11 07:19:56', '2026-06-13 21:19:56'),
(18, 19, 'TXN0019442230', 'momo', 19898000.00, 'failed', NULL, '2026-08-03 10:50:26', '2026-08-06 00:50:26'),
(19, 20, 'TXN0020131064', 'bank_transfer', 17843000.00, 'success', '2026-08-18 19:39:16', '2026-08-16 22:39:16', '2026-08-18 19:39:16'),
(20, 21, 'TXN0021764926', 'bank_transfer', 6184000.00, 'pending', NULL, '2026-07-16 13:05:49', '2026-07-16 20:05:49'),
(21, 22, NULL, 'cod', 17825000.00, 'failed', NULL, '2026-06-08 09:21:21', '2026-06-10 12:21:21'),
(22, 23, 'TXN0023386604', 'momo', 23520000.00, 'failed', NULL, '2026-07-07 11:45:02', '2026-07-07 13:45:02'),
(23, 24, 'TXN0024921609', 'vnpay', 36970000.00, 'success', '2026-07-24 01:55:33', '2026-07-22 02:55:33', '2026-07-24 01:55:33'),
(24, 25, 'TXN0025572436', 'vnpay', 16734000.00, 'refunded', NULL, '2026-09-11 09:02:18', '2026-09-13 19:02:18'),
(25, 26, 'TXN0026614435', 'vnpay', 33815000.00, 'refunded', NULL, '2026-07-31 04:10:27', '2026-07-31 07:10:27'),
(26, 27, 'TXN0027339220', 'bank_transfer', 11426000.00, 'pending', NULL, '2026-08-07 20:43:30', '2026-08-08 12:43:30'),
(27, 28, 'TXN0028473095', 'momo', 6730000.00, 'failed', NULL, '2026-07-18 01:35:10', '2026-07-19 04:35:10'),
(28, 29, 'TXN0029679902', 'bank_transfer', 3270000.00, 'failed', NULL, '2026-09-10 13:12:21', '2026-09-11 18:12:21'),
(29, 30, NULL, 'cod', 57689000.00, 'success', '2026-06-15 12:32:49', '2026-06-12 12:32:49', '2026-06-15 12:32:49'),
(30, 31, 'TXN0031493663', 'momo', 19362000.00, 'failed', NULL, '2026-09-02 10:08:20', '2026-09-03 13:08:20'),
(31, 32, 'TXN0032552825', 'bank_transfer', 29040000.00, 'refunded', NULL, '2026-06-20 11:32:51', '2026-06-23 00:32:51'),
(32, 33, 'TXN0033294760', 'vnpay', 12553000.00, 'failed', NULL, '2026-09-04 05:08:21', '2026-09-06 20:08:21'),
(33, 34, 'TXN0034812711', 'momo', 11406000.00, 'failed', NULL, '2026-09-04 01:33:58', '2026-09-04 04:33:58'),
(34, 35, NULL, 'cod', 7481000.00, 'pending', NULL, '2026-08-12 18:32:46', '2026-08-14 22:32:46'),
(35, 36, 'TXN0036498535', 'bank_transfer', 1818000.00, 'pending', NULL, '2026-07-23 23:23:40', '2026-07-24 05:23:40'),
(36, 37, NULL, 'cod', 636000.00, 'pending', NULL, '2026-09-19 07:25:46', '2026-09-19 07:25:46'),
(37, 38, NULL, 'bank_transfer', 4433000.00, 'pending', NULL, '2026-09-19 07:34:15', '2026-09-19 07:34:15'),
(38, 39, NULL, 'cod', 10890000.00, 'pending', NULL, '2026-09-19 15:10:39', '2026-09-19 15:10:39');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `sku` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `price` decimal(15,2) NOT NULL,
  `condition_type` enum('like_new','excellent','good','fair','poor') NOT NULL DEFAULT 'good',
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('available','sold_out','hidden') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `category_id`, `sku`, `name`, `description`, `specifications`, `price`, `condition_type`, `stock`, `status`, `created_at`, `updated_at`) VALUES
(9, NULL, 1, 'SP-20260914092659-3411D7', 'IPhone 15 Pro', 'sdafdf', '{\"Dung lượng\":\"128GB\",\"RAM\":\"8GB\",\"Màu sắc\":\"Xám Titan\",\"Chip\":\"Apple A17 Pro\",\"Màn hình\":\"6.1 inch Super Retina XDR\"}', 17890000.00, 'excellent', 1, 'available', '2026-09-14 00:26:59', '2026-09-15 10:21:21'),
(10, NULL, 1, 'SP-20260915191528-5579FD', 'IPhone X', NULL, '{\"Dung lượng\":\"64GB\",\"RAM\":\"3GB\",\"Màu sắc\":\"Đen\",\"Chip\":\"Apple A11 Bionic\",\"Màn hình\":\"5.8 inch OLED\"}', 9870000.00, 'good', 1, 'available', '2026-09-15 10:15:28', '2026-09-15 10:15:28'),
(11, 27, 6, 'SP-20260629142415-25E6', 'Cáp sạc USB-C 1m', 'Cáp sạc USB-C 1m tình trạng poor, đầy đủ phụ kiện.', '{\"Chiều dài\":\"1m\",\"Chuẩn kết nối\":\"USB-C to USB-C\",\"Công suất sạc\":\"60W\",\"Chất liệu\":\"Dây bọc nylon\"}', 116000.00, 'poor', 3, 'available', '2026-06-29 07:24:15', '2026-07-02 07:24:15'),
(12, 28, 3, 'SP-20260814144708-1351', 'iPad Air 5', 'iPad Air 5 tình trạng poor, đầy đủ phụ kiện.', '{\"Dung lượng\":\"64GB\",\"Màu sắc\":\"Xanh dương\",\"Kết nối\":\"Wi-Fi\",\"Màn hình\":\"10.9 inch Liquid Retina\",\"Chip\":\"Apple M1\"}', 12321000.00, 'poor', 1, 'available', '2026-08-14 07:47:08', '2026-08-23 07:47:08'),
(13, 28, 9, 'SP-20260720215431-0481', 'Loa Marshall Emberton', 'Loa Marshall Emberton tình trạng good, đầy đủ phụ kiện.', '{\"Công suất\":\"20W\",\"Kết nối\":\"Bluetooth 5.1\",\"Thời lượng pin\":\"20 giờ\",\"Chống nước\":\"IP67\",\"Màu sắc\":\"Đen\"}', 3472000.00, 'good', 3, 'sold_out', '2026-07-20 14:54:31', '2026-07-30 14:54:31'),
(14, 27, 3, 'SP-20260807102709-170D', 'iPad Gen 9', 'iPad Gen 9 tình trạng good, đầy đủ phụ kiện.', '{\"Dung lượng\":\"64GB\",\"Màu sắc\":\"Bạc\",\"Kết nối\":\"Wi-Fi\",\"Màn hình\":\"10.2 inch Retina\",\"Chip\":\"Apple A13 Bionic\"}', 7916000.00, 'good', 2, 'available', '2026-08-07 03:27:09', '2026-08-16 03:27:09'),
(15, 27, 4, 'SP-20260903035446-1316', 'Garmin Forerunner 245', 'Garmin Forerunner 245 tình trạng poor, đầy đủ phụ kiện.', '{\"Kích thước mặt\":\"42mm\",\"Pin\":\"7 ngày\",\"GPS\":\"Có\",\"Chống nước\":\"5 ATM\",\"Màu sắc\":\"Đen\"}', 6780000.00, 'poor', 2, 'sold_out', '2026-09-02 20:54:46', '2026-09-03 20:54:46'),
(16, 2, 5, 'SP-20260817043518-046C', 'Sony WH-1000XM4', 'Sony WH-1000XM4 tình trạng like new, đầy đủ phụ kiện.', '{\"Kết nối\":\"Bluetooth 5.0\",\"Chống ồn\":\"Chủ động (ANC)\",\"Thời lượng pin\":\"30 giờ\",\"Màu sắc\":\"Đen\",\"Trọng lượng\":\"254g\"}', 5036000.00, 'like_new', 3, 'hidden', '2026-08-16 21:35:18', '2026-08-20 21:35:18'),
(17, 11, 9, 'SP-20260716002545-1A74', 'Loa Marshall Emberton', 'Loa Marshall Emberton tình trạng excellent, đầy đủ phụ kiện.', '{\"Công suất\":\"20W\",\"Kết nối\":\"Bluetooth 5.1\",\"Thời lượng pin\":\"20 giờ\",\"Chống nước\":\"IP67\",\"Màu sắc\":\"Kem\"}', 3803000.00, 'excellent', 3, 'available', '2026-07-15 17:25:45', '2026-07-24 17:25:45'),
(18, NULL, 3, 'SP-20260608234321-201E', 'iPad Gen 9', 'iPad Gen 9 tình trạng poor, đầy đủ phụ kiện.', '{\"Dung lượng\":\"256GB\",\"Màu sắc\":\"Xanh\",\"Kết nối\":\"Wi-Fi\",\"Màn hình\":\"10.2 inch Retina\",\"Chip\":\"Apple A13 Bionic\"}', 8885000.00, 'poor', 2, 'hidden', '2026-06-08 16:43:21', '2026-06-12 16:43:21'),
(19, 7, 3, 'SP-20260728151851-1E7F', 'iPad Gen 9', 'iPad Gen 9 tình trạng excellent, đầy đủ phụ kiện.', '{\"Dung lượng\":\"64GB\",\"Màu sắc\":\"Vàng Gold\",\"Kết nối\":\"Wi-Fi + 4G\",\"Màn hình\":\"10.2 inch Retina\",\"Chip\":\"Apple A13 Bionic\"}', 8898000.00, 'excellent', 1, 'sold_out', '2026-07-28 08:18:51', '2026-08-02 08:18:51'),
(20, 24, 6, 'SP-20260721053402-16CB', 'Cáp sạc USB-C 1m', 'Cáp sạc USB-C 1m tình trạng like new, đầy đủ phụ kiện.', '{\"Chiều dài\":\"1m\",\"Chuẩn kết nối\":\"USB-C to Lightning\",\"Công suất sạc\":\"20W\",\"Chất liệu\":\"Dây bọc nylon\"}', 82000.00, 'like_new', 2, 'available', '2026-07-20 22:34:02', '2026-07-27 22:34:02'),
(21, 4, 3, 'SP-20260817120418-1B50', 'iPad Air 5', 'iPad Air 5 tình trạng fair, đầy đủ phụ kiện.', '{\"Dung lượng\":\"256GB\",\"Màu sắc\":\"Xám\",\"Kết nối\":\"Wi-Fi\",\"Màn hình\":\"10.9 inch Liquid Retina\",\"Chip\":\"Apple M1\"}', 14827000.00, 'fair', 1, 'available', '2026-08-17 05:04:18', '2026-08-25 05:04:18'),
(22, 2, 10, 'SP-20260811214401-14AF', 'Bàn phím cơ Logitech G Pro', 'Bàn phím cơ Logitech G Pro tình trạng fair, đầy đủ phụ kiện.', '{\"Loại switch\":\"GX Blue Clicky\",\"Kết nối\":\"Có dây USB\",\"Layout\":\"TKL (Tenkeyless)\",\"Đèn nền\":\"RGB\"}', 1467000.00, 'fair', 3, 'available', '2026-08-11 14:44:01', '2026-08-21 14:44:01'),
(23, 2, 2, 'SP-20260831211429-18E6', 'HP Pavilion 14', 'HP Pavilion 14 tình trạng excellent, đầy đủ phụ kiện.', '{\"CPU\":\"Intel Core i5-1235U\",\"RAM\":\"8GB\",\"Ổ cứng\":\"512GB SSD\",\"Màn hình\":\"14 inch FHD\",\"Card đồ họa\":\"Intel Iris Xe\"}', 10971000.00, 'excellent', 3, 'available', '2026-08-31 14:14:29', '2026-08-31 14:14:29'),
(24, NULL, 9, 'SP-20260819144747-0CDA', 'Loa Marshall Emberton', 'Loa Marshall Emberton tình trạng like new, đầy đủ phụ kiện.', '{\"Công suất\":\"20W\",\"Kết nối\":\"Bluetooth 5.1\",\"Thời lượng pin\":\"20 giờ\",\"Chống nước\":\"IP67\",\"Màu sắc\":\"Cam\"}', 3671000.00, 'like_new', 2, 'available', '2026-08-19 07:47:47', '2026-08-20 07:47:47'),
(25, 28, 2, 'SP-20260913170855-1CC7', 'MacBook Air M1', 'MacBook Air M1 tình trạng good, đầy đủ phụ kiện.', '{\"CPU\":\"Apple M1\",\"RAM\":\"8GB\",\"Ổ cứng\":\"256GB SSD\",\"Màn hình\":\"13.3 inch Retina\",\"Màu sắc\":\"Bạc\"}', 15975000.00, 'good', 3, 'hidden', '2026-09-13 10:08:55', '2026-09-15 10:08:55'),
(26, 28, 10, 'SP-20260701080744-0A3E', 'Chuột Logitech MX Master 3', 'Chuột Logitech MX Master 3 tình trạng poor, đầy đủ phụ kiện.', '{\"Kết nối\":\"Bluetooth / USB Receiver\",\"DPI\":\"4000\",\"Pin\":\"70 ngày\",\"Số nút\":\"7 nút\"}', 1798000.00, 'poor', 2, 'available', '2026-07-01 01:07:44', '2026-07-07 01:07:44'),
(27, 24, 1, 'SP-20260826101853-1304', 'Samsung Galaxy S23 Ultra', 'Samsung Galaxy S23 Ultra tình trạng like new, đầy đủ phụ kiện.', '{\"Dung lượng\":\"256GB\",\"RAM\":\"12GB\",\"Màu sắc\":\"Đen\",\"Chip\":\"Snapdragon 8 Gen 2\",\"Màn hình\":\"6.8 inch Dynamic AMOLED\"}', 17813000.00, 'like_new', 3, 'available', '2026-08-26 03:18:53', '2026-09-02 03:18:53'),
(28, 4, 10, 'SP-20260817152754-100D', 'Chuột Logitech MX Master 3', 'Chuột Logitech MX Master 3 tình trạng fair, đầy đủ phụ kiện.', '{\"Kết nối\":\"Bluetooth / USB Receiver\",\"DPI\":\"4000\",\"Pin\":\"70 ngày\",\"Số nút\":\"7 nút\"}', 1522000.00, 'fair', 1, 'hidden', '2026-08-17 08:27:54', '2026-08-21 08:27:54'),
(29, 2, 4, 'SP-20260805021555-0BF7', 'Apple Watch Series 7', 'Apple Watch Series 7 tình trạng excellent, đầy đủ phụ kiện.', '{\"Kích thước mặt\":\"41mm\",\"Chất liệu dây\":\"Dây cao su\",\"Kết nối\":\"GPS\",\"Màu sắc\":\"Đen\",\"Pin\":\"18 giờ\"}', 6096000.00, 'excellent', 1, 'sold_out', '2026-08-04 19:15:55', '2026-08-07 19:15:55'),
(30, 12, 4, 'SP-20260804031154-0416', 'Apple Watch SE', 'Apple Watch SE tình trạng excellent, đầy đủ phụ kiện.', '{\"Kích thước mặt\":\"40mm\",\"Chất liệu dây\":\"Dây cao su\",\"Kết nối\":\"GPS\",\"Màu sắc\":\"Bạc\",\"Pin\":\"18 giờ\"}', 5479000.00, 'excellent', 1, 'sold_out', '2026-08-03 20:11:54', '2026-08-05 20:11:54'),
(31, 24, 5, 'SP-20260606080839-04DB', 'AirPods 3', 'AirPods 3 tình trạng excellent, đầy đủ phụ kiện.', '{\"Kết nối\":\"Bluetooth 5.0\",\"Chống ồn\":\"Không\",\"Thời lượng pin\":\"6 giờ (1 lần sạc)\",\"Chống nước\":\"IPX4\"}', 3605000.00, 'excellent', 3, 'available', '2026-06-06 01:08:39', '2026-06-08 01:08:39'),
(32, 1, 1, 'SP-20260611121149-1ED9', 'iPhone 13', 'iPhone 13 tình trạng like new, đầy đủ phụ kiện.', '{\"Dung lượng\":\"128GB\",\"RAM\":\"4GB\",\"Màu sắc\":\"Xanh\",\"Chip\":\"Apple A15 Bionic\",\"Màn hình\":\"6.1 inch OLED\"}', 13103000.00, 'like_new', 3, 'available', '2026-06-11 05:11:49', '2026-06-13 05:11:49'),
(33, 28, 9, 'SP-20260810151326-0AE2', 'Loa Marshall Emberton', 'Loa Marshall Emberton tình trạng poor, đầy đủ phụ kiện.', '{\"Công suất\":\"20W\",\"Kết nối\":\"Bluetooth 5.1\",\"Thời lượng pin\":\"20 giờ\",\"Chống nước\":\"IP67\",\"Màu sắc\":\"Xám\"}', 3806000.00, 'poor', 1, 'sold_out', '2026-08-10 08:13:26', '2026-08-18 08:13:26'),
(34, 27, 1, 'SP-20260607091021-228F', 'Samsung Galaxy S22', 'Samsung Galaxy S22 tình trạng fair, đầy đủ phụ kiện.', '{\"Dung lượng\":\"128GB\",\"RAM\":\"8GB\",\"Màu sắc\":\"Tím\",\"Chip\":\"Snapdragon 8 Gen 1\",\"Màn hình\":\"6.1 inch Dynamic AMOLED\"}', 10911000.00, 'fair', 3, 'available', '2026-06-07 02:10:21', '2026-06-07 02:10:21'),
(35, 4, 9, 'SP-20260617003039-0D66', 'Loa Marshall Emberton', 'Loa Marshall Emberton tình trạng excellent, đầy đủ phụ kiện.', '{\"Công suất\":\"20W\",\"Kết nối\":\"Bluetooth 5.1\",\"Thời lượng pin\":\"20 giờ\",\"Chống nước\":\"IP67\",\"Màu sắc\":\"Đen\"}', 3067000.00, 'excellent', 2, 'sold_out', '2026-06-16 17:30:39', '2026-06-21 17:30:39'),
(36, 1, 11, 'SP-20260912083426-20F1', 'Màn hình LG UltraWide 29 inch', 'Màn hình LG UltraWide 29 inch tình trạng poor, đầy đủ phụ kiện.', '{\"Kích thước\":\"29 inch\",\"Độ phân giải\":\"2560x1080 (UltraWide)\",\"Tần số quét\":\"75Hz\",\"Cổng kết nối\":\"HDMI, DisplayPort\"}', 4789000.00, 'poor', 2, 'available', '2026-09-12 01:34:26', '2026-09-16 01:34:26'),
(37, 28, 2, 'SP-20260916092430-1F6E', 'HP Pavilion 14', 'HP Pavilion 14 tình trạng excellent, đầy đủ phụ kiện.', '{\"CPU\":\"Intel Core i5-1235U\",\"RAM\":\"16GB\",\"Ổ cứng\":\"512GB SSD\",\"Màn hình\":\"14 inch FHD\",\"Card đồ họa\":\"Intel Iris Xe\"}', 12720000.00, 'excellent', 2, 'available', '2026-09-16 02:24:30', '2026-09-19 02:24:30'),
(38, 24, 9, 'SP-20260619190755-1F38', 'Loa Marshall Emberton', 'Loa Marshall Emberton tình trạng good, đầy đủ phụ kiện.', '{\"Công suất\":\"20W\",\"Kết nối\":\"Bluetooth 5.1\",\"Thời lượng pin\":\"20 giờ\",\"Chống nước\":\"IP67\",\"Màu sắc\":\"Nâu\"}', 3426000.00, 'good', 2, 'available', '2026-06-19 12:07:55', '2026-06-24 12:07:55'),
(39, 4, 9, 'SP-20260617214936-1F8B', 'Loa JBL Flip 6', 'Loa JBL Flip 6 tình trạng good, đầy đủ phụ kiện.', '{\"Công suất\":\"20W\",\"Kết nối\":\"Bluetooth 5.1\",\"Thời lượng pin\":\"12 giờ\",\"Chống nước\":\"IP67\",\"Màu sắc\":\"Đen\"}', 2086000.00, 'good', 1, 'sold_out', '2026-06-17 14:49:36', '2026-06-18 14:49:36'),
(40, NULL, 1, 'SP-20260820023025-1EF9', 'Samsung Galaxy S23 Ultra', 'Samsung Galaxy S23 Ultra tình trạng good, đầy đủ phụ kiện.', '{\"Dung lượng\":\"512GB\",\"RAM\":\"12GB\",\"Màu sắc\":\"Xanh\",\"Chip\":\"Snapdragon 8 Gen 2\",\"Màn hình\":\"6.8 inch Dynamic AMOLED\"}', 17015000.00, 'good', 3, 'available', '2026-08-19 19:30:25', '2026-08-24 19:30:25'),
(41, 11, 6, 'SP-20260712152604-22C4', 'Sạc dự phòng Anker 20000mAh', 'Sạc dự phòng Anker 20000mAh tình trạng like new, đầy đủ phụ kiện.', '{\"Dung lượng pin\":\"20000mAh\",\"Công suất ra\":\"22.5W (PD/QC)\",\"Cổng kết nối\":\"USB-A, USB-C\",\"Trọng lượng\":\"356g\"}', 756000.00, 'like_new', 2, 'sold_out', '2026-07-12 08:26:04', '2026-07-14 08:26:04'),
(42, 2, 6, 'SP-20260715055839-0596', 'Sạc dự phòng Anker 20000mAh', 'Sạc dự phòng Anker 20000mAh tình trạng good, đầy đủ phụ kiện.', '{\"Dung lượng pin\":\"20000mAh\",\"Công suất ra\":\"22.5W (PD/QC)\",\"Cổng kết nối\":\"USB-A, USB-C\",\"Trọng lượng\":\"356g\"}', 636000.00, 'good', 0, 'sold_out', '2026-07-14 22:58:39', '2026-09-19 07:25:46'),
(43, 7, 5, 'SP-20260808115315-0F84', 'Sony WH-1000XM4', 'Sony WH-1000XM4 tình trạng poor, đầy đủ phụ kiện.', '{\"Kết nối\":\"Bluetooth 5.0\",\"Chống ồn\":\"Chủ động (ANC)\",\"Thời lượng pin\":\"30 giờ\",\"Màu sắc\":\"Xanh\",\"Trọng lượng\":\"254g\"}', 5703000.00, 'poor', 3, 'available', '2026-08-08 04:53:15', '2026-08-08 04:53:15'),
(44, 11, 2, 'SP-20260607065220-25DB', 'MacBook Pro 13 M2', 'MacBook Pro 13 M2 tình trạng fair, đầy đủ phụ kiện.', '{\"CPU\":\"Apple M2\",\"RAM\":\"16GB\",\"Ổ cứng\":\"512GB SSD\",\"Màn hình\":\"13.3 inch Retina\",\"Màu sắc\":\"Xám\"}', 27301000.00, 'fair', 2, 'available', '2026-06-06 23:52:20', '2026-06-07 23:52:20'),
(45, NULL, 6, 'SP-20260731202007-0957', 'Ốp lưng iPhone silicon', 'Ốp lưng iPhone silicon tình trạng excellent, đầy đủ phụ kiện.', '{\"Chất liệu\":\"Silicon\",\"Dòng máy tương thích\":\"iPhone 13/14\",\"Màu sắc\":\"Đen\",\"Chống sốc\":\"Có\"}', 139000.00, 'excellent', 1, 'sold_out', '2026-07-31 13:20:07', '2026-08-05 13:20:07'),
(46, 28, 1, 'SP-20260826090322-1F0B', 'iPhone 12 Pro Max', 'iPhone 12 Pro Max tình trạng poor, đầy đủ phụ kiện.', '{\"Dung lượng\":\"128GB\",\"RAM\":\"6GB\",\"Màu sắc\":\"Xanh Thái Bình Dương\",\"Chip\":\"Apple A14 Bionic\",\"Màn hình\":\"6.7 inch OLED\"}', 15131000.00, 'poor', 2, 'available', '2026-08-26 02:03:22', '2026-09-05 02:03:22'),
(47, 27, 5, 'SP-20260908194224-19FF', 'AirPods 3', 'AirPods 3 tình trạng like new, đầy đủ phụ kiện.', '{\"Kết nối\":\"Bluetooth 5.0\",\"Chống ồn\":\"Không\",\"Thời lượng pin\":\"6 giờ (1 lần sạc)\",\"Chống nước\":\"IPX4\"}', 4282000.00, 'like_new', 2, 'hidden', '2026-09-08 12:42:24', '2026-09-09 12:42:24'),
(48, 27, 11, 'SP-20260731052018-1204', 'Màn hình LG UltraWide 29 inch', 'Màn hình LG UltraWide 29 inch tình trạng poor, đầy đủ phụ kiện.', '{\"Kích thước\":\"29 inch\",\"Độ phân giải\":\"2560x1080 (UltraWide)\",\"Tần số quét\":\"75Hz\",\"Cổng kết nối\":\"HDMI, DisplayPort\"}', 5095000.00, 'poor', 1, 'available', '2026-07-30 22:20:18', '2026-07-30 22:20:18'),
(49, 24, 4, 'SP-20260627233337-166E', 'Samsung Galaxy Watch 5', 'Samsung Galaxy Watch 5 tình trạng like new, đầy đủ phụ kiện.', '{\"Kích thước mặt\":\"40mm\",\"Chất liệu dây\":\"Fluoroelastomer\",\"Kết nối\":\"Bluetooth\",\"Màu sắc\":\"Bạc\",\"Pin\":\"40 giờ\"}', 4433000.00, 'like_new', 0, 'sold_out', '2026-06-27 16:33:37', '2026-09-19 07:34:15'),
(52, NULL, 8, 'SP-20260917201245-FF8797', 'Canon EOS M50', 'Canon EOS M50 nhỏ gọn, đầy đủ phụ kiện.\r\nChụp ảnh sắc nét, quay video chất lượng tốt.', '{\"Độ phân giải\":\"24.1 MP\",\"Cảm biến\":\"APS-C CMOS\",\"Quay video\":\"4K 24fps\",\"Ống kính kèm theo\":\"15-45mm\",\"Kết nối\":\"Wi-Fi, Bluetooth\"}', 10890000.00, 'excellent', 0, 'available', '2026-09-18 21:15:57', '2026-09-19 15:10:39'),
(53, NULL, 1, 'SP-20260918213948-23B017', 'IPhone 16 Pro', NULL, '{\"Strorage\":\"256 GB\"}', 18700000.00, 'excellent', 1, 'available', '2026-09-18 21:15:57', '2026-09-18 21:15:57');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 9, 'uploads/iphone_15_pro.png', 1, 0, '2026-09-15 10:15:28'),
(7, 10, 'uploads/iphone-x-64gb-bac-org.png', 1, 0, '2026-09-15 10:30:19'),
(8, 11, 'uploads/cáp_sạc_USB-C_1m.webp', 1, 0, '2026-06-29 07:24:15'),
(9, 12, 'uploads/ipad_air_5__colors__06251c7b63d5478188404b205b5b5fdb_master_e4974377db874cdba8a161c22deb4bcd_master.webp', 1, 0, '2026-08-14 07:47:08'),
(10, 13, 'uploads/loa-marshall-Emberton1-black-moi-2023.jpg', 1, 0, '2026-07-20 14:54:31'),
(11, 13, 'uploads/Loa Marshall Emberton 2.jpg', 0, 1, '2026-07-20 14:54:31'),
(12, 14, 'uploads/Ipad_gen_9.webp', 1, 0, '2026-07-20 14:54:31'),
(13, 15, 'uploads/Garmin_Forerunner_245.webp', 1, 0, '2026-08-07 03:27:09'),
(14, 16, 'uploads/Sony WH-1000XM4.png', 1, 0, '2026-09-02 20:54:46'),
(15, 17, 'uploads/product_17_15.jpg', 1, 0, '2026-07-16 00:25:45'),
(16, 17, 'uploads/loa-marshall-Emberton1-black-moi-2023.jpg', 1, 0, '2026-08-16 21:35:18'),
(17, 18, 'uploads/Ipad_gen_9.webp', 1, 0, '2026-07-15 17:25:45'),
(18, 19, 'uploads/Ipad_gen_9.webp', 1, 0, '2026-06-08 16:43:21'),
(19, 20, 'uploads/cáp_sạc_USB-C_1m.webp', 1, 0, '2026-07-28 08:18:51'),
(20, 21, 'uploads/ipad_air_5__colors__06251c7b63d5478188404b205b5b5fdb_master_e4974377db874cdba8a161c22deb4bcd_master.webp', 1, 0, '2026-07-20 22:34:02'),
(21, 22, 'uploads/Bàn phím cơ Logitech G Pro.jpg', 1, 0, '2026-07-20 22:34:02'),
(22, 23, 'uploads/HP Pavilion 14.jpg', 1, 0, '2026-08-17 05:04:18'),
(23, 24, 'uploads/loa-marshall-Emberton1-black-moi-2023.jpg', 1, 0, '2026-08-11 14:44:01'),
(24, 25, 'uploads/Macbook-Air-2020-ARM.png', 1, 0, '2026-08-11 14:44:01'),
(25, 26, 'uploads/Chuột Logitech MX Master 3.jpg', 1, 0, '2026-08-31 14:14:29'),
(26, 27, 'uploads/Samsung Galaxy S23 Ultra.jpeg', 1, 0, '2026-08-19 07:47:47'),
(27, 28, 'uploads/Chuột Logitech MX Master 3.jpg', 1, 0, '2026-08-19 07:47:47'),
(28, 29, 'uploads/Apple Watch Series 7.webp', 1, 0, '2026-09-13 10:08:55'),
(29, 30, 'uploads/Apple Watch SE.jpg', 1, 0, '2026-09-13 10:08:55'),
(30, 31, 'uploads/AirPods 3.jpg', 1, 0, '2026-07-01 01:07:44'),
(31, 32, 'uploads/iphone-13_2_2.webp', 1, 0, '2026-08-26 03:18:53'),
(32, 33, 'uploads/loa-marshall-Emberton1-black-moi-2023.jpg', 1, 0, '2026-08-17 08:27:54'),
(33, 34, 'uploads/Samsung Galaxy S22.jpg', 1, 0, '2026-08-04 19:15:55'),
(34, 35, 'uploads/loa-marshall-Emberton1-black-moi-2023.jpg', 1, 0, '2026-08-03 20:11:54'),
(35, 36, 'uploads/Màn hình LG UltraWide 29 inch.avif', 1, 0, '2026-06-06 01:08:39'),
(36, 37, 'uploads/HP Pavilion 14.jpg', 1, 0, '2026-06-11 05:11:49'),
(37, 38, 'uploads/loa-marshall-Emberton1-black-moi-2023.jpg', 1, 0, '2026-08-10 08:13:26'),
(38, 39, 'uploads/Loa JBL Flip 6.jpg', 1, 0, '2026-08-10 08:13:26'),
(39, 40, 'uploads/Samsung Galaxy S23 Ultra.jpeg', 1, 0, '2026-06-07 02:10:21'),
(40, 41, 'uploads/Sạc dự phòng Anker 20000mAh.webp', 1, 0, '2026-06-16 17:30:39'),
(41, 42, 'uploads/Sạc dự phòng Anker 20000mAh 2.webp', 1, 0, '2026-06-16 17:30:39'),
(42, 43, 'uploads/Sony WH-1000XM4 2.jpg', 0, 1, '2026-09-12 01:34:26'),
(43, 44, 'uploads/MacBook Pro 13 M2.jpg', 1, 0, '2026-09-16 02:24:30'),
(44, 45, 'uploads/Ốp lưng iPhone silicon.jpeg', 1, 0, '2026-06-19 12:07:55'),
(45, 46, 'uploads/iPhone 12 Pro Max.webp', 1, 0, '2026-06-17 14:49:36'),
(46, 47, 'uploads/AirPods 3.jpg', 1, 0, '2026-08-19 19:30:25'),
(47, 48, 'uploads/Màn hình LG UltraWide 29 inch.avif', 1, 0, '2026-07-12 08:26:04'),
(48, 49, 'uploads/Samsung Galaxy Watch 5.webp', 1, 0, '2026-07-14 22:58:39'),
(50, 43, 'uploads/Sony WH-1000XM4.png', 1, 0, '2026-08-08 04:53:15'),
(51, 44, 'uploads/MacBook Pro 13 M2.jpg', 0, 1, '2026-06-06 23:52:20'),
(52, 44, 'uploads/MacBook Pro 13 M2.jpg', 0, 2, '2026-06-06 23:52:20'),
(53, 45, 'uploads/Ốp lưng iPhone silicon.jpeg', 0, 1, '2026-07-31 13:20:07'),
(54, 46, 'uploads/iPhone 12 Pro Max.webp', 0, 1, '2026-08-26 02:03:22'),
(55, 47, 'uploads/AirPods 3.jpg', 0, 1, '2026-09-08 12:42:24'),
(56, 48, 'uploads/Màn hình LG UltraWide 29 inch.avif', 0, 1, '2026-07-30 22:20:18'),
(58, 49, 'uploads/Samsung Galaxy Watch 5.webp', 0, 1, '2026-06-27 16:33:37'),
(61, 52, 'uploads/product_52_1789668773.webp', 1, 0, '2026-09-17 18:12:53');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(1, 36, 18, 7, 3, 'Máy còn khá mới, pin trâu, rất hài lòng.', 'visible', '2026-06-19 15:59:51', '2026-06-19 15:59:51'),
(2, 37, 5, 11, 4, 'Giao hàng nhanh, người bán nhiệt tình tư vấn.', 'visible', '2026-06-27 09:03:54', '2026-06-27 09:03:54'),
(3, 9, 30, 26, 5, 'Máy còn khá mới, pin trâu, rất hài lòng.', 'visible', '2026-07-31 07:10:27', '2026-07-31 07:10:27'),
(4, 26, 8, 2, 5, 'Chất lượng tốt hơn mong đợi, sẽ ủng hộ tiếp.', 'visible', '2026-07-27 03:29:23', '2026-07-27 03:29:23'),
(5, 19, 5, 8, 5, 'Đóng gói hơi sơ sài nhưng máy vẫn ổn.', 'visible', '2026-08-21 04:04:36', '2026-08-21 04:04:36'),
(6, 44, 30, 30, 5, 'Máy còn khá mới, pin trâu, rất hài lòng.', 'visible', '2026-06-15 12:32:49', '2026-06-15 12:32:49'),
(7, 16, 1, 24, 5, 'Tư vấn nhiệt tình, hỗ trợ đổi trả nhanh chóng.', 'visible', '2026-07-24 01:55:33', '2026-07-24 01:55:33'),
(8, 27, 12, 22, 5, 'Đóng gói hơi sơ sài nhưng máy vẫn ổn.', 'visible', '2026-06-10 12:21:21', '2026-06-10 12:21:21'),
(9, 25, 30, 26, 4, 'Sản phẩm tạm ổn, cần kiểm tra kỹ trước khi nhận.', 'visible', '2026-07-31 07:10:27', '2026-07-31 07:10:27'),
(10, 34, 9, 17, 4, 'Giao hàng nhanh, người bán nhiệt tình tư vấn.', 'visible', '2026-08-05 17:25:53', '2026-08-05 17:25:53'),
(11, 20, 19, 10, 5, 'Rất đáng tiền, sẽ giới thiệu cho bạn bè.', 'visible', '2026-07-30 10:53:57', '2026-07-30 10:53:57'),
(12, 13, 5, 11, 5, 'Chất lượng tốt hơn mong đợi, sẽ ủng hộ tiếp.', 'visible', '2026-06-27 09:03:54', '2026-06-27 09:03:54'),
(13, 15, 2, 28, 4, 'Máy còn khá mới, pin trâu, rất hài lòng.', 'visible', '2026-07-19 04:35:10', '2026-07-19 04:35:10'),
(14, 13, 6, 25, 2, 'Rất đáng tiền, sẽ giới thiệu cho bạn bè.', 'visible', '2026-09-13 19:02:18', '2026-09-13 19:02:18'),
(15, 48, 10, 14, 5, 'Chất lượng tốt hơn mong đợi, sẽ ủng hộ tiếp.', 'visible', '2026-06-20 10:10:30', '2026-06-20 10:10:30'),
(16, 45, 17, 23, 5, 'Máy hoạt động ổn định, đúng tình trạng như đăng.', 'visible', '2026-07-07 13:45:02', '2026-07-07 13:45:02'),
(17, 28, 10, 29, 5, 'Tư vấn nhiệt tình, hỗ trợ đổi trả nhanh chóng.', 'visible', '2026-09-11 18:12:21', '2026-09-11 18:12:21'),
(18, 20, 12, 22, 1, 'Máy hoạt động ổn định, đúng tình trạng như đăng.', 'visible', '2026-06-10 12:21:21', '2026-06-10 12:21:21'),
(19, 27, 25, 20, 1, 'Có vài vết xước nhỏ nhưng chấp nhận được so với giá.', 'visible', '2026-08-18 19:39:16', '2026-08-18 19:39:16'),
(20, 29, 14, 19, 5, 'Tư vấn nhiệt tình, hỗ trợ đổi trả nhanh chóng.', 'visible', '2026-08-06 00:50:26', '2026-08-06 00:50:26'),
(21, 33, 10, 14, 4, 'Sản phẩm tạm ổn, cần kiểm tra kỹ trước khi nhận.', 'visible', '2026-06-20 10:10:30', '2026-06-20 10:10:30'),
(22, 11, 7, 3, 2, 'Có vài vết xước nhỏ nhưng chấp nhận được so với giá.', 'visible', '2026-08-06 05:33:58', '2026-08-06 05:33:58'),
(23, 41, 3, 6, 4, 'Giao hàng nhanh, người bán nhiệt tình tư vấn.', 'visible', '2026-07-10 07:35:48', '2026-07-10 07:35:48'),
(24, 35, 15, 21, 5, 'Giao hàng nhanh, người bán nhiệt tình tư vấn.', 'visible', '2026-07-16 20:05:49', '2026-07-16 20:05:49'),
(25, 26, 10, 29, 5, 'Tư vấn nhiệt tình, hỗ trợ đổi trả nhanh chóng.', 'visible', '2026-09-11 18:12:21', '2026-09-11 18:12:21'),
(26, 32, 30, 32, 4, 'Đóng gói hơi sơ sài nhưng máy vẫn ổn.', 'visible', '2026-06-23 00:32:51', '2026-06-23 00:32:51'),
(27, 26, 31, 36, 3, 'Máy hoạt động ổn định, đúng tình trạng như đăng.', 'visible', '2026-07-24 05:23:40', '2026-07-24 05:23:40'),
(28, 24, 1, 24, 4, 'Sản phẩm đúng như mô tả, đóng gói cẩn thận.', 'visible', '2026-07-24 01:55:33', '2026-07-24 01:55:33'),
(29, 43, 9, 27, 1, 'Đóng gói hơi sơ sài nhưng máy vẫn ổn.', 'visible', '2026-08-08 12:43:30', '2026-08-08 12:43:30'),
(30, 10, 5, 8, 5, 'Sản phẩm tạm ổn, cần kiểm tra kỹ trước khi nhận.', 'visible', '2026-08-21 04:04:36', '2026-08-21 04:04:36'),
(31, 16, 29, 16, 5, 'Rất đáng tiền, sẽ giới thiệu cho bạn bè.', 'visible', '2026-09-17 14:31:11', '2026-09-17 14:31:11'),
(32, 43, 31, 34, 5, 'Sản phẩm đúng như mô tả, đóng gói cẩn thận.', 'visible', '2026-09-04 04:33:58', '2026-09-04 04:33:58'),
(33, 22, 30, 32, 5, 'Máy còn khá mới, pin trâu, rất hài lòng.', 'visible', '2026-06-23 00:32:51', '2026-06-23 00:32:51'),
(34, 42, 16, 9, 5, 'Chất lượng tốt hơn mong đợi, sẽ ủng hộ tiếp.', 'visible', '2026-06-23 12:42:31', '2026-06-23 12:42:31'),
(35, 27, 17, 23, 3, 'Sản phẩm tạm ổn, cần kiểm tra kỹ trước khi nhận.', 'visible', '2026-07-07 13:45:02', '2026-07-07 13:45:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `role` enum('user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `avatar`, `date_of_birth`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@gmail.com', '$2y$10$fWNsNkQxZjpZm3//vI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', NULL, NULL, NULL, 'user', 'active', '2026-09-08 08:43:29', '2026-09-16 18:12:42'),
(2, 'TheSecond Seller', 'seller@thesecond.vn', '$2y$10$placeholder', NULL, NULL, NULL, 'user', 'active', '2026-09-08 09:04:21', '2026-09-16 18:12:49'),
(3, 'adsafs', 'admin@gmail.com', '$2y$10$bkVSuqeoHCPKkz/5lQ415.7NdviQot.Yo6COQMJA/YpCRNDgX1/aa', NULL, NULL, NULL, 'user', 'active', '2026-09-13 09:34:33', '2026-09-16 18:12:59'),
(4, 'Hoàng Thị Trung', 'hoang.thi.trung4@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0981960013', NULL, NULL, 'user', 'active', '2026-06-06 11:39:59', '2026-06-10 11:39:59'),
(5, 'Phan Đức Ngân', 'phan.duc.ngan5@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0937940265', NULL, NULL, 'user', 'active', '2026-07-13 03:26:05', '2026-07-15 03:26:05'),
(6, 'Phạm Thị Nam', 'pham.thi.nam6@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0915594078', NULL, NULL, 'user', 'active', '2026-08-13 20:05:53', '2026-08-13 20:05:53'),
(7, 'Trịnh Ngọc Hải', 'trinh.ngoc.hai7@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0995931034', NULL, NULL, 'user', 'blocked', '2026-07-16 12:53:02', '2026-07-16 12:53:02'),
(8, 'Hồ Ngọc Oanh', 'ho.ngoc.oanh8@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0952553419', NULL, NULL, 'user', 'inactive', '2026-09-13 01:16:20', '2026-09-18 01:16:20'),
(9, 'Vũ Hữu Oanh', 'vu.huu.oanh9@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0964835030', NULL, NULL, 'user', 'blocked', '2026-08-18 05:33:48', '2026-08-20 05:33:48'),
(10, 'Lê Minh Phúc', 'le.minh.phuc10@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0995376724', NULL, NULL, 'user', 'active', '2026-09-13 23:48:58', '2026-09-15 23:48:58'),
(11, 'Đoàn Quốc Lan', 'doan.quoc.lan11@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0996532871', NULL, NULL, 'user', 'inactive', '2026-06-22 15:00:07', '2026-06-23 15:00:07'),
(12, 'Huỳnh Quốc Thắng', 'huynh.quoc.thang12@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0916697848', NULL, NULL, 'user', 'blocked', '2026-06-03 13:30:19', '2026-06-08 13:30:19'),
(13, 'Phạm Đức Vy', 'pham.duc.vy13@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0945146270', NULL, NULL, 'user', 'blocked', '2026-07-22 11:28:54', '2026-07-26 11:28:54'),
(14, 'Huỳnh Đức Phúc', 'huynh.duc.phuc14@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0914893252', NULL, NULL, 'user', 'active', '2026-09-12 07:37:38', '2026-09-12 07:37:38'),
(15, 'Mai Thành Phong', 'mai.thanh.phong15@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0901543039', NULL, NULL, 'user', 'blocked', '2026-06-17 23:10:26', '2026-06-22 23:10:26'),
(16, 'Lý Thị Vy', 'ly.thi.vy16@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0982278248', NULL, NULL, 'user', 'blocked', '2026-08-22 11:57:56', '2026-08-23 11:57:56'),
(17, 'Trịnh Minh Tuấn', 'trinh.minh.tuan17@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0946578713', NULL, NULL, 'user', 'active', '2026-08-05 23:35:34', '2026-08-05 23:35:34'),
(18, 'Đoàn Đức Hùng', 'doan.duc.hung18@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0993010310', NULL, NULL, 'user', 'blocked', '2026-06-15 02:13:09', '2026-06-19 02:13:09'),
(19, 'Vũ Ngọc Trung', 'vu.ngoc.trung19@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0973829973', NULL, NULL, 'user', 'inactive', '2026-08-19 09:02:12', '2026-08-20 09:02:12'),
(20, 'Phạm Thị Trung', 'pham.thi.trung20@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0965667010', NULL, NULL, 'user', 'active', '2026-08-06 05:15:53', '2026-08-06 05:15:53'),
(21, 'Vũ Minh Hà', 'vu.minh.ha21@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0987262473', NULL, NULL, 'user', 'blocked', '2026-06-15 23:19:08', '2026-06-18 23:19:08'),
(22, 'Trịnh Thị Bình', 'trinh.thi.binh22@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0980132677', NULL, NULL, 'user', 'active', '2026-08-18 04:58:59', '2026-08-18 04:58:59'),
(23, 'Huỳnh Quốc An', 'huynh.quoc.an23@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0964746872', NULL, NULL, 'user', 'active', '2026-07-13 14:33:34', '2026-07-13 14:33:34'),
(24, 'Đoàn Đức Bình', 'doan.duc.binh24@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0950097882', NULL, NULL, 'user', 'active', '2026-09-07 22:39:08', '2026-09-07 22:39:08'),
(25, 'Huỳnh Thị Thắng', 'huynh.thi.thang25@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0913619399', NULL, NULL, 'user', 'active', '2026-06-17 06:04:13', '2026-06-20 06:04:13'),
(26, 'Đoàn Xuân Quang', 'doan.xuan.quang26@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0954353462', NULL, NULL, 'user', 'inactive', '2026-07-29 14:05:15', '2026-08-01 14:05:15'),
(27, 'Bùi Thị An', 'bui.thi.an27@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0979911838', NULL, NULL, 'user', 'active', '2026-08-08 02:29:56', '2026-08-08 02:29:56'),
(28, 'Vũ Thành Linh', 'vu.thanh.linh28@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0927849808', NULL, NULL, 'user', 'active', '2026-06-21 10:44:53', '2026-06-22 10:44:53'),
(29, 'Võ Thị Lan', 'vo.thi.lan29@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0918244935', NULL, NULL, 'user', 'active', '2026-07-22 14:15:14', '2026-07-26 14:15:14'),
(30, 'Lý Ngọc Lan', 'ly.ngoc.lan30@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0901640052', NULL, NULL, 'user', 'inactive', '2026-07-22 04:48:00', '2026-07-23 04:48:00'),
(31, 'Dương Đức Tuấn', 'duong.duc.tuan31@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0968011280', NULL, NULL, 'user', 'blocked', '2026-09-16 14:57:12', '2026-09-17 14:57:12'),
(32, 'Ngô Hữu Bình', 'ngo.huu.binh32@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0945053315', NULL, NULL, 'user', 'inactive', '2026-08-19 05:51:21', '2026-08-23 05:51:21'),
(33, 'Hoàng Minh Kiên', 'hoang.minh.kien33@gmail.com', '$2y$10$fWNsNkQxZjpZm3rIVI6chODQQdDsEV1LfG3EXjqjaPeXsJye4Xbo.', '0922602563', NULL, NULL, 'user', 'active', '2026-06-22 07:45:43', '2026-06-25 07:45:43'),
(34, 'bé tí teo', 'zzz@gmail.com', '$2y$10$5mD2QMrLvBsEYQBsuggZUeotyDttMlJ0Pk3rDWH0P3B/0K.tYwJMC', '', NULL, NULL, 'user', 'active', '2026-09-18 15:10:14', '2026-09-18 21:22:28');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 31, 31, '2026-09-01 19:04:41'),
(2, 7, 37, '2026-08-28 04:33:24'),
(3, 21, 13, '2026-07-29 15:19:55'),
(4, 3, 16, '2026-06-05 17:15:15'),
(6, 11, 24, '2026-09-09 12:42:21'),
(7, 12, 44, '2026-07-02 10:17:11'),
(8, 22, 44, '2026-08-23 03:01:31'),
(9, 30, 23, '2026-08-18 23:39:28'),
(10, 12, 20, '2026-08-24 05:53:33'),
(11, 26, 10, '2026-07-09 15:35:54'),
(12, 29, 46, '2026-08-23 17:51:40'),
(13, 25, 9, '2026-07-13 01:01:20'),
(14, 14, 26, '2026-06-13 11:34:05'),
(15, 7, 43, '2026-07-07 15:17:45'),
(16, 24, 29, '2026-07-09 14:25:57'),
(17, 30, 16, '2026-07-22 06:39:19'),
(18, 32, 42, '2026-08-01 04:34:08'),
(19, 25, 48, '2026-08-16 13:23:15'),
(20, 8, 31, '2026-08-08 16:22:07'),
(21, 30, 48, '2026-07-04 20:02:11'),
(22, 20, 48, '2026-06-17 18:58:22'),
(23, 9, 29, '2026-06-24 05:16:30'),
(24, 16, 28, '2026-06-24 01:01:17'),
(25, 12, 32, '2026-06-28 18:46:42'),
(26, 33, 33, '2026-08-21 13:08:07'),
(27, 9, 45, '2026-08-14 20:13:04'),
(28, 28, 20, '2026-09-03 20:02:51'),
(29, 11, 40, '2026-07-27 10:04:03'),
(30, 9, 20, '2026-08-01 10:02:56'),
(31, 29, 48, '2026-06-11 17:50:47'),
(32, 23, 9, '2026-09-03 10:26:28'),
(33, 9, 21, '2026-08-14 21:18:34'),
(35, 27, 40, '2026-08-21 00:58:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addresses_user` (`user_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_product` (`cart_id`,`product_id`),
  ADD KEY `idx_cart_items_cart` (`cart_id`),
  ADD KEY `idx_cart_items_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_payment_status` (`payment_status`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `idx_payments_order` (`order_id`),
  ADD KEY `idx_payments_status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_seller` (`seller_id`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_price` (`price`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_product` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_order` (`order_id`),
  ADD KEY `idx_reviews_product` (`product_id`),
  ADD KEY `idx_reviews_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist_product` (`user_id`,`product_id`),
  ADD KEY `fk_wishlists_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `fk_wishlists_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wishlists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
