-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2025 at 06:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qlbansach`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_phone` varchar(20) NOT NULL,
  `province` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `specific_address` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `receiver_name`, `receiver_phone`, `province`, `district`, `ward`, `specific_address`, `is_default`) VALUES
(1, 1, 'Nguyễn Văn An', '0901234567', 'Hà Nội', 'Cầu Giấy', 'Dịch Vọng', 'số 12 Trần Quốc Hoàn', 1);

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`author_id`, `name`, `bio`) VALUES
(1, 'Nguyễn Nhật Ánh', 'Nhà văn nổi tiếng Việt Nam'),
(2, 'Haruki Murakami', 'Nhà văn Nhật Bản'),
(3, 'Gosho Aoyama', 'Tác giả viết truyện nổi tiếng Nhật Bản'),
(4, 'Koyoharu Gotouge', 'Cây viết truyện nổi tiếng Nhật Bản'),
(5, 'Kohei Horikoshi', 'Cây bút viết manga nổi tiếng Nhật Bản'),
(6, 'Nam Cao', 'Nhà văn hiện thực tiêu biểu của văn học Việt Nam'),
(7, 'Ngô Tất Tố', 'Nhà văn có ảnh hưởng lớn ở Việt Nam');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `publish_year` year(4) NOT NULL,
  `pages` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `cover_type` enum('hard','soft','','') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `publisher_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','hidden','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `slug`, `description`, `publish_year`, `pages`, `weight`, `cover_type`, `price`, `discounted_price`, `stock_quantity`, `category_id`, `publisher_id`, `created_at`, `updated_at`, `status`) VALUES
(1, 'Mắt biếc', 'mat-biec', 'Tiểu thuyết nổi tiếng của Nguyễn Nhật Ánh', '2015', 250, 300, 'soft', 85000.00, 70000.00, 99, 2, 1, '2025-12-01 15:47:18', '2025-12-12 23:56:04', 'active'),
(2, 'Rừng Nauy', 'rung-nauy', 'Tác phẩm của Haruki Murakami', '2010', 350, 400, 'soft', 120000.00, 95000.00, 76, 1, 2, '2025-12-01 15:47:18', '2025-12-13 23:58:02', 'active'),
(3, 'Thám tử lừng danh Conan', NULL, 'Bộ truyện tranh trinh thánh nổi tiếng khắp Châu Á', '1994', 80, 250, 'hard', 45000.00, 40000.00, 97, 10, 2, '2025-12-01 16:29:26', '2025-12-13 23:55:49', 'active'),
(4, 'Thanh gươm diệt quỷ', 'thanh-guom-diet-quy', 'Bộ truyện tranh hành động kịch tính kể về hành trình tiêu diệt loài quỷ của nhân vật chính Tanjiro', '2016', 350, 400, 'hard', 200000.00, 180000.00, 79, 9, 2, '2025-12-01 16:29:26', '2025-12-14 00:00:02', 'active'),
(5, 'Học viện siêu anh hùng', 'hoc-vien-sieu-anh-hung', 'Bộ truyện tranh hoành đống siêu kịch tính kể về hành trình trở thành người anh hùng vĩ đại nhất của nhân vật chính không có năng lực anh hùng Zuku', '2014', 500, 450, 'hard', 300000.00, 270000.00, 29, 9, 3, '2025-12-01 16:34:50', '2025-12-13 23:33:23', 'active'),
(6, 'Lão Hạc', '', 'Bộ truyện ngắn hiện thực nổi tiếng', '1943', 260, 230, 'soft', 60000.00, 55000.00, 97, 2, 3, '2025-12-01 16:34:50', '2025-12-14 00:21:19', 'active'),
(7, 'Tắt đèn', NULL, 'Tác phẩm văn học hiện thực nổi tiếng Việt Nam', '2016', 216, 264, 'soft', 55000.00, 45000.00, 138, 2, 3, '2025-12-01 16:37:08', '2025-12-14 00:20:42', 'active'),
(8, 'Chí Phèo', NULL, 'Truyện ngắn hiện thực nổi tiếng', '2022', 196, 250, 'soft', 50000.00, 1000.00, 75, 1, 2, '2025-12-01 16:48:00', '2025-12-14 00:33:25', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `book_authors`
--

CREATE TABLE `book_authors` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_authors`
--

