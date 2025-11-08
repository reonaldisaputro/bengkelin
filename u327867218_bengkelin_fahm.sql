-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 02, 2025 at 09:20 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u327867218_bengkelin_fahm`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@gmail.com', NULL, '$2y$10$t1AUGglaPSWfScATAutDzOI8vjAZWvVeaviI6H098Vj55kEp5BJ1y', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bengkels`
--

CREATE TABLE `bengkels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemilik_id` bigint(20) UNSIGNED NOT NULL,
  `specialist_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `alamat` longtext NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kecamatan_id` bigint(20) UNSIGNED NOT NULL,
  `kelurahan_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bengkels`
--

INSERT INTO `bengkels` (`id`, `pemilik_id`, `specialist_id`, `name`, `image`, `description`, `alamat`, `latitude`, `longitude`, `created_at`, `updated_at`, `kecamatan_id`, `kelurahan_id`) VALUES
(1, 2, NULL, 'Bengkel Ngawi', '1742284047.jpg', 'ini deskripsi', 'jawa', -6.20150000, 106.81700000, '2025-03-18 14:47:27', '2025-03-18 14:47:27', 2, 8),
(2, 3, NULL, 'bengkel reo', '1752291266.png', 'bengkel reo', 'pamulang', -6.34376920, 106.67314230, '2025-07-12 10:34:26', '2025-08-09 10:52:28', 3, 15),
(3, 7, NULL, 'cihuy garage', '1753201720.png', 'spesialis mobil berat', 'pamulang', NULL, NULL, '2025-07-22 23:28:40', '2025-07-22 23:28:40', 3, 17),
(5, 12, NULL, 'Bengkel Aprit', '1754063671.jpg', 'rawr', 'pamulang 321', NULL, NULL, '2025-08-01 22:54:31', '2025-08-01 22:54:31', 3, 17);

-- --------------------------------------------------------

--
-- Table structure for table `bengkel_carts`
--

CREATE TABLE `bengkel_carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `layanan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bengkel_specialist`
--

CREATE TABLE `bengkel_specialist` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `specialist_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bengkel_specialist`
--

INSERT INTO `bengkel_specialist` (`id`, `bengkel_id`, `specialist_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 4, NULL, NULL),
(3, 2, 1, NULL, NULL),
(4, 2, 2, NULL, NULL),
(5, 2, 4, NULL, NULL),
(6, 2, 5, NULL, NULL),
(7, 2, 7, NULL, NULL),
(8, 2, 8, NULL, NULL),
(9, 2, 10, NULL, NULL),
(10, 3, 1, NULL, NULL),
(11, 3, 2, NULL, NULL),
(12, 3, 4, NULL, NULL),
(13, 3, 5, NULL, NULL),
(14, 3, 6, NULL, NULL),
(15, 3, 7, NULL, NULL),
(16, 3, 8, NULL, NULL),
(17, 3, 10, NULL, NULL),
(19, 5, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `waktu_booking` time NOT NULL,
  `tanggal_booking` date NOT NULL,
  `brand` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `plat` varchar(255) NOT NULL,
  `tahun_pembuatan` varchar(255) NOT NULL,
  `kilometer` int(11) NOT NULL,
  `transmisi` enum('Manual','Matic') NOT NULL,
  `booking_status` enum('Pending','Diterima','Ditolak','Selesai') NOT NULL,
  `catatan_tambahan` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `bengkel_id`, `waktu_booking`, `tanggal_booking`, `brand`, `model`, `plat`, `tahun_pembuatan`, `kilometer`, `transmisi`, `booking_status`, `catatan_tambahan`, `created_at`, `updated_at`) VALUES
