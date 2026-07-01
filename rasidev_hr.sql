-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 04:38 AM
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
-- Database: `rasidev_hr`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Asset','Liability','Equity','Income','Expense','COGS') NOT NULL,
  `description` text DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `type`, `description`, `balance`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Accounts Receivable', 'Asset', NULL, -17517165.40, 'Active', '2026-01-28 07:19:08', '2026-02-23 15:56:42'),
(2, 'Accounts Payable', 'Liability', NULL, -1853.00, 'Active', '2026-01-28 07:19:08', '2026-02-01 09:26:05'),
(3, 'Cash', 'Asset', NULL, 39375.00, 'Active', '2026-01-28 07:19:08', '2026-02-23 15:26:01'),
(4, 'Bank Account', 'Asset', NULL, 18110282.00, 'Active', '2026-01-28 07:19:08', '2026-02-01 09:26:05'),
(5, 'Sales Income', 'Income', NULL, 546230.00, 'Active', '2026-01-28 07:19:08', '2026-02-23 15:56:42'),
(6, 'Cost of Goods Sold', 'COGS', NULL, 0.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:19:08'),
(7, 'Cost of Purchase', 'COGS', NULL, 0.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:19:08'),
(8, 'Customer Discount', 'Expense', NULL, 25.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:34:58'),
(9, 'Vendor Payment Discount', 'Income', NULL, 0.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:19:08'),
(10, 'Sales Return', 'Expense', NULL, 0.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:19:08'),
(11, 'Purchase Return', 'Income', NULL, 0.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:19:08'),
(12, 'GST Payable', 'Liability', NULL, 88479.40, 'Active', '2026-01-28 07:19:08', '2026-02-23 15:56:42'),
(13, 'GST Receivable', 'Asset', NULL, 0.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:19:08'),
(14, 'Mahimai Income', 'Income', NULL, 20.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:34:58'),
(15, 'Postal Charges', 'Income', NULL, 320.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:34:58'),
(16, 'Agent Commission Expense', 'Expense', NULL, 0.00, 'Active', '2026-01-28 07:19:08', '2026-01-28 07:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) UNSIGNED NOT NULL,
  `owner_type` enum('customer','vendor') NOT NULL,
  `owner_id` int(11) UNSIGNED NOT NULL,
  `address_type` enum('billing','shipping') NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `state_id` int(11) UNSIGNED NOT NULL,
  `country_id` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `owner_type`, `owner_id`, `address_type`, `address_line1`, `address_line2`, `city`, `pincode`, `state_id`, `country_id`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'customer', 1, 'billing', '18/3A, Krishnan Kovil Street, C.P Kannaiah Road', 'Opp: Gopinath Engineering Works', 'RASIPURAM', '637408', 1, 1, 0, '2026-01-27 03:05:47', '2026-01-30 11:07:34'),
(4, 'customer', 2, 'billing', '18/3A, Krishnan Kovil Street, C.P Kannaiah Road', 'Opp: Gopinath Engineering Works', 'RASIPURAM', '637408', 1, 1, 1, '2026-01-27 03:08:04', '2026-01-27 03:08:04'),
(5, 'customer', 2, 'shipping', '18/3A, Krishnan Kovil Street, C.P Kannaiah Road', 'Opp: Gopinath Engineering Works', 'RASIPURAM', '637408', 1, 1, 1, '2026-01-30 11:07:12', '2026-01-30 11:07:12'),
(6, 'customer', 1, 'billing', '18/3A, Krishnan Kovil Street, C.P Kannaiah Road', 'Opp: Gopinath Engineering Works', 'RASIPURAM', '637408', 14, 1, 1, '2026-01-30 11:07:34', '2026-01-30 11:07:34'),
(7, 'customer', 1, 'shipping', '18/3A, Krishnan Kovil Street, C.P Kannaiah Road', 'Opp: Gopinath Engineering Works', 'RASIPURAM', '637408', 14, 1, 1, '2026-01-30 11:07:34', '2026-01-30 11:07:34'),
(9, 'customer', 3, 'billing', 'LIGH-211, APHB Colony', 'Moulali', 'Hyderabad', '500040', 35, 1, 1, '2026-02-01 12:25:36', '2026-02-01 12:25:36'),
(10, 'customer', 3, 'shipping', 'LIGH-211, APHB Colony', 'Moulali', 'Hyderabad', '500040', 35, 1, 1, '2026-02-01 12:25:36', '2026-02-01 12:25:36'),
(11, 'customer', 4, 'billing', 'Lig 211, Moulali', '', 'Hyderabad', '500039', 35, 1, 1, '2026-02-01 12:25:37', '2026-02-01 12:25:37'),
(12, 'customer', 4, 'shipping', 'Lig 211, Moulali', '', 'Hyderabad', '500039', 35, 1, 1, '2026-02-01 12:25:37', '2026-02-01 12:25:37'),
(13, 'customer', 8, 'billing', 'Main Road,\nAMLA TOLA,', '', 'Katihar', '854105', 15, 1, 1, '2026-02-01 12:25:41', '2026-02-01 12:25:41'),
(14, 'customer', 8, 'shipping', 'Main Road,\nAMLA TOLA,', '', 'Katihar', '854105', 15, 1, 1, '2026-02-01 12:25:41', '2026-02-01 12:25:41'),
(15, 'customer', 9, 'billing', '15/16, Pasupathi Nagar,\n1st Street, \nNear Mazhil Mahal,', 'Kulamangalam Main Road,\nMela Panagadi,', 'Madurai.', '625017', 1, 1, 1, '2026-02-01 12:25:42', '2026-02-01 12:25:42'),
(16, 'customer', 9, 'shipping', '15/16, Pasupathi Nagar,\n1st Street, \nNear Mazhil Mahal,', 'Kulamangalam Main Road,\nMela Panagadi,', 'Madurai.', '625017', 1, 1, 1, '2026-02-01 12:25:42', '2026-02-01 12:25:42'),
(17, 'customer', 10, 'billing', 'Near Post Office Line,', 'New Madhavaram (P.O),\nY.S.R Kadapa (D.T)', 'Kadapa', '516002', 4, 1, 1, '2026-02-01 12:25:43', '2026-02-01 12:25:43'),
(18, 'customer', 10, 'shipping', 'Near Post Office Line,', 'New Madhavaram (P.O),\nY.S.R Kadapa (D.T)', 'Kadapa', '516002', 4, 1, 1, '2026-02-01 12:25:43', '2026-02-01 12:25:43'),
(19, 'customer', 11, 'billing', '66, Sanjeevappa Lane, Avenue road cross', '', 'Bangalore', '560002', 3, 1, 1, '2026-02-01 12:25:44', '2026-02-01 12:25:44'),
(20, 'customer', 11, 'shipping', '66, Sanjeevappa Lane, Avenue road cross', '', 'Bangalore', '560002', 3, 1, 1, '2026-02-01 12:25:44', '2026-02-01 12:25:44'),
(21, 'customer', 12, 'billing', '482, HMT Layout, 80Ft Road,', 'R.T Nagar, Bus Depot', 'Bengaluru', '560032', 3, 1, 1, '2026-02-01 12:25:45', '2026-02-01 12:25:45'),
(22, 'customer', 12, 'shipping', '482, HMT Layout, 80Ft Road,', 'R.T Nagar, Bus Depot', 'Bengaluru', '560032', 3, 1, 1, '2026-02-01 12:25:45', '2026-02-01 12:25:45'),
(23, 'customer', 322, 'billing', '1st Floor, Sacred Heart Commercial Complex,', 'Opp Bus Stand, Main Road,\nBajpe,', 'Mangalore.', '574 142', 3, 1, 1, '2026-02-01 12:25:46', '2026-02-01 12:25:46'),
(24, 'customer', 322, 'shipping', '1st Floor, Sacred Heart Commercial Complex,', 'Opp Bus Stand, Main Road,\nBajpe,', 'Mangalore.', '574 142', 3, 1, 1, '2026-02-01 12:25:46', '2026-02-01 12:25:46'),
(25, 'customer', 13, 'billing', 'Market Road', '', 'Macherial', '504208', 35, 1, 1, '2026-02-01 12:25:47', '2026-02-01 12:25:47'),
(26, 'customer', 13, 'shipping', 'Market Road', '', 'Macherial', '504208', 35, 1, 1, '2026-02-01 12:25:47', '2026-02-01 12:25:47'),
(27, 'customer', 14, 'billing', 'M.Colony Park Site,\nB-14,', 'Shop No:6, Road No: 6,\nVikhroli(W).', 'Mumbai.', '400079', 5, 1, 1, '2026-02-01 12:25:48', '2026-02-01 12:25:48'),
(28, 'customer', 15, 'billing', 'SHOP NO: 22 11 7 268,\nGROUND FLOOR,', 'PVT MARKET, KOTHAPET X ROAD,SAROOR NAGAR,', 'Hyderabad', '500035', 35, 1, 1, '2026-02-01 12:25:49', '2026-02-01 12:25:49'),
(29, 'customer', 15, 'shipping', 'SHOP NO: 22 11 7 268,\nGROUND FLOOR,', 'PVT MARKET, KOTHAPET X ROAD,SAROOR NAGAR,', 'Hyderabad', '500035', 35, 1, 1, '2026-02-01 12:25:49', '2026-02-01 12:25:49'),
(30, 'customer', 16, 'billing', 'Near Muthalamman Kovil', 'Elumalai', 'Madurai', '625535', 1, 1, 1, '2026-02-01 12:25:50', '2026-02-01 12:25:50'),
(31, 'customer', 16, 'shipping', 'Near Muthalamman Kovil', 'Elumalai', 'Madurai', '625535', 1, 1, 1, '2026-02-01 12:25:50', '2026-02-01 12:25:50'),
(32, 'customer', 18, 'billing', 'No: 21-2-554,\n1ST FLOOR, \nU STREET,', 'URDU SHAREEF GALLI,\nGHANSI BAZAR,\nPATEL MARKET,', 'HYDERABAD', '500002', 35, 1, 1, '2026-02-01 12:25:52', '2026-02-01 12:25:52'),
(33, 'customer', 18, 'shipping', 'No: 21-2-554,\n1ST FLOOR, \nU STREET,', 'URDU SHAREEF GALLI,\nGHANSI BAZAR,\nPATEL MARKET,', 'HYDERABAD', '500002', 35, 1, 1, '2026-02-01 12:25:52', '2026-02-01 12:25:52'),
(34, 'customer', 19, 'billing', 'No: 1-7-70 TO 73,\nMASHA ALLAH BUILDING,', 'SAROJINI DEVI ROAD,\nBESIDE PARADISE HOTEL,', 'SECUNDERABAD', '500003', 35, 1, 1, '2026-02-01 12:25:53', '2026-02-01 12:25:53'),
(35, 'customer', 19, 'shipping', 'No: 1-7-70 TO 73,\nMASHA ALLAH BUILDING,', 'SAROJINI DEVI ROAD,\nBESIDE PARADISE HOTEL,', 'SECUNDERABAD', '500003', 35, 1, 1, '2026-02-01 12:25:53', '2026-02-01 12:25:53'),
(36, 'customer', 22, 'billing', '# 2,', 'Old Bangalore Main Road,', 'Hosur.', '635109', 1, 1, 1, '2026-02-01 12:25:56', '2026-02-01 12:25:56'),
(37, 'customer', 22, 'shipping', '# 2,', 'Old Bangalore Main Road,', 'Hosur.', '635109', 1, 1, 1, '2026-02-01 12:25:56', '2026-02-01 12:25:56'),
(38, 'customer', 23, 'billing', '# 5,\nDharmapuri Main Road,', 'Opp. IOB,\nPappireddipatti,', 'Dharmapuri.', '636905', 1, 1, 1, '2026-02-01 12:25:57', '2026-02-01 12:25:57'),
(39, 'customer', 23, 'shipping', '# 5,\nDharmapuri Main Road,', 'Opp. IOB,\nPappireddipatti,', 'Dharmapuri.', '636905', 1, 1, 1, '2026-02-01 12:25:57', '2026-02-01 12:25:57'),
(40, 'customer', 24, 'billing', '37, Kallukkumiyalpatti,', 'Kulathur,', 'Pudukottai.', '622504', 1, 1, 1, '2026-02-01 12:25:58', '2026-02-01 12:25:58'),
(41, 'customer', 24, 'shipping', '37, Kallukkumiyalpatti,', 'Kulathur,', 'Pudukottai.', '622504', 1, 1, 1, '2026-02-01 12:25:58', '2026-02-01 12:25:58'),
(42, 'customer', 25, 'billing', 'No: 74-1/21,\nManickavasagar Street,', 'No. 2,\nAmmapet,', 'Salem.', '636 003', 1, 1, 1, '2026-02-01 12:26:01', '2026-02-01 12:26:01'),
(43, 'customer', 25, 'shipping', 'No: 74-1/21,\nManickavasagar Street,', 'No. 2,\nAmmapet,', 'Salem.', '636 003', 1, 1, 1, '2026-02-01 12:26:01', '2026-02-01 12:26:01'),
(44, 'customer', 26, 'billing', 'Badvel', '', 'Kadapa District', '516227', 4, 1, 1, '2026-02-01 12:26:02', '2026-02-01 12:26:02'),
(45, 'customer', 26, 'shipping', 'Badvel', '', 'Kadapa District', '516227', 4, 1, 1, '2026-02-01 12:26:02', '2026-02-01 12:26:02'),
(46, 'customer', 27, 'billing', 'No: 633,\nTHIRUVANANTHAPURAM MAIN ROAD,', 'KRISHNAN KOVIL,', 'NAGARCOIL', '629001', 1, 1, 1, '2026-02-01 12:26:05', '2026-02-01 12:26:05'),
(47, 'customer', 27, 'shipping', 'No: 633,\nTHIRUVANANTHAPURAM MAIN ROAD,', 'KRISHNAN KOVIL,', 'NAGARCOIL', '629001', 1, 1, 1, '2026-02-01 12:26:05', '2026-02-01 12:26:05'),
(48, 'customer', 28, 'billing', 'No: 2 - 148,', 'Sivalayam Street,\nMain Road,', 'Velivennu.', '534329', 4, 1, 1, '2026-02-01 12:26:06', '2026-02-01 12:26:06'),
(49, 'customer', 28, 'shipping', 'No: 2 - 148,', 'Sivalayam Street,\nMain Road,', 'Velivennu.', '534329', 4, 1, 1, '2026-02-01 12:26:06', '2026-02-01 12:26:06'),
(50, 'customer', 29, 'billing', 'Andhra University', 'Exhibition Ground', 'Visakhapatnam.', '530013', 4, 1, 1, '2026-02-01 12:26:07', '2026-02-01 12:26:07'),
(51, 'customer', 29, 'shipping', 'Andhra University', 'Exhibition Ground', 'Visakhapatnam.', '530013', 4, 1, 1, '2026-02-01 12:26:07', '2026-02-01 12:26:07'),
(52, 'customer', 30, 'billing', '38/105, Lingay Gounder Street', 'B1 Baby illam,\nOdakkadu', 'Tiruppur', '641602', 1, 1, 1, '2026-02-01 12:26:08', '2026-02-01 12:26:08'),
(53, 'customer', 30, 'shipping', '38/105, Lingay Gounder Street', 'B1 Baby illam,\nOdakkadu', 'Tiruppur', '641602', 1, 1, 1, '2026-02-01 12:26:08', '2026-02-01 12:26:08'),
(54, 'customer', 31, 'billing', 'NO: 4/135/1,\nNear Saibaba Temple,', 'Kadali Road,', 'Jaggannapeta.', '533249', 4, 1, 1, '2026-02-01 12:26:09', '2026-02-01 12:26:09'),
(55, 'customer', 31, 'shipping', 'NO: 4/135/1,\nNear Saibaba Temple,', 'Kadali Road,', 'Jaggannapeta.', '533249', 4, 1, 1, '2026-02-01 12:26:09', '2026-02-01 12:26:09'),
(56, 'customer', 323, 'billing', 'Basaveshwar Complex,\nAbove Rajkamal Hoetl', 'Main Road\nDist Bidar', 'Basavakalyan', '585327', 3, 1, 1, '2026-02-01 12:26:09', '2026-02-01 12:26:09'),
(57, 'customer', 323, 'shipping', 'Basaveshwar Complex,\nAbove Rajkamal Hoetl', 'Main Road\nDist Bidar', 'Basavakalyan', '585327', 1, 1, 1, '2026-02-01 12:26:09', '2026-02-01 12:26:09'),
(58, 'customer', 32, 'billing', 'KHATA NO 153/163, PLOT NO 173/491,', 'UNIT NO 04 GOLAPBAG,\nNEAR JUBLIEE LIBRARY SCHOOL,BARIPADA.', 'MAYURBHANJ.', '757001', 26, 1, 1, '2026-02-01 12:26:10', '2026-02-01 12:26:10'),
(59, 'customer', 32, 'shipping', 'KHATA NO 153/163, PLOT NO 173/491,', 'UNIT NO 04 GOLAPBAG,\nNEAR JUBLIEE LIBRARY SCHOOL,BARIPADA.', 'MAYURBHANJ.', '757001', 26, 1, 1, '2026-02-01 12:26:10', '2026-02-01 12:26:10'),
(60, 'customer', 33, 'billing', 'Main Road', 'Suryapet Dist', 'Kodad', '508206', 35, 1, 1, '2026-02-01 12:26:11', '2026-02-01 12:26:11'),
(61, 'customer', 33, 'shipping', 'Main Road', 'Suryapet Dist', 'Kodad', '508206', 35, 1, 1, '2026-02-01 12:26:11', '2026-02-01 12:26:11'),
(62, 'customer', 35, 'billing', 'No: 16/2,3,\n1st Floor, 1st Cross,\nSapthagiri Lay Out,', 'Kogilu Road,\nMaruthi Nagar,\nYelahanka,', 'Bangalore.', '560064', 3, 1, 1, '2026-02-01 12:26:13', '2026-02-01 12:26:13'),
(63, 'customer', 35, 'shipping', 'No: 16/2,3,\n1st Floor, 1st Cross,\nSapthagiri Lay Out,', 'Kogilu Road,\nMaruthi Nagar,\nYelahanka,', 'Bangalore.', '560064', 3, 1, 1, '2026-02-01 12:26:13', '2026-02-01 12:26:13'),
(64, 'customer', 36, 'billing', 'Plot No:70,\nRoad No,1/A,\nNear Ranga Sai Super Market,\nWater Tank Road,', 'Opp S.N Reddy Gardens, \nSai Ram Nagar Colony, \nChampapet.', 'Hyderabad,', '500079', 35, 1, 1, '2026-02-01 12:26:14', '2026-02-01 12:26:14'),
(65, 'customer', 36, 'shipping', 'Plot No:70,\nRoad No,1/A,\nNear Ranga Sai Super Market,', 'Water Tank Road, Opp S N Reddy Gardens,\nSai Ram Nagar Colony,\nChampapet,', 'Hyderabad,', '500079', 35, 1, 1, '2026-02-01 12:26:14', '2026-02-01 12:26:14'),
(66, 'customer', 37, 'billing', 'Market Road', '', 'Jagtial', '505327', 35, 1, 1, '2026-02-01 12:26:15', '2026-02-01 12:26:15'),
(67, 'customer', 37, 'shipping', 'Market Road', '', 'Jagtial', '505327', 35, 1, 1, '2026-02-01 12:26:15', '2026-02-01 12:26:15'),
(68, 'customer', 38, 'billing', 'Ram Bazar', '', 'JAGTIAL', '505327', 35, 1, 1, '2026-02-01 12:26:16', '2026-02-01 12:26:16'),
(69, 'customer', 38, 'shipping', 'Ram Bazar', '', 'JAGTIAL', '505327', 35, 1, 1, '2026-02-01 12:26:16', '2026-02-01 12:26:16'),
(70, 'customer', 39, 'billing', 'M.G Road', '', 'Raichur', '584101', 3, 1, 1, '2026-02-01 12:26:17', '2026-02-01 12:26:17'),
(71, 'customer', 39, 'shipping', 'M.G Road', '', 'Raichur', '584101', 3, 1, 1, '2026-02-01 12:26:17', '2026-02-01 12:26:17'),
(72, 'customer', 40, 'billing', 'D.no: 12-05-20, Near Taj Lodge', 'Puthur Raju Line, Station Road', 'Chirala', '523155', 4, 1, 1, '2026-02-01 12:26:18', '2026-02-01 12:26:18'),
(73, 'customer', 40, 'shipping', 'D.no: 12-05-20, Near Taj Lodge', 'Puthur Raju Line, Station Road', 'Chirala', '523155', 4, 1, 1, '2026-02-01 12:26:18', '2026-02-01 12:26:18'),
(74, 'customer', 41, 'billing', 'BUILDING NO/FLAT NO: 5-3-265', 'VIDYANAGAR COLONY', 'KAMAREDDY', '503111', 35, 1, 1, '2026-02-01 12:26:19', '2026-02-01 12:26:19'),
(75, 'customer', 41, 'shipping', 'BUILDING NO/FLAT NO: 5-3-265', 'VIDYANAGAR COLONY', 'KAMAREDDY', '503111', 35, 1, 1, '2026-02-01 12:26:19', '2026-02-01 12:26:19'),
(76, 'customer', 42, 'billing', 'No: 1193-H Block,\n2nd Street,', '12th Main Road,\nAnna Nagar,', 'Chennai.', '600040', 1, 1, 1, '2026-02-01 12:26:20', '2026-02-01 12:26:20'),
(77, 'customer', 42, 'shipping', 'No: 1193-H Block,\n2nd Street,', '12th Main Road,\nAnna Nagar,', 'Chennai.', '600040', 1, 1, 1, '2026-02-01 12:26:20', '2026-02-01 12:26:20'),
(78, 'customer', 324, 'billing', 'Aminagad Gold Drinks House,1st Floor', 'Subhas Road,', 'Dharwad', '580001', 3, 1, 1, '2026-02-01 12:26:21', '2026-02-01 12:26:21'),
(79, 'customer', 324, 'shipping', 'Aminagad Gold Drinks House,1st Floor', 'Subhas Road,', 'Dharwad', '580001', 3, 1, 1, '2026-02-01 12:26:21', '2026-02-01 12:26:21'),
(80, 'customer', 349, 'billing', '8-3-949/1/B, Kamma Sangham Buiding', 'Ameerpet', 'Hyderabad', '500073', 35, 1, 1, '2026-02-01 12:26:23', '2026-02-01 12:26:23'),
(81, 'customer', 349, 'shipping', '8-3-949/1/B, Kamma Sangham Buiding', 'Ameerpet', 'Hyderabad', '500073', 35, 1, 1, '2026-02-01 12:26:23', '2026-02-01 12:26:23'),
(82, 'customer', 44, 'billing', '2-1/2, IDA Phase - 1, Patancheru', '', 'Sangareddy District', '502319', 35, 1, 1, '2026-02-01 12:26:24', '2026-02-01 12:26:24'),
(83, 'customer', 44, 'shipping', '2-1/2, IDA Phase - 1, Patancheru', '', 'Sangareddy District', '502319', 35, 1, 1, '2026-02-01 12:26:24', '2026-02-01 12:26:24'),
(84, 'customer', 325, 'billing', '8-3-949/1/B, Kamma Sangam Building', 'Ameerpet', 'Hyderabad', '500073', 35, 1, 1, '2026-02-01 12:26:25', '2026-02-01 12:26:25'),
(85, 'customer', 325, 'shipping', '8-3-949/1/B, Kamma Sangam Building', 'Ameerpet', 'Hyderabad', '500073', 35, 1, 1, '2026-02-01 12:26:25', '2026-02-01 12:26:25'),
(86, 'customer', 45, 'billing', 'Opp: Head Post Office, R.P Road,', 'Patny Centre', 'Secundrabad', '500003', 35, 1, 1, '2026-02-01 12:26:26', '2026-02-01 12:26:26'),
(87, 'customer', 45, 'shipping', 'Opp: Head Post Office, R.P Road,', 'Patny Centre', 'Secundrabad', '500003', 35, 1, 1, '2026-02-01 12:26:26', '2026-02-01 12:26:26'),
(88, 'customer', 46, 'billing', 'D.No. : 16-1-658', 'C.M.R Centre, G.T.Road', 'Nellore', '524001', 4, 1, 1, '2026-02-01 12:26:27', '2026-02-01 12:26:27'),
(89, 'customer', 46, 'shipping', 'D.No. : 16-1-658', 'C.M.R Centre, G.T.Road', 'Nellore', '524001', 4, 1, 1, '2026-02-01 12:26:27', '2026-02-01 12:26:27'),
(90, 'customer', 47, 'billing', 'Trunk Road', '', 'Nellore', '524001', 4, 1, 1, '2026-02-01 12:26:28', '2026-02-01 12:26:28'),
(91, 'customer', 47, 'shipping', 'Trunk Road', '', 'Nellore', '524001', 4, 1, 1, '2026-02-01 12:26:28', '2026-02-01 12:26:28'),
(92, 'customer', 48, 'billing', '18-883, Church Street', '', 'Chittoor', '517001', 4, 1, 1, '2026-02-01 12:26:31', '2026-02-01 12:26:31'),
(93, 'customer', 48, 'shipping', '18-883, Church Street', '', 'Chittoor', '517001', 4, 1, 1, '2026-02-01 12:26:31', '2026-02-01 12:26:31'),
(94, 'customer', 49, 'billing', '23,Main Road', '', 'MANAMADURAI', '630606', 1, 1, 1, '2026-02-01 12:26:32', '2026-02-01 12:26:32'),
(95, 'customer', 49, 'shipping', '23,Main Road', '', 'MANAMADURAI', '630606', 1, 1, 1, '2026-02-01 12:26:32', '2026-02-01 12:26:32'),
(96, 'customer', 50, 'billing', '109/2, Sankari Road', 'Near Old Bus Stand', 'Tiruchengode', '637211', 1, 1, 1, '2026-02-01 12:26:33', '2026-02-01 12:26:33'),
(97, 'customer', 50, 'shipping', '109/2, Sankari Road', 'Near Old Bus Stand', 'Tiruchengode', '637211', 1, 1, 1, '2026-02-01 12:26:33', '2026-02-01 12:26:33'),
(98, 'customer', 51, 'billing', 'No: 7/13,\nK B. Dasan Road,', 'Alwarpet,', 'Chennai.', '600018', 1, 1, 1, '2026-02-01 12:26:34', '2026-02-01 12:26:34'),
(99, 'customer', 51, 'shipping', 'No: 7/13,\nK B. Dasan Road,', 'Alwarpet,', 'Chennai.', '600018', 1, 1, 1, '2026-02-01 12:26:34', '2026-02-01 12:26:34'),
(100, 'customer', 52, 'billing', 'NO: 1,', 'KALAIMAGAL KALVI NILAYAM ROAD,', 'ERODE', '638001', 1, 1, 1, '2026-02-01 12:26:35', '2026-02-01 12:26:35'),
(101, 'customer', 52, 'shipping', 'NO: 1,', 'KALAIMAGAL KALVI NILAYAM ROAD,', 'ERODE', '638001', 1, 1, 1, '2026-02-01 12:26:35', '2026-02-01 12:26:35'),
(102, 'customer', 53, 'billing', '25, Jagath Narayani Apartments, Bank Road,  Near Dadaji Mandir', '', 'Patna', '800001', 15, 1, 1, '2026-02-01 12:26:36', '2026-02-01 12:26:36'),
(103, 'customer', 53, 'shipping', '25, Jagath Narayani Apartments, Bank Road,  Near Dadaji Mandir', '', 'Patna', '800001', 15, 1, 1, '2026-02-01 12:26:36', '2026-02-01 12:26:36'),
(104, 'customer', 350, 'billing', 'D.NO: 32-9-43/1, Beside Ayyappa Swamy Temple,', 'Tunglam Village, Harijana Jaggayyapalem, Sheelanagar', 'Visakhapatnam', '530012', 4, 1, 1, '2026-02-01 12:26:37', '2026-02-01 12:26:37'),
(105, 'customer', 350, 'shipping', 'D.NO: 32-9-43/1, Beside Ayyappa Swamy Temple,', 'Tunglam Village, Harijana Jaggayyapalem, Sheelanagar', 'Visakhapatnam', '530012', 4, 1, 1, '2026-02-01 12:26:37', '2026-02-01 12:26:37'),
(106, 'customer', 55, 'billing', 'S/O Picchi Ramaiah,\n14/85, Anjaneyaluswamy Temple,', 'Ipuru Palem(Rural),\nIpurupalem, Prakasam,', 'Chirala,', '523166', 4, 1, 1, '2026-02-01 12:26:38', '2026-02-01 12:26:38'),
(107, 'customer', 55, 'shipping', 'S/O Picchi Ramaiah,\n14/85, Anjaneyaluswamy Temple,', 'Ipuru Palem(Rural),\nIpurupalem, Prakasam,', 'Chirala,', '523166', 4, 1, 1, '2026-02-01 12:26:38', '2026-02-01 12:26:38'),
(108, 'customer', 56, 'billing', '# 750, Vishwas Nilaya, D V Gundappa Road, Maasti Venkatesh Lyengar Road,', 'BTS Layout, Arekere,\nBG Road,', 'Bangalore', '560076', 3, 1, 1, '2026-02-01 12:26:39', '2026-02-01 12:26:39'),
(109, 'customer', 56, 'shipping', '# 750, Vishwas Nilaya, D V Gundappa Road, Maasti Venkatesh Lyengar Road,', 'BTS Layout, Arekere,\nBG Road,', 'Bangalore', '560076', 3, 1, 1, '2026-02-01 12:26:39', '2026-02-01 12:26:39'),
(110, 'customer', 57, 'billing', '10-16,\nTheppakullam Street,', 'No.3 (Maidan),', 'Coimbatore.', '641 001', 1, 1, 1, '2026-02-01 12:26:40', '2026-02-01 12:26:40'),
(111, 'customer', 57, 'shipping', '10-16,\nTheppakullam Street,', 'No.3 (Maidan),', 'Coimbatore.', '641 001', 1, 1, 1, '2026-02-01 12:26:40', '2026-02-01 12:26:40'),
(112, 'customer', 59, 'billing', 'Steamer Road', 'Narsapur, West Godavari DT', 'Narsapur', '534275', 4, 1, 1, '2026-02-01 12:26:42', '2026-02-01 12:26:42'),
(113, 'customer', 59, 'shipping', 'Steamer Road', 'Narsapur, West Godavari DT', 'Narsapur', '534275', 4, 1, 1, '2026-02-01 12:26:42', '2026-02-01 12:26:42'),
(114, 'customer', 60, 'billing', '4/11, New Madhavaram, vontimitta M', '', 'Kadapa', '516247', 4, 1, 1, '2026-02-01 12:26:45', '2026-02-01 12:26:45'),
(115, 'customer', 60, 'shipping', '4/11, New Madhavaram, vontimitta M', '', 'Kadapa', '516247', 4, 1, 1, '2026-02-01 12:26:45', '2026-02-01 12:26:45'),
(116, 'customer', 61, 'billing', 'No: 3895, Devaraja Market Building', 'Sayyaji Rao Road', 'Mysore', '570001', 3, 1, 1, '2026-02-01 12:26:46', '2026-02-01 12:26:46'),
(117, 'customer', 61, 'shipping', 'No: 3895, Devaraja Market Building', 'Sayyaji Rao Road', 'Mysore', '570001', 3, 1, 1, '2026-02-01 12:26:46', '2026-02-01 12:26:46'),
(118, 'customer', 326, 'billing', 'Poonja Arcade,', 'K.S. Rao Road', 'Mangalore', '575 001', 3, 1, 1, '2026-02-01 12:26:47', '2026-02-01 12:26:47'),
(119, 'customer', 326, 'shipping', 'Poonja Arcade,', 'K.S. Rao Road', 'Mangalore', '575 001', 3, 1, 1, '2026-02-01 12:26:47', '2026-02-01 12:26:47'),
(120, 'customer', 63, 'billing', 'H.No: 4-248,\nPlot No:10,\nSarvodaya Nagar,', 'Opp: More Super Market,\nMeerpet, Karmanghat,\nRangareddy,', 'Hyderabad.', '500097', 35, 1, 1, '2026-02-01 12:26:49', '2026-02-01 12:26:49'),
(121, 'customer', 63, 'shipping', 'H.No: 4-248,\nPlot No:10,\nSarvodaya Nagar,', 'Opp: More Super Market,\nMeerpet, Karmanghat,\nRangareddy,', 'Hyderabad.', '500097', 35, 1, 1, '2026-02-01 12:26:49', '2026-02-01 12:26:49'),
(122, 'customer', 64, 'billing', 'Main Road', 'Narayanpet', 'Mahabubnagar', '509210', 35, 1, 1, '2026-02-01 12:26:50', '2026-02-01 12:26:50'),
(123, 'customer', 64, 'shipping', 'Main Road', 'Narayanpet', 'Mahabubnagar', '509210', 35, 1, 1, '2026-02-01 12:26:50', '2026-02-01 12:26:50'),
(124, 'customer', 65, 'billing', 'Citizen Club Complex', 'Maddanapeta Road, Narsampet', 'Warangal', '506132', 35, 1, 1, '2026-02-01 12:26:51', '2026-02-01 12:26:51'),
(125, 'customer', 65, 'shipping', 'Citizen Club Complex', 'Maddanapeta Road, Narsampet', 'Warangal', '506132', 35, 1, 1, '2026-02-01 12:26:51', '2026-02-01 12:26:51'),
(126, 'customer', 327, 'billing', 'Shop No 2-1-565/1,Opposite Shankarmutt, Beside Nandini Hotel,', 'OU Road, Nallakunta', 'Hyderabad', '500044', 35, 1, 1, '2026-02-01 12:26:53', '2026-02-01 12:26:53'),
(127, 'customer', 327, 'shipping', 'Shop No 2-1-565/1,Opposite Shankarmutt, Beside Nandini Hotel,', 'OU Road, Nallakunta', 'Hyderabad', '500044', 35, 1, 1, '2026-02-01 12:26:53', '2026-02-01 12:26:53'),
(128, 'customer', 66, 'billing', '157/1B, A.P.C Roy Road', 'Near Khanna Cinema', 'Kolkatta', '700006', 24, 1, 1, '2026-02-01 12:26:54', '2026-02-01 12:26:54'),
(129, 'customer', 66, 'shipping', '157/1B, A.P.C Roy Road', 'Near Khanna Cinema', 'Kolkatta', '700006', 24, 1, 1, '2026-02-01 12:26:54', '2026-02-01 12:26:54'),
(130, 'customer', 67, 'billing', 'APC ROAD', '', 'KOLKATA', '700006', 24, 1, 1, '2026-02-01 12:26:55', '2026-02-01 12:26:55'),
(131, 'customer', 67, 'shipping', 'APC ROAD', '', 'KOLKATA', '700006', 24, 1, 1, '2026-02-01 12:26:55', '2026-02-01 12:26:55'),
(132, 'customer', 328, 'billing', 'Link Road', 'Kayamkulam', 'Alappuzha', '690502', 2, 1, 1, '2026-02-01 12:26:56', '2026-02-01 12:26:56'),
(133, 'customer', 328, 'shipping', 'Link Road', 'Kayamkulam', 'Alappuzha', '690502', 2, 1, 1, '2026-02-01 12:26:56', '2026-02-01 12:26:56'),
(134, 'customer', 68, 'billing', '6-4-10/3,\nKotipalli Vari Street,', 'Narasapuram,', 'West Godavari (D.T)', '534275', 4, 1, 1, '2026-02-01 12:26:57', '2026-02-01 12:26:57'),
(135, 'customer', 68, 'shipping', '6-4-10/3,\nKotipalli Vari Street,', 'Narasapuram,', 'West Godavari (D.T)', '534275', 4, 1, 1, '2026-02-01 12:26:57', '2026-02-01 12:26:57'),
(136, 'customer', 69, 'billing', 'N.H.Byepass,\nVadakara,', '', 'Calicut', '673104', 2, 1, 1, '2026-02-01 12:26:58', '2026-02-01 12:26:58'),
(137, 'customer', 69, 'shipping', 'N.H.Byepass,\nVadakara,', '', 'Calicut', '673104', 2, 1, 1, '2026-02-01 12:26:58', '2026-02-01 12:26:58'),
(138, 'customer', 70, 'billing', '3-8, Ground Main Road', 'Dharmasagar', 'Warangal', '506142', 35, 1, 1, '2026-02-01 12:26:59', '2026-02-01 12:26:59'),
(139, 'customer', 70, 'shipping', '3-8, Ground Main Road', 'Dharmasagar', 'Warangal', '506142', 35, 1, 1, '2026-02-01 12:26:59', '2026-02-01 12:26:59'),
(140, 'customer', 72, 'billing', 'Handloom Heritage\n40/2/1,', 'Purna Das Road,', 'Kolkatta', '700029', 24, 1, 1, '2026-02-01 12:27:01', '2026-02-01 12:27:01'),
(141, 'customer', 72, 'shipping', 'Handloom Heritage\n40/2/1,', 'Purna Das Road,', 'Kolkatta', '700029', 24, 1, 1, '2026-02-01 12:27:01', '2026-02-01 12:27:01'),
(142, 'customer', 73, 'billing', '9/9-4A,\nMunippan Kovil Street,', 'Tharamangalam,', 'Salem.', '636502', 1, 1, 1, '2026-02-01 12:27:02', '2026-02-01 12:27:02'),
(143, 'customer', 73, 'shipping', '9/9-4A,\nMunippan Kovil Street,', 'Tharamangalam,', 'Salem.', '636502', 1, 1, 1, '2026-02-01 12:27:02', '2026-02-01 12:27:02'),
(144, 'customer', 74, 'billing', 'MAIN ROAD, BRODIPET', '', 'GUNTUR', '522002', 4, 1, 1, '2026-02-01 12:27:03', '2026-02-01 12:27:03'),
(145, 'customer', 74, 'shipping', 'MAIN ROAD, BRODIPET', '', 'GUNTUR', '522002', 4, 1, 1, '2026-02-01 12:27:03', '2026-02-01 12:27:03'),
(146, 'customer', 75, 'billing', 'H.NO: 8-39/7, Flat No. F-I, Kalyani Estates,', 'Konark Theatre Road, Dilsukhnagar', 'Hyderabad', '500060', 35, 1, 1, '2026-02-01 12:27:04', '2026-02-01 12:27:04'),
(147, 'customer', 75, 'shipping', 'H.NO: 8-39/7, Flat No. F-I, Kalyani Estates,', 'Konark Theatre Road, Dilsukhnagar', 'Hyderabad', '500060', 35, 1, 1, '2026-02-01 12:27:04', '2026-02-01 12:27:04'),
(148, 'customer', 76, 'billing', 'No: 29,\nNTI Layout,', 'Vidyaranyapura Main Road,', 'Bangalore.', '560097', 3, 1, 1, '2026-02-01 12:27:05', '2026-02-01 12:27:05'),
(149, 'customer', 76, 'shipping', 'No: 29,\nNTI Layout,', 'Vidyaranyapura Main Road,', 'Bangalore.', '560097', 3, 1, 1, '2026-02-01 12:27:05', '2026-02-01 12:27:05'),
(150, 'customer', 77, 'billing', 'Plot No: 140/A,\nHigh-Tension Main Road,', 'Eshwarpuri Colony,\nSainikpuri,', 'Secunderabad.', '500094', 35, 1, 1, '2026-02-01 12:27:06', '2026-02-01 12:27:06'),
(151, 'customer', 77, 'shipping', 'Plot No: 140/A,\nHigh-Tension Main Road,', 'Eshwarpuri Colony,\nSainikpuri,', 'Secunderabad.', '500094', 35, 1, 1, '2026-02-01 12:27:06', '2026-02-01 12:27:06'),
(152, 'customer', 78, 'billing', 'Geethanjali Shoper City', 'Geethanjali Road', 'Udupi', '576101', 3, 1, 1, '2026-02-01 12:27:07', '2026-02-01 12:27:07'),
(153, 'customer', 78, 'shipping', 'Geethanjali Shoper City', 'Geethanjali Road', 'Udupi', '576101', 3, 1, 1, '2026-02-01 12:27:07', '2026-02-01 12:27:07'),
(154, 'customer', 80, 'billing', '(Ref: Nurullah Bacchoo),\nUsha Enterprises,', '4 Tottee Lane,\n2nd Floor,\nNew Market Area,', 'Kolkata.', '700016', 24, 1, 1, '2026-02-01 12:27:08', '2026-02-01 12:27:08'),
(155, 'customer', 80, 'shipping', '(Ref: Nurullah Bacchoo),\nUsha Enterprises,', '4 Tottee Lane,\n2nd Floor,\nNew Market Area,', 'Kolkata.', '700016', 24, 1, 1, '2026-02-01 12:27:08', '2026-02-01 12:27:08'),
(156, 'customer', 79, 'billing', 'Hemonto (Triesh Surmi)\nHB 120,\nHB Block,', 'Sector - 3,\nSalt Lake City,\nBidhan Nagar,', 'Kolkata.', '700106', 24, 1, 1, '2026-02-01 12:27:09', '2026-02-01 12:27:09'),
(157, 'customer', 79, 'shipping', 'Hemonto (Triesh Surmi)\nHB 120,\nHB Block,', 'Sector - 3,\nSalt Lake City,\nBidhan Nagar,', 'Kolkata.', '700106', 24, 1, 1, '2026-02-01 12:27:09', '2026-02-01 12:27:09'),
(158, 'customer', 81, 'billing', 'KACHERY ROAD,', 'OPP AXIS BANK,', 'ROURKELA,', '769012', 26, 1, 1, '2026-02-01 12:27:10', '2026-02-01 12:27:10'),
(159, 'customer', 81, 'shipping', 'KACHERY ROAD,', 'OPP AXIS BANK,', 'ROURKELA,', '769012', 26, 1, 1, '2026-02-01 12:27:10', '2026-02-01 12:27:10'),
(160, 'customer', 84, 'billing', '4/1, BRODIPET, MAIN BAZAR', '', 'GUNTUR', '522002', 4, 1, 1, '2026-02-01 12:27:13', '2026-02-01 12:27:13'),
(161, 'customer', 84, 'shipping', '4/1, BRODIPET, MAIN BAZAR', '', 'GUNTUR', '522002', 4, 1, 1, '2026-02-01 12:27:13', '2026-02-01 12:27:13'),
(162, 'customer', 85, 'billing', '1-1-1788, Flat No 202', 'Sri Lakshmi Venkateswara Apartments,\nGandhi Nagar,\nRTC X Roads', 'Hyderabad', '500020', 35, 1, 1, '2026-02-01 12:27:14', '2026-02-01 12:27:14'),
(163, 'customer', 85, 'shipping', '1-1-1788, Flat No 202', 'Sri Lakshmi Venkateswara Apartments,\nGandhi Nagar,\nRTC X Roads', 'Hyderabad', '500020', 35, 1, 1, '2026-02-01 12:27:14', '2026-02-01 12:27:14'),
(164, 'customer', 86, 'billing', 'No: 3-8, Ground,\nMain Road,', 'Dharmasagar,', 'Warangal', '506142', 35, 1, 1, '2026-02-01 12:27:15', '2026-02-01 12:27:15'),
(165, 'customer', 86, 'shipping', 'No: 3-8, Ground,\nMain Road,', 'Dharmasagar,', 'Warangal', '506142', 35, 1, 1, '2026-02-01 12:27:15', '2026-02-01 12:27:15'),
(166, 'customer', 87, 'billing', 'GUPS Venkottumukku,', 'Ven Code(po),\nVattapara,', 'Tiruvandrum.', '695028', 2, 1, 1, '2026-02-01 12:27:16', '2026-02-01 12:27:16'),
(167, 'customer', 87, 'shipping', 'GUPS Venkottumukku,', 'Ven Code(po),\nVattapara,', 'Tiruvandrum.', '695028', 2, 1, 1, '2026-02-01 12:27:16', '2026-02-01 12:27:16'),
(168, 'customer', 89, 'billing', 'H1G, Near Temple Bus Stop, 3rd Phase', 'KPHB Colony,\nKukatpally', 'Hyderabad', '500072', 35, 1, 1, '2026-02-01 12:27:17', '2026-02-01 12:27:17'),
(169, 'customer', 89, 'shipping', 'H1G, Near Temple Bus Stop, 3rd Phase', 'KPHB Colony,\nKukatpally', 'Hyderabad', '500072', 35, 1, 1, '2026-02-01 12:27:17', '2026-02-01 12:27:17'),
(170, 'customer', 90, 'billing', 'No: 1', 'Ganesh Nagar,', 'KARUR', '639002', 1, 1, 1, '2026-02-01 12:27:18', '2026-02-01 12:27:18'),
(171, 'customer', 90, 'shipping', 'No: 1', 'Ganesh Nagar,', 'KARUR', '639002', 1, 1, 1, '2026-02-01 12:27:18', '2026-02-01 12:27:18'),
(172, 'customer', 92, 'billing', 'SAKEENA PLAZA,\nWard-Ill 486/3 AND 486/17,', 'MANGALORE RAOD,', 'KARKALA.', '574104', 3, 1, 1, '2026-02-01 12:27:20', '2026-02-01 12:27:20'),
(173, 'customer', 92, 'shipping', 'SAKEENA PLAZA,\nWard-Ill 486/3 AND 486/17,', 'MANGALORE RAOD,', 'KARKALA.', '574104', 3, 1, 1, '2026-02-01 12:27:20', '2026-02-01 12:27:20'),
(174, 'customer', 93, 'billing', '133/E,RASH BEHARI AVENUE,', '', 'Kolkatta', '700029', 24, 1, 1, '2026-02-01 12:27:21', '2026-02-01 12:27:21'),
(175, 'customer', 93, 'shipping', '133/E,RASH BEHARI AVENUE,', '', 'Kolkatta', '700029', 24, 1, 1, '2026-02-01 12:27:21', '2026-02-01 12:27:21'),
(176, 'customer', 94, 'billing', 'N.H-34,', 'Rathbari,\n', 'Malda', '732101', 24, 1, 1, '2026-02-01 12:27:25', '2026-02-01 12:27:25'),
(177, 'customer', 94, 'shipping', 'N.H-34,', 'Rathbari,\n', 'Malda', '732101', 24, 1, 1, '2026-02-01 12:27:25', '2026-02-01 12:27:25'),
(178, 'customer', 96, 'billing', 'Jawli Kadai Bazar,', 'Usilampatti.', 'Madurai', '625532', 1, 1, 1, '2026-02-01 12:27:27', '2026-02-01 12:27:27'),
(179, 'customer', 96, 'shipping', 'Jawli Kadai Bazar,', 'Usilampatti.', 'Madurai', '625532', 1, 1, 1, '2026-02-01 12:27:27', '2026-02-01 12:27:27'),
(180, 'customer', 97, 'billing', 'Badahat,', 'Tinimuhani, College square road,', 'Kendrapara', '754211', 26, 1, 1, '2026-02-01 12:27:28', '2026-02-01 12:27:28'),
(181, 'customer', 97, 'shipping', 'Badahat,', 'Tinimuhani, College square road,', 'Kendrapara', '754211', 26, 1, 1, '2026-02-01 12:27:28', '2026-02-01 12:27:28'),
(182, 'customer', 98, 'billing', 'No5', 'Vasantha nagar', 'Pallipalayam', '638008', 1, 1, 1, '2026-02-01 12:27:29', '2026-02-01 12:27:29'),
(183, 'customer', 98, 'shipping', 'No5', 'Vasantha nagar', 'Pallipalayam', '638008', 1, 1, 1, '2026-02-01 12:27:29', '2026-02-01 12:27:29'),
(184, 'customer', 99, 'billing', 'No.15,16 Imayam Complex,\nKallakulam', 'NK Road', 'Thanjavur', '613001', 1, 1, 1, '2026-02-01 12:27:30', '2026-02-01 12:27:30'),
(185, 'customer', 99, 'shipping', 'No.15,16 Imayam Complex,\nKallakulam', 'NK Road', 'Thanjavur', '613001', 1, 1, 1, '2026-02-01 12:27:30', '2026-02-01 12:27:30'),
(186, 'customer', 100, 'billing', 'No: 104A/1,\nM G Road,', 'Koranadu,', 'Mayiladuthurai.', '609002', 1, 1, 1, '2026-02-01 12:27:31', '2026-02-01 12:27:31'),
(187, 'customer', 100, 'shipping', 'No: 104A/1,\nM G Road,', 'Koranadu,', 'Mayiladuthurai.', '609002', 1, 1, 1, '2026-02-01 12:27:31', '2026-02-01 12:27:31'),
(188, 'customer', 101, 'billing', 'NO.2/13, \n2ND WARD 1ST CROSS,', 'KAMAKSHAMMA LAYOUT,\nYELAHANKA OLD TOWN,', 'BANGALORE.', '560064', 3, 1, 1, '2026-02-01 12:27:32', '2026-02-01 12:27:32'),
(189, 'customer', 101, 'shipping', 'NO.2/13, \n2ND WARD 1ST CROSS,', 'KAMAKSHAMMA LAYOUT,\nYELAHANKA OLD TOWN,', 'BANGALORE.', '560064', 3, 1, 1, '2026-02-01 12:27:32', '2026-02-01 12:27:32'),
(190, 'customer', 102, 'billing', '#21452/', 'VP Kovil Street,\nM Chavadi,', 'Thanjavur.', '613001', 1, 1, 1, '2026-02-01 12:27:33', '2026-02-01 12:27:33'),
(191, 'customer', 102, 'shipping', '#21452/', 'VP Kovil Street,\nM Chavadi,', 'Thanjavur.', '613001', 1, 1, 1, '2026-02-01 12:27:33', '2026-02-01 12:27:33'),
(192, 'customer', 329, 'billing', 'No: 414, S.M. Road', 'Thiruvannamalai Dist', 'Arni', '632301', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(193, 'customer', 329, 'shipping', 'No: 414, S.M. Road', 'Thiruvannamalai Dist', 'Arni', '632301', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(194, 'customer', 104, 'billing', '36/11, \nC.H.B. COLONY,', 'STREET NO.4,\nTIRUCHENGODE,', 'Namakkal.', '637211', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(195, 'customer', 104, 'shipping', '36/11, \nC.H.B. COLONY,', 'STREET NO.4,\nTIRUCHENGODE,', 'Namakkal.', '637211', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(196, 'customer', 105, 'billing', 'Rajangana Road,', 'Badagupet,', 'Udupi.', '576 101', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(197, 'customer', 105, 'shipping', 'Rajangana Road,', 'Badagupet,', 'Udupi.', '576 101', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(198, 'customer', 106, 'billing', 'Opp. K.S.R.T.C. Bus Stand, NH Road', 'Kaniyapuram', 'Thiruvananthapuram', '695301', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(199, 'customer', 106, 'shipping', 'Opp. K.S.R.T.C. Bus Stand, NH Road', 'Kaniyapuram', 'Thiruvananthapuram', '695301', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(200, 'customer', 330, 'billing', 'No. 51/2, B.V.K Iyengar Road,', 'Opp: Sri Ram Temple', 'Bangalore', '560053', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(201, 'customer', 330, 'shipping', 'No. 51/2, B.V.K Iyengar Road,', 'Opp: Sri Ram Temple', 'Bangalore', '560053', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(202, 'customer', 107, 'billing', 'No.53/1, Quadrant Road,', 'Near Taj Hotel Shivajinagar', 'Bangalore', '560001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(203, 'customer', 107, 'shipping', 'No.53/1, Quadrant Road,', 'Near Taj Hotel Shivajinagar', 'Bangalore', '560001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(204, 'customer', 108, 'billing', 'No: 1/509,', 'KULLAKKAPALAYAM,\nPOLLACHI,', 'Coimbatore', '642002', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(205, 'customer', 108, 'shipping', 'No: 1/509,', 'KULLAKKAPALAYAM,\nPOLLACHI,', 'Coimbatore', '642002', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(206, 'customer', 109, 'billing', 'BP VIII/3A,\nSingle Street,', 'Balaramapuram Ottatheruvu,\nBalaramapurm,', 'Thiruvananthapuram,', '695501', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(207, 'customer', 109, 'shipping', 'BP VIII/3A,\nSingle Street,', 'Balaramapuram Ottatheruvu,\nBalaramapurm,', 'Thiruvananthapuram,', '695501', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(208, 'customer', 110, 'billing', 'B.P.VIII/1,\nSumukha Vilas,', 'Single Street,\nBalaramapuram,', 'Thiruvananthapuram.', '695501', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(209, 'customer', 110, 'shipping', 'B.P.VIII/1,\nSumukha Vilas,', 'Single Street,\nBalaramapuram,', 'Thiruvananthapuram.', '695501', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(210, 'customer', 111, 'billing', 'Old No: 1/296, New No: 7/49', 'Manickasamy Kovil Street,\nT.Subbulapuram', 'Aundipatti', '625536', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(211, 'customer', 111, 'shipping', 'Old No: 1/296, New No: 7/49', 'Manickasamy Kovil Street,\nT.Subbulapuram', 'Aundipatti', '625536', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(212, 'customer', 112, 'billing', '41/327-2, Sankarapuram', 'Opp: Kanara Bank', 'KADAPA', '516002', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(213, 'customer', 112, 'shipping', '41/327-2, Sankarapuram', 'Opp: Kanara Bank', 'KADAPA', '516002', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(214, 'customer', 113, 'billing', 'No: 4,\nVaiyapuri Nagar,', '3rd Cross,', 'Karur.', '639002', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(215, 'customer', 113, 'shipping', 'No: 4,\nVaiyapuri Nagar,', '3rd Cross,', 'Karur.', '639002', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(216, 'customer', 114, 'billing', 'NEAR MAHARANAPRATAP BHAWAN,\nRUNICHA DHAM,', 'OPP SHANKAR PARWATI MANDIR, \nARYA KUMAR ROAD,\nMACHUATOLI,', 'PATNA,', '800004', 15, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(217, 'customer', 114, 'shipping', 'NEAR MAHARANAPRATAP BHAWAN,\nRUNICHA DHAM,', 'OPP SHANKAR PARWATI MANDIR, \nARYA KUMAR ROAD,\nMACHUATOLI,', 'PATNA,', '800004', 15, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(218, 'customer', 115, 'billing', '4/21-4, Kotha Madhavaram 1,2,3 wards, siddavatam mandal', '', 'Kadapa', '516247', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(219, 'customer', 115, 'shipping', '4/21-4, Kotha Madhavaram 1,2,3 wards, siddavatam mandal', '', 'Kadapa', '516247', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(220, 'customer', 116, 'billing', 'Shop no: C-856, NGO\'S Colony', 'Vanasthalipuram', 'Hyderabad', '500070', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(221, 'customer', 116, 'shipping', 'Shop no: C-856, NGO\'S Colony', 'Vanasthalipuram', 'Hyderabad', '500070', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(222, 'customer', 119, 'billing', 'House No:7-2-324,', 'Ricca Bazar,', 'Khammam.', '507003', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(223, 'customer', 119, 'shipping', 'House No:7-2-324,', 'Ricca Bazar,', 'Khammam.', '507003', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(224, 'customer', 120, 'billing', 'OM SANKEERNA 1-99/1,\nISWARAMANGALA,', 'NETTANIGE MUDNOOR,', 'Puttur. (D.K).', '574 201', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(225, 'customer', 120, 'shipping', 'OM SANKEERNA 1-99/1,\nISWARAMANGALA,', 'NETTANIGE MUDNOOR,', 'Puttur. (D.K).', '574 201', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(226, 'customer', 121, 'billing', 'M.K Towers,', 'Convent Road,', 'Kollam,', '691 001', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(227, 'customer', 121, 'shipping', 'M.K Towers,', 'Convent Road,', 'Kollam,', '691 001', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(228, 'customer', 123, 'billing', 'No: 56, Dharmaraja Koil Street', 'Kosapalayam, Arni, Tiruvannamalai', 'Arni', '632301', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(229, 'customer', 123, 'shipping', 'No: 56, Dharmaraja Koil Street', 'Kosapalayam, Arni, Tiruvannamalai', 'Arni', '632301', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(230, 'customer', 124, 'billing', '114/896,Gujarat Housing Board.Khokhara.Hotkeswar.', 'Amariwadi Post.', 'AHMEDABAD', '380026', 29, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(231, 'customer', 124, 'shipping', '114/896,Gujarat Housing Board,', 'Hotkeswar,\nAmariwadi Post.', 'AHMEDABAD', '380026', 29, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(232, 'customer', 125, 'billing', 'Subhas Road,', '', 'Dharwad', '580001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(233, 'customer', 125, 'shipping', 'Subhas Road,', '', 'Dharwad', '580001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(234, 'customer', 54, 'billing', 'D.No:32-9-43/1,\nBeside Ayyappa Swamy Temple,', 'Tunglam Village,\nHarijana Jaggayya Palem,\nSheelanagar,', 'Visakhapatnam.', '530012', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(235, 'customer', 54, 'shipping', 'D.No:32-9-43/1,\nBeside Ayyappa Swamy Temple,', 'Tunglam Village,\nHarijana Jaggayya Palem,\nSheelanagar,', 'Visakhapatnam.', '530012', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(236, 'customer', 126, 'billing', 'Opp. Gandhi Park,', 'Station Road,', 'Bhongir,', '508 116', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(237, 'customer', 126, 'shipping', 'Opp. Gandhi Park,', 'Station Road,', 'Bhongir,', '508 116', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(238, 'customer', 127, 'billing', '# 41,  Regent  Palace,', '(Opp. Ranikuthi Telephone Exchange) Near Chayanika indances Gas.', 'Kolkata', '700040', 24, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(239, 'customer', 127, 'shipping', '# 41,  Regent  Palace,', '(Opp. Ranikuthi Telephone Exchange) Near Chayanika indances Gas.', 'Kolkata', '700040', 24, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(240, 'customer', 129, 'billing', 'No. 5/142.1, P.P.H. Plaza,\nNear Mariamman Kovil,', 'Chinthamaniyur (PO),\nOmalur Via, Mettur (TK),', 'Salem.', '636 455', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(241, 'customer', 129, 'shipping', 'No. 5/142.1, P.P.H. Plaza,\nNear Mariamman Kovil,', 'Chinthamaniyur (PO),\nOmalur Via, Mettur (TK),', 'Salem.', '636 455', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(242, 'customer', 130, 'billing', 'D.No. 18/641-5,', 'MUNCIPAL MAIN ROAD', 'ADONI', '518301', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(243, 'customer', 130, 'shipping', 'D.No. 18/641-5,', 'MUNCIPAL MAIN ROAD', 'ADONI', '518301', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(244, 'customer', 131, 'billing', '2nd Floor,\nKelle Court,\nDoor No:13-1-15,13-1-15/2 TO 5,', 'P.M. Rao Road,\nHampankatta,', 'Mangalore.', '575 001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(245, 'customer', 131, 'shipping', '2nd Floor,\nKelle Court,\nDoor No:13-1-15,13-1-15/2 TO 5,', 'P.M. Rao Road,\nHampankatta,', 'Mangalore.', '575 001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(246, 'customer', 132, 'billing', 'No: 18/74,\nL.B. Street,', 'Prabhakar Talkies Road,\nAdoni,', 'Kurnool Dist.', '518301', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(247, 'customer', 132, 'shipping', 'No: 18/74,\nL.B. Street,', 'Prabhakar Talkies Road,\nAdoni,', 'Kurnool Dist.', '518301', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(248, 'customer', 133, 'billing', '9/124, KANNIMEL CHERRY,', 'VALLIKEEZHU, KAVANADU (PO),', 'Kollam,', '691003', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(249, 'customer', 133, 'shipping', '9/124, KANNIMEL CHERRY,', 'VALLIKEEZHU, KAVANADU (PO),', 'Kollam,', '691003', 2, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(250, 'customer', 134, 'billing', '33-A,', 'KRISHNA TALKIES ROAD,', 'Erode.', '638003', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(251, 'customer', 134, 'shipping', '33-A,', 'KRISHNA TALKIES ROAD,', 'Erode.', '638003', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(252, 'customer', 135, 'billing', '15, Pillaiyar Kovil Street,\nNo: 2,', 'Gugai,', 'Salem', '636006', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(253, 'customer', 135, 'shipping', '15, Pillaiyar Kovil Street, \nNo: 2,', 'Gugai,', 'Salem', '636006', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(254, 'customer', 136, 'billing', '8/1,\nCHAKARAPANI STREET,', 'VENKATESA COLONY,', 'POLLACHI', '642001', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(255, 'customer', 136, 'shipping', '8/1,\nCHAKARAPANI STREET,', 'VENKATESA COLONY,', 'POLLACHI', '642001', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(256, 'customer', 137, 'billing', 'No 6,\nQuardrant Road,', 'Duraiswamy Circle,\nShivaji Nagar.', 'Bangalore.', '560001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(257, 'customer', 137, 'shipping', 'No 6,\nQuardrant Road,', 'Duraiswamy Circle,\nShivaji Nagar.', 'Bangalore.', '560001', 3, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(258, 'customer', 138, 'billing', 'First Floor, 16/82A,\nNTR Circle,', 'Govt Hospital Street,', 'Dharmavaram', '515671', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(259, 'customer', 138, 'shipping', 'First Floor, 16/82A,\nNTR Circle,', 'Govt Hospital Street,', 'Dharmavaram', '515671', 4, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(260, 'customer', 139, 'billing', 'Shivaji Nagar,', '', 'Nalgonda.', '508001', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(261, 'customer', 139, 'shipping', 'Shivaji Nagar,', '', 'Nalgonda.', '508001', 35, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(262, 'customer', 140, 'billing', 'East main Street ', '', 'Pudukkottai ', '622001', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(263, 'customer', 140, 'shipping', 'East main Street ', '', 'Pudukkottai ', '622001', 1, 1, 1, '2026-02-01 12:37:18', '2026-02-01 12:37:18'),
(264, 'customer', 142, 'billing', 'No: 171,\nEast Car Street,', 'Chettinayakan Patty,', 'Dindigul.', '624001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(265, 'customer', 142, 'shipping', 'No: 171,\nEast Car Street,', 'Chettinayakan Patty,', 'Dindigul.', '624001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(266, 'customer', 331, 'billing', 'No. 140,', 'East Car Street,', 'Dindigul.', '624001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(267, 'customer', 331, 'shipping', 'No. 140,', 'East Car Street,', 'Dindigul.', '624001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(268, 'customer', 145, 'billing', 'Padmasali Street', 'Venkatagiri', 'SPSR Nellore District', '524132', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(269, 'customer', 145, 'shipping', 'Padmasali Street', 'Venkatagiri', 'SPSR Nellore District', '524132', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(270, 'customer', 146, 'billing', 'No: 12-89,', 'BAZZAR STREET,', 'CHITTOOR.', '517001', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(271, 'customer', 146, 'shipping', 'No: 12-89,', 'BAZZAR STREET,', 'CHITTOOR.', '517001', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(272, 'customer', 147, 'billing', 'No: 81,\nSivanandha Salai,', 'Rasipuram,', 'Namakkal.', '637408', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(273, 'customer', 147, 'shipping', 'No: 81,\nSivanandha Salai,', 'Rasipuram,', 'Namakkal.', '637408', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(274, 'customer', 148, 'billing', '78/1, ShukrawarPeth', 'Bhandegalli', 'Solapur', '413001', 5, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(275, 'customer', 148, 'shipping', '78/1, ShukrawarPeth', 'Bhandegalli', 'Solapur', '413001', 5, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(276, 'customer', 332, 'billing', '9, Margosa Road, 8th Cross', 'Malleshwaram', 'Bangalore', '560003', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(277, 'customer', 332, 'shipping', '9, Margosa Road, 8th Cross', 'Malleshwaram', 'Bangalore', '560003', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(278, 'customer', 149, 'billing', 'No: GF3, Jalaram Market,', 'D K Lane,\nChickpet.', 'Bangalore', '560053', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(279, 'customer', 149, 'shipping', 'No: GF3, Jalaram Market,', 'D K Lane,\nChickpet.', 'Bangalore', '560053', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(280, 'customer', 150, 'billing', '37 DEVI NAGAR', 'P N PUDUR', 'COIMBATORE', '641041', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(281, 'customer', 150, 'shipping', '37 DEVI NAGAR', 'P N PUDUR', 'COIMBATORE', '641041', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(282, 'customer', 152, 'billing', 'Main Road, Madanthyar,', '', 'Belthangady Taluk', '574224', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(283, 'customer', 152, 'shipping', 'Main Road, Madanthyar,', '', 'Belthangady Taluk', '574224', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(284, 'customer', 153, 'billing', 'No: 3-46-A4,\nRamalayam Street,', 'Neerugattuvaripalli,\nMadanapalle,', 'Chittoor.', '517325', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(285, 'customer', 153, 'shipping', 'No: 3-46-A4,\nRamalayam Street,', 'Neerugattuvaripalli,\nMadanapalle,', 'Chittoor.', '517325', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(286, 'customer', 154, 'billing', 'Badvel M', '', 'Kadapa', '516227', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(287, 'customer', 154, 'shipping', 'Badvel M', '', 'Kadapa', '516227', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(288, 'customer', 155, 'billing', 'Kaliyakkavilai,', '', 'Kanyakumari.', '629153', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(289, 'customer', 155, 'shipping', 'Kaliyakkavilai,', '', 'Kanyakumari.', '629153', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(290, 'customer', 156, 'billing', 'Cloth merchant', 'opp. Mayura Inland Mainroad', 'PUTTUR', '574201', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(291, 'customer', 156, 'shipping', 'Cloth merchant', 'opp. Mayura Inland Mainroad', 'PUTTUR', '574201', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(292, 'customer', 157, 'billing', '1/6  St Mary\'S Road', 'Mandaveli', 'Chennai', '600028', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(293, 'customer', 157, 'shipping', '1/6  St Mary\'S Road', 'Mandaveli', 'Chennai', '600028', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(294, 'customer', 158, 'billing', 'No.49-B, Ezhai Mariyamman Kovil Street,', 'Muthialpet,', 'Puducherry', '605003', 33, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(295, 'customer', 158, 'shipping', 'No.49-B, Ezhai Mariyamman Kovil Street,', 'Muthialpet,', 'Puducherry', '605003', 33, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(296, 'customer', 159, 'billing', '263, Seekalai Road', '', 'Karaikudi', '630001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(297, 'customer', 159, 'shipping', '263, Seekalai Road', '', 'Karaikudi', '630001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(298, 'customer', 161, 'billing', '29/336-2,Chinthaguntapalem,kattavari veedhi,', '', 'Machilipatnam, Krishna', '521001', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(299, 'customer', 161, 'shipping', '29/336-2,Chinthaguntapalem,kattavari veedhi,', '', 'Machilipatnam, Krishna', '521001', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(300, 'customer', 162, 'billing', 'No: 34,\nSri Devi Nagar,', 'Moondramkattalai,\nKundrathur,', 'Chennai.', '600 069', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(301, 'customer', 162, 'shipping', 'No: 34,\nSri Devi Nagar,', 'Moondramkattalai,\nKundrathur,', 'Chennai.', '600 069', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(302, 'customer', 163, 'billing', '1ST FLOOR, 40-9-74, 74A,\nREVENUE WARD 13,', 'VIJAYAWADA MUNICIPA CORPORATION,\nTRENDSET MALL, BENZ CIRCLE,', 'Vijayawada', '520010', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(303, 'customer', 163, 'shipping', '1ST FLOOR, 40-9-74, 74A,\nREVENUE WARD 13,', 'VIJAYAWADA MUNICIPA CORPORATION,\nTRENDSET MALL, BENZ CIRCLE,', 'Vijayawada', '520010', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(304, 'customer', 164, 'billing', '# 12, Wonderland,', '7, M.G. Road,', 'Pune', '411 001', 5, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20');
INSERT INTO `addresses` (`id`, `owner_type`, `owner_id`, `address_type`, `address_line1`, `address_line2`, `city`, `pincode`, `state_id`, `country_id`, `is_active`, `created_at`, `updated_at`) VALUES
(305, 'customer', 164, 'shipping', '# 12, Wonderland,', '7, M.G. Road,', 'Pune', '411 001', 5, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(306, 'customer', 166, 'billing', 'No: 120/1, B.T. Market Road,', 'Near Iyappan Temple,', 'Bargur.', '635104', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(307, 'customer', 166, 'shipping', 'No: 120/1, B.T. Market Road,', 'Near Iyappan Temple,', 'Bargur.', '635104', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(308, 'customer', 167, 'billing', '23/2, K.H.Road,', '', 'Bengaluru', '560027', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(309, 'customer', 167, 'shipping', '23/2, K.H.Road,', '', 'Bengaluru', '560027', 3, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(310, 'customer', 168, 'billing', 'No1, N.G. Basak Road', 'DUM DUM', 'Kolkatta', '700080', 24, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(311, 'customer', 168, 'shipping', 'No1, N.G. Basak Road', 'DUM DUM', 'Kolkatta', '700080', 24, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(312, 'customer', 169, 'billing', '92/A11 &A12, P.V. Rajamannar Salai', 'K K Nagar West', 'Chennai', '600078', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(313, 'customer', 169, 'shipping', '92/A11 &A12, P.V. Rajamannar Salai', 'K K Nagar West', 'Chennai', '600078', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(314, 'customer', 170, 'billing', 'Plot No:  361-364, PSR Weavers Park,\nDr Rajendra Prasad Road,', '100 Feet Road, Tatabad,', 'Coimbatore.', '641012', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(315, 'customer', 170, 'shipping', 'Plot No:  361-364,\nPSR Weavers Park,\nDr Rajendra Prasad Road,', '100 Feet Road,\nTatabad,', 'Coimbatore.', '641012', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(316, 'customer', 171, 'billing', 'Parvathy Enclave,\nDoor No.37,\nKalidas Road,', 'Kondasamy Layout,\nRamnagar,', 'Coimbatore.', '641009', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(317, 'customer', 171, 'shipping', 'Parvathy Enclave,\nDoor No.37,\nKalidas Road,', 'Kondasamy Layout,\nRamnagar,', 'Coimbatore.', '641009', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(318, 'customer', 172, 'billing', '18-131/A, Main Road', '', 'Nandigama', '521185', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(319, 'customer', 172, 'shipping', '18-131/A, Main Road', '', 'Nandigama', '521185', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(320, 'customer', 173, 'billing', 'New No: 29, OLD No: 17,\nSyed Complex,', 'Ground Floor,\nGopalakrishnan Road,\nT.Nagar,', 'Chennai.', '600017', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(321, 'customer', 173, 'shipping', 'New No: 29, OLD No: 17,\nSyed Complex,', 'Ground Floor,\nGopalakrishnan Road,\nT.Nagar,', 'Chennai.', '600017', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(322, 'customer', 174, 'billing', '8 Munusamy Cherry Street', '', 'ARNI', '632301', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(323, 'customer', 174, 'shipping', '8 Munusamy Cherry Street', '', 'ARNI', '632301', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(324, 'customer', 177, 'billing', 'Shop No: 5&6,\nKrishnaveni Cloth Market,', 'Panja Centre,', 'Vijayawada.', '520001', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(325, 'customer', 177, 'shipping', 'Shop No: 5&6,\nKrishnaveni Cloth Market,', 'Panja Centre,', 'Vijayawada.', '520001', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(326, 'customer', 178, 'billing', '5/1 Netaji Road', 'oldpet', 'Kuppam', '517425', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(327, 'customer', 178, 'shipping', '5/1 Netaji Road', 'oldpet', 'Kuppam', '517425', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(328, 'customer', 179, 'billing', '# 129,', 'Big Street,', 'Pattukkottai.', '614 601', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(329, 'customer', 179, 'shipping', '# 129,', 'Big Street,', 'Pattukkottai.', '614 601', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(330, 'customer', 180, 'billing', '# 96,', 'Big Street', 'Pattukkottai.', '614 601', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(331, 'customer', 180, 'shipping', '# 96,', 'Big Street', 'Pattukkottai.', '614 601', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(332, 'customer', 181, 'billing', '20-43, Beside Lane of Sharada Theatre', 'Saroor Nagar', 'Hyderabad', '500035', 35, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(333, 'customer', 181, 'shipping', '20-43, Beside Lane of Sharada Theatre', 'Saroor Nagar', 'Hyderabad', '500035', 35, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(334, 'customer', 182, 'billing', '9/59, Main Road', 'Krishna Dt', 'Gudivada', '521301', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(335, 'customer', 182, 'shipping', '9/59, Main Road', 'Krishna Dt', 'Gudivada', '521301', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(336, 'customer', 183, 'billing', 'NO:14-8-3,\nSHOP NO 144,', 'M.G.C.MARKET,', 'CHIRALA,', '523155', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(337, 'customer', 183, 'shipping', 'NO:14-8-3,\nSHOP NO 144,', 'M.G.C.MARKET,', 'CHIRALA,', '523155', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(338, 'customer', 184, 'billing', '3/922-B,\nOpp. Aditya Apartment,', 'Kakumani Nilayam,\nY.M.R. Colony,', 'Proddatur.', '516360', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(339, 'customer', 184, 'shipping', '3/922-B,\nOpp. Aditya Apartment,', 'Kakumani Nilayam,\nY.M.R. Colony,', 'Proddatur.', '516360', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(340, 'customer', 185, 'billing', 'PANKAJ MARKET', 'SARAIYAGANJ', 'MUZZAFFARPUR', '842001', 15, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(341, 'customer', 186, 'billing', 'New No: 48/1, Old No: 4-A,', 'Rajaji Road,', 'Salem.', '636 007', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(342, 'customer', 186, 'shipping', 'New No: 48/1, Old No: 4-A,', 'Rajaji Road,', 'Salem.', '636 007', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(343, 'customer', 187, 'billing', 'Badvel mandal', '', 'Kadapa', '516227', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(344, 'customer', 187, 'shipping', 'Badvel mandal', '', 'Kadapa', '516227', 4, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(345, 'customer', 188, 'billing', 'No.89', 'Ramasamy Street\nMannady,', 'Chennai.', '600001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(346, 'customer', 188, 'shipping', 'No.89,', 'Ramasamy Street,\nMannady,', 'Chennai.', '600001', 1, 1, 1, '2026-02-01 12:37:20', '2026-02-01 12:37:20'),
(347, 'customer', 189, 'billing', 'U.B Road', '', 'Bhadrachalam', '507111', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(348, 'customer', 189, 'shipping', 'U.B Road', '', 'Bhadrachalam', '507111', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(349, 'customer', 191, 'billing', '# 12, Ground Floor,\nWarehouse,\nSouth Bye Pass Road,', 'Vannarapettai.', 'Tirunelveli', '627003', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(350, 'customer', 191, 'shipping', '#12, Ground Floor, \nWarehouse,\nSouth Bye Pass Road,', 'Vannarapettai.', 'Tirunelveli', '627003', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(351, 'customer', 193, 'billing', 'Ramachandra Kalyana Mandapam,Puthiyavan nagar', 'Avarampalayam', 'Coimbatore', '641006', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(352, 'customer', 193, 'shipping', 'Ramachandra Kalyana Mandapam,Puthiyavan nagar', 'Avarampalayam', 'Coimbatore', '641006', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(353, 'customer', 333, 'billing', '7-1-589 To 590, opp. Sree Kanaka Gurga Tempale', 'Ameer Pet,', 'HYDERABAD.', '500016', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(354, 'customer', 333, 'shipping', '7-1-589 To 590, opp. Sree Kanaka Gurga Tempale', 'Ameer Pet,', 'HYDERABAD.', '500016', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(355, 'customer', 194, 'billing', '5/1 Muzzafar Ahmed Street', '1st Floor', 'Kolkata', '700016', 24, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(356, 'customer', 194, 'shipping', '5/1 Muzzafar Ahmed Street', '1st Floor', 'Kolkata', '700016', 24, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(357, 'customer', 195, 'shipping', 'Kaliyakkavilai,', '', 'Kanyakumari', '629153', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(358, 'customer', 196, 'billing', 'No: 289,', 'Oppanakara Street,', 'Coimbatore.', '641001', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(359, 'customer', 196, 'shipping', 'No: 289,', 'Oppanakara Street,', 'Coimbatore.', '641001', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(360, 'customer', 197, 'billing', 'No:108/47-B,', 'Mettu Street,', 'Kanchipuram.', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(361, 'customer', 197, 'shipping', 'No:108/47-B,', 'Mettu Street,', 'Kanchipuram.', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(362, 'customer', 198, 'billing', '63/1, 1st Floor, A Block,\nMenaka Appartment,', 'Annamalai Nagar,\nSeelanaicken Patti,', 'Salem.', '636201', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(363, 'customer', 198, 'shipping', '63/1, 1st Floor, A Block,\nMenaka Appartment,', 'Annamalai Nagar,\nSeelanaicken Patti,', 'Salem.', '636201', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(364, 'customer', 199, 'billing', 'No: 196/C,\nMovva Residency,\nBeside Vijetha College,', 'Jai Vayu Vihar Road,\nWestern Hills Area, KPHP.', 'Hyderabad', '500085', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(365, 'customer', 199, 'shipping', 'No: 196/C,\nMovva Residency,\nBeside Vijetha College,', 'Jai Vayu Vihar Road,\nWestern Hills Area, KPHP.', 'Hyderabad', '500085', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(366, 'customer', 200, 'billing', 'No: 43-A,', 'Sangusahpet Street,', 'Kanchipuram.', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(367, 'customer', 200, 'shipping', 'No: 43-A,', 'Sangusahpet Street,', 'Kanchipuram.', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(368, 'customer', 201, 'billing', 'No: 154/1,', 'SEKKALAI ROAD,\nKARAIKUDI,', 'SIVAGANGAI.', '630001', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(369, 'customer', 201, 'shipping', 'No: 154/1,', 'SEKKALAI ROAD,\nKARAIKUDI,', 'SIVAGANGAI.', '630001', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(370, 'customer', 203, 'billing', 'No: 45, N.S.B Road,', 'N.S.B. Road,', 'Trichy.', '620002', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(371, 'customer', 203, 'shipping', 'No: 45, N.S.B Road,', 'N.S.B. Road,', 'Trichy.', '620002', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(372, 'customer', 337, 'billing', '154, South Masi Street', '', 'Madurai', '625001', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(373, 'customer', 337, 'shipping', '154, South Masi Street', '', 'Madurai', '625001', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(374, 'customer', 205, 'billing', '21 A/3,\nKRISHNAN STREET,', 'PILLAIYAR PALAYAM,', 'KANCHIPURAM', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(375, 'customer', 205, 'shipping', '21 A/3,\nKRISHNAN STREET,', 'PILLAIYAR PALAYAM,', 'KANCHIPURAM', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(376, 'customer', 206, 'billing', 'D.NO 12-94,\nNear Vidyajyothi School,', 'Old Police Station Street,\nGanapavaram,', 'West Godavari District,', '534198', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(377, 'customer', 206, 'shipping', 'D.NO 12-94,\nNear Vidyajyothi School,', 'Old Police Station Street,\nGanapavaram,', 'West Godavari District,', '534198', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(378, 'customer', 208, 'billing', 'qwe', 'qwer', 'qwe', '123123', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(379, 'customer', 208, 'shipping', 'qwe', 'qwer', 'qwe', '123123', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(380, 'customer', 209, 'billing', 'Shanthi Complex,\nNo: 18,  M M Lane,', 'Nagarthpet Cross,', 'Bangalore.', '560002', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(381, 'customer', 209, 'shipping', 'Shanthi Complex,\nNo: 18,  M M Lane,', 'Nagarthpet Cross,', 'Bangalore.', '560002', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(382, 'customer', 210, 'billing', '#1, Main Road', 'Melur', 'Madurai', '625106', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(383, 'customer', 210, 'shipping', '#1, Main Road', 'Melur', 'Madurai', '625106', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(384, 'customer', 211, 'billing', 'B-Block, Unity Building', 'J.C Road', 'Bangalore', '560002', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(385, 'customer', 211, 'shipping', 'B-Block, Unity Building', 'J.C Road', 'Bangalore', '560002', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(386, 'customer', 212, 'billing', 'Old no:134, New no:148, Plot no:16, G.A.Road,', 'Old Washermenpet', 'Chennai', '600021', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(387, 'customer', 212, 'shipping', 'Old no:134, New no:148, Plot no:16, G.A.Road,', 'Old Washermenpet', 'Chennai', '600021', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(388, 'customer', 213, 'billing', '2-14-7, 1st Floor', 'Devi Theatre Road', 'Kavali', '524201', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(389, 'customer', 213, 'shipping', '2-14-7, 1st Floor', 'Devi Theatre Road', 'Kavali', '524201', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(390, 'customer', 214, 'billing', 'Saraiyaganj Tower', '', 'Muzaffarpur', '842001', 15, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(391, 'customer', 214, 'shipping', 'Saraiyaganj Tower', '', 'Muzaffarpur', '842001', 15, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(392, 'customer', 215, 'billing', '46, 1st floor ', 'Papanna Lane, K R settypet', 'Bangalore', '560002', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(393, 'customer', 215, 'shipping', '46, 1st floor ', 'Papanna Lane, K R settypet', 'Bangalore', '560002', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(394, 'customer', 216, 'billing', 'No: 23B-9-25, 2nd Floor,\nRevenue Ward No 26,', 'Ramachandra Rao pet,\nMain Road,', 'Eluru.', '534002', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(395, 'customer', 216, 'shipping', 'No: 23B-9-25, 2nd Floor,\nRevenue Ward No 26,', 'Ramachandra Rao pet,\nMain Road,', 'Eluru.', '534002', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(396, 'customer', 218, 'billing', '1st Floor, Flat No: 8/9,\nNanda Apartment,\nOpp Sumitra Tower,', 'Near Shankar Mandir,\nAai Nagar,', 'Kalwa (Thane),', '400605', 5, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(397, 'customer', 218, 'shipping', '1st Floor, Flat No: 8/9,\nNanda Apartment,\nOpp Sumitra Tower,', 'Near Shankar Mandir,\nAai Nagar,', 'Kalwa (Thane),', '400605', 5, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(398, 'customer', 219, 'billing', 'C-51, GROUND FLOOR,\n4TH CROSS,', 'THILLAINAGAR,', 'Tiruchirappalli,', '620018', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(399, 'customer', 219, 'shipping', 'C-51, GROUND FLOOR,\n4TH CROSS,', 'THILLAINAGAR,', 'Tiruchirappalli,', '620018', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(400, 'customer', 221, 'billing', 'H NO 7-4-78/1,\nBAZAR AREA MAIN ROAD,', 'BELLAMPALLI,', 'Mancherial,', '504251', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(401, 'customer', 221, 'shipping', 'H NO 7-4-78/1,\nBAZAR AREA MAIN ROAD,', 'BELLAMPALLI,', 'Mancherial,', '504251', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(402, 'customer', 222, 'billing', '3rd Floor, Vishaal De Mall,', '31 Gokhale Road,\nChinnachokkikulam', 'Madurai', '625020', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(403, 'customer', 222, 'shipping', '3rd Floor, Vishaal De Mall,', '31 Gokhale Road,\nChinnachokkikulam', 'Madurai', '625020', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(404, 'customer', 338, 'billing', 'Main Road', 'Siruguppa', 'Bellary', '583121', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(405, 'customer', 338, 'shipping', 'Main Road', 'Siruguppa', 'Bellary', '583121', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(406, 'customer', 223, 'billing', '108/47-B', 'Mettu Street', 'Kanchipuram', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(407, 'customer', 223, 'shipping', '108/47-B', 'Mettu Street', 'Kanchipuram', '631501', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(408, 'customer', 339, 'billing', '7\"Bhivandiwala\" Bldg.,', 'L. Napoo Road Matunga,(.Rly)', 'MUMBAI', '400019', 5, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(409, 'customer', 339, 'shipping', '7\"Bhivandiwala\" Bldg.,', 'L. Napoo Road Matunga,(.Rly)', 'MUMBAI', '400019', 5, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(410, 'customer', 224, 'billing', 'S.V.N Road', '', 'Warangal', '506002', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(411, 'customer', 224, 'shipping', 'S.V.N Road', '', 'Warangal', '506002', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(412, 'customer', 225, 'billing', 'Premium Enclave, Light House Hill Road,', 'Near Jos Alukkas, Hampankatta,', 'Mangaluru', '575001', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(413, 'customer', 225, 'shipping', 'Premium Enclave, Light House Hill Road,', 'Near Jos Alukkas, Hampankatta,', 'Mangaluru', '575001', 3, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(414, 'customer', 226, 'billing', '12/201, B.N. Talkies Road', 'ADONI', 'Kurnool', '518301', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(415, 'customer', 226, 'shipping', '12/201, B.N. Talkies Road', 'ADONI', 'Kurnool', '518301', 4, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(416, 'customer', 227, 'billing', 'Plot no. 2-11, Bhagyanagar Estates,', 'Opp: KPHB Colony', 'Hyderabad', '500072', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(417, 'customer', 227, 'shipping', 'Plot no. 2-11, Bhagyanagar Estates,', 'Opp: KPHB Colony', 'Hyderabad', '500072', 35, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(418, 'customer', 228, 'billing', 'No:  241/92,\n MOONGAPADI STREET,', 'GUGAI,', 'Salem.', '636006', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(419, 'customer', 228, 'shipping', 'No:  241/92,\n MOONGAPADI STREET,', 'GUGAI,', 'Salem.', '636006', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(420, 'customer', 229, 'billing', 'No:  23/13,\nSamundi street,', 'Gugai,', 'Salem.', '636006', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(421, 'customer', 229, 'shipping', 'No:  23/13,\nSamundi street,', 'Gugai,', 'Salem.', '636006', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(422, 'customer', 230, 'billing', 'No. 1/349,\nSoundamman Kovil Street,', 'Pappampadi,\nChinnappampatti (Po),\nOmalur (Tk),', 'Salem', '636306', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(423, 'customer', 230, 'shipping', 'No. 1/349,\nSoundamman Kovil Street,', 'Pappampadi,\nChinnappampatti (Po),\nOmalur (Tk),', 'Salem', '636306', 1, 1, 1, '2026-02-01 12:37:42', '2026-02-01 12:37:42'),
(424, 'customer', 232, 'billing', '2-2-117, Aam Bazar Masjid Road', 'Khammam Dt', 'YELLANDU', '507123', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(425, 'customer', 232, 'shipping', '2-2-117, Aam Bazar Masjid Road', 'Khammam Dt', 'YELLANDU', '507123', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(426, 'customer', 233, 'billing', 'Old No.32 New No.14,\nA.K.S. Nagar', 'Ponniarajapuram', 'Coimbatore', '641001', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(427, 'customer', 233, 'shipping', 'Old No.32 New No.14,\nA.K.S. Nagar', 'Ponniarajapuram', 'Coimbatore', '641001', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(428, 'customer', 234, 'billing', '108/15, All India Radio Nagar, Sunami Quarters, Nethaji Nagar', 'Near By Tiruvattiyur, Ernavoor (Tiruvallur District)', 'Chennai', '600057', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(429, 'customer', 234, 'shipping', '108/15, All India Radio Nagar, Sunami Quarters, Nethaji Nagar', 'Near By Tiruvattiyur, Ernavoor (Tiruvallur District)', 'Chennai', '600057', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(430, 'customer', 235, 'billing', 'No: 1/17-1,\nAsoka Natesan Street,', 'Next To Solapuri Amman Kovil,\nNear CMC Eye Hospital,', 'Vellore.', '632 001', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(431, 'customer', 235, 'shipping', 'No: 1/17-1,\nAsoka Natesan Street,', 'Next To Solapuri Amman Kovil,\nNear CMC Eye Hospital,', 'Vellore.', '632 001', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(432, 'customer', 237, 'billing', 'No. 845, Basement Floor,\n60 feet Road, A Block,', 'Near Mahalaxmi Sweets,\nSahakar Nagar,', 'Bangalure.', '560092', 3, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(433, 'customer', 237, 'shipping', 'No. 845, Basement Floor,\n60 feet Road, A Block,', 'Near Mahalaxmi Sweets,\nSahakar Nagar,', 'Bangalure.', '560092', 3, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(434, 'customer', 238, 'billing', 'Shop No : 139, MGNC Complex', 'Dwarapuri', 'Dwarapudi', '533341', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(435, 'customer', 238, 'shipping', 'Shop No : 139, MGNC Complex', 'Dwarapuri', 'Dwarapudi', '533341', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(436, 'customer', 239, 'billing', '68, North Car Street', '', 'Tiruchengode', '637211', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(437, 'customer', 239, 'shipping', '68, North Car Street', '', 'Tiruchengode', '637211', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(438, 'customer', 240, 'billing', 'No. 1491, Vinoaba Road,', 'Shivrampet,\nNear Rajkamal Theatre,', 'Mysore', '570001', 3, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(439, 'customer', 240, 'shipping', 'No. 1491, Vinoaba Road,', 'Shivrampet,\nNear Rajkamal Theatre,', 'Mysore', '570001', 3, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(440, 'customer', 241, 'billing', 'M G Road,', '', 'Ramanagara', '562109', 3, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(441, 'customer', 241, 'shipping', 'M G Road,', '', 'Ramanagara', '562109', 3, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(442, 'customer', 242, 'billing', 'RAGHU MANSION,\nNo: 5-45-7,', '4th Line,\nBrodipet,', 'Guntur', '522002', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(443, 'customer', 242, 'shipping', 'RAGHU MANSION,\nNo: 5-45-7,', '4th Line,\nBrodipet,', 'Guntur', '522002', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(444, 'customer', 243, 'billing', 'No: 6-2-53,\nM.G. ROAD,', 'KOTHAGUDEM,', 'KHAMMAM DIST.', '507101', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(445, 'customer', 243, 'shipping', 'No: 6-2-53,\nM.G. ROAD,', 'KOTHAGUDEM,', 'KHAMMAM DIST.', '507101', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(446, 'customer', 244, 'billing', '32, AKS NAGAR', 'PONNIARAJAPURAM', 'Coimbatore', '641001', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(447, 'customer', 244, 'shipping', '32, AKS NAGAR', 'PONNIARAJAPURAM', 'Coimbatore', '641001', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(448, 'customer', 245, 'billing', '12-1-3, SKML Building,\nNear Balatripurasundaradevi Temple and Ramalayam,', 'Jawahar Street', 'Kakinada', '533001', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(449, 'customer', 245, 'shipping', '12-1-3, SKML Building,\nNear Balatripurasundaradevi Temple and Ramalayam,', 'Jawahar Street', 'Kakinada', '533001', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(450, 'customer', 340, 'billing', '60-A, Thiruvalluvar Street', 'Little Kanchipuram', 'Kanchipuram', '631503', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(451, 'customer', 340, 'shipping', '60-A, Thiruvalluvar Street', 'Little Kanchipuram', 'Kanchipuram', '631503', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(452, 'customer', 246, 'billing', '1/39, 1,2,3 wards, new Madhavaram', '', 'Kadapa', '516247', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(453, 'customer', 246, 'shipping', '1/39, 1,2,3 wards, new Madhavaram', '', 'Kadapa', '516247', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(454, 'customer', 248, 'billing', '296, Omalur Main Road,', '', 'Salem', '636009', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(455, 'customer', 248, 'shipping', '296, Omalur Main Road,', '', 'Salem', '636009', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(456, 'customer', 250, 'billing', 'No: 12-1-10,', 'U.B.Road,', 'Bhadrachalam,', '507111', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(457, 'customer', 250, 'shipping', 'No: 12-1-10,', 'U.B.Road,', 'Bhadrachalam,', '507111', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(458, 'customer', 251, 'billing', '111 C5, Trichy Road,\nopp: Womens Police Station', '', 'Palladam', '641664', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(459, 'customer', 251, 'shipping', '111 C5, Trichy Road,\nopp: Womens Police Station', '', 'Palladam', '641664', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(460, 'customer', 253, 'billing', 'No 2 Ashtapujam East Mada Street,Rangasamikulam bus stop', '', 'Kanchipuram', '631501', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(461, 'customer', 253, 'shipping', 'No 2 Ashtapujam East Mada Street,Rangasamikulam bus stop', '', 'Kanchipuram', '631501', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(462, 'customer', 254, 'billing', 'FLOT NO 13 AND 14,\nVALLALAR NAGAR,\nSAIDAPET,', 'MULLIPET VILLAGE,\nARNI,', 'THIRUVANNAMALAI.', '632301', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(463, 'customer', 254, 'shipping', 'FLOT NO 13 AND 14,\nVALLALAR NAGAR,\nSAIDAPET,', 'MULLIPET VILLAGE,\nARNI,', 'THIRUVANNAMALAI.', '632301', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(464, 'customer', 256, 'billing', 'No. 168/1,\nPalani Road,', 'Udumalaipettai,', 'Tiruppur', '642154', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(465, 'customer', 256, 'shipping', 'No. 168/1,\nPalani Road,', 'Udumalaipettai,', 'Tiruppur', '642154', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(466, 'customer', 257, 'billing', 'Near Veerabhadraswami Temple,', 'Bandarulanka,\nAmalapuram Mandal,', 'East Godavari Dist', '533221', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(467, 'customer', 257, 'shipping', 'Near Veerabhadraswami Temple,', 'Bandarulanka,\nAmalapuram Mandal,', 'East Godavari Dist', '533221', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(468, 'customer', 258, 'billing', 'H.NO.5-8-34,\nM.G.ROAD,', 'KOTHAGUDEM,', 'Badradri,', '507101', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(469, 'customer', 258, 'shipping', 'H.NO.5-8-34,\nM.G.ROAD,', 'KOTHAGUDEM,', 'Badradri,', '507101', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(470, 'customer', 259, 'billing', 'M.G Road', 'Kothagudem', 'Bhadradri Kothagudem DIST', '507101', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(471, 'customer', 259, 'shipping', 'M.G Road', 'Kothagudem', 'Bhadradri Kothagudem DIST', '507101', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(472, 'customer', 260, 'billing', 'No: 34, \nMain Road,', 'Puranasingupalayam,', 'Puducherry,', '605107', 33, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(473, 'customer', 260, 'shipping', 'No: 34, \nMain Road,', 'Puranasingupalayam,', 'Puducherry,', '605107', 33, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(474, 'customer', 261, 'billing', 'H.No: 5-48 To 49/1,\nBeside Shadi Khana,', 'Market Road,', 'Mancherila.', '504208', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(475, 'customer', 261, 'shipping', 'H.No: 5-48 To 49/1,\nBeside Shadi Khana,', 'Market Road,', 'Mancherila.', '504208', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(476, 'customer', 262, 'billing', 'H.No.8-2-2/1, 2nd Floor,\nK.K. Residency,', 'Sri Nagar Colony,\nMain Road,', 'Hyderabad.', '500082', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(477, 'customer', 262, 'shipping', 'Survey No. 3 & 6,\nBeside Zila Parishad High School,', 'Muneerabad Medchal,\nMedchal Malkajgiri,', 'Hyderabad.', '501401', 35, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(478, 'customer', 265, 'billing', 'No: 7/59, Near Bus Stop,\nMettur Main Road,', 'Panjukalipatti,\nPanankattur(po),\nOmalur(Tk),', 'Salem.', '636455', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(479, 'customer', 265, 'shipping', 'No: 7/59, Near Bus Stop,\nMettur Main Road,', 'Panjukalipatti,\nPanankattur(po),\nOmalur(Tk),', 'Salem.', '636455', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(480, 'customer', 343, 'billing', 'Yankappa Buildings', 'Main Bazaar', 'Alur', '518395', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(481, 'customer', 343, 'shipping', 'Yankappa Buildings', 'Main Bazaar', 'Alur', '518395', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(482, 'customer', 266, 'billing', 'Masjit Street,', 'Bosu Bommacenter,', 'Jangareddygudm,', '534447', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(483, 'customer', 266, 'shipping', 'Masjit Street,', 'Bosu Bommacenter,', 'Jangareddygudm,', '534447', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(484, 'customer', 344, 'billing', 'BASEMENT, 6/289 B-4,\nWAIKIKI COMPLEX,\nNEAR DEVETON JUNCTION,', 'PURASAWALKAM HIGH ROAD,\nPURASAWALKAM,', 'CHENNAI', '600007', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(485, 'customer', 344, 'shipping', 'BASEMENT, 6/289 B-4,\nWAIKIKI COMPLEX,\nNEAR DEVETON JUNCTION,', 'PURASAWALKAM HIGH ROAD,\nPURASAWALKAM,', 'CHENNAI', '600007', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(486, 'customer', 267, 'billing', '9/4, NagalPudur', '3rd Lane', 'Dindigul', '624003', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(487, 'customer', 267, 'shipping', '9/4, NagalPudur', '3rd Lane', 'Dindigul', '624003', 1, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(488, 'customer', 268, 'billing', 'No: 6-53,', 'DEVANGALA VEEDHI,\nSANTHAKAVITI.', 'RAJAM.', '532123', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(489, 'customer', 268, 'shipping', 'No: 6-53,', 'DEVANGALA VEEDHI,\nSANTHAKAVITI.', 'RAJAM.', '532123', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(490, 'customer', 269, 'billing', 'No:  27-17-44/1,\nBesant Road Cross,', 'Peddibhotlavari Street,\nGovernorpet,', 'Vijayawada,', '520002', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(491, 'customer', 269, 'shipping', 'No:  27-17-44/1,\nBesant Road Cross,', 'Peddibhotlavari Street,\nGovernorpet,', 'Vijayawada,', '520002', 4, 1, 1, '2026-02-01 12:37:44', '2026-02-01 12:37:44'),
(492, 'customer', 271, 'billing', 'Shop No:64,', 'Vastralatha,', 'Vijayawada,', '520001', 4, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(493, 'customer', 271, 'shipping', 'Shop No:64,', 'Vastralatha,', 'Vijayawada,', '520001', 4, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(494, 'customer', 272, 'billing', '275, 2nd cross, 5th Main, Agrahara Layout', 'Yelanhanka', 'Bengaluru', '560064', 3, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(495, 'customer', 272, 'shipping', '275, 2nd cross, 5th Main, Agrahara Layout', 'Yelanhanka', 'Bengaluru', '560064', 3, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(496, 'customer', 273, 'billing', 'No: 69B,', 'Vilakkadi Koil Street,', 'Kanchipuram.', '631501', 1, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(497, 'customer', 273, 'shipping', 'No: 69B,', 'Vilakkadi Koil Street,', 'Kanchipuram.', '631501', 1, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(498, 'customer', 276, 'billing', 'NO: 2/183,\nVICHAITHARI PATTARAI,', 'ALINCHIKUTHUPALLAM,\nMATTUPATTI POST,', 'MUSIRI', '621211', 1, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(499, 'customer', 276, 'shipping', 'NO: 2/183,\nVICHAITHARI PATTARAI,', 'ALINCHIKUTHUPALLAM,\nMATTUPATTI POST,', 'MUSIRI', '621211', 1, 1, 1, '2026-02-01 12:37:45', '2026-02-01 12:37:45'),
(500, 'customer', 277, 'billing', 'No. 1798, 9th Cross,\n18th \'A\' Main,', 'J.P Nagar,\n2nd Phase,', 'Bengaluru,', '560078', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(501, 'customer', 277, 'shipping', 'No. 1798, 9th Cross,\n18th \'A\' Main,', 'J.P Nagar,\n2nd Phase,', 'Bengaluru,', '560078', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(502, 'customer', 278, 'billing', 'No: 14/3,\nChord Road,', '20th Main, 2nd Block,\nRajaji Nagar,', 'Bangalore', '560010', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(503, 'customer', 278, 'shipping', 'No: 14/3,\nChord Road,', '20th Main, 2nd Block,\nRajaji Nagar,', 'Bangalore', '560010', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(504, 'customer', 279, 'billing', 'EMINENT MALL,\nXI/814-B,E,G,', 'VATANAPPALLY  P.O.,', 'THRISSUR.', '680614', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(505, 'customer', 279, 'shipping', 'EMINENT MALL,\nXI/814-B,E,G,', 'VATANAPPALLY  P.O.,', 'THRISSUR.', '680614', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(506, 'customer', 280, 'billing', 'Citizen Club Complex', 'Madanapeta Road\nNarsampet', 'Warangal', '506132', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(507, 'customer', 280, 'shipping', 'Citizen Club Complex', 'Madanapeta Road\nNarsampet', 'Warangal', '506132', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(508, 'customer', 345, 'billing', '21-1-646, High Court Road', 'Rikab Gunj', 'Hyderabad', '500002', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(509, 'customer', 345, 'shipping', '21-1-646, High Court Road', 'Rikab Gunj', 'Hyderabad', '500002', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(510, 'customer', 282, 'billing', '9-12.37 A, PRR Street,', '', 'Chinnalapatti', '624301', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(511, 'customer', 282, 'shipping', '9-12.37 A, PRR Street,', '', 'Chinnalapatti', '624301', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(512, 'customer', 283, 'billing', 'UNIVERSAL TOWERS,\nAMC X1/227 TO 232 AND 234,242,244,245,', 'MAIN ROAD,', 'ATTINGAL,', '695101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(513, 'customer', 283, 'shipping', 'UNIVERSAL TOWERS,\nAMC X1/227 TO 232 AND 234,242,244,245,', 'MAIN ROAD,', 'ATTINGAL,', '695101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(514, 'customer', 346, 'billing', '110, Kadalaikara Street ', '', 'Kovilpatri', '628501', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(515, 'customer', 346, 'shipping', '110, Kadalaikara Street ', '', 'Kovilpatri', '628501', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(516, 'customer', 284, 'billing', '20-F, old kuyavarpalayam road', '', 'Madurai', '625009', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(517, 'customer', 284, 'shipping', '20-F, old kuyavarpalayam road', '', 'Madurai', '625009', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(518, 'customer', 285, 'billing', '10B, Sengunthapuram', '1st Street, Mangalam Road, Karuvampalayam', 'Tiruppur', '641604', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(519, 'customer', 285, 'shipping', '10B, Sengunthapuram', '1st Street, Mangalam Road, Karuvampalayam', 'Tiruppur', '641604', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(520, 'customer', 286, 'billing', 'Pottiswamy Street', 'Governerpet', 'Vijayawada', '520001', 4, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(521, 'customer', 286, 'shipping', 'Pottiswamy Street', 'Governerpet', 'Vijayawada', '520001', 4, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(522, 'customer', 287, 'billing', '26/138, 139, Buttai Pet,', 'Machilipatnam', 'Krishna', '521001', 4, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(523, 'customer', 287, 'shipping', '26/138, 139, Buttai Pet,', 'Machilipatnam', 'Krishna', '521001', 4, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(524, 'customer', 347, 'billing', 'SYED TOWER, PUMP JUNCTION', 'RS. ROAD, ALUVA', 'Ernakulam', '683101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(525, 'customer', 347, 'shipping', 'SYED TOWER, PUMP JUNCTION', 'RS. ROAD, ALUVA', 'Ernakulam', '683101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(526, 'customer', 289, 'billing', '149/A, RASH BEHARI AVENUE,', '1st FLOOR', 'KOLKATTA', '700029', 24, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(527, 'customer', 289, 'shipping', '149/A, RASH BEHARI AVENUE,', '1st FLOOR', 'KOLKATTA', '700029', 24, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(528, 'customer', 290, 'billing', 'New No: 28- Old No: 160,\nEldams Road,', 'Tenampet,', 'Chennai.', '600018', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(529, 'customer', 290, 'shipping', 'New No: 28- Old No: 160,\nEldams Road,', 'Tenampet,', 'Chennai.', '600018', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(530, 'customer', 291, 'billing', 'Deep Majumder,\n15/B, Mitrangan,', 'Barasat Road,\nSodepur,', 'Kolkata.', '700110', 24, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(531, 'customer', 291, 'shipping', 'Deep Majumder,\n15/B, Mitrangan,', 'Barasat Road,\nSodepur,', 'Kolkata.', '700110', 24, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(532, 'customer', 292, 'billing', 'Lokamaleswaram, Star Nagar', 'Vadakkenada', 'Kodungallur', '680664', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(533, 'customer', 292, 'shipping', 'Lokamaleswaram, Star Nagar', 'Vadakkenada', 'Kodungallur', '680664', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(534, 'customer', 293, 'billing', 'Near Sub Treasury', 'Main Road', 'Attingal', '695101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(535, 'customer', 293, 'shipping', 'Near Sub Treasury', 'Main Road', 'Attingal', '695101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(536, 'customer', 294, 'billing', '1/1, YOGA NAGAR, TRICHY MAIN ROAD,', 'GANDHIGRAMAM', 'KARUR', '639004', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(537, 'customer', 294, 'shipping', '1/1, YOGA NAGAR, TRICHY MAIN ROAD,', 'GANDHIGRAMAM', 'KARUR', '639004', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(538, 'customer', 295, 'billing', 'G-1-335-6-5,\nSri Rama Commercial Complex,', 'Kalladka, Bantwal Taluk,', 'Dakshina Kannada', '574222', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(539, 'customer', 295, 'shipping', 'G-1-335-6-5,\nSri Rama Commercial Complex,', 'Kalladka, Bantwal Taluk,', 'Dakshina Kannada', '574222', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(540, 'customer', 296, 'billing', '6/98 A, Solavandhan Road', 'Checkanurani', 'Madurai', '625514', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(541, 'customer', 296, 'shipping', '6/98 A, Solavandhan Road', 'Checkanurani', 'Madurai', '625514', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(542, 'customer', 297, 'billing', 'Ramraj-V-Tower,\n10, Sengunthapuram,', 'Mangalam Road,', 'Tirupur.', '641604', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(543, 'customer', 297, 'shipping', 'Ramraj-V-Tower,\n10, Sengunthapuram,', 'Mangalam Road,', 'Tirupur.', '641604', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(544, 'customer', 298, 'billing', '25-367, R.V.S.C.V.S High School Road', 'Palnadu Dist', 'Chilakaluripet', '522616', 4, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(545, 'customer', 298, 'shipping', '25-367, R.V.S.C.V.S High School Road', 'Palnadu Dist', 'Chilakaluripet', '522616', 4, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(546, 'customer', 299, 'billing', 'M.G Road', '', 'Kothagudem', '507101', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(547, 'customer', 299, 'shipping', 'M.G Road', '', 'Kothagudem', '507101', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(548, 'customer', 300, 'billing', 'D.No: 1-6-141/29/D2/A', 'N H Main Road, Sriram Nagar', 'Suryapet', '508213', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(549, 'customer', 300, 'shipping', 'D.No: 1-6-141/29/D2/A', 'N H Main Road, Sriram Nagar', 'Suryapet', '508213', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(550, 'customer', 301, 'billing', 'At post Gala No:2,\nMahatma Phule Chowk', 'SATARA DIST', 'Phalthan', '415523', 5, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(551, 'customer', 301, 'shipping', 'At post Gala No:2,\nMahatma Phule Chowk', 'SATARA DIST', 'Phalthan', '415523', 5, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(552, 'customer', 302, 'billing', 'opp. Gandhi Park,', 'Station Road', 'Bhongir', '508116', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(553, 'customer', 302, 'shipping', 'opp. Gandhi Park,', 'Station Road', 'Bhongir', '508116', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(554, 'customer', 304, 'billing', '6-344, NTR COLONY,', 'BOPPAPURAM', 'VENKATAGIRI', '524132', 4, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(555, 'customer', 304, 'shipping', 'VNR RESIDENCY', 'KUKATPALLY', 'HYDERABAD', '500037', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(556, 'customer', 305, 'billing', 'D.No. 11-2-336,\nSai Krishna Residency', 'Mylargadda,', 'Secunderabad.', '500 061', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(557, 'customer', 305, 'shipping', 'D.No. 11-2-336,\nSai Krishna Residency', 'Mylargadda,', 'Secunderabad.', '500 061', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(558, 'customer', 306, 'billing', 'No. 8, 15th Cross,\nOpp. Kaveri Bhavan,', 'Cubbonpet,', 'Bengaluru.', '560002', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(559, 'customer', 306, 'shipping', 'No. 8, 15th Cross,\nOpp. Kaveri Bhavan,', 'Cubbonpet,', 'Bengaluru.', '560002', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(560, 'customer', 307, 'billing', 'TC 39/1797, BYPASS ROAD', 'CHALAI,MANACAUD', 'THIRUVANANTHAPURAM', '695009', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(561, 'customer', 307, 'shipping', 'TC 39/1797, BYPASS ROAD', 'CHALAI,MANACAUD', 'THIRUVANANTHAPURAM', '695009', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(562, 'customer', 309, 'billing', '19-1-927/33/A,\nMURLI NAGAR COLONY,', 'DOODH BOWLI,\nBAHADURPURA,', 'HYDERABAD.', '500064', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(563, 'customer', 309, 'shipping', '19-1-927/33/A,\nMURLI NAGAR COLONY,', 'DOODH BOWLI,\nBAHADURPURA,', 'HYDERABAD.', '500064', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(564, 'customer', 310, 'billing', 'Old No: 22/4, New No: A/16,\nBalan Nagar,', 'Peelamedu,', 'Coimbatore.', '641004', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(565, 'customer', 310, 'shipping', 'Old No: 22/4, New No: A/16,\nBalan Nagar,', 'Peelamedu,', 'Coimbatore.', '641004', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(566, 'customer', 312, 'billing', 'No: 3-36/71,\nShanthala Aashiyana,\nB Block R4,', 'Barebail Road, Vyasanagar,\nBehaind K P T, Kadri,', 'Mangalore.', '5755004', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(567, 'customer', 312, 'shipping', 'No: 3-36/71,\nShanthala Aashiyana,\nB Block R4,', 'Barebail Road, Vyasanagar,\nBehaind K P T, Kadri,', 'Mangalore.', '5755004', 3, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(568, 'customer', 313, 'billing', '36/11, CHB Colony Street No- 04,', 'Vellur Road,', 'Tiruchengode.', '637211', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(569, 'customer', 313, 'shipping', '36/11, CHB Colony Street No- 04,', 'Vellur Road,', 'Tiruchengode.', '637211', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(570, 'customer', 314, 'billing', '12/45\nBig Street', 'Kossapalayam', 'Arni', '632301', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(571, 'customer', 314, 'shipping', '12/45\nBig Street', 'Kossapalayam', 'Arni', '632301', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(572, 'customer', 315, 'billing', 'Plot No: 489, HIG 6th Phase,\nKPHB Colony', 'JNTU - Hitech City Road', 'Hyderabad', '500072', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(573, 'customer', 315, 'shipping', 'Plot No: 489, HIG 6th Phase,\nKPHB Colony', 'JNTU - Hitech City Road', 'Hyderabad', '500072', 35, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(574, 'customer', 316, 'billing', '8/13,Kabaleeshwarar Sannadhi Street', 'Mylapore,', 'Chennai', '600004', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(575, 'customer', 316, 'shipping', '8/13,Kabaleeshwarar Sannadhi Street', 'Mylapore,', 'Chennai', '600004', 1, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(576, 'customer', 318, 'billing', 'RAWABI TOWER,', 'MOONNU MUKKU,\nATTINGAL,', 'TRIVANDRUM', '695101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(577, 'customer', 318, 'shipping', 'RAWABI TOWER,', 'MOONNU MUKKU,\nATTINGAL,', 'TRIVANDRUM', '695101', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(578, 'customer', 319, 'billing', 'Near KSRTC Bus Stand', 'Haripad', 'Alappuzha', '690514', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(579, 'customer', 319, 'shipping', 'Near KSRTC Bus Stand', 'Haripad', 'Alappuzha', '690514', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(580, 'customer', 320, 'billing', 'M B SHOPPING COMPLEX,\nPAIKADA ROAD 17,', 'ANDAMUKKAM,\nCHINNAKADA,', 'KOLLAM,', '691001', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(581, 'customer', 320, 'shipping', 'M B SHOPPING COMPLEX,\nPAIKADA ROAD 17,', 'ANDAMUKKAM,\nCHINNAKADA,', 'KOLLAM,', '691001', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(582, 'customer', 321, 'billing', 'MIST', 'THANA', 'Kannur', '670012', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(583, 'customer', 321, 'shipping', 'MIST', 'THANA', 'Kannur', '670012', 2, 1, 1, '2026-02-01 12:38:01', '2026-02-01 12:38:01'),
(584, 'vendor', 4, 'billing', '78, North Rathina Sabapathypuram street,', 'Thirunagaram', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(585, 'vendor', 4, 'shipping', '78, North Rathina Sabapathypuram street,', 'Thirunagaram', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(586, 'vendor', 5, 'billing', '7 Railway Line north Street,', 'Ponnammapet', 'Salem', '636001', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(587, 'vendor', 5, 'shipping', '7 Railway Line north Street,', 'Ponnammapet', 'Salem', '636001', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(588, 'vendor', 6, 'billing', '1, Aaru Veetu Street', 'Puliyampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(589, 'vendor', 6, 'shipping', '1, Aaru Veetu Street', 'Puliyampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(590, 'vendor', 7, 'billing', '20, North Rathinasabapathypuram Street', 'Thirunagaram', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(591, 'vendor', 7, 'shipping', '20, North Rathinasabapathypuram Street', 'Thirunagaram', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(592, 'vendor', 8, 'billing', '16/8, Malayarasan Kovil Kilamel Street', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(593, 'vendor', 8, 'shipping', '16/8, Malayarasan Kovil Kilamel Street', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(594, 'vendor', 11, 'billing', '3-4/11 Mariamman Kovil Street', 'Konagapadi,\nK R Thoppur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(595, 'vendor', 11, 'shipping', '3-4/11 Mariamman Kovil Street', 'Konagapadi,\nK R Thoppur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(596, 'vendor', 12, 'billing', 'GUPS Venkottumukku,', 'Ven Code (po),\nVattapara,', 'Tiruvandrum', '695028', 2, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(597, 'vendor', 12, 'shipping', 'GUPS Venkottumukku,', 'Ven Code (po),\nVattapara,', 'Tiruvandrum', '695028', 2, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(598, 'vendor', 13, 'billing', '8/2, DHANDUMARIAMMAN KOVIL STREET,\nAvinashi Road,', '', 'Coimbatore', '641018', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(599, 'vendor', 13, 'shipping', '8/2, DHANDUMARIAMMAN KOVIL STREET,\nAvinashi Road,', '', 'Coimbatore', '641018', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(600, 'vendor', 14, 'billing', '11 Ganiyaar 2nd Street', 'Vellaikottai', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(601, 'vendor', 14, 'shipping', '11 Ganiyaar 2nd Street', 'Vellaikottai', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(602, 'vendor', 15, 'billing', 'G-3, New Ratan Market', 'Ring Road', 'Surat', '395002', 29, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(603, 'vendor', 15, 'shipping', 'G-3, New Ratan Market', 'Ring Road', 'Surat', '395002', 29, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(604, 'vendor', 16, 'billing', '16j8, Malaiyarasan Kovil Kilamal Street,', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(605, 'vendor', 16, 'shipping', '16j8, Malaiyarasan Kovil Kilamal Street,', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(606, 'vendor', 17, 'billing', 'No.7/268,\nKasi Nagar,', 'T. Subbulapuram,\nAundipatti (t.k).', 'Theni (d.t).', '625 536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(607, 'vendor', 17, 'shipping', 'No.7/268,\nKasi Nagar,', 'T. Subbulapuram,\nAundipatti (t.k).', 'Theni (d.t).', '625 536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21');
INSERT INTO `addresses` (`id`, `owner_type`, `owner_id`, `address_type`, `address_line1`, `address_line2`, `city`, `pincode`, `state_id`, `country_id`, `is_active`, `created_at`, `updated_at`) VALUES
(608, 'vendor', 19, 'billing', '12/194, Ayegoundampalayam,', 'Pattakaranpalayam, Thudupathi', 'Erode', '638057', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(609, 'vendor', 19, 'shipping', '12/194, Ayegoundampalayam,', 'Pattakaranpalayam, Thudupathi', 'Erode', '638057', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(610, 'vendor', 21, 'billing', 'Annai Sathya Nagar,', 'T. Subbulapuram,\nAundipatti (T.K),', 'Theni (D.T).', '625 536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(611, 'vendor', 21, 'shipping', 'Annai Sathya Nagar,', 'T. Subbulapuram,\nAundipatti (T.K),', 'Theni (D.T).', '625 536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(612, 'vendor', 22, 'billing', 'Old No: 1/296, New No: 7/49 Manickasamy Kovil Street', 'T.Subbulapuram', 'Aundipatti', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(613, 'vendor', 22, 'shipping', 'Old No: 1/296, New No: 7/49 Manickasamy Kovil Street', 'T.Subbulapuram', 'Aundipatti', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(614, 'vendor', 23, 'billing', 'B-3031-3032, Millennium Textile Market', 'Ring Road', 'Surat', '395002', 29, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(615, 'vendor', 23, 'shipping', 'B-3031-3032, Millennium Textile Market', 'Ring Road', 'Surat', '395002', 29, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(616, 'vendor', 24, 'billing', '4/6-122/137,\nNew 4th Ward', 'MGR Colony(PO),Taramangalam, Omalur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(617, 'vendor', 24, 'shipping', '4/6-122/137,\nNew 4th Ward', 'MGR Colony(PO),Taramangalam, Omalur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(618, 'vendor', 25, 'billing', '7/270, KASI STREEET', 'T.SUBBULAPURAM', 'AUNDIPATTI', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(619, 'vendor', 25, 'shipping', '7/270, KASI STREEET', 'T.SUBBULAPURAM', 'AUNDIPATTI', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(620, 'vendor', 26, 'billing', 'K.K 19 Society Opposite', 'KR Thoppur,\r\nT.Konagappadi (po)', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(621, 'vendor', 26, 'shipping', 'K.K 19 Society Opposite', 'KR Thoppur,\r\nT.Konagappadi (po)', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(622, 'vendor', 27, 'billing', '13, Boring Pipe Street', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(623, 'vendor', 27, 'shipping', '13, Boring Pipe Street', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(624, 'vendor', 28, 'billing', '21, Krishnan Pudhur', 'Ammapet', 'Salem', '636003', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(625, 'vendor', 28, 'shipping', '21, Krishnan Pudhur', 'Ammapet', 'Salem', '636003', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(626, 'vendor', 29, 'billing', 'No.2/212, \nSoundaram Nagar,', 'T.Subbulapuram,\nAundipatti,', 'Theni.', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(627, 'vendor', 29, 'shipping', 'No.2/212, \nSoundaram Nagar,', 'T.Subbulapuram,\nAundipatti,', 'Theni.', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(628, 'vendor', 30, 'billing', 'No: 83,\nAlazhapuri Street,', 'Thirunagaram,', 'Arupukottai.', '626 101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(629, 'vendor', 30, 'shipping', 'No: 83,\nAlazhapuri Street,', 'Thirunagaram,', 'Arupukottai.', '626 101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(630, 'vendor', 31, 'billing', '2/341, Soundaram Nagar,', 'T. Subbulapuram, Aundipatty(tk),', 'Theni (Dt).', '625 536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(631, 'vendor', 31, 'shipping', '2/341, Soundaram Nagar,', 'T. Subbulapuram, Aundipatty(tk),', 'Theni (Dt).', '625 536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(632, 'vendor', 32, 'billing', 'Near Ampedkar Nagar,\nNew 7th Ward,', 'Vetnary Hospital Backside', 'Tharamangalam', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(633, 'vendor', 32, 'shipping', 'Near Ampedkar Nagar,\nNew 7th Ward,', 'Vetnary Hospital Backside', 'Tharamangalam', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(634, 'vendor', 33, 'billing', '3-A,', 'Kuzhi Veetar Street,', 'Aruppukottai.', '626 101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(635, 'vendor', 33, 'shipping', '3-A,', 'Kuzhi Veetar Street,', 'Aruppukottai.', '626 101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(636, 'vendor', 34, 'billing', '14, Nesavalar Colony', '2nd Street', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(637, 'vendor', 34, 'shipping', '14, Nesavalar Colony', '2nd Street', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(638, 'vendor', 35, 'billing', '5-A pillayar kovil street', 'Puliyampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(639, 'vendor', 35, 'shipping', '5-A pillayar kovil street', 'Puliyampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(640, 'vendor', 36, 'billing', '5-18-113F, Postal Colony', 'Palayampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(641, 'vendor', 36, 'shipping', '5-18-113F, Postal Colony', 'Palayampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(642, 'vendor', 37, 'billing', '221-224, Lr. Ground, Adharsh Market-2,', 'Ring Road', 'Surat', '395002', 29, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(643, 'vendor', 37, 'shipping', '221-224, Lr. Ground, Adharsh Market-2,', 'Ring Road', 'Surat', '395002', 29, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(644, 'vendor', 38, 'billing', '18(1), Nesavalar Colony 3rd street', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(645, 'vendor', 38, 'shipping', '18(1), Nesavalar Colony 3rd street', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(646, 'vendor', 40, 'billing', '10/2-41, New Street Line - 2', 'Tharamangalam, Omalur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(647, 'vendor', 40, 'shipping', '10/2-41, New Street Line - 2', 'Tharamangalam, Omalur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(648, 'vendor', 41, 'billing', 'SF No: 692, Thanner Pandal', 'V Vellode', 'Erode', '638112', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(649, 'vendor', 41, 'shipping', 'SF No: 692, Thanner Pandal', 'V Vellode', 'Erode', '638112', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(650, 'vendor', 42, 'billing', 'No: 2/341,\nSoundaram Nagar,', 'Aundipati,', 'Theni.', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(651, 'vendor', 42, 'shipping', 'No: 2/341,\nSoundaram Nagar,', 'Aundipati,', 'Theni.', '625536', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(652, 'vendor', 44, 'billing', '32/8, 32/9, Thiruvudaiyan Salai 2nd Street', 'Sankarankovil', 'Tenkasi DT', '627756', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(653, 'vendor', 44, 'shipping', '32/8, 32/9, Thiruvudaiyan Salai 2nd Street', 'Sankarankovil', 'Tenkasi DT', '627756', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(654, 'vendor', 45, 'billing', '1-7/169 Sankari Main Road', 'Tharamangalam', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(655, 'vendor', 45, 'shipping', '1-7/169 Sankari Main Road', 'Tharamangalam', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(656, 'vendor', 46, 'billing', '6/15-12, Kattumale Vannara Street', 'Tharamangalam', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(657, 'vendor', 47, 'billing', 'No. 5/65-14, Gandhi Nagar 2nd Street', 'MGR NAGAR', 'Komarapalayam', '638183', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(658, 'vendor', 47, 'shipping', 'No. 5/65-14, Gandhi Nagar 2nd Street', 'MGR NAGAR', 'Komarapalayam', '638183', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(659, 'vendor', 48, 'billing', '320, Kaliamman Koil Street', 'Sakkampatty', 'Aundipatty', '625512', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(660, 'vendor', 48, 'shipping', '320, Kaliamman Koil Street', 'Sakkampatty', 'Aundipatty', '625512', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(661, 'vendor', 49, 'billing', '7c Sivan Pillayarpatti Koli the vandal street, puliyampatty', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(662, 'vendor', 49, 'shipping', '7c Sivan Pillayarpatti Koli the vandal street, puliyampatty', '', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(663, 'vendor', 50, 'billing', '5/93, Sivan Nagar,\nK.R Thoppur', 'Omalur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(664, 'vendor', 50, 'shipping', '5/93, Sivan Nagar,\nK.R Thoppur', 'Omalur', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(665, 'vendor', 51, 'billing', '131, Anna Salai, Rasipuram', '', 'Rasipuram', '637408', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(666, 'vendor', 51, 'shipping', '131, Anna Salai, Rasipuram', '', 'Rasipuram', '637408', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(667, 'vendor', 52, 'billing', 'No: 12, K.N. Muthusammy Chettiar Street,\nChockalingapuram,', 'Arupukottai', 'Arupukottai', '626 101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(668, 'vendor', 52, 'shipping', 'No: 12, K.N. Muthusammy Chettiar Street,\nChockalingapuram,', 'Arupukottai', 'Arupukottai', '626 101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(669, 'vendor', 53, 'billing', '2-1, North Sivaganapuram Street', 'Puliampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(670, 'vendor', 53, 'shipping', '2-1, North Sivaganapuram Street', 'Puliampatti', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:21', '2026-02-01 12:39:21'),
(671, 'vendor', 55, 'billing', '3/20-1, Ayyam Perumampatti', 'Palaiyur', 'Salem', '636302', 1, 1, 1, '2026-02-01 12:39:22', '2026-02-01 12:39:22'),
(672, 'vendor', 55, 'shipping', '3/20-1, Ayyam Perumampatti', 'Palaiyur', 'Salem', '636302', 1, 1, 1, '2026-02-01 12:39:22', '2026-02-01 12:39:22'),
(673, 'vendor', 56, 'billing', '11/199-1, Eswaran Kovil Street,\nK.R. Thoppur', 'T. Konagappadi (PO),\nOmalur (Tk)', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(674, 'vendor', 56, 'shipping', '11/199-1, Eswaran Kovil Street,\nK.R. Thoppur', 'T. Konagappadi (PO),\nOmalur (Tk)', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(675, 'vendor', 57, 'billing', 'No: 2/183,\nVICHAITHARI PATTARAI,', 'ALINCHIKUTHUPALLAM,\nMATTUPATTI POST,', 'MUSIRI.', '621211', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(676, 'vendor', 57, 'shipping', 'No: 2/183,\nVICHAITHARI PATTARAI,', 'ALINCHIKUTHUPALLAM,\nMATTUPATTI POST,', 'MUSIRI.', '621211', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(677, 'vendor', 58, 'billing', 'oldno,6/16-10A, New 6-3/10-A,\nKattaiyamuthu Mudali Street,', 'Tharamangalam', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(678, 'vendor', 58, 'shipping', 'oldno,6/16-10A, New 6-3/10-A,\nKattaiyamuthu Mudali Street,', 'Tharamangalam', 'Salem', '636502', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(679, 'vendor', 60, 'billing', 'NO: 13-18-90, S.V.T. Nagar,\nMadurai Theni Road,', 'Jakkampatty,\nAundipatty(tk),', 'Theni(Dt).', '625 512', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(680, 'vendor', 60, 'shipping', 'NO: 13-18-90, S.V.T. Nagar,\nMadurai Theni Road,', 'Jakkampatty,\nAundipatty(tk),', 'Theni(Dt).', '625 512', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(681, 'vendor', 61, 'billing', '10, Boring Pipe Street', 'Thirunagaram', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23'),
(682, 'vendor', 61, 'shipping', '10, Boring Pipe Street', 'Thirunagaram', 'Aruppukottai', '626101', 1, 1, 1, '2026-02-01 12:39:23', '2026-02-01 12:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) UNSIGNED NOT NULL,
  `agent_name` varchar(100) NOT NULL,
  `commission_percentage` decimal(5,2) DEFAULT 0.00,
  `phone_number` varchar(20) NOT NULL,
  `address_proof_type` varchar(50) NOT NULL,
  `address_proof_id` varchar(50) NOT NULL,
  `address_proof_front` varchar(255) DEFAULT NULL,
  `address_proof_back` varchar(255) DEFAULT NULL,
  `address_1` text NOT NULL,
  `address_2` text DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state_id` int(11) UNSIGNED DEFAULT NULL,
  `country_id` int(11) UNSIGNED DEFAULT NULL,
  `pincode` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `agent_name`, `commission_percentage`, `phone_number`, `address_proof_type`, `address_proof_id`, `address_proof_front`, `address_proof_back`, `address_1`, `address_2`, `village`, `city`, `state_id`, `country_id`, `pincode`, `created_at`, `updated_at`) VALUES
(1, 'caDfba', 2.00, '09626077333', 'sdgvsdf', '18/3A, Krishnan Kovil Street, C.P Kannaiah Road', NULL, NULL, 'Opp: Gopinath Engineering Works', '', 'sdfsf', 'RASIPURAM', 1, 1, '637408', '2026-01-27 06:07:06', '2026-01-27 06:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `agent_payments`
--

CREATE TABLE `agent_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `payment_number` varchar(50) NOT NULL,
  `agent_id` int(11) UNSIGNED NOT NULL,
  `payment_date` date NOT NULL,
  `payment_mode` enum('Cash','Bank Transfer','Cheque','UPI','Other') NOT NULL DEFAULT 'Cash',
  `bank_account_id` int(11) UNSIGNED DEFAULT NULL,
  `bank_transaction_id` int(11) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_payments`
--

INSERT INTO `agent_payments` (`id`, `payment_number`, `agent_id`, `payment_date`, `payment_mode`, `bank_account_id`, `bank_transaction_id`, `amount`, `reference_number`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, 'AGPAY-202601-0001', 1, '2026-01-27', 'Cash', NULL, NULL, 362250.00, '', '', 1, NULL, '2026-01-27 07:17:58', '2026-01-27 07:17:58'),
(3, 'AGPAY-202601-0002', 1, '2026-01-27', 'Cash', NULL, NULL, 24.00, '', '', 1, NULL, '2026-01-27 08:39:13', '2026-01-27 08:39:13');

-- --------------------------------------------------------

--
-- Table structure for table `agent_payment_items`
--

CREATE TABLE `agent_payment_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `agent_payment_id` int(11) UNSIGNED NOT NULL,
  `invoice_id` int(11) UNSIGNED NOT NULL,
  `commission_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) UNSIGNED NOT NULL,
  `employee_id` int(11) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT 0.00,
  `shortfall_hours` decimal(5,2) DEFAULT 0.00,
  `surplus_hours` decimal(5,2) DEFAULT 0.00,
  `is_recovered` tinyint(1) DEFAULT 0,
  `status` enum('Present','Absent','Half Day','Holiday') DEFAULT 'Absent',
  `multiplier` decimal(3,1) DEFAULT 1.0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `attendance_date`, `check_in_time`, `check_out_time`, `hours_worked`, `shortfall_hours`, `surplus_hours`, `is_recovered`, `status`, `multiplier`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-01-21', '10:00:00', '18:00:00', 0.00, 0.00, 0.00, 0, 'Absent', 1.0, '2026-01-21 05:44:25', '2026-01-21 05:45:50'),
(2, 1, '2026-01-20', '09:55:00', '18:00:00', 8.08, 0.00, 0.00, 1, 'Present', 1.0, '2026-01-21 05:46:08', '2026-01-21 06:54:32'),
(3, 1, '2026-01-01', '10:00:00', '18:00:00', 0.00, 0.00, 0.00, 0, 'Present', 1.0, '2026-01-21 06:37:44', '2026-01-21 06:39:36'),
(4, 1, '2026-01-19', '10:05:00', '18:00:00', 7.92, 0.00, 0.00, 1, 'Present', 1.0, '2026-01-21 06:51:31', '2026-01-21 06:54:32'),
(5, 1, '2026-01-18', '09:55:00', '18:10:00', 8.25, 0.00, 0.00, 1, 'Present', 1.0, '2026-01-21 06:53:54', '2026-01-21 06:53:54'),
(6, 1, '2026-02-01', NULL, NULL, 0.00, 0.00, 0.00, 0, 'Absent', 1.0, '2026-02-01 07:59:58', '2026-02-01 13:30:59');

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) UNSIGNED NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `ifsc_code` varchar(20) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `account_type` enum('Bank','Cash') DEFAULT 'Bank',
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `bank_name`, `account_number`, `ifsc_code`, `branch_name`, `account_type`, `current_balance`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CITY UNION BANK', '510909010007385', 'CUBI0000509', 'Rasipuram', 'Bank', 15000.00, 'active', '2026-01-21 11:19:21', '2026-01-21 11:19:45'),
(2, 'Cash in Hand', 'CASH-001', 'N/A', 'Cash Account', 'Cash', 0.00, 'active', '2026-01-28 16:05:28', '2026-01-28 16:05:28');

-- --------------------------------------------------------

--
-- Table structure for table `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) UNSIGNED NOT NULL,
  `bank_account_id` int(11) UNSIGNED NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('credit','debit') NOT NULL DEFAULT 'credit',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `balance_after` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_reconciled` tinyint(1) DEFAULT 0,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_transactions`
--

INSERT INTO `bank_transactions` (`id`, `bank_account_id`, `transaction_date`, `description`, `type`, `amount`, `reference_number`, `balance_after`, `is_reconciled`, `reference_type`, `reference_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-01-21', 'INVIOEC', 'credit', 15000.00, '123546879', 15000.00, 1, 'invoice_payment', 5, '2026-01-21 11:19:45', '2026-01-21 11:19:45');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) UNSIGNED NOT NULL,
  `vendor_id` int(11) UNSIGNED NOT NULL,
  `bill_number` varchar(50) NOT NULL,
  `zoho_bill_id` varchar(100) DEFAULT NULL,
  `zoho_sync_status` enum('Pending','Synced','Failed') DEFAULT 'Pending',
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('Draft','Open','Paid','Partially Paid','Overdue','Void') DEFAULT 'Draft',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `discount_type` enum('Amount','Percentage') DEFAULT 'Amount',
  `shipping_charge` decimal(12,2) DEFAULT 0.00,
  `roundoff_amount` decimal(10,2) DEFAULT 0.00,
  `cgst_amount` decimal(12,2) DEFAULT 0.00,
  `sgst_amount` decimal(12,2) DEFAULT 0.00,
  `igst_amount` decimal(12,2) DEFAULT 0.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `balance` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_inter_state` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `vendor_id`, `bill_number`, `zoho_bill_id`, `zoho_sync_status`, `bill_date`, `due_date`, `reference_number`, `status`, `subtotal`, `discount_amount`, `discount_type`, `shipping_charge`, `roundoff_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `tax_amount`, `total_amount`, `paid_amount`, `balance`, `notes`, `terms`, `created_by`, `updated_by`, `zoho_sync_at`, `created_at`, `updated_at`, `is_inter_state`) VALUES
(2, 10, '27', '698964000007121125', 'Synced', '2026-06-25', '2026-06-25', '', 'Overdue', 0.00, 0.00, 'Amount', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 37275.00, 0.00, 37275.00, NULL, NULL, NULL, NULL, '2026-06-30 03:09:46', '2026-06-30 03:09:46', '2026-06-30 03:09:46', 0),
(3, 54, '26', '698964000007121108', 'Synced', '2026-06-17', '2026-06-17', '', 'Paid', 0.00, 0.00, 'Amount', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 52605.00, 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-06-30 03:09:46', '2026-06-30 03:09:46', '2026-06-30 03:09:46', 0),
(4, 22, '28', '698964000007121090', 'Synced', '2026-06-19', '2026-06-19', '', 'Overdue', 0.00, 0.00, 'Amount', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 95351.00, 0.00, 95351.00, NULL, NULL, NULL, NULL, '2026-06-30 03:09:46', '2026-06-30 03:09:46', '2026-06-30 03:09:46', 0);

-- --------------------------------------------------------

--
-- Table structure for table `bill_items`
--

CREATE TABLE `bill_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `bill_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `rate` decimal(12,2) DEFAULT 0.00,
  `tax_id` int(11) UNSIGNED DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT 0.00,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `amount` decimal(12,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_reminders`
--

CREATE TABLE `calendar_reminders` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `reminder_date` date NOT NULL,
  `reminder_time` time DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('pending','completed') DEFAULT 'pending',
  `is_notified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar_reminders`
--

INSERT INTO `calendar_reminders` (`id`, `title`, `description`, `reminder_date`, `reminder_time`, `priority`, `status`, `is_notified`, `created_at`, `updated_at`) VALUES
(1, 'dfazdf', 'zdfAVSd', '2026-01-30', '00:00:00', 'medium', 'completed', 1, '2026-01-30 10:16:29', '2026-01-30 10:43:47');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `iso_code_2` char(2) DEFAULT NULL,
  `iso_code_3` char(3) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `iso_code_2`, `iso_code_3`, `phone_code`, `created_at`, `updated_at`) VALUES
(1, 'India', 'IN', 'IND', '91', NULL, NULL),
(2, 'United States', 'US', 'USA', '1', NULL, NULL),
(3, 'United Kingdom', 'GB', 'GBR', '44', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) UNSIGNED NOT NULL,
  `zoho_contact_id` varchar(100) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `gst_type` enum('Regular','Composition','Unregistered','Consumer') NOT NULL DEFAULT 'Unregistered',
  `gstin` varchar(15) DEFAULT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `balance_type` enum('Dr','Cr') DEFAULT 'Dr',
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `credit_period_days` int(5) DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `agent_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `zoho_contact_id`, `name`, `contact_person`, `email`, `website`, `phone`, `whatsapp_number`, `gst_type`, `gstin`, `pan_number`, `opening_balance`, `balance_type`, `credit_limit`, `credit_period_days`, `status`, `agent_id`, `created_at`, `updated_at`, `notes`, `zoho_sync_at`) VALUES
(3, '698964000001520503', 'A Indu W/O Ravi Kiran', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(4, '698964000001304001', 'A Ravi', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(5, '698964000000495076', 'A V S Apparels', NULL, '', '', '0000000000', NULL, 'Regular', '33AAXFA9978Q1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(6, '698964000000110001', 'Abhinandan', NULL, '', '', '0000000000', NULL, 'Regular', '19AEZPB3532MIZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(7, '698964000000453001', 'Advi Fashion', NULL, '', '', '0000000000', NULL, 'Regular', '33BHUPD0022R1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(8, '698964000004582368', 'AGRAWAL HANDLOOM STORE', NULL, '', '', '0000000000', NULL, 'Regular', '10ABOFA5317R1Z6', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(9, '698964000004020059', 'AISHWARYA', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(10, '698964000000608001', 'Akshaya Durga Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '37AJZPT2935P1ZW', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(11, '698964000000438003', 'Akshaya Silks & Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '29AOFPP0187E1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(12, '698964000002552005', 'Alankar Fashion', NULL, '', '', '0000000000', NULL, 'Regular', '29AAXFA5939M1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(13, '698964000002614145', 'Ameya Family Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36ABAFA6978J1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(14, '698964000000504001', 'Anita Tailor & Cloth Marchant', NULL, '', '', '0000000000', NULL, 'Regular', '27AJSPC5259Q1ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(15, '698964000002291003', 'ANJALI HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '36ABAPD2222L1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(16, '698964000004824005', 'AnnamalaiRaja Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33AAXFA5978J1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(17, '698964000000404001', 'Anthony Creation', NULL, '', '', '0000000000', NULL, 'Regular', '27AJBPN6640H1Z6', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(18, '698964000006376102', 'ARBAZ TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '36AAKPI6506K1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(19, '698964000006376005', 'ARBAZ TEXTILES PRIVATE LIMITED', NULL, '', '', '0000000000', NULL, 'Regular', '36ABBCA9277A1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(20, '698964000000306001', 'ARRS SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AADFA5815A1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(21, '698964000000369001', 'ARRS SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AADFA5815A1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(22, '698964000000685001', 'ARRS SILKS', NULL, '', 'www.arrssilks.in', '0000000000', NULL, 'Regular', '33AADFA5815A1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(23, '698964000000700067', 'ARUNA TEX', NULL, '', '', '0000000000', NULL, 'Regular', '33BNFPA2126J1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(24, '698964000004020001', 'Haseena Manickam', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(25, '698964000000954001', 'B A TEX', NULL, '', '', '0000000000', NULL, 'Regular', '33AHJPN4660M1ZZ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(26, '698964000001899090', 'B Ravi', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(27, '698964000000808001', 'B.S. SUBIKSHAA SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33BCLPB6662D2ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(28, '698964000000919126', 'Balabadra Narasimhamurthy Firm', NULL, '', '', '0000000000', NULL, 'Regular', '37ABMPB3158M1ZD', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(29, '698964000005033001', 'BALAJI', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(30, '698964000002138128', 'Balaji Fashion', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(31, '698964000000731001', 'BAPUJEE CLOTH SHOP', NULL, '', '', '0000000000', NULL, 'Regular', '37ABUPY5808R1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(32, '698964000006089078', 'M/S BASUDEV TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '21ABLFM0200E1Z5', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(33, '698964000002614003', 'Bhadrakali Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36ABAFB3503A1ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(34, '698964000000208015', 'BHANU TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '37AATFB2692Q1ZG', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(35, '698964000001098001', 'BHAVANI HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '29BCUPJ8648A1Z3', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(36, '698964000000234001', 'BHAVANI SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '36AUHPS0295G1ZD', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(37, '698964000001462003', 'Bhonagiri Selections', NULL, '', '', '0000000000', NULL, 'Regular', '36AAEFB8151B1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(38, '698964000001450095', 'Bhonagiri Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36AAXFB3213F1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(39, '698964000002216870', 'BOHRA KEVALCHAND MOHANLAL', NULL, '', '', '0000000000', NULL, 'Regular', '29AAEFB9378C1Z8', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(40, '698964000002408001', 'Bojja Lakshmi Narayana', NULL, '', '', '0000000000', NULL, 'Regular', '37AAIHB1669Q1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(41, '698964000005094009', 'BOMBAY CLOTH HOUSE', NULL, '', '', '0000000000', NULL, 'Regular', '36ABDFB4984C1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(42, '698964000001415003', 'BUVANAGIRI HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '33AALFB0677K1ZB', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(43, '698964000000239011', 'Chandana Brothers Shopping Mall Pvt,Ltd.,', NULL, '', '', '0000000000', NULL, 'Regular', '37AAFCC6436L3Z9', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(44, '698964000004966005', 'Chandana Brothers Silks & Jewellers Pvt. Ltd.', NULL, '', '', '0000000000', NULL, 'Regular', '36AAECC7196G1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(45, '698964000001570003', 'Chandana Brothers Textiles & Jewellers Pvt Ltd', NULL, '', '', '0000000000', NULL, 'Regular', '36AACCC3901F1Z5', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(46, '698964000000144103', 'Chandana Brothers Textiles & Jewellers Pvt. Ltd.,', NULL, '', '', '0000000000', NULL, 'Regular', '37AACCC3901F1Z3', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(47, '698964000000144089', 'Chandana Saree Mandir', NULL, '', '', '0000000000', NULL, 'Regular', '37AABFC4965F1ZG', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(48, '698964000001733007', 'Chandana\'s Textiles and Readymades', NULL, '', '', '0000000000', NULL, 'Regular', '37AADFC3783H1ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(49, '698964000000336001', 'CHANDRAMURUGAN JAVULI STORE', NULL, '', '', '0000000000', NULL, 'Regular', '33APYPC5244B1Z3', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(50, '698964000005167071', 'Chellas Readymade', NULL, '', '', '0000000000', NULL, 'Regular', '33AENPT1831K1Z8', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(51, '698964000000713001', 'Chettinad Thari', NULL, '', '', '0000000000', NULL, 'Regular', '33AALFC7147H1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(52, '698964000004754067', 'CHITHIRAI', NULL, '', '', '0000000000', NULL, 'Regular', '33AAQFC6362D1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(53, '698964000000442009', 'Chitra Kailasam', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(54, '698964000000583001', 'M/s Chandana Bros Shopping Mall Pvt. Ltd.,', NULL, '', '', '0000000000', NULL, 'Regular', '37AAFCC6436L3Z9', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(55, '698964000001456001', 'Damarla Yogachalam', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(56, '698964000000634001', 'Deepa Silks', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(57, '698964000000581001', 'Dev Ganga Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '33AGMPC3889R1ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(58, '698964000000217001', 'Deva', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(59, '698964000002314005', 'DEVI CUTPIECES', NULL, '', '', '0000000000', NULL, 'Regular', '37AQHPN0668G1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(60, '698964000003191001', 'Devi Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '37AVLPA3404E1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(61, '698964000002389005', 'Devi Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29AATFD9918K1ZG', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(62, '698964000000245001', 'Dhanalakshmi Stores', NULL, '', '', '0000000000', NULL, 'Regular', '29AADFD8641F1ZD', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(63, '698964000001329091', 'DHANALAXMI SILK SAREE HOUSE', NULL, '', '', '0000000000', NULL, 'Regular', '36AAKFD3819Q1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(64, '698964000000905007', 'Dilipkumar Rajabhau Vaikunth', NULL, '', '', '0000000000', NULL, 'Regular', '36AASPV1662M1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(65, '698964000000935005', 'Divya Fancy Cloth Stores', NULL, '', '', '0000000000', NULL, 'Regular', '36AFVPT2475G1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(66, '698964000000084299', 'DR ENTERPRISE', NULL, '', 'www.nirmalsarees.com', '0000000000', NULL, 'Regular', '19ADHPD6050H1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(67, '698964000000167001', 'DR Enterprise', NULL, '', '', '0000000000', NULL, 'Regular', '19ADHPD6050H1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(68, '698964000000601039', 'Fahmida Silks', NULL, '', '', '0000000000', NULL, 'Regular', '37AAJPF6917G1ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(69, '698964000000568001', 'Family Wedding Centre', NULL, '', '', '0000000000', NULL, 'Regular', '32AADFF6024A1Z9', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(70, '698964000002712005', 'G.K Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36ACAPG3231B1ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(71, '698964000000134487', 'G.V. Corporation', NULL, '', '', '0000000000', NULL, 'Regular', '33ATAPS0503R1ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(72, '698964000000509157', 'GAAP International', NULL, '', '', '0000000000', NULL, 'Regular', '19APUPP5837K1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(73, '698964000000714001', 'GANAPATHY TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33BPTPM1523B1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(74, '698964000001605225', 'GANDHI HANDLOOM SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '37ACJPG0576E1ZQ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(75, '698964000000187001', 'Ganesh Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '36AUFPG7623N1ZA', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(76, '698964000001258017', 'GANESH TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '29AEXPA7159P1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(77, '698964000003596003', 'GAYATHRI SAREE HOUSE', NULL, '', '', '0000000000', NULL, 'Regular', '36ASIPV7711M1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(78, '698964000000341079', 'Geethanjali Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29AAOFG1029P1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(79, '698964000000699120', 'Ghenu Bibi', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(80, '698964000000797032', 'AMIT BHAMBANI', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(81, '698964000004599122', 'GIRDHARILAL AND COMPANY', NULL, '', '', '0000000000', NULL, 'Regular', '21AABFG1708L1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(82, '698964000001891190', 'Golden Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33FFIPS8547N2ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(83, '698964000000318001', 'GOWRI GANESH,SILKS &SAREES,', NULL, '', '', '0000000000', NULL, 'Regular', '37ALOPV6520F1ZP', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(84, '698964000001605088', 'GRAND GANDHI KHADI BHANDAR', NULL, '', '', '0000000000', NULL, 'Regular', '37AADHJ1784M1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(85, '698964000002056140', 'Gunturu Manikumar', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(86, '698964000001899001', 'GURRAPU KATTAIAH CLOTH MERCHANTS', NULL, '', '', '0000000000', NULL, 'Regular', '36ABIPG4554E1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(87, '698964000000679142', 'H M', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(88, '698964000000480001', 'Hariny Silks', NULL, '', '', '0000000000', NULL, 'Regular', '32AJFPR2968G1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(89, '698964000001416087', 'Hemaraj Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '36CLTPS9015B1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(90, '698964000000741001', 'HOME ESSENTIALS', NULL, '', '', '0000000000', NULL, 'Regular', '33AIYPR9121M1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(91, '698964000000457001', 'IBAJ Trading Establishment', NULL, '', '', '0000000000', NULL, 'Regular', '32AACFI2118K1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(92, '698964000000705001', 'IDEAL FASHION', NULL, '', '', '0000000000', NULL, 'Regular', '29AFAPH7847B1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(93, '698964000000392005', 'JAILAKSHMI SAREE HOUSE', NULL, '', '', '0000000000', NULL, 'Regular', '19AACFJ4526A1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(94, '698964000000082001', 'Janaki Bastra Bipani', NULL, '', '', '0000000000', NULL, 'Regular', '19AADFJ9787E1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(95, '698964000000194101', 'Jayakumar Hand Looms', NULL, '', '', '0000000000', NULL, 'Regular', '37AQVPN9264G1ZZ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(96, '698964000001453001', 'JAYAKUMAR TEXTILE STORES', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(97, '698964000006198140', 'JAYSHREE SAREE CENTER', NULL, '', '', '0000000000', NULL, 'Regular', '21ABFPM4401M1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(98, '698964000006543001', 'Jhanvi Colors', NULL, '', '', '0000000000', NULL, 'Regular', '33AKUPR0201J1Z6', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(99, '698964000001100003', 'Jyothi Ladies Special', NULL, '', '', '0000000000', NULL, 'Regular', '33BKJPK3702K1Z9', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(100, '698964000001258049', 'K S PATTU CENTRE', NULL, '', '', '0000000000', NULL, 'Regular', '33DYFPK4972M1ZX', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(101, '698964000001687074', 'K.C. SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '29AJAPV7985Q1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(102, '698964000000712001', 'K.K. Siva Kumar', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(103, '698964000000281015', 'K.Muthukaruppan & Sons', NULL, '', '', '0000000000', NULL, 'Regular', '34AAKFK2150P1Z3', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(104, '698964000001821001', 'KALAIMAN DISTRIBUTERS', NULL, '', '', '0000000000', NULL, 'Regular', '33AAKPE9987C1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(105, '698964000000567001', 'Kalsankar Deepam Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29ADKPP9025F1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(106, '698964000000123001', 'Kalyan Fabrics', NULL, '', '', '0000000000', NULL, 'Regular', '32AAGFK7680L1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(107, '698964000000276001', 'Kanchipuram Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29AQUPK3843R1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(108, '698964000004718003', 'KANISH HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '33AGHPN8683M1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(109, '698964000001700001', 'KANNAN BLOUSE MATCHING CENTRE', NULL, '', '', '0000000000', NULL, 'Regular', '32DJFPA2609E1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(110, '698964000000778001', 'Kannan Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '32ACSPR8682B1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(111, '698964000005971806', 'Karthika Cotton Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33LYQPS5733K1ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(112, '698964000002732502', 'Kartik Handloom & Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '37CAYPN4381H1ZX', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(113, '698964000003644103', 'KARUR SARVODAYA SANGH', NULL, '', '', '0000000000', NULL, 'Regular', '33AABAK3190P1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(114, '698964000006157128', 'KESAR SUMAN SAREE', NULL, '', '', '0000000000', NULL, 'Regular', '10ADGPA8067J1ZS', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(115, '698964000003191077', 'Krishna Handlooms', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(116, '698964000002526003', 'Krishna Nandini Cloth Centre', NULL, '', '', '0000000000', NULL, 'Regular', '36AEUPV6198Q1ZV', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(117, '698964000001121001', 'Lakshmi silks', NULL, '', '', '0000000000', NULL, 'Regular', '33aadfl7888c1z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(118, '698964000003835001', 'Lalithaa Fashion Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36BDOPJ8218B1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(119, '698964000000781001', 'M. Srinivas Rao', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(120, '698964000000543001', 'M.G. Family Shop', NULL, '', '', '0000000000', NULL, 'Regular', '29BFJPR6808E1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(121, '698964000000483003', 'M.K FABRICS', NULL, '', 'www.mkfabrics.in', '0000000000', NULL, 'Regular', '32AACFB8032F1Z0', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(122, '698964000003176001', 'M.K Fashion', NULL, '', '', '0000000000', NULL, 'Regular', '33AIEPH8895G1ZZ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(123, '698964000001935594', 'M.P Silks', NULL, '', '', '0000000000', NULL, 'Regular', '33AAAPU4767E2ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(124, '698964000000381001', 'M.R. SILK SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '24BWAPM1869C1ZS', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(125, '698964000000223001', 'M/S C.M Sari Depot Saree Merchant', NULL, '', '', '0000000000', NULL, 'Regular', '29AAEFC3358D1ZL', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(126, '698964000000647001', 'M/S. R. Ramalingam & Sons', NULL, '', '', '0000000000', NULL, 'Regular', '36AAOFM0188P1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(127, '698964000000472001', 'MAA KALI ENTERPRISE', NULL, '', '', '0000000000', NULL, 'Regular', '19AJHPK3794P1ZA', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(128, '698964000001326001', 'Mahalakshmi jawli store', NULL, '', '', '0000000000', NULL, 'Regular', '33ABAFM5562A1ZS', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(129, '698964000000629075', 'Maharani Silks', NULL, '', '', '0000000000', NULL, 'Regular', '33ABJFM9678G1ZQ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(130, '698964000000726001', 'Mangala Saree Centre', NULL, '', '', '0000000000', NULL, 'Regular', '37AAHPV2899M1ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(131, '698964000000865115', 'MANGALORE HANDLOOM SAREES EMPORIUM', NULL, '', '', '0000000000', NULL, 'Regular', '29AACFM3777C1Z8', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(132, '698964000000971001', 'MANISH FASHION', NULL, '', '', '0000000000', NULL, 'Regular', '37AJVPT8339D1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(133, '698964000002186080', 'ME TOO', NULL, '', '', '0000000000', NULL, 'Regular', '32BRBPB2429R2ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(134, '698964000001615001', 'Mehta Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33AMGPK1978B1ZA', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(135, '698964000000547001', 'Menaka Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33ABHPG1630J1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(136, '698964000000856001', 'MITHRA SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '33GJYPS2209J1ZL', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(137, '698964000000766001', 'MM Collection', NULL, '', '', '0000000000', NULL, 'Regular', '29ALMPK6066B1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(138, '698964000005107003', 'MOUNIKA TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '37HSSPS5291G2ZQ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(139, '698964000000976001', 'MOUNY STYELS', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(140, '698964000002837001', 'Mudaliar Silks Palace ', NULL, '', '', '0000000000', NULL, 'Regular', '33AALPS4010P1ZG', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(141, '698964000000281029', 'Muthu Silk Plaza', NULL, '', '', '0000000000', NULL, 'Regular', '34AARFM9722G1ZX', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(142, '698964000000974078', 'N.PARVATHY SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFCN4639L1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(143, '698964000000398042', 'N.PARVATHY TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33AAECN1813F1ZZ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(144, '698964000000194087', 'N.Venkateswararao', NULL, '', '', '0000000000', NULL, 'Regular', '37AATPN5742K1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(145, '698964000000102001', 'Nakka Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '37ABEPN9434A1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(146, '698964000000913001', 'NATARAJ TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '37AABFN7717J2ZZ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(147, '698964000001313199', 'Naveena Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33JWFPS6122H1ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(148, '698964000004144029', 'NAVRATNA SAREES AND READYMADE MART', NULL, '', '', '0000000000', NULL, 'Regular', '27AATFN6026E1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(149, '698964000000639030', 'Nirvaan Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '29AAIFN4633L1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(150, '698964000001492003', 'NoolLooms', NULL, '', '', '0000000000', NULL, 'Regular', '33BNTPG8497C1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(151, '698964000000398028', 'NPT TEXTILES PRAVATELIMITED', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFCN4639L1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(152, '698964000000109001', 'Nuthan Cloth Centre', NULL, '', '', '0000000000', NULL, 'Regular', '29AASFN5487C1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(153, '698964000000930001', 'OM SILK HOUSE', NULL, '', '', '0000000000', NULL, 'Regular', '37ACWPB5651J1Z5', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(154, '698964000003005049', 'P. RAMAIAH', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(155, '698964000000106001', 'P.K.Mohanan', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(156, '698964000000178001', 'P.Narayana Nayak & Son', NULL, '', '', '0000000000', NULL, 'Regular', '29AAXPN6773E1ZV', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(157, '698964000000418116', 'P.S.R.BROTHERS', NULL, '', '', '0000000000', NULL, 'Regular', '33AEJPP1678N1ZX', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(158, '698964000000527037', 'Pachaiyappa\'s Collections', NULL, '', '', '0000000000', NULL, 'Regular', '34BNAPV9870R1ZW', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(159, '698964000001853445', 'PADMALAYA', NULL, '', '', '0000000000', NULL, 'Regular', '33AAOFP5439J1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(160, '698964000000283001', 'PARAMA SAREE KENDRA', NULL, '', '', '0000000000', NULL, 'Regular', '19BPLPB6099F1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(161, '698964000000412001', 'Perisetty Venkateswararao', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(162, '698964000000618001', 'Perumal Samy Traders', NULL, '', '', '0000000000', NULL, 'Regular', '33FBJPS2632R2ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(163, '698964000005878005', 'POOJITHA HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '37ALEPG1104G1ZQ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(164, '698964000000600001', 'Poona Saree Centre Nx', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(165, '698964000000144001', 'Pothys Private Limited', NULL, '', '', '0000000000', NULL, 'Regular', '33AAHCP7473N1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(166, '698964000003480189', 'PRANEETHAA PRINTS FANCY SAREES', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(167, '698964000000210031', 'Prasiddhi Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29AADFH2582D1ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(168, '698964000000325378', 'Pratap Saree Centre', NULL, '', '', '0000000000', NULL, 'Regular', '19AIIPK5815B1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(169, '698964000005880330', 'PRIYADARSHINI SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33ABKPS2489L1Z0', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(170, '698964000001366001', 'PSR SILK SAREES INDIA PRIVATE LIMITED', NULL, '', '', '0000000000', NULL, 'Regular', '33AAECP7548M1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(171, '698964000000780001', 'PSR SILKS SAREES INDIA PVT LTD', NULL, '', '', '0000000000', NULL, 'Regular', '33AAECP7548M1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(172, '698964000002205280', 'R K Cloth Emporium', NULL, '', '', '0000000000', NULL, 'Regular', '37AACFR6401Q1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(173, '698964000001611339', 'R M HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '33ABCFR2905C1ZS', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(174, '698964000001403105', 'R N Samy Javuli Store', NULL, '', '', '0000000000', NULL, 'Regular', '33AAVPS6673C1Z8', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(175, '698964000000281001', 'R.N.Samy Javuli Trader', NULL, '', '', '0000000000', NULL, 'Regular', '33AAVPS6674F1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(176, '698964000000388001', 'R.N.SAMY JAVULI TRADER', NULL, '', '', '0000000000', NULL, 'Regular', '33AAVPS6674F1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(177, '698964000003583005', 'RAGHAVENDRA TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '37AAFFR9171E1ZZ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(178, '698964000005156013', 'RAJA RAJESWARI HALL', NULL, '', '', '0000000000', NULL, 'Regular', '37EQTPK2057L1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(179, '698964000000698001', 'RAJA SILK PALACE', NULL, '', '', '0000000000', NULL, 'Regular', '33AAAFR6455N1ZW', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(180, '698964000000698016', 'RAJA SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFFR5534D1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(181, '698964000003049060', 'Rajeswari Silks', NULL, '', '', '0000000000', NULL, 'Regular', '36AMOPP5138L1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(182, '698964000005031009', 'RAJKAMAL CLOTH SHOWROOM', NULL, '', '', '0000000000', NULL, 'Regular', '37AACFR6517P2ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(183, '698964000001731001', 'RAMESH TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '37ABQPG0970J1ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(184, '698964000001008027', 'RANGOLI CREATION', NULL, '', '', '0000000000', NULL, 'Regular', '37AAXFR6397J1Z0', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(185, '698964000004714339', 'RANI SATI', NULL, '', '', '0000000000', NULL, 'Regular', '10ACEPJ8390C1Z0', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(186, '698964000000696082', 'Rathi Silk and Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33ACCPR2061A1ZA', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(187, '698964000002935001', 'Ravi B', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(188, '698964000000944087', 'RG EXPORTS', NULL, '', '', '0000000000', NULL, 'Regular', '33CIDPR1803B1ZW', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(189, '698964000001892003', 'RK Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36BABPK6076J1ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(190, '698964000000160066', 'Rmkv Fabrics Pvt. Ltd.', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFCR4022H1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(191, '698964000000085181', 'RMKV SILKS PVT LIMITED', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFCR4024B1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(192, '698964000000084618', 'RMKV SILKS PVT LIMITED', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFCR4024B1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(193, '698964000000059060', 'RMKV SILKS PVT LTD', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFCR4024B1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(194, '698964000000941057', 'Roop Mohini Suits & Sarees Pvt Ltd', NULL, '', '', '0000000000', NULL, 'Regular', '19AAGCR8593L1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(195, '698964000000434003', 'S .KARTHIK', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(196, '698964000001245001', 'S P P Silks', NULL, '', '', '0000000000', NULL, 'Regular', '33ABRFS9234Q1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(197, '698964000001748001', 'S.K.PONNUSAMY AND BROTHERS', NULL, '', '', '0000000000', NULL, 'Regular', '33AADPN0643F1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(198, '698964000003416033', 'S.KUMARAN', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(199, '698964000006488005', 'SAAVITHRI HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '36AWRPD7816L1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(200, '698964000003700037', 'SAI VARUN SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33ACZFS3178A1ZV', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(201, '698964000004663003', 'SANGEETHA SILK', NULL, '', '', '0000000000', NULL, 'Regular', '33ABFPG5463K1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(202, '698964000000587001', 'SANTHI TRADING ENTERPRISES', NULL, '', '', '0000000000', NULL, 'Regular', '32AKCPG8071B2ZP', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(203, '698964000001135001', 'SARATHA\'S', NULL, '', '', '0000000000', NULL, 'Regular', '33AAAFS8843D1ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(204, '698964000000111001', 'SARATHI TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33AADPU9540J1ZD', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(205, '698964000005133003', 'SATHIYARAJ SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AODPL0502M1ZB', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(206, '698964000000919194', 'SATYA KRISHNA CUT PIECES', NULL, '', '', '0000000000', NULL, 'Regular', '37AJFPC5011M1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(207, '698964000001590288', 'SELVI T', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(208, '698964000004489001', 'SGR', NULL, '', '', '0000000000', NULL, 'Regular', '33FOPPS7926B1ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(209, '698964000000785016', 'SHA HIRACHAND VIMALKUMAR & CO', NULL, '', '', '0000000000', NULL, 'Regular', '29ADFPJ8445C1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(210, '698964000002563005', 'Shabra Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33BLDPB8196B1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(211, '698964000002400005', 'Shakthi Silk Route', NULL, '', '', '0000000000', NULL, 'Regular', '29ABEFS0698D1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(212, '698964000000081001', 'Shanthakumari Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '33ANMPN6647F1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(213, '698964000001585218', 'Shivani Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '37ADWFS8731R1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(214, '698964000004711007', 'SHREE ARUN VASTRALAYA', NULL, '', '', '0000000000', NULL, 'Regular', '10AAGHP1295L1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(215, '698964000001556001', 'Shree Dholi Sati Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29ADLPS9765L1ZB', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(216, '698964000006435005', 'SHREE DURGA SAREE MANDIR', NULL, '', '', '0000000000', NULL, 'Regular', '37AFDFS7977L1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(217, '698964000003245001', 'Shree Tex Engineers', NULL, '', '', '0000000000', NULL, 'Regular', '27ABQFS4345H1ZQ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(218, '698964000000871001', 'Shri Kalaivani', NULL, '', '', '0000000000', NULL, 'Regular', '27AGEPA6799C1ZD', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42');
INSERT INTO `customers` (`id`, `zoho_contact_id`, `name`, `contact_person`, `email`, `website`, `phone`, `whatsapp_number`, `gst_type`, `gstin`, `pan_number`, `opening_balance`, `balance_type`, `credit_limit`, `credit_period_days`, `status`, `agent_id`, `created_at`, `updated_at`, `notes`, `zoho_sync_at`) VALUES
(219, '698964000001713060', 'SHRIE G', NULL, '', '', '0000000000', NULL, 'Regular', '33AFAPG3709H1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(220, '698964000000178015', 'Shringaram Woven For Women', NULL, '', '', '0000000000', NULL, 'Regular', '33ACQFS4415F1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(221, '698964000001512029', 'SHUBHAM TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '36APQPG7304C1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(222, '698964000001927142', 'SINDHU RAAGHAVI LLP', NULL, '', '', '0000000000', NULL, 'Regular', '33ADBFS5004Q1Z2', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(223, '698964000001392001', 'SKP Kanchi Cotton Sarees', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(224, '698964000002056005', 'South Indian Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '36AFHPP4219E1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(225, '698964000000254001', 'Souzas', NULL, '', '', '0000000000', NULL, 'Regular', '29ADEFS4619N1ZG', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(226, '698964000000359003', 'Sree Brindaavan Silks & Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '37GSPPK6070R1ZL', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(227, '698964000000496001', 'Sree Chandana Brothers', NULL, '', '', '0000000000', NULL, 'Regular', '36ABYFS3328L2ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(228, '698964000000857001', 'SREE GHAYATHRI TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33AGDPJ0363Q1ZB', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(229, '698964000000857035', 'SREE GHAYATHRI TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33BRLPJ5682G1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(230, '698964000006197005', 'SREE GOVINDHARAJAN SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33APGPG0188L2ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(231, '698964000000262001', 'Sree Kalanikethan Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '29AGXPR7950B1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(232, '698964000001479003', 'SREE LAXMI CLOTH & READYMADE DRESSES', NULL, '', '', '0000000000', NULL, 'Regular', '36AAMPA7101L1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(233, '698964000000078001', 'Sree Maruthi Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33AHLPK6133G1ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(234, '698964000000310001', 'Sree Matha Textiles', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(235, '698964000000957001', 'SREE SUNDARAM SILK SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '33AAEPB2914J1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(236, '698964000000229019', 'SREE VELAN SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '33AWHPV5024E1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(237, '698964000001687218', 'SRI BHAIRAVA SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '29AERPL8829B1Z0', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(238, '698964000000208001', 'Sri Bhavani Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '37ADMFS6932N1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(239, '698964000002307002', 'SRI CENTRAL SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AJDPM3176F1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(240, '698964000002389189', 'Sri Chakra Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29BPRPS3648F1Z6', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(241, '698964000000667003', 'Sri Chamundeshwari Silks & Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '29ACGPM5647M1ZX', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(242, '698964000000826209', 'SRI DHANALAKSHMI CHENETA VASTRALAYAM', NULL, '', '', '0000000000', NULL, 'Regular', '37AUOPP8991K1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(243, '698964000000923001', 'SRI DURGA SAREE CENTER', NULL, '', '', '0000000000', NULL, 'Regular', '36ABLPB6266F2ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(244, '698964000004805112', 'SRI HARI SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AFAPV1390P1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(245, '698964000002020009', 'Sri KanakaMahalakshmi Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '37BHUPP5537R1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(246, '698964000003191464', 'Sri Krishna Handlooms ', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(247, '698964000000121001', 'Sri Kumaran', NULL, '', '', '0000000000', NULL, 'Regular', '33ABDFS0186K1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(248, '698964000001947109', 'Sri kumarappa Silks', NULL, '', 'https://srikumarappasilks.com/', '0000000000', NULL, 'Regular', '33ADJFS7797L1Z5', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(249, '698964000005921001', 'SRI MURUGAN DYEING', NULL, '', '', '0000000000', NULL, 'Regular', '33AAMFS5810Q1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(250, '698964000001010015', 'SRI PADMA CLOTH STORES', NULL, '', '', '0000000000', NULL, 'Regular', '36ABTPG8278J2ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(251, '698964000002916003', 'SRI PALANIAPPA TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33ALHPI1487M1ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(252, '698964000000307005', 'SRI POORNANANDA BANIAN CENTRE', NULL, '', '', '0000000000', NULL, 'Regular', '37AGDPV6441M1ZS', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(253, '698964000000409001', 'Sri R.R.Silks & Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33AYBPS7485G1Z3', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(254, '698964000000796001', 'SRI RAJALAKSHMI SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33BADPR0336N1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(255, '698964000000422001', 'SRI ROHINI TEX', NULL, '', '', '0000000000', NULL, 'Regular', '33ASXPG4635Q1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(256, '698964000006243003', 'SRI SAMUTHRA SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '33AKDPR0731N1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(257, '698964000004537109', 'SRI SUBRAHMANYESWARA TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '37AAFPU4446H1ZD', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(258, '698964000001590314', 'SRI SUMANGALI CREATIONS', NULL, '', '', '0000000000', NULL, 'Regular', '36AOKPT2577F2ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(259, '698964000002699093', 'Sri Sumangali Creations', NULL, '', '', '0000000000', NULL, 'Regular', '36AOKPT2577F2ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(260, '698964000001737001', 'SRI THILAGAVATHI STORES', NULL, '', '', '0000000000', NULL, 'Regular', '34AXYPT0114F1ZD', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(261, '698964000000886001', 'SRI VAISHNAVI RETAIL (INDIA)LLP', NULL, '', '', '0000000000', NULL, 'Regular', '36ADOFS9410R1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(262, '698964000006157045', 'SRI VAISNAVI RETAIL INDIA LLP', NULL, '', '', '0000000000', NULL, 'Regular', '36ADOFS9410R1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(263, '698964000000188001', 'Sri Valli Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33AMQPR3888RIZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(264, '698964000000356062', 'Sri Varadharaja Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33ADCPV1709N1ZC', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(265, '698964000001442086', 'SRI VENKAT SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AEDFS6017G1Z9', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(266, '698964000000919001', 'SRI VENKATESWARA CLOTH EMPORIUM', NULL, '', '', '0000000000', NULL, 'Regular', '37ABTPC4722F1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(267, '698964000004885521', 'SRI VIGNESH TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33AAKFS8445D1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(268, '698964000000934001', 'SRI VISALAKSH CLOTH STORES', NULL, '', '', '0000000000', NULL, 'Regular', '37AYFPP4467P1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(269, '698964000000880001', 'SRI VYSHNAVI HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '37ACFPV3647K1Z0', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(270, '698964000000418003', 'SRIDHAR FASHIONS', NULL, '', '', '0000000000', NULL, 'Regular', '29ADDFS0358K1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(271, '698964000003622003', 'SRINIVASA SILK & SAREE HOUSE', NULL, '', '', '0000000000', NULL, 'Regular', '37ABIPV6016G1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:45', NULL, '2026-02-01 12:37:45'),
(272, '698964000003444239', 'Srinivasa Silk and Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '29APZPC9177Q1ZG', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:45', NULL, '2026-02-01 12:37:45'),
(273, '698964000003493003', 'SRINIVASA SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AAYFK6561G1ZV', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:45', NULL, '2026-02-01 12:37:45'),
(274, '698964000000982001', 'SRINIVASAN TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33BMGPS0163M2ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:45', NULL, '2026-02-01 12:37:45'),
(275, '698964000000982027', 'SRINIVASAN TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33BMGPS0163M2ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:45', NULL, '2026-02-01 12:37:45'),
(276, '698964000000982044', 'SRINIVASAN TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33BMGPS0163M2ZU', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:37:45', NULL, '2026-02-01 12:37:45'),
(277, '698964000004153033', 'S R K SILKS & SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '29ADWPG6240R1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(278, '698964000000798001', 'SUDAKSHINA DAS', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(279, '698964000001006001', 'SUDHAS SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '32ACFFS3330L1Z8', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(280, '698964000000935111', 'Sumangali Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '36ALTPG2956K1ZI', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(281, '698964000000390003', 'SUNDARA  DEVARAJA', NULL, '', '', '0000000000', NULL, 'Regular', '29AMVPD3327Q1Z9', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(282, '698964000001476149', 'Surya Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33AGEPS5608Q1ZV', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(283, '698964000001339001', 'SWAYAMVARA SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '32ACKPR7946N1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(284, '698964000003155001', 'T.K.D. Sons', NULL, '', '', '0000000000', NULL, 'Regular', '33ACYPS0626E1ZG', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(285, '698964000003101181', 'Tara Saree Collections ', NULL, '', '', '0000000000', NULL, 'Regular', '33AAIFA8010E1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(286, '698964000000877001', 'Tatikonda Nageswara Rao Son', NULL, '', '', '0000000000', NULL, 'Regular', '37ABBPT6081C1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(287, '698964000002698051', 'Thammana Naveen Venkata Maruthin', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(288, '698964000000184001', 'The Mogan Silks & Readymades', NULL, '', '', '0000000000', NULL, 'Regular', '37BBAPA1713N1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(289, '698964000000392019', 'TRISHIKA HANDLOOM PRIVATE LTD', NULL, '', '', '0000000000', NULL, 'Regular', '19AACCT7582M1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(290, '698964000003441019', 'TULSI WEAVES', NULL, '', '', '0000000000', NULL, 'Regular', '33AASFT4817C1Z5', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(291, '698964000000690001', 'UMA CREATION', NULL, '', '', '0000000000', NULL, 'Regular', '19ANBPM5739Q1Z7', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(292, '698964000001430001', 'Uni Silks Private Limited (Swayamvara Silks)', NULL, '', '', '0000000000', NULL, 'Regular', '32AACCU7483N1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(293, '698964000002133013', 'UNI SILKS PRIVATE LIMITED (SWAYAMWARA)', NULL, '', '', '0000000000', NULL, 'Regular', '32AACCU7483N1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(294, '698964000001099003', 'V HASHTAG CLOTHING', NULL, '', '', '0000000000', NULL, 'Regular', '33AASFV9358A1ZT', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(295, '698964000002994103', 'VAISHNAVI TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '29AAOFV9561B1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(296, '698964000004582079', 'VANAJA SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '33AHPPV0921K1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(297, '698964000001833001', 'ENES TEXTILE MILLS', NULL, '', '', '0000000000', NULL, 'Regular', '33AAIFA8010E1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(298, '698964000004961011', 'VASUDHA SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '37EXQPP0725J1ZX', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(299, '698964000001461143', 'Vasundhara Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36AATPT4443K1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(300, '698964000001590003', 'Vasundhara Shopping Mall - Suryapet', NULL, '', '', '0000000000', NULL, 'Regular', '36AAUFV3931H1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(301, '698964000003071001', 'Vedant Textiles', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(302, '698964000000302001', 'Veerabhadra Shopping Mall', NULL, '', '', '0000000000', NULL, 'Regular', '36AAOFM0188P1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(303, '698964000000506001', 'Velmurugan Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33AAUPM4742K1Z8', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(304, '698964000006077043', 'VENKATAGIRI HANDLOOMS', NULL, '', '', '0000000000', NULL, 'Regular', '37FBEPM6052C1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(305, '698964000000621148', 'Vijaya Lakshmi', NULL, '', '', '0000000000', NULL, 'Regular', '36AJNPA1562F1ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(306, '698964000003986070', 'VIJAYALAKSHMI TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '29AKLPP4416G1ZW', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(307, '698964000000097001', 'Vijayalekshmi Stores', NULL, '', '', '0000000000', NULL, 'Regular', '32AAFFV5196D1Z6', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(308, '698964000000238001', 'Vijayalekshmi Stores', NULL, '', '', '0000000000', NULL, 'Regular', '33AAFFV5196D1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(309, '698964000001769001', 'VISHNU KANCHI CREATIONS', NULL, '', '', '0000000000', NULL, 'Regular', '36AFVPT1054P3ZH', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(310, '698964000003437003', 'VISHWAMBARA SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '33AAWFV4202B1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(311, '698964000000314001', 'VISMAYA SILKS (FASHION BUG ADIMALY BRANCH,)', NULL, '', '', '0000000000', NULL, 'Regular', '32BKRPR9844P1ZZ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(312, '698964000003937001', 'Vivek Hosad Madiwal', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(313, '698964000003449119', 'VL GROUP', NULL, '', '', '0000000000', NULL, 'Regular', '33AAVFV5771G1ZL', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(314, '698964000000614001', 'Warp Weft and Knot', NULL, '', '', '0000000000', NULL, 'Regular', '33AERPJ9491F1Z1', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(315, '698964000001921319', 'Weavers Emporium', NULL, '', '', '0000000000', NULL, 'Regular', '36AACFW3213H1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(316, '698964000000147001', 'Weavers Sarees House', NULL, '', '', '0000000000', NULL, 'Regular', '33ANJPS9869F2ZA', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(317, '698964000000124001', 'Wedland Silks and Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '32AABFW2475J1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(318, '698964000001000256', 'WEDLAND WEDDINGS', NULL, '', '', '0000000000', NULL, 'Regular', '32AADFW3261G1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(319, '698964000002016253', 'Wedland Weddings - Haripad', NULL, '', '', '0000000000', NULL, 'Regular', '32AADFW3224F1ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(320, '698964000001000101', 'WEDLAND WEDDINGS', NULL, '', '', '0000000000', NULL, 'Regular', '32AABFW2475J1Z4', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(321, '698964000001537001', 'Zoya Charitable Trust', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:48:37', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(322, '698964000000542001', 'Alankar Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '29AQUPK8357Q1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(323, '698964000002878095', 'BARADAPURE SILK & SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '29BOTPN7089G1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(324, '698964000000420001', 'C M SAREE DEPOT', NULL, '', '', '0000000000', NULL, 'Regular', '29AAEFC3358D1ZL', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(325, '698964000000294001', 'Chandana Brothers Textiles & Jewellers Pvt Ltd', NULL, '', '', '0000000000', NULL, 'Regular', '36AACCC3901F1Z5', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(326, '698964000000522001', 'Devika Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '29AELPJ8389J1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(327, '698964000000084476', 'Diwan Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '36ACFPC6654F1ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(328, '698964000002083035', 'ELEMECS WEDDING CASTLE', NULL, '', 'www.elemecs.in', '0000000000', NULL, 'Regular', '32AKQPG9226J1ZW', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37'),
(329, '698964000002170119', 'K.K.E. AMALGAMATIONS', NULL, '', '', '0000000000', NULL, 'Regular', '33AACPM7188H1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(330, '698964000002609005', 'KANCHI CO SRIDHAR SILKS', NULL, '', '', '0000000000', NULL, 'Regular', '29AAIFK3858L1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:37:18', NULL, '2026-02-01 12:37:18'),
(331, '698964000001091001', 'N.PARVATHI TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33AABFN3571G1ZJ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(332, '698964000002805031', 'New Sudarshan Silks', NULL, '', '', '0000000000', NULL, 'Regular', '29AAOFN6485C1ZS', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:31', '2026-02-01 12:37:20', NULL, '2026-02-01 12:37:20'),
(333, '698964000000599001', 'ROHINI MATCHING CENTER', NULL, '', '', '0000000000', NULL, 'Regular', '36AAEFR3711M1Z3', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(334, '698964000002987001', 'Saikala Paithani', NULL, '', '', '0000000000', NULL, 'Regular', '27BSGPB7929E1ZQ', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(335, '698964000003208155', 'Santhakumari Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33AASPS7423B1ZN', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(336, '698964000005084039', 'SARASWATHI SILK', NULL, '', '', '0000000000', NULL, 'Regular', '33AAAHN3394L1Z0', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(337, '698964000002182003', 'Sarathi Jauvli Kadai', NULL, '', '', '0000000000', NULL, 'Regular', '33ALTPG8064E1ZY', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(338, '698964000005288005', 'Siva Shankar Cloth Stores', NULL, '', '', '0000000000', NULL, 'Regular', '29AAXPR8223K1ZP', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(339, '698964000000309001', 'SOLANKI CLOTH STORES', NULL, '', '', '0000000000', NULL, 'Regular', '27AAOFS8299R1ZO', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:42', NULL, '2026-02-01 12:37:42'),
(340, '698964000002126066', 'Sri Karpaag Vinayagar Silks', NULL, '', '', '0000000000', NULL, 'Regular', '33AEVPJ7303Q1ZV', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(341, '698964000003032031', 'Sri Siva Parvathi Handloom Sarees', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(342, '698964000000207001', 'Sri Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33AAMPS5620E2ZR', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(343, '698964000005288037', 'SRI VENKATALAKSHMI DISTRIBUTORS', NULL, '', '', '0000000000', NULL, 'Regular', '37ASEPB7233N1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(344, '698964000003030003', 'SRI VENKATESWARA TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33AAAFS9925Q1ZK', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:37:44', NULL, '2026-02-01 12:37:44'),
(345, '698964000002693005', 'Sunitta Textiles Mills (P) Ltd', NULL, '', '', '0000000000', NULL, 'Regular', '36AACCS8363B1ZF', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(346, '698964000002901001', 'T.K. Gurusamy Nadar & Sons Sri Koodalingam Pattu Centre (P) Ltd', NULL, '', '', '0000000000', NULL, 'Regular', '33AACCT4845P1ZW', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(347, '698964000001865118', 'THAYEES WEDDING CENTRE', NULL, '', '', '0000000000', NULL, 'Regular', '32AAOFT4509K1ZX', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(348, '698964000000084604', 'Tulsi Silks', NULL, '', 'https://tulsisilks.co.in', '0000000000', NULL, 'Regular', '33AABFT9421B1ZM', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 11:54:33', '2026-02-01 12:38:01', NULL, '2026-02-01 12:38:01'),
(349, '698964000001943086', 'Chandana Brothers Silks & Jewellers Pvt Ltd', NULL, '', '', '0000000000', NULL, 'Regular', '36AAECC7196G1ZE', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 12:02:14', '2026-02-01 12:34:34', NULL, '2026-02-01 12:34:34'),
(350, '698964000002406293', 'CMR Textiles & Jewellers Private Ltd', NULL, '', 'cmrvizag.com', '0000000000', NULL, 'Regular', '37AAFCC6436L3Z9', NULL, 0.00, 'Dr', 0.00, 0, 'active', NULL, '2026-02-01 12:02:14', '2026-02-01 12:34:37', NULL, '2026-02-01 12:34:37');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `guardian_first_name` varchar(100) DEFAULT NULL,
  `guardian_last_name` varchar(100) DEFAULT NULL,
  `guardian_type` enum('Father','Mother','Husband','Other') NOT NULL DEFAULT 'Father',
  `mobile_number` varchar(15) NOT NULL,
  `guardian_mobile_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `inactive_date` date DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `state_id` int(11) UNSIGNED DEFAULT NULL,
  `country_id` int(11) UNSIGNED DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `address_proof_type` varchar(50) DEFAULT NULL,
  `address_proof_number` varchar(100) DEFAULT NULL,
  `address_proof_front_image` varchar(255) DEFAULT NULL,
  `address_proof_back_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `employment_type` enum('Permanent','Temporary') DEFAULT 'Permanent',
  `basic_salary` decimal(10,2) DEFAULT 0.00,
  `daily_working_hours` decimal(4,2) DEFAULT 8.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `guardian_first_name`, `guardian_last_name`, `guardian_type`, `mobile_number`, `guardian_mobile_number`, `email`, `photo`, `joining_date`, `inactive_date`, `address_line_1`, `address_line_2`, `state_id`, `country_id`, `village`, `city`, `state`, `country`, `pincode`, `address_proof_type`, `address_proof_number`, `address_proof_front_image`, `address_proof_back_image`, `status`, `employment_type`, `basic_salary`, `daily_working_hours`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Rasi', 'Textiles', '', '', 'Father', '09626077333', '', 'sgrgowthamraj63@gmail.com', 'uploads/employees/photos/1768974224_d75c5d296bfc17a18752.jpg', '2021-01-21', NULL, '', '', NULL, NULL, '', '', '', NULL, '', 'Aadhar', '', 'uploads/employees/proofs/1768974224_9ec126b1b6e2bece1efe.png', 'uploads/employees/proofs/1768974224_a00841b5766cacf16e6d.png', 'active', 'Permanent', 7000.00, 8.00, '2026-01-21 05:43:44', '2026-01-21 08:05:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) UNSIGNED NOT NULL,
  `expense_date` date NOT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `payment_mode` varchar(50) NOT NULL DEFAULT 'Cash',
  `bank_account_id` int(11) UNSIGNED DEFAULT NULL,
  `bank_transaction_id` int(11) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'STATIONARIES', 'Stationary Supplies', 'active', '2026-01-21 11:24:28', '2026-01-21 11:25:24'),
(2, 'EB BILL', 'Electricity Bill Payment', 'active', '2026-01-21 11:24:45', '2026-01-21 11:24:45'),
(3, 'RENT', 'Rent for shops', 'active', '2026-01-21 11:24:56', '2026-01-21 11:24:56'),
(4, 'FUEL', 'Fuel for Vehicles', 'active', '2026-01-21 11:25:14', '2026-01-21 11:25:14'),
(5, 'PACKAGING', 'PACKING MATERIALS', 'active', '2026-01-21 11:25:58', '2026-01-21 11:25:58'),
(6, 'RECEIVED PARCEL', 'RECEIVED PARCEL Payments', 'active', '2026-01-21 11:26:55', '2026-01-21 11:26:55'),
(7, 'SENDING PARCEL', 'SENDING PARCEL Payments', 'active', '2026-01-21 11:27:12', '2026-01-21 11:27:12'),
(8, 'VEHICLE MAINTENANCE', 'VEHICLE MAINTENANCE', 'active', '2026-01-21 11:29:17', '2026-01-21 11:29:17'),
(9, 'FOOD EXPENSE', 'FOOD EXPENSE for Peoples Going out for Oraganization', 'active', '2026-01-21 11:30:00', '2026-01-21 11:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) UNSIGNED DEFAULT NULL,
  `agent_commission_percent` decimal(5,2) DEFAULT 0.00,
  `agent_commission_amount` decimal(15,2) DEFAULT 0.00,
  `agent_commission_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `agent_commission_paid_at` datetime DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `zoho_invoice_id` varchar(100) DEFAULT NULL,
  `zoho_sync_status` enum('Pending','Synced','Failed') NOT NULL DEFAULT 'Pending',
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `transport_name` varchar(150) DEFAULT NULL,
  `waybill_number` varchar(50) DEFAULT NULL,
  `packages_count` int(11) DEFAULT NULL,
  `waybill_date` date DEFAULT NULL,
  `ewaybill_number` varchar(50) DEFAULT NULL,
  `waybill_image` varchar(255) DEFAULT NULL,
  `transport_amount` decimal(10,2) DEFAULT 0.00,
  `transport_pay_type` enum('Paid','To Pay') DEFAULT 'To Pay',
  `waybill_shipping_charge` decimal(10,2) DEFAULT 0.00,
  `doc_courier_name` varchar(255) DEFAULT NULL,
  `doc_tracking_number` varchar(100) DEFAULT NULL,
  `doc_dispatched_date` date DEFAULT NULL,
  `doc_status` enum('Pending','Dispatched','Delivered','Returned') DEFAULT 'Pending',
  `doc_received_date` date DEFAULT NULL,
  `delivery_status` enum('Pending','In Transit','Delivered','Cancelled') DEFAULT 'Pending',
  `delivered_date` date DEFAULT NULL,
  `status` enum('Draft','Open','Paid','Partially Paid','Overdue','Void') NOT NULL DEFAULT 'Draft',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('Fixed','Percentage') NOT NULL DEFAULT 'Fixed',
  `shipping_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `roundoff_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_inter_state` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `customer_id`, `agent_id`, `agent_commission_percent`, `agent_commission_amount`, `agent_commission_status`, `agent_commission_paid_at`, `invoice_number`, `zoho_invoice_id`, `zoho_sync_status`, `invoice_date`, `due_date`, `reference_number`, `po_date`, `transport_name`, `waybill_number`, `packages_count`, `waybill_date`, `ewaybill_number`, `waybill_image`, `transport_amount`, `transport_pay_type`, `waybill_shipping_charge`, `doc_courier_name`, `doc_tracking_number`, `doc_dispatched_date`, `doc_status`, `doc_received_date`, `delivery_status`, `delivered_date`, `status`, `subtotal`, `discount_amount`, `discount_type`, `shipping_charge`, `cgst_amount`, `sgst_amount`, `igst_amount`, `tax_amount`, `roundoff_amount`, `total_amount`, `paid_amount`, `balance`, `notes`, `terms`, `created_by`, `updated_by`, `zoho_sync_at`, `created_at`, `updated_at`, `is_inter_state`) VALUES
(9, 8, NULL, 0.00, 0.00, 'Unpaid', NULL, 'INV-202602-0001', NULL, 'Failed', '2026-02-23', '2026-02-23', '', NULL, 'AKR', NULL, NULL, NULL, '78946511321', NULL, 0.00, 'To Pay', 0.00, NULL, NULL, NULL, 'Pending', NULL, 'Pending', NULL, 'Paid', 37500.00, 0.00, 'Fixed', 0.00, 0.00, 0.00, 1875.00, 1875.00, 0.00, 39375.00, 39375.00, 0.00, '', '1. Goods once sold cannot be taken back or exchanged.\r\n2. Please verify the items at the time of delivery.', 1, NULL, NULL, '2026-02-23 15:20:03', '2026-02-23 15:26:01', 1),
(10, 27, NULL, 0.00, 0.00, 'Unpaid', NULL, 'INV-202602-0002', NULL, 'Failed', '2026-02-23', '2026-02-23', '', NULL, 'AKR', NULL, 20, NULL, '', NULL, 0.00, 'To Pay', 0.00, NULL, NULL, NULL, 'Pending', NULL, 'Pending', NULL, 'Draft', 53310.00, 0.00, 'Fixed', 150.00, 2360.40, 2360.40, 0.00, 4720.80, 0.20, 58181.00, 0.00, 58181.00, '', '1. Goods once sold cannot be taken back or exchanged.\r\n2. Please verify the items at the time of delivery.', 1, NULL, NULL, '2026-02-23 15:56:42', '2026-02-23 15:56:43', 0);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `sgst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `igst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `description`, `hsn_code`, `quantity`, `rate`, `tax_percentage`, `cgst_rate`, `sgst_rate`, `igst_rate`, `amount`, `created_at`, `updated_at`) VALUES
(14, 9, 1, 'KASAVU KERALA SAREE GOLD', '5208', 150.00, 250.00, 5.00, 0.00, 0.00, 5.00, 37500.00, '2026-02-23 15:20:03', '2026-02-23 15:20:03'),
(15, 10, 1, 'KASAVU KERALA SAREE GOLD', '5208', 150.00, 250.00, 5.00, 2.50, 2.50, 0.00, 37500.00, '2026-02-23 15:56:42', '2026-02-23 15:56:42'),
(16, 10, 2, 'Plain Neli Border', '5208', 17.00, 930.00, 18.00, 9.00, 9.00, 0.00, 15810.00, '2026-02-23 15:56:42', '2026-02-23 15:56:42');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `bank_account_id` int(11) UNSIGNED DEFAULT NULL,
  `bank_transaction_id` int(11) UNSIGNED DEFAULT NULL,
  `payment_number` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `mahimai_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `postal_charges` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `zoho_payment_id` varchar(100) DEFAULT NULL,
  `zoho_sync_status` enum('Pending','Synced','Failed') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_payments`
--

INSERT INTO `invoice_payments` (`id`, `invoice_id`, `customer_id`, `bank_account_id`, `bank_transaction_id`, `payment_number`, `payment_date`, `payment_mode`, `amount`, `discount_amount`, `mahimai_amount`, `postal_charges`, `reference_number`, `zoho_payment_id`, `zoho_sync_status`, `notes`, `created_by`, `updated_by`, `zoho_sync_at`, `created_at`, `updated_at`) VALUES
(6, 9, 8, NULL, NULL, 'RECP-202602-0001', '2026-02-23', 'Cash', 39375.00, 0.00, 0.00, 0.00, '', NULL, 'Pending', '', 1, 1, NULL, '2026-02-23 15:26:01', '2026-02-23 15:26:01');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_status_history`
--

CREATE TABLE `invoice_status_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_id` int(11) UNSIGNED NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_entries`
--

CREATE TABLE `ledger_entries` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ledger_entries`
--

INSERT INTO `ledger_entries` (`id`, `account_id`, `entry_date`, `debit`, `credit`, `description`, `reference_type`, `reference_id`, `created_at`) VALUES
(1, 4, '2026-01-28', 18112135.00, 0.00, 'Payment: RECP-202601-0001 (Ref: INV-202601-0003)', 'invoice_payment', 5, '2026-01-28 07:34:58'),
(2, 8, '2026-01-28', 25.00, 0.00, 'Discount allowed on RECP-202601-0001', 'invoice_payment', 5, '2026-01-28 07:34:58'),
(3, 1, '2026-01-28', 0.00, 18112500.00, 'Gross settlement for RECP-202601-0001', 'invoice_payment', 5, '2026-01-28 07:34:58'),
(4, 14, '2026-01-28', 0.00, 20.00, 'Mahimai collected in RECP-202601-0001', 'invoice_payment', 5, '2026-01-28 07:34:58'),
(5, 15, '2026-01-28', 0.00, 320.00, 'Postal charges collected in RECP-202601-0001', 'invoice_payment', 5, '2026-01-28 07:34:58'),
(6, 1, '2026-02-01', 262.50, 0.00, 'Sale Invoice: INV-202602-0001', 'invoice', 5, '2026-02-01 08:28:52'),
(7, 5, '2026-02-01', 0.00, 250.00, 'Sale Invoice: INV-202602-0001', 'invoice', 5, '2026-02-01 08:28:52'),
(8, 12, '2026-02-01', 0.00, 12.50, 'GST on Sale Invoice: INV-202602-0001', 'invoice', 5, '2026-02-01 08:28:52'),
(9, 1, '2026-02-01', 0.00, 0.00, 'Sale Invoice: INV-202602-0002', 'invoice', 6, '2026-02-01 09:10:36'),
(10, 5, '2026-02-01', 0.00, 0.00, 'Sale Invoice: INV-202602-0002', 'invoice', 6, '2026-02-01 09:10:36'),
(13, 2, '2026-02-01', 1853.00, 0.00, 'Gross settlement for PAY-202602-0001', 'vendor_payment', 3, '2026-02-01 09:26:05'),
(14, 4, '2026-02-01', 0.00, 1853.00, 'Vendor Payment: PAY-202602-0001 (Ref: BILL-202601-0001)', 'vendor_payment', 3, '2026-02-01 09:26:05'),
(15, 1, '2026-02-01', 262.50, 0.00, 'Sale Invoice: INV-202602-0003', 'invoice', 7, '2026-02-01 09:32:00'),
(16, 5, '2026-02-01', 0.00, 250.00, 'Sale Invoice: INV-202602-0003', 'invoice', 7, '2026-02-01 09:32:00'),
(17, 12, '2026-02-01', 0.00, 12.50, 'GST on Sale Invoice: INV-202602-0003', 'invoice', 7, '2026-02-01 09:32:00'),
(18, 1, '2026-02-01', 536628.60, 0.00, 'Sale Invoice: INV-202602-0004', 'invoice', 8, '2026-02-01 09:51:12'),
(19, 5, '2026-02-01', 0.00, 454770.00, 'Sale Invoice: INV-202602-0004', 'invoice', 8, '2026-02-01 09:51:12'),
(20, 12, '2026-02-01', 0.00, 81858.60, 'GST on Sale Invoice: INV-202602-0004', 'invoice', 8, '2026-02-01 09:51:12'),
(21, 1, '2026-02-23', 39375.00, 0.00, 'Sale Invoice: INV-202602-0001', 'invoice', 9, '2026-02-23 15:20:03'),
(22, 5, '2026-02-23', 0.00, 37500.00, 'Sale Invoice: INV-202602-0001', 'invoice', 9, '2026-02-23 15:20:03'),
(23, 12, '2026-02-23', 0.00, 1875.00, 'GST on Sale Invoice: INV-202602-0001', 'invoice', 9, '2026-02-23 15:20:03'),
(24, 3, '2026-02-23', 39375.00, 0.00, 'Payment: RECP-202602-0001 (Ref: INV-202602-0001)', 'invoice_payment', 6, '2026-02-23 15:26:01'),
(25, 1, '2026-02-23', 0.00, 39375.00, 'Gross settlement for RECP-202602-0001', 'invoice_payment', 6, '2026-02-23 15:26:01'),
(26, 1, '2026-02-23', 58181.00, 0.00, 'Sale Invoice: INV-202602-0002', 'invoice', 10, '2026-02-23 15:56:42'),
(27, 5, '2026-02-23', 0.00, 53310.00, 'Sale Invoice: INV-202602-0002', 'invoice', 10, '2026-02-23 15:56:42'),
(28, 12, '2026-02-23', 0.00, 4720.80, 'GST on Sale Invoice: INV-202602-0002', 'invoice', 10, '2026-02-23 15:56:42'),
(29, 5, '2026-02-23', 0.00, 150.00, 'Shipping handling for Sale Invoice: INV-202602-0002', 'invoice', 10, '2026-02-23 15:56:42');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) UNSIGNED NOT NULL,
  `employee_id` int(11) UNSIGNED NOT NULL,
  `loan_amount` decimal(10,2) NOT NULL,
  `loan_date` date NOT NULL,
  `processed_date` date DEFAULT NULL,
  `credited_date` date DEFAULT NULL,
  `credit_status` enum('Pending','Credited') DEFAULT 'Pending',
  `monthly_deduction` decimal(10,2) NOT NULL,
  `remaining_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Approved','active','Completed','paid','cancelled') DEFAULT 'Pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `employee_id`, `loan_amount`, `loan_date`, `processed_date`, `credited_date`, `credit_status`, `monthly_deduction`, `remaining_amount`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 20000.00, '2026-01-21', '2026-01-21', '2026-01-21', 'Credited', 2000.00, 20000.00, 'Approved', '2026-01-21 06:08:11', '2026-01-21 06:50:29');

-- --------------------------------------------------------

--
-- Table structure for table `loan_payments`
--

CREATE TABLE `loan_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `loan_id` int(11) UNSIGNED NOT NULL,
  `salary_id` int(11) UNSIGNED DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `remaining_after_payment` decimal(10,2) NOT NULL,
  `payment_month` varchar(7) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2024-01-21-100001', 'App\\Database\\Migrations\\CreateRolesTable', 'default', 'App', 1768971204, 1),
(2, '2024-01-21-100002', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1768971204, 1),
(3, '2024-01-21-100003', 'App\\Database\\Migrations\\CreateModulesTable', 'default', 'App', 1768971204, 1),
(4, '2024-01-21-100004', 'App\\Database\\Migrations\\CreatePermissionsTable', 'default', 'App', 1768971204, 1),
(5, '2024-01-21-100005', 'App\\Database\\Migrations\\CreateEmployeesTable', 'default', 'App', 1768971204, 1),
(6, '2024-01-21-100006', 'App\\Database\\Migrations\\CreateAttendanceTable', 'default', 'App', 1768971204, 1),
(7, '2024-01-21-100007', 'App\\Database\\Migrations\\CreateLoansTable', 'default', 'App', 1768971204, 1),
(8, '2024-01-21-100008', 'App\\Database\\Migrations\\CreateSalariesTable', 'default', 'App', 1768971204, 1),
(9, '2024-01-21-100009', 'App\\Database\\Migrations\\CreateRolePermissionsTable', 'default', 'App', 1768971327, 2),
(10, '2024-01-21-100010', 'App\\Database\\Migrations\\AddSalaryToEmployees', 'default', 'App', 1768971588, 3),
(11, '2024-01-21-100011', 'App\\Database\\Migrations\\CreateSalaryIncrementsTable', 'default', 'App', 1768973858, 4),
(12, '2024-01-21-100012', 'App\\Database\\Migrations\\AddMultiplierToAttendance', 'default', 'App', 1768973858, 4),
(13, '2024-01-21-100013', 'App\\Database\\Migrations\\AddLoanDatesAndFixStatus', 'default', 'App', 1768974930, 5),
(14, '2024-01-21-100014', 'App\\Database\\Migrations\\CreateLoanPaymentsTable', 'default', 'App', 1768974930, 5),
(15, '2024-01-21-100015', 'App\\Database\\Migrations\\AddPaidStatusAndExemptionsToSalaries', 'default', 'App', 1768975323, 6),
(16, '2024-01-21-100016', 'App\\Database\\Migrations\\AddCreditStatusToLoans', 'default', 'App', 1768976205, 7),
(17, '2024-01-21-100017', 'App\\Database\\Migrations\\UpdateAttendanceStatusEnum', 'default', 'App', 1768977521, 8),
(18, '2024-01-21-100018', 'App\\Database\\Migrations\\AddWorkingHoursToEmployeesAndAttendance', 'default', 'App', 1768977820, 9),
(19, '2024-01-21-100019', 'App\\Database\\Migrations\\AddSurplusHoursToAttendance', 'default', 'App', 1768978579, 10),
(20, '2024-01-21-100020', 'App\\Database\\Migrations\\CreateSettingsTable', 'default', 'App', 1768978927, 11),
(21, '2024-01-21-100021', 'App\\Database\\Migrations\\AddSettingsPermission', 'default', 'App', 1768981743, 12),
(22, '2024-01-21-100022', 'App\\Database\\Migrations\\FinalizeSuperAdminPermissions', 'default', 'App', 1768981743, 12),
(23, '2024-01-21-100023', 'App\\Database\\Migrations\\AddEmploymentTypeToEmployees', 'default', 'App', 1768981785, 13),
(24, '2024-01-21-100024', 'App\\Database\\Migrations\\AddEmployeeIdToUsers', 'default', 'App', 1768981785, 13),
(29, '2024-01-21-110001', 'App\\Database\\Migrations\\CreateCountriesTable', 'default', 'App', 1768985217, 14),
(30, '2024-01-21-110002', 'App\\Database\\Migrations\\CreateStatesTable', 'default', 'App', 1768985217, 14),
(31, '2024-01-21-110003', 'App\\Database\\Migrations\\CreateAgentsTable', 'default', 'App', 1768985217, 14),
(32, '2024-01-21-110004', 'App\\Database\\Migrations\\CreateTransportTable', 'default', 'App', 1768985217, 14),
(33, '2024-01-21-110005', 'App\\Database\\Migrations\\AddLocationToEmployees', 'default', 'App', 1768985217, 14),
(34, '2024-01-21-110006', 'App\\Database\\Migrations\\RegisterNewModules', 'default', 'App', 1768993579, 15),
(35, '2024-01-21-110007', 'App\\Database\\Migrations\\ForceAddLocationToEmployees', 'default', 'App', 1768993579, 15),
(36, '2024-01-21-120001', 'App\\Database\\Migrations\\CreateBankAccountsTable', 'default', 'App', 1768993580, 15),
(37, '2024-01-21-120002', 'App\\Database\\Migrations\\CreateExpensesTable', 'default', 'App', 1768993580, 15),
(38, '2024-01-21-120003', 'App\\Database\\Migrations\\RegisterFinanceModules', 'default', 'App', 1768993580, 15),
(39, '2024-01-21-130000', 'App\\Database\\Migrations\\RestoreAndFixPermissions', 'default', 'App', 1768993580, 15),
(40, '2024-01-21-140000', 'App\\Database\\Migrations\\FixPermissionsUrgent', 'default', 'App', 1768993580, 15),
(41, '2024-01-21-150000', 'App\\Database\\Migrations\\AddModuleAndPermissionManagement', 'default', 'App', 1768993883, 16),
(42, '2024-01-21-160000', 'App\\Database\\Migrations\\CreateBankTransactionsTable', 'default', 'App', 1768994181, 17),
(43, '2024-01-21-160001', 'App\\Database\\Migrations\\AddBankTransactionPermissions', 'default', 'App', 1768994493, 18),
(44, '2024-01-21-170000', 'App\\Database\\Migrations\\CreateExpenseCategoriesAndAdjustExpenses', 'default', 'App', 1768994493, 18),
(45, '2024-01-21-170001', 'App\\Database\\Migrations\\RegisterExpenseCategoryModule', 'default', 'App', 1768994614, 19),
(46, '2024-01-21-180000', 'App\\Database\\Migrations\\CreateCustomersAndVendorsTables', 'default', 'App', 1768995085, 20),
(47, '2024-01-21-180001', 'App\\Database\\Migrations\\RegisterSalesAndPurchaseModules', 'default', 'App', 1768995233, 21),
(48, '2024-01-21-190000', 'App\\Database\\Migrations\\ExpandCustomersAndVendorsFields', 'default', 'App', 1768995423, 22),
(49, '2024-01-21-193000', 'App\\Database\\Migrations\\DropCustomerBankFields', 'default', 'App', 1768996856, 23),
(50, '2024-01-21-200000', 'App\\Database\\Migrations\\CreateAddressesTable', 'default', 'App', 1768997094, 24),
(51, '2024-01-21-200500', 'App\\Database\\Migrations\\MigrateExistingAddresses', 'default', 'App', 1768997094, 24),
(52, '2024-01-21-210000', 'App\\Database\\Migrations\\AddZohoFields', 'default', 'App', 1768997523, 25),
(53, '2024-01-21-210500', 'App\\Database\\Migrations\\CreateZohoSettingsTable', 'default', 'App', 1768997523, 25),
(54, '2024-01-21-211000', 'App\\Database\\Migrations\\RegisterZohoSettingsModule', 'default', 'App', 1768998353, 26),
(67, '2024-01-22-100007', 'AddVerificationToProductItems', 'default', 'App\\Database\\Migrations', 1769056885, 1),
(68, '2024-01-22-100008', 'EnhanceApprovalProcess', 'default', 'App\\Database\\Migrations', 1769061148, 2),
(69, '2024-01-22-120000', 'AddReturnActionToProductItems', 'default', 'App\\Database\\Migrations', 1769062781, 3),
(76, '2024-01-22-100001', 'App\\Database\\Migrations\\CreateProductCategoriesTable', 'default', 'App', 1769066830, 27),
(77, '2024-01-22-100002', 'App\\Database\\Migrations\\CreateProductsTable', 'default', 'App', 1769066830, 27),
(78, '2024-01-22-100003', 'App\\Database\\Migrations\\CreateProductImagesTable', 'default', 'App', 1769066830, 27),
(79, '2024-01-22-100004', 'App\\Database\\Migrations\\CreateProductItemsTable', 'default', 'App', 1769066830, 27),
(80, '2024-01-22-100005', 'App\\Database\\Migrations\\CreateStockMovementsTable', 'default', 'App', 1769066830, 27),
(81, '2024-01-22-100006', 'App\\Database\\Migrations\\RegisterInventoryModules', 'default', 'App', 1769066831, 27),
(82, '2024-01-22-100007', 'App\\Database\\Migrations\\AddVerificationToProductItems', 'default', 'App', 1769400858, 28),
(83, '2024-01-22-100008', 'App\\Database\\Migrations\\EnhanceApprovalProcess', 'default', 'App', 1769400858, 28),
(84, '2024-01-22-120000', 'App\\Database\\Migrations\\AddReturnActionToProductItems', 'default', 'App', 1769400859, 28),
(85, '2026-01-22-120100', 'App\\Database\\Migrations\\CreateVendorCreditsTable', 'default', 'App', 1769400914, 29),
(86, '2026-01-22-120200', 'App\\Database\\Migrations\\CreateBillsTable', 'default', 'App', 1769400943, 30),
(87, '2026-01-22-120201', 'App\\Database\\Migrations\\CreateBillItemsTable', 'default', 'App', 1769400943, 30),
(88, '2026-01-22-120202', 'App\\Database\\Migrations\\CreatePaymentsTable', 'default', 'App', 1769400944, 30),
(89, '2026-01-22-120203', 'App\\Database\\Migrations\\RegisterBillsModule', 'default', 'App', 1769401119, 31),
(90, '2026-01-22-131500', 'App\\Database\\Migrations\\AddDiscountShippingToBills', 'default', 'App', 1769401119, 31),
(91, '2026-01-22-133000', 'App\\Database\\Migrations\\CreateTaxesTable', 'default', 'App', 1769401119, 31),
(92, '2026-01-22-134500', 'App\\Database\\Migrations\\AddFieldsToBillsAndItems', 'default', 'App', 1769401156, 32),
(93, '2026-01-26-101000', 'App\\Database\\Migrations\\AddDeductionsToPayments', 'default', 'App', 1769402405, 33),
(94, '2026-01-26-110000', 'App\\Database\\Migrations\\CreateInvoicesTable', 'default', 'App', 1769403536, 34),
(95, '2026-01-26-110001', 'App\\Database\\Migrations\\CreateInvoiceItemsTable', 'default', 'App', 1769403536, 34),
(96, '2026-01-26-110002', 'App\\Database\\Migrations\\CreateInvoicePaymentsTable', 'default', 'App', 1769403536, 34),
(97, '2026-01-26-110003', 'App\\Database\\Migrations\\RegisterInvoicesModule', 'default', 'App', 1769403571, 35),
(98, '2026-01-27-090000', 'App\\Database\\Migrations\\CreateQuotationsTable', 'default', 'App', 1769482538, 36),
(99, '2026-01-27-090001', 'App\\Database\\Migrations\\CreateQuotationItemsTable', 'default', 'App', 1769482538, 36),
(100, '2026-01-27-090002', 'App\\Database\\Migrations\\CreateSalesOrdersTable', 'default', 'App', 1769482538, 36),
(101, '2026-01-27-090003', 'App\\Database\\Migrations\\CreateSalesOrderItemsTable', 'default', 'App', 1769482538, 36),
(102, '2026-01-27-090004', 'App\\Database\\Migrations\\RegisterSalesModules', 'default', 'App', 1769482538, 36),
(103, '2026-01-27-100000', 'App\\Database\\Migrations\\AddPricingToProducts', 'default', 'App', 1769483512, 37),
(104, '2026-01-27-110000', 'App\\Database\\Migrations\\AddLogisticsToSalesDocuments', 'default', 'App', 1769484059, 38),
(105, '2026-01-27-111000', 'App\\Database\\Migrations\\AddBarcodeToProducts', 'default', 'App', 1769484932, 39),
(106, '2026-01-27-113000', 'App\\Database\\Migrations\\AddAgentCommissionFields', 'default', 'App', 1769486115, 40),
(107, '2026-01-27-120000', 'App\\Database\\Migrations\\CreateAgentPaymentsTable', 'default', 'App', 1769493600, 41),
(108, '2026-01-27-120001', 'App\\Database\\Migrations\\CreateAgentPaymentItemsTable', 'default', 'App', 1769493600, 41);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) UNSIGNED NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_slug` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module_name`, `module_slug`, `status`, `created_at`, `updated_at`) VALUES
(1, 'User Management', 'users', 'active', NULL, NULL),
(2, 'Employee Management', 'employees', 'active', NULL, NULL),
(3, 'Attendance Management', 'attendance', 'active', NULL, NULL),
(4, 'Payroll Management', 'payroll', 'active', NULL, NULL),
(5, 'Settings', '', 'active', '2026-01-21 07:02:52', '2026-01-21 07:02:52'),
(12, 'Settings', 'settings', 'active', '2026-01-21 07:48:25', '2026-01-21 07:48:25'),
(14, 'Countries', 'country', 'active', '2026-01-21 11:06:19', '2026-01-21 11:06:19'),
(15, 'States', 'state', 'active', '2026-01-21 11:06:19', '2026-01-21 11:06:19'),
(16, 'Agents', 'agent', 'active', '2026-01-21 11:06:19', '2026-01-21 11:06:19'),
(17, 'Transports', 'transport', 'active', '2026-01-21 11:06:19', '2026-01-21 11:06:19'),
(18, 'Expenses', 'expense', 'active', '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(19, 'Bank Accounts', 'bank_account', 'active', '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(20, 'Roles', 'roles', 'active', NULL, NULL),
(21, 'Employee', 'employee', 'active', NULL, NULL),
(22, 'Loan', 'loan', 'active', NULL, NULL),
(23, 'Salary', 'salary', 'active', NULL, NULL),
(24, 'Expense Categories', 'expense_category', 'active', '2026-01-21 11:23:34', '2026-01-21 11:23:34'),
(25, 'Customers', 'customer', 'active', '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(26, 'Vendors', 'vendor', 'active', '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(27, 'Zoho Integration', 'zoho_settings', 'active', '2026-01-21 12:25:53', '2026-01-21 12:25:53'),
(28, 'Product Categories', 'product_category', 'active', '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(29, 'Products', 'product', 'active', '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(30, 'Finance Management', 'finance', 'active', NULL, NULL),
(31, 'Sales Management', 'sales', 'active', NULL, NULL),
(32, 'Purchase Management', 'purchases', 'active', NULL, NULL),
(33, 'Inventory Management', 'inventory', 'active', NULL, NULL),
(34, 'Master Data', 'master_data', 'active', NULL, NULL),
(35, 'Tax Management', 'tax', 'active', NULL, NULL),
(36, 'Bills', 'bills', 'active', '2026-01-26 04:18:16', '2026-01-26 04:18:16'),
(37, 'Invoices', 'invoices', 'active', '2026-01-26 04:58:56', '2026-01-26 04:58:56'),
(38, 'Quotations', 'quotations', 'active', '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(39, 'Sales Orders', 'sales_orders', 'active', '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(42, 'Agent Payments', 'agent_payments', 'active', '2026-01-27 11:52:52', '2026-01-27 11:52:52'),
(46, 'Production', 'production', 'active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `bill_id` int(11) UNSIGNED NOT NULL,
  `vendor_id` int(11) UNSIGNED NOT NULL,
  `zoho_payment_id` varchar(100) DEFAULT NULL,
  `bank_transaction_id` int(11) UNSIGNED DEFAULT NULL,
  `payment_number` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_mode` enum('Cash','Bank Transfer','Cheque','Credit Card','Debit Card','UPI','Other') DEFAULT 'Bank Transfer',
  `amount` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `mahimai_amount` decimal(12,2) DEFAULT 0.00,
  `postal_charges` decimal(12,2) DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `bank_account_id` int(11) UNSIGNED DEFAULT NULL,
  `zoho_sync_status` enum('Pending','Synced','Failed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) UNSIGNED NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `module_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_key`, `permission_name`, `module_id`, `created_at`, `updated_at`) VALUES
(1, 'user.view', 'View Users', 1, NULL, NULL),
(2, 'user.create', 'Create User', 1, NULL, NULL),
(3, 'user.edit', 'Edit User', 1, NULL, NULL),
(4, 'user.delete', 'Delete User', 1, NULL, NULL),
(5, 'employee.view', 'View Employees', 2, NULL, NULL),
(6, 'employee.create', 'Create Employee', 2, NULL, NULL),
(7, 'employee.edit', 'Edit Employee', 2, NULL, NULL),
(8, 'employee.delete', 'Delete Employee', 2, NULL, NULL),
(9, 'attendance.view', 'View Attendance', 3, NULL, NULL),
(10, 'attendance.manage', 'Manage Attendance', 3, NULL, NULL),
(11, 'salary.view', 'View Salary', 4, NULL, NULL),
(12, 'salary.calculate', 'Calculate Salary', 4, NULL, NULL),
(13, 'setting.view', 'View Settings', 5, '2026-01-21 07:02:52', '2026-01-21 07:02:52'),
(14, 'role.view', 'View Roles', 1, '2026-01-21 12:41:54', '2026-01-21 12:41:54'),
(15, 'role.create', 'Create Role', 1, '2026-01-21 12:41:54', '2026-01-21 12:41:54'),
(16, 'role.edit', 'Edit Role', 1, '2026-01-21 12:41:54', '2026-01-21 12:41:54'),
(17, 'role.delete', 'Delete Role', 1, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(18, 'attendance.create', 'Create Attendance', 3, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(19, 'loan.view', 'View Loans', 4, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(20, 'loan.create', 'Create Loan', 4, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(21, 'loan.edit', 'Edit Loan', 4, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(22, 'loan.delete', 'Delete Loan', 4, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(23, 'salary.create', 'Calculate Salary', 4, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(24, 'salary.edit', 'Edit Salary Status', 4, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(25, 'salary.delete', 'Delete Salary', 4, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(26, 'setting.edit', 'Edit Settings', 5, '2026-01-21 12:41:55', '2026-01-21 12:41:55'),
(27, 'setting.view', 'View Settings', 12, '2026-01-21 07:48:25', '2026-01-21 07:48:25'),
(28, 'setting.view', 'View Settings', 12, '2026-01-21 07:49:03', '2026-01-21 07:49:03'),
(29, 'setting.edit', 'Edit Settings', 12, '2026-01-21 07:49:03', '2026-01-21 07:49:03'),
(30, 'users.view', 'View User Management', 1, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(31, 'users.create', 'Create User Management', 1, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(32, 'users.edit', 'Edit User Management', 1, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(33, 'users.delete', 'Delete User Management', 1, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(34, 'employees.view', 'View Employee Management', 2, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(35, 'employees.create', 'Create Employee Management', 2, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(36, 'employees.edit', 'Edit Employee Management', 2, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(37, 'employees.delete', 'Delete Employee Management', 2, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(38, 'attendance.edit', 'Edit Attendance Management', 3, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(39, 'attendance.delete', 'Delete Attendance Management', 3, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(40, 'payroll.view', 'View Payroll Management', 4, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(41, 'payroll.create', 'Create Payroll Management', 4, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(42, 'payroll.edit', 'Edit Payroll Management', 4, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(43, 'payroll.delete', 'Delete Payroll Management', 4, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(44, 'settings.view', 'View Settings', 12, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(45, 'settings.create', 'Create Settings', 12, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(46, 'settings.edit', 'Edit Settings', 12, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(47, 'settings.delete', 'Delete Settings', 12, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(48, 'country.view', 'View Countries', 14, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(49, 'country.create', 'Create Countries', 14, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(50, 'country.edit', 'Edit Countries', 14, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(51, 'country.delete', 'Delete Countries', 14, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(52, 'state.view', 'View States', 15, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(53, 'state.create', 'Create States', 15, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(54, 'state.edit', 'Edit States', 15, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(55, 'state.delete', 'Delete States', 15, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(56, 'agent.view', 'View Agents', 16, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(57, 'agent.create', 'Create Agents', 16, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(58, 'agent.edit', 'Edit Agents', 16, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(59, 'agent.delete', 'Delete Agents', 16, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(60, 'transport.view', 'View Transports', 17, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(61, 'transport.create', 'Create Transports', 17, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(62, 'transport.edit', 'Edit Transports', 17, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(63, 'transport.delete', 'Delete Transports', 17, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(64, 'expense.view', 'View Expenses', 18, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(65, 'expense.create', 'Create Expenses', 18, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(66, 'expense.edit', 'Edit Expenses', 18, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(67, 'expense.delete', 'Delete Expenses', 18, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(68, 'bank_account.view', 'View Bank Accounts', 19, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(69, 'bank_account.create', 'Create Bank Accounts', 19, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(70, 'bank_account.edit', 'Edit Bank Accounts', 19, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(71, 'bank_account.delete', 'Delete Bank Accounts', 19, '2026-01-21 11:06:20', '2026-01-21 11:06:20'),
(72, 'module.view', 'View Modules', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(73, 'module.create', 'Create Module', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(74, 'module.edit', 'Edit Module', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(75, 'module.delete', 'Delete Module', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(76, 'permission.view', 'View Permission Keys', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(77, 'permission.create', 'Create Permission Key', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(78, 'permission.edit', 'Edit Permission Key', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(79, 'permission.delete', 'Delete Permission Key', 1, '2026-01-21 11:11:23', '2026-01-21 11:11:23'),
(80, 'customer.view', 'View Customers', 25, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(81, 'customer.create', 'Create Customers', 25, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(82, 'customer.edit', 'Edit Customers', 25, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(83, 'customer.delete', 'Delete Customers', 25, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(84, 'vendor.view', 'View Vendors', 26, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(85, 'vendor.create', 'Create Vendors', 26, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(86, 'vendor.edit', 'Edit Vendors', 26, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(87, 'vendor.delete', 'Delete Vendors', 26, '2026-01-21 11:33:53', '2026-01-21 11:33:53'),
(88, 'zoho.view', 'View Zoho Settings', 27, NULL, NULL),
(89, 'zoho.edit', 'Edit Zoho Settings', 27, NULL, NULL),
(90, 'zoho.sync', 'Sync with Zoho', 27, NULL, NULL),
(91, 'product_category.view', 'View Product Categories', 28, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(92, 'product_category.create', 'Create Product Categories', 28, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(93, 'product_category.edit', 'Edit Product Categories', 28, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(94, 'product_category.delete', 'Delete Product Categories', 28, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(95, 'product.view', 'View Products', 29, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(96, 'product.create', 'Create Products', 29, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(97, 'product.edit', 'Edit Products', 29, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(98, 'product.delete', 'Delete Products', 29, '2026-01-22 04:04:13', '2026-01-22 04:04:13'),
(99, 'bill.view', 'View Bills', 32, NULL, NULL),
(100, 'bill.create', 'Create Bill', 32, NULL, NULL),
(101, 'bill.edit', 'Edit Bill', 32, NULL, NULL),
(102, 'bill.delete', 'Delete/Void Bill', 32, NULL, NULL),
(103, 'tax.view', 'View Taxes', 35, NULL, NULL),
(104, 'tax.create', 'Create Tax', 35, NULL, NULL),
(105, 'tax.edit', 'Edit Tax', 35, NULL, NULL),
(106, 'tax.delete', 'Delete Tax', 35, NULL, NULL),
(107, 'invoice.view', 'View Invoices', 37, '2026-01-26 04:58:56', '2026-01-26 04:58:56'),
(108, 'invoice.create', 'Create Invoices', 37, '2026-01-26 04:58:56', '2026-01-26 04:58:56'),
(109, 'invoice.edit', 'Edit Invoices', 37, '2026-01-26 04:58:56', '2026-01-26 04:58:56'),
(110, 'invoice.delete', 'Delete/Void Invoices', 37, '2026-01-26 04:58:56', '2026-01-26 04:58:56'),
(111, 'quotation.view', 'View Quotations', 38, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(112, 'quotation.create', 'Create Quotations', 38, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(113, 'quotation.edit', 'Edit Quotations', 38, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(114, 'quotation.delete', 'Delete Quotations', 38, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(115, 'sales_order.view', 'View Sales Orders', 39, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(116, 'sales_order.create', 'Create Sales Orders', 39, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(117, 'sales_order.edit', 'Edit Sales Orders', 39, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(118, 'sales_order.delete', 'Delete Sales Orders', 39, '2026-01-27 02:55:38', '2026-01-27 02:55:38'),
(140, 'agent_payments.view', 'View Agent Payments', 42, NULL, NULL),
(141, 'agent_payments.create', 'Create Agent Payments', 42, NULL, NULL),
(142, 'agent_payments.delete', 'Delete Agent Payments', 42, NULL, NULL),
(143, 'agent_payments.reports', 'View Commission Reports', 42, NULL, NULL),
(144, 'production.view', 'Access Production Module', 46, NULL, NULL),
(145, 'weaver.view', 'View Weavers', 46, NULL, NULL),
(146, 'weaver.create', 'Create Weaver', 46, NULL, NULL),
(147, 'weaver.edit', 'Edit Weaver', 46, NULL, NULL),
(148, 'weaver.delete', 'Delete Weaver', 46, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_agreements`
--

CREATE TABLE `production_agreements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `party_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `agreement_file` varchar(255) DEFAULT NULL,
  `status` enum('active','expired','terminated') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_agreements`
--

INSERT INTO `production_agreements` (`id`, `title`, `party_name`, `description`, `start_date`, `end_date`, `agreement_file`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Weaver Contract', 'Rasi Handloom Textiles Rasipuram', 'sdfgasdfgvasd', '2026-06-19', '2026-06-30', 'uploads/agreements/1782788806_1835a7f0fd3f27a61e65.pdf', 'active', 1, 1, '2026-06-30 03:05:29', '2026-06-30 03:06:46');

-- --------------------------------------------------------

--
-- Table structure for table `production_beams`
--

CREATE TABLE `production_beams` (
  `id` int(11) NOT NULL,
  `beam_number` varchar(100) NOT NULL,
  `status` enum('Empty','Loaded') NOT NULL DEFAULT 'Empty',
  `condition_status` enum('Active','Damaged') NOT NULL DEFAULT 'Active',
  `damaged_date` date DEFAULT NULL,
  `location` enum('In-House','At Job Work','At Weaving') NOT NULL DEFAULT 'In-House',
  `current_holder` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `production_beams`
--

INSERT INTO `production_beams` (`id`, `beam_number`, `status`, `condition_status`, `damaged_date`, `location`, `current_holder`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'BM-101', 'Empty', 'Active', NULL, '', 'Jhanvi', NULL, '2026-06-30 12:11:43', '2026-06-30 10:19:32'),
(2, 'BM-102', 'Empty', 'Active', NULL, 'At Job Work', 'VR SIZING', NULL, '2026-06-30 12:11:43', '2026-06-30 07:27:56'),
(3, 'BM-103', 'Empty', 'Active', NULL, 'In-House', NULL, NULL, '2026-06-30 12:11:43', '2026-06-30 12:11:43'),
(4, 'BM-104', 'Empty', 'Active', NULL, 'In-House', NULL, NULL, '2026-06-30 12:11:43', '2026-06-30 12:11:43'),
(5, 'BM-105', 'Empty', 'Active', NULL, 'In-House', NULL, NULL, '2026-06-30 12:11:43', '2026-06-30 12:11:43'),
(6, 'BM-106', 'Empty', 'Active', NULL, 'In-House', NULL, NULL, '2026-06-30 12:11:43', '2026-06-30 12:11:43'),
(7, 'BM-107', 'Empty', 'Active', NULL, 'In-House', NULL, NULL, '2026-06-30 12:11:43', '2026-06-30 12:11:43'),
(8, 'BM-108', 'Empty', 'Active', NULL, 'In-House', NULL, NULL, '2026-06-30 12:11:43', '2026-06-30 12:11:43'),
(9, 'BM-109', 'Empty', 'Active', NULL, 'In-House', NULL, NULL, '2026-06-30 12:11:43', '2026-06-30 12:11:43'),
(10, 'BM-110', 'Empty', 'Active', NULL, 'At Job Work', 'VR SIZING', NULL, '2026-06-30 12:11:43', '2026-06-30 07:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `production_beam_ledger`
--

CREATE TABLE `production_beam_ledger` (
  `id` int(11) NOT NULL,
  `beam_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` enum('Issue_Warping_Sizing','Receipt_Warping_Sizing','Issue_Weaving','Receipt_Weaving','Manual_Adjustment') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `from_location` varchar(255) NOT NULL,
  `to_location` varchar(255) NOT NULL,
  `status_from` enum('Empty','Loaded') NOT NULL,
  `status_to` enum('Empty','Loaded') NOT NULL,
  `yarn_details` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `production_beam_ledger`
--

INSERT INTO `production_beam_ledger` (`id`, `beam_id`, `transaction_date`, `transaction_type`, `reference_id`, `from_location`, `to_location`, `status_from`, `status_to`, `yarn_details`, `remarks`, `created_by`, `created_at`) VALUES
(6, 10, '2026-06-30', 'Issue_Warping_Sizing', 5, 'In-House', 'VR SIZING', 'Empty', 'Empty', NULL, 'Sent empty for warping & sizing on DC DC-YARN-1002', 1, '2026-06-30 12:57:56'),
(7, 2, '2026-06-30', 'Issue_Warping_Sizing', 5, 'In-House', 'VR SIZING', 'Empty', 'Empty', NULL, 'Sent empty for warping & sizing on DC DC-YARN-1002', 1, '2026-06-30 12:57:56'),
(9, 1, '2026-06-30', 'Manual_Adjustment', NULL, 'At Job Work', 'At Job Work', 'Empty', 'Empty', NULL, 'Condition marked as Damaged on 2026-06-30', 1, '2026-06-30 14:59:26'),
(10, 1, '2026-06-30', 'Manual_Adjustment', NULL, 'At Job Work', 'At Job Work', 'Empty', 'Empty', NULL, 'Condition marked as Active', 1, '2026-06-30 14:59:32'),
(16, 1, '2026-06-30', 'Issue_Warping_Sizing', 6, 'In-House', 'Jhanvi', 'Empty', 'Empty', NULL, 'Sent empty for warping & sizing on DC DC-YARN-1003', 1, '2026-06-30 15:49:32');

-- --------------------------------------------------------

--
-- Table structure for table `production_vendors`
--

CREATE TABLE `production_vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `gst_number` varchar(15) DEFAULT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_vendors`
--

INSERT INTO `production_vendors` (`id`, `name`, `business_type`, `gst_number`, `pan_number`, `phone`, `address`, `location`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Rasi Handloom Textiles Rasipuram', 'Proprietorship', '33FOPPS7926B1ZK', 'FOPPS7926B', '09865073006', '2/9, KUNDUKALLAR THOTTAM', 'https://maps.google.com', 'active', 1, NULL, '2026-06-30 02:55:26', '2026-06-30 02:55:26');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarns`
--

CREATE TABLE `production_yarns` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `yarn_type` enum('Dyed','Raw') NOT NULL,
  `color` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `stock_kg` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_dyeing_dcs`
--

CREATE TABLE `production_yarn_dyeing_dcs` (
  `id` int(11) NOT NULL,
  `dc_number` varchar(50) NOT NULL,
  `dc_date` date NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `vehicle_details` varchar(255) DEFAULT NULL,
  `status` enum('Open','Partially Received','Completed','Cancelled') DEFAULT 'Open',
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_dyeing_dcs`
--

INSERT INTO `production_yarn_dyeing_dcs` (`id`, `dc_number`, `dc_date`, `vendor_name`, `expected_return_date`, `vehicle_details`, `status`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'DC-YARN-1001', '2026-06-30', 'Jhanvi', '2026-06-30', '', 'Completed', '0', 1, 1, '2026-06-30 11:08:26', '2026-06-30 11:56:32');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_dyeing_dc_items`
--

CREATE TABLE `production_yarn_dyeing_dc_items` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `mill_name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `warp_weft` varchar(50) NOT NULL,
  `csp` varchar(50) DEFAULT NULL,
  `lot_number` varchar(50) DEFAULT NULL,
  `yarn_type` varchar(50) NOT NULL,
  `current_color` varchar(50) NOT NULL,
  `required_color` varchar(50) DEFAULT NULL,
  `quantity_issued_kg` decimal(10,2) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_dyeing_dc_items`
--

INSERT INTO `production_yarn_dyeing_dc_items` (`id`, `dc_id`, `mill_name`, `yarn_count`, `warp_weft`, `csp`, `lot_number`, `yarn_type`, `current_color`, `required_color`, `quantity_issued_kg`, `quantity_received_kg`, `quantity_wastage_kg`) VALUES
(2, 1, 'SAMBANDAM', '80', 'Warp', '3200', 'LOT50', 'Raw', 'Raw', '0', 50.00, 40.00, 10.00),
(3, 1, 'SAMBANDAM', '80', 'Warp', '3200', 'LOT50', 'Raw', 'Raw', '0', 45.00, 35.00, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_dyeing_receipts`
--

CREATE TABLE `production_yarn_dyeing_receipts` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `transport_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loading_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `packing_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_dyeing_receipts`
--

INSERT INTO `production_yarn_dyeing_receipts` (`id`, `dc_id`, `receipt_number`, `receipt_date`, `transport_charges`, `loading_charges`, `packing_charges`, `other_expenses`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(10, 1, 'REC-YARN-1001', '2026-06-30', 150.00, 100.00, 10.00, 10.00, '', 1, NULL, NULL, NULL),
(11, 1, 'REC-YARN-1002', '2026-06-30', 0.00, 0.00, 0.00, 0.00, '', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_dyeing_receipt_items`
--

CREATE TABLE `production_yarn_dyeing_receipt_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `dc_item_id` int(11) NOT NULL,
  `received_color` varchar(50) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity_shortage_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity_excess_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `job_work_charges` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_dyeing_receipt_items`
--

INSERT INTO `production_yarn_dyeing_receipt_items` (`id`, `receipt_id`, `dc_item_id`, `received_color`, `quantity_received_kg`, `quantity_wastage_kg`, `quantity_shortage_kg`, `quantity_excess_kg`, `job_work_charges`) VALUES
(24, 11, 2, '0', 30.00, 0.00, 0.00, 0.00, 160.00),
(25, 11, 3, '0', 25.00, 0.00, 0.00, 0.00, 140.00),
(26, 10, 2, '0', 10.00, 10.00, 0.00, 0.00, 150.00),
(27, 10, 3, '0', 10.00, 10.00, 0.00, 0.00, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_job_work_dcs`
--

CREATE TABLE `production_yarn_job_work_dcs` (
  `id` int(11) NOT NULL,
  `dc_number` varchar(50) NOT NULL,
  `dc_date` date NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `job_work_type` enum('Dyeing','Warping & Sizing','Warping','Sizing','Twisting','Weaving') NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `vehicle_details` varchar(255) DEFAULT NULL,
  `status` enum('Open','Partially Received','Completed','Cancelled') DEFAULT 'Open',
  `remarks` text DEFAULT NULL,
  `design_pattern` varchar(255) DEFAULT NULL,
  `total_ends` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_job_work_dcs`
--

INSERT INTO `production_yarn_job_work_dcs` (`id`, `dc_number`, `dc_date`, `vendor_name`, `job_work_type`, `expected_return_date`, `vehicle_details`, `status`, `remarks`, `design_pattern`, `total_ends`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'DC-YARN-1001', '2026-06-30', 'Jhanvi', 'Dyeing', '2026-06-30', '', 'Completed', '', NULL, NULL, 1, 1, '2026-06-30 11:08:26', '2026-06-30 11:56:32'),
(5, 'DC-YARN-1002', '2026-06-30', 'VR SIZING', 'Warping & Sizing', '2026-06-18', 'TN28BY9140', 'Open', '', NULL, 3700, 1, NULL, '2026-06-30 12:57:56', '2026-06-30 12:57:56'),
(6, 'DC-YARN-1003', '2026-06-30', 'Jhanvi', 'Warping & Sizing', NULL, 'TN28BY9140', 'Open', '', '3inch Border ', 3700, 1, NULL, '2026-06-30 12:59:36', '2026-06-30 12:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_job_work_dc_beams`
--

CREATE TABLE `production_yarn_job_work_dc_beams` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `receipt_id` int(11) DEFAULT NULL,
  `returned_status` enum('Loaded','Empty','Not Returned') DEFAULT NULL,
  `meters` decimal(10,2) DEFAULT NULL,
  `sizing_no` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `beam_number` varchar(100) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `production_yarn_job_work_dc_beams`
--

INSERT INTO `production_yarn_job_work_dc_beams` (`id`, `dc_id`, `receipt_id`, `returned_status`, `meters`, `sizing_no`, `color`, `return_date`, `beam_number`, `remarks`, `created_at`) VALUES
(6, 5, NULL, NULL, NULL, NULL, NULL, NULL, 'BM-110', '', '2026-06-30 12:57:56'),
(7, 5, NULL, NULL, NULL, NULL, NULL, NULL, 'BM-102', '', '2026-06-30 12:57:56'),
(8, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'BM-101', '', '2026-06-30 12:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_job_work_dc_color_ends`
--

CREATE TABLE `production_yarn_job_work_dc_color_ends` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `color` varchar(100) NOT NULL,
  `ends_count` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `production_yarn_job_work_dc_color_ends`
--

INSERT INTO `production_yarn_job_work_dc_color_ends` (`id`, `dc_id`, `color`, `ends_count`, `created_at`) VALUES
(7, 5, 'Green', 450, '2026-06-30 12:57:56'),
(8, 5, 'Royal Blue', 2800, '2026-06-30 12:57:56'),
(9, 5, 'Green', 450, '2026-06-30 12:57:56'),
(10, 6, 'Raw', 3700, '2026-06-30 12:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_job_work_dc_items`
--

CREATE TABLE `production_yarn_job_work_dc_items` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `mill_name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `warp_weft` enum('Warp','Weft') NOT NULL,
  `csp` varchar(50) DEFAULT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `yarn_type` enum('Raw','Dyed') NOT NULL,
  `current_color` varchar(100) DEFAULT NULL,
  `required_color` varchar(100) DEFAULT NULL,
  `quantity_issued_kg` decimal(12,2) NOT NULL,
  `quantity_received_kg` decimal(12,2) DEFAULT 0.00,
  `quantity_wastage_kg` decimal(12,2) DEFAULT 0.00,
  `quantity_returned_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_job_work_dc_items`
--

INSERT INTO `production_yarn_job_work_dc_items` (`id`, `dc_id`, `mill_name`, `yarn_count`, `warp_weft`, `csp`, `lot_number`, `yarn_type`, `current_color`, `required_color`, `quantity_issued_kg`, `quantity_received_kg`, `quantity_wastage_kg`, `quantity_returned_kg`) VALUES
(2, 1, 'SAMBANDAM', '80', 'Warp', '3200', 'LOT50', 'Raw', 'Raw', 'Royal Blue', 50.00, 40.00, 10.00, 0.00),
(3, 1, 'SAMBANDAM', '80', 'Warp', '3200', 'LOT50', 'Raw', 'Raw', 'Green', 45.00, 35.00, 10.00, 0.00),
(5, 5, 'SAMBANDAM', '80', 'Warp', '3200', 'GJ1230', 'Dyed', 'Green', NULL, 15.00, 0.00, 0.00, 0.00),
(6, 5, 'SAMBANDAM', '80', 'Warp', '3200', 'HJ2331', 'Dyed', 'Royal Blue', NULL, 20.00, 0.00, 0.00, 0.00),
(7, 6, 'SAMBANDAM', '80', 'Warp', '3200', 'LOT50', 'Raw', 'Raw', NULL, 15.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_job_work_receipts`
--

CREATE TABLE `production_yarn_job_work_receipts` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `transport_charges` decimal(12,2) DEFAULT 0.00,
  `loading_charges` decimal(12,2) DEFAULT 0.00,
  `packing_charges` decimal(12,2) DEFAULT 0.00,
  `other_expenses` decimal(12,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_job_work_receipts`
--

INSERT INTO `production_yarn_job_work_receipts` (`id`, `dc_id`, `receipt_number`, `receipt_date`, `transport_charges`, `loading_charges`, `packing_charges`, `other_expenses`, `remarks`, `created_by`, `created_at`) VALUES
(10, 1, 'REC-YARN-1001', '2026-06-30', 150.00, 100.00, 10.00, 10.00, '', 1, NULL),
(11, 1, 'REC-YARN-1002', '2026-06-30', 0.00, 0.00, 0.00, 0.00, '', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_job_work_receipt_items`
--

CREATE TABLE `production_yarn_job_work_receipt_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `dc_item_id` int(11) NOT NULL,
  `received_color` varchar(100) DEFAULT NULL,
  `quantity_received_kg` decimal(12,2) NOT NULL,
  `quantity_wastage_kg` decimal(12,2) DEFAULT 0.00,
  `quantity_returned_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity_shortage_kg` decimal(12,2) DEFAULT 0.00,
  `quantity_excess_kg` decimal(12,2) DEFAULT 0.00,
  `job_work_charges` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_job_work_receipt_items`
--

INSERT INTO `production_yarn_job_work_receipt_items` (`id`, `receipt_id`, `dc_item_id`, `received_color`, `quantity_received_kg`, `quantity_wastage_kg`, `quantity_returned_kg`, `quantity_shortage_kg`, `quantity_excess_kg`, `job_work_charges`) VALUES
(24, 11, 2, 'Royal Blue', 30.00, 0.00, 0.00, 0.00, 0.00, 160.00),
(25, 11, 3, 'Green', 25.00, 0.00, 0.00, 0.00, 0.00, 140.00),
(26, 10, 2, 'Royal Blue', 10.00, 10.00, 0.00, 0.00, 0.00, 150.00),
(27, 10, 3, 'Green', 10.00, 10.00, 0.00, 0.00, 0.00, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_master`
--

CREATE TABLE `production_yarn_master` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `yarn_type` enum('Dyed','Raw') NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_master`
--

INSERT INTO `production_yarn_master` (`id`, `name`, `yarn_type`, `yarn_count`, `created_at`, `updated_at`) VALUES
(2, 'Cotton 60s', 'Raw', '60s', '2026-06-30 08:47:19', NULL),
(3, 'Cotton 80s', 'Raw', '80s', '2026-06-30 08:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_purchases`
--

CREATE TABLE `production_yarn_purchases` (
  `id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `mill_name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `material_type` varchar(100) NOT NULL,
  `warp_weft` enum('Warp','Weft') NOT NULL,
  `csp` varchar(50) DEFAULT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `number_bags` int(11) DEFAULT 0,
  `total_weight_kg` decimal(12,2) DEFAULT 0.00,
  `rate_per_kg` decimal(12,2) DEFAULT 0.00,
  `gst_percent` decimal(5,2) DEFAULT 0.00,
  `transport_charges` decimal(12,2) DEFAULT 0.00,
  `other_charges` decimal(12,2) DEFAULT 0.00,
  `warehouse_location` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_purchases`
--

INSERT INTO `production_yarn_purchases` (`id`, `purchase_date`, `supplier`, `invoice_number`, `mill_name`, `yarn_count`, `material_type`, `warp_weft`, `csp`, `lot_number`, `number_bags`, `total_weight_kg`, `rate_per_kg`, `gst_percent`, `transport_charges`, `other_charges`, `warehouse_location`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '2026-06-30', 'Gowtham', '1234', 'SAMBANDAM', '80', 'Cotton Yarn', 'Warp', '3200', 'LOT50', 3, 150.00, 430.00, 5.00, 1500.00, 150.00, 'Main Warehouse', 1, 1, '2026-06-30 10:50:12', '2026-06-30 10:55:58');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_stock_movements`
--

CREATE TABLE `production_yarn_stock_movements` (
  `id` int(11) NOT NULL,
  `yarn_name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `yarn_type` enum('Raw','Dyed') NOT NULL,
  `color` varchar(100) DEFAULT NULL,
  `brand_mill` varchar(255) DEFAULT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `csp` varchar(50) DEFAULT NULL,
  `warp_weft` enum('Warp','Weft') NOT NULL,
  `quantity_kg` decimal(12,2) NOT NULL,
  `cost_per_kg` decimal(12,2) NOT NULL,
  `warehouse` varchar(255) DEFAULT NULL,
  `movement_type` enum('Purchase','Issue_Job_Work','Receipt_Job_Work','Adjustment','Return') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_stock_movements`
--

INSERT INTO `production_yarn_stock_movements` (`id`, `yarn_name`, `yarn_count`, `yarn_type`, `color`, `brand_mill`, `lot_number`, `csp`, `warp_weft`, `quantity_kg`, `cost_per_kg`, `warehouse`, `movement_type`, `reference_id`, `remarks`, `created_by`, `created_at`) VALUES
(3, 'Cotton Yarn', '80', 'Raw', 'Raw', 'SAMBANDAM', 'LOT50', '3200', 'Warp', 150.00, 441.00, 'Main Warehouse', 'Purchase', 1, 'Updated Yarn purchase. Supplier: Gowtham, Invoice: 1234', 1, '2026-06-30 10:55:58'),
(6, 'Yarn (80)', '80', 'Raw', 'Raw', 'SAMBANDAM', 'LOT50', '3200', 'Warp', -50.00, 441.00, 'Main Warehouse', 'Issue_Job_Work', 1, 'Issued for Dyeing to Jhanvi (DC: DC-YARN-1001)', 1, '2026-06-30 11:27:27'),
(7, 'Yarn (80)', '80', 'Raw', 'Raw', 'SAMBANDAM', 'LOT50', '3200', 'Warp', -45.00, 441.00, 'Main Warehouse', 'Issue_Job_Work', 1, 'Issued for Dyeing to Jhanvi (DC: DC-YARN-1001)', 1, '2026-06-30 11:27:27'),
(30, 'Yarn (80)', '80', 'Dyed', 'Royal Blue', 'SAMBANDAM', 'HJ2331', '3200', 'Warp', 30.00, 601.00, 'Main Warehouse', 'Receipt_Job_Work', 11, 'Received from Jhanvi against DC-YARN-1001 (Color: Royal Blue, Receipt: REC-YARN-1002)', 1, '2026-06-30 11:56:10'),
(31, 'Yarn (80)', '80', 'Dyed', 'Green', 'SAMBANDAM', 'GJ1230', '3200', 'Warp', 25.00, 581.00, 'Main Warehouse', 'Receipt_Job_Work', 11, 'Received from Jhanvi against DC-YARN-1001 (Color: Green, Receipt: REC-YARN-1002)', 1, '2026-06-30 11:56:10'),
(32, 'Yarn (80)', '80', 'Dyed', 'Royal Blue', 'SAMBANDAM', 'HJ2331q', '3200', 'Warp', 10.00, 604.50, 'Main Warehouse', 'Receipt_Job_Work', 10, 'Received from Jhanvi against DC-YARN-1001 (Color: Royal Blue, Receipt: REC-YARN-1001) [Updated]', 1, '2026-06-30 11:56:32'),
(33, 'Yarn (80)', '80', 'Dyed', 'Green', 'SAMBANDAM', 'GJ12301', '3200', 'Warp', 10.00, 554.50, 'Main Warehouse', 'Receipt_Job_Work', 10, 'Received from Jhanvi against DC-YARN-1001 (Color: Green, Receipt: REC-YARN-1001) [Updated]', 1, '2026-06-30 11:56:32'),
(35, 'Yarn (80)', '80', 'Dyed', 'Green', 'SAMBANDAM', 'GJ1230', '3200', 'Warp', -15.00, 581.00, 'Main Warehouse', 'Issue_Job_Work', 5, 'Issued for Warping & Sizing to VR SIZING (DC: DC-YARN-1002)', 1, '2026-06-30 12:57:56'),
(36, 'Yarn (80)', '80', 'Dyed', 'Royal Blue', 'SAMBANDAM', 'HJ2331', '3200', 'Warp', -20.00, 601.00, 'Main Warehouse', 'Issue_Job_Work', 5, 'Issued for Warping & Sizing to VR SIZING (DC: DC-YARN-1002)', 1, '2026-06-30 12:57:56'),
(43, 'Yarn (80)', '80', 'Raw', 'Raw', 'SAMBANDAM', 'LOT50', '3200', 'Warp', -15.00, 441.00, 'Main Warehouse', 'Issue_Job_Work', 6, 'Issued for Warping & Sizing to Jhanvi (DC: DC-YARN-1003)', 1, '2026-06-30 15:49:32');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_twisting_dcs`
--

CREATE TABLE `production_yarn_twisting_dcs` (
  `id` int(11) NOT NULL,
  `dc_number` varchar(50) NOT NULL,
  `dc_date` date NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `vehicle_details` varchar(255) DEFAULT NULL,
  `status` enum('Open','Partially Received','Completed','Cancelled') DEFAULT 'Open',
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_twisting_dc_items`
--

CREATE TABLE `production_yarn_twisting_dc_items` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `mill_name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `warp_weft` varchar(50) NOT NULL,
  `csp` varchar(50) DEFAULT NULL,
  `lot_number` varchar(50) DEFAULT NULL,
  `yarn_type` varchar(50) NOT NULL,
  `current_color` varchar(50) NOT NULL,
  `quantity_issued_kg` decimal(10,2) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_twisting_receipts`
--

CREATE TABLE `production_yarn_twisting_receipts` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `transport_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loading_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `packing_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_twisting_receipt_items`
--

CREATE TABLE `production_yarn_twisting_receipt_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `dc_item_id` int(11) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `job_work_charges` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_warping_sizing_dcs`
--

CREATE TABLE `production_yarn_warping_sizing_dcs` (
  `id` int(11) NOT NULL,
  `dc_number` varchar(50) NOT NULL,
  `dc_date` date NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `vehicle_details` varchar(255) DEFAULT NULL,
  `status` enum('Open','Partially Received','Completed','Cancelled') DEFAULT 'Open',
  `remarks` text DEFAULT NULL,
  `design_pattern` varchar(255) DEFAULT NULL,
  `total_ends` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_warping_sizing_dcs`
--

INSERT INTO `production_yarn_warping_sizing_dcs` (`id`, `dc_number`, `dc_date`, `vendor_name`, `expected_return_date`, `vehicle_details`, `status`, `remarks`, `design_pattern`, `total_ends`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(5, 'DC-YARN-1002', '2026-06-30', 'VR SIZING', '2026-06-18', 'TN28BY9140', 'Open', '0', NULL, 3700, 1, NULL, '2026-06-30 12:57:56', '2026-06-30 12:57:56'),
(6, 'DC-YARN-1003', '2026-06-30', 'Jhanvi', NULL, 'TN28BY9140', 'Open', NULL, '3inch Border ', 3700, 1, 1, '2026-06-30 12:59:36', '2026-06-30 15:49:32');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_warping_sizing_dc_beams`
--

CREATE TABLE `production_yarn_warping_sizing_dc_beams` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `receipt_id` int(11) DEFAULT NULL,
  `returned_status` enum('Loaded','Empty','Not Returned') DEFAULT NULL,
  `meters` decimal(10,2) DEFAULT NULL,
  `sizing_no` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `beam_number` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_warping_sizing_dc_beams`
--

INSERT INTO `production_yarn_warping_sizing_dc_beams` (`id`, `dc_id`, `receipt_id`, `returned_status`, `meters`, `sizing_no`, `color`, `return_date`, `beam_number`, `remarks`) VALUES
(1, 5, NULL, NULL, NULL, NULL, NULL, NULL, 'BM-110', ''),
(2, 5, NULL, NULL, NULL, NULL, NULL, NULL, 'BM-102', ''),
(6, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'BM-101', 'Issued empty on DC DC-YARN-1003');

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_warping_sizing_dc_color_ends`
--

CREATE TABLE `production_yarn_warping_sizing_dc_color_ends` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `color` varchar(100) NOT NULL,
  `ends_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_warping_sizing_dc_color_ends`
--

INSERT INTO `production_yarn_warping_sizing_dc_color_ends` (`id`, `dc_id`, `color`, `ends_count`) VALUES
(1, 5, 'Green', 450),
(2, 5, 'Royal Blue', 2800),
(3, 5, 'Green', 450),
(5, 6, 'Raw', 3700);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_warping_sizing_dc_items`
--

CREATE TABLE `production_yarn_warping_sizing_dc_items` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `mill_name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `warp_weft` varchar(50) NOT NULL,
  `warp_yarn_type` varchar(50) DEFAULT NULL,
  `csp` varchar(50) DEFAULT NULL,
  `lot_number` varchar(50) DEFAULT NULL,
  `yarn_type` varchar(50) NOT NULL,
  `current_color` varchar(50) NOT NULL,
  `quantity_issued_kg` decimal(10,2) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_yarn_warping_sizing_dc_items`
--

INSERT INTO `production_yarn_warping_sizing_dc_items` (`id`, `dc_id`, `mill_name`, `yarn_count`, `warp_weft`, `warp_yarn_type`, `csp`, `lot_number`, `yarn_type`, `current_color`, `quantity_issued_kg`, `quantity_received_kg`, `quantity_wastage_kg`) VALUES
(5, 5, 'SAMBANDAM', '80', 'Warp', NULL, '3200', 'GJ1230', 'Dyed', 'Green', 15.00, 0.00, 0.00),
(6, 5, 'SAMBANDAM', '80', 'Warp', NULL, '3200', 'HJ2331', 'Dyed', 'Royal Blue', 20.00, 0.00, 0.00),
(10, 6, 'SAMBANDAM', '80', 'Warp', NULL, '3200', 'LOT50', 'Raw', 'Raw', 15.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_warping_sizing_receipts`
--

CREATE TABLE `production_yarn_warping_sizing_receipts` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `transport_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loading_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `packing_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_warping_sizing_receipt_items`
--

CREATE TABLE `production_yarn_warping_sizing_receipt_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `dc_item_id` int(11) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `job_work_charges` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_weaving_dcs`
--

CREATE TABLE `production_yarn_weaving_dcs` (
  `id` int(11) NOT NULL,
  `dc_number` varchar(50) NOT NULL,
  `dc_date` date NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `vehicle_details` varchar(255) DEFAULT NULL,
  `status` enum('Open','Partially Received','Completed','Cancelled') DEFAULT 'Open',
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_weaving_dc_items`
--

CREATE TABLE `production_yarn_weaving_dc_items` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `mill_name` varchar(255) NOT NULL,
  `yarn_count` varchar(50) NOT NULL,
  `warp_weft` varchar(50) NOT NULL,
  `csp` varchar(50) DEFAULT NULL,
  `lot_number` varchar(50) DEFAULT NULL,
  `yarn_type` varchar(50) NOT NULL,
  `current_color` varchar(50) NOT NULL,
  `quantity_issued_kg` decimal(10,2) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_weaving_receipts`
--

CREATE TABLE `production_yarn_weaving_receipts` (
  `id` int(11) NOT NULL,
  `dc_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `transport_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loading_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `packing_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_yarn_weaving_receipt_items`
--

CREATE TABLE `production_yarn_weaving_receipt_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `dc_item_id` int(11) NOT NULL,
  `quantity_received_kg` decimal(10,2) NOT NULL,
  `quantity_wastage_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `job_work_charges` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `hsn_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `selling_price` decimal(12,2) DEFAULT 0.00,
  `tax_id` int(11) UNSIGNED DEFAULT NULL,
  `total_stock` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `barcode`, `category_id`, `hsn_code`, `description`, `unit`, `selling_price`, `tax_id`, `total_stock`, `status`, `created_at`, `updated_at`) VALUES
(1, 'KASAVU KERALA SAREE GOLD', NULL, 9, '5208', '', 'Pcs', 250.00, 1, 0, 'active', '2026-01-26 04:09:44', '2026-01-30 11:59:01'),
(2, 'Plain Neli Border', NULL, 4, '5208', '', 'Pcs', 930.00, 2, 0, 'active', '2026-01-30 10:57:15', '2026-01-30 11:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `parent_id` int(11) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `category_name`, `parent_id`, `description`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Sarees', NULL, '', 'active', '2026-01-30 11:55:11', '2026-01-30 11:55:11'),
(4, 'Plain', 8, '', 'active', '2026-01-30 11:55:56', '2026-01-30 11:57:28'),
(5, 'Butta', 8, '', 'active', '2026-01-30 11:56:03', '2026-01-30 11:57:46'),
(6, 'Fancy', 3, '', 'active', '2026-01-30 11:56:27', '2026-01-30 11:56:27'),
(7, 'Low Cost Cotton Mix', 3, '', 'active', '2026-01-30 11:56:50', '2026-01-30 11:56:50'),
(8, 'Chettinad', 3, '', 'active', '2026-01-30 11:57:21', '2026-01-30 11:57:21'),
(9, 'Kasavu', 3, '', 'active', '2026-01-30 11:58:50', '2026-01-30 11:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 1, 'uploads/products/1769400585_1d9031871b90a93e7ab7.png', 1, '2026-01-26 04:09:45', '2026-01-26 04:09:45'),
(2, 1, 'uploads/products/1769400585_eedd01530d351ffc15a9.png', 0, '2026-01-26 04:09:45', '2026-01-26 04:09:45'),
(3, 1, 'uploads/products/1769400585_93684edffff3a34291d1.jpg', 0, '2026-01-26 04:09:45', '2026-01-26 04:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `product_items`
--

CREATE TABLE `product_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `received_image` varchar(255) DEFAULT NULL,
  `verified_image` varchar(255) DEFAULT NULL,
  `purchase_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('received','available','sold','damaged','returned','rejected') DEFAULT 'received',
  `is_approved` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejection_image` varchar(255) DEFAULT NULL,
  `return_action` enum('Pending','Returned','Exchanged') DEFAULT 'Pending',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `sold_date` datetime DEFAULT NULL,
  `sold_reference_no` varchar(100) DEFAULT NULL,
  `sold_type` varchar(50) DEFAULT NULL,
  `vendor_id` int(11) UNSIGNED DEFAULT NULL,
  `return_shipment_id` int(11) DEFAULT NULL,
  `vendor_invoice_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `is_damaged` enum('Yes','No') NOT NULL DEFAULT 'No',
  `damage_owner` enum('vendor','owner','none') NOT NULL DEFAULT 'none',
  `damage_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) UNSIGNED DEFAULT NULL,
  `agent_commission_percent` decimal(5,2) DEFAULT 0.00,
  `agent_commission_amount` decimal(15,2) DEFAULT 0.00,
  `agent_commission_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `agent_commission_paid_at` datetime DEFAULT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `zoho_estimate_id` varchar(100) DEFAULT NULL,
  `zoho_sync_status` enum('Pending','Synced','Failed') NOT NULL DEFAULT 'Pending',
  `quotation_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `transport_name` varchar(150) DEFAULT NULL,
  `waybill_number` varchar(50) DEFAULT NULL,
  `status` enum('Draft','Sent','Accepted','Declined','Invoiced','Expired') NOT NULL DEFAULT 'Draft',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('Fixed','Percentage') NOT NULL DEFAULT 'Fixed',
  `shipping_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `roundoff_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_inter_state` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `quotation_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_shipments`
--

CREATE TABLE `return_shipments` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `return_date` date NOT NULL,
  `transport_name` varchar(100) DEFAULT NULL,
  `waybill_number` varchar(100) DEFAULT NULL,
  `waybill_date` date DEFAULT NULL,
  `packages_count` int(11) DEFAULT NULL,
  `ewaybill_number` varchar(50) DEFAULT NULL,
  `waybill_image` varchar(255) DEFAULT NULL,
  `delivery_status` enum('Pending','In Transit','Completed','Cancelled') DEFAULT 'Pending',
  `status` varchar(20) DEFAULT 'Pending',
  `item_count` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrator with full access', '2026-01-21 04:53:24', '2026-01-21 04:53:24'),
(2, 'staff', 'Regular employee with limited access', '2026-01-21 04:53:24', '2026-01-21 04:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) UNSIGNED NOT NULL,
  `permission_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 72),
(1, 73),
(1, 74),
(1, 75),
(1, 76),
(1, 77),
(1, 78),
(1, 79),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 9),
(1, 10),
(1, 18),
(1, 38),
(1, 39),
(1, 11),
(1, 12),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 13),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(1, 58),
(1, 59),
(1, 60),
(1, 61),
(1, 62),
(1, 63),
(1, 64),
(1, 65),
(1, 66),
(1, 67),
(1, 68),
(1, 69),
(1, 70),
(1, 71),
(1, 80),
(1, 81),
(1, 82),
(1, 83),
(1, 84),
(1, 85),
(1, 86),
(1, 87),
(1, 88),
(1, 89),
(1, 90),
(1, 91),
(1, 92),
(1, 93),
(1, 94),
(1, 95),
(1, 96),
(1, 97),
(1, 98),
(1, 99),
(1, 100),
(1, 101),
(1, 102),
(1, 103),
(1, 104),
(1, 105),
(1, 106),
(1, 107),
(1, 108),
(1, 109),
(1, 110),
(1, 111),
(1, 112),
(1, 113),
(1, 114),
(1, 115),
(1, 116),
(1, 117),
(1, 118),
(1, 140),
(1, 141),
(1, 142),
(1, 143),
(1, 144),
(1, 145),
(1, 146),
(1, 147),
(1, 148);

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `id` int(11) UNSIGNED NOT NULL,
  `employee_id` int(11) UNSIGNED NOT NULL,
  `salary_month` date NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowances` text DEFAULT NULL,
  `deductions` text DEFAULT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `is_paid` tinyint(1) DEFAULT 0,
  `paid_date` date DEFAULT NULL,
  `exempt_loan_deduction` tinyint(1) DEFAULT 0 COMMENT 'If 1, skip loan deduction for this salary',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salaries`
--

INSERT INTO `salaries` (`id`, `employee_id`, `salary_month`, `basic_salary`, `allowances`, `deductions`, `net_salary`, `is_paid`, `paid_date`, `exempt_loan_deduction`, `created_at`, `updated_at`) VALUES
(9, 1, '2026-01-01', 7000.00, '0', '225.8064516129', 6774.19, 1, '2026-01-21', 1, '2026-01-21 06:58:51', '2026-01-21 12:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `salary_increments`
--

CREATE TABLE `salary_increments` (
  `id` int(11) UNSIGNED NOT NULL,
  `employee_id` int(11) UNSIGNED NOT NULL,
  `old_salary` decimal(10,2) NOT NULL,
  `new_salary` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) UNSIGNED DEFAULT NULL,
  `agent_commission_percent` decimal(5,2) DEFAULT 0.00,
  `agent_commission_amount` decimal(15,2) DEFAULT 0.00,
  `agent_commission_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `agent_commission_paid_at` datetime DEFAULT NULL,
  `quotation_id` int(11) UNSIGNED DEFAULT NULL,
  `sales_order_number` varchar(50) NOT NULL,
  `zoho_salesorder_id` varchar(100) DEFAULT NULL,
  `zoho_sync_status` enum('Pending','Synced','Failed') NOT NULL DEFAULT 'Pending',
  `order_date` date NOT NULL,
  `shipment_date` date DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `transport_name` varchar(150) DEFAULT NULL,
  `waybill_number` varchar(50) DEFAULT NULL,
  `status` enum('Draft','Confirmed','Closed','Void') NOT NULL DEFAULT 'Draft',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('Fixed','Percentage') NOT NULL DEFAULT 'Fixed',
  `shipping_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `roundoff_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_inter_state` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_items`
--

CREATE TABLE `sales_order_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `sales_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_returns`
--

CREATE TABLE `sales_returns` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `return_number` varchar(50) NOT NULL,
  `return_date` date NOT NULL,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Draft',
  `zoho_credit_note_id` varchar(100) DEFAULT NULL,
  `zoho_sync_status` varchar(20) DEFAULT 'Not Synced',
  `zoho_sync_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_inter_state` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_returns`
--

INSERT INTO `sales_returns` (`id`, `customer_id`, `invoice_id`, `return_number`, `return_date`, `subtotal`, `tax_amount`, `total_amount`, `reason`, `status`, `zoho_credit_note_id`, `zoho_sync_status`, `zoho_sync_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_inter_state`) VALUES
(1, 8, NULL, 'SRTN-202602-0001', '2026-02-23', 139750.00, 25122.50, 164872.50, '', 'Open', NULL, 'Not Synced', NULL, 1, NULL, '2026-02-23 15:21:41', '2026-02-23 15:21:41', 1),
(2, 8, NULL, 'SRTN-202602-0002', '2026-02-23', 139750.00, 25122.50, 164872.50, '', 'Open', NULL, 'Not Synced', NULL, 1, NULL, '2026-02-23 15:21:42', '2026-02-23 15:21:42', 1),
(3, 8, NULL, 'SRTN-202602-0003', '2026-02-23', 930.00, 167.40, 1097.40, '', 'Open', NULL, 'Not Synced', NULL, 1, NULL, '2026-02-23 15:25:23', '2026-02-23 15:25:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales_return_items`
--

CREATE TABLE `sales_return_items` (
  `id` int(11) NOT NULL,
  `sales_return_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT 0.00,
  `rate` decimal(15,2) DEFAULT 0.00,
  `tax_percentage` decimal(5,2) DEFAULT 0.00,
  `amount` decimal(15,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_return_items`
--

INSERT INTO `sales_return_items` (`id`, `sales_return_id`, `product_id`, `description`, `quantity`, `rate`, `tax_percentage`, `amount`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'KASAVU KERALA SAREE GOLD', 1.00, 250.00, 5.00, 262.50, '2026-02-23 15:21:41', '2026-02-23 15:21:41'),
(2, 1, 2, 'Plain Neli Border', 150.00, 930.00, 18.00, 164610.00, '2026-02-23 15:21:41', '2026-02-23 15:21:41'),
(3, 2, 1, 'KASAVU KERALA SAREE GOLD', 1.00, 250.00, 5.00, 262.50, '2026-02-23 15:21:42', '2026-02-23 15:21:42'),
(4, 2, 2, 'Plain Neli Border', 150.00, 930.00, 18.00, 164610.00, '2026-02-23 15:21:42', '2026-02-23 15:21:42'),
(5, 3, 2, 'Plain Neli Border', 1.00, 930.00, 18.00, 1097.40, '2026-02-23 15:25:23', '2026-02-23 15:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
('app_name', 'Rasi Handloom Textiles', '2026-01-21 07:02:07', '2026-02-01 07:15:05'),
('company_address_1', 'OLD NO-36F NEW NO-1/156 R.PUDUPALAYAM PO RASIPURAM TK', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_address_2', 'OLD NO-36F NEW NO-1/156 R.PUDUPALAYAM PO RASIPURAM TK', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_city', 'NAMAKKAL', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_country', '1', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_email', 'sgrgowthamraj63@gmail.com', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_gstin', 'Rasi Handloom Textiles', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_gst_type', 'Regular', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_mobile', '09626077333', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_phone', '09626077333', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_pincode', '637408', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_state', '1', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('company_whatsapp', '', '2026-01-22 12:44:55', '2026-02-01 07:15:05'),
('org_address', '3/18A, Krishnan Kovil Street, Rasipuram-637408', '2026-01-21 07:02:07', '2026-01-22 07:42:17'),
('org_contact', '+91-9626077333', '2026-01-21 07:02:07', '2026-01-22 07:42:17'),
('org_name', 'Rasi Handloom Textiles', '2026-01-21 07:02:07', '2026-02-01 07:15:05');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) UNSIGNED NOT NULL,
  `country_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `state_code` varchar(10) DEFAULT NULL,
  `gst_state_code` varchar(5) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `country_id`, `name`, `state_code`, `gst_state_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tamil Nadu', 'TN', '33', 'active', NULL, '2026-01-21 08:55:53'),
(2, 1, 'Kerala', 'KL', '32', 'active', NULL, '2026-01-21 08:55:53'),
(3, 1, 'Karnataka', 'KA', '29', 'active', NULL, '2026-01-21 08:55:53'),
(4, 1, 'Andhra Pradesh', 'AP', '28', 'active', NULL, '2026-01-21 08:55:53'),
(5, 1, 'Maharashtra', 'MH', '27', 'active', NULL, '2026-01-21 08:55:53'),
(6, 1, 'Delhi', 'DL', '07', 'active', NULL, '2026-01-21 08:55:53'),
(7, 1, 'Jammu and Kashmir', 'JK', '01', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(8, 1, 'Himachal Pradesh', 'HP', '02', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(9, 1, 'Punjab', 'PB', '03', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(10, 1, 'Chandigarh', 'CH', '04', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(11, 1, 'Uttarakhand', 'UT', '05', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(12, 1, 'Haryana', 'HR', '06', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(13, 1, 'Rajasthan', 'RJ', '08', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(14, 1, 'Uttar Pradesh', 'UP', '09', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(15, 1, 'Bihar', 'BR', '10', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(16, 1, 'Sikkim', 'SK', '11', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(17, 1, 'Arunachal Pradesh', 'AR', '12', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(18, 1, 'Nagaland', 'NL', '13', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(19, 1, 'Manipur', 'MN', '14', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(20, 1, 'Mizoram', 'MZ', '15', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(21, 1, 'Tripura', 'TR', '16', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(22, 1, 'Meghalaya', 'ML', '17', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(23, 1, 'Assam', 'AS', '18', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(24, 1, 'West Bengal', 'WB', '19', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(25, 1, 'Jharkhand', 'JH', '20', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(26, 1, 'Odisha', 'OR', '21', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(27, 1, 'Chhattisgarh', 'CT', '22', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(28, 1, 'Madhya Pradesh', 'MP', '23', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(29, 1, 'Gujarat', 'GJ', '24', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(30, 1, 'Dadra and Nagar Haveli and Daman and Diu', 'DN', '26', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(31, 1, 'Goa', 'GA', '30', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(32, 1, 'Lakshadweep', 'LD', '31', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(33, 1, 'Puducherry', 'PY', '34', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(34, 1, 'Andaman and Nicobar Islands', 'AN', '35', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(35, 1, 'Telangana', 'TG', '36', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53'),
(36, 1, 'Ladakh', 'LA', '37', 'active', '2026-01-21 08:55:53', '2026-01-21 08:55:53');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_item_id` int(11) UNSIGNED NOT NULL,
  `type` enum('in','out') NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`id`, `name`, `percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'GST 5%', 5.00, 'Active', '2026-01-22 07:46:49', '2026-01-22 07:46:49'),
(2, 'GST 18%', 18.00, 'Active', '2026-01-26 04:07:13', '2026-01-26 04:07:13');

-- --------------------------------------------------------

--
-- Table structure for table `transports`
--

CREATE TABLE `transports` (
  `id` int(11) UNSIGNED NOT NULL,
  `transport_name` varchar(100) NOT NULL,
  `transport_code` varchar(50) DEFAULT NULL,
  `branch` varchar(100) NOT NULL,
  `branch_address` text NOT NULL,
  `branch_phone_number` varchar(20) NOT NULL,
  `branch_gst_number` varchar(20) DEFAULT NULL,
  `state_id` int(11) UNSIGNED DEFAULT NULL,
  `country_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transports`
--

INSERT INTO `transports` (`id`, `transport_name`, `transport_code`, `branch`, `branch_address`, `branch_phone_number`, `branch_gst_number`, `state_id`, `country_id`, `created_at`, `updated_at`) VALUES
(1, 'AKR', 'AKR', 'Rasipuram', '18/3A, Krishnan Kovil Street, C.P Kannaiah Road\r\nOpp: Gopinath Engineering Works', '09626077333', '1516161', 1, 1, '2026-01-27 08:50:06', '2026-01-27 08:50:06'),
(2, 'MSS', 'MSS CODE', 'Rasipuram', '161263', '123155', '5163156', 1, 1, '2026-02-23 15:28:00', '2026-02-23 15:28:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) UNSIGNED NOT NULL,
  `employee_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `employee_id`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'sgrgowthamraj63@gmail.com', '$2y$10$SzRzoj7XRm8l7B/fA4D7pOYuQxxg8uO7.6bZqQRlV1fhxoyY0lELO', 1, NULL, 'active', NULL, '2026-01-21 04:53:24', '2026-01-21 04:53:24'),
(2, 'Rasi Textiles', 'test_user_1@example.com', '$2y$10$lufypcua0mol/bkLUw0nZelTPPdT8gBZXRTFCrAzftuGx57r.iroO', 1, 1, 'active', NULL, '2026-01-21 07:57:13', '2026-01-21 07:57:13');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) UNSIGNED NOT NULL,
  `zoho_contact_id` varchar(100) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `gst_type` enum('Regular','Composition','Unregistered') NOT NULL DEFAULT 'Unregistered',
  `gstin` varchar(15) DEFAULT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `balance_type` enum('Dr','Cr') DEFAULT 'Dr',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_no` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `zoho_contact_id`, `name`, `contact_person`, `email`, `website`, `phone`, `whatsapp_number`, `gst_type`, `gstin`, `pan_number`, `opening_balance`, `balance_type`, `status`, `created_at`, `updated_at`, `bank_name`, `bank_account_no`, `bank_ifsc`, `bank_branch`, `notes`, `zoho_sync_at`) VALUES
(4, '698964000001487247', 'A.Senthilraja', NULL, '', '', '0000000000', NULL, 'Regular', '33FAEPS5713R1Z2', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(5, '698964000002886252', 'ARS Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33AXIPS6969B1Z5', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(6, '698964000001716003', 'D. Subramaniam', NULL, '', '', '0000000000', NULL, 'Regular', '33DOZPS5744K1ZX', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(7, '698964000003963177', 'Dharamamuneeswarar Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33MENPS4173M1ZK', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(8, '698964000004948114', 'Emmanuel Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33AYOPJ1574E1ZH', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(9, '698964000001299059', 'G Thirumurugan Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33BCFPG2140F1ZB', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(10, '698964000001299099', 'Ganapathy Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33BPTPM1523B1Z7', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(11, '698964000001611155', 'Gokul Handloom Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33DZVPP4153A1ZA', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(12, '698964000000679127', 'H M', NULL, '', '', '0000000000', NULL, 'Unregistered', '', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(13, '698964000001465964', 'J Vision', NULL, '', '', '0000000000', NULL, 'Regular', '33AAOFJ4190Q1ZO', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(14, '698964000001493360', 'J.K & Co', NULL, '', '', '0000000000', NULL, 'Regular', '33BHXPJ0846C1ZD', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(15, '698964000002717150', 'JAI SHRI KRISHNA FASHION', NULL, '', '', '0000000000', NULL, 'Regular', '24ABCPJ6247P1ZC', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(16, '698964000000646001', 'Jaya Sudhan', NULL, '', '', '0000000000', NULL, 'Regular', '33AYOPJ1574E1ZH', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(17, '698964000000703080', 'JEGAN COTTON SAREES', NULL, '', '', '0000000000', NULL, 'Regular', '33BKEPN6987L1ZG', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(18, '698964000000229001', 'K K G TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33DIJPK3662N1ZU', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(19, '698964000001602134', 'K Kannan', NULL, '', '', '0000000000', NULL, 'Regular', '33DSIPK9354QIZW', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(20, '698964000000229033', 'K.K.19, THOPPUR HANDLOOM WEAVERS CO-OPERATIVE PRODUCTION AND SALE SOCIETY LTD', NULL, '', '', '0000000000', NULL, 'Regular', '33AAAAT7367G1ZE', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(21, '698964000000676001', 'K.M.S. TEX', NULL, '', '', '0000000000', NULL, 'Regular', '33CWRPS7485J1ZI', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(22, '698964000002867204', 'Karthika Cotton Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33LYQPS5733K1ZH', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(23, '698964000002717280', 'Kesaria Texco Private Limited', NULL, '', '', '0000000000', NULL, 'Regular', '24AAJCK1154H1ZQ', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(24, '698964000001126178', 'Kiruthika Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33DKXPM2922R1Z9', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(25, '698964000004470001', 'KSS TEX', NULL, '', '', '0000000000', NULL, 'Regular', '33HQTPS7684L1ZI', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(26, '698964000001258495', 'Mousika Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33HSAPS3688R1ZP', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(27, '698964000005869005', 'N.RAJAGOPAL', NULL, '', '', '0000000000', NULL, 'Regular', '33CLGPR5369J1ZL', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(28, '698964000001602188', 'Niranjan Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33BMTPA7350M1ZP', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(29, '698964000001087001', 'PVK Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33CUFPK0603J1Z0', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(30, '698964000001336001', 'RAJI TEX', NULL, '', '', '0000000000', NULL, 'Regular', '33ATUPG5410H1ZR', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(31, '698964000000656001', 'Ramesh Cloth Store', NULL, '', '', '0000000000', NULL, 'Regular', '33BDNPR6708M1ZX', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(32, '698964000001585158', 'Right Line Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33AVUPR6419D1Z9', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(33, '698964000000669001', 'S. Sivasankaran', NULL, '', '', '0000000000', NULL, 'Regular', '33ABKFS2479J1ZR', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(34, '698964000001496037', 'S.Dhanalashmi', NULL, '', '', '0000000000', NULL, 'Regular', '33HIKPS2293Q1ZA', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(35, '698964000001370001', 'S.Ganesan', NULL, '', '', '0000000000', NULL, 'Regular', '33AMJPG5320Q1ZW', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(36, '698964000001688095', 'S.Vallimayil', NULL, '', '', '0000000000', NULL, 'Regular', '33HIKPS2292R1Z9', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(37, '698964000002717235', 'Saboori Fashion', NULL, '', '', '0000000000', NULL, 'Regular', '24ASCPP6133D2Z2', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(38, '698964000002717718', 'Saravana Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33HTKPS4073M1ZY', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(39, '698964000003159581', 'Sashtika Handlooms', NULL, '', '', '0000000000', NULL, 'Regular', '33AYRPV9419R1Z3', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(40, '698964000001126017', 'SENTHIL TEXTILE', NULL, '', '', '0000000000', NULL, 'Regular', '33HKXPS1379J1Z6', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(41, '698964000005928060', 'SENTHILKUMAR TEXTILE MILLS PRIVATE LIMITED', NULL, '', '', '0000000000', NULL, 'Regular', '33AAJCS0497B1ZJ', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(42, '698964000001335001', 'SHANMUGAA TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33DJDPM6277A1ZE', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(43, '698964000003340232', 'SHREETEX ENGINEERS', NULL, '', '', '0000000000', NULL, 'Regular', '27ABQFS4345H1ZQ', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(44, '698964000001487101', 'Shunmuga Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33BJVPK4179B1Z1', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(45, '698964000003498689', 'Sivam Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33AMUPM7060N1ZC', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(46, '698964000001465061', 'Sivanantham Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33HLKPS9414Q1Z3', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(47, '698964000004784401', 'Sre Esha Textiles Pvt Limited', NULL, '', '', '0000000000', NULL, 'Regular', '33ABKCS1395N1ZS', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(48, '698964000001573077', 'Sree Muthulakshmi Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33ATWPK8129M1ZW', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(49, '698964000001390001', 'Sree Uma Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33AQIPN0778J1ZK', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(50, '698964000001126096', 'Sree Velan Sarees', NULL, '', '', '0000000000', NULL, 'Regular', '33AWHPV5024E1ZN', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(51, '698964000004882291', 'Sree Venkateswara Hardwares', NULL, '', '', '0000000000', NULL, 'Regular', '33AAIFS3579G1ZZ', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(52, '698964000000640001', 'Sri Jayamurugan Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33FOPPS7926B1ZK', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(53, '698964000001537137', 'Sri Kannimar Textiles', NULL, '', '', '0000000000', NULL, 'Regular', '33EALPM9823A1ZQ', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:21', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:21'),
(54, '698964000001716100', 'SRI SM & Co', NULL, '', '', '0000000000', NULL, 'Regular', '33CTJPS6344H1ZD', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:22', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:22'),
(55, '698964000005036003', 'Sri Vaari Package', NULL, '', '', '0000000000', NULL, 'Regular', '33AEFFS8144E1Z3', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:22', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:22'),
(56, '698964000001465122', 'SRI VENKATESWARA TEX', NULL, '', '', '0000000000', NULL, 'Regular', '33BJZPG8869E1Z1', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:23', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:23'),
(57, '698964000000982080', 'SRINIVASAN TEXTILES', NULL, '', '', '0000000000', NULL, 'Regular', '33BMGPS0163M2ZU', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:23', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:23'),
(58, '698964000001465007', 'Sunmathi Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33BCZPT2703B1ZL', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:23', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:23'),
(59, '698964000004187001', 'T.Raguram Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33ANQPT3023M1ZM', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:23', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:23'),
(60, '698964000000657001', 'TSM Exports', NULL, '', '', '0000000000', NULL, 'Regular', '33DCXPS6121N1ZT', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:23', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:23'),
(61, '698964000005002078', 'Viji Tex', NULL, '', '', '0000000000', NULL, 'Regular', '33AVQPV0442G1ZG', NULL, 0.00, 'Cr', 'active', '2026-02-01 12:04:44', '2026-02-01 12:39:23', NULL, NULL, NULL, NULL, NULL, '2026-02-01 12:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_credits`
--

CREATE TABLE `vendor_credits` (
  `id` int(11) UNSIGNED NOT NULL,
  `vendor_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Unused','Partial','Used') DEFAULT 'Unused',
  `used_amount` decimal(10,2) DEFAULT 0.00,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weavers`
--

CREATE TABLE `weavers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_proof` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weavers`
--

INSERT INTO `weavers` (`id`, `name`, `code`, `phone`, `address`, `address_proof`, `location`, `status`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Rasi Handloom Textiles Rasipuram', '', '09865073006', '2/9, KUNDUKALLAR THOTTAM', NULL, 'rtsdrfgsh', 'active', '2026-06-30 02:42:26', '2026-06-30 03:06:23', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `zoho_settings`
--

CREATE TABLE `zoho_settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `client_id` varchar(255) DEFAULT NULL,
  `client_secret` varchar(255) DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `organization_id` varchar(100) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `api_base_url` varchar(255) NOT NULL DEFAULT 'https://books.zoho.in/api/v3',
  `accounts_url` varchar(255) NOT NULL DEFAULT 'https://accounts.zoho.in/oauth/v2/token',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zoho_settings`
--

INSERT INTO `zoho_settings` (`id`, `client_id`, `client_secret`, `refresh_token`, `organization_id`, `access_token`, `token_expires_at`, `api_base_url`, `accounts_url`, `updated_at`) VALUES
(1, '1000.NN5YO26HD1FNEAVGWDJPZ6URDWD56P', '336cd1abbe33ba652a86269a2fbd70abb159a0951c', '1000.636a7d3566eb7ab7ea4dcb1c07687cf0.224cae4ce87e06ed53270d05a847445a', '648833159', '1000.2ebe19b1cebc54f96ae57bc18446c37b.4c5a5c3292a34999c9d9b89ee20eff6c', '2026-06-30 03:59:43', 'https://www.zohoapis.com/books/v3', 'https://accounts.zoho.com/oauth/v2/token', '2026-06-30 02:59:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_type_owner_id_address_type` (`owner_type`,`owner_id`,`address_type`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agents_state_id_foreign` (`state_id`),
  ADD KEY `agents_country_id_foreign` (`country_id`);

--
-- Indexes for table `agent_payments`
--
ALTER TABLE `agent_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `agent_payments_bank_account_id_foreign` (`bank_account_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `payment_date` (`payment_date`),
  ADD KEY `agent_payments_bank_transaction_id_foreign` (`bank_transaction_id`);

--
-- Indexes for table `agent_payment_items`
--
ALTER TABLE `agent_payment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_payment_id` (`agent_payment_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_number` (`account_number`);

--
-- Indexes for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_transactions_bank_account_id_foreign` (`bank_account_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `bills_vendor_id_foreign` (`vendor_id`),
  ADD KEY `bills_created_by_foreign` (`created_by`),
  ADD KEY `bills_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_items_bill_id_foreign` (`bill_id`),
  ADD KEY `bill_items_product_id_foreign` (`product_id`),
  ADD KEY `bill_items_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `calendar_reminders`
--
ALTER TABLE `calendar_reminders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_bank_account_id_foreign` (`bank_account_id`),
  ADD KEY `expenses_bank_transaction_id_foreign` (`bank_transaction_id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `invoices_customer_id_foreign` (`customer_id`),
  ADD KEY `invoices_created_by_foreign` (`created_by`),
  ADD KEY `invoices_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `invoice_payments_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_payments_customer_id_foreign` (`customer_id`),
  ADD KEY `invoice_payments_bank_account_id_foreign` (`bank_account_id`),
  ADD KEY `invoice_payments_bank_transaction_id_foreign` (`bank_transaction_id`);

--
-- Indexes for table `invoice_status_history`
--
ALTER TABLE `invoice_status_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loans_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loan_payments_loan_id_foreign` (`loan_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_slug` (`module_slug`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `payments_bill_id_foreign` (`bill_id`),
  ADD KEY `payments_vendor_id_foreign` (`vendor_id`),
  ADD KEY `payments_created_by_foreign` (`created_by`),
  ADD KEY `payments_updated_by_foreign` (`updated_by`),
  ADD KEY `payments_bank_account_id_foreign` (`bank_account_id`),
  ADD KEY `payments_bank_transaction_id_foreign` (`bank_transaction_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permissions_module_id_foreign` (`module_id`);

--
-- Indexes for table `production_agreements`
--
ALTER TABLE `production_agreements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `production_beams`
--
ALTER TABLE `production_beams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `beam_number` (`beam_number`);

--
-- Indexes for table `production_beam_ledger`
--
ALTER TABLE `production_beam_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beam_id` (`beam_id`);

--
-- Indexes for table `production_vendors`
--
ALTER TABLE `production_vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `production_yarns`
--
ALTER TABLE `production_yarns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `production_yarn_dyeing_dcs`
--
ALTER TABLE `production_yarn_dyeing_dcs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dc_number` (`dc_number`);

--
-- Indexes for table `production_yarn_dyeing_dc_items`
--
ALTER TABLE `production_yarn_dyeing_dc_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_dyeing_receipts`
--
ALTER TABLE `production_yarn_dyeing_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_dyeing_receipt_items`
--
ALTER TABLE `production_yarn_dyeing_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `dc_item_id` (`dc_item_id`);

--
-- Indexes for table `production_yarn_job_work_dcs`
--
ALTER TABLE `production_yarn_job_work_dcs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dc_number` (`dc_number`);

--
-- Indexes for table `production_yarn_job_work_dc_beams`
--
ALTER TABLE `production_yarn_job_work_dc_beams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_job_work_dc_color_ends`
--
ALTER TABLE `production_yarn_job_work_dc_color_ends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_job_work_dc_items`
--
ALTER TABLE `production_yarn_job_work_dc_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_job_work_receipts`
--
ALTER TABLE `production_yarn_job_work_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_job_work_receipt_items`
--
ALTER TABLE `production_yarn_job_work_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `dc_item_id` (`dc_item_id`);

--
-- Indexes for table `production_yarn_master`
--
ALTER TABLE `production_yarn_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `production_yarn_purchases`
--
ALTER TABLE `production_yarn_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `production_yarn_stock_movements`
--
ALTER TABLE `production_yarn_stock_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `production_yarn_twisting_dcs`
--
ALTER TABLE `production_yarn_twisting_dcs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dc_number` (`dc_number`);

--
-- Indexes for table `production_yarn_twisting_dc_items`
--
ALTER TABLE `production_yarn_twisting_dc_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_twisting_receipts`
--
ALTER TABLE `production_yarn_twisting_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_twisting_receipt_items`
--
ALTER TABLE `production_yarn_twisting_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `dc_item_id` (`dc_item_id`);

--
-- Indexes for table `production_yarn_warping_sizing_dcs`
--
ALTER TABLE `production_yarn_warping_sizing_dcs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dc_number` (`dc_number`);

--
-- Indexes for table `production_yarn_warping_sizing_dc_beams`
--
ALTER TABLE `production_yarn_warping_sizing_dc_beams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_warping_sizing_dc_color_ends`
--
ALTER TABLE `production_yarn_warping_sizing_dc_color_ends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_warping_sizing_dc_items`
--
ALTER TABLE `production_yarn_warping_sizing_dc_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_warping_sizing_receipts`
--
ALTER TABLE `production_yarn_warping_sizing_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_warping_sizing_receipt_items`
--
ALTER TABLE `production_yarn_warping_sizing_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `dc_item_id` (`dc_item_id`);

--
-- Indexes for table `production_yarn_weaving_dcs`
--
ALTER TABLE `production_yarn_weaving_dcs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dc_number` (`dc_number`);

--
-- Indexes for table `production_yarn_weaving_dc_items`
--
ALTER TABLE `production_yarn_weaving_dc_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_weaving_receipts`
--
ALTER TABLE `production_yarn_weaving_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `dc_id` (`dc_id`);

--
-- Indexes for table `production_yarn_weaving_receipt_items`
--
ALTER TABLE `production_yarn_weaving_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `dc_item_id` (`dc_item_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_items`
--
ALTER TABLE `product_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`),
  ADD KEY `quotations_customer_id_foreign` (`customer_id`),
  ADD KEY `quotations_created_by_foreign` (`created_by`),
  ADD KEY `quotations_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  ADD KEY `quotation_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `return_shipments`
--
ALTER TABLE `return_shipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD KEY `role_permissions_role_id_foreign` (`role_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salaries_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `salary_increments`
--
ALTER TABLE `salary_increments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_increments_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_order_number` (`sales_order_number`),
  ADD KEY `sales_orders_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_orders_quotation_id_foreign` (`quotation_id`),
  ADD KEY `sales_orders_created_by_foreign` (`created_by`),
  ADD KEY `sales_orders_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_order_items_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `sales_order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `states_country_id_foreign` (`country_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transports`
--
ALTER TABLE `transports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transports_state_id_foreign` (`state_id`),
  ADD KEY `transports_country_id_foreign` (`country_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_credits`
--
ALTER TABLE `vendor_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_credits_vendor_id_foreign` (`vendor_id`),
  ADD KEY `vendor_credits_created_by_foreign` (`created_by`);

--
-- Indexes for table `weavers`
--
ALTER TABLE `weavers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `zoho_settings`
--
ALTER TABLE `zoho_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=683;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agent_payments`
--
ALTER TABLE `agent_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `agent_payment_items`
--
ALTER TABLE `agent_payment_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `calendar_reminders`
--
ALTER TABLE `calendar_reminders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=351;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoice_status_history`
--
ALTER TABLE `invoice_status_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_payments`
--
ALTER TABLE `loan_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `production_agreements`
--
ALTER TABLE `production_agreements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `production_beams`
--
ALTER TABLE `production_beams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `production_beam_ledger`
--
ALTER TABLE `production_beam_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `production_vendors`
--
ALTER TABLE `production_vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `production_yarns`
--
ALTER TABLE `production_yarns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_dyeing_dcs`
--
ALTER TABLE `production_yarn_dyeing_dcs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `production_yarn_dyeing_dc_items`
--
ALTER TABLE `production_yarn_dyeing_dc_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `production_yarn_dyeing_receipts`
--
ALTER TABLE `production_yarn_dyeing_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `production_yarn_dyeing_receipt_items`
--
ALTER TABLE `production_yarn_dyeing_receipt_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `production_yarn_job_work_dcs`
--
ALTER TABLE `production_yarn_job_work_dcs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `production_yarn_job_work_dc_beams`
--
ALTER TABLE `production_yarn_job_work_dc_beams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `production_yarn_job_work_dc_color_ends`
--
ALTER TABLE `production_yarn_job_work_dc_color_ends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `production_yarn_job_work_dc_items`
--
ALTER TABLE `production_yarn_job_work_dc_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `production_yarn_job_work_receipts`
--
ALTER TABLE `production_yarn_job_work_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `production_yarn_job_work_receipt_items`
--
ALTER TABLE `production_yarn_job_work_receipt_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `production_yarn_master`
--
ALTER TABLE `production_yarn_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `production_yarn_purchases`
--
ALTER TABLE `production_yarn_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `production_yarn_stock_movements`
--
ALTER TABLE `production_yarn_stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `production_yarn_twisting_dcs`
--
ALTER TABLE `production_yarn_twisting_dcs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_twisting_dc_items`
--
ALTER TABLE `production_yarn_twisting_dc_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_twisting_receipts`
--
ALTER TABLE `production_yarn_twisting_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_twisting_receipt_items`
--
ALTER TABLE `production_yarn_twisting_receipt_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_warping_sizing_dcs`
--
ALTER TABLE `production_yarn_warping_sizing_dcs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `production_yarn_warping_sizing_dc_beams`
--
ALTER TABLE `production_yarn_warping_sizing_dc_beams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `production_yarn_warping_sizing_dc_color_ends`
--
ALTER TABLE `production_yarn_warping_sizing_dc_color_ends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `production_yarn_warping_sizing_dc_items`
--
ALTER TABLE `production_yarn_warping_sizing_dc_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `production_yarn_warping_sizing_receipts`
--
ALTER TABLE `production_yarn_warping_sizing_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `production_yarn_warping_sizing_receipt_items`
--
ALTER TABLE `production_yarn_warping_sizing_receipt_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `production_yarn_weaving_dcs`
--
ALTER TABLE `production_yarn_weaving_dcs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_weaving_dc_items`
--
ALTER TABLE `production_yarn_weaving_dc_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_weaving_receipts`
--
ALTER TABLE `production_yarn_weaving_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_yarn_weaving_receipt_items`
--
ALTER TABLE `production_yarn_weaving_receipt_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_items`
--
ALTER TABLE `product_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_shipments`
--
ALTER TABLE `return_shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `salary_increments`
--
ALTER TABLE `salary_increments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_returns`
--
ALTER TABLE `sales_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transports`
--
ALTER TABLE `transports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `vendor_credits`
--
ALTER TABLE `vendor_credits`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `weavers`
--
ALTER TABLE `weavers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zoho_settings`
--
ALTER TABLE `zoho_settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `agents_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `agent_payments`
--
ALTER TABLE `agent_payments`
  ADD CONSTRAINT `agent_payments_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `agent_payments_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `agent_payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `agent_payment_items`
--
ALTER TABLE `agent_payment_items`
  ADD CONSTRAINT `agent_payment_items_agent_payment_id_foreign` FOREIGN KEY (`agent_payment_id`) REFERENCES `agent_payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `agent_payment_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD CONSTRAINT `bank_transactions_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD CONSTRAINT `bill_items_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bill_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bill_items_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `expenses_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoices_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD CONSTRAINT `invoice_payments_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `invoice_payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD CONSTRAINT `ledger_entries_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD CONSTRAINT `loan_payments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `production_beam_ledger`
--
ALTER TABLE `production_beam_ledger`
  ADD CONSTRAINT `production_beam_ledger_ibfk_1` FOREIGN KEY (`beam_id`) REFERENCES `production_beams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_dyeing_dc_items`
--
ALTER TABLE `production_yarn_dyeing_dc_items`
  ADD CONSTRAINT `production_yarn_dyeing_dc_items_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_dyeing_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_dyeing_receipts`
--
ALTER TABLE `production_yarn_dyeing_receipts`
  ADD CONSTRAINT `production_yarn_dyeing_receipts_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_dyeing_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_dyeing_receipt_items`
--
ALTER TABLE `production_yarn_dyeing_receipt_items`
  ADD CONSTRAINT `production_yarn_dyeing_receipt_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `production_yarn_dyeing_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_yarn_dyeing_receipt_items_ibfk_2` FOREIGN KEY (`dc_item_id`) REFERENCES `production_yarn_dyeing_dc_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_job_work_dc_beams`
--
ALTER TABLE `production_yarn_job_work_dc_beams`
  ADD CONSTRAINT `production_yarn_job_work_dc_beams_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_job_work_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_job_work_dc_color_ends`
--
ALTER TABLE `production_yarn_job_work_dc_color_ends`
  ADD CONSTRAINT `production_yarn_job_work_dc_color_ends_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_job_work_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_job_work_dc_items`
--
ALTER TABLE `production_yarn_job_work_dc_items`
  ADD CONSTRAINT `production_yarn_job_work_dc_items_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_job_work_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_job_work_receipts`
--
ALTER TABLE `production_yarn_job_work_receipts`
  ADD CONSTRAINT `production_yarn_job_work_receipts_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_job_work_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_job_work_receipt_items`
--
ALTER TABLE `production_yarn_job_work_receipt_items`
  ADD CONSTRAINT `production_yarn_job_work_receipt_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `production_yarn_job_work_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_yarn_job_work_receipt_items_ibfk_2` FOREIGN KEY (`dc_item_id`) REFERENCES `production_yarn_job_work_dc_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_twisting_dc_items`
--
ALTER TABLE `production_yarn_twisting_dc_items`
  ADD CONSTRAINT `production_yarn_twisting_dc_items_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_twisting_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_twisting_receipts`
--
ALTER TABLE `production_yarn_twisting_receipts`
  ADD CONSTRAINT `production_yarn_twisting_receipts_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_twisting_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_twisting_receipt_items`
--
ALTER TABLE `production_yarn_twisting_receipt_items`
  ADD CONSTRAINT `production_yarn_twisting_receipt_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `production_yarn_twisting_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_yarn_twisting_receipt_items_ibfk_2` FOREIGN KEY (`dc_item_id`) REFERENCES `production_yarn_twisting_dc_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_warping_sizing_dc_beams`
--
ALTER TABLE `production_yarn_warping_sizing_dc_beams`
  ADD CONSTRAINT `production_yarn_warping_sizing_dc_beams_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_warping_sizing_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_warping_sizing_dc_color_ends`
--
ALTER TABLE `production_yarn_warping_sizing_dc_color_ends`
  ADD CONSTRAINT `production_yarn_warping_sizing_dc_color_ends_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_warping_sizing_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_warping_sizing_dc_items`
--
ALTER TABLE `production_yarn_warping_sizing_dc_items`
  ADD CONSTRAINT `production_yarn_warping_sizing_dc_items_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_warping_sizing_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_warping_sizing_receipts`
--
ALTER TABLE `production_yarn_warping_sizing_receipts`
  ADD CONSTRAINT `production_yarn_warping_sizing_receipts_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_warping_sizing_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_warping_sizing_receipt_items`
--
ALTER TABLE `production_yarn_warping_sizing_receipt_items`
  ADD CONSTRAINT `production_yarn_warping_sizing_receipt_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `production_yarn_warping_sizing_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_yarn_warping_sizing_receipt_items_ibfk_2` FOREIGN KEY (`dc_item_id`) REFERENCES `production_yarn_warping_sizing_dc_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_weaving_dc_items`
--
ALTER TABLE `production_yarn_weaving_dc_items`
  ADD CONSTRAINT `production_yarn_weaving_dc_items_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_weaving_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_weaving_receipts`
--
ALTER TABLE `production_yarn_weaving_receipts`
  ADD CONSTRAINT `production_yarn_weaving_receipts_ibfk_1` FOREIGN KEY (`dc_id`) REFERENCES `production_yarn_weaving_dcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_yarn_weaving_receipt_items`
--
ALTER TABLE `production_yarn_weaving_receipt_items`
  ADD CONSTRAINT `production_yarn_weaving_receipt_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `production_yarn_weaving_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_yarn_weaving_receipt_items_ibfk_2` FOREIGN KEY (`dc_item_id`) REFERENCES `production_yarn_weaving_dc_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `salaries`
--
ALTER TABLE `salaries`
  ADD CONSTRAINT `salaries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `salary_increments`
--
ALTER TABLE `salary_increments`
  ADD CONSTRAINT `salary_increments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `sales_orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD CONSTRAINT `sales_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `sales_order_items_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transports`
--
ALTER TABLE `transports`
  ADD CONSTRAINT `transports_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `transports_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `vendor_credits`
--
ALTER TABLE `vendor_credits`
  ADD CONSTRAINT `vendor_credits_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `vendor_credits_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
