-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2026 at 01:55 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) UNSIGNED NOT NULL,
  `agent_name` varchar(100) NOT NULL,
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
(5, 1, '2026-01-18', '09:55:00', '18:10:00', 8.25, 0.00, 0.00, 1, 'Present', 1.0, '2026-01-21 06:53:54', '2026-01-21 06:53:54');

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
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `bank_name`, `account_number`, `ifsc_code`, `branch_name`, `current_balance`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CITY UNION BANK', '510909010007385', 'CUBI0000509', 'Rasipuram', 15000.00, 'active', '2026-01-21 11:19:21', '2026-01-21 11:19:45');

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
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_transactions`
--

INSERT INTO `bank_transactions` (`id`, `bank_account_id`, `transaction_date`, `description`, `type`, `amount`, `reference_number`, `balance_after`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-01-21', 'INVIOEC', 'credit', 15000.00, '123546879', 15000.00, '2026-01-21 11:19:45', '2026-01-21 11:19:45');

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
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `zoho_sync_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(54, '2024-01-21-211000', 'App\\Database\\Migrations\\RegisterZohoSettingsModule', 'default', 'App', 1768998353, 26);

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
(27, 'Zoho Integration', 'zoho_settings', 'active', '2026-01-21 12:25:53', '2026-01-21 12:25:53');

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
(90, 'zoho.sync', 'Sync with Zoho', 27, NULL, NULL);

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
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 18),
(1, 11),
(1, 12),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 13),
(1, 26),
(1, 28),
(1, 29),
(1, 27),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
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
(1, 72),
(1, 73),
(1, 74),
(1, 75),
(1, 76),
(1, 77),
(1, 78),
(1, 79),
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
(1, 90);

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
(9, 1, '2026-01-01', 7000.00, '0', '225.8064516129', 6774.19, 0, NULL, 1, '2026-01-21 06:58:51', '2026-01-21 08:12:35');

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
('app_name', 'Rasi Handloom Textiles', '2026-01-21 07:02:07', '2026-01-21 12:06:46'),
('org_address', '3/18A, Krishnan Kovil Street, Rasipuram-637408', '2026-01-21 07:02:07', '2026-01-21 12:06:46'),
('org_contact', '+91-9626077333', '2026-01-21 07:02:07', '2026-01-21 12:06:46'),
('org_name', 'Rasi Handloom Textiles', '2026-01-21 07:02:07', '2026-01-21 12:06:46');

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
(1, NULL, NULL, NULL, NULL, NULL, NULL, 'https://books.zoho.in/api/v3', 'https://accounts.zoho.in/oauth/v2/token', NULL);

--
-- Indexes for dumped tables
--

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
  ADD KEY `expenses_bank_account_id_foreign` (`bank_account_id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permissions_module_id_foreign` (`module_id`);

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
-- Indexes for table `zoho_settings`
--
ALTER TABLE `zoho_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

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
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `transports`
--
ALTER TABLE `transports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

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
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
