-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Jun 2025 pada 23.20
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emas`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `appointment_date`, `appointment_time`, `note`, `status`, `created_at`) VALUES
(4, 1, '2025-06-25', '15:00:00', 'Mau Konsultadi Perihal Pembuatan Perhiasan Custom', 'pending', '2025-06-08 04:55:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Kalung'),
(2, 'Cincin'),
(4, 'Anting-anting'),
(5, 'Gelang'),
(6, 'Bros'),
(7, 'Liontin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `custom_orders`
--

CREATE TABLE `custom_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reference_image` varchar(255) DEFAULT NULL,
  `status` enum('submitted','in_progress','completed','cancelled') DEFAULT 'submitted',
  `estimated_price` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `custom_orders`
--

INSERT INTO `custom_orders` (`id`, `user_id`, `description`, `reference_image`, `status`, `estimated_price`, `created_at`) VALUES
(3, 1, 'Jenis: 6\nBahan: emas_kuning\nKadar: \nUkuran: 2,5 cm\nUkiran: ', 'bros bunga.jpeg', 'in_progress', 100000.00, '2025-06-08 04:57:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `gold`
--

CREATE TABLE `gold` (
  `id_gold` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price_per_gram` decimal(15,2) NOT NULL,
  `purity` decimal(5,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gold`
--

INSERT INTO `gold` (`id_gold`, `type`, `price_per_gram`, `purity`, `description`, `last_updated`) VALUES
(1, '24K', 950000.00, 99.99, 'Pure gold, 24 karats', '2025-05-26 02:55:30'),
(2, '22K', 870000.00, 91.67, 'Gold with 22 parts gold and 2 parts other metals', '2025-05-26 02:55:30'),
(3, '18K', 720000.00, 75.00, 'Gold with 18 parts gold and 6 parts other metals', '2025-05-26 02:55:30'),
(4, '14K', 560000.00, 58.33, 'Gold with 14 parts gold and 10 parts other metals', '2025-05-26 02:55:30'),
(5, '10K', 400000.00, 41.67, 'Gold with 10 parts gold and 14 parts other metals', '2025-05-26 02:55:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `gold_transactions`
--

CREATE TABLE `gold_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('buy','sell') DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `price_per_gram` decimal(15,2) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `created_at`, `proof`) VALUES
(1, 1, 46000000.00, 'paid', '2025-06-08 11:27:57', '1749382077_photo_2025-06-02_17-37-06.jpg'),
(2, 1, 40500000.00, 'paid', '2025-06-08 11:33:00', '1749382380_ihidh.png'),
(3, 1, 500000.00, 'pending', '2025-06-08 11:50:27', '1749383427_ahahahahumormeme.jpg'),
(4, 1, 50300000.00, 'cancelled', '2025-06-08 16:19:38', '1749399578_memebinenalpha.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 9, 1, 6000000.00),
(2, 1, 7, 1, 40000000.00),
(3, 2, 10, 1, 500000.00),
(4, 2, 7, 1, 40000000.00),
(5, 3, 8, 1, 500000.00),
(6, 4, 3, 1, 300000.00),
(7, 4, 5, 1, 50000000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(50) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `material` varchar(255) NOT NULL DEFAULT '',
  `stock` int(11) NOT NULL DEFAULT 0,
  `price` decimal(15,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category_id`, `weight`, `material`, `stock`, `price`, `image`, `created_at`) VALUES
(2, 'Kalung Nabi Adam', 'Kalung Nabi Adam Yang Sangat Mewah', 1, 100.00, 'Emas 24 Karat', 10, 10000000.00, '1747565879_perhiasan.jpg', '2025-05-18 10:57:59'),
(3, 'test', 'ASOAHA', 1, 1.00, 'Perak', 2, 300000.00, '1747566078_perhiasan.jpg', '2025-05-18 11:01:18'),
(4, 'Cincin Squiliam', 'Cincin Squiliam Yang Sangat Cantik', 2, 5.00, 'Emas 17 Karat', 10, 400000.00, '1747578819_cincin.jpeg', '2025-05-18 14:33:39'),
(5, 'Cincin Nabi Adam', 'Cincin Yang Sangat Legendaris', 2, 20.00, 'Emas 24 Karat', 2, 50000000.00, '1747580141_cincin.jpeg', '2025-05-18 14:55:41'),
(6, 'Anting-anting Ayu Ting Ting', 'Anting-anting Bekas Ayu Ting Ting', 4, 1.00, 'Emas 18 Karat', 10, 600000.00, '1747580876_anting.jpeg', '2025-05-18 15:07:56'),
(7, 'Gelang Emas Hollow', 'Gelang Emas Hollow bla bla bla bla', 5, 20.00, 'Emas 24 Karat', 1, 40000000.00, '1747581118_gelang emas hollow.jpg', '2025-05-18 15:11:58'),
(8, 'Bros Bunga', 'Bros Bunga Bunga', 6, 1.00, 'Emas 10 Karat', 20, 500000.00, '1747581253_bros bunga.jpeg', '2025-05-18 15:14:13'),
(9, 'liontin Cute', 'Liontin', 7, 1.00, 'Emas 24 Karat', 1, 6000000.00, '1747581519_liontin.jpg', '2025-05-18 15:18:39'),
(10, 'Gelang Galing', 'Gelang Galing', 5, 10.00, 'Perak', 20, 500000.00, '1747581619_gelang emas hollow.jpg', '2025-05-18 15:20:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `product_id`, `quantity`, `total_price`, `status`, `created_at`, `proof`) VALUES
(1, 1, 9, 1, 6000000.00, 'pending', '2025-06-08 06:15:27', NULL),
(2, 1, 9, 1, 6000000.00, 'pending', '2025-06-08 06:17:12', NULL),
(3, 1, 8, 1, 500000.00, 'cancelled', '2025-06-08 06:19:53', NULL),
(4, 1, 7, 1, 40000000.00, 'pending', '2025-06-08 06:19:53', NULL),
(5, 1, 10, 1, 500000.00, 'pending', '2025-06-08 06:25:32', NULL),
(6, 1, 9, 1, 6000000.00, 'paid', '2025-06-08 07:24:43', '1749367483_photo_2025-06-02_17-37-06.jpg'),
(7, 1, 4, 1, 400000.00, 'pending', '2025-06-08 09:43:18', '1749375798_nftsomniascary.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(1, 'Aril Ponco Nugroho', 'arilponconugroho@gmail.com', '$2y$10$IS0ew3rUehZgmieDeAHkTuXTtkTLaNLB2Hh9TLSakNProNZr2Ffjq', '0895619917517', 'customer', '2025-05-23 13:51:23'),
(3, 'admin', 'admin@gmail.com', '$2y$10$mSwseMNBI4T0E4iBsRkeEeBTuZ8yy1dwJt3Pr9A0m/7oWJ.PePGFq', NULL, 'admin', '2025-05-23 14:56:56');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `gold`
--
ALTER TABLE `gold`
  ADD PRIMARY KEY (`id_gold`);

--
-- Indeks untuk tabel `gold_transactions`
--
ALTER TABLE `gold_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `custom_orders`
--
ALTER TABLE `custom_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `gold`
--
ALTER TABLE `gold`
  MODIFY `id_gold` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `gold_transactions`
--
ALTER TABLE `gold_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD CONSTRAINT `custom_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `gold_transactions`
--
ALTER TABLE `gold_transactions`
  ADD CONSTRAINT `gold_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
