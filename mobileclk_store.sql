-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 24, 2026 lúc 06:33 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `mobileclk_store`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account`
--

CREATE TABLE `account` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `privilege` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `account`
--

INSERT INTO `account` (`id`, `username`, `password`, `email`, `privilege`) VALUES
(1, 'admin', '123', 'admin@gmail.com', 1),
(2, 'duykhanh', '123', 'duykhanh@gmail.com', 0),
(3, 'loc', '123', 'loc@gmail.com', 0),
(5, 'chinh', '123', 'chinh@gmail.com', 0),
(10, 'minh tran', '123', 'minhtran@gmail.com', 0),
(11, 'lannguyen', '123', 'lannguyen@gmail.com', 0),
(12, 'hungle', '123', 'hungle@gmail.com', 0),
(13, 'thupham', '123', 'thupham@gmail.com', 0),
(14, 'ducvo', '123', 'ducvo@gmail.com', 0),
(15, 'hoabui', '123', 'hoabui@gmail.com', 0),
(16, 'khanhngo', '123', 'khanhngo@gmail.com', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`id`, `item_id`, `user_id`, `quantity`) VALUES
(13, 7, 1, 1),
(14, 16, 1, 5),
(26, 3, 1, 1),
(28, 18, 1, 1),
(29, 10, 5, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category`
--

CREATE TABLE `category` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-box'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category`
--

INSERT INTO `category` (`id`, `slug`, `name`, `icon`) VALUES
(1, 'phone', 'Điện thoại', 'fas fa-mobile-alt'),
(2, 'headphone', 'Tai nghe', 'fas fa-headphones'),
(3, 'charger', 'Sạc & Cáp', 'fas fa-bolt'),
(4, 'case', 'Ốp lưng', 'fas fa-shield-alt'),
(5, 'powerbank', 'Pin dự phòng', 'fas fa-battery-full');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_value` int(11) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `min_order_value` int(11) NOT NULL DEFAULT 0,
  `max_discount` int(11) NOT NULL DEFAULT 0,
  `usage_limit` int(11) NOT NULL DEFAULT 0,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `min_user_orders` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_value`, `discount_type`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `valid_from`, `valid_until`, `status`, `min_user_orders`) VALUES