(1, 3, 2, '17:41:00', '2025-08-02', 'Toyota', 'Avanza', 'B 1234 XYZ', '2020', 50000, 'Manual', 'Pending', NULL, '2025-08-02 17:40:37', '2025-08-02 17:40:37'),
(2, 3, 2, '10:00:00', '2025-08-04', 'BMW', 'BMW XM', 'B 6747ZET', '2025', 5000, 'Matic', 'Diterima', 'Baru saya pakai drag race semalem tapi ketika di top speed mesin mobil langsung mati dan berasap mobil tidak mau menyala lagi', '2025-08-02 17:55:16', '2025-08-02 17:56:42'),
(3, 3, 2, '19:11:00', '2025-08-03', 'honda', 'brio', 'a3212s', '2020', 3000, 'Manual', 'Diterima', 'testing ajaa yaa', '2025-08-02 18:11:58', '2025-08-02 18:15:12'),
(4, 3, 2, '17:41:00', '2025-08-03', 'Toyota', 'Avanza', 'B 1234 XYZ', '2020', 50000, 'Manual', 'Pending', NULL, '2025-08-03 15:51:16', '2025-08-03 15:51:16'),
(5, 3, 2, '18:11:00', '2025-08-03', 'toyota', 'avanza', 'b 12345', '2015', 50000, 'Manual', 'Pending', NULL, '2025-08-03 17:12:06', '2025-08-03 17:12:06'),
(6, 3, 2, '15:44:00', '2025-08-07', 'asd', 'asd', '13131', '2009', 300, 'Manual', 'Diterima', NULL, '2025-08-06 14:43:37', '2025-08-06 14:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `bengkel_id`, `product_id`, `user_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 200000, '2025-03-18 14:50:27', '2025-03-18 14:50:27'),
(9, 2, 2, 6, 1, 30000, '2025-07-22 22:09:32', '2025-07-22 22:09:32');

-- --------------------------------------------------------

--
-- Table structure for table `category_kendaraans`
--

CREATE TABLE `category_kendaraans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_kendaraans`
--

INSERT INTO `category_kendaraans` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Mobil', NULL, NULL),
(2, 'Motor', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `detail_layanan_bookings`
--

CREATE TABLE `detail_layanan_bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `layanan_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_transactions`
--

CREATE TABLE `detail_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `layanan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `product_price` int(11) DEFAULT NULL,
  `layanan_price` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_transactions`
--

INSERT INTO `detail_transactions` (`id`, `transaction_id`, `product_id`, `layanan_id`, `qty`, `product_price`, `layanan_price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, 200000, NULL, '2025-07-12 10:26:22', '2025-07-12 10:26:22'),
(2, 2, 2, NULL, 1, 30000, NULL, '2025-07-20 14:42:05', '2025-07-20 14:42:05'),
(3, 3, 1, NULL, 1, 200000, NULL, '2025-07-21 19:57:45', '2025-07-21 19:57:45'),
(4, 4, 1, NULL, 1, 200000, NULL, '2025-07-22 22:13:27', '2025-07-22 22:13:27'),
(5, 5, NULL, 1, 1, NULL, 10000, '2025-08-02 18:14:32', '2025-08-02 18:14:32'),
(6, 6, 2, NULL, 2, 30000, NULL, '2025-08-06 08:08:13', '2025-08-06 08:08:13'),
(7, 7, 2, NULL, 1, 30000, NULL, '2025-08-06 08:11:39', '2025-08-06 08:11:39'),
(8, 8, 2, NULL, 1, 30000, NULL, '2025-08-06 08:13:45', '2025-08-06 08:13:45'),
(9, 9, 2, NULL, 1, 30000, NULL, '2025-08-06 14:40:54', '2025-08-06 14:40:54'),
(10, 10, 2, NULL, 2, 30000, NULL, '2025-08-06 14:48:04', '2025-08-06 14:48:04'),
(11, 10, 3, NULL, 2, 100000, NULL, '2025-08-06 14:48:04', '2025-08-06 14:48:04'),
(12, 10, NULL, 1, 1, NULL, 10000, '2025-08-06 14:48:04', '2025-08-06 14:48:04'),
(13, 11, 2, NULL, 1, 30000, NULL, '2025-08-06 15:19:01', '2025-08-06 15:19:01'),
(14, 11, 3, NULL, 1, 100000, NULL, '2025-08-06 15:19:01', '2025-08-06 15:19:01'),
(15, 12, 2, NULL, 1, 30000, NULL, '2025-08-07 07:57:07', '2025-08-07 07:57:07'),
(16, 12, 3, NULL, 1, 100000, NULL, '2025-08-07 07:57:07', '2025-08-07 07:57:07'),
(17, 13, 2, NULL, 1, 30000, NULL, '2025-08-07 20:00:30', '2025-08-07 20:00:30');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwals`
--

CREATE TABLE `jadwals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `senin_buka` time NOT NULL,
  `senin_tutup` time NOT NULL,
  `selasa_buka` time NOT NULL,
  `selasa_tutup` time NOT NULL,
  `rabu_buka` time NOT NULL,
  `rabu_tutup` time NOT NULL,
  `kamis_buka` time NOT NULL,
  `kamis_tutup` time NOT NULL,
  `jumat_buka` time NOT NULL,
  `jumat_tutup` time NOT NULL,
  `sabtu_buka` time DEFAULT NULL,
  `sabtu_tutup` time DEFAULT NULL,
  `minggu_buka` time DEFAULT NULL,
  `minggu_tutup` time DEFAULT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwals`
--

INSERT INTO `jadwals` (`id`, `senin_buka`, `senin_tutup`, `selasa_buka`, `selasa_tutup`, `rabu_buka`, `rabu_tutup`, `kamis_buka`, `kamis_tutup`, `jumat_buka`, `jumat_tutup`, `sabtu_buka`, `sabtu_tutup`, `minggu_buka`, `minggu_tutup`, `bengkel_id`, `created_at`, `updated_at`) VALUES
(1, '09:28:00', '23:26:00', '09:26:00', '23:26:00', '09:27:00', '23:27:00', '09:27:00', '23:27:00', '09:27:00', '23:27:00', '09:26:00', '23:26:00', '09:27:00', '23:27:00', 2, '2025-08-02 17:27:39', '2025-08-02 17:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `kecamatans`
--

CREATE TABLE `kecamatans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kecamatans`
--

INSERT INTO `kecamatans` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Ciputat', NULL, NULL),
(2, 'Ciputan Timur', NULL, NULL),
(3, 'Pemulang', NULL, NULL),
(4, 'Pondok Aren', NULL, NULL),
(5, 'Serpong', NULL, NULL),
(6, 'Serpong Utara', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kelurahans`
--

CREATE TABLE `kelurahans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kecamatan_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelurahans`
--

INSERT INTO `kelurahans` (`id`, `kecamatan_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cipayung', NULL, NULL),
(2, 1, 'Ciputat', NULL, NULL),
(3, 1, 'Jombang', NULL, NULL),
(4, 1, 'Sawah Baru', NULL, NULL),
(5, 1, 'Sawah Lama', NULL, NULL),
(6, 1, 'Serua', NULL, NULL),
(7, 1, 'Serua Indah', NULL, NULL),
(8, 2, 'Cempaka Putih', NULL, NULL),
(9, 2, 'Cireundue', NULL, NULL),
(10, 2, 'Pisangan', NULL, NULL),
(11, 2, 'Pondok Ranji', NULL, NULL),
(12, 2, 'Rempoa', NULL, NULL),
(13, 2, 'Rengas', NULL, NULL),
(14, 3, 'Bambu Apus', NULL, NULL),
(15, 3, 'Benda Baru', NULL, NULL),
(16, 3, 'Kedaung', NULL, NULL),
(17, 3, 'Pamulang Barat', NULL, NULL),
(18, 3, 'Pamulang Timur', NULL, NULL),
(19, 3, 'Pondok Benda', NULL, NULL),
(20, 3, 'Pondok Cabe Ilir', NULL, NULL),
(21, 3, 'Pondok Cabe Udik', NULL, NULL),
(22, 4, 'Jurang Mangu Barat', NULL, NULL),
(23, 4, 'Jurang Mangu Timur', NULL, NULL),
(24, 4, 'Perigi Baru', NULL, NULL),
(25, 4, 'Perigi Lama', NULL, NULL),
(26, 4, 'Pondok Aren', NULL, NULL),
(27, 4, 'Pondok Betung', NULL, NULL),
(28, 4, 'Pondok Jaya', NULL, NULL),
(29, 4, 'Pondok Kacang Barat', NULL, NULL),
(30, 4, 'Pondok Kacang Timur', NULL, NULL),
(31, 4, 'Pondok Karya', NULL, NULL),
(32, 5, 'Buaran', NULL, NULL),
(33, 5, 'Ciater', NULL, NULL),
(34, 5, 'Cilenggang', NULL, NULL),
(35, 5, 'Lengkong Gudang', NULL, NULL),
(36, 5, 'Lengkong Gudang Timur', NULL, NULL),
(37, 5, 'Lengkong Wetan', NULL, NULL),
(38, 5, 'Rawa Buntu', NULL, NULL),
(39, 5, 'Rawa Mekar Jaya', NULL, NULL),
(40, 5, 'Serpong', NULL, NULL),
(41, 6, 'Jelupang', NULL, NULL),
(42, 6, 'Lengkong Karya', NULL, NULL),
(43, 6, 'Paku Jaya', NULL, NULL),
(44, 6, 'Pakualam', NULL, NULL),
(45, 6, 'Pakulonan', NULL, NULL),
(46, 6, 'Pondok Jagung', NULL, NULL),
(47, 6, 'Pondok Jagung Timur', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kendaraans`
--

CREATE TABLE `kendaraans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `merk` varchar(100) NOT NULL,
  `model` varchar(255) NOT NULL,
  `plat` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_kendaraan_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `layanans`
--

CREATE TABLE `layanans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `layanans`
--

INSERT INTO `layanans` (`id`, `name`, `price`, `bengkel_id`, `created_at`, `updated_at`) VALUES
(1, 'tambal ban', 10000, 2, '2025-08-02 17:28:04', '2025-08-02 17:28:04');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2023_05_17_151754_create_admins_table', 1),
(6, '2023_05_17_152036_create_pemilik_bengkels_table', 1),
(7, '2023_05_17_152548_create_bengkels_table', 1),
(8, '2023_05_17_153117_create_layanans_table', 1),
(9, '2023_05_17_153312_create_jadwals_table', 1),
(10, '2023_05_17_153352_create_category_kendaraans_table', 1),
(11, '2023_05_17_153425_create_kendaraans_table', 1),
(12, '2023_05_17_153459_create_bookings_table', 1),
(13, '2023_05_17_153542_create_detail_layanan_bookings_table', 1),
(14, '2024_07_14_093224_create_kecamatans_table', 1),
(15, '2024_07_14_093312_create_kelurahans_table', 1),
(16, '2024_07_14_093616_add_kecamatn_id_and_kelurahan_id_columns_to_users_table', 1),
(17, '2024_07_14_093638_add_kecamatn_id_and_kelurahan_id_columns_to_bengkels_table', 1),
(18, '2024_07_14_111104_create_products_table', 1),
(19, '2024_07_14_122318_create_carts_table', 1),
(20, '2024_07_14_225356_create_transactions_table', 1),
(21, '2024_07_14_225522_create_detail_transactions_table', 1),
(22, '2024_07_18_222008_create_bengkel_carts_table', 1),
(23, '2024_08_10_154005_create_specialists_table', 1),
(24, '2024_08_10_163820_create_bengkel_specialist_table', 1),
(25, '2024_08_21_072616_create_withdraw_requests_table', 1),
(26, '2024_08_22_195703_add_column_withdrawn_at_in_transactions_table', 1),
(27, '2024_08_24_164828_add_column_name_in_withdraw_requests_table', 1),
(28, '2025_08_07_191537_add_lat_long_to_bengkels_table', 2),
(29, '2025_08_10_113720_create_ratings_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemilik_bengkels`
--

CREATE TABLE `pemilik_bengkels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_number` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pemilik_bengkels`
--

INSERT INTO `pemilik_bengkels` (`id`, `name`, `email`, `email_verified_at`, `phone_number`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Fulan', 'prtmyog17@gmail.com', NULL, '08123456789', '$2y$10$s1KyNeaXyi6SlbwMxwlHfuCo.F4up.0LVViLqcGvYu/WDB1Kkwfk6', NULL, '2025-03-18 14:45:33', '2025-03-18 14:45:33'),
(2, 'yoga', 'ygytama17@gmail.com', NULL, '08123456789', '$2y$10$f0/TjVXV4AZNWZQGg4GV9Od.TUc99VmgzmG8TwKSYMePLnOGJiufC', NULL, '2025-03-18 14:46:33', '2025-03-18 14:46:33'),
(3, 'reonaldi owner', 'reonaldi15@gmail.com', NULL, '0895375873744', '$2y$10$O4ymlsXoywrXK2s1ejNkd.9gW9PMIeXb6fenP8mbh5wMkbEEawZda', NULL, '2025-07-12 10:33:20', '2025-07-12 10:33:20'),
(4, 'Pemilik Bengkel', 'owner@email.com', NULL, '08123456789', '$2y$10$q/wl2Y3KbU03DnPBGiOgS.HJRTUHUT6wpqXm1VtL7GiChUIiDTmCW', NULL, '2025-07-17 15:43:57', '2025-07-17 15:43:57'),
(5, 'Rofid', 'rofid@email.com', NULL, '08123456789', '$2y$10$0SKz0nkAmrZ8u913GdoNh.vFZgmzFOZliCDCQ0in/uZj5BWr6wlrS', NULL, '2025-07-17 15:50:33', '2025-07-17 15:50:33'),
(6, 'Fahmi', 'ahmadfahmicni@gmail.com', NULL, '098694251421', '$2y$10$k0FeT2B.d4WwRYO67tRLy.aVdv9SFHzuvvsWzbKmbUUVgphSc7vzW', NULL, '2025-07-22 20:05:02', '2025-07-22 20:05:02'),
(7, 'f4hmi', 'ahmadfahmicihuy@gmail.com', NULL, '088298473868', '$2y$10$NTSoKb3MP0PaRFKBZr9xdeTp0.QwFjRixNiCU7KzskE/YjNB6/44q', NULL, '2025-07-22 20:33:58', '2025-07-22 20:33:58'),
(8, 'reonaldi owner 1', 'reonaldi15+1@gmail.com', NULL, '0895375873744', '$2y$10$qyInIwJOMMJ.gF/5k0qyIedhudl5lEmzk4txDohdDuxMcP9SMoiU2', NULL, '2025-07-25 09:08:07', '2025-07-25 09:08:07'),
(9, 'Rofid', 'rofidnasifannafie@email.com', NULL, '08123456789', '$2y$10$Kat7Whz/.RclhWiFqPPUJOZJ7bRAzeDC37EgfmaOmIabli9PX.hua', NULL, '2025-07-31 12:41:17', '2025-07-31 12:41:17'),
(10, 'Budiono Siregar', 'budi@gmail.com', NULL, '089764743673', '$2y$10$3b/PJQmjM3WgPe0Z0OcAqe.Qceb4PQAHsB7/280f4PbOSvvcAxfXi', NULL, '2025-07-31 12:42:02', '2025-07-31 12:42:02'),
(11, 'Budi', 'budi@email.com', NULL, '08123456789', '$2y$10$LRJpbxpzeo.7SGxfpgidGOejT.v5IyErzJJvGoQZiQ7VMB7LVAH1y', NULL, '2025-07-31 12:43:01', '2025-07-31 12:43:01'),
(12, 'aprit', 'aprit10@gmail.com', NULL, '087871711025', '$2y$10$KdArXIowXNKbkePSTzLCvOFNtmCv/CbkUk0HLg2GN9NxsLroMbaf.', NULL, '2025-07-31 17:19:13', '2025-07-31 17:19:13'),
(13, 'reonaldi owner 1', 'reonaldi@gmail.com', NULL, '0895375873744', '$2y$10$OZ9rxq/IENUVV.z3df4.o.F5K5RqK.XG6xumaCHqL2NK4mI4VOZ3e', NULL, '2025-08-01 23:43:22', '2025-08-01 23:43:22'),
(14, 'iam1', 'iam1@gmail.com', NULL, '087871710294', '$2y$10$8FkOz3wx5R1IfMFNl2.Tm.wLl1JFjOCv/Q9GoQ3MdgRn/zSRmJCju', NULL, '2025-08-04 21:59:14', '2025-08-04 21:59:14'),
(15, 'ijulijul', 'ijul1@gmail.com', NULL, '09891281982', '$2y$10$aOhpqtVVmxS669tQ2yHQJeQ8/QSFjxj.hGqRiy6yUVfmzJRnPTGg2', NULL, '2025-08-04 22:03:17', '2025-08-04 22:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 3, 'auth_token', '86613c81065de568da93cd8358cfab1838e5d45107a39e2a50eab77e4b5f1204', '[\"*\"]', NULL, '2025-07-12 10:39:13', '2025-07-12 10:39:13'),
(2, 'App\\Models\\User', 4, 'auth_token', 'a53e07e1970a6b0415c4d8bd13c4e60da1e9c974791c6231518705512f44d360', '[\"*\"]', NULL, '2025-07-14 11:56:28', '2025-07-14 11:56:28'),
(3, 'App\\Models\\User', 4, 'auth_token', '6259d68687af320bc6ee4006121500f6fd8a879a81c419ee0fc4a1aea84cb3e9', '[\"*\"]', '2025-07-14 12:32:58', '2025-07-14 12:17:32', '2025-07-14 12:32:58'),
(4, 'App\\Models\\User', 5, 'auth_token', '9107a3c74865048ae69208b11c408a2af5d18c10a621b585a03516dd66c0bbdc', '[\"*\"]', NULL, '2025-07-16 12:33:38', '2025-07-16 12:33:38'),
(5, 'App\\Models\\User', 5, 'auth_token', '79579a884c08924e473facdabe5a205cbbd2c0838a32ecec0916b8c0046a2fe9', '[\"*\"]', NULL, '2025-07-16 16:41:10', '2025-07-16 16:41:10'),
(6, 'App\\Models\\User', 5, 'auth_token', '749905b06b2eec8eeb0a5571de5dda7027351cb56bc681cb62c37d2fdcf36393', '[\"*\"]', NULL, '2025-07-16 16:41:45', '2025-07-16 16:41:45'),
(7, 'App\\Models\\User', 5, 'auth_token', '1b318418be2058292aef375d320fb2f0f1c9610c1503d56c4500d4a469b4d277', '[\"*\"]', NULL, '2025-07-16 17:10:18', '2025-07-16 17:10:18'),
(8, 'App\\Models\\User', 5, 'auth_token', 'afd56bafc53fb1e36635f515432c240f273457be91a50d870876028858132076', '[\"*\"]', NULL, '2025-07-16 17:14:45', '2025-07-16 17:14:45'),
(9, 'App\\Models\\User', 5, 'auth_token', '7ae9a8e068b6201f77cd450551472d0c810c3ff1a6dd5ddbd73ebcd719ff29e0', '[\"*\"]', NULL, '2025-07-16 17:17:28', '2025-07-16 17:17:28'),
(10, 'App\\Models\\User', 5, 'auth_token', 'e60f6afc86665e849520567466a625b394671f3fc9d2bf60aaff4c1469021ede', '[\"*\"]', NULL, '2025-07-16 17:17:39', '2025-07-16 17:17:39'),
(11, 'App\\Models\\User', 5, 'auth_token', 'e261740e9d758dce32706fae9621797497a579ef5bc255298def1d140f1abffa', '[\"*\"]', NULL, '2025-07-16 17:19:51', '2025-07-16 17:19:51'),
(12, 'App\\Models\\User', 5, 'auth_token', '82b5deea2089be042fbbf5edb0b14cd1bfd039967e9e9e87b0189c78d8b61684', '[\"*\"]', NULL, '2025-07-16 17:21:34', '2025-07-16 17:21:34'),
(13, 'App\\Models\\User', 5, 'auth_token', '3cc34302aefb6e61d45103e234a18f2caafa8399916e61c87cb2f744fcfbd881', '[\"*\"]', NULL, '2025-07-16 17:28:09', '2025-07-16 17:28:09'),
(14, 'App\\Models\\User', 5, 'auth_token', 'd78bfcfd0984fdc66af175c1f58d8327745b0c994d6158c560e809b3958c0fa1', '[\"*\"]', NULL, '2025-07-16 17:38:55', '2025-07-16 17:38:55'),
(15, 'App\\Models\\User', 5, 'auth_token', '945b0d3604f2ccfb149bc192a857e4a6f852106cffc2410e740b061fe518d856', '[\"*\"]', NULL, '2025-07-16 17:44:36', '2025-07-16 17:44:36'),
(16, 'App\\Models\\User', 6, 'auth_token', 'b487a250c1d9ac50182280a00dc4bf8e40bf816ec45284b48299d88ed1ae14cc', '[\"*\"]', NULL, '2025-07-16 18:48:07', '2025-07-16 18:48:07'),
(17, 'App\\Models\\User', 6, 'auth_token', '8a957c3c9c71206fd9b794135967506249464a04f63b31448ad75d9f668a9245', '[\"*\"]', NULL, '2025-07-16 18:50:24', '2025-07-16 18:50:24'),
(18, 'App\\Models\\User', 6, 'auth_token', '783f449c1a862f64b93cfaa097fe81856f222dd93b165fae9b76330d3392fa62', '[\"*\"]', NULL, '2025-07-16 18:51:10', '2025-07-16 18:51:10'),
(19, 'App\\Models\\User', 6, 'auth_token', 'f5f5198f5552ab982224a8f33610302b0ab87fc41edbb292ae0d48108132a72d', '[\"*\"]', NULL, '2025-07-16 18:52:43', '2025-07-16 18:52:43'),
(20, 'App\\Models\\User', 6, 'auth_token', '01e886f8215ca3c6a3babc6a122ae3a62a19e0603141df28f9044dab9890ac3b', '[\"*\"]', NULL, '2025-07-16 18:53:18', '2025-07-16 18:53:18'),
(21, 'App\\Models\\User', 6, 'auth_token', '2b6f43e9b53b6d427cf50883bfa7513764f1042cd253dd554b9932652c9df860', '[\"*\"]', NULL, '2025-07-16 18:59:13', '2025-07-16 18:59:13'),
(22, 'App\\Models\\User', 3, 'auth_token', 'a5224d5f4192f46ee84193d129bf6450f1d1f8c10ddef523b90a2529ebd7146a', '[\"*\"]', NULL, '2025-07-16 19:30:07', '2025-07-16 19:30:07'),
(23, 'App\\Models\\User', 3, 'auth_token', 'a3d69e9b3e253384cfc6fa7b9c3a7ed68aaf04455ce7c32e9c57ddf0c5199238', '[\"*\"]', NULL, '2025-07-16 19:30:28', '2025-07-16 19:30:28'),
(24, 'App\\Models\\User', 3, 'auth_token', '80e508ff6b8b9dafa262b4bef68913cc4cef8e6e20dad6f7d4efdc9f856ddfbb', '[\"*\"]', '2025-07-16 19:42:06', '2025-07-16 19:33:09', '2025-07-16 19:42:06'),
(25, 'App\\Models\\User', 6, 'auth_token', 'b86d60ae519d2c6c71ffa05f9d5d7fb1070a0c9e24a9655d95884ea5630c0051', '[\"*\"]', NULL, '2025-07-16 19:34:53', '2025-07-16 19:34:53'),
(26, 'App\\Models\\User', 3, 'auth_token', '2a83e7a2e9de9ae23ee42950d6981a38ecfb16eebaf50d97a1c51de9a3ba17bb', '[\"*\"]', NULL, '2025-07-16 20:40:44', '2025-07-16 20:40:44'),
(27, 'App\\Models\\User', 7, 'auth_token', 'f14438aca1a808983b6a71f6ed2181cc2a76f04eb503e1f3c5a3fa398619b94e', '[\"*\"]', NULL, '2025-07-17 15:44:38', '2025-07-17 15:44:38'),
(28, 'App\\Models\\User', 6, 'auth_token', '50668524961164a533ac5ea41e1014c70d9f1d9c8e5ea1b94491d4cd950a633d', '[\"*\"]', NULL, '2025-07-18 04:53:07', '2025-07-18 04:53:07'),
(29, 'App\\Models\\User', 6, 'auth_token', '53d4e5a1366d9181ca3059e32a5e2dc277d8a586d3a16aa50d301006f1fb97e0', '[\"*\"]', '2025-07-18 05:17:07', '2025-07-18 05:17:02', '2025-07-18 05:17:07'),
(30, 'App\\Models\\User', 6, 'auth_token', '94d821a249af5308b0a4696c6240470e11444ce964b5109044bc2333b578f2d4', '[\"*\"]', NULL, '2025-07-18 05:22:01', '2025-07-18 05:22:01'),
(31, 'App\\Models\\User', 6, 'auth_token', 'ad1fc311d15cd77b5e7f017bb2389fe6b5c8afd2a9bfaef6503254496165916b', '[\"*\"]', NULL, '2025-07-18 05:42:13', '2025-07-18 05:42:13'),
(32, 'App\\Models\\User', 6, 'auth_token', '9b38521a4f54181abb086f59b887b40f3c780c341aa4c3fa969c9064be2d3747', '[\"*\"]', NULL, '2025-07-18 05:45:58', '2025-07-18 05:45:58'),
(33, 'App\\Models\\User', 6, 'auth_token', '5e1c7449aae62907204e0f01a5566d5703dea94ea6d62e4df01c772fae4111bb', '[\"*\"]', '2025-07-18 06:07:12', '2025-07-18 06:07:07', '2025-07-18 06:07:12'),
(34, 'App\\Models\\User', 6, 'auth_token', '0e6506d4f288c46b2cf2412155d7903e372e81a04d96227624e361c80e3eb161', '[\"*\"]', '2025-07-18 07:11:57', '2025-07-18 06:20:20', '2025-07-18 07:11:57'),
(35, 'App\\Models\\User', 6, 'auth_token', 'aa181bad9cd98a4f01d766c4aa564c0785b3fa46be9b265695d4f163be1df20e', '[\"*\"]', '2025-07-18 07:19:06', '2025-07-18 07:18:11', '2025-07-18 07:19:06'),
(36, 'App\\Models\\User', 3, 'auth_token', '7cce2278e6b3106d1d484cd40de148f8006cc09a88adcd5d545b43f5d1951404', '[\"*\"]', '2025-07-20 14:10:27', '2025-07-20 10:43:50', '2025-07-20 14:10:27'),
(37, 'App\\Models\\User', 3, 'auth_token', '13c96340850cc65145c53bec53df98e80aee6b04aa42c0beb5ee3eef0f51bed7', '[\"*\"]', NULL, '2025-07-20 11:02:57', '2025-07-20 11:02:57'),
(38, 'App\\Models\\User', 3, 'auth_token', 'eed3b53b943f2314a5e85779db7f9340e96686a1905ad8d50c1045d2611dc9cc', '[\"*\"]', '2025-07-21 20:01:42', '2025-07-20 13:56:13', '2025-07-21 20:01:42'),
(39, 'App\\Models\\User', 3, 'auth_token', '9454c20f4a8c69b54724bc33ed463c9d1b957653fda2e21099339e7f4d6bb3ec', '[\"*\"]', '2025-07-22 21:23:48', '2025-07-20 15:37:24', '2025-07-22 21:23:48'),
(40, 'App\\Models\\User', 3, 'auth_token', '5f54e9d242285e8f758c03607fde9d07402ff6a6b90d2eb10608b52546f85836', '[\"*\"]', '2025-07-20 19:42:26', '2025-07-20 18:33:50', '2025-07-20 19:42:26'),
(41, 'App\\Models\\User', 3, 'auth_token', '32a78af2a34bf06cb4bf34a51d3f35ee2e31f3243afc1eee53959bbfc402ee21', '[\"*\"]', '2025-07-20 19:19:18', '2025-07-20 19:13:04', '2025-07-20 19:19:18'),
(42, 'App\\Models\\User', 9, 'auth_token', 'a509683e6590ab087c25d23b6f29cc7303898927f053cf8944751dc1157ef3af', '[\"*\"]', NULL, '2025-07-21 04:29:10', '2025-07-21 04:29:10'),
(43, 'App\\Models\\User', 3, 'auth_token', '7743c35e091fdff4fac0e739eb7bcf2a766bfb9d50fa1fedcc36c955fa9f3846', '[\"*\"]', '2025-07-22 21:25:49', '2025-07-22 21:25:47', '2025-07-22 21:25:49'),
(44, 'App\\Models\\User', 6, 'auth_token', '0e771bb341f1e550df600d5e433aa476f78391996d360c07a8a07b8b8a7d4a49', '[\"*\"]', '2025-07-22 22:23:07', '2025-07-22 22:07:24', '2025-07-22 22:23:07'),
(45, 'App\\Models\\User', 6, 'auth_token', 'c25440058367f0633590cc95fb8e8add34a1bcbf5aacfb144e4b42c86073702f', '[\"*\"]', '2025-07-23 01:45:31', '2025-07-23 00:24:45', '2025-07-23 01:45:31'),
(46, 'App\\Models\\User', 6, 'auth_token', '16bfe38f2d7043b0a38b9e1da2cdb61c57e6c332c50f7e9cd26ed5cc48a5663a', '[\"*\"]', '2025-07-23 03:23:50', '2025-07-23 01:49:19', '2025-07-23 03:23:50'),
(47, 'App\\Models\\User', 3, 'auth_token', '1b87610f1b36a0f1bc60bd9bae04e578545375b85611ba483cf2ac0db4788f06', '[\"*\"]', NULL, '2025-07-24 08:09:19', '2025-07-24 08:09:19'),
(48, 'App\\Models\\User', 10, 'auth_token', '49ae5e46606a8b7d8586581786305e9d5ce4a1dccc8b635b8e67cbd7d2f48ed8', '[\"*\"]', NULL, '2025-07-24 08:10:18', '2025-07-24 08:10:18'),
(50, 'App\\Models\\PemilikBengkel', 8, 'auth_token', '6a715869f62c4a562b94fbef6a357a5f361c3c61b3b0f08e542c33889a62cc10', '[\"*\"]', NULL, '2025-07-25 09:08:07', '2025-07-25 09:08:07'),
(51, 'App\\Models\\PemilikBengkel', 5, 'auth_token', 'e03e8dd9e01ee910d73e445d556057843968e4115bb86af24428c1942b645e50', '[\"*\"]', NULL, '2025-07-25 09:44:51', '2025-07-25 09:44:51'),
(52, 'App\\Models\\User', 3, 'auth_token', '5157b03cff5fe079b7f9b8fc861b1ebbc2f0a6ef521106b326910114d0007692', '[\"*\"]', '2025-07-30 08:02:53', '2025-07-30 08:02:51', '2025-07-30 08:02:53'),
(53, 'App\\Models\\User', 3, 'auth_token', 'a7e6a8e414a6929f0eea634efc2eb6c38e58b72509ff6ca66d1075d9a75c6dac', '[\"*\"]', '2025-07-30 08:19:39', '2025-07-30 08:19:27', '2025-07-30 08:19:39'),
(54, 'App\\Models\\PemilikBengkel', 9, 'auth_token', '9978dde36d9be18defb8e9fbe59faf4c4cfd5315e49d20475d4d59f7f2c1f63d', '[\"*\"]', NULL, '2025-07-31 12:41:17', '2025-07-31 12:41:17'),
(55, 'App\\Models\\PemilikBengkel', 11, 'auth_token', '2376139418db840896a87cad3fe45598d7c78e04f992b174b0bab1df62cb9067', '[\"*\"]', NULL, '2025-07-31 12:43:01', '2025-07-31 12:43:01'),
(56, 'App\\Models\\PemilikBengkel', 11, 'auth_token', '6b8a9b509f13716c0f5c01b95362f0b887672f1206ababb578287a87e4c8676a', '[\"*\"]', NULL, '2025-07-31 12:43:06', '2025-07-31 12:43:06'),
(57, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '29f80935c9b07aeddf1352be967b6a17f830fbfcac028b27db8dec455db2c9cb', '[\"*\"]', NULL, '2025-07-31 17:19:39', '2025-07-31 17:19:39'),
(58, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '167709ec8b6c6105a6f80381a9373413aeb0f41ae78ae28add5fb825501231d4', '[\"*\"]', NULL, '2025-07-31 17:26:27', '2025-07-31 17:26:27'),
(59, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'b8b26d135bf8ed775370839450796b5795de29a3e9403de4f1f97c5063d610ef', '[\"*\"]', NULL, '2025-07-31 17:27:13', '2025-07-31 17:27:13'),
(60, 'App\\Models\\User', 3, 'auth_token', '0ec395d9a7ec5cd511cb0dac72772264732cdcd2b8f16e1aa3259a10ec604af4', '[\"*\"]', NULL, '2025-08-01 08:27:47', '2025-08-01 08:27:47'),
(61, 'App\\Models\\User', 3, 'auth_token', '9f429a093c9a63f375a9404a405982ec81a0a4101a66de886885a1b6d9f15235', '[\"*\"]', '2025-08-01 08:34:11', '2025-08-01 08:34:09', '2025-08-01 08:34:11'),
(62, 'App\\Models\\User', 3, 'auth_token', '8e9a59c58e18278be95caaac2e5e0185e6e2e493650fb0d9550e76212434dcca', '[\"*\"]', NULL, '2025-08-01 08:40:00', '2025-08-01 08:40:00'),
(63, 'App\\Models\\User', 3, 'auth_token', '799ff8c0e4e63fe713aec850488d6b5194bdf3d9daadfe5eda6500bac4846cc4', '[\"*\"]', '2025-08-01 08:50:19', '2025-08-01 08:50:16', '2025-08-01 08:50:19'),
(64, 'App\\Models\\PemilikBengkel', 3, 'auth_token', '5e9d239e933007c6836d5f85d0ea8fcf4c9d32b864d0b4fe3fc079ce5c3c1318', '[\"*\"]', '2025-08-02 17:27:44', '2025-08-01 08:53:33', '2025-08-02 17:27:44'),
(65, 'App\\Models\\User', 3, 'auth_token', '1317c81bb20272e4a1b6c5b1bf93182ed7d911d33e1ac2855aa8ab993ce356d3', '[\"*\"]', '2025-08-01 22:01:36', '2025-08-01 21:28:42', '2025-08-01 22:01:36'),
(66, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '8a7830d3203299db3bf9d9ed2086f808acc37812064f5e97cfdc7e1ef13ce15d', '[\"*\"]', NULL, '2025-08-01 21:34:38', '2025-08-01 21:34:38'),
(67, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '62e216ab370f7e7513966ad243f112cc25200d51aa4df8efc5d21e545b7d5fb6', '[\"*\"]', NULL, '2025-08-01 21:35:33', '2025-08-01 21:35:33'),
(68, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'd5f44b929376234b30f97540a9e83ee800de8f27db067eb7b3b4656b25cf2299', '[\"*\"]', NULL, '2025-08-01 21:42:55', '2025-08-01 21:42:55'),
(69, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '2daba7e259315f4e26296595136daea92283489a47e4315491018ef7979dce2e', '[\"*\"]', NULL, '2025-08-01 22:17:11', '2025-08-01 22:17:11'),
(70, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '3a088edf1b53edf150d3f5a5cb124f40c46734c67321a42efffc6d7282cd52dd', '[\"*\"]', '2025-08-01 22:28:07', '2025-08-01 22:28:07', '2025-08-01 22:28:07'),
(71, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'ed5b0919cac4ed9b0eac41383c3e1e99b509ce27f77bb6040eaef489194cceae', '[\"*\"]', '2025-08-01 22:32:42', '2025-08-01 22:32:42', '2025-08-01 22:32:42'),
(72, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '3236427b971c2748cb3e7c81a0cd7899a8d5cba7bd1a7ca5f420878286e39dbd', '[\"*\"]', '2025-08-01 22:35:11', '2025-08-01 22:35:11', '2025-08-01 22:35:11'),
(73, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'fbecf0095b23c28f902897cb8a666dcadcda088cff5533dbe478d38eade0259c', '[\"*\"]', '2025-08-01 22:37:30', '2025-08-01 22:37:29', '2025-08-01 22:37:30'),
(74, 'App\\Models\\PemilikBengkel', 3, 'auth_token', 'b0f255514096205c40a5753c2ea4281541640be6e7efbcd9c8ae816ac7cb58a1', '[\"*\"]', '2025-08-01 22:43:12', '2025-08-01 22:42:29', '2025-08-01 22:43:12'),
(75, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'dd22581cda302786b99cc2dedf24ecc3f3f3228674c696fc705d6e9df559632b', '[\"*\"]', '2025-08-01 22:43:29', '2025-08-01 22:43:28', '2025-08-01 22:43:29'),
(76, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'ac5cb38a5a6aeda3895648e3632cd582a6a19fd22097fbe91ada574c41e3c380', '[\"*\"]', '2025-08-01 22:46:17', '2025-08-01 22:46:15', '2025-08-01 22:46:17'),
(77, 'App\\Models\\PemilikBengkel', 3, 'auth_token', '576c9bdd0b2c77b1c29fe8b72bf3139692aded81cf104daef0df7b9b099fdd7f', '[\"*\"]', '2025-08-01 22:52:25', '2025-08-01 22:52:23', '2025-08-01 22:52:25'),
(78, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '2827dbad00f019e50a3ee4a1b6c26e47bbe83ae0fd6893fbb1e06e2fcbe25c16', '[\"*\"]', '2025-08-01 23:06:34', '2025-08-01 22:55:13', '2025-08-01 23:06:34'),
(79, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'd019d75144e137a694b2991933e242d97cb97b979957662256f3b265926e9fc3', '[\"*\"]', '2025-08-01 23:09:23', '2025-08-01 23:09:21', '2025-08-01 23:09:23'),
(80, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '93aa69dcb9089a4825773f9c5f5391c159abb6a68a110ffafb11e00cfb596987', '[\"*\"]', '2025-08-01 23:12:03', '2025-08-01 23:12:02', '2025-08-01 23:12:03'),
(81, 'App\\Models\\PemilikBengkel', 3, 'auth_token', 'd3063185f69d34109bcfa9fe203f969bcc770347e72762d3c64ebf12911f7c05', '[\"*\"]', '2025-08-01 23:12:30', '2025-08-01 23:12:28', '2025-08-01 23:12:30'),
(82, 'App\\Models\\PemilikBengkel', 12, 'auth_token', 'cf4aa09645af48623186cbe772d61db0f0d5cdcb4d9a3e9ce0fe5486fd0a9c34', '[\"*\"]', '2025-08-01 23:21:14', '2025-08-01 23:13:54', '2025-08-01 23:21:14'),
(83, 'App\\Models\\User', 4, 'auth_token', '69a18990f07a2e28444b47d1413b25c679a0b19fad61614ffb83522152ef97d8', '[\"*\"]', NULL, '2025-08-01 23:27:06', '2025-08-01 23:27:06'),
(84, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '9f29b6dd23678c3179e8861e38e781aae0b3467b8174ec5417b6940c87458588', '[\"*\"]', '2025-08-01 23:29:24', '2025-08-01 23:28:38', '2025-08-01 23:29:24'),
(85, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '1bac7edd11d4437c6a435f56445f7ed5b22b932cbb7baf729d99a98148a9c87b', '[\"*\"]', '2025-08-01 23:29:44', '2025-08-01 23:29:43', '2025-08-01 23:29:44'),
(86, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '1dbded3d36028854634902d4558c7a4b832fad6ea312b5cdf002c7915232de20', '[\"*\"]', '2025-08-01 23:30:52', '2025-08-01 23:30:50', '2025-08-01 23:30:52'),
(87, 'App\\Models\\PemilikBengkel', 13, 'auth_token', '7280a07c5c51e896b3f872d48fc553d4532ac9a11a7ec4f3c4f885e7704a6434', '[\"*\"]', NULL, '2025-08-01 23:43:22', '2025-08-01 23:43:22'),
(88, 'App\\Models\\PemilikBengkel', 12, 'auth_token', '4cd755b1b327920e9260cd3aaba08b1701a21315f8b2cd550053b30a2e4ed558', '[\"*\"]', '2025-08-02 09:55:44', '2025-08-02 09:55:42', '2025-08-02 09:55:44'),
(89, 'App\\Models\\User', 3, 'auth_token', '0fa5a4541b50bc6859c000dc773e9ad89d1cb09734c2f222270ca608e5942cfb', '[\"*\"]', '2025-08-03 16:42:44', '2025-08-02 16:47:42', '2025-08-03 16:42:44'),
(90, 'App\\Models\\User', 3, 'auth_token', '02e187650fceebd3e0d5d1a9ea66494ef44124484d162cf8a0bc8127efa64548', '[\"*\"]', '2025-08-06 19:12:18', '2025-08-02 17:24:24', '2025-08-06 19:12:18'),
(92, 'App\\Models\\User', 3, 'auth_token', 'def1e7a9be4e12f8c9a0fe6e3a3431f277dfabe797a6df11d3f6b666cb7bc14d', '[\"*\"]', '2025-08-03 17:53:56', '2025-08-03 17:47:01', '2025-08-03 17:53:56'),
(93, 'App\\Models\\PemilikBengkel', 3, 'auth_token', '38e8adec7fd4850c11cabc5f534bc8edcade7ee684d5b4025c2d49e4b4e1f0c3', '[\"*\"]', '2025-08-04 21:45:11', '2025-08-04 21:45:08', '2025-08-04 21:45:11'),
(94, 'App\\Models\\PemilikBengkel', 14, 'auth_token', 'a96d380ea3ec77db10ef67fd0ab931f3c6a6e6c1d09137763e1934b9b4899098', '[\"*\"]', NULL, '2025-08-04 21:59:14', '2025-08-04 21:59:14'),
(95, 'App\\Models\\PemilikBengkel', 14, 'auth_token', '68f611acbb5296592a32b3d8e3646e488e1db8c493c96cbd1bacd3186b9e2a03', '[\"*\"]', '2025-08-04 22:02:44', '2025-08-04 21:59:24', '2025-08-04 22:02:44'),
(96, 'App\\Models\\PemilikBengkel', 15, 'auth_token', '6f1f51ef739e9aae88a34aa38550fad3d055773c79950f16eede171d61017169', '[\"*\"]', NULL, '2025-08-04 22:03:17', '2025-08-04 22:03:17'),
(97, 'App\\Models\\PemilikBengkel', 15, 'auth_token', '1fe1968a89429edccbdbcaf933f3c5207258e460cbf2cbb8101393baa6db2bf3', '[\"*\"]', '2025-08-04 22:03:26', '2025-08-04 22:03:25', '2025-08-04 22:03:26'),
(98, 'App\\Models\\PemilikBengkel', 15, 'auth_token', '09e551bdb6c163d33c6791d353144039292da97235f10ae24174b07bbf0598ff', '[\"*\"]', '2025-08-04 22:09:23', '2025-08-04 22:09:21', '2025-08-04 22:09:23'),
(99, 'App\\Models\\PemilikBengkel', 3, 'auth_token', '209d0452a0182c48133db4ca080ae26e3b1045e980ba3e0af2d3d0b33429d5fb', '[\"*\"]', '2025-08-04 22:10:29', '2025-08-04 22:10:27', '2025-08-04 22:10:29'),
(100, 'App\\Models\\User', 3, 'auth_token', '258c625aa372225ba2f76331ffb409c7466548acdd576ace23a24cae75149574', '[\"*\"]', '2025-08-13 21:33:08', '2025-08-06 19:11:49', '2025-08-13 21:33:08'),
(101, 'App\\Models\\User', 3, 'auth_token', 'c5d355228630c8cd2c45557e9f3f73fe3ed0f3238e917808c1eec281ce448deb', '[\"*\"]', '2025-08-06 20:23:55', '2025-08-06 20:23:16', '2025-08-06 20:23:55'),
(102, 'App\\Models\\User', 3, 'auth_token', 'bdd292c7a1f06eef9066d219dd730554103f89eebfc09c72bacfe183099ad434', '[\"*\"]', '2025-08-07 07:57:07', '2025-08-07 07:55:06', '2025-08-07 07:57:07'),
(103, 'App\\Models\\User', 3, 'auth_token', '0f4981753f0d6f9631f86a46f23708aab4b2f537e18d7cc1285db182debdb2d2', '[\"*\"]', '2025-08-09 20:29:12', '2025-08-07 22:52:13', '2025-08-09 20:29:12'),
(104, 'App\\Models\\User', 3, 'auth_token', 'd78111385363b849b8341a28d13613d44d326a77005eff71af6241a0da6e0695', '[\"*\"]', '2025-08-13 20:53:15', '2025-08-09 19:50:51', '2025-08-13 20:53:15'),
(105, 'App\\Models\\User', 3, 'auth_token', '66fa6ebe780c2cf38bce9fdd619ae2343fc2bef1601af1bf7a93565ce252356a', '[\"*\"]', '2025-08-13 12:57:03', '2025-08-13 12:54:57', '2025-08-13 12:57:03'),
(106, 'App\\Models\\User', 3, 'auth_token', 'b79956b7e7a3ad16893fcc9c38679a29182ac33b162c521fda77a85dc699d0b4', '[\"*\"]', '2025-08-13 20:56:46', '2025-08-13 20:50:19', '2025-08-13 20:56:46'),
(107, 'App\\Models\\User', 3, 'auth_token', '3e8f044ce22a1e3bbadc6cf80f1a0ef812ee4c3e23a4e978d5d91fb33349911c', '[\"*\"]', '2025-08-13 22:06:41', '2025-08-13 22:04:52', '2025-08-13 22:06:41'),
(108, 'App\\Models\\User', 3, 'auth_token', 'f7afa0c4f9ecc85b8c625e4f510b09a2a66f73653c56657c3062d0bb66cd918c', '[\"*\"]', '2025-10-14 13:09:17', '2025-08-13 22:07:02', '2025-10-14 13:09:17');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `price` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `image`, `description`, `bengkel_id`, `price`, `weight`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'oli samping', '1742284210.jpg', 'ini deskripsi', 1, 200000, 2, 9, '2025-03-18 14:50:10', '2025-07-22 22:13:27'),
(2, 'kap mobil', '1753197318.png', 'kap mobil', 2, 30000, 100, 12, '2025-07-12 10:35:35', '2025-08-07 20:00:30'),
(3, 'ban', '1753200302.png', 'ban tronton', 2, 100000, 7, 6, '2025-07-22 23:05:02', '2025-08-07 07:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `detail_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `stars` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `product_id`, `transaction_id`, `detail_transaction_id`, `stars`, `comment`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 11, 13, 5, 'Barang ori, pengiriman cepat!', '2025-08-10 14:31:40', '2025-08-10 14:31:40'),
(2, 3, 2, 10, 10, 4, 'test', '2025-08-10 17:08:47', '2025-08-10 17:08:47'),
(3, 3, 3, 11, 14, 5, 'mantap men', '2025-08-13 12:56:18', '2025-08-13 12:56:18'),
(4, 3, 2, 9, 9, 5, 'test', '2025-08-13 21:29:35', '2025-08-13 21:29:35'),
(5, 3, 3, 10, 11, 4, 'ban nya bagus banget', '2025-08-13 21:31:39', '2025-08-13 21:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `specialists`
--

CREATE TABLE `specialists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specialists`
--

INSERT INTO `specialists` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Bengkel Umum', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(2, 'Spesialis Mesin', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(3, 'Ban', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(4, 'Transmisi', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(5, 'AC', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(6, 'Kelistrikan', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(7, 'Kaki-kaki', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(8, 'Body Repair dan Cat', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(9, 'Custom dan Restorasi', '2025-03-17 14:17:34', '2025-03-17 14:17:34'),
(10, 'Audio Aksesoris', '2025-03-17 14:17:34', '2025-03-17 14:17:34');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_code` text NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `layanan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `administrasi` int(11) DEFAULT NULL,
  `payment_status` enum('pending','success','failed','expired') DEFAULT NULL,
  `shipping_status` enum('Pending','Disiapkan','Dikirim','Delesai') DEFAULT NULL,
  `ongkir` int(11) DEFAULT NULL,
  `grand_total` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `withdrawn_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_code`, `user_id`, `bengkel_id`, `booking_id`, `product_id`, `layanan_id`, `administrasi`, `payment_status`, `shipping_status`, `ongkir`, `grand_total`, `created_at`, `updated_at`, `withdrawn_at`) VALUES
(1, 'TRANS-439', 3, 1, NULL, NULL, NULL, 10000, 'pending', 'Pending', 15000, 225000, '2025-07-12 10:26:22', '2025-07-12 10:26:22', NULL),
(2, 'TRANS-251', 3, 2, NULL, NULL, NULL, 1500, 'pending', 'Pending', 925000, 956500, '2025-07-20 14:42:05', '2025-07-20 14:42:05', NULL),
(3, 'TRANS-844', 8, 1, NULL, NULL, NULL, 10000, 'pending', 'Pending', 25000, 235000, '2025-07-21 19:57:45', '2025-07-21 19:57:45', NULL),
(4, 'TRANS-270', 8, 1, NULL, NULL, NULL, 10000, 'pending', 'Pending', 25000, 235000, '2025-07-22 22:13:27', '2025-07-22 22:13:27', NULL),
(5, 'TRANS-316', 3, 2, 3, NULL, NULL, 500, 'pending', NULL, 0, 10500, '2025-08-02 18:14:32', '2025-08-02 18:14:32', NULL),
(6, 'TRANS-763', 3, 2, NULL, NULL, NULL, 3000, 'pending', 'Pending', 1925000, 1988000, '2025-08-06 08:08:13', '2025-08-06 08:08:13', NULL),
(7, 'TRANS-538', 3, 2, NULL, NULL, NULL, 1500, 'pending', 'Pending', 925000, 956500, '2025-08-06 08:11:39', '2025-08-06 08:11:39', NULL),
(8, 'TRANS-79', 3, 2, NULL, NULL, NULL, 1500, 'pending', 'Pending', 925000, 956500, '2025-08-06 08:13:45', '2025-08-06 08:13:45', NULL),
(9, 'TRANS-248', 3, 2, NULL, NULL, NULL, 1500, 'success', 'Pending', 925000, 956500, '2025-08-06 14:40:54', '2025-08-06 14:40:54', NULL),
(10, 'TRANS-987', 3, 2, 6, NULL, NULL, 13500, 'success', NULL, 0, 283500, '2025-08-06 14:48:04', '2025-08-06 14:48:04', NULL),
(11, 'TRANS-435', 3, 2, 5, NULL, NULL, 6500, 'success', NULL, 0, 136500, '2025-08-06 15:19:01', '2025-08-06 15:19:01', NULL),
(12, 'TRANS-137991', 3, 2, NULL, NULL, NULL, 6500, 'pending', 'Pending', 995000, 1131500, '2025-08-07 07:57:07', '2025-08-07 07:57:07', NULL),
(13, 'TRANS-853438', 3, 2, NULL, NULL, NULL, 1500, 'pending', 'Pending', 925000, 956500, '2025-08-07 20:00:30', '2025-08-07 20:00:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_number` varchar(255) NOT NULL,
  `alamat` longtext NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kecamatan_id` bigint(20) UNSIGNED NOT NULL,
  `kelurahan_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `phone_number`, `alamat`, `password`, `remember_token`, `created_at`, `updated_at`, `kecamatan_id`, `kelurahan_id`) VALUES
(1, 'Fulan', 'prtmyog17@gmail.com', NULL, '08123456789', 'jalan jati no 07 rw09 wt03 ngawi barat', '$2y$10$noiUlDrxJyv6tvuFZSjKgOQrfBwLOp6ZvtNNiqwCjhub26YHGZJOu', NULL, '2025-03-18 14:39:55', '2025-03-18 14:39:55', 2, 9),
(2, 'f4hmi', 'ahmadfahmicihuy@gmail.com', NULL, '098694251421', 'asdafd', '$2y$10$AhAvMxr89lSgsrJiezvEH..SxxrjHJJUwwAR.yg05eR0p9SVdb4Km', NULL, '2025-06-18 23:07:47', '2025-06-18 23:07:47', 1, 3),
(3, 'reonaldi saputro', 'reonaldi1105@gmail.com', NULL, '0895375873744', 'Pondok aren', '$2y$10$TLsPOazpRJHzPhvlvZmhA.twStluYtks2RT7mDS20vH7JkcPouLue', NULL, '2025-07-12 10:25:42', '2025-07-12 10:25:42', 2, 9),
(4, 'User', 'user@email.com', NULL, '08123456789', 'Jl. Contoh', '$2y$10$geUJYvAgsRFjHxS010qWhuZ4.JnZrCvdxaijaW6cSY9UQN0CUQ/V6', NULL, '2025-07-14 11:56:28', '2025-07-14 11:56:28', 1, 1),
(5, 'rafli80', 'rafli80@gmail.com', NULL, '089123849128', 'binong', '$2y$10$YhUXAoyXUaSsJWRwI0GmCuAzyKTUi0AZwEaZdU9/kVcYuzzBKdT2K', NULL, '2025-07-16 12:33:16', '2025-07-16 12:33:16', 1, 2),
(6, 'afrit', 'afrit10@gmail.com', NULL, '087822828282', 'cisauk', '$2y$10$O76.5ZxqBKaaDJ87FrCC3udFj019/JlYsJluIIB.KUre3SxTZafhu', NULL, '2025-07-16 18:47:46', '2025-07-18 05:41:38', 2, 8),
(7, 'User 1', 'user1@email.com', NULL, '08123456789', 'Jl. Contoh', '$2y$10$6Hv.wZgR5GcjSXggmNxrOOMmWLYAxwAEV9bcQ9UyH7T6TCglYVoAW', NULL, '2025-07-17 15:44:38', '2025-07-17 15:44:38', 1, 1),
(8, 'Fahmi', 'ahmadfahmicni@gmail.com', NULL, '098694251421', 'serpong', '$2y$10$s7f3gcoUS3Uih0KdvZIBtOvjMzssM84j2SDxYYst4jn9kycqKIVry', NULL, '2025-07-20 21:48:10', '2025-07-20 21:48:10', 5, 40),
(9, 'User 2', 'user2@email.com', NULL, '08123456789', 'Jl. Contoh', '$2y$10$5vp8ZNeijiq5WZjrRSBkw.OggP2C8p9UAQ/7axeaDq.SPk7AhJaT.', NULL, '2025-07-21 04:29:10', '2025-07-21 04:29:10', 1, 1),
(10, 'reonaldi satu', 'reonaldi1105+1@gmail.com', NULL, '0895375837434', 'Jl. Contoh', '$2y$10$GskHZQEJKyQuoFB5C7uiAexDq5cwQvVXNCYvQMaGwmmOfHmFKQ77.', NULL, '2025-07-24 08:10:18', '2025-07-24 08:10:18', 2, 9);

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_requests`
--

CREATE TABLE `withdraw_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bengkel_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','transferred') NOT NULL DEFAULT 'pending',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `bank` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `bengkels`
--
ALTER TABLE `bengkels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bengkels_pemilik_id_foreign` (`pemilik_id`),
  ADD KEY `bengkels_specialist_id_index` (`specialist_id`),
  ADD KEY `bengkels_kecamatan_id_foreign` (`kecamatan_id`),
  ADD KEY `bengkels_kelurahan_id_foreign` (`kelurahan_id`);

--
-- Indexes for table `bengkel_carts`
--
ALTER TABLE `bengkel_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bengkel_carts_bengkel_id_foreign` (`bengkel_id`),
  ADD KEY `bengkel_carts_booking_id_foreign` (`booking_id`),
  ADD KEY `bengkel_carts_product_id_foreign` (`product_id`),
  ADD KEY `bengkel_carts_layanan_id_foreign` (`layanan_id`),
  ADD KEY `bengkel_carts_user_id_foreign` (`user_id`);

--
-- Indexes for table `bengkel_specialist`
--
ALTER TABLE `bengkel_specialist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bengkel_specialist_bengkel_id_foreign` (`bengkel_id`),
  ADD KEY `bengkel_specialist_specialist_id_foreign` (`specialist_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_user_id_foreign` (`user_id`),
  ADD KEY `bookings_bengkel_id_foreign` (`bengkel_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_bengkel_id_foreign` (`bengkel_id`),
  ADD KEY `carts_product_id_foreign` (`product_id`),
  ADD KEY `carts_user_id_foreign` (`user_id`);

--
-- Indexes for table `category_kendaraans`
--
ALTER TABLE `category_kendaraans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detail_layanan_bookings`
--
ALTER TABLE `detail_layanan_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_layanan_bookings_booking_id_foreign` (`booking_id`),
  ADD KEY `detail_layanan_bookings_layanan_id_foreign` (`layanan_id`);

--
-- Indexes for table `detail_transactions`
--
ALTER TABLE `detail_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_transactions_transaction_id_foreign` (`transaction_id`),
  ADD KEY `detail_transactions_product_id_foreign` (`product_id`),
  ADD KEY `detail_transactions_layanan_id_foreign` (`layanan_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jadwals`
--
ALTER TABLE `jadwals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jadwals_bengkel_id_foreign` (`bengkel_id`);

--
-- Indexes for table `kecamatans`
--
ALTER TABLE `kecamatans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kelurahans`
--
ALTER TABLE `kelurahans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelurahans_kecamatan_id_foreign` (`kecamatan_id`);

--
-- Indexes for table `kendaraans`
--
ALTER TABLE `kendaraans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kendaraans_user_id_foreign` (`user_id`),
  ADD KEY `kendaraans_category_kendaraan_id_foreign` (`category_kendaraan_id`);

--
-- Indexes for table `layanans`
--
ALTER TABLE `layanans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `layanans_bengkel_id_foreign` (`bengkel_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `pemilik_bengkels`
--
ALTER TABLE `pemilik_bengkels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pemilik_bengkels_email_unique` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_bengkel_id_foreign` (`bengkel_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ratings_user_id_detail_transaction_id_unique` (`user_id`,`detail_transaction_id`),
  ADD KEY `ratings_transaction_id_foreign` (`transaction_id`),
  ADD KEY `ratings_product_id_transaction_id_index` (`product_id`,`transaction_id`);

--
-- Indexes for table `specialists`
--
ALTER TABLE `specialists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`),
  ADD KEY `transactions_bengkel_id_foreign` (`bengkel_id`),
  ADD KEY `transactions_booking_id_foreign` (`booking_id`),
  ADD KEY `transactions_product_id_foreign` (`product_id`),
  ADD KEY `transactions_layanan_id_foreign` (`layanan_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_kecamatan_id_foreign` (`kecamatan_id`),
  ADD KEY `users_kelurahan_id_foreign` (`kelurahan_id`);

--
-- Indexes for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdraw_requests_bengkel_id_foreign` (`bengkel_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bengkels`
--
ALTER TABLE `bengkels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bengkel_carts`
--
ALTER TABLE `bengkel_carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bengkel_specialist`
--
ALTER TABLE `bengkel_specialist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `category_kendaraans`
--
ALTER TABLE `category_kendaraans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detail_layanan_bookings`
--
ALTER TABLE `detail_layanan_bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_transactions`
--
ALTER TABLE `detail_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwals`
--
ALTER TABLE `jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kecamatans`
--
ALTER TABLE `kecamatans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kelurahans`
--
ALTER TABLE `kelurahans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `kendaraans`
--
ALTER TABLE `kendaraans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `layanans`
--
ALTER TABLE `layanans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `pemilik_bengkels`
--
ALTER TABLE `pemilik_bengkels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `specialists`
--
ALTER TABLE `specialists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bengkels`
--
ALTER TABLE `bengkels`
  ADD CONSTRAINT `bengkels_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bengkels_kelurahan_id_foreign` FOREIGN KEY (`kelurahan_id`) REFERENCES `kelurahans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bengkels_pemilik_id_foreign` FOREIGN KEY (`pemilik_id`) REFERENCES `pemilik_bengkels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bengkel_carts`
--
ALTER TABLE `bengkel_carts`
  ADD CONSTRAINT `bengkel_carts_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bengkel_carts_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bengkel_carts_layanan_id_foreign` FOREIGN KEY (`layanan_id`) REFERENCES `layanans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bengkel_carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bengkel_carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bengkel_specialist`
--
ALTER TABLE `bengkel_specialist`
  ADD CONSTRAINT `bengkel_specialist_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bengkel_specialist_specialist_id_foreign` FOREIGN KEY (`specialist_id`) REFERENCES `specialists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `detail_layanan_bookings`
--
ALTER TABLE `detail_layanan_bookings`
  ADD CONSTRAINT `detail_layanan_bookings_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_layanan_bookings_layanan_id_foreign` FOREIGN KEY (`layanan_id`) REFERENCES `layanans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `detail_transactions`
--
ALTER TABLE `detail_transactions`
  ADD CONSTRAINT `detail_transactions_layanan_id_foreign` FOREIGN KEY (`layanan_id`) REFERENCES `layanans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transactions_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jadwals`
--
ALTER TABLE `jadwals`
  ADD CONSTRAINT `jadwals_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kelurahans`
--
ALTER TABLE `kelurahans`
  ADD CONSTRAINT `kelurahans_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kendaraans`
--
ALTER TABLE `kendaraans`
  ADD CONSTRAINT `kendaraans_category_kendaraan_id_foreign` FOREIGN KEY (`category_kendaraan_id`) REFERENCES `category_kendaraans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kendaraans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `layanans`
--
ALTER TABLE `layanans`
  ADD CONSTRAINT `layanans_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_layanan_id_foreign` FOREIGN KEY (`layanan_id`) REFERENCES `layanans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_kelurahan_id_foreign` FOREIGN KEY (`kelurahan_id`) REFERENCES `kelurahans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD CONSTRAINT `withdraw_requests_bengkel_id_foreign` FOREIGN KEY (`bengkel_id`) REFERENCES `bengkels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
