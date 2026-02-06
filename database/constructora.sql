-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 06-02-2026 a las 19:09:32
-- Versión del servidor: 5.7.44
-- Versión de PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `constructora`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `advertisement`
--

CREATE TABLE `advertisement` (
  `advertisement_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `status` tinyint(1) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `company`
--

INSERT INTO `company` (`company_id`, `name`, `phone`, `address`, `contact_name`, `contact_email`, `created_at`, `updated_at`) VALUES
(1, 'CONTRACTOR, INC', '(618)985-7850', '', NULL, NULL, '2024-04-13 19:10:40', '2024-10-18 23:59:37'),
(3, 'Disrupsoft', '(653)289-6532', '', NULL, NULL, '2024-04-24 04:23:31', '2024-10-11 19:55:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company_contact`
--

CREATE TABLE `company_contact` (
  `contact_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `notes` text,
  `company_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `company_contact`
--

INSERT INTO `company_contact` (`contact_id`, `name`, `email`, `phone`, `role`, `notes`, `company_id`) VALUES
(1, 'Dan Schamerhorn', 'merhorn@earsnel.com', '(618)985-7850', 'Senior', ' test', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concrete_class`
--

CREATE TABLE `concrete_class` (
  `concrete_class_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concrete_vendor`
--

CREATE TABLE `concrete_vendor` (
  `vendor_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text,
  `phone` varchar(50) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concrete_vendor_contact`
--

CREATE TABLE `concrete_vendor_contact` (
  `contact_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `notes` text,
  `vendor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `county`
--

CREATE TABLE `county` (
  `county_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking`
--

CREATE TABLE `data_tracking` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `station_number` varchar(255) DEFAULT NULL,
  `measured_by` varchar(255) DEFAULT NULL,
  `conc_vendor` varchar(255) DEFAULT NULL,
  `crew_lead` varchar(255) DEFAULT NULL,
  `notes` text,
  `other_materials` text,
  `total_conc_used` decimal(18,2) DEFAULT NULL,
  `conc_price` decimal(18,2) DEFAULT NULL,
  `total_stamps` decimal(18,2) DEFAULT NULL,
  `total_people` decimal(18,2) DEFAULT NULL,
  `overhead_price` decimal(18,2) DEFAULT NULL,
  `color_used` decimal(18,2) DEFAULT NULL,
  `color_price` decimal(18,2) DEFAULT NULL,
  `pending` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `inspector_id` int(11) DEFAULT NULL,
  `overhead_price_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `data_tracking`
--

INSERT INTO `data_tracking` (`id`, `date`, `station_number`, `measured_by`, `conc_vendor`, `crew_lead`, `notes`, `other_materials`, `total_conc_used`, `conc_price`, `total_stamps`, `total_people`, `overhead_price`, `color_used`, `color_price`, `pending`, `created_at`, `updated_at`, `project_id`, `inspector_id`, `overhead_price_id`) VALUES
(3, '2024-08-31', '45453', 'Marcel', NULL, NULL, '', NULL, NULL, NULL, 0.00, 2.00, 100.00, 2.00, 100.00, 0, '2024-06-23 21:07:27', '2025-02-18 22:30:47', 2, NULL, 1),
(4, '2024-06-11', '435435', 'Marcel', NULL, NULL, '', '', NULL, NULL, 0.00, 4.00, 0.00, 0.00, 0.00, 0, '2024-06-23 21:16:44', '2025-01-24 20:06:43', 3, NULL, NULL),
(5, '2025-02-19', 'rtertert', '', NULL, NULL, '', NULL, NULL, NULL, 0.00, 1.00, 100.00, 0.00, 0.00, 0, '2025-02-19 23:15:27', '2025-02-21 17:41:52', 2, 1, 1),
(6, '2025-03-01', '', '', NULL, NULL, '', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0, '2025-03-01 13:25:07', NULL, 1, NULL, NULL),
(7, '2025-03-02', '', '', NULL, NULL, '', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0, '2025-03-01 13:43:40', NULL, 1, NULL, NULL),
(11, '2025-02-28', '', '', NULL, NULL, '', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0, '2025-03-02 14:15:40', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_attachment`
--

CREATE TABLE `data_tracking_attachment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `data_tracking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_conc_vendor`
--

CREATE TABLE `data_tracking_conc_vendor` (
  `id` int(11) NOT NULL,
  `conc_vendor` varchar(255) DEFAULT NULL,
  `total_conc_used` decimal(18,2) DEFAULT NULL,
  `conc_price` decimal(18,2) DEFAULT NULL,
  `data_tracking_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `data_tracking_conc_vendor`
--

INSERT INTO `data_tracking_conc_vendor` (`id`, `conc_vendor`, `total_conc_used`, `conc_price`, `data_tracking_id`, `vendor_id`) VALUES
(1, 'Disrupsoft', 5.00, 10.00, 4, NULL),
(2, 'Disrupsoft', 100.00, 100.00, 3, NULL),
(3, 'DGGG', 25.00, 100.00, 5, NULL),
(4, 'DIO', 50.00, 100.00, 5, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_item`
--

CREATE TABLE `data_tracking_item` (
  `id` int(11) NOT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `notes` text,
  `data_tracking_id` int(11) DEFAULT NULL,
  `project_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `data_tracking_item`
--

INSERT INTO `data_tracking_item` (`id`, `quantity`, `price`, `notes`, `data_tracking_id`, `project_item_id`) VALUES
(8, 400.000000, 160.00, 'test', 3, 11),
(9, 500.000000, 200.00, 'otra test', 3, 12),
(11, 50.000000, 16.50, NULL, 4, 1),
(13, 10.000000, 300.00, 'test pending', 3, 13),
(15, 10.000000, 100.00, '', 3, 16),
(16, 10.000000, 160.00, '', 5, 11),
(17, 10.000000, 200.00, '', 5, 12),
(18, 10.000000, 300.00, '', 5, 13),
(19, 10.000000, 100.00, '', 5, 16),
(20, 10.000000, 100.00, '', 5, 17),
(21, 10.000000, 100.00, '', 6, 18),
(22, 10.000000, 100.00, '', 7, 18),
(26, 10.000000, 100.00, '', 11, 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_labor`
--

CREATE TABLE `data_tracking_labor` (
  `id` int(11) NOT NULL,
  `hours` decimal(18,2) DEFAULT NULL,
  `hourly_rate` decimal(18,2) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `data_tracking_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `subcontractor_employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `data_tracking_labor`
--

INSERT INTO `data_tracking_labor` (`id`, `hours`, `hourly_rate`, `role`, `color`, `data_tracking_id`, `employee_id`, `subcontractor_employee_id`) VALUES
(1, 1.00, 56.00, 'Jefe', NULL, 4, 1, NULL),
(3, 5.00, 70.00, 'Jefe', NULL, 3, 2, NULL),
(4, 10.00, 56.00, 'Constructor', NULL, 3, 1, NULL),
(5, 4.00, 70.00, 'Asistent', NULL, 4, 2, NULL),
(6, 2.00, 2.00, 'RRHH', NULL, 4, 4, NULL),
(7, 2.00, 2.00, 'Ayudante', NULL, 4, 5, NULL),
(8, 5.00, 70.00, 'Developer', NULL, 5, 2, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_material`
--

CREATE TABLE `data_tracking_material` (
  `id` int(11) NOT NULL,
  `quantity` decimal(18,2) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `data_tracking_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `data_tracking_material`
--

INSERT INTO `data_tracking_material` (`id`, `quantity`, `price`, `data_tracking_id`, `material_id`) VALUES
(1, 1.00, 100.00, 4, 1),
(3, 5.00, 5000.00, 3, 1),
(4, 10.00, 500.00, 3, 2),
(5, 5.00, 100.00, 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_subcontract`
--

CREATE TABLE `data_tracking_subcontract` (
  `id` int(11) NOT NULL,
  `quantity` decimal(18,2) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `notes` text,
  `data_tracking_id` int(11) DEFAULT NULL,
  `subcontractor_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `project_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `data_tracking_subcontract`
--

INSERT INTO `data_tracking_subcontract` (`id`, `quantity`, `price`, `notes`, `data_tracking_id`, `subcontractor_id`, `item_id`, `project_item_id`) VALUES
(6, 10.00, 100.00, '', 3, NULL, 11, NULL),
(7, 10.00, 50.00, '', 5, NULL, 12, NULL),
(8, 10.00, 60.00, '', 5, NULL, 6, NULL),
(9, 10.00, 100.00, '', 5, NULL, 7, NULL),
(10, 10.00, 50.00, '', 5, NULL, 11, NULL),
(11, 10.00, 100.00, '', 5, NULL, 8, NULL),
(12, 10.00, 100.00, '', 6, NULL, 11, NULL),
(13, 10.00, 100.00, '', 7, NULL, 11, NULL),
(17, 10.00, 100.00, '', 11, NULL, 11, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `district`
--

CREATE TABLE `district` (
  `district_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hourly_rate` float(8,2) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `address` text,
  `phone` varchar(50) DEFAULT NULL,
  `cert_rate_type` varchar(255) DEFAULT NULL,
  `social_security_number` varchar(50) DEFAULT NULL,
  `apprentice_percentage` decimal(18,2) DEFAULT NULL,
  `work_code` varchar(50) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `race_id` int(11) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `date_terminated` date DEFAULT NULL,
  `reason_terminated` varchar(255) DEFAULT NULL,
  `time_card_notes` varchar(255) DEFAULT NULL,
  `regular_rate_per_hour` decimal(18,2) DEFAULT NULL,
  `overtime_rate_per_hour` decimal(18,2) DEFAULT NULL,
  `special_rate_per_hour` decimal(18,2) DEFAULT NULL,
  `trade_licenses_info` text,
  `notes` text,
  `is_osha_10_certified` tinyint(1) DEFAULT NULL,
  `is_veteran` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `employee`
--

INSERT INTO `employee` (`employee_id`, `name`, `hourly_rate`, `position`, `role_id`, `color`, `address`, `phone`, `cert_rate_type`, `social_security_number`, `apprentice_percentage`, `work_code`, `gender`, `race_id`, `date_hired`, `date_terminated`, `reason_terminated`, `time_card_notes`, `regular_rate_per_hour`, `overtime_rate_per_hour`, `special_rate_per_hour`, `trade_licenses_info`, `notes`, `is_osha_10_certified`, `is_veteran`, `status`) VALUES
(1, 'Marcel Curbelo Carmona', 56.00, 'Gerente', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, 'Andres Iglesias', 70.00, 'Developer', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(3, 'Geydis Marquez', 5.00, 'Jefe', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(4, 'Luis Miguel', 2.00, 'RRHH', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(5, 'Brian Marcel', 2.00, 'Ayudante', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee_role`
--

CREATE TABLE `employee_role` (
  `role_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `employee_role`
--

INSERT INTO `employee_role` (`role_id`, `description`, `status`) VALUES
(1, 'Gerente', 1),
(2, 'Developer', 1),
(3, 'Jefe', 1),
(4, 'RRHH', 1),
(5, 'Ayudante', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equation`
--

CREATE TABLE `equation` (
  `equation_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `equation` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `equation`
--

INSERT INTO `equation` (`equation_id`, `description`, `equation`, `status`) VALUES
(2, 'SW, 4 IN, SF', '(X*4)/324', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estimate`
--

CREATE TABLE `estimate` (
  `estimate_id` int(11) NOT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `bid_deadline` datetime DEFAULT NULL,
  `county` varchar(255) DEFAULT NULL,
  `priority` varchar(50) DEFAULT NULL,
  `bid_no` varchar(50) DEFAULT NULL,
  `work_hour` varchar(50) DEFAULT NULL,
  `phone` text,
  `email` text,
  `job_walk` datetime DEFAULT NULL,
  `rfi_due_date` datetime DEFAULT NULL,
  `project_start` datetime DEFAULT NULL,
  `project_end` datetime DEFAULT NULL,
  `submitted_date` datetime DEFAULT NULL,
  `awarded_date` datetime DEFAULT NULL,
  `lost_date` datetime DEFAULT NULL,
  `location` text,
  `sector` varchar(50) DEFAULT NULL,
  `bid_description` text,
  `bid_instructions` text,
  `plan_link` text,
  `quote_received` tinyint(1) DEFAULT NULL,
  `project_stage_id` int(11) DEFAULT NULL,
  `proposal_type_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `county_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `plan_downloading_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estimate_bid_deadline`
--

CREATE TABLE `estimate_bid_deadline` (
  `id` int(11) NOT NULL,
  `bid_deadline` datetime DEFAULT NULL,
  `tag` varchar(50) DEFAULT NULL,
  `address` text,
  `estimate_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estimate_company`
--

CREATE TABLE `estimate_company` (
  `id` int(11) NOT NULL,
  `estimate_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estimate_estimator`
--

CREATE TABLE `estimate_estimator` (
  `id` int(11) NOT NULL,
  `estimate_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estimate_project_type`
--

CREATE TABLE `estimate_project_type` (
  `id` int(11) NOT NULL,
  `estimate_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estimate_quote`
--

CREATE TABLE `estimate_quote` (
  `id` int(11) NOT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `price` decimal(18,6) DEFAULT NULL,
  `yield_calculation` varchar(50) DEFAULT NULL,
  `estimate_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `equation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `function`
--

CREATE TABLE `function` (
  `function_id` int(11) NOT NULL,
  `url` varchar(50) DEFAULT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `function`
--

INSERT INTO `function` (`function_id`, `url`, `description`) VALUES
(1, 'home', 'Home'),
(2, 'rol', 'Roles'),
(3, 'users', 'Users'),
(4, 'log', 'Logs'),
(5, 'unit', 'Unit of Measurement'),
(6, 'item', 'Items'),
(7, 'inspectors', 'Inspectors'),
(8, 'company', 'Companies'),
(9, 'projects', 'Projects'),
(10, 'data_tracking', 'Data Tracking'),
(11, 'invoice', 'Invoices'),
(12, 'notification', 'Notifications'),
(13, 'equation', 'Equations'),
(14, 'employees', 'Employees'),
(15, 'materials', 'Materials'),
(16, 'overhead', 'Overhead Price'),
(17, 'advertisement', 'Advertisements'),
(18, 'subcontractor', 'Subcontractor'),
(19, 'reporte_subcontractor', 'Subcontractors'),
(20, 'reporte_employee', 'Employees'),
(21, 'conc_vendor', 'Concrete Vendors'),
(22, 'schedule', 'Schedule Document'),
(23, 'reminder', 'Reminders'),
(24, 'project_stage', 'Project Stages'),
(25, 'project_type', 'Project Type'),
(26, 'proposal_type', 'Proposal Type'),
(27, 'plan_status', 'Plan Status'),
(28, 'district', 'District'),
(29, 'estimate', 'Estimates'),
(30, 'plan_downloading', 'Plans Downloading'),
(31, 'holiday', 'Holidays'),
(32, 'county', 'County'),
(33, 'payment', 'Payments'),
(34, 'race', 'Races'),
(35, 'employee_rrhh', 'Employees'),
(36, 'concrete_class', 'Concrete Class'),
(37, 'employee_role', 'Employee Role');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `holiday`
--

CREATE TABLE `holiday` (
  `holiday_id` int(11) NOT NULL,
  `day` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inspector`
--

CREATE TABLE `inspector` (
  `inspector_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `inspector`
--

INSERT INTO `inspector` (`inspector_id`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Carlos Magill', '(678)558-2565', 'gill@arersnl.com', 1, '2024-04-13 00:03:19', '2024-04-13 00:03:50'),
(3, 'Marcel Curbelo Carmona', '(349)995-0162', 'cyborgmnk@gmail.com', 1, '2024-05-15 21:57:30', NULL),
(4, 'Cristián Gwinner', '(025)940-5185', 'cgwinner@canteras.cl', 1, '2024-05-18 16:08:15', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice`
--

CREATE TABLE `invoice` (
  `invoice_id` int(11) NOT NULL,
  `number` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text,
  `paid` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `txn_id` varchar(255) DEFAULT NULL,
  `edit_sequence` varchar(255) DEFAULT NULL,
  `bon_quantity_requested` decimal(10,6) DEFAULT NULL COMMENT 'Bon Quantity solicitado (0 a 1)',
  `bon_quantity` decimal(10,6) DEFAULT NULL COMMENT 'Bon Quantity aplicado (cap por acumulado)',
  `bon_amount` decimal(18,2) DEFAULT NULL COMMENT 'Bon General * Bon Quantity aplicado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `invoice`
--

INSERT INTO `invoice` (`invoice_id`, `number`, `start_date`, `end_date`, `notes`, `paid`, `created_at`, `updated_at`, `project_id`, `txn_id`, `edit_sequence`, `bon_quantity_requested`, `bon_quantity`, `bon_amount`) VALUES
(9, '1', '2025-02-01', '2025-02-28', '', 1, '2025-02-24 02:01:04', '2025-02-24 02:11:48', 2, NULL, NULL, NULL, NULL, NULL),
(12, '2', '2025-03-01', '2025-03-31', '', 1, '2025-02-24 02:22:13', '2025-02-24 02:25:18', 2, NULL, NULL, NULL, NULL, NULL),
(13, '3', '2025-04-01', '2025-04-30', '', 1, '2025-02-24 02:27:06', '2025-02-24 02:27:25', 2, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_attachment`
--

CREATE TABLE `invoice_attachment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_item`
--

CREATE TABLE `invoice_item` (
  `id` int(11) NOT NULL,
  `quantity_from_previous` decimal(18,6) DEFAULT NULL,
  `unpaid_from_previous` decimal(18,6) DEFAULT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `paid_qty` decimal(18,6) DEFAULT NULL,
  `unpaid_qty` decimal(18,6) DEFAULT NULL,
  `quantity_brought_forward` decimal(18,6) DEFAULT NULL,
  `paid_amount` decimal(18,6) DEFAULT NULL,
  `paid_amount_total` decimal(18,6) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `project_item_id` int(11) DEFAULT NULL,
  `txn_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `invoice_item`
--

INSERT INTO `invoice_item` (`id`, `quantity_from_previous`, `unpaid_from_previous`, `quantity`, `price`, `paid_qty`, `unpaid_qty`, `quantity_brought_forward`, `paid_amount`, `paid_amount_total`, `invoice_id`, `project_item_id`, `txn_id`) VALUES
(37, 0.000000, 0.000000, 10.000000, 160.00, 4.000000, NULL, NULL, 640.000000, 640.000000, 9, 11, NULL),
(38, 0.000000, 0.000000, 10.000000, 200.00, 4.000000, NULL, NULL, 800.000000, 800.000000, 9, 12, NULL),
(39, 0.000000, 0.000000, 10.000000, 300.00, 4.000000, NULL, NULL, 1200.000000, 1200.000000, 9, 13, NULL),
(40, 0.000000, 0.000000, 10.000000, 100.00, 4.000000, NULL, NULL, 400.000000, 400.000000, 9, 16, NULL),
(41, 0.000000, 0.000000, 10.000000, 100.00, 4.000000, NULL, NULL, 400.000000, 400.000000, 9, 17, NULL),
(52, 10.000000, 3.000000, 0.000000, 160.00, 1.000000, NULL, NULL, 160.000000, 800.000000, 12, 11, NULL),
(53, 10.000000, 3.000000, 0.000000, 200.00, 1.000000, NULL, NULL, 200.000000, 1000.000000, 12, 12, NULL),
(54, 10.000000, 3.000000, 0.000000, 300.00, 1.000000, NULL, NULL, 300.000000, 1500.000000, 12, 13, NULL),
(55, 10.000000, 3.000000, 0.000000, 100.00, 1.000000, NULL, NULL, 100.000000, 500.000000, 12, 16, NULL),
(56, 10.000000, 3.000000, 0.000000, 100.00, 1.000000, NULL, NULL, 100.000000, 500.000000, 12, 17, NULL),
(57, 10.000000, 5.000000, 0.000000, 160.00, 5.000000, NULL, NULL, 800.000000, 1600.000000, 13, 11, NULL),
(58, 10.000000, 5.000000, 0.000000, 200.00, 5.000000, NULL, NULL, 1000.000000, 2000.000000, 13, 12, NULL),
(59, 10.000000, 5.000000, 0.000000, 300.00, 5.000000, NULL, NULL, 1500.000000, 3000.000000, 13, 13, NULL),
(60, 10.000000, 5.000000, 0.000000, 100.00, 5.000000, NULL, NULL, 500.000000, 1000.000000, 13, 16, NULL),
(61, 10.000000, 5.000000, 0.000000, 100.00, 5.000000, NULL, NULL, 500.000000, 1000.000000, 13, 17, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_item_notes`
--

CREATE TABLE `invoice_item_notes` (
  `id` int(11) NOT NULL,
  `notes` text,
  `date` date DEFAULT NULL,
  `invoice_item_id` int(11) DEFAULT NULL,
  `override_unpaid_qty` decimal(18,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_notes`
--

CREATE TABLE `invoice_notes` (
  `id` int(11) NOT NULL,
  `notes` text,
  `date` date DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` float(8,2) DEFAULT NULL,
  `yield_calculation` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `bone` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `equation_id` int(11) DEFAULT NULL,
  `txn_id` varchar(255) DEFAULT NULL,
  `edit_sequence` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `item`
--

INSERT INTO `item` (`item_id`, `name`, `description`, `price`, `yield_calculation`, `status`, `bone`, `created_at`, `updated_at`, `unit_id`, `equation_id`, `txn_id`, `edit_sequence`) VALUES
(1, 'CONC MEDIAN 4IN', 'CONC MEDIAN 4IN', 29.00, NULL, 1, NULL, '2024-04-12 20:18:17', NULL, 1, NULL, NULL, NULL),
(2, 'CONC MEDIAN 6IN', 'CONC MEDIAN 6IN', 70.00, NULL, 1, NULL, '2024-04-12 20:18:40', NULL, 1, NULL, NULL, NULL),
(3, 'CONCRETE V GUTTER', 'CONCRETE V GUTTER', 25.00, NULL, 1, NULL, '2024-04-12 20:19:00', NULL, 2, NULL, NULL, NULL),
(4, 'CONC VALLEY GUTTER 6IN', 'CONC VALLEY GUTTER 6IN', 58.00, NULL, 1, NULL, '2024-04-12 20:19:26', NULL, 1, NULL, NULL, NULL),
(5, 'CONC VALLEY GUTTER 8IN', 'CONC VALLEY GUTTER 8IN', 77.00, NULL, 1, NULL, '2024-04-12 20:19:51', NULL, 1, NULL, NULL, NULL),
(6, 'CONC CURB & GUTTEER 8INX30IN TP2', 'CONC CURB & GUTTEER 8INX30IN TP2', 16.50, NULL, 1, NULL, '2024-04-12 20:20:29', NULL, 2, NULL, NULL, NULL),
(7, 'CONC CURB & GUTTEER 8INX30IN TP7', 'CONC CURB & GUTTEER 8INX30IN TP7', 16.50, NULL, 1, NULL, '2024-04-12 20:22:07', NULL, 2, NULL, NULL, NULL),
(8, 'CLASS B CONCRETE', 'CLASS B CONCRETE', 700.00, NULL, 1, NULL, '2024-04-12 20:22:31', NULL, 3, NULL, NULL, NULL),
(9, 'CLASS B CONCRETE, INCL REINF STEEL', 'CLASS B CONCRETE, INCL REINF STEEL', 0.00, NULL, 1, NULL, '2024-04-12 20:23:08', NULL, 3, NULL, NULL, NULL),
(10, 'CLASS B CONC, BASE OR PVMT WIDENING', 'CLASS B CONC, BASE OR PVMT WIDENING', 253.00, NULL, 1, NULL, '2024-04-12 20:23:35', NULL, 3, NULL, NULL, NULL),
(11, 'BAR REINF. STEEL ', 'BAR REINF. STEEL ', 0.00, NULL, 1, NULL, '2024-04-12 20:23:52', NULL, 4, NULL, NULL, NULL),
(12, 'CONC DRIVEWAY 8IN', 'CONC DRIVEWAY 8IN', 70.00, NULL, 1, NULL, '2024-04-12 20:24:09', NULL, 1, NULL, NULL, NULL),
(13, 'CONC SLOPE DRAIN ', 'CONC SLOPE DRAIN ', 100.00, NULL, 1, NULL, '2024-04-12 20:24:29', NULL, 1, NULL, NULL, NULL),
(14, 'CONC SIDEWALK 4IN', 'CONC SIDEWALK 4IN', 30.00, NULL, 1, NULL, '2024-04-12 20:25:08', NULL, 1, NULL, NULL, NULL),
(15, 'CONC SIDEWALK 8IN', 'CONC SIDEWALK 8IN', 63.00, NULL, 1, NULL, '2024-04-12 20:25:30', NULL, 1, NULL, NULL, NULL),
(16, 'CONC SPILLWAY TP3', 'CONC SPILLWAY TP3', 2100.00, 'none', 1, NULL, '2024-04-12 20:25:50', '2024-05-12 19:18:34', 5, NULL, NULL, NULL),
(17, 'PLAIN CONC DITCH PAVING', 'PLAIN CONC DITCH PAVING', 47.18, 'equation', 1, NULL, '2024-04-12 20:26:54', '2024-05-12 19:18:15', 1, 2, NULL, NULL),
(18, 'EXTRA CONCRETE', 'EXTRA CONCRETE', 208.00, NULL, 1, NULL, '2024-04-12 20:27:17', '2024-05-12 19:18:05', 3, NULL, NULL, NULL),
(19, 'EXTRA LABOR', 'EXTRA LABOR', 1500.00, 'same', 1, NULL, '2024-04-12 20:27:37', '2024-05-12 19:17:54', 6, NULL, NULL, NULL),
(20, 'Cubic Yards of Concrete', 'Cubic Yards of Concrete', 150.00, 'none', 1, NULL, '2024-04-12 20:28:15', '2025-01-25 17:25:02', 3, NULL, NULL, NULL),
(21, 'Test', 'Test', 100.00, 'none', 1, NULL, '2025-02-16 17:49:58', NULL, 3, NULL, NULL, NULL),
(22, 'Test 2', 'Test 2', NULL, NULL, 1, NULL, '2025-02-16 19:46:08', NULL, 3, NULL, NULL, NULL),
(23, 'Test 3', 'Test 3', NULL, NULL, 1, NULL, '2025-02-16 19:47:33', NULL, 3, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE `log` (
  `log_id` int(11) NOT NULL,
  `operation` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text,
  `ip` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `log`
--

INSERT INTO `log` (`log_id`, `operation`, `category`, `description`, `ip`, `created_at`, `user_id`) VALUES
(2, 'Update', 'Rol', 'The rol is modified: Administrator', '::1', '2024-04-12 17:15:24', 1),
(3, 'Update', 'Rol', 'The rol is modified: User', '::1', '2024-04-12 17:15:29', 1),
(4, 'Update', 'Rol', 'The rol is modified: User', '::1', '2024-04-12 17:15:39', 1),
(5, 'Update', 'User', 'The user is modified: Administrator Concrete', '::1', '2024-04-12 18:37:27', 1),
(6, 'Add', 'Unit', 'The unit is added: CU', '::1', '2024-04-12 19:46:36', 1),
(7, 'Update', 'Unit', 'The unit is modified: CU', '::1', '2024-04-12 19:46:41', 1),
(8, 'Delete', 'Unit', 'The unit is deleted: CU', '::1', '2024-04-12 19:46:45', 1),
(9, 'Add', 'Item', 'The item is added: CONC MEDIAN 4IN', '::1', '2024-04-12 20:18:17', 1),
(10, 'Add', 'Item', 'The item is added: CONC MEDIAN 6IN', '::1', '2024-04-12 20:18:40', 1),
(11, 'Add', 'Item', 'The item is added: CONCRETE V GUTTER', '::1', '2024-04-12 20:19:00', 1),
(12, 'Add', 'Item', 'The item is added: CONC VALLEY GUTTER 6IN', '::1', '2024-04-12 20:19:26', 1),
(13, 'Add', 'Item', 'The item is added: CONC VALLEY GUTTER 8IN', '::1', '2024-04-12 20:19:51', 1),
(14, 'Add', 'Item', 'The item is added: CONC CURB & GUTTEER 8INX30IN TP2', '::1', '2024-04-12 20:20:29', 1),
(15, 'Add', 'Item', 'The item is added: CONC CURB & GUTTEER 8INX30IN TP7', '::1', '2024-04-12 20:22:07', 1),
(16, 'Add', 'Item', 'The item is added: CLASS B CONCRETE', '::1', '2024-04-12 20:22:31', 1),
(17, 'Add', 'Item', 'The item is added: CLASS B CONCRETE, INCL REINF STEEL', '::1', '2024-04-12 20:23:08', 1),
(18, 'Add', 'Item', 'The item is added: CLASS B CONC, BASE OR PVMT WIDENING', '::1', '2024-04-12 20:23:35', 1),
(19, 'Add', 'Item', 'The item is added: BAR REINF. STEEL ', '::1', '2024-04-12 20:23:52', 1),
(20, 'Add', 'Item', 'The item is added: CONC DRIVEWAY 8IN', '::1', '2024-04-12 20:24:09', 1),
(21, 'Add', 'Item', 'The item is added: CONC SLOPE DRAIN ', '::1', '2024-04-12 20:24:29', 1),
(22, 'Add', 'Item', 'The item is added: CONC SIDEWALK 4IN', '::1', '2024-04-12 20:25:08', 1),
(23, 'Add', 'Item', 'The item is added: CONC SIDEWALK 8IN', '::1', '2024-04-12 20:25:30', 1),
(24, 'Add', 'Item', 'The item is added: CONC SPILLWAY TP3', '::1', '2024-04-12 20:25:50', 1),
(25, 'Add', 'Item', 'The item is added: PLAIN CONC DITCH PAVING', '::1', '2024-04-12 20:26:54', 1),
(26, 'Add', 'Item', 'The item is added: EXTRA CONCRETE', '::1', '2024-04-12 20:27:17', 1),
(27, 'Add', 'Item', 'The item is added: EXTRA LABOR', '::1', '2024-04-12 20:27:37', 1),
(28, 'Add', 'Item', 'The item is added: Cubic Yards of Concrete', '::1', '2024-04-12 20:28:15', 1),
(29, 'Add', 'Inspector', 'The inspector is added: Carlos Magill', '::1', '2024-04-13 00:03:19', 1),
(30, 'Update', 'Inspector', 'The inspector is modified: Carlos Magill', '::1', '2024-04-13 00:03:44', 1),
(31, 'Update', 'Inspector', 'The inspector is modified: Carlos Magill', '::1', '2024-04-13 00:03:50', 1),
(32, 'Add', 'Contractor', 'The contractor is added: CONTRACTOR, INC', '::1', '2024-04-13 19:10:41', 1),
(33, 'Update', 'Contractor', 'The contractor is modified: CONTRACTOR, INC', '::1', '2024-04-13 19:16:11', 1),
(34, 'Update', 'Contractor', 'The contractor is modified: CONTRACTOR, INC', '::1', '2024-04-13 19:54:24', 1),
(35, 'Add', 'Contractor', 'The contractor is added: Disrupsoft', '::1', '2024-04-13 23:00:10', 1),
(36, 'Delete', 'Contractor', 'The contractor is deleted: Disrupsoft', '::1', '2024-04-13 23:00:20', 1),
(37, 'Update', 'Item', 'The item is modified: Cubic Yards of Concrete', '::1', '2024-04-14 17:54:52', 1),
(38, 'Add', 'Project', 'The project is added: FL COUNTY', '::1', '2024-04-14 20:24:53', 1),
(39, 'Add', 'Project Item Details', 'The item is add: PLAIN CONC DITCH PAVING', '::1', '2024-04-14 21:55:05', 1),
(40, 'Update', 'Project Item Details', 'The item is modified: PLAIN CONC DITCH PAVING', '::1', '2024-04-14 22:15:53', 1),
(41, 'Add', 'Project Item Details', 'The item is add: CLASS B CONC, BASE OR PVMT WIDENING', '::1', '2024-04-14 22:21:23', 1),
(42, 'Delete', 'Item Project', 'The item details project is deleted: CLASS B CONC, BASE OR PVMT WIDENING', '::1', '2024-04-14 22:21:30', 1),
(43, 'Add', 'Project Item Details', 'The item is add: CLASS B CONCRETE', '::1', '2024-04-16 18:38:12', 1),
(44, 'Delete', 'Item Project', 'The item details project is deleted: CLASS B CONCRETE', '::1', '2024-04-16 18:39:12', 1),
(45, 'Add', 'Project Item Details', 'The item is add: CLASS B CONCRETE', '::1', '2024-04-16 18:39:28', 1),
(46, 'Update', 'Project Item Details', 'The item is modified: CLASS B CONCRETE', '::1', '2024-04-16 18:39:38', 1),
(47, 'Delete', 'Item Project', 'The item details project is deleted: CLASS B CONCRETE', '::1', '2024-04-16 18:39:42', 1),
(48, 'Add', 'Project Item Details', 'The item is add: CLASS B CONCRETE', '::1', '2024-04-16 18:39:48', 1),
(49, 'Add', 'Project Item Details', 'The item is add: CONC MEDIAN 4IN', '::1', '2024-04-16 18:44:17', 1),
(50, 'Add', 'Project Item Details', 'The item is add: CONC MEDIAN 4IN', '::1', '2024-04-16 18:44:29', 1),
(51, 'Add', 'Project Item Details', 'The item is add: CONC SPILLWAY TP3', '::1', '2024-04-16 18:44:39', 1),
(52, 'Add', 'Project Item Details', 'The item is add: EXTRA LABOR', '::1', '2024-04-16 18:48:15', 1),
(53, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2024-04-23 02:39:04', 1),
(54, 'Delete', 'Invoice', 'The invoice #1 is deleted', '::1', '2024-04-23 02:41:16', 1),
(55, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2024-04-23 04:14:51', 1),
(56, 'Delete', 'Invoice', 'The invoice #1 is deleted', '::1', '2024-04-23 04:19:45', 1),
(57, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2024-04-23 04:21:25', 1),
(58, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-04-23 04:21:35', 1),
(59, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-04-23 17:43:33', 1),
(60, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-04-23 19:01:16', 1),
(61, 'Add', 'Project Item Details', 'The item is add: CLASS B CONCRETE', '::1', '2024-04-23 19:35:31', 1),
(62, 'Add', 'Project Item Details', 'The item is add: CONC MEDIAN 6IN', '::1', '2024-04-23 19:35:45', 1),
(63, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2024-04-23 19:36:18', 1),
(64, 'Add', 'Project Item Details', 'The item is add: CONC CURB & GUTTEER 8INX30IN TP2', '::1', '2024-04-23 19:37:31', 1),
(65, 'Add', 'Project Item Details', 'The item is add: CLASS B CONCRETE', '::1', '2024-04-23 19:37:44', 1),
(66, 'Add', 'Project Item Details', 'The item is add: CONC SPILLWAY TP3', '::1', '2024-04-23 19:37:55', 1),
(67, 'Add', 'Project Item Details', 'The item is add: Cubic Yards of Concrete', '::1', '2024-04-23 19:38:09', 1),
(68, 'Update', 'Project Item Details', 'The item is modified: Cubic Yards of Concrete', '::1', '2024-04-23 19:38:20', 1),
(69, 'Add', 'Project Item Details', 'The item is add: CONC CURB & GUTTEER 8INX30IN TP7', '::1', '2024-04-23 19:38:51', 1),
(70, 'Add', 'Project Item Details', 'The item is add: CONC SIDEWALK 4IN', '::1', '2024-04-23 19:39:02', 1),
(71, 'Add', 'Project Item Details', 'The item is add: CONC DRIVEWAY 8IN', '::1', '2024-04-23 19:39:19', 1),
(72, 'Add', 'Project Item Details', 'The item is add: Cubic Yards of Concrete', '::1', '2024-04-23 19:39:35', 1),
(73, 'Add', 'Invoice', 'The invoice #3 is added', '::1', '2024-04-23 19:40:22', 1),
(74, 'Add', 'Invoice', 'The invoice #4 is added', '::1', '2024-04-23 19:40:44', 1),
(75, 'Add', 'Project', 'The project is added: FL MIAMI', '::1', '2024-04-24 04:20:22', 1),
(76, 'Add', 'Project Item Details', 'The item is add: CONC SLOPE DRAIN ', '::1', '2024-04-24 04:20:56', 1),
(77, 'Add', 'Project Item Details', 'The item is add: CONC CURB & GUTTEER 8INX30IN TP2', '::1', '2024-04-24 04:21:06', 1),
(78, 'Add', 'Invoice', 'The invoice #5 is added', '::1', '2024-04-24 04:21:24', 1),
(79, 'Add', 'Contractor', 'The contractor is added: CONTRACTOR TWO , INC', '::1', '2024-04-24 04:23:31', 1),
(80, 'Add', 'Project', 'The project is added: Houston Texas', '::1', '2024-04-24 04:24:02', 1),
(81, 'Add', 'Project Item Details', 'The item is add: CONC CURB & GUTTEER 8INX30IN TP2', '::1', '2024-04-24 04:24:23', 1),
(82, 'Add', 'Project Item Details', 'The item is add: CONC VALLEY GUTTER 6IN', '::1', '2024-04-24 04:24:33', 1),
(83, 'Add', 'Invoice', 'The invoice #6 is added', '::1', '2024-04-24 04:24:52', 1),
(84, 'Update', 'Invoice', 'The invoice #6 is modified', '::1', '2024-04-24 16:59:17', 1),
(85, 'Update', 'Invoice', 'The invoice #5 is modified', '::1', '2024-04-24 16:59:59', 1),
(86, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-04-27 18:37:14', 1),
(87, 'Update', 'Project', 'The project is modified: FL MIAMI', '::1', '2024-04-27 18:37:22', 1),
(88, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2024-04-27 18:37:31', 1),
(89, 'Add', 'Project Notes', 'The notes: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. is add to the project: Houston Texas', '::1', '2024-04-27 21:15:21', 1),
(90, 'Update', 'Project Notes', 'The notes: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. is modified to the project: Houston Texas', '::1', '2024-04-27 21:15:35', 1),
(91, 'Delete', 'Project Notes', 'The notes: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. is delete from project: Houston Texas', '::1', '2024-04-27 21:16:03', 1),
(92, 'Update', 'Company', 'The company is modified: CONTRACTOR, INC', '::1', '2024-04-29 14:51:27', 1),
(93, 'Add', 'Company', 'The company is added: gjhjkl', '::1', '2024-04-29 14:52:29', 1),
(94, 'Delete', 'Company', 'The company is deleted: gjhjkl', '::1', '2024-04-29 14:52:36', 1),
(95, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-04-29 14:53:41', 1),
(96, 'Update', 'Data Tracking', 'The data tracking is modified: CONC VALLEY GUTTER 6IN', '::1', '2024-04-29 14:53:58', 1),
(97, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-04-29 23:38:38', 1),
(98, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-04-29 23:39:10', 1),
(99, 'Delete', 'Project Item', 'The item: CONCRETE V GUTTER of the project is deleted', '::1', '2024-04-29 23:39:45', 1),
(100, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-04-29 23:41:10', 1),
(101, 'Add', 'Project Notes', 'The notes: Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups. is add to the project: Houston Texas', '::1', '2024-05-07 18:02:54', 1),
(102, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-05-07 19:17:23', 1),
(103, 'Update', 'Invoice', 'The invoice #6 is modified', '::1', '2024-05-08 23:49:36', 1),
(104, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-05-10 20:55:23', 1),
(105, 'Update', 'Equation', 'The equation is modified: SW, 4 IN, SY', '::1', '2024-05-10 23:06:04', 1),
(106, 'Update', 'Item', 'The item is modified: Cubic Yards of Concrete', '::1', '2024-05-12 19:17:47', 1),
(107, 'Update', 'Item', 'The item is modified: EXTRA LABOR', '::1', '2024-05-12 19:17:54', 1),
(108, 'Update', 'Item', 'The item is modified: EXTRA CONCRETE', '::1', '2024-05-12 19:18:05', 1),
(109, 'Update', 'Item', 'The item is modified: PLAIN CONC DITCH PAVING', '::1', '2024-05-12 19:18:15', 1),
(110, 'Update', 'Item', 'The item is modified: CONC SPILLWAY TP3', '::1', '2024-05-12 19:18:34', 1),
(111, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-05-12 20:53:23', 1),
(112, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2024-05-14 15:21:02', 1),
(113, 'Delete', 'Project Item', 'The item: Test item of the project is deleted', '::1', '2024-05-14 15:22:13', 1),
(114, 'Delete', 'Item', 'The item is deleted: Test item', '::1', '2024-05-14 15:22:27', 1),
(115, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2024-05-14 15:23:15', 1),
(116, 'Delete', 'Project Item', 'The item: Test item of the project is deleted', '::1', '2024-05-14 15:23:26', 1),
(117, 'Delete', 'Item', 'The item is deleted: Test item', '::1', '2024-05-14 15:23:37', 1),
(118, 'Update', 'Project', 'The project is modified: FL MIAMI', '::1', '2024-05-14 15:52:43', 1),
(119, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2024-05-14 15:52:54', 1),
(120, 'Add', 'Inspector', 'The inspector is added: ', '::1', '2024-05-15 21:54:53', 1),
(121, 'Add', 'Inspector', 'The inspector is added: Marcel Curbelo Carmona', '::1', '2024-05-15 21:57:30', 1),
(122, 'Update', 'Data Tracking', 'The data tracking is modified: CONC CURB & GUTTEER 8INX30IN TP2', '::1', '2024-05-15 22:01:08', 1),
(123, 'Update', 'Data Tracking', 'The data tracking is modified: CONC CURB & GUTTEER 8INX30IN TP2', '::1', '2024-05-15 22:02:15', 1),
(124, 'Update', 'Data Tracking', 'The data tracking is modified: CONC SLOPE DRAIN ', '::1', '2024-05-15 22:02:20', 1),
(125, 'Add', 'Data Tracking', 'The data tracking is add: Cubic Yards of Concrete', '::1', '2024-05-16 03:08:03', 1),
(126, 'Add', 'Data Tracking', 'The data tracking is add: CONC CURB & GUTTEER 8INX30IN TP2', '::1', '2024-05-16 03:53:30', 1),
(127, 'Add', 'Data Tracking', 'The data tracking is add: Cubic Yards of Concrete', '::1', '2024-05-17 15:05:17', 1),
(128, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-05-17 18:24:44', 1),
(129, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-05-17 18:31:58', 1),
(130, 'Add', 'Data Tracking', 'The data tracking is add: CONCRETE V GUTTER', '::1', '2024-05-17 20:06:16', 1),
(131, 'Update', 'Data Tracking', 'The data tracking is modified: CONCRETE V GUTTER', '::1', '2024-05-17 20:18:21', 1),
(132, 'Add', 'Inspector', 'The inspector is added: Cristián Gwinner', '::1', '2024-05-18 16:08:15', 1),
(133, 'Add', 'Equation', 'The equation is added: SW 6 IN, SF', '::1', '2024-05-18 16:31:11', 1),
(134, 'Add', 'Unit', 'The unit is added: ZX', '::1', '2024-05-18 16:55:58', 1),
(135, 'Delete', 'Unit', 'The unit is deleted: ZX', '::1', '2024-05-18 16:56:25', 1),
(136, 'Add', 'Unit', 'The unit is added: ZY', '::1', '2024-05-18 18:46:54', 1),
(137, 'Delete', 'Unit', 'The unit is deleted: ZY', '::1', '2024-05-18 18:47:10', 1),
(138, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009003 - Houston Texas, Date: 05/21/2024', '::1', '2024-05-21 18:33:02', 1),
(139, 'Delete', 'Data Tracking', 'The item of the data tracking is deleted, Item: CONC CURB & GUTTEER 8INX30IN TP7, Project: 0009003 - Houston Texas, Date: 05/21/2024', '::1', '2024-05-21 18:35:45', 1),
(140, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 05/21/2024', '::1', '2024-05-21 18:35:55', 1),
(141, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 05/21/2024', '::1', '2024-05-21 19:32:51', 1),
(142, 'Delete', 'Data Tracking', 'The data tracking is deleted, Project: 0009003 - Houston Texas, Date: 05/21/2024', '::1', '2024-05-21 19:32:59', 1),
(143, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009003 - Houston Texas, Date: 05/22/2024', '::1', '2024-05-22 14:15:59', 1),
(144, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 05/22/2024', '::1', '2024-05-28 19:35:59', 1),
(145, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 05/22/2024', '::1', '2024-06-21 17:37:22', 1),
(146, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 05/22/2024', '::1', '2024-06-21 18:01:18', 1),
(147, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 05/22/2024', '::1', '2024-06-21 18:01:32', 1),
(148, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-06-21 18:16:03', 1),
(149, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-06-21 18:21:59', 1),
(150, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-06-21 18:22:19', 1),
(151, 'Update', 'Equation', 'The equation is modified: SW 6 IN, SF', '::1', '2024-06-21 18:27:33', 1),
(152, 'Update', 'Equation', 'The equation is modified: SW, 4 IN, SF', '::1', '2024-06-21 18:27:45', 1),
(153, 'Update', 'Equation', 'The equation is modified: SW, 4 IN, SY', '::1', '2024-06-21 18:27:52', 1),
(154, 'Update', 'Item', 'The item is modified: Cubic Yards of Concrete', '::1', '2024-06-21 18:34:03', 1),
(155, 'Update', 'Item', 'The item is modified: Cubic Yards of Concrete', '::1', '2024-06-21 18:34:07', 1),
(156, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 05/22/2024', '::1', '2024-06-23 16:57:05', 1),
(157, 'Update', 'Project', 'The project is modified: FL MIAMI', '::1', '2024-06-23 21:06:22', 1),
(158, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009002 - FL MIAMI, Date: 06/11/2024', '::1', '2024-06-23 21:07:27', 1),
(159, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 06/11/2024', '::1', '2024-06-23 21:14:41', 1),
(160, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-06-23 21:16:44', 1),
(161, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-06-23 21:24:33', 1),
(162, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2024-07-07 19:19:29', 1),
(163, 'Update', 'Company', 'The company is modified: CONTRACTOR TWO , INC', '::1', '2024-07-28 17:43:27', 1),
(164, 'Update', 'Company', 'The company is modified: CONTRACTOR TWO , INC', '::1', '2024-07-28 17:43:32', 1),
(165, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 18:07:28', 1),
(166, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 18:09:03', 1),
(167, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 18:09:08', 1),
(168, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 18:10:04', 1),
(169, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 18:30:01', 1),
(170, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 18:30:08', 1),
(171, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 18:31:52', 1),
(172, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-07-28 19:23:17', 1),
(173, 'Add', 'Project', 'The project is added: Prueba de nuevos cambios', '::1', '2024-08-04 17:25:34', 1),
(174, 'Update', 'Project', 'The project is modified: Prueba de nuevos cambios', '::1', '2024-08-04 17:26:58', 1),
(175, 'Update', 'Project', 'The project is modified: Prueba de nuevos cambios', '::1', '2024-08-04 17:50:13', 1),
(176, 'Delete', 'Item', 'The item is deleted: Nuevo Item', '::1', '2024-08-04 18:02:10', 1),
(177, 'Update', 'Equation', 'The equation is modified: SW 6 IN, SF', '::1', '2024-08-04 18:03:31', 1),
(178, 'Update', 'Equation', 'The equation is modified: SW 6 IN, SF', '::1', '2024-08-04 18:06:45', 1),
(179, 'Add', 'Equation', 'The equation is added: New', '::1', '2024-08-04 18:44:57', 1),
(180, 'Delete', 'Equation', 'The equation is deleted: New', '::1', '2024-08-04 18:45:03', 1),
(181, 'Delete', 'Equation', 'The equation is deleted: SW 6 IN, SF', '::1', '2024-08-04 20:38:42', 1),
(182, 'Delete', 'Equation', 'The equation is deleted: SW, 4 IN, SY', '::1', '2024-08-04 20:39:03', 1),
(183, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 06/11/2024', '::1', '2024-08-04 22:35:04', 1),
(184, 'Delete', 'Data Tracking', 'The data tracking is deleted, Project: 0009003 - Houston Texas, Date: 05/22/2024', '::1', '2024-08-04 22:42:25', 1),
(185, 'Delete', 'Project', 'The project is deleted: Prueba de nuevos cambios', '::1', '2024-08-09 18:10:55', 1),
(186, 'Add', 'Project', 'The project is added: Prueba', '::1', '2024-08-09 18:12:18', 1),
(187, 'Delete', 'Project', 'The project is deleted: Prueba', '::1', '2024-08-09 18:18:27', 1),
(188, 'Add', 'Employee', 'The employee is added: Marcel Curbelo Carmona', '::1', '2024-08-09 19:38:01', 1),
(189, 'Update', 'Employee', 'The employee is modified: Marcel Curbelo Carmona', '::1', '2024-08-09 19:38:32', 1),
(190, 'Add', 'Employee', 'The employee is added: Andres Iglesias', '::1', '2024-08-09 19:38:48', 1),
(191, 'Update', 'Employee', 'The employee is modified: Andres Iglesias', '::1', '2024-08-09 19:38:51', 1),
(192, 'Add', 'Employee', 'The employee is added: dsfdsf', '::1', '2024-08-09 19:38:56', 1),
(193, 'Delete', 'Employee', 'The employee is deleted: dsfdsf', '::1', '2024-08-09 19:39:00', 1),
(194, 'Add', 'Employee', 'The employee is added: fgdfg', '::1', '2024-08-09 19:39:04', 1),
(195, 'Delete', 'Employee', 'The employee is deleted: fgdfg', '::1', '2024-08-09 19:39:07', 1),
(196, 'Add', 'Material', 'The material is added: Material 1', '::1', '2024-08-09 19:53:40', 1),
(197, 'Add', 'Material', 'The material is added: Material 2', '::1', '2024-08-09 19:53:50', 1),
(198, 'Add', 'Material', 'The material is added: Material 3', '::1', '2024-08-09 19:54:01', 1),
(199, 'Add', 'Material', 'The material is added: sdfdf', '::1', '2024-08-09 19:54:09', 1),
(200, 'Update', 'Material', 'The material is modified: sdfdf fdgdfg', '::1', '2024-08-09 19:54:13', 1),
(201, 'Delete', 'Material', 'The material is deleted: sdfdf fdgdfg', '::1', '2024-08-09 19:54:17', 1),
(202, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-08-09 20:25:39', 1),
(203, 'Delete', 'Data Tracking', 'The item of the data tracking is deleted, Item: CONC CURB & GUTTEER 8INX30IN TP7, Project: 0009002 - FL MIAMI, Date: 06/11/2024', '::1', '2024-08-10 17:38:56', 1),
(204, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-08-10 19:10:49', 1),
(205, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-08-10 19:13:36', 1),
(206, 'Delete', 'Data Tracking', 'The material of the data tracking is deleted, Material: Material 2, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-08-10 19:15:23', 1),
(207, 'Delete', 'Data Tracking', 'The employee of the data tracking is deleted, Employee: Andres Iglesias, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-08-10 19:15:26', 1),
(208, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-08-10 19:15:28', 1),
(209, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 06/11/2024', '::1', '2024-08-10 19:26:35', 1),
(210, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '127.0.0.1', '2024-08-31 16:32:41', 1),
(211, 'Update', 'Invoice', 'The invoice #1 is modified', '127.0.0.1', '2024-08-31 16:37:43', 1),
(212, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-05 17:13:53', 1),
(213, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-06 14:07:16', 1),
(214, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-06 14:07:55', 1),
(215, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-06 14:08:31', 1),
(216, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-06 14:18:43', 1),
(217, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-06 14:25:01', 1),
(218, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-11 19:42:49', 1),
(219, 'Update', 'Company', 'The company is modified: Disrupsoft', '::1', '2024-10-11 19:55:35', 1),
(220, 'Update', 'Company', 'The company is modified: CONTRACTOR, INC', '::1', '2024-10-18 23:59:37', 1),
(221, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-19 00:19:05', 1),
(222, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-19 00:19:06', 1),
(223, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-19 00:19:14', 1),
(224, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-19 00:20:40', 1),
(225, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-19 00:20:46', 1),
(226, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-19 15:52:53', 1),
(227, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-19 15:57:27', 1),
(228, 'Update', 'Project', 'The project is modified: Houston Texas Updt', '::1', '2024-10-27 14:19:58', 1),
(229, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-27 14:20:07', 1),
(230, 'Delete', 'Project Notes', 'The notes 10/01/2024 and 10/27/2024 is delete from project: Houston Texas', '::1', '2024-10-27 14:39:28', 1),
(231, 'Add', 'Project Notes', 'The notes: sdasds is add to the project: Houston Texas', '::1', '2024-10-27 14:39:51', 1),
(232, 'Delete', 'Project Notes', 'The notes 10/28/2024 and 10/29/2024 is delete from project: Houston Texas', '::1', '2024-10-27 14:40:02', 1),
(233, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-10-27 14:41:20', 1),
(234, 'Delete', 'Project Notes', 'The notes: sdasds is delete from project: Houston Texas', '::1', '2024-10-27 14:41:28', 1),
(235, 'Delete', 'Project Notes', 'The notes 10/27/2024 and  is delete from project: Houston Texas', '::1', '2024-10-27 14:41:37', 1),
(236, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-11-02 16:12:36', 1),
(237, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-11-02 16:13:09', 1),
(238, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-11-02 16:15:42', 1),
(239, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-11-02 16:15:48', 1),
(240, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-11-08 02:01:19', 1),
(241, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2024-11-22 19:14:41', 1),
(242, 'Delete', 'Data Tracking', 'The conc vendor of the data tracking is deleted, Conc Vendor: Disrupsoft, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2024-11-22 19:14:58', 1),
(243, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:04:03', 1),
(244, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:04:09', 1),
(245, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:04:12', 1),
(246, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:04:16', 1),
(247, 'Add', 'Project Notes', 'The notes: cvcxvcx is add to the project: Houston Texas', '::1', '2024-12-05 22:04:26', 1),
(248, 'Delete', 'Project Notes', 'The notes: cvcxvcx is delete from project: Houston Texas', '::1', '2024-12-05 22:04:30', 1),
(249, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:08:58', 1),
(250, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:10:21', 1),
(251, 'Delete', 'Contact', 'The project contact is deleted: Administrador Sistema', '::1', '2024-12-05 22:16:35', 1),
(252, 'Delete', 'Contact', 'The project contact is deleted: Geydis', '::1', '2024-12-05 22:16:37', 1),
(253, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:16:41', 1),
(254, 'Update', 'Project', 'The project is modified: Houston Texas', '::1', '2024-12-05 22:16:43', 1),
(255, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-12-05 22:35:03', 1),
(256, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-12-05 22:45:42', 1),
(257, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-12-05 22:46:03', 1),
(258, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-12-05 22:47:49', 1),
(259, 'Update', 'Material', 'The material is modified: Material 1', '::1', '2024-12-05 22:47:58', 1),
(260, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-12-05 22:48:27', 1),
(261, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-12-05 22:49:03', 1),
(262, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2024-12-05 22:58:01', 1),
(263, 'Update', 'Project Notes', 'The notes: Change start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023 is modified to the project: Houston Texas', '::1', '2024-12-06 18:05:46', 1),
(264, 'Update', 'Project Notes', 'The notes: Change start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\n\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023\nChange start date, old value: 08/01/2023 is modified to the project: Houston Texas', '::1', '2024-12-06 18:18:53', 1),
(265, 'Update', 'Project Notes', 'The notes: <p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023</p> is modified to the project: Houston Texas', '::1', '2024-12-06 18:28:17', 1),
(266, 'Update', 'Project Notes', 'The notes: <p><br></p><p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023</p> is modified to the project: Houston Texas', '::1', '2024-12-06 18:29:57', 1),
(267, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2024-12-06 20:41:39', 1),
(268, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2024-12-06 20:51:03', 1),
(269, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009002 - FL MIAMI, Date: 12/15/2024', '::1', '2024-12-15 14:52:28', 1),
(270, 'Delete', 'Item Project', 'The data tracking is deleted, Project: 0009002 - FL MIAMI, Date: 12/15/2024', '::1', '2024-12-15 14:52:42', 1),
(271, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-12-20 18:20:59', 1),
(272, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-12-20 18:21:07', 1),
(273, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-12-20 18:34:24', 1),
(274, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-12-20 18:36:03', 1),
(275, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-12-22 15:25:27', 1),
(276, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2024-12-23 01:46:20', 1),
(277, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-12-23 01:46:36', 1),
(278, 'Update', 'Invoice', 'The invoice #2 is modified', '::1', '2024-12-23 02:07:17', 1),
(279, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2024-12-23 02:07:29', 1),
(280, 'Update', 'Invoice', 'The invoice #2 is modified', '::1', '2024-12-23 02:07:47', 1),
(281, 'Add', 'Invoice', 'The invoice #3 is added', '::1', '2024-12-23 02:17:18', 1),
(282, 'Delete', 'Invoice', 'The invoice #3 is deleted', '::1', '2024-12-23 02:18:52', 1),
(283, 'Delete', 'Invoice', 'The invoice #2 is deleted', '::1', '2024-12-23 02:18:52', 1),
(284, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2024-12-23 02:25:37', 1),
(285, 'Delete', 'Invoice', 'The invoice #2 is deleted', '::1', '2024-12-23 02:37:42', 1),
(286, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2024-12-23 02:37:58', 1),
(287, 'Update', 'Invoice', 'The invoice #2 is modified', '::1', '2024-12-23 02:38:14', 1),
(288, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-01-11 16:23:39', 1),
(289, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-01-11 16:27:17', 1),
(290, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2025-01-11 17:53:43', 1),
(291, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2025-01-11 17:55:14', 1),
(292, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-01-11 17:56:03', 1),
(293, 'Delete', 'Data Tracking', 'The data tracking is deleted, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-01-11 17:56:09', 1),
(294, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-01-11 18:04:08', 1),
(295, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2025-01-11 18:04:22', 1),
(321, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-01-26 22:33:08', 1),
(322, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-01-26 23:13:35', 1),
(323, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-01-26 23:13:51', 1),
(324, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-01-26 23:15:31', 1),
(325, 'Delete', 'Invoice', 'The invoice #1 is deleted', '::1', '2025-02-16 12:57:09', 1),
(326, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2025-02-16 13:05:50', 1),
(327, 'Update', 'Project', 'The project is modified: FL MIAMI', '::1', '2025-02-16 13:07:10', 1),
(328, 'Delete', 'Data Tracking', 'The data tracking is deleted, Project: 0009001 - FL COUNTY, Date: 01/24/2025', '::1', '2025-02-16 13:53:56', 1),
(329, 'Delete', 'Data Tracking', 'The item of the data tracking is deleted, Item: CONC SIDEWALK 8IN, Project: 0009003 - Houston Texas, Date: 06/11/2024', '::1', '2025-02-16 13:54:31', 1),
(330, 'Delete', 'Project Item', 'The item: CONC DRIVEWAY 8IN of the project is deleted', '::1', '2025-02-16 13:55:22', 1),
(331, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:08:41', 1),
(332, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:18:35', 1),
(333, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:22:13', 1),
(334, 'Delete', 'Data Tracking', 'The subcontract item of the data tracking is deleted, Item: CONC CURB & GUTTEER 8INX30IN TP2, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:41:02', 1),
(335, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:43:36', 1),
(336, 'Add', 'Item', 'The item is added: Test 2', '::1', '2025-02-16 19:46:08', 1),
(337, 'Add', 'Item', 'The item is added: Test 3', '::1', '2025-02-16 19:47:33', 1),
(338, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:47:51', 1),
(339, 'Delete', 'Data Tracking', 'The subcontract item of the data tracking is deleted, Item: Test 3, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:58:15', 1),
(340, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-16 19:58:19', 1),
(341, 'Delete', 'Data Tracking', 'The subcontract item of the data tracking is deleted, Item: CLASS B CONCRETE, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-18 22:26:12', 1),
(342, 'Delete', 'Data Tracking', 'The subcontract item of the data tracking is deleted, Item: CONC DRIVEWAY 8IN, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-18 22:26:14', 1),
(343, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-18 22:26:36', 1),
(344, 'Delete', 'Data Tracking', 'The subcontract item of the data tracking is deleted, Item: CONC SPILLWAY TP3, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-18 22:28:04', 1),
(345, 'Delete', 'Data Tracking', 'The item of the data tracking is deleted, Item: BAR REINF. STEEL , Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-18 22:28:07', 1),
(346, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2025-02-18 22:30:47', 1),
(347, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2025-02-19 22:56:02', 1),
(348, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2025-02-19 23:06:30', 1),
(349, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2025-02-19 23:11:00', 1),
(350, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2025-02-19 23:13:13', 1),
(351, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009002 - FL MIAMI, Date: 02/19/2025', '::1', '2025-02-19 23:15:28', 1),
(352, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2025-02-19 23:19:22', 1),
(353, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2025-02-19 23:19:56', 1),
(354, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 02/19/2025', '::1', '2025-02-20 01:17:59', 1),
(355, 'Update', 'Invoice', 'The invoice #2 is modified', '::1', '2025-02-20 01:24:54', 1),
(356, 'Update', 'Project', 'The project is modified: FL MIAMI', '::1', '2025-02-21 17:36:25', 1),
(357, 'Update', 'Data Tracking', 'The data tracking is modified, Project: 0009002 - FL MIAMI, Date: 02/19/2025', '::1', '2025-02-21 17:41:52', 1),
(358, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2025-02-21 17:47:35', 1),
(359, 'Update', 'Invoice', 'The invoice #2 is modified', '::1', '2025-02-21 17:51:07', 1),
(360, 'Delete', 'Invoice', 'The invoice #1 is deleted', '::1', '2025-02-21 17:51:13', 1),
(361, 'Delete', 'Invoice', 'The invoice #1 is deleted', '::1', '2025-02-21 17:53:15', 1),
(362, 'Delete', 'Invoice', 'The invoice #2 is deleted', '::1', '2025-02-21 17:53:24', 1),
(363, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2025-02-21 18:51:16', 1),
(364, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2025-02-21 18:52:05', 1),
(365, 'Delete', 'Invoice', 'The invoice #2 is deleted', '::1', '2025-02-22 14:07:44', 1),
(366, 'Delete', 'Invoice', 'The invoice #1 is deleted', '::1', '2025-02-22 14:07:44', 1),
(367, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2025-02-22 14:08:23', 1),
(368, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2025-02-22 14:18:31', 1),
(369, 'Add', 'Invoice', 'The invoice #3 is added', '::1', '2025-02-22 14:19:11', 1),
(370, 'Delete', 'Invoice', 'The invoice #3 is deleted', '::1', '2025-02-22 14:19:47', 1),
(371, 'Delete', 'Invoice', 'The invoice #2 is deleted', '::1', '2025-02-22 14:19:47', 1),
(372, 'Delete', 'Invoice', 'The invoice #1 is deleted', '::1', '2025-02-24 01:52:32', 1),
(373, 'Add', 'Invoice', 'The invoice #1 is added', '::1', '2025-02-24 02:01:04', 1),
(374, 'Update', 'Invoice', 'The invoice #1 is modified', '::1', '2025-02-24 02:11:48', 1),
(375, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2025-02-24 02:13:46', 1),
(376, 'Delete', 'Invoice', 'The invoice #2 is deleted', '::1', '2025-02-24 02:17:18', 1),
(377, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2025-02-24 02:18:34', 1),
(378, 'Delete', 'Invoice', 'The invoice #2 is deleted', '::1', '2025-02-24 02:19:18', 1),
(379, 'Add', 'Invoice', 'The invoice #2 is added', '::1', '2025-02-24 02:22:13', 1),
(380, 'Update', 'Invoice', 'The invoice #2 is modified', '::1', '2025-02-24 02:25:18', 1),
(381, 'Add', 'Invoice', 'The invoice #3 is added', '::1', '2025-02-24 02:27:06', 1),
(382, 'Update', 'Invoice', 'The invoice #3 is modified', '::1', '2025-02-24 02:27:25', 1),
(383, 'Add', 'Advertisement', 'The advertisement is added: ', '::1', '2025-02-28 18:24:52', 1),
(384, 'Update', 'Advertisement', 'The advertisement is modified: Fashion Store Advertisement', '::1', '2025-02-28 18:25:47', 1),
(385, 'Update', 'Advertisement', 'The advertisement is modified: Fashion Store Advertisement', '::1', '2025-02-28 18:27:24', 1),
(386, 'Add', 'Advertisement', 'The advertisement is added: Fitness Gym Advertisement', '::1', '2025-02-28 18:28:53', 1),
(387, 'Add', 'Advertisement', 'The advertisement is added: Travel Agency Advertisement', '::1', '2025-02-28 18:30:05', 1),
(388, 'Add', 'Advertisement', 'The advertisement is added: ssdf', '::1', '2025-02-28 18:30:43', 1),
(389, 'Delete', 'Advertisement', 'The advertisement is deleted: ssdf', '::1', '2025-02-28 18:30:46', 1),
(390, 'Update', 'Advertisement', 'The advertisement is modified: Fashion Store Advertisement', '::1', '2025-02-28 23:21:30', 1),
(391, 'Update', 'Advertisement', 'The advertisement is modified: Fashion Store Advertisement', '::1', '2025-02-28 23:21:48', 1),
(392, 'Update', 'Advertisement', 'The advertisement is modified: Travel Agency Advertisement', '::1', '2025-02-28 23:21:53', 1),
(393, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-03-01 13:02:56', 1),
(394, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-03-01 13:03:05', 1),
(395, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-03-01 13:11:26', 1),
(396, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009001 - FL COUNTY, Date: 03/01/2025', '::1', '2025-03-01 13:25:07', 1),
(397, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-03-01 13:25:19', 1),
(398, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009001 - FL COUNTY, Date: 03/02/2025', '::1', '2025-03-01 13:43:40', 1),
(399, 'Update', 'Project Notes', 'The notes: Change <b>status</b>, old value: <b>Not</b> <b>Started</b> is modified to the project: FL COUNTY', '::1', '2025-03-01 14:42:16', 1),
(400, 'Update', 'Advertisement', 'The advertisement is modified: Fashion Store Advertisement', '::1', '2025-03-02 13:56:39', 1),
(401, 'Delete', 'Advertisement', 'The advertisement is deleted: Travel Agency Advertisement', '::1', '2025-03-02 14:01:54', 1),
(402, 'Delete', 'Advertisement', 'The advertisement is deleted: Fashion Store Advertisement', '::1', '2025-03-02 14:02:06', 1),
(403, 'Delete', 'Advertisement', 'The advertisement is deleted: Fitness Gym Advertisement', '::1', '2025-03-02 14:02:06', 1),
(404, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009001 - FL COUNTY, Date: 02/28/2025', '::1', '2025-03-02 14:05:57', 1),
(405, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-03-02 14:09:36', 1),
(406, 'Delete', 'Data Tracking', 'The data tracking is deleted, Project: 0009001 - FL COUNTY, Date: 02/28/2025', '::1', '2025-03-02 14:09:47', 1),
(407, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009001 - FL COUNTY, Date: 02/28/2025', '::1', '2025-03-02 14:10:03', 1),
(408, 'Update', 'Project', 'The project is modified: FL COUNTY', '::1', '2025-03-02 14:13:09', 1),
(409, 'Delete', 'Data Tracking', 'The data tracking is deleted, Project: 0009001 - FL COUNTY, Date: 02/28/2025', '::1', '2025-03-02 14:13:18', 1),
(410, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009001 - FL COUNTY, Date: 02/28/2025', '::1', '2025-03-02 14:13:31', 1),
(411, 'Delete', 'Data Tracking', 'The data tracking is deleted, Project: 0009001 - FL COUNTY, Date: 02/28/2025', '::1', '2025-03-02 14:15:16', 1),
(412, 'Add', 'Data Tracking', 'The data tracking is add, Project: 0009001 - FL COUNTY, Date: 02/28/2025', '::1', '2025-03-02 14:15:40', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material`
--

CREATE TABLE `material` (
  `material_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` float(8,2) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `material`
--

INSERT INTO `material` (`material_id`, `name`, `price`, `unit_id`) VALUES
(1, 'Material 1', 100.00, 3),
(2, 'Material 2', 500.00, 5),
(3, 'Material 3', 500.00, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `content` varchar(255) DEFAULT NULL,
  `readed` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `notification`
--

INSERT INTO `notification` (`id`, `content`, `readed`, `created_at`, `user_id`, `project_id`) VALUES
(2, 'Generate april invoice', 1, '2024-04-16 13:23:00', 1, NULL),
(3, 'Project 0009003 - Houston Texas is close to its due date 01/29/2025', 1, '2025-01-24 16:25:30', 1, 3),
(4, 'Project 0009001 - FL COUNTY is close to its due date 01/30/2025', 1, '2025-01-24 16:25:30', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `overhead_price`
--

CREATE TABLE `overhead_price` (
  `overhead_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `overhead_price`
--

INSERT INTO `overhead_price` (`overhead_id`, `name`, `price`) VALUES
(1, 'Price 1', 100.00),
(2, 'Price 2', 150.00),
(3, 'Price 3', 200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_downloading`
--

CREATE TABLE `plan_downloading` (
  `plan_downloading_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `plan_downloading`
--

INSERT INTO `plan_downloading` (`plan_downloading_id`, `description`, `status`) VALUES
(1, 'In Progress', 1),
(2, 'Done', 1),
(3, 'Done - Requested Scopes Not Found', 1),
(4, 'No Plans Available', 1),
(5, 'Invalid Platform Credentials', 1),
(6, 'Limit Reached', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_status`
--

CREATE TABLE `plan_status` (
  `status_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `project_id_number` varchar(50) DEFAULT NULL,
  `project_number` varchar(50) DEFAULT NULL,
  `proposal_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `subcontract` varchar(255) DEFAULT NULL,
  `contract_amount` decimal(18,2) DEFAULT NULL,
  `federal_funding` tinyint(1) DEFAULT NULL,
  `county` varchar(255) DEFAULT NULL,
  `resurfacing` tinyint(1) DEFAULT NULL,
  `invoice_contact` varchar(255) DEFAULT NULL,
  `certified_payrolls` tinyint(1) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `manager` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `po_cg` varchar(255) DEFAULT NULL,
  `concrete_quote_price` decimal(18,2) DEFAULT NULL,
  `concrete_quote_price_escalator` decimal(18,2) DEFAULT NULL,
  `concrete_time_period_every_n` int(11) DEFAULT NULL,
  `concrete_time_period_unit` enum('day','month','year','') DEFAULT NULL,
  `retainage` tinyint(1) DEFAULT NULL,
  `retainage_percentage` double(18,2) DEFAULT NULL,
  `retainage_adjustment_percentage` double(18,2) DEFAULT NULL,
  `retainage_adjustment_completion` double(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_at_concrete_quote_price` datetime DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `inspector_id` int(11) DEFAULT NULL,
  `county_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `concrete_class_id` int(11) DEFAULT NULL,
  `prevailing_wage` tinyint(1) DEFAULT NULL,
  `prevailing_county_id` int(11) DEFAULT NULL,
  `prevailing_role_id` int(11) DEFAULT NULL,
  `prevailing_rate` decimal(18,2) DEFAULT NULL,
  `bon_general` decimal(18,2) DEFAULT NULL COMMENT 'Bon total del proyecto (ej: -1850)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `project`
--

INSERT INTO `project` (`project_id`, `project_id_number`, `project_number`, `proposal_number`, `name`, `description`, `location`, `owner`, `subcontract`, `contract_amount`, `federal_funding`, `county`, `resurfacing`, `invoice_contact`, `certified_payrolls`, `start_date`, `end_date`, `due_date`, `manager`, `status`, `po_number`, `po_cg`, `concrete_quote_price`, `concrete_quote_price_escalator`, `concrete_time_period_every_n`, `concrete_time_period_unit`, `retainage`, `retainage_percentage`, `retainage_adjustment_percentage`, `retainage_adjustment_completion`, `created_at`, `updated_at`, `updated_at_concrete_quote_price`, `company_id`, `inspector_id`, `county_id`, `vendor_id`, `concrete_class_id`, `prevailing_wage`, `prevailing_county_id`, `prevailing_role_id`, `prevailing_rate`, `bon_general`) VALUES
(1, '435435435', '0009001', '345435435', 'FL COUNTY', NULL, NULL, 'f345435435', 'rt54543', 1000.00, 0, 'TEst', 0, '', 0, '2025-02-01', '2025-02-28', NULL, 'Andres', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-14 20:24:53', '2025-03-02 14:13:09', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '34435435', '0009002', '34345435', 'FL MIAMI', NULL, NULL, 'Marcel', 'M345435435', 45000.00, 0, 'Miami', 0, '', 0, '2025-02-01', '2025-02-28', '2024-05-28', 'Dan', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-24 04:20:22', '2025-02-21 17:36:25', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '3243545', '0009003', '434354', 'Houston Texas', NULL, NULL, 'Marcel', '896532', 844500.00, 1, 'Miami', 1, 'Marcel Curbelo Carmona', 1, '2024-11-06', '2024-11-29', '2025-01-29', 'Marcel', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-24 04:24:02', '2025-01-24 19:25:26', NULL, 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_attachment`
--

CREATE TABLE `project_attachment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_concrete_class`
--

CREATE TABLE `project_concrete_class` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `concrete_class_id` int(11) NOT NULL,
  `concrete_quote_price` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_contact`
--

CREATE TABLE `project_contact` (
  `contact_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `notes` text,
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `project_contact`
--

INSERT INTO `project_contact` (`contact_id`, `name`, `email`, `phone`, `role`, `notes`, `project_id`) VALUES
(1, 'Marcel Curbelo Carmona', 'cyborgmnk@gmail.com', '(955)383-3543', '', '', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_county`
--

CREATE TABLE `project_county` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `county_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_item`
--

CREATE TABLE `project_item` (
  `id` int(11) NOT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `yield_calculation` varchar(50) DEFAULT NULL,
  `quantity_old` decimal(18,6) DEFAULT NULL,
  `price_old` decimal(18,6) DEFAULT NULL,
  `principal` tinyint(1) DEFAULT NULL,
  `change_order` tinyint(1) DEFAULT NULL,
  `change_order_date` datetime DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `equation_id` int(11) DEFAULT NULL,
  `apply_retainage` tinyint(1) DEFAULT NULL,
  `boned` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `project_item`
--

INSERT INTO `project_item` (`id`, `quantity`, `price`, `yield_calculation`, `quantity_old`, `price_old`, `principal`, `change_order`, `change_order_date`, `project_id`, `item_id`, `equation_id`, `apply_retainage`, `boned`) VALUES
(1, 1500.000000, 16.50, 'equation', NULL, NULL, 1, NULL, NULL, 3, 6, 2, NULL, NULL),
(2, 2000.000000, 63.00, 'same', NULL, NULL, 1, NULL, NULL, 3, 15, NULL, NULL, NULL),
(4, 1600.000000, 150.00, 'none', NULL, NULL, 1, NULL, NULL, 3, 20, NULL, NULL, NULL),
(8, 2500.000000, 25.00, 'equation', NULL, NULL, 1, NULL, NULL, 3, 3, 2, NULL, NULL),
(9, 2500.000000, 16.50, 'equation', NULL, NULL, 1, NULL, NULL, 3, 7, 2, NULL, NULL),
(10, 5000.000000, 70.00, 'equation', NULL, NULL, 1, NULL, NULL, 3, 12, 2, NULL, NULL),
(11, 50.000000, 160.00, 'none', NULL, NULL, 1, NULL, NULL, 2, 12, NULL, NULL, NULL),
(12, 60.000000, 200.00, '', NULL, NULL, 1, NULL, NULL, 2, 6, NULL, NULL, NULL),
(13, 50.000000, 300.00, '', NULL, NULL, 1, NULL, NULL, 2, 7, NULL, NULL, NULL),
(15, 10.000000, 100.00, 'same', NULL, NULL, 1, NULL, NULL, 3, 21, NULL, NULL, NULL),
(16, 0.000000, 100.00, 'same', NULL, NULL, 1, NULL, NULL, 2, 11, NULL, NULL, NULL),
(17, 100.000000, 100.00, 'equation', NULL, NULL, 1, NULL, NULL, 2, 8, 2, NULL, NULL),
(18, 10.000000, 100.00, 'none', NULL, NULL, 1, NULL, NULL, 1, 11, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_item_history`
--

CREATE TABLE `project_item_history` (
  `id` int(11) NOT NULL,
  `project_item_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_notes`
--

CREATE TABLE `project_notes` (
  `id` int(11) NOT NULL,
  `notes` text,
  `date` date DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `project_notes`
--

INSERT INTO `project_notes` (`id`, `notes`, `date`, `project_id`) VALUES
(2, 'Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.', '2024-05-07', 3),
(3, 'Change start date, old value: 04/01/2024', '2024-11-02', 3),
(4, 'Change end date, old value: 04/30/2024', '2024-11-02', 3),
(5, 'Change start date, old value: 11/01/2024', '2024-11-02', 3),
(6, 'Change end date, old value: 11/30/2024', '2024-11-02', 3),
(7, 'Change start date, old value: 11/03/2024', '2024-11-02', 3),
(8, 'Change start date, old value: 08/08/2023', '2024-11-02', 3),
(9, '<p>Change start date, old value: 08/01/2023</p><p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023\n</p><p>Change start date, old value: 08/01/2023</p>', '2024-11-08', 3),
(10, 'Change location, old value: FL COUNTY', '2025-01-24', 1),
(11, 'Change contract amount, old value: ', '2025-01-24', 1),
(12, 'Change proposal id #, old value: ', '2025-01-24', 1),
(13, 'Change project id #, old value: ', '2025-01-24', 1),
(14, 'Change owner, old value: ', '2025-01-24', 1),
(15, 'Change Subcontract NO, old value: ', '2025-01-24', 1),
(16, 'Change county, old value: ', '2025-01-24', 1),
(17, 'Change due date, old value: 05/31/2024', '2025-01-24', 1),
(18, 'Change due date, old value: 05/30/2024', '2025-01-24', 3),
(19, 'Change due date, old value: 01/30/2025', '2025-01-26', 1),
(20, 'Change due date, old value: ', '2025-01-26', 1),
(21, 'Change end date, old value: ', '2025-01-26', 1),
(22, 'Change location, old value: FL MIAMI', '2025-02-16', 2),
(23, 'Change contract amount, old value: ', '2025-02-16', 2),
(24, 'Change proposal id #, old value: ', '2025-02-16', 2),
(25, 'Change project id #, old value: ', '2025-02-16', 2),
(26, 'Change owner, old value: ', '2025-02-16', 2),
(27, 'Change Subcontract NO, old value: ', '2025-02-16', 2),
(28, 'Change county, old value: ', '2025-02-16', 2),
(29, 'Change start date, old value: ', '2025-02-16', 2),
(30, 'Change end date, old value: ', '2025-02-16', 2),
(31, 'Change contract amount, old value: 35000', '2025-02-21', 2),
(32, 'Change <b>status</b>, old value: <b>Not</b> <b>Started</b>', '2025-03-01', 1),
(33, 'Change status, old value: In Progress', '2025-03-01', 1),
(34, 'Change start date, old value: ', '2025-03-01', 1),
(35, 'Change end date, old value: 01/31/2025', '2025-03-01', 1),
(36, 'Change status, old value: In Progress', '2025-03-01', 1),
(37, 'Change contract amount, old value: 0', '2025-03-01', 1),
(38, 'Change status, old value: In Progress', '2025-03-02', 1),
(39, 'Change status, old value: In Progress', '2025-03-02', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_price_adjustment`
--

CREATE TABLE `project_price_adjustment` (
  `id` int(11) NOT NULL,
  `day` date DEFAULT NULL,
  `percent` decimal(8,2) DEFAULT NULL,
  `items_id` text,
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_stage`
--

CREATE TABLE `project_stage` (
  `stage_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_type`
--

CREATE TABLE `project_type` (
  `type_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proposal_type`
--

CREATE TABLE `proposal_type` (
  `type_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `race`
--

CREATE TABLE `race` (
  `race_id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `classification` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `race`
--

INSERT INTO `race` (`race_id`, `code`, `description`, `classification`) VALUES
(1, 'As-Ind', 'Asian-Indian', 'Asian or Pacific Islander'),
(2, 'As-Pac', 'Asian-Pacific', 'Asian or Pacific Islander'),
(3, 'Blk', 'Black', 'Black (not of Hispanic origin)'),
(4, 'White', 'White', 'Not a minority'),
(5, 'His', 'Hispanic', 'Hispanic'),
(6, 'Na/Am', 'Native American', 'American Indian or Native American'),
(7, 'Oth', 'Other', 'Not a minority');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reminder`
--

CREATE TABLE `reminder` (
  `reminder_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text,
  `day` date DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reminder_recipient`
--

CREATE TABLE `reminder_recipient` (
  `id` int(11) NOT NULL,
  `reminder_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `rol_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`rol_id`, `name`) VALUES
(1, 'Administrator'),
(2, 'User');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permission`
--

CREATE TABLE `rol_permission` (
  `id` int(11) NOT NULL,
  `view_permission` tinyint(1) DEFAULT NULL,
  `add_permission` tinyint(1) DEFAULT NULL,
  `edit_permission` tinyint(1) DEFAULT NULL,
  `delete_permission` tinyint(1) DEFAULT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `function_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rol_permission`
--

INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`) VALUES
(9, 1, 1, 1, 1, 1, 1),
(10, 1, 1, 1, 1, 1, 2),
(11, 1, 1, 1, 1, 1, 3),
(12, 1, 1, 1, 1, 1, 4),
(14, 1, 1, 1, 1, 2, 1),
(15, 1, 0, 0, 0, 2, 4),
(16, 1, 1, 1, 1, 1, 5),
(17, 1, 1, 1, 1, 1, 6),
(18, 1, 1, 1, 1, 1, 7),
(19, 1, 1, 1, 1, 1, 8),
(20, 1, 1, 1, 1, 1, 9),
(21, 1, 1, 1, 1, 1, 10),
(22, 1, 1, 1, 1, 1, 11),
(23, 1, 1, 1, 1, 1, 12),
(24, 1, 1, 1, 1, 2, 12),
(25, 1, 1, 1, 1, 1, 13),
(26, 1, 1, 1, 1, 1, 14),
(27, 1, 1, 1, 1, 1, 15),
(28, 1, 1, 1, 1, 1, 16),
(29, 1, 1, 1, 1, 1, 17),
(30, 1, 1, 1, 1, 1, 18),
(31, 1, 1, 1, 1, 1, 19),
(32, 1, 1, 1, 1, 1, 20),
(33, 1, 1, 1, 1, 1, 21),
(34, 1, 1, 1, 1, 1, 22),
(35, 1, 1, 1, 1, 1, 23),
(36, 1, 1, 1, 1, 1, 24),
(37, 1, 1, 1, 1, 1, 25),
(38, 1, 1, 1, 1, 1, 26),
(39, 1, 1, 1, 1, 1, 27),
(40, 1, 1, 1, 1, 1, 28),
(41, 1, 1, 1, 1, 1, 29),
(42, 1, 1, 1, 1, 1, 30),
(43, 1, 1, 1, 1, 1, 31),
(44, 1, 1, 1, 1, 1, 32),
(45, 1, 1, 1, 1, 1, 33),
(46, 1, 1, 1, 1, 1, 34),
(47, 1, 1, 1, 1, 1, 35),
(48, 1, 1, 1, 1, 1, 36),
(49, 1, 1, 1, 1, 1, 37);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitud` varchar(50) DEFAULT NULL,
  `longitud` varchar(50) DEFAULT NULL,
  `day` datetime DEFAULT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `notes` text,
  `project_id` int(11) DEFAULT NULL,
  `project_contact_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedule_concrete_vendor_contact`
--

CREATE TABLE `schedule_concrete_vendor_contact` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedule_employee`
--

CREATE TABLE `schedule_employee` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcontractor`
--

CREATE TABLE `subcontractor` (
  `subcontractor_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text,
  `phone` varchar(50) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_phone` varchar(50) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcontractor_employee`
--

CREATE TABLE `subcontractor_employee` (
  `subcontractor_employee_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `subcontractor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcontractor_notes`
--

CREATE TABLE `subcontractor_notes` (
  `id` int(11) NOT NULL,
  `notes` text,
  `date` date DEFAULT NULL,
  `subcontractor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sync_queue_qbwc`
--

CREATE TABLE `sync_queue_qbwc` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `intentos` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unit`
--

CREATE TABLE `unit` (
  `unit_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `unit`
--

INSERT INTO `unit` (`unit_id`, `description`, `status`) VALUES
(1, 'SY', 1),
(2, 'LF', 1),
(3, 'CY', 1),
(4, 'LB', 1),
(5, 'EA', 1),
(6, 'LS', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `estimator` tinyint(1) DEFAULT NULL,
  `bone` tinyint(1) DEFAULT NULL,
  `retainage` tinyint(1) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `player_id` varchar(255) DEFAULT NULL,
  `push_token` varchar(255) DEFAULT NULL,
  `plataforma` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `rol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`user_id`, `name`, `lastname`, `email`, `password`, `status`, `estimator`, `bone`, `retainage`, `phone`, `created_at`, `updated_at`, `player_id`, `push_token`, `plataforma`, `imagen`, `rol_id`) VALUES
(1, 'Administrator', 'Concrete', 'admin@concrete.com', '$2y$12$ojiMWHh/4xuvv0D8JdpY7OnlBd5TuYTW76SyWlR5QNbOAgtBt64dy', 1, NULL, NULL, NULL, '', '2024-04-12 09:24:44', '2024-04-12 18:37:27', NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_access_token`
--

CREATE TABLE `user_access_token` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_permission`
--

CREATE TABLE `user_permission` (
  `id` int(11) NOT NULL,
  `view_permission` tinyint(1) DEFAULT NULL,
  `add_permission` tinyint(1) DEFAULT NULL,
  `edit_permission` tinyint(1) DEFAULT NULL,
  `delete_permission` tinyint(1) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `function_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `user_permission`
--

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`) VALUES
(5, 1, 1, 1, 1, 1, 1),
(6, 1, 1, 1, 1, 1, 2),
(7, 1, 1, 1, 1, 1, 3),
(8, 1, 1, 1, 1, 1, 4),
(9, 1, 1, 1, 1, 1, 5),
(10, 1, 1, 1, 1, 1, 6),
(11, 1, 1, 1, 1, 1, 7),
(12, 1, 1, 1, 1, 1, 8),
(13, 1, 1, 1, 1, 1, 9),
(14, 1, 1, 1, 1, 1, 10),
(15, 1, 1, 1, 1, 1, 11),
(16, 1, 1, 1, 1, 1, 12),
(17, 1, 1, 1, 1, 1, 13),
(18, 1, 1, 1, 1, 1, 14),
(19, 1, 1, 1, 1, 1, 15),
(20, 1, 1, 1, 1, 1, 16),
(21, 1, 1, 1, 1, 1, 17),
(22, 1, 1, 1, 1, 1, 18),
(23, 1, 1, 1, 1, 1, 19),
(24, 1, 1, 1, 1, 1, 20),
(25, 1, 1, 1, 1, 1, 21),
(26, 1, 1, 1, 1, 1, 22),
(27, 1, 1, 1, 1, 1, 23),
(28, 1, 1, 1, 1, 1, 24),
(29, 1, 1, 1, 1, 1, 25),
(30, 1, 1, 1, 1, 1, 26),
(31, 1, 1, 1, 1, 1, 27),
(32, 1, 1, 1, 1, 1, 28),
(33, 1, 1, 1, 1, 1, 29),
(34, 1, 1, 1, 1, 1, 30),
(35, 1, 1, 1, 1, 1, 31),
(36, 1, 1, 1, 1, 1, 32),
(37, 1, 1, 1, 1, 1, 33),
(38, 1, 1, 1, 1, 1, 34),
(39, 1, 1, 1, 1, 1, 35),
(40, 1, 1, 1, 1, 1, 36),
(41, 1, 1, 1, 1, 1, 37);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_qbwc_token`
--

CREATE TABLE `user_qbwc_token` (
  `id` int(11) NOT NULL,
  `token` text,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `advertisement`
--
ALTER TABLE `advertisement`
  ADD PRIMARY KEY (`advertisement_id`);

--
-- Indices de la tabla `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`);

--
-- Indices de la tabla `company_contact`
--
ALTER TABLE `company_contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `Ref6474` (`company_id`);

--
-- Indices de la tabla `concrete_class`
--
ALTER TABLE `concrete_class`
  ADD PRIMARY KEY (`concrete_class_id`);

--
-- Indices de la tabla `concrete_vendor`
--
ALTER TABLE `concrete_vendor`
  ADD PRIMARY KEY (`vendor_id`);

--
-- Indices de la tabla `concrete_vendor_contact`
--
ALTER TABLE `concrete_vendor_contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `Refcontactconcvendor1` (`vendor_id`);

--
-- Indices de la tabla `county`
--
ALTER TABLE `county`
  ADD PRIMARY KEY (`county_id`),
  ADD KEY `district_id` (`district_id`);

--
-- Indices de la tabla `data_tracking`
--
ALTER TABLE `data_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inspector_id` (`inspector_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `overhead_price_id` (`overhead_price_id`);

--
-- Indices de la tabla `data_tracking_attachment`
--
ALTER TABLE `data_tracking_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refdata_tracking_attachment1` (`data_tracking_id`);

--
-- Indices de la tabla `data_tracking_conc_vendor`
--
ALTER TABLE `data_tracking_conc_vendor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref6345` (`data_tracking_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indices de la tabla `data_tracking_item`
--
ALTER TABLE `data_tracking_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref7185` (`data_tracking_id`),
  ADD KEY `Ref7686` (`project_item_id`);

--
-- Indices de la tabla `data_tracking_labor`
--
ALTER TABLE `data_tracking_labor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_data_tracking_labor` (`data_tracking_id`),
  ADD KEY `fk_data_tracking_labor_employee` (`employee_id`),
  ADD KEY `subcontractor_employee_id` (`subcontractor_employee_id`);

--
-- Indices de la tabla `data_tracking_material`
--
ALTER TABLE `data_tracking_material`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_data_tracking_material` (`data_tracking_id`),
  ADD KEY `fk_data_tracking_material_2` (`material_id`);

--
-- Indices de la tabla `data_tracking_subcontract`
--
ALTER TABLE `data_tracking_subcontract`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref63452` (`data_tracking_id`),
  ADD KEY `Ref63451` (`item_id`),
  ADD KEY `project_item_id` (`project_item_id`),
  ADD KEY `subcontractor_id` (`subcontractor_id`);

--
-- Indices de la tabla `district`
--
ALTER TABLE `district`
  ADD PRIMARY KEY (`district_id`);

--
-- Indices de la tabla `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `race_id` (`race_id`),
  ADD KEY `idx_role_id` (`role_id`);

--
-- Indices de la tabla `employee_role`
--
ALTER TABLE `employee_role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `unique_description` (`description`);

--
-- Indices de la tabla `equation`
--
ALTER TABLE `equation`
  ADD PRIMARY KEY (`equation_id`);

--
-- Indices de la tabla `estimate`
--
ALTER TABLE `estimate`
  ADD PRIMARY KEY (`estimate_id`),
  ADD KEY `Refestimate1` (`project_stage_id`),
  ADD KEY `Refestimate2` (`proposal_type_id`),
  ADD KEY `Refestimate3` (`status_id`),
  ADD KEY `Refestimate4` (`district_id`),
  ADD KEY `Refestimate5` (`company_id`),
  ADD KEY `Refestimate6` (`contact_id`),
  ADD KEY `plan_downloading_id` (`plan_downloading_id`),
  ADD KEY `county_id` (`county_id`);

--
-- Indices de la tabla `estimate_bid_deadline`
--
ALTER TABLE `estimate_bid_deadline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refestimate_bid_dealine1` (`estimate_id`),
  ADD KEY `Refestimate_bid_dealine2` (`company_id`);

--
-- Indices de la tabla `estimate_company`
--
ALTER TABLE `estimate_company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refestimate_company1` (`estimate_id`),
  ADD KEY `Refestimate_company2` (`company_id`),
  ADD KEY `Refestimate_company3` (`contact_id`);

--
-- Indices de la tabla `estimate_estimator`
--
ALTER TABLE `estimate_estimator`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refestimate_estimator1` (`estimate_id`),
  ADD KEY `Refestimate_estimator2` (`user_id`);

--
-- Indices de la tabla `estimate_project_type`
--
ALTER TABLE `estimate_project_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refestimate_project_type1` (`estimate_id`),
  ADD KEY `Refestimate_project_type2` (`type_id`);

--
-- Indices de la tabla `estimate_quote`
--
ALTER TABLE `estimate_quote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refestimate_quote1` (`estimate_id`),
  ADD KEY `Refestimate_quote2` (`item_id`),
  ADD KEY `Refestimate_quote3` (`equation_id`);

--
-- Indices de la tabla `function`
--
ALTER TABLE `function`
  ADD PRIMARY KEY (`function_id`);

--
-- Indices de la tabla `holiday`
--
ALTER TABLE `holiday`
  ADD PRIMARY KEY (`holiday_id`);

--
-- Indices de la tabla `inspector`
--
ALTER TABLE `inspector`
  ADD PRIMARY KEY (`inspector_id`);

--
-- Indices de la tabla `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `Ref6665` (`project_id`);

--
-- Indices de la tabla `invoice_attachment`
--
ALTER TABLE `invoice_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refinvoice_attachment1` (`invoice_id`);

--
-- Indices de la tabla `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `Ref6969` (`invoice_id`),
  ADD KEY `project_item_id` (`project_item_id`);

--
-- Indices de la tabla `invoice_item_notes`
--
ALTER TABLE `invoice_item_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refinvoice_item_notes1` (`invoice_item_id`);

--
-- Indices de la tabla `invoice_notes`
--
ALTER TABLE `invoice_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refinvoice_notes1` (`invoice_id`);

--
-- Indices de la tabla `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `Ref6864` (`unit_id`),
  ADD KEY `equation_id` (`equation_id`);

--
-- Indices de la tabla `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `Ref135434` (`user_id`);

--
-- Indices de la tabla `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `fk_material_unit` (`unit_id`);

--
-- Indices de la tabla `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref1377` (`user_id`);

--
-- Indices de la tabla `overhead_price`
--
ALTER TABLE `overhead_price`
  ADD PRIMARY KEY (`overhead_id`);

--
-- Indices de la tabla `plan_downloading`
--
ALTER TABLE `plan_downloading`
  ADD PRIMARY KEY (`plan_downloading_id`);

--
-- Indices de la tabla `plan_status`
--
ALTER TABLE `plan_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indices de la tabla `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `Ref6467` (`company_id`),
  ADD KEY `Ref6573` (`inspector_id`),
  ADD KEY `county_id` (`county_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `concrete_class_id` (`concrete_class_id`),
  ADD KEY `prevailing_county_id` (`prevailing_county_id`),
  ADD KEY `prevailing_role_id` (`prevailing_role_id`);

--
-- Indices de la tabla `project_attachment`
--
ALTER TABLE `project_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refproject_attachment1` (`project_id`);

--
-- Indices de la tabla `project_concrete_class`
--
ALTER TABLE `project_concrete_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `concrete_class_id` (`concrete_class_id`);

--
-- Indices de la tabla `project_contact`
--
ALTER TABLE `project_contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `Ref6475` (`project_id`);

--
-- Indices de la tabla `project_county`
--
ALTER TABLE `project_county`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_project_county` (`project_id`,`county_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `county_id` (`county_id`);

--
-- Indices de la tabla `project_item`
--
ALTER TABLE `project_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref6679` (`project_id`),
  ADD KEY `Ref6780` (`item_id`),
  ADD KEY `equation_id` (`equation_id`);

--
-- Indices de la tabla `project_item_history`
--
ALTER TABLE `project_item_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_item_id` (`project_item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `project_notes`
--
ALTER TABLE `project_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref6678` (`project_id`);

--
-- Indices de la tabla `project_price_adjustment`
--
ALTER TABLE `project_price_adjustment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refproject_price_adjustment1` (`project_id`);

--
-- Indices de la tabla `project_stage`
--
ALTER TABLE `project_stage`
  ADD PRIMARY KEY (`stage_id`);

--
-- Indices de la tabla `project_type`
--
ALTER TABLE `project_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indices de la tabla `proposal_type`
--
ALTER TABLE `proposal_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indices de la tabla `race`
--
ALTER TABLE `race`
  ADD PRIMARY KEY (`race_id`);

--
-- Indices de la tabla `reminder`
--
ALTER TABLE `reminder`
  ADD PRIMARY KEY (`reminder_id`);

--
-- Indices de la tabla `reminder_recipient`
--
ALTER TABLE `reminder_recipient`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refreminderrecipient1` (`reminder_id`),
  ADD KEY `Refreminderrecipient2` (`user_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`rol_id`);

--
-- Indices de la tabla `rol_permission`
--
ALTER TABLE `rol_permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref151` (`rol_id`),
  ADD KEY `Ref192` (`function_id`);

--
-- Indices de la tabla `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `Refscheduleprojectid` (`project_id`),
  ADD KEY `Refscheduleprojectcontactid` (`project_contact_id`),
  ADD KEY `Refscheduleconcvendorid` (`vendor_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indices de la tabla `schedule_concrete_vendor_contact`
--
ALTER TABLE `schedule_concrete_vendor_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refs_chedule_concrete_vendor_contacts_cheduleid` (`schedule_id`),
  ADD KEY `Refs_chedule_concrete_vendor_contacts_contactid` (`contact_id`);

--
-- Indices de la tabla `schedule_employee`
--
ALTER TABLE `schedule_employee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refschedule_employee1` (`schedule_id`),
  ADD KEY `Refschedule_employee2` (`employee_id`);

--
-- Indices de la tabla `subcontractor`
--
ALTER TABLE `subcontractor`
  ADD PRIMARY KEY (`subcontractor_id`);

--
-- Indices de la tabla `subcontractor_employee`
--
ALTER TABLE `subcontractor_employee`
  ADD PRIMARY KEY (`subcontractor_employee_id`),
  ADD KEY `Ref63452` (`subcontractor_id`);

--
-- Indices de la tabla `subcontractor_notes`
--
ALTER TABLE `subcontractor_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref63453` (`subcontractor_id`);

--
-- Indices de la tabla `sync_queue_qbwc`
--
ALTER TABLE `sync_queue_qbwc`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`unit_id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `Ref156` (`rol_id`);

--
-- Indices de la tabla `user_access_token`
--
ALTER TABLE `user_access_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`token`);

--
-- Indices de la tabla `user_permission`
--
ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref133` (`user_id`),
  ADD KEY `Ref194` (`function_id`);

--
-- Indices de la tabla `user_qbwc_token`
--
ALTER TABLE `user_qbwc_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refuser_qbwc_token1` (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `advertisement`
--
ALTER TABLE `advertisement`
  MODIFY `advertisement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `company_contact`
--
ALTER TABLE `company_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `concrete_class`
--
ALTER TABLE `concrete_class`
  MODIFY `concrete_class_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `concrete_vendor`
--
ALTER TABLE `concrete_vendor`
  MODIFY `vendor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `concrete_vendor_contact`
--
ALTER TABLE `concrete_vendor_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `county`
--
ALTER TABLE `county`
  MODIFY `county_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `data_tracking`
--
ALTER TABLE `data_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `data_tracking_attachment`
--
ALTER TABLE `data_tracking_attachment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `data_tracking_conc_vendor`
--
ALTER TABLE `data_tracking_conc_vendor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `data_tracking_item`
--
ALTER TABLE `data_tracking_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `data_tracking_labor`
--
ALTER TABLE `data_tracking_labor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `data_tracking_material`
--
ALTER TABLE `data_tracking_material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `data_tracking_subcontract`
--
ALTER TABLE `data_tracking_subcontract`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `district`
--
ALTER TABLE `district`
  MODIFY `district_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `employee_role`
--
ALTER TABLE `employee_role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `equation`
--
ALTER TABLE `equation`
  MODIFY `equation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estimate`
--
ALTER TABLE `estimate`
  MODIFY `estimate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estimate_bid_deadline`
--
ALTER TABLE `estimate_bid_deadline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estimate_company`
--
ALTER TABLE `estimate_company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estimate_estimator`
--
ALTER TABLE `estimate_estimator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estimate_project_type`
--
ALTER TABLE `estimate_project_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estimate_quote`
--
ALTER TABLE `estimate_quote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `function`
--
ALTER TABLE `function`
  MODIFY `function_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `holiday`
--
ALTER TABLE `holiday`
  MODIFY `holiday_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inspector`
--
ALTER TABLE `inspector`
  MODIFY `inspector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `invoice_attachment`
--
ALTER TABLE `invoice_attachment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `invoice_item`
--
ALTER TABLE `invoice_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `invoice_item_notes`
--
ALTER TABLE `invoice_item_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `invoice_notes`
--
ALTER TABLE `invoice_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `log`
--
ALTER TABLE `log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=413;

--
-- AUTO_INCREMENT de la tabla `material`
--
ALTER TABLE `material`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `overhead_price`
--
ALTER TABLE `overhead_price`
  MODIFY `overhead_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `plan_downloading`
--
ALTER TABLE `plan_downloading`
  MODIFY `plan_downloading_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `plan_status`
--
ALTER TABLE `plan_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `project_attachment`
--
ALTER TABLE `project_attachment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project_concrete_class`
--
ALTER TABLE `project_concrete_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project_contact`
--
ALTER TABLE `project_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `project_county`
--
ALTER TABLE `project_county`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project_item`
--
ALTER TABLE `project_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `project_item_history`
--
ALTER TABLE `project_item_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project_notes`
--
ALTER TABLE `project_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `project_price_adjustment`
--
ALTER TABLE `project_price_adjustment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project_stage`
--
ALTER TABLE `project_stage`
  MODIFY `stage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project_type`
--
ALTER TABLE `project_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proposal_type`
--
ALTER TABLE `proposal_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `race`
--
ALTER TABLE `race`
  MODIFY `race_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `reminder`
--
ALTER TABLE `reminder`
  MODIFY `reminder_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reminder_recipient`
--
ALTER TABLE `reminder_recipient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `rol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `rol_permission`
--
ALTER TABLE `rol_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `schedule`
--
ALTER TABLE `schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `schedule_concrete_vendor_contact`
--
ALTER TABLE `schedule_concrete_vendor_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `schedule_employee`
--
ALTER TABLE `schedule_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subcontractor`
--
ALTER TABLE `subcontractor`
  MODIFY `subcontractor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subcontractor_employee`
--
ALTER TABLE `subcontractor_employee`
  MODIFY `subcontractor_employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subcontractor_notes`
--
ALTER TABLE `subcontractor_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sync_queue_qbwc`
--
ALTER TABLE `sync_queue_qbwc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unit`
--
ALTER TABLE `unit`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `user_access_token`
--
ALTER TABLE `user_access_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_permission`
--
ALTER TABLE `user_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `user_qbwc_token`
--
ALTER TABLE `user_qbwc_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `company_contact`
--
ALTER TABLE `company_contact`
  ADD CONSTRAINT `Refcontractor74` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Filtros para la tabla `concrete_vendor_contact`
--
ALTER TABLE `concrete_vendor_contact`
  ADD CONSTRAINT `Refcontactconcvendor1` FOREIGN KEY (`vendor_id`) REFERENCES `concrete_vendor` (`vendor_id`);

--
-- Filtros para la tabla `county`
--
ALTER TABLE `county`
  ADD CONSTRAINT `Refcountydistrictid` FOREIGN KEY (`district_id`) REFERENCES `district` (`district_id`);

--
-- Filtros para la tabla `data_tracking`
--
ALTER TABLE `data_tracking`
  ADD CONSTRAINT `Refinspector158` FOREIGN KEY (`inspector_id`) REFERENCES `inspector` (`inspector_id`),
  ADD CONSTRAINT `Refoverheadprice25` FOREIGN KEY (`overhead_price_id`) REFERENCES `overhead_price` (`overhead_id`),
  ADD CONSTRAINT `Refproject25` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `data_tracking_attachment`
--
ALTER TABLE `data_tracking_attachment`
  ADD CONSTRAINT `Refdata_tracking_attachment1` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`);

--
-- Filtros para la tabla `data_tracking_conc_vendor`
--
ALTER TABLE `data_tracking_conc_vendor`
  ADD CONSTRAINT `Refdatatrackingconcvendor35` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `Refdatatrackingconcvendor36` FOREIGN KEY (`vendor_id`) REFERENCES `concrete_vendor` (`vendor_id`);

--
-- Filtros para la tabla `data_tracking_item`
--
ALTER TABLE `data_tracking_item`
  ADD CONSTRAINT `Refdata_tracking85` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`),
  ADD CONSTRAINT `Refproject_item86` FOREIGN KEY (`project_item_id`) REFERENCES `project_item` (`id`);

--
-- Filtros para la tabla `data_tracking_labor`
--
ALTER TABLE `data_tracking_labor`
  ADD CONSTRAINT `fk_data_tracking_labor` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_data_tracking_labor_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_data_tracking_labor_subcontractor_employee` FOREIGN KEY (`subcontractor_employee_id`) REFERENCES `subcontractor_employee` (`subcontractor_employee_id`);

--
-- Filtros para la tabla `data_tracking_material`
--
ALTER TABLE `data_tracking_material`
  ADD CONSTRAINT `fk_data_tracking_material` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_data_tracking_material_2` FOREIGN KEY (`material_id`) REFERENCES `material` (`material_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `data_tracking_subcontract`
--
ALTER TABLE `data_tracking_subcontract`
  ADD CONSTRAINT `Refdatatrackingsubcontract35` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `Refdatatrackingsubcontract36` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `Refdatatrackingsubcontract37` FOREIGN KEY (`project_item_id`) REFERENCES `project_item` (`id`),
  ADD CONSTRAINT `Refdatatrackingsubcontract38` FOREIGN KEY (`subcontractor_id`) REFERENCES `subcontractor` (`subcontractor_id`);

--
-- Filtros para la tabla `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `Refemployee1` FOREIGN KEY (`race_id`) REFERENCES `race` (`race_id`),
  ADD CONSTRAINT `fk_employee_role` FOREIGN KEY (`role_id`) REFERENCES `employee_role` (`role_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `estimate`
--
ALTER TABLE `estimate`
  ADD CONSTRAINT `Refestimate1` FOREIGN KEY (`project_stage_id`) REFERENCES `project_stage` (`stage_id`),
  ADD CONSTRAINT `Refestimate2` FOREIGN KEY (`proposal_type_id`) REFERENCES `proposal_type` (`type_id`),
  ADD CONSTRAINT `Refestimate3` FOREIGN KEY (`status_id`) REFERENCES `plan_status` (`status_id`),
  ADD CONSTRAINT `Refestimate4` FOREIGN KEY (`district_id`) REFERENCES `district` (`district_id`),
  ADD CONSTRAINT `Refestimate5` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `Refestimate6` FOREIGN KEY (`contact_id`) REFERENCES `company_contact` (`contact_id`),
  ADD CONSTRAINT `Refestimate7` FOREIGN KEY (`plan_downloading_id`) REFERENCES `plan_downloading` (`plan_downloading_id`),
  ADD CONSTRAINT `Refestimatecountyid` FOREIGN KEY (`county_id`) REFERENCES `county` (`county_id`);

--
-- Filtros para la tabla `estimate_bid_deadline`
--
ALTER TABLE `estimate_bid_deadline`
  ADD CONSTRAINT `Refestimate_bid_dealine1` FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`),
  ADD CONSTRAINT `Refestimate_bid_dealine2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Filtros para la tabla `estimate_company`
--
ALTER TABLE `estimate_company`
  ADD CONSTRAINT `Refestimate_company1` FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`),
  ADD CONSTRAINT `Refestimate_company2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `Refestimate_company3` FOREIGN KEY (`contact_id`) REFERENCES `company_contact` (`contact_id`);

--
-- Filtros para la tabla `estimate_estimator`
--
ALTER TABLE `estimate_estimator`
  ADD CONSTRAINT `Refestimate_estimator1` FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`),
  ADD CONSTRAINT `Refestimate_estimator2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Filtros para la tabla `estimate_project_type`
--
ALTER TABLE `estimate_project_type`
  ADD CONSTRAINT `Refestimate_project_type1` FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`),
  ADD CONSTRAINT `Refestimate_project_type2` FOREIGN KEY (`type_id`) REFERENCES `project_type` (`type_id`);

--
-- Filtros para la tabla `estimate_quote`
--
ALTER TABLE `estimate_quote`
  ADD CONSTRAINT `Refestimate_quote1` FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`),
  ADD CONSTRAINT `Refestimate_quote2` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`),
  ADD CONSTRAINT `Refestimate_quote3` FOREIGN KEY (`equation_id`) REFERENCES `equation` (`equation_id`);

--
-- Filtros para la tabla `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `Refproject65` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `invoice_attachment`
--
ALTER TABLE `invoice_attachment`
  ADD CONSTRAINT `Refinvoice_attachment1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`);

--
-- Filtros para la tabla `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD CONSTRAINT `Refinvoice69` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`),
  ADD CONSTRAINT `Refprojectitem26` FOREIGN KEY (`project_item_id`) REFERENCES `project_item` (`id`);

--
-- Filtros para la tabla `invoice_item_notes`
--
ALTER TABLE `invoice_item_notes`
  ADD CONSTRAINT `Refinvoice_item_notes1` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_item` (`id`);

--
-- Filtros para la tabla `invoice_notes`
--
ALTER TABLE `invoice_notes`
  ADD CONSTRAINT `Refinvoice_notes1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`);

--
-- Filtros para la tabla `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `Refequation10` FOREIGN KEY (`equation_id`) REFERENCES `equation` (`equation_id`),
  ADD CONSTRAINT `Refunit64` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`unit_id`);

--
-- Filtros para la tabla `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `Refuser434` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Filtros para la tabla `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `fk_material_unit` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`unit_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `Refuser77` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Filtros para la tabla `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `Refconcreteclassid` FOREIGN KEY (`concrete_class_id`) REFERENCES `concrete_class` (`concrete_class_id`),
  ADD CONSTRAINT `Refconcretevendorid` FOREIGN KEY (`vendor_id`) REFERENCES `concrete_vendor` (`vendor_id`),
  ADD CONSTRAINT `Refcontractor67` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `Refinspector73` FOREIGN KEY (`inspector_id`) REFERENCES `inspector` (`inspector_id`),
  ADD CONSTRAINT `Refprojectcountyid` FOREIGN KEY (`county_id`) REFERENCES `county` (`county_id`),
  ADD CONSTRAINT `Refprojectprevailingcountyid` FOREIGN KEY (`prevailing_county_id`) REFERENCES `county` (`county_id`),
  ADD CONSTRAINT `Refprojectprevailingroleid` FOREIGN KEY (`prevailing_role_id`) REFERENCES `employee_role` (`role_id`);

--
-- Filtros para la tabla `project_attachment`
--
ALTER TABLE `project_attachment`
  ADD CONSTRAINT `Refproject_attachment1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `project_concrete_class`
--
ALTER TABLE `project_concrete_class`
  ADD CONSTRAINT `Refprojectconcreteclassclassid` FOREIGN KEY (`concrete_class_id`) REFERENCES `concrete_class` (`concrete_class_id`),
  ADD CONSTRAINT `Refprojectconcreteclassprojectid` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `project_contact`
--
ALTER TABLE `project_contact`
  ADD CONSTRAINT `Refcontractor75` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `project_county`
--
ALTER TABLE `project_county`
  ADD CONSTRAINT `Refprojectcountycountyid` FOREIGN KEY (`county_id`) REFERENCES `county` (`county_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Refprojectcountyprojectid` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `project_item`
--
ALTER TABLE `project_item`
  ADD CONSTRAINT `Refequation11` FOREIGN KEY (`equation_id`) REFERENCES `equation` (`equation_id`),
  ADD CONSTRAINT `Refitem80` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`),
  ADD CONSTRAINT `Refproject79` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `project_item_history`
--
ALTER TABLE `project_item_history`
  ADD CONSTRAINT `project_item_history_ibfk_1` FOREIGN KEY (`project_item_id`) REFERENCES `project_item` (`id`),
  ADD CONSTRAINT `project_item_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Filtros para la tabla `project_notes`
--
ALTER TABLE `project_notes`
  ADD CONSTRAINT `Refproject78` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `project_price_adjustment`
--
ALTER TABLE `project_price_adjustment`
  ADD CONSTRAINT `Refproject_price_adjustment1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `reminder_recipient`
--
ALTER TABLE `reminder_recipient`
  ADD CONSTRAINT `Refreminderrecipient1` FOREIGN KEY (`reminder_id`) REFERENCES `reminder` (`reminder_id`),
  ADD CONSTRAINT `Refreminderrecipient2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Filtros para la tabla `rol_permission`
--
ALTER TABLE `rol_permission`
  ADD CONSTRAINT `Reffunction2` FOREIGN KEY (`function_id`) REFERENCES `function` (`function_id`),
  ADD CONSTRAINT `Refrol1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`rol_id`);

--
-- Filtros para la tabla `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `Refscheduleconcvendorid` FOREIGN KEY (`vendor_id`) REFERENCES `concrete_vendor` (`vendor_id`),
  ADD CONSTRAINT `Refscheduleemployeeid` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `Refscheduleprojectcontactid` FOREIGN KEY (`project_contact_id`) REFERENCES `project_contact` (`contact_id`),
  ADD CONSTRAINT `Refscheduleprojectid` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `schedule_concrete_vendor_contact`
--
ALTER TABLE `schedule_concrete_vendor_contact`
  ADD CONSTRAINT `Refs_chedule_concrete_vendor_contacts_cheduleid` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`schedule_id`),
  ADD CONSTRAINT `Refs_chedule_concrete_vendor_contacts_contactid` FOREIGN KEY (`contact_id`) REFERENCES `concrete_vendor_contact` (`contact_id`);

--
-- Filtros para la tabla `schedule_employee`
--
ALTER TABLE `schedule_employee`
  ADD CONSTRAINT `Refschedule_employee1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`schedule_id`),
  ADD CONSTRAINT `Refschedule_employee2` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`);

--
-- Filtros para la tabla `subcontractor_employee`
--
ALTER TABLE `subcontractor_employee`
  ADD CONSTRAINT `Refsubcontractor35` FOREIGN KEY (`subcontractor_id`) REFERENCES `subcontractor` (`subcontractor_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `subcontractor_notes`
--
ALTER TABLE `subcontractor_notes`
  ADD CONSTRAINT `Refsubcontractor36` FOREIGN KEY (`subcontractor_id`) REFERENCES `subcontractor` (`subcontractor_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `Refrol6` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`rol_id`);

--
-- Filtros para la tabla `user_access_token`
--
ALTER TABLE `user_access_token`
  ADD CONSTRAINT `fk_user_access_token_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_permission`
--
ALTER TABLE `user_permission`
  ADD CONSTRAINT `Reffunction4` FOREIGN KEY (`function_id`) REFERENCES `function` (`function_id`),
  ADD CONSTRAINT `Refuser3` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Filtros para la tabla `user_qbwc_token`
--
ALTER TABLE `user_qbwc_token`
  ADD CONSTRAINT `Refuser_qbwc_token1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
