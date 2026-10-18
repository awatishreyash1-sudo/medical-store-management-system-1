-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 12:55 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medical_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `doctor_id`
--

CREATE TABLE `doctor_id` (
  `id` int(11) NOT NULL,
  `d_code` varchar(50) NOT NULL,
  `d_name` varchar(100) NOT NULL,
  `d_address` text NOT NULL,
  `d_password` varchar(255) NOT NULL,
  `d_mobile` varchar(20) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_id`
--

INSERT INTO `doctor_id` (`id`, `d_code`, `d_name`, `d_address`, `d_password`, `d_mobile`, `datetime`) VALUES
(2, 'a', 'Karan', 'sangli', '1', '8010480410', '2025-02-11 20:36:11');

-- --------------------------------------------------------

--
-- Table structure for table `expiry_medicin`
--

CREATE TABLE `expiry_medicin` (
  `id` int(11) NOT NULL,
  `product` varchar(255) NOT NULL,
  `mrp` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `s_name` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `pay_mode` enum('Cash','Card','UPI') NOT NULL,
  `sales_pass` varchar(255) NOT NULL,
  `batch` varchar(100) NOT NULL,
  `ex_date` date NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `company` varchar(50) NOT NULL,
  `gst` decimal(30,5) NOT NULL,
  `new_qty` int(11) DEFAULT 0,
  `status` enum('pending','used') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expiry_medicin`
--

INSERT INTO `expiry_medicin` (`id`, `product`, `mrp`, `amount`, `s_name`, `city`, `pay_mode`, `sales_pass`, `batch`, `ex_date`, `datetime`, `company`, `gst`, `new_qty`, `status`) VALUES
(51, 'Aciloc', 7.00, 73.50, 'vaibhav', '', 'Cash', 'sham2', 'jhdeuy36', '2026-04-11', '2026-04-09 09:16:42', 'miraj', 5.00000, 7, 'pending'),
(52, 'Calpol', 4.00, 53.56, 'ruturaj', '', 'Cash', 'sham2', 'efwe23', '2026-04-11', '2026-04-11 05:00:22', 'sangli', 3.00000, 13, 'used'),
(53, 'Paracetamol', 5.00, 111.30, 'sham', '', 'Cash', 'rahul', 'fdfgf4', '2026-04-12', '2026-04-11 05:06:13', 'Sangli ', 6.00000, 21, 'pending'),
(54, 'abc', 4.00, 41.60, 'ruturaj', '', 'Cash', 'sham2', 'frdt45', '2026-04-11', '2026-04-11 08:36:12', 'sangli', 4.00000, 10, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_master`
--

CREATE TABLE `medicine_master` (
  `id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_master`
--

INSERT INTO `medicine_master` (`id`, `medicine_name`, `company_name`, `stock`, `created_at`) VALUES
(67, 'Paracetamol', 'Crocin', 0, '2026-03-28 06:05:06'),
(74, 'Dolo 650', 'Micro Labs', 0, '2026-04-08 13:36:49'),
(75, 'Calpol', 'GSK (GlaxoSmithKline)', 20, '2026-04-08 13:37:21'),
(76, 'Combiflam', 'Sanofi', 25, '2026-04-08 13:37:41'),
(77, 'Zerodol-P', 'Ipca Laboratories', 39, '2026-04-08 13:38:07'),
(78, 'Saridon', 'Bayer', 0, '2026-04-08 13:38:26'),
(79, 'Pantocid', 'Alkem', 0, '2026-04-08 13:39:08'),
(80, 'Aciloc', 'Cadila', 25, '2026-04-08 13:39:39'),
(81, 'Augmentin', 'GSK', 10, '2026-04-08 13:40:06'),
(82, 'Ciplox', 'Cipla', 0, '2026-04-08 13:40:34'),
(83, 'abc', 'GSK', 0, '2026-04-11 06:31:26');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return`
--

CREATE TABLE `purchase_return` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `return_qty` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `gst` decimal(5,2) DEFAULT NULL,
  `base_amount` decimal(10,2) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `return_amount` decimal(10,2) DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return_medicine`
--

CREATE TABLE `purchase_return_medicine` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `sr_no` int(11) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `batch` varchar(100) DEFAULT NULL,
  `gst` decimal(5,2) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `ex_date` date DEFAULT NULL,
  `return_qty` int(11) DEFAULT NULL,
  `return_amount` decimal(10,2) DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_return_medicine`
--

INSERT INTO `purchase_return_medicine` (`id`, `bill_id`, `sr_no`, `product`, `batch`, `gst`, `mrp`, `ex_date`, `return_qty`, `return_amount`, `return_date`) VALUES
(28, 158, 1, 'Zerodol-P', 'fgr54', 4.00, 5.00, '2026-04-13', 10, 52.00, '2026-04-11 12:08:16');

-- --------------------------------------------------------

--
-- Table structure for table `salesman`
--

CREATE TABLE `salesman` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `color` varchar(20) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salesman`
--

INSERT INTO `salesman` (`id`, `code`, `name`, `address`, `password`, `mobile`, `color`, `datetime`) VALUES
(25, 'SM001', 'rahul', 'sangli', 'rahul', '34540984545', '', '2025-07-24 16:43:28'),
(26, 'SM002', 'karan', 'sangli', '22', '343546434354', '', '2025-07-25 16:13:46'),
(28, 'SM003', 'pranav', 'sangli', '33', '4587384783', '', '2025-07-25 16:14:39'),
(29, 'SM004', 'sham2', 'sangli', '1', '4534576878', '', '2025-07-30 14:35:53'),
(30, 'SM005', 'karan', 'sangli', '1', '7554875456', '', '2026-02-24 12:24:50'),
(32, 'SM006', 'kalash', 'sangli', '123', '8737639876', '', '2026-02-24 12:26:00');

-- --------------------------------------------------------

--
-- Table structure for table `sales_bills`
--

CREATE TABLE `sales_bills` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `salesman_name` varchar(255) NOT NULL,
  `f_total_amount` decimal(10,2) NOT NULL,
  `city` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_bills`
--

INSERT INTO `sales_bills` (`id`, `patient_name`, `total_amount`, `salesman_name`, `f_total_amount`, `city`, `mobile`, `datetime`) VALUES
(121, 'samarth', 31.50, 'sham2', 31.50, 'sangli', '9354857483', '2026-04-09 12:39:40'),
(122, 'sammed ', 44.10, 'rahul', 44.10, 'Dudhgaon', '3454654332', '2026-04-11 08:15:26'),
(123, 'pranv', 42.40, 'sham2', 42.40, 'Dudhgaon', '6453565756', '2026-04-11 12:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `sales_medicin`
--

CREATE TABLE `sales_medicin` (
  `id` int(5) NOT NULL,
  `bill_id` int(5) NOT NULL,
  `sr_no` int(5) NOT NULL,
  `product` varchar(250) NOT NULL,
  `qty1` int(50) NOT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `batch` varchar(250) NOT NULL,
  `ex_date` date NOT NULL,
  `pay_mode` varchar(200) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `gst` decimal(30,5) NOT NULL,
  `return_qty` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_medicin`
--

INSERT INTO `sales_medicin` (`id`, `bill_id`, `sr_no`, `product`, `qty1`, `mrp`, `amount`, `batch`, `ex_date`, `pay_mode`, `datetime`, `gst`, `return_qty`) VALUES
(160, 121, 1, '269', 3, 10.00, 31.50, 'jhdeuy36', '2026-04-11', 'Cash', '2026-04-09 12:39:40', 5.00000, 0),
(161, 122, 1, '273', 6, 7.00, 44.10, 'csd3e', '2026-04-19', 'Card', '2026-04-11 08:15:26', 5.00000, 0),
(162, 123, 1, '278', 5, 8.00, 42.40, 'dfrgtdr34', '2026-05-01', 'UPI', '2026-04-11 12:04:31', 6.00000, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sales_returns`
--

CREATE TABLE `sales_returns` (
  `id` int(11) NOT NULL,
  `sales_medicin_id` int(11) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `return_qty` int(11) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `gst` decimal(5,2) DEFAULT NULL,
  `base_amount` decimal(10,2) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `return_amount` decimal(10,2) DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ss_bills`
--

CREATE TABLE `ss_bills` (
  `id` int(11) NOT NULL,
  `s_name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `salesman_name` varchar(255) NOT NULL,
  `f_total_amount` decimal(10,2) NOT NULL,
  `city` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) NOT NULL,
  `pay_mode` varchar(255) NOT NULL,
  `expiry_return_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ss_bills`
--

INSERT INTO `ss_bills` (`id`, `s_name`, `total_amount`, `salesman_name`, `f_total_amount`, `city`, `mobile`, `datetime`, `address`, `pay_mode`, `expiry_return_amount`) VALUES
(149, 'vaibhav', 73.50, 'sham2', 73.50, '', '3242345676', '2026-04-09 08:20:04', 'miraj', 'Cash', 0.00),
(150, 'ruturaj', 391.70, 'karan', 391.70, '', '3245656785', '2026-04-09 12:45:04', 'sangli', 'Cash', 0.00),
(151, 'ruturaj', 147.00, 'sham2', 147.00, '', '3245656785', '2026-04-11 08:11:53', 'sangli', 'UPI', 0.00),
(152, 'ruturaj', 806.00, 'sham2', 806.00, '', '3245656785', '2026-04-11 08:28:14', 'sangli', 'Cash', 0.00),
(153, 'ruturaj', 53.56, 'sham2', 53.56, '', '3245656785', '2026-04-11 08:30:12', 'sangli', 'Cash', 0.00),
(154, 'samarth', 63.00, 'sham2', 63.00, '', '7554875435', '2026-04-11 08:31:42', 'sangli ', 'Cash', 0.00),
(155, 'sham', 111.30, 'rahul', 111.30, '', '8737639876', '2026-04-11 08:35:58', 'Sangli ', 'Cash', 0.00),
(156, 'ruturaj', 159.00, 'sham2', 105.44, '', '3245656785', '2026-04-11 12:04:00', 'sangli', 'Cash', 53.56),
(157, 'ruturaj', 41.60, 'sham2', 41.60, '', '3245656785', '2026-04-11 12:05:18', 'sangli', 'Cash', 0.00),
(158, 'samarth', 135.50, 'sham2', 135.50, '', '7554875435', '2026-04-11 12:07:29', 'sangli ', 'Cash', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `ss_medicine`
--

CREATE TABLE `ss_medicine` (
  `id` int(5) NOT NULL,
  `bill_id` int(5) NOT NULL,
  `sr_no` int(5) NOT NULL,
  `product` varchar(250) NOT NULL,
  `qty1` int(50) NOT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `batch` varchar(250) NOT NULL,
  `ex_date` date DEFAULT NULL,
  `pay_mode` varchar(200) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `gst` decimal(30,5) NOT NULL,
  `return_qty` int(11) DEFAULT 0,
  `new_qty` int(50) NOT NULL,
  `discount` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ss_medicine`
--

INSERT INTO `ss_medicine` (`id`, `bill_id`, `sr_no`, `product`, `qty1`, `mrp`, `amount`, `batch`, `ex_date`, `pay_mode`, `datetime`, `gst`, `return_qty`, `new_qty`, `discount`) VALUES
(269, 149, 1, 'Aciloc', 10, 7.00, 73.50, 'jhdeuy36', '2026-04-11', 'Cash', '2026-04-09 08:20:04', 5.00000, 0, 0, 0),
(270, 150, 1, 'Augmentin', 30, 4.00, 127.20, 'df34d3', '2026-04-11', 'Cash', '2026-04-09 12:45:04', 6.00000, 0, 0, 0),
(271, 150, 2, 'Calpol', 20, 5.00, 107.00, 'fer34', '2026-04-19', 'Cash', '2026-04-09 12:45:04', 7.00000, 0, 20, 0),
(272, 150, 3, 'Combiflam', 25, 6.00, 157.50, 'dew3', '2026-04-17', 'Cash', '2026-04-09 12:45:04', 5.00000, 0, 25, 0),
(273, 151, 1, 'Zerodol-P', 35, 4.00, 147.00, 'csd3e', '2026-04-19', 'UPI', '2026-04-11 08:11:53', 5.00000, 0, 29, 0),
(274, 152, 1, 'Aciloc', 155, 5.00, 806.00, 'fef34', '2026-04-11', 'Cash', '2026-04-11 08:28:14', 4.00000, 0, 0, 0),
(275, 153, 1, 'Calpol', 13, 4.00, 53.56, 'efwe23', '2026-04-11', 'Cash', '2026-04-11 08:30:12', 3.00000, 0, 0, 0),
(276, 154, 1, 'Pantocid', 10, 6.00, 63.00, 'frdfg345', '2026-04-11', 'Cash', '2026-04-11 08:31:42', 5.00000, 0, 0, 0),
(277, 155, 1, 'Paracetamol', 21, 5.00, 111.30, 'fdfgf4', '2026-04-12', 'Cash', '2026-04-11 08:35:58', 6.00000, 0, 0, 0),
(278, 156, 1, 'Aciloc', 30, 5.00, 159.00, 'dfrgtdr34', '2026-05-01', 'Cash', '2026-04-11 12:04:00', 6.00000, 0, 25, 0),
(279, 157, 1, 'abc', 10, 4.00, 41.60, 'frdt45', '2026-04-11', 'Cash', '2026-04-11 12:05:18', 4.00000, 0, 0, 0),
(280, 158, 1, 'Zerodol-P', 20, 5.00, 104.00, 'fgr54', '2026-04-13', 'Cash', '2026-04-11 12:07:29', 4.00000, 0, 10, 0),
(281, 158, 2, 'Augmentin', 10, 3.00, 31.50, 'fe55', '2026-04-12', 'Cash', '2026-04-11 12:07:29', 5.00000, 0, 10, 0);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_master`
--

CREATE TABLE `supplier_master` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gst_number` varchar(25) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_master`
--

INSERT INTO `supplier_master` (`id`, `supplier_name`, `company_name`, `contact_number`, `email`, `address`, `gst_number`, `created_at`) VALUES
(1, 'samarth', 'dolo', '7554875435', 'samarth1@gmail.com', 'sangli ', 'efe4545dfd', '2025-07-12 19:29:30'),
(4, 'vaibhav', 'abcd', '3242345676', 'vaibhav@gmail.com', 'miraj', '4566677gfd', '2025-07-25 16:16:10'),
(5, 'ruturaj', 'xyz', '3245656785', 'ruturaj@gmail.com', 'sangli', '34rc5t543', '2025-07-25 16:16:57'),
(6, 'sham', 'GSK', '8737639876', 'sham@gmail.com', 'Sangli ', '834347', '2026-02-24 11:56:57'),
(8, 'abs', 'GSK', '8737639876', 'awatishreyash1@gmail.com', 'Sangli ', '834347844874747', '2026-04-08 19:21:55');

-- --------------------------------------------------------

--
-- Table structure for table `s_medicin`
--

CREATE TABLE `s_medicin` (
  `id` int(11) NOT NULL,
  `product` varchar(255) NOT NULL,
  `mrp` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `s_name` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `pay_mode` enum('Cash','Card','UPI','Credit') NOT NULL,
  `sales_pass` varchar(255) NOT NULL,
  `batch` varchar(100) NOT NULL,
  `ex_date` date NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `company` varchar(50) NOT NULL,
  `gst` decimal(30,5) NOT NULL,
  `new_qty` int(11) DEFAULT 0,
  `salesman_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','salesman') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `code`, `name`, `password`, `role`) VALUES
(14, 'SM001', 'rahul', 'rahul', 'salesman'),
(15, 'SM002', 'karan', '22', 'salesman'),
(16, 'SM003', 'pranav', '33', 'salesman'),
(17, 'SM004', 'sham2', '1', 'salesman'),
(19, 'SM006', 'kalash', '123', 'salesman'),
(21, '', 'rahulac', 'Shreyash@1', 'salesman'),
(23, 'SM008', 'asd', 'Asdfgh@1', 'salesman'),
(26, '', 'shreyash awati', '1', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `doctor_id`
--
ALTER TABLE `doctor_id`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `d_code` (`d_code`),
  ADD UNIQUE KEY `d_mobile` (`d_mobile`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `expiry_medicin`
--
ALTER TABLE `expiry_medicin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `medicine_master`
--
ALTER TABLE `medicine_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `medicine_name` (`medicine_name`);

--
-- Indexes for table `purchase_return`
--
ALTER TABLE `purchase_return`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_return_ibfk_1` (`medicine_id`);

--
-- Indexes for table `purchase_return_medicine`
--
ALTER TABLE `purchase_return_medicine`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salesman`
--
ALTER TABLE `salesman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `code_2` (`code`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sales_bills`
--
ALTER TABLE `sales_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `datetime` (`datetime`);

--
-- Indexes for table `sales_medicin`
--
ALTER TABLE `sales_medicin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`,`sr_no`),
  ADD KEY `id` (`id`),
  ADD KEY `product` (`product`);

--
-- Indexes for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_medicin_id` (`sales_medicin_id`);

--
-- Indexes for table `ss_bills`
--
ALTER TABLE `ss_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `datetime` (`datetime`);

--
-- Indexes for table `ss_medicine`
--
ALTER TABLE `ss_medicine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`,`sr_no`),
  ADD KEY `product` (`product`);

--
-- Indexes for table `supplier_master`
--
ALTER TABLE `supplier_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_medicin`
--
ALTER TABLE `s_medicin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_2` (`id`),
  ADD KEY `id` (`id`,`product`),
  ADD KEY `s_name` (`s_name`),
  ADD KEY `id_3` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `doctor_id`
--
ALTER TABLE `doctor_id`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expiry_medicin`
--
ALTER TABLE `expiry_medicin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `medicine_master`
--
ALTER TABLE `medicine_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `purchase_return`
--
ALTER TABLE `purchase_return`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchase_return_medicine`
--
ALTER TABLE `purchase_return_medicine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `salesman`
--
ALTER TABLE `salesman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `sales_bills`
--
ALTER TABLE `sales_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `sales_medicin`
--
ALTER TABLE `sales_medicin`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `sales_returns`
--
ALTER TABLE `sales_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `ss_bills`
--
ALTER TABLE `ss_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `ss_medicine`
--
ALTER TABLE `ss_medicine`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=282;

--
-- AUTO_INCREMENT for table `supplier_master`
--
ALTER TABLE `supplier_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `s_medicin`
--
ALTER TABLE `s_medicin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `purchase_return`
--
ALTER TABLE `purchase_return`
  ADD CONSTRAINT `purchase_return_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `s_medicin` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD CONSTRAINT `sales_returns_ibfk_1` FOREIGN KEY (`sales_medicin_id`) REFERENCES `sales_medicin` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