(1, 'VIPPRO', 30, 'percent', 5000000, 5000000, 50, 2, '2026-05-01 17:56:00', '2026-05-31 17:56:00', 1, 0),
(2, 'HAGIA500', 500000, 'fixed', 5000000, 0, 100, 0, '2026-05-01 18:17:00', '2026-05-31 18:17:00', 1, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `manufacturer`
--

CREATE TABLE `manufacturer` (
  `id` int(10) UNSIGNED NOT NULL,
  `brand` varchar(30) NOT NULL,
  `company` varchar(50) NOT NULL,
  `headquarter` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `manufacturer`
--

INSERT INTO `manufacturer` (`id`, `brand`, `company`, `headquarter`) VALUES
(1, 'Samsung', 'Samsung Electronics', 'South Korea'),
(2, 'Redmi', 'Xiaomi Corporation', 'China'),
(3, 'Apple', 'Apple Inc.', 'USA'),
(4, 'Oppo', 'OPPO Electronics', 'China');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `total_amount` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `shipping_address` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(15) NOT NULL DEFAULT '',
  `payment_method` varchar(50) DEFAULT 'cod',
  `discount_amount` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `status`, `total_amount`, `shipping_address`, `phone`, `payment_method`, `discount_amount`) VALUES
(21, 3, '2026-05-10 22:16:56', 'returned', 89000000.00, 'Chu Van An', '123456789', 'cod', 0),
(22, 3, '2026-05-15 13:07:25', 'delivered', 9510000.00, 'Chu Van An', '123456789', 'cod', 500000),
(23, 3, '2026-05-15 17:33:40', 'shipping', 3920000.00, 'Chu Van An', '123456789', 'cod', 0),
(24, 3, '2026-05-15 18:01:31', 'cancelled', 99999999.99, 'Chu Van An', '123456789', 'cod', 0),
(25, 3, '2026-05-17 09:21:02', 'pending', 22030000.00, 'Chu Van An', '123456789', 'cod', 5000000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_detail`
--

CREATE TABLE `order_detail` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` double(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_detail`
--

INSERT INTO `order_detail` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(26, 13, 7, 1, 3990000.00),
(27, 14, 11, 1, 6990000.00),
(28, 15, 16, 1, 2490000.00),
(29, 16, 17, 1, 790000.00),
(30, 17, 11, 1, 142000.00),
(31, 18, 12, 1, 122000.00),
(32, 19, 10, 1, 82000.00),
(33, 20, 11, 1, 142000.00),
(34, 10, 14, 1, 3990000.00),
(35, 11, 11, 1, 6990000.00),
(36, 12, 15, 1, 1190000.00),
(37, 13, 7, 1, 3990000.00),
(38, 14, 11, 1, 6990000.00),
(39, 15, 16, 1, 2490000.00),
(40, 16, 17, 1, 790000.00),
(41, 17, 11, 1, 142000.00),
(42, 18, 12, 1, 122000.00),
(43, 19, 10, 1, 82000.00),
(44, 20, 11, 1, 142000.00),
(45, 21, 10, 1, 15990000.00),
(46, 21, 12, 2, 15990000.00),
(47, 21, 6, 1, 40990000.00),
(48, 22, 14, 1, 9990000.00),
(49, 23, 20, 10, 390000.00),
(50, 24, 11, 8, 14990000.00),
(51, 24, 6, 1, 40990000.00),
(52, 25, 7, 1, 26990000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product`
--

CREATE TABLE `product` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` int(10) UNSIGNED NOT NULL,
  `price` double(10,2) UNSIGNED NOT NULL,
  `image` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 100,
  `category` varchar(30) NOT NULL DEFAULT 'phone'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product`
--

INSERT INTO `product` (`id`, `name`, `brand`, `price`, `image`, `description`, `stock`, `category`) VALUES
(1, 'iPhone 15 Pro Max 256GBb', 3, 29990000.00, './assets/products/33.png', 'San pham chinh hang.', 100, 'phone'),
(2, 'Samsung Galaxy S24 Ultra 5G', 1, 33990000.00, './assets/products/34.png', 'San pham chinh hang.', 100, 'phone'),
(3, 'Xiaomi 14 Pro 5G', 2, 24990000.00, './assets/products/35.png', 'San pham chinh hang.', 100, 'phone'),
(4, 'Oppo Find X7 Ultra', 4, 27990000.00, './assets/products/36.png', 'San pham chinh hang.', 100, 'phone'),
(5, 'iPhone 15 Plus 128GB', 3, 24500000.00, './assets/products/37.png', 'San pham chinh hang.', 100, 'phone'),
(6, 'Samsung Galaxy Z Fold5', 1, 40990000.00, './assets/products/38.png', 'San pham chinh hang.', 100, 'phone'),
(7, 'iPhone 14 Pro Max 256GB', 3, 26990000.00, './assets/products/39.png', 'San pham chinh hang.', 99, 'phone'),
(8, 'Redmi Note 13 Pro+', 2, 10990000.00, './assets/products/40.png', 'San pham chinh hang.', 100, 'phone'),
(9, 'Samsung Galaxy A55 5G', 1, 9990000.00, './assets/products/41.png', 'San pham chinh hang.', 100, 'phone'),
(10, 'iPhone 13 128GB', 3, 15990000.00, './assets/products/42.png', 'San pham chinh hang.', 100, 'phone'),
(11, 'Oppo Reno 11 Pro 5G', 4, 14990000.00, './assets/products/43.png', 'San pham chinh hang.', 100, 'phone'),
(12, 'Xiaomi 13T Pro', 2, 15990000.00, './assets/products/44.png', 'San pham chinh hang.', 100, 'phone'),
(13, 'Samsung Galaxy S23 FE', 1, 13990000.00, './assets/products/45.png', 'San pham chinh hang.', 100, 'phone'),
(14, 'iPhone 11 64GB', 3, 9990000.00, './assets/products/46.png', 'San pham chinh hang.', 99, 'phone'),
(15, 'Tai nghe AirPods Pro 2 MagSafe', 3, 5990000.00, './assets/products/17.png', 'Chống ồn siêu việt.', 50, 'headphone'),
(16, 'Tai nghe Samsung Galaxy Buds FE', 1, 1990000.00, './assets/products/15.png', 'Bass sâu, pin trâu.', 30, 'headphone'),
(17, 'Sạc nhanh 20W Apple Chính Hãng', 3, 550000.00, './assets/products/16.png', 'Sạc nhanh cho iPhone.', 60, 'charger'),
(18, 'Cáp sạc Samsung Type-C to Type-C', 1, 250000.00, './assets/products/14.png', 'Dây cáp sạc siêu nhanh 45W.', 40, 'charger'),
(19, 'Ốp lưng iPhone 15 Pro Max Clear Case', 3, 1200000.00, './assets/products/32.png', 'Trong suốt mỏng nhẹ.', 100, 'case'),
(20, 'Pin sạc dự phòng Xiaomi 10000mAh', 2, 390000.00, './assets/products/18.png', 'Sạc dự phòng chính hãng Xiaomi.', 50, 'powerbank');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variant`
--

CREATE TABLE `product_variant` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `ram` varchar(10) NOT NULL,
  `rom` varchar(10) NOT NULL,
  `price` double(10,2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `return_request`
--

CREATE TABLE `return_request` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `return_request`
--

INSERT INTO `return_request` (`id`, `order_id`, `user_id`, `reason`, `status`, `created_at`) VALUES
(2, 21, 3, 'sản phẩm lỗi', 'rejected', '2026-05-11 03:19:03'),
(3, 21, 3, 'alo', 'approved', '2026-05-11 03:19:37'),
(4, 23, 3, 'sản Phẩm lỗi', 'pending', '2026-05-15 22:35:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 4, 'Điện thoại chính hãng nguyên seal, dùng ngon lành.', '2026-04-21 12:13:03'),
(2, 1, 3, 4, 'Sản phẩm dùng rất mượt, cấu hình mạnh mẽ đáng đồng tiền bát gạo!', '2026-05-05 12:13:03'),
(3, 1, 4, 4, 'Thiết kế sang trọng, viền mỏng cầm rất chắc tay.', '2026-04-28 12:13:03'),
(4, 1, 5, 5, 'Chất lượng hiển thị tuyệt vời, hệ điều hành rất mượt.', '2026-04-25 12:13:03'),
(5, 2, 5, 5, 'Sản phẩm dùng rất mượt, cấu hình mạnh mẽ đáng đồng tiền bát gạo!', '2026-04-17 12:13:03'),
(6, 3, 2, 4, 'Màn hình sắc nét, chơi game không bị giật lag.', '2026-05-11 12:13:03'),
(7, 3, 4, 5, 'Thiết kế sang trọng, viền mỏng cầm rất chắc tay.', '2026-05-14 12:13:03'),
(8, 4, 2, 4, 'Điện thoại chính hãng nguyên seal, dùng ngon lành.', '2026-05-09 12:13:03'),
(9, 4, 3, 5, 'Sản phẩm dùng rất mượt, cấu hình mạnh mẽ đáng đồng tiền bát gạo!', '2026-04-20 12:13:03'),
(10, 4, 4, 4, 'Sản phẩm dùng rất mượt, cấu hình mạnh mẽ đáng đồng tiền bát gạo!', '2026-04-24 12:13:03'),
(11, 4, 5, 5, 'Pin trâu, sạc nhanh, camera chụp ảnh rất đẹp.', '2026-05-13 12:13:03'),
(12, 5, 2, 4, 'Chất lượng hiển thị tuyệt vời, hệ điều hành rất mượt.', '2026-04-27 12:13:03'),
(13, 5, 3, 5, 'Pin trâu, sạc nhanh, camera chụp ảnh rất đẹp.', '2026-05-06 12:13:03'),
(14, 5, 4, 5, 'Màn hình sắc nét, chơi game không bị giật lag.', '2026-04-15 12:13:03'),
(15, 5, 5, 5, 'Chất lượng hiển thị tuyệt vời, hệ điều hành rất mượt.', '2026-05-14 12:13:03'),
(16, 6, 3, 4, 'Điện thoại chính hãng nguyên seal, dùng ngon lành.', '2026-05-06 12:13:03'),
(17, 7, 2, 4, 'Màn hình sắc nét, chơi game không bị giật lag.', '2026-04-26 12:13:03'),
(18, 7, 3, 5, 'Pin trâu, sạc nhanh, camera chụp ảnh rất đẹp.', '2026-05-06 12:13:03'),
(19, 7, 4, 4, 'Thiết kế sang trọng, viền mỏng cầm rất chắc tay.', '2026-05-03 12:13:03'),
(20, 7, 5, 4, 'Điện thoại chính hãng nguyên seal, dùng ngon lành.', '2026-05-13 12:13:03'),
(21, 8, 5, 4, 'Màn hình sắc nét, chơi game không bị giật lag.', '2026-04-27 12:13:03'),
(22, 9, 2, 5, 'Thiết kế sang trọng, viền mỏng cầm rất chắc tay.', '2026-04-28 12:13:03'),
(23, 9, 3, 5, 'Chất lượng hiển thị tuyệt vời, hệ điều hành rất mượt.', '2026-04-26 12:13:03'),
(24, 9, 4, 5, 'Pin trâu, sạc nhanh, camera chụp ảnh rất đẹp.', '2026-05-03 12:13:03'),
(25, 10, 2, 4, 'Sản phẩm dùng rất mượt, cấu hình mạnh mẽ đáng đồng tiền bát gạo!', '2026-04-28 12:13:03'),
(26, 10, 3, 4, 'Sản phẩm dùng rất mượt, cấu hình mạnh mẽ đáng đồng tiền bát gạo!', '2026-05-13 12:13:03'),
(27, 10, 4, 5, 'Màn hình sắc nét, chơi game không bị giật lag.', '2026-05-03 12:13:03'),
(28, 11, 2, 4, 'Màn hình sắc nét, chơi game không bị giật lag.', '2026-04-19 12:13:03'),
(29, 11, 3, 4, 'Chất lượng hiển thị tuyệt vời, hệ điều hành rất mượt.', '2026-05-07 12:13:03'),
(30, 11, 4, 5, 'Pin trâu, sạc nhanh, camera chụp ảnh rất đẹp.', '2026-04-22 12:13:03'),
(31, 11, 5, 5, 'Pin trâu, sạc nhanh, camera chụp ảnh rất đẹp.', '2026-04-20 12:13:03'),
(32, 12, 4, 4, 'Màn hình sắc nét, chơi game không bị giật lag.', '2026-04-17 12:13:03'),
(33, 12, 5, 5, 'Điện thoại chính hãng nguyên seal, dùng ngon lành.', '2026-05-14 12:13:03'),
(34, 13, 3, 5, 'Điện thoại chính hãng nguyên seal, dùng ngon lành.', '2026-04-24 12:13:03'),
(35, 13, 4, 5, 'Điện thoại chính hãng nguyên seal, dùng ngon lành.', '2026-05-13 12:13:03'),
(36, 13, 5, 5, 'Pin trâu, sạc nhanh, camera chụp ảnh rất đẹp.', '2026-04-19 12:13:03'),
(37, 14, 2, 4, 'Sản phẩm dùng rất mượt, cấu hình mạnh mẽ đáng đồng tiền bát gạo!', '2026-05-03 12:13:03'),
(38, 15, 3, 4, 'Âm thanh hay, bass đập cực đã.', '2026-05-08 12:13:03'),
(39, 15, 5, 4, 'Mic thoại rõ ràng, mua để học online hay họp đều ok.', '2026-04-15 12:13:03'),
(40, 16, 2, 5, 'Kết nối bluetooth nhanh và ổn định không bị rớt.', '2026-05-10 12:13:03'),
(41, 16, 3, 4, 'Chống ồn tốt, đeo lâu không bị đau tai.', '2026-04-30 12:13:03'),
(42, 16, 4, 5, 'Pin dùng được rất lâu, thiết kế nhỏ gọn xinh xắn.', '2026-04-22 12:13:03'),
(43, 16, 5, 4, 'Kết nối bluetooth nhanh và ổn định không bị rớt.', '2026-05-11 12:13:03'),
(44, 17, 3, 4, 'Hàng chuẩn hãng, cắm vào nhận sạc nhanh liền.', '2026-04-30 12:13:03'),
(45, 18, 3, 5, 'Hàng chuẩn hãng, cắm vào nhận sạc nhanh liền.', '2026-04-22 12:13:03'),
(46, 18, 5, 4, 'Sạc lên pin cực nhanh, máy không bị nóng.', '2026-05-09 12:13:03'),
(47, 19, 2, 5, 'Chống trầy xước ổn, không bị ố vàng sau một thời gian.', '2026-04-16 12:13:03'),
(48, 19, 3, 5, 'Ốp lưng ôm sát máy, chất liệu cầm cực thích.', '2026-05-06 12:13:03'),
(49, 20, 2, 4, 'Có sạc nhanh rất tiện, lõi pin xài bền bỉ.', '2026-04-29 12:13:03'),
(50, 20, 5, 4, 'Có sạc nhanh rất tiện, lõi pin xài bền bỉ.', '2026-05-07 12:13:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(30) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `avatar` varchar(100) DEFAULT NULL,
  `city` varchar(30) NOT NULL DEFAULT '',
  `gender` tinyint(3) NOT NULL DEFAULT 0,
  `address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user`
--

INSERT INTO `user` (`id`, `fullname`, `phone`, `avatar`, `city`, `gender`, `address`) VALUES
(1, 'admin', '123456789', NULL, '', 0, ''),
(2, 'duykhanh', '123456789', NULL, '', 0, 'abc/xyz'),
(3, 'loc', '123456789', 'avatar_3_1777412529.png', '', 0, 'Chu Van An'),
(4, 'loc dep zai', '123456789', NULL, '', 0, '789 cu van an'),
(5, 'chinh', '', NULL, '', 0, '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `manufacturer`
--
ALTER TABLE `manufacturer`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_ibfk_1` (`brand`);

--
-- Chỉ mục cho bảng `product_variant`
--
ALTER TABLE `product_variant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_variant_product` (`product_id`);

--
-- Chỉ mục cho bảng `return_request`
--
ALTER TABLE `return_request`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `account`
--
ALTER TABLE `account`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `category`
--
ALTER TABLE `category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `manufacturer`
--
ALTER TABLE `manufacturer`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT cho bảng `product`
--
ALTER TABLE `product`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT cho bảng `product_variant`
--
ALTER TABLE `product_variant`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `return_request`
--
ALTER TABLE `return_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
