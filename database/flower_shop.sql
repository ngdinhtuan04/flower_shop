-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2025 at 10:53 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flower_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `position` enum('home_slider','home_banner','sidebar') DEFAULT 'home_slider',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(33, 7, NULL, 4, 1, 500000.00, '2025-12-10 14:17:25', '2025-12-10 14:17:25'),
(34, 7, NULL, 2, 1, 450000.00, '2025-12-10 14:17:26', '2025-12-10 14:17:26');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `parent_id`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Hoa Sinh Nhật', 'hoa-sinh-nhat', 'Các loại hoa dành tặng sinh nhật', NULL, NULL, 1, 'active', '2025-11-06 20:05:28', '2025-11-06 20:05:28'),
(2, 'Hoa Khai Trương', 'hoa-khai-truong', 'Hoa chúc mừng khai trương', NULL, NULL, 2, 'active', '2025-11-06 20:05:28', '2025-11-06 20:05:28'),
(3, 'Hoa Cưới', 'hoa-cuoi', 'Hoa trang trí đám cưới', NULL, NULL, 3, 'active', '2025-11-06 20:05:28', '2025-11-06 20:05:28'),
(5, 'Hoa Tình Yêu', 'hoa-tinh-yeu', 'Hoa hồng, hoa tình yêu', NULL, NULL, 5, 'active', '2025-11-06 20:05:28', '2025-11-06 20:05:28'),
(6, 'Hoa Chúc Mừng', 'hoa-chuc-mung', 'Hoa chúc mừng các dịp lễ', NULL, NULL, 6, 'active', '2025-11-06 20:05:28', '2025-11-06 20:05:28');

-- --------------------------------------------------------

--
-- Table structure for table `contract_products`
--

