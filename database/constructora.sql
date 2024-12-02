-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 23-11-2024 a las 16:50:47
-- Versión del servidor: 5.7.44
-- Versión de PHP: 8.1.29

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
  `total_people` decimal(18,2) NOT NULL,
  `overhead_price` decimal(18,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `inspector_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `data_tracking`
--

INSERT INTO `data_tracking` (`id`, `date`, `station_number`, `measured_by`, `conc_vendor`, `crew_lead`, `notes`, `other_materials`, `total_conc_used`, `conc_price`, `total_stamps`, `total_people`, `overhead_price`, `created_at`, `updated_at`, `project_id`, `inspector_id`) VALUES
(3, '2024-08-31', '45453', 'Marcel', NULL, '', '', '', NULL, NULL, 0.00, 0.00, 0.00, '2024-06-23 21:07:27', '2024-11-22 19:14:41', 2, NULL),
(4, '2024-06-11', '435435', 'Marcel', 'CMP', '', '', '', 20.00, 100.00, 0.00, 40.00, 3500.00, '2024-06-23 21:16:44', '2024-08-10 19:15:28', 3, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_conc_vendor`
--

CREATE TABLE `data_tracking_conc_vendor` (
  `id` int(11) NOT NULL,
  `conc_vendor` varchar(255) DEFAULT NULL,
  `total_conc_used` decimal(18,2) DEFAULT NULL,
  `conc_price` decimal(18,2) DEFAULT NULL,
  `data_tracking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_item`
--

CREATE TABLE `data_tracking_item` (
  `id` int(11) NOT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `data_tracking_id` int(11) DEFAULT NULL,
  `project_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `data_tracking_item`
--

INSERT INTO `data_tracking_item` (`id`, `quantity`, `price`, `data_tracking_id`, `project_item_id`) VALUES
(8, 40.000000, 160.00, 3, 11),
(9, 50.000000, 200.00, 3, 12),
(11, 50.000000, 16.50, 4, 1),
(12, 30.000000, 63.00, 4, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `data_tracking_labor`
--

CREATE TABLE `data_tracking_labor` (
  `id` int(11) NOT NULL,
  `hours` decimal(18,2) DEFAULT NULL,
  `hourly_rate` decimal(18,2) DEFAULT NULL,
  `data_tracking_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `data_tracking_labor`
--

INSERT INTO `data_tracking_labor` (`id`, `hours`, `hourly_rate`, `data_tracking_id`, `employee_id`) VALUES
(1, 20.00, 56.00, 4, 1),
(3, 5.00, 70.00, 3, 2),
(4, 10.00, 56.00, 3, 1);

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
(1, 30.00, 5000.00, 4, 1),
(3, 5.00, 5000.00, 3, 1),
(4, 10.00, 500.00, 3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hourly_rate` float(8,2) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `employee`
--

INSERT INTO `employee` (`employee_id`, `name`, `hourly_rate`, `position`) VALUES
(1, 'Marcel Curbelo Carmona', 56.00, 'Gerente'),
(2, 'Andres Iglesias', 70.00, 'Developer');

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
(15, 'materials', 'Materials');

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
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `invoice`
--

INSERT INTO `invoice` (`invoice_id`, `number`, `start_date`, `end_date`, `notes`, `created_at`, `updated_at`, `project_id`) VALUES
(1, '1', '2024-08-01', '2024-08-31', '', '2024-07-07 19:19:29', '2024-08-31 16:37:43', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_item`
--

CREATE TABLE `invoice_item` (
  `id` int(11) NOT NULL,
  `quantity_from_previous` decimal(18,6) DEFAULT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `project_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `invoice_item`
--

INSERT INTO `invoice_item` (`id`, `quantity_from_previous`, `quantity`, `price`, `invoice_id`, `project_item_id`) VALUES
(1, NULL, 40.000000, 160.00, 1, 11),
(2, NULL, 50.000000, 200.00, 1, 12),
(3, NULL, 30.000000, 300.00, 1, 13),
(4, 40.000000, 40.000000, 160.00, 1, 11),
(5, 50.000000, 50.000000, 200.00, 1, 12),
(6, 30.000000, NULL, 300.00, 1, 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` float(8,2) DEFAULT NULL,
  `yield_calculation` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `equation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `item`
--

INSERT INTO `item` (`item_id`, `description`, `price`, `yield_calculation`, `status`, `created_at`, `updated_at`, `unit_id`, `equation_id`) VALUES
(1, 'CONC MEDIAN 4IN', 29.00, NULL, 1, '2024-04-12 20:18:17', NULL, 1, NULL),
(2, 'CONC MEDIAN 6IN', 70.00, NULL, 1, '2024-04-12 20:18:40', NULL, 1, NULL),
(3, 'CONCRETE V GUTTER', 25.00, NULL, 1, '2024-04-12 20:19:00', NULL, 2, NULL),
(4, 'CONC VALLEY GUTTER 6IN', 58.00, NULL, 1, '2024-04-12 20:19:26', NULL, 1, NULL),
(5, 'CONC VALLEY GUTTER 8IN', 77.00, NULL, 1, '2024-04-12 20:19:51', NULL, 1, NULL),
(6, 'CONC CURB & GUTTEER 8INX30IN TP2', 16.50, NULL, 1, '2024-04-12 20:20:29', NULL, 2, NULL),
(7, 'CONC CURB & GUTTEER 8INX30IN TP7', 16.50, NULL, 1, '2024-04-12 20:22:07', NULL, 2, NULL),
(8, 'CLASS B CONCRETE', 700.00, NULL, 1, '2024-04-12 20:22:31', NULL, 3, NULL),
(9, 'CLASS B CONCRETE, INCL REINF STEEL', 0.00, NULL, 1, '2024-04-12 20:23:08', NULL, 3, NULL),
(10, 'CLASS B CONC, BASE OR PVMT WIDENING', 253.00, NULL, 1, '2024-04-12 20:23:35', NULL, 3, NULL),
(11, 'BAR REINF. STEEL ', 0.00, NULL, 1, '2024-04-12 20:23:52', NULL, 4, NULL),
(12, 'CONC DRIVEWAY 8IN', 70.00, NULL, 1, '2024-04-12 20:24:09', NULL, 1, NULL),
(13, 'CONC SLOPE DRAIN ', 100.00, NULL, 1, '2024-04-12 20:24:29', NULL, 1, NULL),
(14, 'CONC SIDEWALK 4IN', 30.00, NULL, 1, '2024-04-12 20:25:08', NULL, 1, NULL),
(15, 'CONC SIDEWALK 8IN', 63.00, NULL, 1, '2024-04-12 20:25:30', NULL, 1, NULL),
(16, 'CONC SPILLWAY TP3', 2100.00, 'none', 1, '2024-04-12 20:25:50', '2024-05-12 19:18:34', 5, NULL),
(17, 'PLAIN CONC DITCH PAVING', 47.18, 'equation', 1, '2024-04-12 20:26:54', '2024-05-12 19:18:15', 1, 2),
(18, 'EXTRA CONCRETE', 208.00, NULL, 1, '2024-04-12 20:27:17', '2024-05-12 19:18:05', 3, NULL),
(19, 'EXTRA LABOR', 1500.00, 'same', 1, '2024-04-12 20:27:37', '2024-05-12 19:17:54', 6, NULL),
(20, 'Cubic Yards of Concrete', 150.00, 'none', 1, '2024-04-12 20:28:15', '2024-06-21 18:34:07', 3, NULL);

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
(242, 'Delete', 'Data Tracking', 'The conc vendor of the data tracking is deleted, Conc Vendor: Disrupsoft, Project: 0009002 - FL MIAMI, Date: 08/31/2024', '::1', '2024-11-22 19:14:58', 1);

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
(1, 'Material 1', 5000.00, 3),
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
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `notification`
--

INSERT INTO `notification` (`id`, `content`, `readed`, `created_at`, `user_id`) VALUES
(2, 'Generate april invoice', 1, '2024-04-16 13:23:00', 1);

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
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `inspector_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `project`
--

INSERT INTO `project` (`project_id`, `project_id_number`, `project_number`, `proposal_number`, `name`, `location`, `owner`, `subcontract`, `contract_amount`, `federal_funding`, `county`, `resurfacing`, `invoice_contact`, `certified_payrolls`, `start_date`, `end_date`, `due_date`, `manager`, `status`, `po_number`, `po_cg`, `created_at`, `updated_at`, `company_id`, `inspector_id`) VALUES
(1, NULL, '0009001', NULL, 'FL COUNTY', 'FL COUNTY', '', '', NULL, 0, '', 0, '', 0, NULL, NULL, '2024-05-31', 'Andres', 0, 'B3C210052148-0', 'ERS025', '2024-04-14 20:24:53', '2024-05-14 15:52:54', 1, 1),
(2, NULL, '0009002', NULL, 'FL MIAMI', 'FL MIAMI', '', '', NULL, 0, '', 0, '', 0, NULL, NULL, '2024-05-28', 'Dan', 1, '896532', '896532', '2024-04-24 04:20:22', '2024-06-23 21:06:22', 1, 1),
(3, '3243545', '0009003', '434354', 'Houston Texas', NULL, 'Marcel', '896532', 844500.00, 1, 'Miami', 1, 'Marcel Curbelo Carmona', 1, '2024-11-06', '2024-11-29', '2024-05-30', 'Marcel', 2, NULL, NULL, '2024-04-24 04:24:02', '2024-11-08 02:01:19', 3, 1);

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
(1, 'Marcel Curbelo Carmona', 'cyborgmnk@gmail.com', '(955)383-3543', 'Master', 'dfsd fdsf ', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_item`
--

CREATE TABLE `project_item` (
  `id` int(11) NOT NULL,
  `quantity` decimal(18,6) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `yield_calculation` varchar(50) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `equation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `project_item`
--

INSERT INTO `project_item` (`id`, `quantity`, `price`, `yield_calculation`, `project_id`, `item_id`, `equation_id`) VALUES
(1, 1500.000000, 16.50, 'equation', 3, 6, 2),
(2, 2000.000000, 63.00, 'same', 3, 15, NULL),
(4, 1600.000000, 150.00, 'equation', 3, 20, 2),
(5, NULL, 253.00, 'none', 1, 10, NULL),
(8, 2500.000000, 25.00, 'equation', 3, 3, 2),
(9, 2500.000000, 16.50, 'equation', 3, 7, 2),
(10, 5000.000000, 70.00, 'equation', 3, 12, 2),
(11, 50.000000, 160.00, 'none', 2, 12, NULL),
(12, 60.000000, 200.00, '', 2, 6, NULL),
(13, 50.000000, 300.00, '', 2, 7, NULL);

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
(9, 'Change start date, old value: 08/01/2023', '2024-11-08', 3);

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
(27, 1, 1, 1, 1, 1, 15);

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
  `phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `rol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`user_id`, `name`, `lastname`, `email`, `password`, `status`, `phone`, `created_at`, `updated_at`, `rol_id`) VALUES
(1, 'Administrator', 'Concrete', 'admin@concrete.com', '$2y$12$ojiMWHh/4xuvv0D8JdpY7OnlBd5TuYTW76SyWlR5QNbOAgtBt64dy', 1, '', '2024-04-12 09:24:44', '2024-04-12 18:37:27', 1);

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
(19, 1, 1, 1, 1, 1, 15);

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `data_tracking`
--
ALTER TABLE `data_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inspector_id` (`inspector_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indices de la tabla `data_tracking_conc_vendor`
--
ALTER TABLE `data_tracking_conc_vendor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref6345` (`data_tracking_id`);

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
  ADD KEY `fk_data_tracking_labor_employee` (`employee_id`);

--
-- Indices de la tabla `data_tracking_material`
--
ALTER TABLE `data_tracking_material`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_data_tracking_material` (`data_tracking_id`),
  ADD KEY `fk_data_tracking_material_2` (`material_id`);

--
-- Indices de la tabla `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indices de la tabla `equation`
--
ALTER TABLE `equation`
  ADD PRIMARY KEY (`equation_id`);

--
-- Indices de la tabla `function`
--
ALTER TABLE `function`
  ADD PRIMARY KEY (`function_id`);

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
-- Indices de la tabla `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `Ref6969` (`invoice_id`),
  ADD KEY `project_item_id` (`project_item_id`);

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
-- Indices de la tabla `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `Ref6467` (`company_id`),
  ADD KEY `Ref6573` (`inspector_id`);

--
-- Indices de la tabla `project_contact`
--
ALTER TABLE `project_contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `Ref6475` (`project_id`);

--
-- Indices de la tabla `project_item`
--
ALTER TABLE `project_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref6679` (`project_id`),
  ADD KEY `Ref6780` (`item_id`),
  ADD KEY `equation_id` (`equation_id`);

--
-- Indices de la tabla `project_notes`
--
ALTER TABLE `project_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref6678` (`project_id`);

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
-- Indices de la tabla `user_permission`
--
ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ref133` (`user_id`),
  ADD KEY `Ref194` (`function_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

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
-- AUTO_INCREMENT de la tabla `data_tracking`
--
ALTER TABLE `data_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `data_tracking_conc_vendor`
--
ALTER TABLE `data_tracking_conc_vendor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `data_tracking_item`
--
ALTER TABLE `data_tracking_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `data_tracking_labor`
--
ALTER TABLE `data_tracking_labor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `data_tracking_material`
--
ALTER TABLE `data_tracking_material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `equation`
--
ALTER TABLE `equation`
  MODIFY `equation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `function`
--
ALTER TABLE `function`
  MODIFY `function_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `inspector`
--
ALTER TABLE `inspector`
  MODIFY `inspector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `invoice_item`
--
ALTER TABLE `invoice_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `log`
--
ALTER TABLE `log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT de la tabla `material`
--
ALTER TABLE `material`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `project_contact`
--
ALTER TABLE `project_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `project_item`
--
ALTER TABLE `project_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `project_notes`
--
ALTER TABLE `project_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `rol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `rol_permission`
--
ALTER TABLE `rol_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
-- AUTO_INCREMENT de la tabla `user_permission`
--
ALTER TABLE `user_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `company_contact`
--
ALTER TABLE `company_contact`
  ADD CONSTRAINT `Refcontractor74` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Filtros para la tabla `data_tracking`
--
ALTER TABLE `data_tracking`
  ADD CONSTRAINT `Refinspector158` FOREIGN KEY (`inspector_id`) REFERENCES `inspector` (`inspector_id`),
  ADD CONSTRAINT `Refproject25` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `data_tracking_conc_vendor`
--
ALTER TABLE `data_tracking_conc_vendor`
  ADD CONSTRAINT `Refdatatrackingconcvendor35` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
  ADD CONSTRAINT `fk_data_tracking_labor_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `data_tracking_material`
--
ALTER TABLE `data_tracking_material`
  ADD CONSTRAINT `fk_data_tracking_material` FOREIGN KEY (`data_tracking_id`) REFERENCES `data_tracking` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_data_tracking_material_2` FOREIGN KEY (`material_id`) REFERENCES `material` (`material_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `Refproject65` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD CONSTRAINT `Refinvoice69` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`),
  ADD CONSTRAINT `Refprojectitem26` FOREIGN KEY (`project_item_id`) REFERENCES `project_item` (`id`);

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
  ADD CONSTRAINT `Refcontractor67` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `Refinspector73` FOREIGN KEY (`inspector_id`) REFERENCES `inspector` (`inspector_id`);

--
-- Filtros para la tabla `project_contact`
--
ALTER TABLE `project_contact`
  ADD CONSTRAINT `Refcontractor75` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `project_item`
--
ALTER TABLE `project_item`
  ADD CONSTRAINT `Refequation11` FOREIGN KEY (`equation_id`) REFERENCES `equation` (`equation_id`),
  ADD CONSTRAINT `Refitem80` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`),
  ADD CONSTRAINT `Refproject79` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `project_notes`
--
ALTER TABLE `project_notes`
  ADD CONSTRAINT `Refproject78` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Filtros para la tabla `rol_permission`
--
ALTER TABLE `rol_permission`
  ADD CONSTRAINT `Reffunction2` FOREIGN KEY (`function_id`) REFERENCES `function` (`function_id`),
  ADD CONSTRAINT `Refrol1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`rol_id`);

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `Refrol6` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`rol_id`);

--
-- Filtros para la tabla `user_permission`
--
ALTER TABLE `user_permission`
  ADD CONSTRAINT `Reffunction4` FOREIGN KEY (`function_id`) REFERENCES `function` (`function_id`),
  ADD CONSTRAINT `Refuser3` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
