-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Jan 29, 2026 at 11:34 AM
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
-- Database: `visual_vibe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_links`
--

CREATE TABLE `admin_links` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `icon` varchar(50) DEFAULT 'fas fa-link',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','editor','viewer') DEFAULT 'editor',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'Visual Vibe', 'admin@yourdomain.com', '$2y$10$Yn2PgSOIlhBBcNTQk5z4.ulxvpXPOhVuLfcHhUm48ZKaih6G21M4C', 'Administrator', 'admin', 1, '2026-01-29 15:18:55', 1, NULL, '2026-01-29 09:01:22', '2026-01-29 09:48:55');

-- --------------------------------------------------------

--
-- Table structure for table `carousel_slides`
--

CREATE TABLE `carousel_slides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carousel_slides`
--

INSERT INTO `carousel_slides` (`id`, `title`, `subtitle`, `image_url`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'sd', 'df', 'uploads/carousel/1769626535_WIN_20250103_11_09_51_Pro.jpg', 'sdfv', 1, 1, '2026-01-28 18:55:35', '2026-01-28 18:55:35');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `service_type` varchar(100) NOT NULL,
  `other_service` varchar(100) DEFAULT NULL,
  `project_type` enum('new','redesign') NOT NULL,
  `project_description` text NOT NULL,
  `design_style` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `budget_range` varchar(50) DEFAULT NULL,
  `contact_preference` set('whatsapp','call','email') NOT NULL,
  `best_time` time DEFAULT NULL,
  `hear_about` varchar(50) DEFAULT NULL,
  `other_source` varchar(100) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('pending','reviewed','contacted','archived') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `full_name`, `email`, `phone`, `company`, `location`, `service_type`, `other_service`, `project_type`, `project_description`, `design_style`, `file_path`, `deadline`, `budget_range`, `contact_preference`, `best_time`, `hear_about`, `other_source`, `additional_notes`, `status`, `created_at`, `updated_at`) VALUES
(83, 'Divyansh Nanavati', 'visualvibe.space@gmail.com', '09601982190', 'def', 'surat,gujarat', 'Logo Design', '', 'new', 'sdfv', 'sdf', NULL, '0000-00-00', '', 'call', '15:42:00', 'Google', '', '', 'pending', '2026-01-28 19:10:00', '2026-01-29 08:34:16');

-- --------------------------------------------------------

--
-- Table structure for table `flyers_posters`
--

CREATE TABLE `flyers_posters` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'Flyer',
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flyers_posters`
--

INSERT INTO `flyers_posters` (`id`, `title`, `description`, `category`, `image_url`, `thumbnail_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'dfgg', 'dwefgb', 'Travel', 'uploads/flyers/1769669172_Untitled design (15).png', '', 1, 1, '2026-01-29 06:46:12', '2026-01-29 06:46:12');

-- --------------------------------------------------------

--
-- Table structure for table `graphic_designs`
--

CREATE TABLE `graphic_designs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT '2D',
  `design_type` varchar(50) DEFAULT 'Graphic Design',
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `graphic_designs`
--

INSERT INTO `graphic_designs` (`id`, `title`, `description`, `category`, `design_type`, `image_url`, `thumbnail_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'swdefgb', 'dsfgb', 'Interior Design', '2D', 'uploads/graphics/1769669673_New Project (43).png', '', 1, 1, '2026-01-29 06:54:33', '2026-01-29 06:54:33');

-- --------------------------------------------------------

--
-- Table structure for table `logo_portfolio`
--

CREATE TABLE `logo_portfolio` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `category` varchar(100) DEFAULT 'Logo',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logo_portfolio`
--

INSERT INTO `logo_portfolio` (`id`, `title`, `description`, `image_url`, `category`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'fddg', 'dssfgbgfh', 'uploads/logos/1769667384_Untitled design (11)-modified.png', 'Brand Identity', 1, 1, '2026-01-29 06:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_videos`
--

CREATE TABLE `portfolio_videos` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `video_file` varchar(500) NOT NULL,
  `thumbnail_file` varchar(500) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(200) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `category` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `designation`, `image_url`, `category`, `display_order`, `is_active`, `created_at`) VALUES
(3, 'DISHANT BHAGAT', 'asd', 'uploads/team/1769626560_WIN_20250103_11_09_59_Pro (3).jpg', 'Head of Operations', 1, 1, '2026-01-28 18:56:00');

-- --------------------------------------------------------

--
-- Table structure for table `uiux_designs`
--

CREATE TABLE `uiux_designs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'UI Design',
  `design_type` varchar(50) DEFAULT 'Web UI',
  `prototype_url` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uiux_designs`
--

INSERT INTO `uiux_designs` (`id`, `title`, `description`, `category`, `design_type`, `prototype_url`, `image_url`, `thumbnail_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'dfghjh', 'dsfghvbjmn,m.', 'Prototype', 'Web UI', 'https://thewardrobefabrics.com/', 'uploads/uiux/1769670686_HAPPY NEW YEAR (2).png', '', 1, 1, '2026-01-29 07:11:26', '2026-01-29 07:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `website_portfolio`
--

CREATE TABLE `website_portfolio` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `website_url` varchar(500) NOT NULL,
  `category` varchar(100) DEFAULT 'Website',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_portfolio`
--

INSERT INTO `website_portfolio` (`id`, `title`, `description`, `image_url`, `website_url`, `category`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'wergth', 'dsfsgdhjgg', 'uploads/websites/1769668875_banner.jpeg', 'https://thewardrobefabrics.com/', 'E-commerce', 1, 1, '2026-01-29 06:41:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_links`
--
ALTER TABLE `admin_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `carousel_slides`
--
ALTER TABLE `carousel_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `flyers_posters`
--
ALTER TABLE `flyers_posters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `graphic_designs`
--
ALTER TABLE `graphic_designs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logo_portfolio`
--
ALTER TABLE `logo_portfolio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `portfolio_videos`
--
ALTER TABLE `portfolio_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uiux_designs`
--
ALTER TABLE `uiux_designs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `website_portfolio`
--
ALTER TABLE `website_portfolio`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_links`
--
ALTER TABLE `admin_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `carousel_slides`
--
ALTER TABLE `carousel_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `flyers_posters`
--
ALTER TABLE `flyers_posters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `graphic_designs`
--
ALTER TABLE `graphic_designs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `logo_portfolio`
--
ALTER TABLE `logo_portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `portfolio_videos`
--
ALTER TABLE `portfolio_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `uiux_designs`
--
ALTER TABLE `uiux_designs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `website_portfolio`
--
ALTER TABLE `website_portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