CREATE TABLE `contract_products` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL COMMENT 'ID hợp đồng',
  `product_id` int(11) NOT NULL COMMENT 'ID sản phẩm của shop',
  `committed_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lượng cam kết cung cấp',
  `delivered_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lượng đã giao thực tế',
  `allow_over_import` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cho phép nhập vượt số lượng cam kết (0=Không, 1=Có)',
  `import_price` decimal(12,2) NOT NULL COMMENT 'Giá nhập theo hợp đồng',
  `unit` varchar(50) DEFAULT 'cái' COMMENT 'Đơn vị tính',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contract_products`
--

INSERT INTO `contract_products` (`id`, `contract_id`, `product_id`, `committed_quantity`, `delivered_quantity`, `allow_over_import`, `import_price`, `unit`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 1000, 0, 0, 100000.00, 'cái', 'abc', '2025-12-10 20:11:30', '2025-12-10 20:11:30'),
(2, 4, 10, 200, 200, 0, 50000.00, 'cái', NULL, '2025-12-10 21:58:15', '2025-12-10 21:58:41'),
(3, 4, 9, 250, 250, 0, 50000.00, 'cái', NULL, '2025-12-10 22:00:00', '2025-12-11 07:49:35'),
(4, 5, 10, 200, 200, 0, 2000.00, 'cái', NULL, '2025-12-11 07:17:06', '2025-12-11 08:25:35'),
(5, 5, 9, 200, 200, 0, 20000.00, 'cái', NULL, '2025-12-11 07:32:22', '2025-12-11 08:25:22'),
(6, 5, 2, 1120, 1120, 1, 5000.00, 'cái', NULL, '2025-12-11 08:26:03', '2025-12-11 08:26:19');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `discount_type` enum('percent','fixed') DEFAULT 'percent',
  `apply_to` enum('product','shipping','all') NOT NULL DEFAULT 'product' COMMENT 'Áp dụng cho: product=Sản phẩm, shipping=Phí vận chuyển, all=Cả hai',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `apply_to`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `valid_from`, `valid_to`, `status`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME2025', 'Giảm 10% cho đơn hàng đầu tiên', 'percent', 'product', 10.00, 200000.00, NULL, 100, 9, '2025-11-07 03:05:00', '2025-12-10 03:05:00', 'active', '2025-11-06 20:05:29', '2025-12-07 20:33:40'),
(2, 'FREESHIP', 'Miễn phí ship cho đơn từ 500K', 'fixed', 'shipping', 30000.00, 500000.00, NULL, 500, 401, '2025-11-07 03:05:00', '2026-01-06 03:05:00', 'active', '2025-11-06 20:05:29', '2025-12-07 20:32:44');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `daily_order_stats`
-- (See below for the actual view)
--
CREATE TABLE `daily_order_stats` (
`order_date` date
,`total_orders` bigint(21)
,`total_revenue` decimal(32,2)
,`delivered_orders` bigint(21)
,`cancelled_orders` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('complaint','suggestion','question','product_inquiry','other') DEFAULT 'other',
  `status` enum('new','processing','resolved','closed') DEFAULT 'new',
  `admin_reply` text DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `product_id`, `name`, `email`, `phone`, `subject`, `message`, `type`, `status`, `admin_reply`, `replied_by`, `replied_at`, `created_at`, `updated_at`) VALUES
(1, 7, NULL, 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'gfhgjk', '.....', 'suggestion', 'resolved', 'abc', 16, '2025-11-13 18:47:37', '2025-11-13 11:22:30', '2025-11-13 11:47:37'),
(2, 7, 6, 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '', 'có giao toàn quốc không ?', 'cafaf', 'product_inquiry', 'resolved', 'ạksdj', 16, '2025-12-07 15:36:36', '2025-12-07 08:28:09', '2025-12-07 08:46:57'),
(3, 7, 6, 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '', 'gfhgjksdasd', 'asadd', 'product_inquiry', 'resolved', 'ạdasd', 16, '2025-12-07 15:47:37', '2025-12-07 08:47:21', '2025-12-07 08:47:37'),
(4, 7, 6, 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '', 'có giao toàn quốc không ?', 'áddsasda', 'product_inquiry', 'resolved', 'ádasdsad', 16, '2025-12-07 15:53:50', '2025-12-07 08:53:32', '2025-12-07 08:53:50'),
(5, 7, 4, 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '', 'gfhgjk', 'áddsad', 'product_inquiry', 'resolved', 'âsđsf', 16, '2025-12-07 15:59:04', '2025-12-07 08:58:49', '2025-12-07 08:59:04'),
(6, 7, 3, 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '', 'gfhgjk', 'adsasd', 'product_inquiry', 'resolved', 'adasd', 16, '2025-12-07 16:01:38', '2025-12-07 09:01:24', '2025-12-07 09:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `image`, `author_id`, `category`, `views`, `status`, `published_at`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 'abcds', 'abcds', 'áhdk', 'ádhkjhads', 'uploads/news/1762621878_Silis-Flower-10-920x518.jpg', 16, 'events', 0, 'draft', NULL, 'ạksdh', 'kashdkhsak', '2025-11-08 17:11:18', '2025-11-08 17:11:18'),
(2, 'Hoa Valentine 2025 - Đặt hoa tươi đẹp tại Hà Nội', 'hoa-valentine-2025-dat-hoa-tuoi-dep-tai-ha-noi', 'Khám phá bộ sưu tập hoa Valentine đẹp nhất', '<!-- Template tin tức mẫu - Copy vào CKEditor (chế độ Source) -->\r\n<h2>Gợi qu&agrave; tặng c&ocirc; thể hiện ch&acirc;n th&agrave;nh</h2>\r\n\r\n<p>Hoa hồng đỏ lu&ocirc;n l&agrave; biểu tượng của t&igrave;nh y&ecirc;u nồng n&agrave;n v&agrave; ch&acirc;n th&agrave;nh. Mỗi b&ocirc;ng hoa mang trong m&igrave;nh một th&ocirc;ng điệp ri&ecirc;ng biệt, thể hiện t&igrave;nh cảm s&acirc;u sắc m&agrave; lời n&oacute;i kh&ocirc;ng thể diễn tả được. Đặc biệt v&agrave;o dịp Valentine, việc tặng hoa kh&ocirc;ng chỉ l&agrave; m&oacute;n qu&agrave;, m&agrave; c&ograve;n l&agrave; c&aacute;ch để bạn b&agrave;y tỏ t&igrave;nh y&ecirc;u của m&igrave;nh một c&aacute;ch tinh tế v&agrave; l&atilde;ng mạn nhất.</p>\r\n\r\n<p>Ch&uacute;ng t&ocirc;i hiểu rằng mỗi kh&aacute;ch h&agrave;ng đều c&oacute; những mong muốn v&agrave; sở th&iacute;ch ri&ecirc;ng, v&igrave; vậy ch&uacute;ng t&ocirc;i lu&ocirc;n tư vấn v&agrave; thiết kế những mẫu hoa ph&ugrave; hợp nhất. Từ những b&oacute; hoa hồng đỏ cổ điển đến những mẫu hoa tulip sang trọng, tất cả đều được chuẩn bị tỉ mỉ v&agrave; tươi mới nhất.</p>\r\n\r\n<h3>Quy tr&igrave;nh của ch&uacute;ng t&ocirc;i - Từ lựa chọn đến giao h&agrave;ng</h3>\r\n\r\n<p>Tại shop hoa của ch&uacute;ng t&ocirc;i, quy tr&igrave;nh từ kh&acirc;u chọn hoa đến khi giao đến tay kh&aacute;ch h&agrave;ng đều được thực hiện một c&aacute;ch chuy&ecirc;n nghiệp v&agrave; chu đ&aacute;o nhất. Ch&uacute;ng t&ocirc;i cam kết mang đến cho bạn những b&ocirc;ng hoa tươi nhất, được nhập khẩu trực tiếp từ c&aacute;c v&ugrave;ng trồng hoa nổi tiếng như Đ&agrave; Lạt, H&agrave; Lan v&agrave; Ecuador.</p>\r\n\r\n<p><img src=\"https://heterotrichous-groundable-elliott.ngrok-free.dev/flower_shop/public/uploads/news/news_1765445166_693a8e2e6d699.png\" style=\"height:267px; width:396px\" /></p>\r\n\r\n<h3>Đội h&agrave;ng đi dọc qua trang web</h3>\r\n\r\n<p>Website của ch&uacute;ng t&ocirc;i được thiết kế th&acirc;n thiện v&agrave; dễ sử dụng, gi&uacute;p bạn c&oacute; thể t&igrave;m kiếm v&agrave; đặt hoa một c&aacute;ch nhanh ch&oacute;ng chỉ trong v&agrave;i ph&uacute;t. Với hệ thống thanh to&aacute;n trực tuyến an to&agrave;n, bạn ho&agrave;n to&agrave;n y&ecirc;n t&acirc;m khi mua sắm tại đ&acirc;y.</p>\r\n\r\n<p>Ngo&agrave;i ra, ch&uacute;ng t&ocirc;i c&ograve;n cung cấp dịch vụ giao hoa nhanh trong v&ograve;ng 2 giờ tại nội th&agrave;nh H&agrave; Nội. Đặc biệt, với những đơn h&agrave;ng từ 1.000.000đ trở l&ecirc;n, bạn sẽ được miễn ph&iacute; giao h&agrave;ng v&agrave; nhận th&ecirc;m thiệp ch&uacute;c mừng được thiết kế theo y&ecirc;u cầu.</p>\r\n\r\n<h3>Dược dẫn xếp cẩn thận khi c&aacute;c chuy&ecirc;n gia</h3>\r\n\r\n<p>Mỗi b&oacute; hoa được cắm v&agrave; sắp xếp bởi những florist gi&agrave;u kinh nghiệm của ch&uacute;ng t&ocirc;i. Họ kh&ocirc;ng chỉ c&oacute; kỹ năng cắm hoa đi&ecirc;u luyện m&agrave; c&ograve;n c&oacute; khả năng phối m&agrave;u v&agrave; tạo h&igrave;nh nghệ thuật, mang đến những t&aacute;c phẩm hoa độc đ&aacute;o v&agrave; ấn tượng.</p>\r\n\r\n<ul>\r\n	<li><strong>Chọn hoa tươi mỗi ng&agrave;y:</strong> Hoa được nhập về mỗi s&aacute;ng từ c&aacute;c vườn hoa uy t&iacute;n</li>\r\n	<li><strong>Cắm hoa thủ c&ocirc;ng:</strong> Mỗi b&oacute; hoa đều được cắm bởi tay nghề cao</li>\r\n	<li><strong>Đ&oacute;ng g&oacute;i cẩn thận:</strong> Sử dụng giấy g&oacute;i cao cấp v&agrave; ruy băng sang trọng</li>\r\n	<li><strong>Giao h&agrave;ng nhanh ch&oacute;ng:</strong> Cam kết giao hoa đ&uacute;ng giờ theo y&ecirc;u cầu</li>\r\n</ul>\r\n\r\n<h3>C&aacute;c tốt dịch vụ giao h&agrave;ng trọng ng&agrave;y v&agrave; thao lịch tr&igrave;nh</h3>\r\n\r\n<p>Một trong những điểm mạnh của ch&uacute;ng t&ocirc;i ch&iacute;nh l&agrave; dịch vụ giao h&agrave;ng linh hoạt v&agrave; đa dạng. Bạn c&oacute; thể chọn giao hoa ngay lập tức, hoặc hẹn giờ giao v&agrave;o thời điểm mong muốn. Đặc biệt, với dịch vụ giao hoa bất ngờ, ch&uacute;ng t&ocirc;i sẽ gi&uacute;p bạn tạo n&ecirc;n những khoảnh khắc đ&aacute;ng nhớ cho người th&acirc;n y&ecirc;u.</p>\r\n\r\n<p>Ch&uacute;ng t&ocirc;i cũng hỗ trợ giao hoa tận nơi tr&ecirc;n to&agrave;n quốc th&ocirc;ng qua mạng lưới đối t&aacute;c rộng khắp. D&ugrave; bạn ở H&agrave; Nội, TP.HCM, Đ&agrave; Nẵng hay bất kỳ tỉnh th&agrave;nh n&agrave;o, ch&uacute;ng t&ocirc;i đều c&oacute; thể giao hoa đến tận tay người nhận một c&aacute;ch nhanh ch&oacute;ng v&agrave; an to&agrave;n.</p>\r\n\r\n<h3>Những c&acirc;u hỏi thường gặp về dịch vụ giao hoa ngay lễ t&igrave;nh nh&acirc;n tại Việt Nam</h3>\r\n\r\n<p>Nhiều kh&aacute;ch h&agrave;ng thường thắc mắc về thời gian giao hoa, c&aacute;ch đặt h&agrave;ng online, hay c&aacute;c ch&iacute;nh s&aacute;ch đổi trả. Ch&uacute;ng t&ocirc;i lu&ocirc;n sẵn s&agrave;ng giải đ&aacute;p mọi thắc mắc của bạn qua hotline <strong>1900 xxxx</strong> hoặc chat trực tuyến tr&ecirc;n website.</p>\r\n\r\n<p><img alt=\"Hoa Valentine đẹp\" src=\"https://silisflower.vn/uploads/images/anh%20-%20news/bo%20hoa%20-%20silisflower%20(2).jpg\" style=\"height:400px; width:600px\" /></p>\r\n\r\n<p>Một số c&acirc;u hỏi thường gặp:</p>\r\n\r\n<ol>\r\n	<li><strong>Hoa c&oacute; tươi kh&ocirc;ng?</strong> - Hoa được nhập mới mỗi ng&agrave;y, đảm bảo độ tươi tốt nhất</li>\r\n	<li><strong>Giao h&agrave;ng mất bao l&acirc;u?</strong> - Nội th&agrave;nh H&agrave; Nội: 1-2 giờ, ngoại th&agrave;nh: 2-4 giờ</li>\r\n	<li><strong>C&oacute; giao hoa trong đ&ecirc;m kh&ocirc;ng?</strong> - C&oacute;, ch&uacute;ng t&ocirc;i hỗ trợ giao hoa 24/7</li>\r\n	<li><strong>Thanh to&aacute;n như thế n&agrave;o?</strong> - Hỗ trợ COD, chuyển khoản, v&iacute; điện tử</li>\r\n</ol>\r\n\r\n<h3>Tư vấn đặt h&agrave;ng khi n&agrave;o?</h3>\r\n\r\n<p>Để đảm bảo c&oacute; được mẫu hoa ưng &yacute; v&agrave; thời gian giao h&agrave;ng ph&ugrave; hợp, ch&uacute;ng t&ocirc;i khuy&ecirc;n bạn n&ecirc;n đặt hoa trước &iacute;t nhất 4-6 giờ. Đặc biệt v&agrave;o c&aacute;c dịp lễ như Valentine, 8/3, 20/10, bạn n&ecirc;n đặt trước 1-2 ng&agrave;y để tr&aacute;nh t&igrave;nh trạng hết h&agrave;ng hoặc giao muộn.</p>\r\n\r\n<p>Tuy nhi&ecirc;n, nếu bạn cần gấp, ch&uacute;ng t&ocirc;i vẫn c&oacute; dịch vụ giao hoa trong 1 giờ với số lượng c&oacute; sẵn tại shop. H&atilde;y li&ecirc;n hệ ngay để được tư vấn chi tiết nhất!</p>\r\n\r\n<h3>Khuyến m&atilde;i đặc biệt dịp Valentine 2025</h3>\r\n\r\n<p>Nh&acirc;n dịp Valentine 14/2/2025, ch&uacute;ng t&ocirc;i c&oacute; chương tr&igrave;nh khuyến m&atilde;i hấp dẫn:</p>\r\n\r\n<ul>\r\n	<li>🎁 <strong>Giảm 15%</strong> cho đơn h&agrave;ng đầu ti&ecirc;n</li>\r\n	<li>🎁 <strong>Tặng thiệp + socola</strong> cho đơn từ 800.000đ</li>\r\n	<li>🎁 <strong>Miễn ph&iacute; giao h&agrave;ng</strong> cho đơn từ 1.000.000đ</li>\r\n	<li>🎁 <strong>Tặng gấu b&ocirc;ng</strong> cho đơn từ 2.000.000đ</li>\r\n</ul>\r\n\r\n<p><strong>Đặt hoa ngay h&ocirc;m nay để kh&ocirc;ng bỏ lỡ những ưu đ&atilde;i hấp dẫn n&agrave;y!</strong></p>\r\n\r\n<p><a href=\"https://heterotrichous-groundable-elliott.ngrok-free.dev/flower_shop/public/products\"><strong>ĐẶT HOA NGAY</strong></a></p>\r\n\r\n<hr />\r\n<p><em>Li&ecirc;n hệ: Hotline <strong>1900 xxxx</strong> | Email: <strong>contact@flowershop.vn</strong> | Website: <strong>www.flowershop.vn</strong></em></p>\r\n', 'uploads/news/1762623476_Silis-Flower-10-920x518.jpg', 16, 'tips', 50, 'published', '2025-11-09 00:37:56', 'ádbmabd', 'jkasdjsdj', '2025-11-08 17:37:56', '2025-12-11 09:28:41');

-- --------------------------------------------------------

--
-- Table structure for table `news_comments`
--

CREATE TABLE `news_comments` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL COMMENT 'ID tin tức',
  `user_id` int(11) NOT NULL COMMENT 'ID người dùng',
  `parent_id` int(11) DEFAULT NULL COMMENT 'ID bình luận cha (cho reply)',
  `content` text NOT NULL COMMENT 'Nội dung bình luận',
  `status` enum('visible','hidden','deleted') NOT NULL DEFAULT 'visible' COMMENT 'Trạng thái hiển thị',
  `is_spam` tinyint(1) DEFAULT 0 COMMENT 'Đánh dấu spam',
  `admin_reason` text DEFAULT NULL COMMENT 'Lý do ẩn/xóa từ admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bình luận tin tức';

--
-- Dumping data for table `news_comments`
--

INSERT INTO `news_comments` (`id`, `news_id`, `user_id`, `parent_id`, `content`, `status`, `is_spam`, `admin_reason`, `created_at`, `updated_at`) VALUES
(7, 2, 7, NULL, 'tuyệt', 'visible', 0, NULL, '2025-12-08 11:39:45', '2025-12-08 12:25:13'),
(8, 2, 7, NULL, 'tuyệt', 'deleted', 0, 'không phù hợp', '2025-12-08 11:44:42', '2025-12-08 12:26:16');

-- --------------------------------------------------------

--
-- Table structure for table `news_likes`
--

CREATE TABLE `news_likes` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL COMMENT 'ID tin tức',
  `user_id` int(11) NOT NULL COMMENT 'ID người dùng',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yêu thích tin tức';

--
-- Dumping data for table `news_likes`
--

INSERT INTO `news_likes` (`id`, `news_id`, `user_id`, `created_at`) VALUES
(2, 2, 7, '2025-12-08 11:57:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('review_approved','review_rejected','order_status','promotion','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(6, 7, 'review_rejected', 'Đánh giá của bạn đã bị từ chối', 'Lý do: abc', NULL, 1, '2025-12-06 16:46:25'),
(7, 7, 'review_approved', 'Đánh giá của bạn đã được duyệt', 'Đánh giá của bạn cho sản phẩm đã được phê duyệt và hiển thị công khai.', '/product/detail/6', 1, '2025-12-06 16:47:50'),
(8, 7, '', 'Admin đã phản hồi góp ý của bạn', 'Góp ý \"có giao toàn quốc không ?\" đã nhận được phản hồi từ admin.', '/feedback/my-feedback', 1, '2025-12-07 08:53:50'),
(9, 7, '', 'Admin đã phản hồi góp ý của bạn', 'Góp ý \"gfhgjk\" đã nhận được phản hồi từ admin.', '/feedback/my-feedback', 1, '2025-12-07 08:59:04'),
(10, 7, '', 'Admin đã phản hồi góp ý của bạn', 'Góp ý \"gfhgjk\" đã nhận được phản hồi từ admin.', '/feedback/my-feedback', 1, '2025-12-07 09:01:38'),
(11, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512072350347847', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/38', 1, '2025-12-07 16:50:34'),
(12, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512072359374294', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/39', 1, '2025-12-07 16:59:37'),
(13, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512080000571154', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/40', 1, '2025-12-07 17:00:57'),
(14, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512080218465629', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/41', 1, '2025-12-07 19:18:46'),
(15, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512080318435473', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/42', 1, '2025-12-07 20:18:43'),
(16, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512080329438877', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/43', 1, '2025-12-07 20:29:43'),
(17, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512080332443392', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/44', 1, '2025-12-07 20:32:44'),
(18, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512080333404937', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/45', 1, '2025-12-07 20:33:40'),
(19, 7, '', 'Thông báo về bình luận', 'Bình luận của bạn đã bị xóa. Lý do: không phù hợp', 'https://heterotrichous-groundable-elliott.ngrok-free.dev/flower_shop/public/users/profile', 1, '2025-12-08 12:26:16'),
(20, 7, 'order_status', 'Cập nhật đơn hàng #45', '📦 Đơn hàng đã giao thành công', '/orders/detail/45', 1, '2025-12-08 16:00:18'),
(21, 7, 'review_approved', 'Đánh giá của bạn đã được duyệt', 'Đánh giá của bạn cho sản phẩm đã được phê duyệt và hiển thị công khai.', '/product/detail/6', 1, '2025-12-08 16:02:22'),
(22, 7, 'order_status', '🎉 Đặt hàng thành công #ORD202512092306006946', 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.', '/orders/detail/46', 0, '2025-12-09 16:06:00'),
(23, 7, 'order_status', 'Cập nhật đơn hàng #46', '✅ Đơn hàng đã được xác nhận', '/orders/detail/46', 0, '2025-12-09 16:07:34'),
(24, 7, 'order_status', 'Cập nhật đơn hàng #46', '📦 Đơn hàng đã giao thành công', '/orders/detail/46', 0, '2025-12-09 16:07:52');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_code` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_note` text DEFAULT NULL,
  `shipping_method` enum('standard','express','same_day','pickup') NOT NULL DEFAULT 'standard' COMMENT 'Hình thức giao hàng: standard=Tiêu chuẩn, express=Nhanh, same_day=Trong ngày, pickup=Nhận tại cửa hàng',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `product_discount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Số tiền giảm giá cho sản phẩm',
  `shipping_discount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Số tiền giảm giá cho phí vận chuyển',
  `discount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('cod','vnpay','bank_transfer') DEFAULT 'cod',
  `coupon_code` varchar(50) DEFAULT NULL COMMENT 'Mã giảm giá đã sử dụng',
  `coupon_discount` decimal(10,2) DEFAULT 0.00 COMMENT 'Số tiền giảm từ coupon',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_info` text DEFAULT NULL,
  `order_status` enum('pending','confirmed','processing','shipping','delivered','cancelled') DEFAULT 'pending',
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `shipping_note`, `shipping_method`, `subtotal`, `shipping_fee`, `product_discount`, `shipping_discount`, `discount`, `total`, `payment_method`, `coupon_code`, `coupon_discount`, `payment_status`, `payment_info`, `order_status`, `cancel_reason`, `cancelled_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(9, 7, 'ORD202511131932134009', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', 'abc', 'standard', 1010000.00, 30000.00, 30000.00, 0.00, 30000.00, 1010000.00, 'vnpay', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 12:32:13', '2025-12-05 10:57:09'),
(10, 7, 'ORD202511131941597523', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', 'abc', 'standard', 1010000.00, 30000.00, 30000.00, 0.00, 30000.00, 1010000.00, 'vnpay', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 12:41:59', '2025-12-05 10:57:09'),
(11, 7, 'ORD202511131949189107', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'Ha Noi', 'abc', 'standard', 1010000.00, 30000.00, 0.00, 0.00, 0.00, 1040000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 12:49:18', '2025-11-13 12:49:18'),
(12, 7, 'ORD202511131950102958', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'abc', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'cod', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 12:50:10', '2025-11-13 12:50:10'),
(13, 7, 'ORD202511131950544900', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654322', 'ád', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 12:50:54', '2025-11-13 12:50:54'),
(14, 7, 'ORD202511132012209082', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'abc', '', 'standard', 1010000.00, 30000.00, 30000.00, 0.00, 30000.00, 1010000.00, 'cod', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 13:12:20', '2025-12-05 10:57:09'),
(15, 7, 'ORD202511132012408479', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'ahasd', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 13:12:40', '2025-11-13 13:12:40'),
(16, 7, 'ORD202511132105522002', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'ạdhdas', '', 'standard', 560000.00, 30000.00, 30000.00, 0.00, 30000.00, 560000.00, 'vnpay', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 14:05:52', '2025-12-05 10:57:09'),
(17, 7, 'ORD202511132108475086', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'aksdjks', '', 'standard', 560000.00, 30000.00, 30000.00, 0.00, 30000.00, 560000.00, 'vnpay', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 14:08:47', '2025-12-05 10:57:09'),
(18, 7, 'ORD202511132147009862', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'ákdhk', 'nạd', 'standard', 560000.00, 30000.00, 30000.00, 0.00, 30000.00, 560000.00, 'vnpay', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 14:47:00', '2025-12-05 10:57:09'),
(19, 7, 'ORD202511132214339717', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'abc', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 15:14:33', '2025-11-13 15:14:33'),
(20, 7, 'ORD202511132253426308', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'ÁDN', '', 'standard', 560000.00, 30000.00, 30000.00, 0.00, 30000.00, 560000.00, 'vnpay', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 15:53:42', '2025-12-05 10:57:09'),
(21, 7, 'ORD202511132300579626', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'jlkjl', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 16:00:57', '2025-11-13 16:00:57'),
(22, 7, 'ORD202511132313311260', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'llk', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'cod', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-11-13 16:13:31', '2025-11-13 16:13:31'),
(23, 7, 'ORD202511132314027904', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'sadad', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'confirmed', NULL, NULL, NULL, '2025-11-13 16:14:02', '2025-11-19 20:40:47'),
(24, 7, 'ORD202511140022369586', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'znsfd', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'processing', NULL, NULL, NULL, '2025-11-13 17:22:36', '2025-11-19 20:50:10'),
(25, 7, 'ORD202511140803533429', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'fgjg', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'paid', NULL, 'delivered', NULL, NULL, NULL, '2025-01-14 01:03:53', '2025-11-16 14:22:34'),
(26, 7, 'ORD202511140929568174', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'rgdhf', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'paid', '{\"vnp_Amount\":\"59000000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15259687\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202511140929568174\",\"vnp_PayDate\":\"20251113193102\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"SODTJRAU\",\"vnp_TransactionNo\":\"15259687\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202511140929568174\",\"vnp_SecureHash\":\"ba5044b844dc25d1cd365eeb5b64239b6703fd03cb32a4ae8021bbe02377496ab32c89e7f8741deb1283f2ae75e4b2497a5c61638b3183fb8c2517e06c997c8f\"}', 'delivered', NULL, NULL, NULL, '2025-10-11 02:29:56', '2025-11-16 14:03:01'),
(27, 7, 'ORD202511141126001481', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'ksdha', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'cod', '', 0.00, 'paid', 'Auto-confirmed on delivery', 'delivered', NULL, NULL, NULL, '2025-11-14 04:26:00', '2025-11-14 04:33:18'),
(28, 7, 'ORD202511200048098879', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0988888876', 'adsasd', 'asdasd', 'standard', 900000.00, 30000.00, 30000.00, 0.00, 30000.00, 900000.00, 'vnpay', 'FREESHIP', 30000.00, 'paid', '{\"vnp_Amount\":\"90000000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15271395\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202511200048098879\",\"vnp_PayDate\":\"20251120004857\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"SODTJRAU\",\"vnp_TransactionNo\":\"15271395\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202511200048098879\",\"vnp_SecureHash\":\"1e55dcafdff563930a79bbcc945ba55fa10f1f918866678f5c65356bb4a8d680442e630b3257ebdd35387295e9e372df965cede95b4a5c8bb8597bfa0c2fb383\"}', 'delivered', NULL, NULL, NULL, '2025-11-19 17:48:09', '2025-12-05 10:57:09'),
(29, 7, 'ORD202512042338303815', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-04 16:38:30', '2025-12-04 16:38:30'),
(30, 7, 'ORD202512042353134390', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'cod', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-04 16:53:13', '2025-12-04 16:53:13'),
(31, 7, 'ORD202512042353342864', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-04 16:53:34', '2025-12-04 16:53:34'),
(32, 7, 'ORD202512051825512428', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'standard', 1010000.00, 30000.00, 101000.00, 30000.00, 131000.00, 909000.00, 'cod', 'WELCOME2025, FREESHIP', 131000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-05 11:25:52', '2025-12-05 11:25:52'),
(33, 7, 'ORD202512051827131777', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'express', 1010000.00, 50000.00, 101000.00, 30000.00, 131000.00, 929000.00, 'cod', 'WELCOME2025, FREESHIP', 131000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-05 11:27:13', '2025-12-05 11:27:13'),
(34, 7, 'ORD202512051828358708', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'standard', 1360000.00, 30000.00, 136000.00, 0.00, 136000.00, 1254000.00, 'vnpay', 'WELCOME2025', 136000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-05 11:28:35', '2025-12-05 11:28:35'),
(35, 7, 'ORD202512052254562573', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'express', 960000.00, 50000.00, 96000.00, 0.00, 96000.00, 914000.00, 'vnpay', 'WELCOME2025', 96000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-05 15:54:56', '2025-12-05 15:54:56'),
(36, 7, 'ORD202512052315085621', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', 'Ha Noi', '', 'standard', 400000.00, 30000.00, 0.00, 0.00, 0.00, 430000.00, 'vnpay', '', 0.00, 'paid', '{\"vnp_Amount\":\"43000000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15322789\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202512052315085621\",\"vnp_PayDate\":\"20251205231609\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"BQPU2FOW\",\"vnp_TransactionNo\":\"15322789\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202512052315085621\",\"vnp_SecureHash\":\"b7e0358ef84f4dc973ee4e167dba760664e7caead5e1304b807846bbb23bc1bfcc0b1ae51f5446881a6d9135a7fecb9a4c948c5d539640244faa315061000e0c\"}', 'pending', NULL, NULL, NULL, '2025-12-05 16:15:08', '2025-12-05 16:16:09'),
(37, 7, 'ORD202512061704477236', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 450000.00, 30000.00, 45000.00, 0.00, 45000.00, 435000.00, 'vnpay', 'WELCOME2025', 45000.00, 'paid', '{\"vnp_Amount\":\"43500000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15323796\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202512061704477236\",\"vnp_PayDate\":\"20251206170547\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"BQPU2FOW\",\"vnp_TransactionNo\":\"15323796\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202512061704477236\",\"vnp_SecureHash\":\"fa90e1434e6a093de85405f4f82ea96dc543a2e1f7cddaa9c15e692c18d1a94bc8ab94c777c0ddc10a27baa15333d4cc2be0e733315d045ace1a8aefc9d7bf46\"}', 'pending', NULL, NULL, NULL, '2025-12-06 10:04:47', '2025-12-06 10:05:53'),
(38, 7, 'ORD202512072350347847', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 1520000.00, 30000.00, 0.00, 0.00, 0.00, 1550000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-07 16:50:34', '2025-12-07 16:50:34'),
(39, 7, 'ORD202512072359374294', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'failed', '{\"vnp_Amount\":\"59000000\",\"vnp_BankCode\":\"VNPAY\",\"vnp_CardType\":\"QRCODE\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202512072359374294\",\"vnp_PayDate\":\"20251207235944\",\"vnp_ResponseCode\":\"24\",\"vnp_TmnCode\":\"BQPU2FOW\",\"vnp_TransactionNo\":\"0\",\"vnp_TransactionStatus\":\"02\",\"vnp_TxnRef\":\"ORD202512072359374294\",\"vnp_SecureHash\":\"67a5589aafa879d2f844f949584db044a936fd75421f5f8a31bb7f4547093d8dacce4bfcc8c4aa1d1fe7e514a9a32ab75f03c01fee1903df564fd25cd82ee9a9\"}', 'pending', NULL, NULL, NULL, '2025-12-07 16:59:37', '2025-12-07 17:00:32'),
(40, 7, 'ORD202512080000571154', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 560000.00, 30000.00, 0.00, 0.00, 0.00, 590000.00, 'vnpay', '', 0.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-07 17:00:57', '2025-12-07 17:00:57'),
(41, 7, 'ORD202512080218465629', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'express', 960000.00, 50000.00, 96000.00, 30000.00, 126000.00, 884000.00, 'vnpay', 'WELCOME2025, FREESHIP', 126000.00, 'paid', '{\"vnp_Amount\":\"88400000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15326192\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Thanh toan don hang ORD202512080218465629\",\"vnp_PayDate\":\"20251208021935\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"BQPU2FOW\",\"vnp_TransactionNo\":\"15326192\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"ORD202512080218465629\",\"vnp_SecureHash\":\"e54f41da3895d2c9a4ee512d74414ed35cdaa6b0bf654538e7415b26386222b007ffedb37f7de8de69a0e9feef7e6b0d4db40cc086bc5c94387e53f899801c51\"}', 'pending', NULL, NULL, NULL, '2025-12-07 19:18:46', '2025-12-07 19:19:40'),
(42, 7, 'ORD202512080318435473', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 450000.00, 30000.00, 45000.00, 0.00, 45000.00, 435000.00, 'cod', 'WELCOME2025', 45000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-07 20:18:43', '2025-12-07 20:18:43'),
(43, 7, 'ORD202512080329438877', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 560000.00, 30000.00, 56000.00, 0.00, 56000.00, 534000.00, 'cod', 'WELCOME2025', 56000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-07 20:29:43', '2025-12-07 20:29:43'),
(44, 7, 'ORD202512080332443392', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 900000.00, 30000.00, 0.00, 30000.00, 30000.00, 900000.00, 'cod', 'FREESHIP', 30000.00, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-12-07 20:32:44', '2025-12-07 20:32:44'),
(45, 7, 'ORD202512080333404937', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 560000.00, 30000.00, 56000.00, 0.00, 56000.00, 534000.00, 'cod', 'WELCOME2025', 56000.00, 'paid', 'Auto-confirmed on delivery', 'delivered', NULL, NULL, NULL, '2025-12-07 20:33:40', '2025-12-08 16:00:18'),
(46, 7, 'ORD202512092306006946', 'Nguyễn Đình Tuấn', 'xmeo2612x@gmail.com', '0987654321', '2B, ngõ 107 hữu hưng, Phường Tây Mỗ, Quận Nam Từ Liêm, Thành phố Hà Nội', '', 'standard', 1300000.00, 30000.00, 0.00, 0.00, 0.00, 1330000.00, 'cod', '', 0.00, 'paid', 'Manual confirmation by admin: Trịnh Phương Anh at 2025-12-09 23:07:45', 'delivered', NULL, NULL, NULL, '2025-12-09 16:06:00', '2025-12-09 16:07:52');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `quantity`, `price`, `subtotal`, `created_at`) VALUES
(13, 9, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 12:32:13'),
(14, 9, 4, 'Hoa Hồng Đỏ 12 Bông', 'uploads/products/690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-11-13 12:32:13'),
(15, 10, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 12:41:59'),
(16, 10, 4, 'Hoa Hồng Đỏ 12 Bông', 'uploads/products/690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-11-13 12:41:59'),
(17, 11, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 12:49:18'),
(18, 11, 4, 'Hoa Hồng Đỏ 12 Bông', 'uploads/products/690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-11-13 12:49:18'),
(19, 12, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 12:50:10'),
(20, 13, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 12:50:54'),
(21, 14, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 13:12:20'),
(22, 14, 4, 'Hoa Hồng Đỏ 12 Bông', 'uploads/products/690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-11-13 13:12:20'),
(23, 15, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 13:12:40'),
(24, 16, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 14:05:52'),
(25, 17, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 14:08:47'),
(26, 18, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 14:47:00'),
(27, 19, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 15:14:33'),
(28, 20, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 15:53:42'),
(29, 21, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 16:00:57'),
(30, 22, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 16:13:31'),
(31, 23, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 16:14:02'),
(32, 24, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-13 17:22:36'),
(33, 25, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-14 01:03:53'),
(34, 26, 6, 'Only rose premium 3', 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-14 02:29:56'),
(35, 27, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-11-14 04:26:00'),
(36, 28, 4, 'Hoa Hồng Đỏ 12 Bông', '690f712b6b0aa_15147_love-and-love.jpg', 2, 450000.00, 900000.00, '2025-11-19 17:48:09'),
(37, 29, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-04 16:38:30'),
(38, 30, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-04 16:53:13'),
(39, 31, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-04 16:53:34'),
(40, 32, 4, 'Hoa Hồng Đỏ 12 Bông', '690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-12-05 11:25:52'),
(41, 32, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-05 11:25:52'),
(42, 33, 4, 'Hoa Hồng Đỏ 12 Bông', '690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-12-05 11:27:13'),
(43, 33, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-05 11:27:13'),
(44, 34, 2, 'Giỏ Hoa Sinh Nhật Mix', '690f716d3a8e8_5049_hidden-feelings.jpg', 1, 400000.00, 400000.00, '2025-12-05 11:28:35'),
(45, 34, 3, 'Hoa đẹp', '690f71885202e_4796_tinh-yeu-dieu-ky.jpg', 1, 960000.00, 960000.00, '2025-12-05 11:28:35'),
(46, 35, 3, 'Hoa đẹp', '690f71885202e_4796_tinh-yeu-dieu-ky.jpg', 1, 960000.00, 960000.00, '2025-12-05 15:54:56'),
(47, 36, 2, 'Giỏ Hoa Sinh Nhật Mix', '690f716d3a8e8_5049_hidden-feelings.jpg', 1, 400000.00, 400000.00, '2025-12-05 16:15:08'),
(48, 37, 4, 'Hoa Hồng Đỏ 12 Bông', '690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-12-06 10:04:47'),
(49, 38, 2, 'Giỏ Hoa Sinh Nhật Mix', '690f716d3a8e8_5049_hidden-feelings.jpg', 1, 400000.00, 400000.00, '2025-12-07 16:50:34'),
(50, 38, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 2, 560000.00, 1120000.00, '2025-12-07 16:50:34'),
(51, 39, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-07 16:59:37'),
(52, 40, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-07 17:00:57'),
(53, 41, 3, 'Hoa đẹp', '690f71885202e_4796_tinh-yeu-dieu-ky.jpg', 1, 960000.00, 960000.00, '2025-12-07 19:18:46'),
(54, 42, 4, 'Hoa Hồng Đỏ 12 Bông', '690f712b6b0aa_15147_love-and-love.jpg', 1, 450000.00, 450000.00, '2025-12-07 20:18:43'),
(55, 43, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-07 20:29:43'),
(56, 44, 4, 'Hoa Hồng Đỏ 12 Bông', '690f712b6b0aa_15147_love-and-love.jpg', 2, 450000.00, 900000.00, '2025-12-07 20:32:44'),
(57, 45, 6, 'Only rose premium 3', '690f8b755aea6_13277_only-rose-premium.jpg', 1, 560000.00, 560000.00, '2025-12-07 20:33:40'),
(58, 46, 2, 'Giỏ Hoa Sinh Nhật Mix', '690f716d3a8e8_5049_hidden-feelings.jpg', 1, 400000.00, 400000.00, '2025-12-09 16:06:00'),
(59, 46, 4, 'Hoa Hồng Đỏ 12 Bông', '690f712b6b0aa_15147_love-and-love.jpg', 2, 450000.00, 900000.00, '2025-12-09 16:06:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `is_featured` tinyint(1) DEFAULT 0,
  `is_bestseller` tinyint(1) DEFAULT 0,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `meta_keywords` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `stock_quantity`, `image`, `gallery`, `sku`, `views`, `status`, `is_featured`, `is_bestseller`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(2, 1, 'Giỏ Hoa Sinh Nhật Mix', 'gio-hoa-sinh-nhat-mix', 'Giỏ hoa sinh nhật đa sắc màu, tươi tắn', 'Giỏ hoa sinh nhật đẹp và ý nghĩa', 450000.00, 1142, 'uploads/products/690f716d3a8e8_5049_hidden-feelings.jpg', NULL, 'BIRTH-MIX-01', 0, 'active', 1, 0, NULL, NULL, NULL, '2025-11-06 20:05:28', '2025-12-11 08:26:19'),
(3, 3, 'Hoa đẹp', 'hoa-dep', 'Kệ hoa chúc mừng khai trương cao cấp', 'Kệ hoa khai trương sang trọng', 1200000.00, 14, 'uploads/products/690f71885202e_4796_tinh-yeu-dieu-ky.jpg', NULL, 'OPEN-STAND-01', 0, 'active', 0, 0, NULL, NULL, NULL, '2025-11-06 20:05:28', '2025-12-07 19:18:46'),
(4, 1, 'Hoa Hồng Đỏ 12 Bông', 'hoa-hong-o-12-bong', 'Đẹp', NULL, 500000.00, 74, 'uploads/products/690f712b6b0aa_15147_love-and-love.jpg', NULL, NULL, 0, 'active', 0, 0, NULL, NULL, NULL, '2025-11-08 16:25:32', '2025-12-10 20:14:20'),
(6, 6, 'Only rose premium 3', 'only-rose-premium-3', 'Sản phẩm bao gồm:\r\nHồng tím cà: 30', NULL, 700000.00, 100, 'uploads/products/690f8b755aea6_13277_only-rose-premium.jpg', NULL, NULL, 0, 'active', 0, 0, NULL, NULL, NULL, '2025-11-08 18:27:01', '2025-12-10 20:13:39'),
(9, 6, 'Xiaomi', 'xiaomi', 'áddasd', NULL, 123000.00, 461, 'uploads/products/693560398f3d9_Screenshot (716).png', NULL, NULL, 0, 'inactive', 0, 0, NULL, NULL, NULL, '2025-12-07 11:08:41', '2025-12-11 08:25:22'),
(10, 6, 'ahsdja', 'ahsdja', 'hạd', NULL, 100000.00, 400, 'uploads/products/6939ec48b021d_cloud-service-models-iaas-paas-saas-stackscale.jpg', NULL, NULL, 0, 'active', 0, 0, NULL, NULL, NULL, '2025-12-10 21:55:20', '2025-12-11 08:25:35');

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_overview`
-- (See below for the actual view)
--
CREATE TABLE `product_overview` (
`id` int(11)
,`category_id` int(11)
,`name` varchar(200)
,`slug` varchar(200)
,`description` text
,`short_description` varchar(500)
,`price` decimal(10,2)
,`stock_quantity` int(11)
,`image` varchar(255)
,`gallery` text
,`sku` varchar(50)
,`views` int(11)
,`status` enum('active','inactive','out_of_stock')
,`is_featured` tinyint(1)
,`is_bestseller` tinyint(1)
,`meta_title` varchar(200)
,`meta_description` varchar(500)
,`meta_keywords` varchar(200)
,`created_at` timestamp
,`updated_at` timestamp
,`category_name` varchar(100)
,`review_count` bigint(21)
,`avg_rating` decimal(14,4)
,`total_sold` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Tên chương trình khuyến mãi',
  `description` text DEFAULT NULL COMMENT 'Mô tả chi tiết',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage' COMMENT 'Loại giảm giá: phần trăm hoặc số tiền cố định',
  `discount_value` decimal(10,2) NOT NULL COMMENT 'Giá trị giảm (% hoặc số tiền)',
  `apply_to` enum('all','category','product') NOT NULL DEFAULT 'all' COMMENT 'Áp dụng cho: tất cả/danh mục/sản phẩm',
  `category_id` int(11) DEFAULT NULL COMMENT 'ID danh mục (nếu apply_to = category)',
  `start_date` datetime NOT NULL COMMENT 'Ngày bắt đầu',
  `end_date` datetime NOT NULL COMMENT 'Ngày kết thúc',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái hoạt động',
  `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'Số tiền giảm tối đa (cho % discount)',
  `min_order_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Giá trị đơn hàng tối thiểu',
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Số lần sử dụng tối đa (NULL = không giới hạn)',
  `used_count` int(11) DEFAULT 0 COMMENT 'Số lần đã sử dụng',
  `priority` int(11) DEFAULT 0 COMMENT 'Độ ưu tiên (số càng cao càng ưu tiên)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `name`, `description`, `discount_type`, `discount_value`, `apply_to`, `category_id`, `start_date`, `end_date`, `is_active`, `max_discount_amount`, `min_order_amount`, `usage_limit`, `used_count`, `priority`, `created_at`, `updated_at`) VALUES
(1, 'Giảm giá 20% cho tất cả sản phẩm', 'Chương trình khuyến mãi đặc biệt - giảm 20% cho toàn bộ sản phẩm trong cửa hàng', 'percentage', 20.00, 'all', NULL, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, 500000.00, 0.00, NULL, 0, 1, '2025-11-10 11:25:12', '2025-11-10 11:25:12'),
(2, 'Giảm 50,000đ cho hoa sinh nhật', 'Giảm giá cố định 50,000đ cho tất cả hoa sinh nhật', 'fixed', 50000.00, 'category', 1, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, NULL, 0.00, NULL, 0, 2, '2025-11-10 11:25:12', '2025-11-10 11:25:12'),
(3, 'Flash Sale 30%', 'Flash sale giảm giá 30% cho sản phẩm được chọn', 'percentage', 30.00, 'product', NULL, '2025-01-01 00:00:00', '2025-12-31 23:59:00', 0, 300000.00, 0.00, NULL, 0, 3, '2025-11-10 11:25:12', '2025-11-10 15:30:03'),
(4, 'Giảm giá 20% cho tất cả sản phẩm', 'Chương trình khuyến mãi đặc biệt - giảm 20% cho toàn bộ sản phẩm trong cửa hàng', 'percentage', 20.00, 'all', NULL, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, 500000.00, 0.00, NULL, 0, 1, '2025-11-10 11:25:12', '2025-11-10 11:25:12'),
(5, 'Giảm 50,000đ cho hoa sinh nhật', 'Giảm giá cố định 50,000đ cho tất cả hoa sinh nhật', 'fixed', 50000.00, 'category', 1, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, NULL, 0.00, NULL, 0, 2, '2025-11-10 11:25:12', '2025-11-10 11:25:12'),
(6, 'Flash Sale 30%', 'Flash sale giảm giá 30% cho sản phẩm được chọn', 'percentage', 30.00, 'product', NULL, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, 300000.00, 0.00, NULL, 0, 3, '2025-11-10 11:25:12', '2025-12-10 16:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `promotion_products`
--

CREATE TABLE `promotion_products` (
  `id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotion_products`
--

INSERT INTO `promotion_products` (`id`, `promotion_id`, `product_id`, `created_at`) VALUES
(1, 3, 3, '2025-11-10 15:29:19');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_code` varchar(50) NOT NULL COMMENT 'Mã đơn mua hàng',
  `supplier_id` int(11) NOT NULL COMMENT 'ID nhà cung cấp',
  `contract_id` int(11) DEFAULT NULL COMMENT 'ID hợp đồng',
  `order_date` date NOT NULL COMMENT 'Ngày đặt hàng',
  `expected_delivery_date` date DEFAULT NULL COMMENT 'Ngày dự kiến giao',
  `actual_delivery_date` date DEFAULT NULL COMMENT 'Ngày giao thực tế',
  `total_amount` decimal(15,2) NOT NULL COMMENT 'Tổng tiền',
  `paid_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Đã thanh toán',
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid' COMMENT 'Trạng thái thanh toán',
  `order_status` enum('draft','pending','confirmed','shipping','completed','cancelled') DEFAULT 'draft' COMMENT 'Trạng thái đơn hàng',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú',
  `created_by` int(11) DEFAULT NULL COMMENT 'Người tạo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL COMMENT 'ID đơn mua hàng',
  `supplier_product_id` int(11) DEFAULT NULL COMMENT 'ID sản phẩm của nhà cung cấp',
  `product_id` int(11) DEFAULT NULL COMMENT 'ID sản phẩm trong hệ thống',
  `product_name` varchar(255) NOT NULL COMMENT 'Tên sản phẩm',
  `quantity` int(11) NOT NULL COMMENT 'Số lượng',
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Đơn giá',
  `subtotal` decimal(15,2) NOT NULL COMMENT 'Thành tiền',
  `received_quantity` int(11) DEFAULT 0 COMMENT 'Số lượng đã nhận',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `moderated_by` int(11) DEFAULT NULL,
  `moderated_at` datetime DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `images`, `status`, `admin_note`, `moderated_by`, `moderated_at`, `admin_reply`, `replied_at`, `replied_by`, `created_at`, `updated_at`) VALUES
(2, 4, 7, 28, 1, NULL, 'xấu', NULL, 'rejected', 'abc', 16, '2025-12-06 23:46:25', NULL, NULL, NULL, '2025-11-19 20:23:12', '2025-12-06 16:46:25'),
(3, 6, 7, 27, 5, NULL, 'Đẹp', NULL, 'approved', NULL, 16, '2025-12-06 23:47:50', NULL, NULL, NULL, '2025-12-06 10:38:47', '2025-12-06 16:47:50'),
(4, 6, 7, 45, 5, NULL, 'Nothing', NULL, 'approved', NULL, 1, '2025-12-08 23:02:22', NULL, NULL, NULL, '2025-12-08 16:01:31', '2025-12-08 16:02:22');

--
-- Triggers `reviews`
--
DELIMITER $$
CREATE TRIGGER `after_review_approved` AFTER UPDATE ON `reviews` FOR EACH ROW BEGIN
    IF OLD.status = 'pending' AND NEW.status = 'approved' THEN
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (
            NEW.user_id,
            'review_approved',
            'Đánh giá của bạn đã được duyệt',
            CONCAT('Đánh giá của bạn cho sản phẩm đã được phê duyệt và hiển thị công khai.'),
            CONCAT('/product/detail/', NEW.product_id)
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_review_insert` AFTER INSERT ON `reviews` FOR EACH ROW BEGIN
    -- Có thể thêm logic cập nhật rating trung bình vào bảng products nếu cần
    -- Hoặc sử dụng view như đã tạo ở trên
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_review_rejected` AFTER UPDATE ON `reviews` FOR EACH ROW BEGIN
    IF OLD.status = 'pending' AND NEW.status = 'rejected' THEN
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (
            NEW.user_id,
            'review_rejected',
            'Đánh giá của bạn đã bị từ chối',
            CONCAT('Lý do: ', IFNULL(NEW.admin_note, 'Không phù hợp với tiêu chuẩn cộng đồng')),
            NULL
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `review_reports`
--

CREATE TABLE `review_reports` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` enum('spam','offensive_language','inappropriate_content','fake_review','personal_attack','other') NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewing','resolved','dismissed') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `handled_by` int(11) DEFAULT NULL,
  `handled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Flower Shop', 'text', 'Tên website', '2025-11-06 20:05:29'),
(2, 'site_email', 'contact@flowershop.com', 'text', 'Email liên hệ', '2025-11-06 20:05:29'),
(3, 'site_phone', '0123456789', 'text', 'Số điện thoại', '2025-11-06 20:05:29'),
(4, 'site_address', '123 Đường ABC, Quận 1, TP.HCM', 'text', 'Địa chỉ', '2025-11-06 20:05:29'),
(5, 'shipping_fee', '30000', 'number', 'Phí ship mặc định', '2025-11-06 20:05:29'),
(6, 'free_ship_threshold', '500000', 'number', 'Miễn phí ship từ', '2025-11-06 20:05:29'),
(7, 'vnpay_enabled', '1', 'boolean', 'Bật VNPay', '2025-11-06 20:05:29'),
(8, 'email_notifications', '1', 'boolean', 'Gửi email thông báo', '2025-11-06 20:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề slider',
  `description` text DEFAULT NULL COMMENT 'Mô tả ngắn',
  `image` varchar(500) NOT NULL COMMENT 'Đường dẫn ảnh slider',
  `link` varchar(500) DEFAULT NULL COMMENT 'Link khi click vào slider',
  `button_text` varchar(100) DEFAULT NULL COMMENT 'Text nút CTA',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Hiển thị, 0=Ẩn',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý slider/banner trang chủ';

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `description`, `image`, `link`, `button_text`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'Hoa Cưới Cao Cấp – Tôn Vinh Vẻ Đẹp Cô Dâu', 'Từng bó hoa đều được thiết kế thủ công với tone màu trang nhã, mang đến sự nổi bật cho cô dâu trong ngày cưới.', 'uploads/sliders/slider_1765142576_6935f030626bd.jpg', 'products?search=&category=3&price_range=&min_price=&max_price=&rating=', 'Xem Hoa Cưới', 0, 1, '2025-12-07 21:22:56', '2025-12-07 21:22:56'),
(5, 'Hoa Sinh Nhật Tươi Đẹp – Thay Lời Chúc Ý Nghĩa', 'Gửi tặng những bó hoa sinh nhật ngọt ngào, sang trọng – giúp bạn tạo dấu ấn trong mọi khoảnh khắc đặc biệt.', 'uploads/sliders/slider_1765142793_6935f1093f7fc.png', 'products?search=&category=1&price_range=&min_price=&max_price=&rating=', 'Xem Sản Phẩm', 1, 1, '2025-12-07 21:26:33', '2025-12-07 21:26:33'),
(6, 'Hoa Tặng Người Thương – Ưu Đãi Hôm Nay', 'Lựa chọn những mẫu hoa đẹp nhất, sang trọng nhất dành riêng cho người bạn yêu. Đặt ngay để nhận ưu đãi đặc biệt.', 'uploads/sliders/slider_1765145281_6935fac17f00e.png', 'products?search=&category=5&price_range=&min_price=&max_price=&rating=', 'Đặt Hoa Ngay', 2, 1, '2025-12-07 21:53:17', '2025-12-07 22:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tax_code` varchar(50) DEFAULT NULL COMMENT 'Mã số thuế',
  `contact_person` varchar(100) DEFAULT NULL COMMENT 'Người liên hệ',
  `contact_position` varchar(100) DEFAULT NULL COMMENT 'Chức vụ người liên hệ',
  `address` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL COMMENT 'Website',
  `bank_account` varchar(50) DEFAULT NULL COMMENT 'Số tài khoản',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'Ngân hàng',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT 'Trạng thái',
  `rating` decimal(2,1) DEFAULT NULL COMMENT 'Đánh giá (1-5 sao)',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `tax_code`, `contact_person`, `contact_position`, `address`, `website`, `bank_account`, `bank_name`, `status`, `rating`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Vườn hoa Đà Lạt', '0123456789', 'dalat@flowers.vn', NULL, NULL, NULL, 'Đà Lạt, Lâm Đồng', NULL, NULL, NULL, 'active', NULL, NULL, '2025-12-04 17:33:12', '2025-12-04 17:33:12', NULL),
(2, 'Trang trại hoa Sài Gòn', '0987654321', 'saigon@flowers.vn', NULL, NULL, NULL, 'Quận 9, TP. Hồ Chí Minh', NULL, NULL, NULL, 'active', NULL, NULL, '2025-12-04 17:33:12', '2025-12-04 17:33:12', NULL),
(3, 'Hoa nhập khẩu Hà Nội', '0369258147', 'hanoi@flowers.vn', NULL, NULL, NULL, 'Hoàn Kiếm, Hà Nội', NULL, NULL, NULL, 'active', NULL, NULL, '2025-12-04 17:33:12', '2025-12-04 17:33:12', NULL),
(4, 'Vườn hoa Mỹ Tho', '0789456123', 'mytho@flowers.vn', NULL, NULL, NULL, 'Mỹ Tho, Tiền Giang', NULL, NULL, NULL, 'active', NULL, NULL, '2025-12-04 17:33:12', '2025-12-04 17:33:12', NULL),
(5, 'abc', '0987654321', '1xss31@gmail.com', NULL, NULL, NULL, 'abc', NULL, NULL, NULL, 'active', NULL, NULL, '2025-12-11 07:14:06', '2025-12-11 07:31:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_contracts`
--

CREATE TABLE `supplier_contracts` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL COMMENT 'ID nhà cung cấp',
  `contract_code` varchar(50) NOT NULL COMMENT 'Mã hợp đồng',
  `contract_name` varchar(255) NOT NULL COMMENT 'Tên hợp đồng',
  `contract_type` enum('purchase','exclusive','partnership') DEFAULT 'purchase' COMMENT 'Loại hợp đồng',
  `contract_value` decimal(15,2) DEFAULT NULL COMMENT 'Giá trị hợp đồng',
  `start_date` date NOT NULL COMMENT 'Ngày bắt đầu',
  `end_date` date DEFAULT NULL COMMENT 'Ngày kết thúc',
  `payment_terms` varchar(255) DEFAULT NULL COMMENT 'Điều khoản thanh toán',
  `delivery_terms` text DEFAULT NULL COMMENT 'Điều khoản giao hàng',
  `status` enum('draft','active','expired','terminated') DEFAULT 'draft' COMMENT 'Trạng thái',
  `file_path` varchar(500) DEFAULT NULL COMMENT 'Đường dẫn file hợp đồng',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú',
  `created_by` int(11) DEFAULT NULL COMMENT 'Người tạo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_contracts`
--

INSERT INTO `supplier_contracts` (`id`, `supplier_id`, `contract_code`, `contract_name`, `contract_type`, `contract_value`, `start_date`, `end_date`, `payment_terms`, `delivery_terms`, `status`, `file_path`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'HD-2025-001', 'Hợp đồng cung cấp hoa Đà Lạt 2025', 'purchase', 500000000.00, '2025-01-01', '2025-12-31', 'Thanh toán trong 30 ngày', NULL, 'active', NULL, NULL, NULL, '2025-12-07 11:45:20', '2025-12-07 11:45:20'),
(2, 2, 'HD-2025-002', 'Hợp đồng độc quyền hoa Sài Gòn', 'exclusive', 800000000.00, '2025-01-01', '2026-12-31', 'Thanh toán trong 15 ngày', NULL, 'active', NULL, NULL, NULL, '2025-12-07 11:45:20', '2025-12-07 11:45:20'),
(3, 3, 'HD-2024-003', 'Hợp đồng hoa nhập khẩu 2024', 'purchase', 300000000.00, '2024-06-01', '2024-12-31', 'Thanh toán ngay', NULL, 'expired', NULL, NULL, NULL, '2025-12-07 11:45:20', '2025-12-07 11:45:20'),
(4, 3, 'HD-2025-004', 'ádasd', 'purchase', 20000000.00, '2025-11-12', '2030-12-12', NULL, NULL, 'active', NULL, 'ákdhjad', 16, '2025-12-10 21:56:49', '2025-12-10 21:56:49'),
(5, 5, 'HD-2025-006', 'Hợp đồng cung cấp hoa Đà Lạt 2025', 'purchase', 10000000.00, '2025-12-02', '2026-01-01', 'b', NULL, 'active', NULL, 'adasd', 16, '2025-12-11 07:15:21', '2025-12-11 07:31:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default-avatar.png',
  `role` enum('superadmin','admin','user') DEFAULT 'user',
  `status` enum('pending','active','inactive','banned') DEFAULT 'pending',
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `admin_approved_by` int(11) DEFAULT NULL,
  `admin_approved_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `avatar`, `role`, `status`, `otp_code`, `otp_expiry`, `reset_token`, `reset_token_expiry`, `email_verified`, `admin_approved_by`, `admin_approved_at`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'superadmin@flowershop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', NULL, NULL, 'default-avatar.png', 'superadmin', 'active', NULL, NULL, NULL, NULL, 1, NULL, NULL, '2025-12-08 22:56:25', '2025-11-06 20:05:28', '2025-12-08 15:56:25'),
(7, 'user1', 'xmeo2612x@gmail.com', '$2y$10$gmS5aTlorNIPu4/PawLA0.iDLGM/0gESsVJrw/dw.Ow2uHt22PDmi', 'Nguyễn Đình Tuấn', '0987654321', 'Ha Noi', 'uploads/avatars/avatar_7_1765184903.jpg', 'user', 'active', NULL, NULL, '451b723eea2c2962324527a5cce2b15f94fa2191118419ca3dbca521b0fd083a', '2025-11-17 18:42:49', 1, NULL, NULL, '2025-12-11 15:06:23', '2025-11-06 21:56:40', '2025-12-11 08:06:23'),
(16, 'phuonganh', 'zmeo2612@gmail.com', '$2y$10$ds82Aakx.gUMJ4DfrcQPeulzzyFt58g59wwJ/T1tccPaaTh6kEmlS', 'Trịnh Phương Anh', '0147258369', '', 'default-avatar.png', 'admin', 'active', NULL, NULL, NULL, NULL, 1, 1, '2025-11-07 06:19:03', '2025-12-11 14:12:32', '2025-11-06 23:14:49', '2025-12-11 07:12:32'),
(17, 'pqtisme', 'phamquangtuan.contact@gmail.com', '$2y$10$wD8UF7e4Zyvz8tFDOjYQd.uHInXMTVLSfHPcVFRpigmvEWpzQuY2i', 'Phạm Quang Tuấn', '0369585104', NULL, 'default-avatar.png', 'admin', 'pending', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-12-08 16:09:54', '2025-12-08 16:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID người dùng',
  `recipient_name` varchar(100) NOT NULL COMMENT 'Tên người nhận',
  `phone` varchar(20) NOT NULL COMMENT 'Số điện thoại người nhận',
  `province` varchar(100) NOT NULL COMMENT 'Tỉnh/Thành phố',
  `district` varchar(100) NOT NULL COMMENT 'Quận/Huyện',
  `ward` varchar(100) NOT NULL COMMENT 'Phường/Xã',
  `address_detail` text NOT NULL COMMENT 'Địa chỉ chi tiết (số nhà, tên đường)',
  `address_type` enum('home','office') DEFAULT 'home' COMMENT 'Loại địa chỉ: Nhà riêng, Cơ quan',
  `is_default` tinyint(1) DEFAULT 0 COMMENT '1 = Địa chỉ mặc định',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quản lý nhiều địa chỉ giao hàng cho mỗi user';

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `recipient_name`, `phone`, `province`, `district`, `ward`, `address_detail`, `address_type`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 7, 'Nguyễn Đình Tuấn', '0987654321', 'Thành phố Hà Nội', 'Quận Nam Từ Liêm', 'Phường Tây Mỗ', '2B, ngõ 107 hữu hưng', 'home', 1, '2025-12-06 09:55:13', '2025-12-06 10:03:59'),
(2, 7, 'Nguyễn Đình Tuấn', '0987654321', 'Tỉnh Thái Bình', 'Huyện Vũ Thư', 'Xã Bách Thuận', 'xóm 7, thôn Chiến Thắng', 'home', 0, '2025-12-06 09:55:59', '2025-12-06 10:03:59');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(24, 7, 6, '2025-12-08 17:16:28');

-- --------------------------------------------------------

--
-- Structure for view `daily_order_stats`
--
DROP TABLE IF EXISTS `daily_order_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_order_stats`  AS SELECT cast(`orders`.`created_at` as date) AS `order_date`, count(0) AS `total_orders`, sum(`orders`.`total`) AS `total_revenue`, count(case when `orders`.`order_status` = 'delivered' then 1 end) AS `delivered_orders`, count(case when `orders`.`order_status` = 'cancelled' then 1 end) AS `cancelled_orders` FROM `orders` GROUP BY cast(`orders`.`created_at` as date) ;

-- --------------------------------------------------------

--
-- Structure for view `product_overview`
--
DROP TABLE IF EXISTS `product_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_overview`  AS SELECT `p`.`id` AS `id`, `p`.`category_id` AS `category_id`, `p`.`name` AS `name`, `p`.`slug` AS `slug`, `p`.`description` AS `description`, `p`.`short_description` AS `short_description`, `p`.`price` AS `price`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`image` AS `image`, `p`.`gallery` AS `gallery`, `p`.`sku` AS `sku`, `p`.`views` AS `views`, `p`.`status` AS `status`, `p`.`is_featured` AS `is_featured`, `p`.`is_bestseller` AS `is_bestseller`, `p`.`meta_title` AS `meta_title`, `p`.`meta_description` AS `meta_description`, `p`.`meta_keywords` AS `meta_keywords`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `c`.`name` AS `category_name`, count(distinct `r`.`id`) AS `review_count`, coalesce(avg(`r`.`rating`),0) AS `avg_rating`, count(distinct `oi`.`order_id`) AS `total_sold` FROM (((`products` `p` left join `categories` `c` on(`p`.`category_id` = `c`.`id`)) left join `reviews` `r` on(`p`.`id` = `r`.`product_id` and `r`.`status` = 'approved')) left join `order_items` `oi` on(`p`.`id` = `oi`.`product_id`)) GROUP BY `p`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- Indexes for table `contract_products`
--
ALTER TABLE `contract_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_contract_product` (`contract_id`,`product_id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_coupon` (`coupon_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `replied_by` (`replied_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_published` (`published_at`);

--
-- Indexes for table `news_comments`
--
ALTER TABLE `news_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `news_likes`
--
ALTER TABLE `news_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`news_id`,`user_id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order_code` (`order_code`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_bestseller` (`is_bestseller`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_apply_to` (`apply_to`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indexes for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promotion_product` (`promotion_id`,`product_id`),
  ADD KEY `idx_promotion_id` (`promotion_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_code` (`po_code`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `supplier_product_id` (`supplier_product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `replied_by` (`replied_by`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `review_reports`
--
ALTER TABLE `review_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_report` (`review_id`,`reporter_id`),
  ADD KEY `idx_review_id` (`review_id`),
  ADD KEY `idx_reporter_id` (`reporter_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `handled_by` (`handled_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `display_order` (`display_order`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_contracts`
--
ALTER TABLE `supplier_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contract_code` (`contract_code`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `admin_approved_by` (`admin_approved_by`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_default` (`is_default`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contract_products`
--
ALTER TABLE `contract_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `news_comments`
--
ALTER TABLE `news_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `news_likes`
--
ALTER TABLE `news_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promotion_products`
--
ALTER TABLE `promotion_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `review_reports`
--
ALTER TABLE `review_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier_contracts`
--
ALTER TABLE `supplier_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contract_products`
--
ALTER TABLE `contract_products`
  ADD CONSTRAINT `fk_contract_products_contract` FOREIGN KEY (`contract_id`) REFERENCES `supplier_contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_contract_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_comments`
--
ALTER TABLE `news_comments`
  ADD CONSTRAINT `fk_news_comments_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_news_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `news_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_news_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_likes`
--
ALTER TABLE `news_likes`
  ADD CONSTRAINT `fk_news_likes_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_news_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `fk_promotion_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD CONSTRAINT `fk_promotion_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_promotion_products_promotion` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `po_contract_fk` FOREIGN KEY (`contract_id`) REFERENCES `supplier_contracts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `po_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `po_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `poi_po_fk` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `poi_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `poi_supplier_product_fk` FOREIGN KEY (`supplier_product_id`) REFERENCES `supplier_products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `review_reports`
--
ALTER TABLE `review_reports`
  ADD CONSTRAINT `review_reports_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_reports_ibfk_3` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supplier_contracts`
--
ALTER TABLE `supplier_contracts`
  ADD CONSTRAINT `contracts_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contracts_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`admin_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `fk_user_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