INSERT INTO `book_authors` (`id`, `book_id`, `author_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 6, 6),
(4, 7, 7),
(5, 3, 3),
(6, 4, 4),
(7, 5, 5),
(8, 8, 6);

-- --------------------------------------------------------

--
-- Table structure for table `cancel_requests`
--

CREATE TABLE `cancel_requests` (
  `cancel_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancel_requests`
--

INSERT INTO `cancel_requests` (`cancel_id`, `order_id`, `user_id`, `reason`, `status`, `created_at`) VALUES
(0, 1, 1, 'Tôi đặt nhầm', 'approved', '2025-12-11 16:29:39'),
(0, 1, 1, 'Tôi đặt nhầm', 'approved', '2025-12-11 16:32:34'),
(0, 1, 1, 'Tôi đặt nhầm số lượng', 'approved', '2025-12-11 16:37:42'),
(0, 1, 1, 'Tôi đã đặt nhầm số lượng', 'approved', '2025-12-11 16:43:06'),
(0, 1, 1, 'Tôi đặt nhầm số lượng', 'pending', '2025-12-11 17:26:25'),
(0, 1, 1, 'aaaaaaaaaaaa', 'pending', '2025-12-13 16:49:34'),
(0, 1, 1, 'hư', 'pending', '2025-12-13 17:07:35'),
(0, 19, 1, 'hư', 'approved', '2025-12-13 17:08:35'),
(0, 40, 1, 'aaaaaaaaaaaa', 'pending', '2025-12-14 00:22:00');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `user_id`, `created_at`) VALUES
(1, 1, '2025-12-01 15:48:40'),
(2, 2, '2025-12-11 16:25:51');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `parent_id`) VALUES
(1, 'Sách văn học', NULL),
(2, 'Tiểu thuyết', 1),
(3, 'Ngôn tình', 2),
(5, 'Đam mỹ', 2),
(6, 'Bách hợp', 2),
(7, 'Kinh tế', NULL),
(8, 'Kỹ năng sống', NULL),
(9, 'Truyện tranh', NULL),
(10, 'Trinh thám', 2);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `discount_type` enum('percent','amount','','') NOT NULL,
  `min_order_value` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `quantity`) VALUES
(1, 'Sale10', 'Giảm 10%', 10.00, 'percent', 50000.00, '2025-12-01', '2025-12-17', 100);

-- --------------------------------------------------------

--
-- Table structure for table `coupon_users`
--

CREATE TABLE `coupon_users` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `used_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupon_users`
--

INSERT INTO `coupon_users` (`id`, `coupon_id`, `user_id`, `used_at`) VALUES
(1, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `image_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`image_id`, `book_id`, `url`) VALUES
(1, 1, 'images\\matbiec.jpg\r\n'),
(2, 2, 'images\\rungnauy.jpg\r\n'),
(3, 6, 'images\\laohac.jpg'),
(4, 7, 'images\\tatden.jpg'),
(5, 3, 'images\\conan.jpg'),
(6, 4, 'images\\thanhguomdietquy.jpg'),
(7, 5, 'images\\hocviensieuanhhung.jpg'),
(8, 8, 'images\\chipheo.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `is_read`, `created_at`, `order_id`) VALUES
(19, 'Đơn hàng mới', 'Có đơn hàng mới #30 vừa được khách đặt', 'new_order', 1, '2025-12-13 23:24:19', 30),
(20, 'Yêu cầu trả hàng mới', 'Đơn hàng #30 vừa có yêu cầu trả hàng từ khách #1', 'return_request', 1, '2025-12-13 23:25:20', 30),
(21, 'Đơn hàng mới', 'Có đơn hàng mới #37 vừa được khách đặt', 'new_order', 1, '2025-12-14 00:00:02', 37),
(22, 'Đơn hàng mới', 'Có đơn hàng mới #39 vừa được khách đặt', 'new_order', 0, '2025-12-14 00:20:42', 39),
(23, 'Yêu cầu hủy đơn hàng', 'Người dùng #1 gửi yêu cầu hủy đơn hàng #40', 'cancel_request', 0, '2025-12-14 00:22:00', 40),
(24, 'Yêu cầu trả hàng mới', 'Đơn hàng #38 vừa có yêu cầu trả hàng từ khách #1', 'return_request', 0, '2025-12-14 00:22:44', 38),
(25, 'Yêu cầu trả hàng mới', 'Đơn hàng #38 vừa có yêu cầu trả hàng từ khách #1', 'return_request', 0, '2025-12-14 00:27:10', 38),
(26, 'Yêu cầu trả hàng mới', 'Đơn hàng #38 vừa có yêu cầu trả hàng từ khách #1', 'return_request', 0, '2025-12-14 00:32:09', 38),
(27, 'Đơn hàng mới', 'Có đơn hàng mới #41 vừa được khách đặt', 'new_order', 0, '2025-12-14 00:33:25', 41),
(28, 'Yêu cầu trả hàng mới', 'Đơn hàng #38 vừa có yêu cầu trả hàng từ khách #1', 'return_request', 0, '2025-12-14 00:33:53', 38),
(29, 'Hủy yêu cầu trả hàng', 'Khách hàng #1 đã hủy yêu cầu trả hàng cho đơn #38', 'return_cancel', 0, '2025-12-14 00:34:18', 38);

-- --------------------------------------------------------

--
-- Table structure for table `notifications_customer`
--

CREATE TABLE `notifications_customer` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications_customer`
--

INSERT INTO `notifications_customer` (`notification_id`, `user_id`, `message`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(13, 1, 'Yêu cầu trả hàng cho đơn #19 đã được phê duyệt. Vui lòng làm theo hướng dẫn để gửi hàng.', 'return_approved', 19, 0, '2025-12-13 17:56:03'),
(14, 1, 'Yêu cầu trả hàng cho đơn #19 đã bị từ chối.', 'return_rejected', 19, 0, '2025-12-13 18:11:22'),
(15, 1, 'Khách hàng vừa đặt đơn hàng mới #20', 'new_order', 20, 0, '2025-12-13 22:17:25'),
(16, 1, 'Khách hàng vừa đặt đơn hàng mới #23', 'new_order', 23, 0, '2025-12-13 22:26:58'),
(17, 1, 'Khách hàng vừa đặt đơn hàng mới #26', 'new_order', 26, 0, '2025-12-13 22:46:01'),
(18, 1, 'Khách hàng vừa đặt đơn hàng mới #29', 'new_order', 29, 0, '2025-12-13 22:50:35'),
(19, 1, 'Khách hàng vừa đặt đơn hàng mới #30', 'new_order', 30, 0, '2025-12-13 23:24:19'),
(20, 1, 'Yêu cầu trả hàng cho đơn #30 đã được phê duyệt. Vui lòng làm theo hướng dẫn để gửi hàng.', 'return_approved', 30, 0, '2025-12-13 23:25:39'),
(21, 1, 'Khách hàng vừa đặt đơn hàng mới #37', 'new_order', 37, 0, '2025-12-14 00:00:02'),
(22, 1, 'Khách hàng vừa đặt đơn hàng mới #39', 'new_order', 39, 0, '2025-12-14 00:20:42'),
(23, 1, 'Yêu cầu trả hàng cho đơn #38 đã được phê duyệt. Vui lòng làm theo hướng dẫn để gửi hàng.', 'return_approved', 38, 0, '2025-12-14 00:27:48'),
(24, 1, 'Khách hàng vừa đặt đơn hàng mới #41', 'new_order', 41, 0, '2025-12-14 00:33:25');

-- --------------------------------------------------------

--
-- Table structure for table `online_queue`
--

CREATE TABLE `online_queue` (
  `queue_id` int(11) NOT NULL,
  `trans_code` varchar(100) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `expire_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `online_queue`
--

INSERT INTO `online_queue` (`queue_id`, `trans_code`, `payload`, `user_id`, `amount`, `expire_at`, `status`) VALUES
(22, 'DH1765620359505', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"7\",\"title\":\"Tắt đèn\",\"price\":\"45000.00\",\"qty\":1,\"image\":\"images\\\\tatden.jpg\"}],\"subtotal\":45000,\"shipping\":15000,\"discount\":0,\"total\":60000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 60000.00, '2025-12-13 17:20:59', 'done'),
(23, 'DH1765620477664', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"7\",\"title\":\"Tắt đèn\",\"price\":\"45000.00\",\"qty\":1,\"image\":\"images\\\\tatden.jpg\"}],\"subtotal\":45000,\"shipping\":15000,\"discount\":0,\"total\":60000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 60000.00, '2025-12-13 17:22:57', 'done'),
(24, 'DH1765639059241', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"8\",\"title\":\"Chí Phèo\",\"price\":\"1000.00\",\"qty\":1,\"image\":\"images\\\\chipheo.jpg\"}],\"subtotal\":1000,\"shipping\":15000,\"discount\":0,\"total\":16000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 16000.00, '2025-12-13 22:32:39', 'done'),
(25, 'DH1765639158569', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"8\",\"title\":\"Chí Phèo\",\"price\":\"1000.00\",\"qty\":1,\"image\":\"images\\\\chipheo.jpg\"}],\"subtotal\":1000,\"shipping\":15000,\"discount\":0,\"total\":16000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 16000.00, '2025-12-13 22:34:18', 'done'),
(26, 'DH1765639610722', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"8\",\"title\":\"Chí Phèo\",\"price\":\"1000.00\",\"qty\":1,\"image\":\"images\\\\chipheo.jpg\"}],\"subtotal\":1000,\"shipping\":15000,\"total\":16000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 16000.00, '2025-12-13 22:41:50', 'pending'),
(27, 'DH1765639693377', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"8\",\"title\":\"Chí Phèo\",\"price\":\"1000.00\",\"qty\":1,\"image\":\"images\\\\chipheo.jpg\"}],\"subtotal\":1000,\"shipping\":15000,\"total\":16000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 16000.00, '2025-12-13 22:43:13', 'pending'),
(28, 'DH1765640266162', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"8\",\"title\":\"Chí Phèo\",\"price\":\"1000.00\",\"qty\":1,\"image\":\"images\\\\chipheo.jpg\"}],\"subtotal\":1000,\"shipping\":15000,\"total\":16000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 16000.00, '2025-12-13 22:52:46', 'done'),
(29, 'DH1765640548800', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"6\",\"title\":\"Lão Hạc\",\"price\":\"55000.00\",\"qty\":1,\"image\":\"images\\\\laohac.jpg\"}],\"subtotal\":55000,\"shipping\":15000,\"total\":70000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 70000.00, '2025-12-13 22:57:28', 'done'),
(30, 'DH1765640968962', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"7\",\"title\":\"Tắt đèn\",\"price\":\"45000.00\",\"qty\":1,\"image\":\"images\\\\tatden.jpg\"}],\"subtotal\":45000,\"shipping\":15000,\"total\":60000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 60000.00, '2025-12-13 23:04:28', 'done'),
(31, 'DH1765641017828', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"3\",\"title\":\"Thám tử lừng danh Conan\",\"price\":\"40000.00\",\"qty\":1,\"image\":\"images\\\\conan.jpg\"}],\"subtotal\":40000,\"shipping\":15000,\"total\":55000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 55000.00, '2025-12-13 23:05:17', 'done'),
(32, 'DH1765643600457', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"5\",\"title\":\"Học viện siêu anh hùng\",\"price\":\"270000.00\",\"qty\":1,\"image\":\"images\\\\hocviensieuanhhung.jpg\"}],\"subtotal\":270000,\"shipping\":15000,\"total\":285000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 285000.00, '2025-12-13 23:48:20', 'done'),
(33, 'DH1765643925538', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"3\",\"title\":\"Thám tử lừng danh Conan\",\"price\":\"40000.00\",\"qty\":1,\"image\":\"images\\\\conan.jpg\"}],\"subtotal\":40000,\"shipping\":15000,\"total\":55000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 55000.00, '2025-12-13 23:53:45', 'done'),
(34, 'DH1765644767126', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"qty\":\"1\",\"book_id\":\"2\",\"title\":\"Rừng Nauy\",\"price\":\"95000.00\",\"image\":\"images\\\\rungnauy.jpg\\r\\n\"}],\"subtotal\":95000,\"shipping\":15000,\"total\":110000,\"notes\":\"\",\"is_buy_now\":false,\"cart_id\":\"1\"}', 1, 110000.00, '2025-12-14 00:07:47', 'done'),
(35, 'DH1765644947641', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"3\",\"title\":\"Thám tử lừng danh Conan\",\"price\":\"40000.00\",\"qty\":1,\"image\":\"images\\\\conan.jpg\"}],\"subtotal\":40000,\"shipping\":15000,\"total\":55000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 55000.00, '2025-12-14 00:10:47', 'done'),
(36, 'DH1765645080102', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"2\",\"title\":\"Rừng Nauy\",\"price\":\"95000.00\",\"qty\":1,\"image\":\"images\\\\rungnauy.jpg\\r\\n\"}],\"subtotal\":95000,\"shipping\":15000,\"total\":110000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 110000.00, '2025-12-14 00:13:00', 'done'),
(37, 'DH1765645167785', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"7\",\"title\":\"Tắt đèn\",\"price\":\"45000.00\",\"qty\":1,\"image\":\"images\\\\tatden.jpg\"}],\"subtotal\":45000,\"shipping\":15000,\"total\":60000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 60000.00, '2025-12-14 00:14:27', 'done'),
(38, 'DH1765645974315', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"8\",\"title\":\"Chí Phèo\",\"price\":\"1000.00\",\"qty\":1,\"image\":\"images\\\\chipheo.jpg\"}],\"subtotal\":1000,\"shipping\":15000,\"total\":16000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 16000.00, '2025-12-14 00:27:54', 'done'),
(39, 'DH1765646477347', '{\"user_id\":1,\"address_id\":1,\"items\":[{\"book_id\":\"6\",\"title\":\"Lão Hạc\",\"price\":\"55000.00\",\"qty\":1,\"image\":\"images\\\\laohac.jpg\"}],\"subtotal\":55000,\"shipping\":15000,\"total\":70000,\"notes\":\"\",\"is_buy_now\":true,\"cart_id\":null}', 1, 70000.00, '2025-12-14 00:36:17', 'done');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('COD','online','','') NOT NULL,
  `payment_status` enum('pending','paid','failed','') NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','shipping','completed','canceled','req_cancel','reject_cancel','req_return','accept_return','reject_return') NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `address_id`, `order_date`, `total_amount`, `payment_method`, `payment_status`, `shipping_fee`, `order_status`, `previous_status`) VALUES
(38, 1, 1, '2025-12-14 00:12:56', 1000.00, 'online', 'paid', 15000.00, 'completed', NULL),
(39, 1, 1, '2025-12-14 00:20:42', 45000.00, 'COD', 'pending', 15000.00, 'pending', NULL),
(40, 1, 1, '2025-12-14 00:21:19', 55000.00, 'online', 'paid', 15000.00, 'req_cancel', 'pending'),
(41, 1, 1, '2025-12-14 00:33:25', 1000.00, 'COD', 'pending', 15000.00, 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_order` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `quantity`, `price_at_order`) VALUES
(1, 1, 1, 1, 70000.00),
(2, 1, 2, 2, 95000.00),
(20, 18, 7, 1, 45000.00),
(21, 19, 7, 1, 45000.00),
(22, 20, 8, 1, 1000.00),
(23, 21, 8, 1, 1000.00),
(24, 22, 8, 1, 1000.00),
(25, 23, 8, 1, 1000.00),
(26, 24, 8, 1, 1000.00),
(27, 25, 6, 1, 55000.00),
(28, 26, 7, 1, 45000.00),
(29, 27, 7, 1, 45000.00),
(30, 28, 3, 1, 40000.00),
(31, 29, 6, 1, 55000.00),
(32, 30, 8, 2, 1000.00),
(33, 31, 5, 1, 270000.00),
(34, 32, 3, 1, 40000.00),
(35, 33, 2, 1, 95000.00),
(36, 34, 3, 1, 40000.00),
(37, 35, 2, 1, 95000.00),
(38, 36, 7, 1, 45000.00),
(39, 37, 4, 1, 180000.00),
(40, 38, 8, 1, 1000.00),
(41, 39, 7, 1, 45000.00),
(42, 40, 6, 1, 55000.00),
(43, 41, 8, 1, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('COD','online','','') NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `status` enum('pending','success','failed','') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `amount`, `method`, `transaction_code`, `status`, `created_at`) VALUES
(1, 1, 260000.00, 'COD', 'TNX_12012025', 'pending', '2025-12-01 15:52:43');

-- --------------------------------------------------------

--
-- Table structure for table `publishers`
--

CREATE TABLE `publishers` (
  `publisher_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publishers`
--

INSERT INTO `publishers` (`publisher_id`, `name`) VALUES
(1, 'NXB Kim Đồng'),
(2, 'NXB Trẻ'),
(3, 'NXB Lao Động'),
(4, 'NXB Văn Học');

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `description` text NOT NULL,
  `images` text NOT NULL,
  `status` enum('pending','approved','rejected','reufunded') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_requests`
--

INSERT INTO `return_requests` (`id`, `order_id`, `user_id`, `reason`, `description`, `images`, `status`, `created_at`) VALUES
(6, 1, 1, '0', 'aaaaaaaaaaaaaaaa', '', 'rejected', '2025-12-13 17:05:31'),
(7, 1, 1, '0', 'aaaaaaaaaaaaaaaaaaa', '', 'rejected', '2025-12-13 17:05:31'),
(8, 1, 1, '0', 'aaaaaaaaaaaaaa', '', 'rejected', '2025-12-13 17:05:31'),
(9, 1, 1, '0', 'aaaaaaaaaaaaaaaaaaa', '', 'pending', '2025-12-13 17:54:06'),
(10, 19, 1, '0', 'aaaaaaaaaaa', '', 'rejected', '2025-12-13 18:11:22'),
(11, 19, 1, '0', 'aaaaaaaaaaaaaaaaaaaa', '', 'rejected', '2025-12-13 18:11:22'),
(12, 30, 1, '0', 'aaaaaaaaaa', '', '', '2025-12-13 23:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','approved','hidden') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `book_id`, `rating`, `comment`, `created_at`, `status`) VALUES
(1, 1, 2, 4, 'Ý nghĩa và truyền cảm hứng !', '2025-12-10 20:23:49', 'approved'),
(2, 1, 1, 5, 'Sách rất hay và ý nghĩa', '2025-12-10 19:45:26', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `shippers`
--

CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hotline` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shippers`
--

INSERT INTO `shippers` (`shipper_id`, `name`, `hotline`) VALUES
(1, 'Giao hàng nhanh', '19002021'),
(2, 'Giao hàng tiết kiệm', '18006092');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_tracking`
--

CREATE TABLE `shipping_tracking` (
  `track_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `shipper_id` int(11) NOT NULL,
  `status_detail` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_tracking`
--

INSERT INTO `shipping_tracking` (`track_id`, `order_id`, `shipper_id`, `status_detail`, `updated_at`) VALUES
(1, 1, 1, 'Đã tiếp nhận đơn hàng', '2025-12-01 15:56:21');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_history`
--

CREATE TABLE `transaction_history` (
  `trans_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `transaction_code` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_history`
--

INSERT INTO `transaction_history` (`trans_id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_code`, `created_at`) VALUES
(22, 18, 'ONLINE_VIETQR', 60000.00, 'success', 'DH1765620359505', '2025-12-13 17:05:59'),
(23, 19, 'ONLINE_VIETQR', 60000.00, 'success', 'DH1765620477664', '2025-12-13 17:07:57'),
(24, 21, 'ONLINE_VIETQR', 16000.00, 'success', 'DH1765639059241', '2025-12-13 22:17:39'),
(25, 22, 'ONLINE_VIETQR', 16000.00, 'success', 'DH1765639158569', '2025-12-13 22:19:18'),
(26, NULL, 'ONLINE_VIETQR', 16000.00, 'pending', 'DH1765639610722', '2025-12-13 22:26:50'),
(27, NULL, 'ONLINE_VIETQR', 16000.00, 'pending', 'DH1765639693377', '2025-12-13 22:28:13'),
(28, 24, 'ONLINE_VIETQR', 16000.00, 'success', 'DH1765640266162', '2025-12-13 22:37:46'),
(29, 25, 'ONLINE_VIETQR', 70000.00, 'success', 'DH1765640548800', '2025-12-13 22:42:28'),
(30, 27, 'ONLINE_VIETQR', 60000.00, 'success', 'DH1765640968962', '2025-12-13 22:49:28'),
(31, 28, 'ONLINE_VIETQR', 55000.00, 'success', 'DH1765641017828', '2025-12-13 22:50:17'),
(32, 31, 'ONLINE_VIETQR', 285000.00, 'success', 'DH1765643600457', '2025-12-13 23:33:20'),
(33, 32, 'ONLINE_VIETQR', 55000.00, 'success', 'DH1765643925538', '2025-12-13 23:38:45'),
(34, 33, 'ONLINE_VIETQR', 110000.00, 'success', 'DH1765644767126', '2025-12-13 23:52:47'),
(35, 34, 'ONLINE_VIETQR', 55000.00, 'success', 'DH1765644947641', '2025-12-13 23:55:47'),
(36, 35, 'ONLINE_VIETQR', 110000.00, 'success', 'DH1765645080102', '2025-12-13 23:58:00'),
(37, 36, 'ONLINE_VIETQR', 60000.00, 'success', 'DH1765645167785', '2025-12-13 23:59:27'),
(38, 38, 'ONLINE_VIETQR', 16000.00, 'success', 'DH1765645974315', '2025-12-14 00:12:54'),
(39, 40, 'ONLINE_VIETQR', 70000.00, 'success', 'DH1765646477347', '2025-12-14 00:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gender` enum('Nữ','Nam','Khác','') NOT NULL,
  `dob` date NOT NULL,
  `role` enum('customer','admin','','') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','banned','','') NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `gender`, `dob`, `role`, `created_at`, `status`, `reset_token`, `token_expiry`, `login_attempts`, `last_attempt`) VALUES
(1, 'Nguyễn Văn An', 'nguyenan@gmail.com', '$2y$10$C2c0l5r5P3lat8zb7MP9.eQ2pn62yW6EM/jletKD/h2GRklNWtsEe', '0901234567', 'Nam', '2015-06-23', 'customer', '2025-12-01 15:23:34', 'active', NULL, NULL, 0, NULL),
(2, 'Phạm Thanh Hồng', 'thanhhong@gmail.com', '$2y$10$0p2sevjruSJHJQfboTISFeTFg7ZWC0EvL1oj9QJcjaNegkkYQofW6', '0987654321', 'Nữ', '2015-10-12', 'admin', '2025-12-01 15:25:11', 'active', NULL, NULL, 0, NULL),
(4, 'Lê Phương Thảo', 'lephuongthao14072005@gmail.com', '$2y$10$.Yj/FYPtt21I.BU7GfXDlOLLdLt8FKEA/JMgGzIbL27sGjE7ihAEe', '', 'Nữ', '0000-00-00', 'customer', '2025-12-04 02:11:30', 'active', NULL, NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `book_authors`
--
ALTER TABLE `book_authors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`);

--
-- Indexes for table `coupon_users`
--
ALTER TABLE `coupon_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications_customer`
--
ALTER TABLE `notifications_customer`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `online_queue`
--
ALTER TABLE `online_queue`
  ADD PRIMARY KEY (`queue_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `publishers`
--
ALTER TABLE `publishers`
  ADD PRIMARY KEY (`publisher_id`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `shippers`
--
ALTER TABLE `shippers`
  ADD PRIMARY KEY (`shipper_id`);

--
-- Indexes for table `shipping_tracking`
--
ALTER TABLE `shipping_tracking`
  ADD PRIMARY KEY (`track_id`);

--
-- Indexes for table `transaction_history`
--
ALTER TABLE `transaction_history`
  ADD PRIMARY KEY (`trans_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `book_authors`
--
ALTER TABLE `book_authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupon_users`
--
ALTER TABLE `coupon_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `notifications_customer`
--
ALTER TABLE `notifications_customer`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `online_queue`
--
ALTER TABLE `online_queue`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `publishers`
--
ALTER TABLE `publishers`
  MODIFY `publisher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shippers`
--
ALTER TABLE `shippers`
  MODIFY `shipper_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipping_tracking`
--
ALTER TABLE `shipping_tracking`
  MODIFY `track_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaction_history`
--
ALTER TABLE `transaction_history`
  MODIFY `trans_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
