-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 29, 2026 at 07:38 AM
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
-- Database: `crasm`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`id`, `name`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Michael Mama', 'michael', '$2y$10$LB/MdUBFozstfP9HUB.L4uDd43IYvEtS1Ud0kt7TlL2LE9ywjpYrS', '2026-08-17 03:04:14', '2026-08-19 03:13:51'),
(2, 'Michael Mama', 'admin', '$2y$10$VnoFar3.wamuCWVGshOzmeiSFnyaZYD2FXhJTrZZwTIvTNzyix2s.', '2026-08-19 05:55:49', '2026-08-19 05:55:49');

-- --------------------------------------------------------

--
-- Table structure for table `authority_records`
--

CREATE TABLE `authority_records` (
  `id` int(11) NOT NULL,
  `no` int(11) NOT NULL,
  `crasm_no` varchar(50) NOT NULL,
  `name_of_so` varchar(150) NOT NULL,
  `provinces` varchar(50) NOT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `status_type` varchar(20) DEFAULT NULL,
  `type` enum('New','Renewal') NOT NULL,
  `religious_sect` varchar(255) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `church_address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `filed` date DEFAULT NULL,
  `payment` date DEFAULT NULL,
  `received_in_rsso` date DEFAULT NULL,
  `processed` date DEFAULT NULL,
  `return_to_province_for_compliance` date DEFAULT NULL,
  `complied` date DEFAULT NULL,
  `received_in_rsso_after_compliance` date DEFAULT NULL,
  `complied_with_the_following` text DEFAULT NULL,
  `approved` date DEFAULT NULL,
  `transmitted_to_pso` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `authority_records`
--

INSERT INTO `authority_records` (`id`, `no`, `crasm_no`, `name_of_so`, `provinces`, `municipality`, `status_type`, `type`, `religious_sect`, `sex`, `church_address`, `contact_number`, `position`, `filed`, `payment`, `received_in_rsso`, `processed`, `return_to_province_for_compliance`, `complied`, `received_in_rsso_after_compliance`, `complied_with_the_following`, `approved`, `transmitted_to_pso`, `created_at`, `updated_at`) VALUES
(133, 3, 'Crasm-SAMPLE-003', 'Ana Lopez', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Roman Catholic', 'Male', 'Isulan', '0917000003', 'Deacon', '2026-01-02', '2026-01-02', '2026-01-05', '2026-01-09', NULL, NULL, NULL, NULL, '2026-01-22', '2026-01-24', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(134, 4, 'Crasm-SAMPLE-004', 'Jose Ramirez', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Roman Catholic', 'Female', 'M\'lang', '0917000004', 'Deaconess', '2026-01-03', '2026-01-03', '2026-01-06', '2026-01-10', NULL, NULL, NULL, NULL, '2026-01-23', '2026-01-25', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(135, 5, 'Crasm-SAMPLE-005', 'Grace Fernandez', 'Sarangani', 'General Santos City', NULL, 'New', 'Roman Catholic', 'Male', 'General Santos City', '0917000005', 'Evangelist', '2026-01-04', '2026-01-04', '2026-01-07', '2026-01-11', NULL, NULL, NULL, NULL, '2026-01-24', '2026-01-26', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(136, 6, 'Crasm-SAMPLE-006', 'Ricardo Gomez', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Kidapawan City', '0917000006', 'Missionary', '2026-01-05', '2026-01-05', '2026-01-08', '2026-01-12', NULL, NULL, NULL, NULL, '2026-01-25', '2026-01-27', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(137, 7, 'Crasm-SAMPLE-007', 'Elena Garcia', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Roman Catholic', 'Male', 'Alabel', '0917000007', 'Pastor', '2026-01-06', '2026-01-06', '2026-01-09', '2026-01-13', NULL, NULL, NULL, NULL, '2026-01-26', '2026-01-28', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(138, 8, 'Crasm-SAMPLE-008', 'Manuel Mendoza', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Koronadal City', '0917000008', 'Minister', '2026-01-07', '2026-01-07', '2026-01-10', '2026-01-14', NULL, NULL, NULL, NULL, '2026-01-27', '2026-01-29', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(139, 9, 'Crasm-SAMPLE-009', 'Luz Torres', 'Sarangani', 'Isulan', NULL, 'New', 'Roman Catholic', 'Male', 'Isulan', '0917000009', 'Elder', '2026-01-08', '2026-01-08', '2026-01-11', '2026-01-15', NULL, NULL, NULL, NULL, '2026-01-28', '2026-01-30', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(140, 10, 'Crasm-SAMPLE-010', 'Juan Dela Cruz', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Roman Catholic', 'Female', 'M\'lang', '0917000010', 'Deacon', '2026-01-09', '2026-01-09', '2026-01-12', '2026-01-16', NULL, NULL, NULL, NULL, '2026-01-29', '2026-01-31', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(141, 11, 'Crasm-SAMPLE-011', 'Maria Santos', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Roman Catholic', 'Male', 'General Santos City', '0917000011', 'Deaconess', '2026-01-10', '2026-01-10', '2026-01-13', '2026-01-17', NULL, NULL, NULL, NULL, '2026-01-30', '2026-02-01', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(142, 12, 'Crasm-SAMPLE-012', 'Pedro Reyes', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Kidapawan City', '0917000012', 'Evangelist', '2026-01-11', '2026-01-11', '2026-01-14', '2026-01-18', NULL, NULL, NULL, NULL, '2026-01-31', '2026-02-02', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(143, 13, 'Crasm-SAMPLE-013', 'Ana Lopez', 'Sarangani', 'Alabel', NULL, 'New', 'Roman Catholic', 'Male', 'Alabel', '0917000013', 'Missionary', '2026-01-12', '2026-01-12', '2026-01-15', '2026-01-19', NULL, NULL, NULL, NULL, '2026-02-01', '2026-02-03', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(144, 14, 'Crasm-SAMPLE-014', 'Jose Ramirez', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Koronadal City', '0917000014', 'Pastor', '2026-01-13', '2026-01-13', '2026-01-16', '2026-01-20', NULL, NULL, NULL, NULL, '2026-02-02', '2026-02-04', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(145, 15, 'Crasm-SAMPLE-015', 'Grace Fernandez', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Roman Catholic', 'Male', 'Isulan', '0917000015', 'Minister', '2026-01-14', '2026-01-14', '2026-01-17', '2026-01-21', NULL, NULL, NULL, NULL, '2026-02-03', '2026-02-05', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(146, 16, 'Crasm-SAMPLE-016', 'Ricardo Gomez', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Roman Catholic', 'Female', 'M\'lang', '0917000016', 'Elder', '2026-01-15', '2026-01-15', '2026-01-18', '2026-01-22', NULL, NULL, NULL, NULL, '2026-02-04', '2026-02-06', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(147, 17, 'Crasm-SAMPLE-017', 'Elena Garcia', 'Sarangani', 'General Santos City', NULL, 'New', 'Roman Catholic', 'Male', 'General Santos City', '0917000017', 'Deacon', '2026-01-16', '2026-01-16', '2026-01-19', '2026-01-23', NULL, NULL, NULL, NULL, '2026-02-05', '2026-02-07', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(148, 18, 'Crasm-SAMPLE-018', 'Manuel Mendoza', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Kidapawan City', '0917000018', 'Deaconess', '2026-01-17', '2026-01-17', '2026-01-20', '2026-01-24', NULL, NULL, NULL, NULL, '2026-02-06', '2026-02-08', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(149, 19, 'Crasm-SAMPLE-019', 'Luz Torres', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Roman Catholic', 'Male', 'Alabel', '0917000019', 'Evangelist', '2026-01-18', '2026-01-18', '2026-01-21', '2026-01-25', NULL, NULL, NULL, NULL, '2026-02-07', '2026-02-09', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(150, 20, 'Crasm-SAMPLE-020', 'Juan Dela Cruz', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Koronadal City', '0917000020', 'Missionary', '2026-01-19', '2026-01-19', '2026-01-22', '2026-01-26', NULL, NULL, NULL, NULL, '2026-02-08', '2026-02-10', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(151, 21, 'Crasm-SAMPLE-021', 'Maria Santos', 'Sarangani', 'Isulan', NULL, 'New', 'Roman Catholic', 'Male', 'Isulan', '0917000021', 'Pastor', '2026-01-20', '2026-01-20', '2026-01-23', '2026-01-27', NULL, NULL, NULL, NULL, '2026-02-09', '2026-02-11', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(152, 22, 'Crasm-SAMPLE-022', 'Pedro Reyes', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Roman Catholic', 'Female', 'M\'lang', '0917000022', 'Minister', '2026-01-21', '2026-01-21', '2026-01-24', '2026-01-28', NULL, NULL, NULL, NULL, '2026-02-10', '2026-02-12', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(153, 23, 'Crasm-SAMPLE-023', 'Ana Lopez', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Roman Catholic', 'Male', 'General Santos City', '0917000023', 'Elder', '2026-01-22', '2026-01-22', '2026-01-25', '2026-01-29', NULL, NULL, NULL, NULL, '2026-02-11', '2026-02-13', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(154, 24, 'Crasm-SAMPLE-024', 'Jose Ramirez', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Kidapawan City', '0917000024', 'Deacon', '2026-01-23', '2026-01-23', '2026-01-26', '2026-01-30', NULL, NULL, NULL, NULL, '2026-02-12', '2026-02-14', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(155, 25, 'Crasm-SAMPLE-025', 'Grace Fernandez', 'Sarangani', 'Alabel', NULL, 'New', 'Roman Catholic', 'Male', 'Alabel', '0917000025', 'Deaconess', '2026-01-24', '2026-01-24', '2026-01-27', '2026-01-31', NULL, NULL, NULL, NULL, '2026-02-13', '2026-02-15', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(156, 26, 'Crasm-SAMPLE-026', 'Ricardo Gomez', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Koronadal City', '0917000026', 'Evangelist', '2026-01-25', '2026-01-25', '2026-01-28', '2026-02-01', NULL, NULL, NULL, NULL, '2026-02-14', '2026-02-16', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(157, 27, 'Crasm-SAMPLE-027', 'Elena Garcia', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Roman Catholic', 'Male', 'Isulan', '0917000027', 'Missionary', '2026-01-26', '2026-01-26', '2026-01-29', '2026-02-02', NULL, NULL, NULL, NULL, '2026-02-15', '2026-02-17', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(158, 28, 'Crasm-SAMPLE-028', 'Manuel Mendoza', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Roman Catholic', 'Female', 'M\'lang', '0917000028', 'Pastor', '2026-01-27', '2026-01-27', '2026-01-30', '2026-02-03', NULL, NULL, NULL, NULL, '2026-02-16', '2026-02-18', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(159, 29, 'Crasm-SAMPLE-029', 'Luz Torres', 'Sarangani', 'General Santos City', NULL, 'New', 'Roman Catholic', 'Male', 'General Santos City', '0917000029', 'Minister', '2026-01-28', '2026-01-28', '2026-01-31', '2026-02-04', NULL, NULL, NULL, NULL, '2026-02-17', '2026-02-19', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(160, 30, 'Crasm-SAMPLE-030', 'Juan Dela Cruz', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Kidapawan City', '0917000030', 'Elder', '2026-01-29', '2026-01-29', '2026-02-01', '2026-02-05', NULL, NULL, NULL, NULL, '2026-02-18', '2026-02-20', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(161, 31, 'Crasm-SAMPLE-031', 'Maria Santos', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Roman Catholic', 'Male', 'Alabel', '0917000031', 'Deacon', '2026-01-30', '2026-01-30', '2026-02-02', '2026-02-06', NULL, NULL, NULL, NULL, '2026-02-19', '2026-02-21', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(162, 32, 'Crasm-SAMPLE-032', 'Pedro Reyes', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Koronadal City', '0917000032', 'Deaconess', '2026-01-31', '2026-01-31', '2026-02-03', '2026-02-07', NULL, NULL, NULL, NULL, '2026-02-20', '2026-02-22', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(163, 33, 'Crasm-SAMPLE-033', 'Ana Lopez', 'Sarangani', 'Isulan', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Isulan', '0917000033', 'Evangelist', '2026-02-01', '2026-02-01', '2026-02-04', '2026-02-08', NULL, NULL, NULL, NULL, '2026-02-21', '2026-02-23', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(164, 34, 'Crasm-SAMPLE-034', 'Jose Ramirez', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'M\'lang', '0917000034', 'Missionary', '2026-02-02', '2026-02-02', '2026-02-05', '2026-02-09', NULL, NULL, NULL, NULL, '2026-02-22', '2026-02-24', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(165, 35, 'Crasm-SAMPLE-035', 'Grace Fernandez', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'General Santos City', '0917000035', 'Pastor', '2026-02-03', '2026-02-03', '2026-02-06', '2026-02-10', NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-25', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(166, 36, 'Crasm-SAMPLE-036', 'Ricardo Gomez', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Kidapawan City', '0917000036', 'Minister', '2026-02-04', '2026-02-04', '2026-02-07', '2026-02-11', NULL, NULL, NULL, NULL, '2026-02-24', '2026-02-26', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(167, 37, 'Crasm-SAMPLE-037', 'Elena Garcia', 'Sarangani', 'Alabel', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Alabel', '0917000037', 'Elder', '2026-02-05', '2026-02-05', '2026-02-08', '2026-02-12', NULL, NULL, NULL, NULL, '2026-02-25', '2026-02-27', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(168, 38, 'Crasm-SAMPLE-038', 'Manuel Mendoza', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Koronadal City', '0917000038', 'Deacon', '2026-02-06', '2026-02-06', '2026-02-09', '2026-02-13', NULL, NULL, NULL, NULL, '2026-02-26', '2026-02-28', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(169, 39, 'Crasm-SAMPLE-039', 'Luz Torres', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Isulan', '0917000039', 'Deaconess', '2026-02-07', '2026-02-07', '2026-02-10', '2026-02-14', NULL, NULL, NULL, NULL, '2026-02-27', '2026-03-01', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(170, 40, 'Crasm-SAMPLE-040', 'Juan Dela Cruz', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'M\'lang', '0917000040', 'Evangelist', '2026-02-08', '2026-02-08', '2026-02-11', '2026-02-15', NULL, NULL, NULL, NULL, '2026-02-28', '2026-03-02', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(171, 41, 'Crasm-SAMPLE-041', 'Maria Santos', 'Sarangani', 'General Santos City', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'General Santos City', '0917000041', 'Missionary', '2026-02-09', '2026-02-09', '2026-02-12', '2026-02-16', NULL, NULL, NULL, NULL, '2026-03-01', '2026-03-03', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(172, 42, 'Crasm-SAMPLE-042', 'Pedro Reyes', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Kidapawan City', '0917000042', 'Pastor', '2026-02-10', '2026-02-10', '2026-02-13', '2026-02-17', NULL, NULL, NULL, NULL, '2026-03-02', '2026-03-04', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(173, 43, 'Crasm-SAMPLE-043', 'Ana Lopez', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Alabel', '0917000043', 'Minister', '2026-02-11', '2026-02-11', '2026-02-14', '2026-02-18', NULL, NULL, NULL, NULL, '2026-03-03', '2026-03-05', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(174, 44, 'Crasm-SAMPLE-044', 'Jose Ramirez', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Koronadal City', '0917000044', 'Elder', '2026-02-12', '2026-02-12', '2026-02-15', '2026-02-19', NULL, NULL, NULL, NULL, '2026-03-04', '2026-03-06', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(175, 45, 'Crasm-SAMPLE-045', 'Grace Fernandez', 'Sarangani', 'Isulan', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Isulan', '0917000045', 'Deacon', '2026-02-13', '2026-02-13', '2026-02-16', '2026-02-20', NULL, NULL, NULL, NULL, '2026-03-05', '2026-03-07', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(176, 46, 'Crasm-SAMPLE-046', 'Ricardo Gomez', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'M\'lang', '0917000046', 'Deaconess', '2026-02-14', '2026-02-14', '2026-02-17', '2026-02-21', NULL, NULL, NULL, NULL, '2026-03-06', '2026-03-08', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(177, 47, 'Crasm-SAMPLE-047', 'Elena Garcia', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'General Santos City', '0917000047', 'Evangelist', '2026-02-15', '2026-02-15', '2026-02-18', '2026-02-22', NULL, NULL, NULL, NULL, '2026-03-07', '2026-03-09', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(178, 48, 'Crasm-SAMPLE-048', 'Manuel Mendoza', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Kidapawan City', '0917000048', 'Missionary', '2026-02-16', '2026-02-16', '2026-02-19', '2026-02-23', NULL, NULL, NULL, NULL, '2026-03-08', '2026-03-10', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(179, 49, 'Crasm-SAMPLE-049', 'Luz Torres', 'Sarangani', 'Alabel', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Alabel', '0917000049', 'Pastor', '2026-02-17', '2026-02-17', '2026-02-20', '2026-02-24', NULL, NULL, NULL, NULL, '2026-03-09', '2026-03-11', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(180, 50, 'Crasm-SAMPLE-050', 'Juan Dela Cruz', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Koronadal City', '0917000050', 'Minister', '2026-02-18', '2026-02-18', '2026-02-21', '2026-02-25', NULL, NULL, NULL, NULL, '2026-03-10', '2026-03-12', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(181, 51, 'Crasm-SAMPLE-051', 'Maria Santos', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Isulan', '0917000051', 'Elder', '2026-02-19', '2026-02-19', '2026-02-22', '2026-02-26', NULL, NULL, NULL, NULL, '2026-03-11', '2026-03-13', '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(182, 52, 'Crasm-SAMPLE-052', 'Pedro Reyes', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Born Again Christian', 'Female', 'M\'lang', '0917000052', 'Deacon', '2026-02-20', '2026-02-20', '2026-02-23', '2026-02-27', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(183, 53, 'Crasm-SAMPLE-053', 'Ana Lopez', 'Sarangani', 'General Santos City', NULL, 'New', 'Born Again Christian', 'Male', 'General Santos City', '0917000053', 'Deaconess', '2026-02-21', '2026-02-21', '2026-02-24', '2026-02-28', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(184, 54, 'Crasm-SAMPLE-054', 'Jose Ramirez', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Kidapawan City', '0917000054', 'Evangelist', '2026-02-22', '2026-02-22', '2026-02-25', '2026-03-01', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(185, 55, 'Crasm-SAMPLE-055', 'Grace Fernandez', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Born Again Christian', 'Male', 'Alabel', '0917000055', 'Missionary', '2026-02-23', '2026-02-23', '2026-02-26', '2026-03-02', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(186, 56, 'Crasm-SAMPLE-056', 'Ricardo Gomez', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Koronadal City', '0917000056', 'Pastor', '2026-02-24', '2026-02-24', '2026-02-27', '2026-03-03', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(187, 57, 'Crasm-SAMPLE-057', 'Elena Garcia', 'Sarangani', 'Isulan', NULL, 'New', 'Born Again Christian', 'Male', 'Isulan', '0917000057', 'Minister', '2026-02-25', '2026-02-25', '2026-02-28', '2026-03-04', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(188, 58, 'Crasm-SAMPLE-058', 'Manuel Mendoza', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Born Again Christian', 'Female', 'M\'lang', '0917000058', 'Elder', '2026-02-26', '2026-02-26', '2026-03-01', '2026-03-05', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(189, 59, 'Crasm-SAMPLE-059', 'Luz Torres', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Born Again Christian', 'Male', 'General Santos City', '0917000059', 'Deacon', '2026-02-27', '2026-02-27', '2026-03-02', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(190, 60, 'Crasm-SAMPLE-060', 'Juan Dela Cruz', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Kidapawan City', '0917000060', 'Deaconess', '2026-02-28', '2026-02-28', '2026-03-03', '2026-03-07', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(191, 61, 'Crasm-SAMPLE-061', 'Maria Santos', 'Sarangani', 'Alabel', NULL, 'New', 'Born Again Christian', 'Male', 'Alabel', '0917000061', 'Evangelist', '2026-03-01', '2026-03-01', '2026-03-04', '2026-03-08', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(192, 62, 'Crasm-SAMPLE-062', 'Pedro Reyes', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Koronadal City', '0917000062', 'Missionary', '2026-03-02', '2026-03-02', '2026-03-05', '2026-03-09', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(193, 63, 'Crasm-SAMPLE-063', 'Ana Lopez', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Born Again Christian', 'Male', 'Isulan', '0917000063', 'Pastor', '2026-03-03', '2026-03-03', '2026-03-06', '2026-03-10', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(194, 64, 'Crasm-SAMPLE-064', 'Jose Ramirez', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Born Again Christian', 'Female', 'M\'lang', '0917000064', 'Minister', '2026-03-04', '2026-03-04', '2026-03-07', '2026-03-11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(195, 65, 'Crasm-SAMPLE-065', 'Grace Fernandez', 'Sarangani', 'General Santos City', NULL, 'New', 'Born Again Christian', 'Male', 'General Santos City', '0917000065', 'Elder', '2026-03-05', '2026-03-05', '2026-03-08', '2026-03-12', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(196, 66, 'Crasm-SAMPLE-066', 'Ricardo Gomez', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Kidapawan City', '0917000066', 'Deacon', '2026-03-06', '2026-03-06', '2026-03-09', '2026-03-13', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(197, 67, 'Crasm-SAMPLE-067', 'Elena Garcia', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Born Again Christian', 'Male', 'Alabel', '0917000067', 'Deaconess', '2026-03-07', '2026-03-07', '2026-03-10', '2026-03-14', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(198, 68, 'Crasm-SAMPLE-068', 'Manuel Mendoza', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Koronadal City', '0917000068', 'Evangelist', '2026-03-08', '2026-03-08', '2026-03-11', '2026-03-15', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(199, 69, 'Crasm-SAMPLE-069', 'Luz Torres', 'Sarangani', 'Isulan', NULL, 'New', 'Born Again Christian', 'Male', 'Isulan', '0917000069', 'Missionary', '2026-03-09', '2026-03-09', '2026-03-12', '2026-03-16', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(200, 70, 'Crasm-SAMPLE-070', 'Juan Dela Cruz', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Born Again Christian', 'Female', 'M\'lang', '0917000070', 'Pastor', '2026-03-10', '2026-03-10', '2026-03-13', '2026-03-17', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(201, 71, 'Crasm-SAMPLE-071', 'Maria Santos', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Born Again Christian', 'Male', 'General Santos City', '0917000071', 'Minister', '2026-03-11', '2026-03-11', '2026-03-14', '2026-03-18', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(202, 72, 'Crasm-SAMPLE-072', 'Pedro Reyes', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Seventh-day Adventist', 'Female', 'Kidapawan City', '0917000072', 'Elder', '2026-03-12', '2026-03-12', '2026-03-15', '2026-03-19', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(203, 73, 'Crasm-SAMPLE-073', 'Ana Lopez', 'Sarangani', 'Alabel', NULL, 'New', 'Seventh-day Adventist', 'Male', 'Alabel', '0917000073', 'Deacon', '2026-03-13', '2026-03-13', '2026-03-16', '2026-03-20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(204, 74, 'Crasm-SAMPLE-074', 'Jose Ramirez', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Seventh-day Adventist', 'Female', 'Koronadal City', '0917000074', 'Deaconess', '2026-03-14', '2026-03-14', '2026-03-17', '2026-03-21', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(205, 75, 'Crasm-SAMPLE-075', 'Grace Fernandez', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Seventh-day Adventist', 'Male', 'Isulan', '0917000075', 'Evangelist', '2026-03-15', '2026-03-15', '2026-03-18', '2026-03-22', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(206, 76, 'Crasm-SAMPLE-076', 'Ricardo Gomez', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Seventh-day Adventist', 'Female', 'M\'lang', '0917000076', 'Missionary', '2026-03-16', '2026-03-16', '2026-03-19', '2026-03-23', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(207, 77, 'Crasm-SAMPLE-077', 'Elena Garcia', 'Sarangani', 'General Santos City', NULL, 'New', 'Seventh-day Adventist', 'Male', 'General Santos City', '0917000077', 'Pastor', '2026-03-17', '2026-03-17', '2026-03-20', '2026-03-24', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(208, 78, 'Crasm-SAMPLE-078', 'Manuel Mendoza', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Seventh-day Adventist', 'Female', 'Kidapawan City', '0917000078', 'Minister', '2026-03-18', '2026-03-18', '2026-03-21', '2026-03-25', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(209, 79, 'Crasm-SAMPLE-079', 'Luz Torres', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Seventh-day Adventist', 'Male', 'Alabel', '0917000079', 'Elder', '2026-03-19', '2026-03-19', '2026-03-22', '2026-03-26', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(210, 80, 'Crasm-SAMPLE-080', 'Juan Dela Cruz', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Seventh-day Adventist', 'Female', 'Koronadal City', '0917000080', 'Deacon', '2026-03-20', '2026-03-20', '2026-03-23', '2026-03-27', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(211, 81, 'Crasm-SAMPLE-081', 'Maria Santos', 'Sarangani', 'Isulan', NULL, 'New', 'Seventh-day Adventist', 'Male', 'Isulan', '0917000081', 'Deaconess', '2026-03-21', '2026-03-21', '2026-03-24', '2026-03-28', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(212, 82, 'Crasm-SAMPLE-082', 'Pedro Reyes', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Pentecostal', 'Female', 'M\'lang', '0917000082', 'Evangelist', '2026-03-22', '2026-03-22', '2026-03-25', '2026-03-29', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(213, 83, 'Crasm-SAMPLE-083', 'Ana Lopez', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Pentecostal', 'Male', 'General Santos City', '0917000083', 'Missionary', '2026-03-23', '2026-03-23', '2026-03-26', '2026-03-30', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(214, 84, 'Crasm-SAMPLE-084', 'Jose Ramirez', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Pentecostal', 'Female', 'Kidapawan City', '0917000084', 'Pastor', '2026-03-24', '2026-03-24', '2026-03-27', '2026-03-31', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(215, 85, 'Crasm-SAMPLE-085', 'Grace Fernandez', 'Sarangani', 'Alabel', NULL, 'New', 'Pentecostal', 'Male', 'Alabel', '0917000085', 'Minister', '2026-03-25', '2026-03-25', '2026-03-28', '2026-04-01', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(216, 86, 'Crasm-SAMPLE-086', 'Ricardo Gomez', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Pentecostal', 'Female', 'Koronadal City', '0917000086', 'Elder', '2026-03-26', '2026-03-26', '2026-03-29', '2026-04-02', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(217, 87, 'Crasm-SAMPLE-087', 'Elena Garcia', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Pentecostal', 'Male', 'Isulan', '0917000087', 'Deacon', '2026-03-27', '2026-03-27', '2026-03-30', '2026-04-03', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(218, 88, 'Crasm-SAMPLE-088', 'Manuel Mendoza', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Pentecostal', 'Female', 'M\'lang', '0917000088', 'Deaconess', '2026-03-28', '2026-03-28', '2026-03-31', '2026-04-04', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(219, 89, 'Crasm-SAMPLE-089', 'Luz Torres', 'Sarangani', 'General Santos City', NULL, 'New', 'Pentecostal', 'Male', 'General Santos City', '0917000089', 'Evangelist', '2026-03-29', '2026-03-29', '2026-04-01', '2026-04-05', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(220, 90, 'Crasm-SAMPLE-090', 'Juan Dela Cruz', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Pentecostal', 'Female', 'Kidapawan City', '0917000090', 'Missionary', '2026-03-30', '2026-03-30', '2026-04-02', '2026-04-06', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(221, 91, 'Crasm-SAMPLE-091', 'Maria Santos', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Pentecostal', 'Male', 'Alabel', '0917000091', 'Pastor', '2026-03-31', '2026-03-31', '2026-04-03', '2026-04-07', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(222, 92, 'Crasm-SAMPLE-092', 'Pedro Reyes', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Baptist', 'Female', 'Koronadal City', '0917000092', 'Minister', '2026-04-01', '2026-04-01', '2026-04-04', '2026-04-08', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(223, 93, 'Crasm-SAMPLE-093', 'Ana Lopez', 'Sarangani', 'Isulan', NULL, 'New', 'Baptist', 'Male', 'Isulan', '0917000093', 'Elder', '2026-04-02', '2026-04-02', '2026-04-05', '2026-04-09', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(224, 94, 'Crasm-SAMPLE-094', 'Jose Ramirez', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Baptist', 'Female', 'M\'lang', '0917000094', 'Deacon', '2026-04-03', '2026-04-03', '2026-04-06', '2026-04-10', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(225, 95, 'Crasm-SAMPLE-095', 'Grace Fernandez', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Baptist', 'Male', 'General Santos City', '0917000095', 'Deaconess', '2026-04-04', '2026-04-04', '2026-04-07', '2026-04-11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(226, 96, 'Crasm-SAMPLE-096', 'Ricardo Gomez', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Baptist', 'Female', 'Kidapawan City', '0917000096', 'Evangelist', '2026-04-05', '2026-04-05', '2026-04-08', '2026-04-12', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(227, 97, 'Crasm-SAMPLE-097', 'Elena Garcia', 'Sarangani', 'Alabel', NULL, 'New', 'Jehovah\'s Witnesses', 'Male', 'Alabel', '0917000097', 'Missionary', '2026-04-06', '2026-04-06', '2026-04-09', '2026-04-13', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(228, 98, 'Crasm-SAMPLE-098', 'Manuel Mendoza', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Jehovah\'s Witnesses', 'Female', 'Koronadal City', '0917000098', 'Pastor', '2026-04-07', '2026-04-07', '2026-04-10', '2026-04-14', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(229, 99, 'Crasm-SAMPLE-099', 'Luz Torres', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Jehovah\'s Witnesses', 'Male', 'Isulan', '0917000099', 'Minister', '2026-04-08', '2026-04-08', '2026-04-11', '2026-04-15', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(230, 100, 'Crasm-SAMPLE-100', 'Juan Dela Cruz', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Jehovah\'s Witnesses', 'Female', 'M\'lang', '0917000100', 'Elder', '2026-04-09', '2026-04-09', '2026-04-12', '2026-04-16', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(231, 101, 'Crasm-SAMPLE-101', 'Maria Santos', 'Sarangani', 'General Santos City', NULL, 'New', 'Aglipayan', 'Male', 'General Santos City', '0917000101', 'Deacon', '2026-04-10', '2026-04-10', '2026-04-13', '2026-04-17', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(232, 102, 'Crasm-SAMPLE-102', 'Pedro Reyes', 'South Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Aglipayan', 'Female', 'Kidapawan City', '0917000102', 'Deaconess', '2026-04-11', '2026-04-11', '2026-04-14', '2026-04-18', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(233, 103, 'Crasm-SAMPLE-103', 'Ana Lopez', 'Sultan Kudarat', 'Alabel', NULL, 'New', 'Aglipayan', 'Male', 'Alabel', '0917000103', 'Evangelist', '2026-04-12', '2026-04-12', '2026-04-15', '2026-04-19', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(234, 104, 'Crasm-SAMPLE-104', 'Jose Ramirez', 'Cotabato', 'Koronadal City', NULL, 'Renewal', 'Methodist', 'Female', 'Koronadal City', '0917000104', 'Missionary', '2026-04-13', '2026-04-13', '2026-04-16', '2026-04-20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(235, 105, 'Crasm-SAMPLE-105', 'Grace Fernandez', 'Sarangani', 'Isulan', NULL, 'New', 'Methodist', 'Male', 'Isulan', '0917000105', 'Pastor', '2026-04-14', '2026-04-14', '2026-04-17', '2026-04-21', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(236, 106, 'Crasm-SAMPLE-106', 'Ricardo Gomez', 'South Cotabato', 'M\'lang', NULL, 'Renewal', 'Methodist', 'Female', 'M\'lang', '0917000106', 'Minister', '2026-04-15', '2026-04-15', '2026-04-18', '2026-04-22', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(237, 107, 'Crasm-SAMPLE-107', 'Elena Garcia', 'Sultan Kudarat', 'General Santos City', NULL, 'New', 'Latter-day Saints', 'Male', 'General Santos City', '0917000107', 'Elder', '2026-04-16', '2026-04-16', '2026-04-19', '2026-04-23', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(238, 108, 'Crasm-SAMPLE-108', 'Manuel Mendoza', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Latter-day Saints', 'Female', 'Kidapawan City', '0917000108', 'Deacon', '2026-04-17', '2026-04-17', '2026-04-20', '2026-04-24', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 04:56:44', '2026-08-19 04:56:44'),
(300, 240, 'Crasm-2024-0239', 'Ricardo Ramirez', 'Cotabato', 'Midsayap', NULL, 'New', 'Methodist', 'Male', 'Midsayap', '09804036506', 'Pastor', '2024-02-24', '2024-02-24', '2024-02-26', '2024-02-29', NULL, NULL, NULL, NULL, NULL, NULL, '2024-02-24 00:00:00', '2024-02-24 00:00:00'),
(301, 241, 'Crasm-2024-0240', 'Eduardo Fernandez', 'Sarangani', 'Malapatan', NULL, 'Renewal', 'Seventh-day Adventist', 'Male', 'Malapatan', '09802719211', 'Deaconess', '2024-02-05', '2024-02-05', '2024-02-07', '2024-02-11', NULL, NULL, NULL, NULL, '2024-02-26', '2024-03-01', '2024-02-05 00:00:00', '2024-02-05 00:00:00'),
(302, 242, 'Crasm-2024-0241', 'Ana Lopez', 'Sarangani', 'Glan', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Glan', '09539319644', 'Deacon', '2024-03-18', '2024-03-18', '2024-03-22', '2024-03-25', NULL, NULL, NULL, NULL, '2024-04-12', '2024-04-14', '2024-03-18 00:00:00', '2024-03-18 00:00:00'),
(303, 243, 'Crasm-2024-0242', 'Beatriz Garcia', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Iglesia ni Cristo', 'Female', 'Isulan', '09880026086', 'Pastor', '2024-01-30', '2024-01-30', '2024-02-03', '2024-02-06', NULL, NULL, NULL, NULL, '2024-02-17', '2024-02-22', '2024-01-30 00:00:00', '2024-01-30 00:00:00'),
(304, 244, 'Crasm-2024-0243', 'Grace Garcia', 'South Cotabato', 'Surallah', NULL, 'Renewal', 'Seventh-day Adventist', 'Female', 'Surallah', '09456665249', 'Evangelist', '2024-03-28', '2024-03-28', '2024-03-30', '2024-04-06', NULL, NULL, NULL, NULL, '2024-04-24', '2024-04-27', '2024-03-28 00:00:00', '2024-03-28 00:00:00'),
(305, 245, 'Crasm-2024-0244', 'Luz Villanueva', 'Sarangani', 'Maasim', NULL, 'New', 'Baptist', 'Female', 'Maasim', '09994970419', 'Missionary', '2024-01-08', '2024-01-08', '2024-01-11', '2024-01-14', NULL, NULL, NULL, NULL, '2024-01-30', '2024-02-03', '2024-01-08 00:00:00', '2024-01-08 00:00:00'),
(306, 246, 'Crasm-2024-0245', 'Elena Villanueva', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Jehovah\'s Witnesses', 'Female', 'M\'lang', '09662688426', 'Evangelist', '2024-01-19', '2024-01-19', '2024-01-23', '2024-01-27', '2024-02-03', NULL, NULL, NULL, NULL, NULL, '2024-01-19 00:00:00', '2024-01-19 00:00:00'),
(307, 247, 'Crasm-2024-0246', 'Rosa Fernandez', 'Sultan Kudarat', 'Esperanza', NULL, 'New', 'Methodist', 'Female', 'Esperanza', '09267613238', 'Deaconess', '2024-01-07', '2024-01-07', '2024-01-09', '2024-01-13', NULL, NULL, NULL, NULL, '2024-02-02', '2024-02-07', '2024-01-07 00:00:00', '2024-01-07 00:00:00'),
(308, 248, 'Crasm-2024-0247', 'Beatriz Torres', 'Cotabato', 'Makilala', NULL, 'Renewal', 'Methodist', 'Female', 'Makilala', '09182327652', 'Missionary', '2024-03-28', '2024-03-28', '2024-03-30', '2024-04-07', NULL, NULL, NULL, NULL, '2024-04-21', '2024-04-25', '2024-03-28 00:00:00', '2024-03-28 00:00:00'),
(309, 249, 'Crasm-2024-0248', 'Carlos Reyes', 'South Cotabato', 'Surallah', NULL, 'Renewal', 'Methodist', 'Male', 'Surallah', '09361825997', 'Missionary', '2024-06-04', '2024-06-04', '2024-06-06', '2024-06-14', '2024-06-20', NULL, NULL, NULL, NULL, NULL, '2024-06-04 00:00:00', '2024-06-04 00:00:00'),
(310, 250, 'Crasm-2024-0249', 'Eduardo Bautista', 'Sarangani', 'Kiamba', NULL, 'New', 'Latter-day Saints', 'Male', 'Kiamba', '09694636385', 'Deacon', '2024-04-03', '2024-04-03', '2024-04-05', '2024-04-10', NULL, NULL, NULL, NULL, '2024-04-24', '2024-04-27', '2024-04-03 00:00:00', '2024-04-03 00:00:00'),
(311, 251, 'Crasm-2024-0250', 'Pedro Aquino', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Iglesia ni Cristo', 'Male', 'M\'lang', '09741988889', 'Missionary', '2024-04-17', '2024-04-17', '2024-04-20', '2024-04-28', NULL, NULL, NULL, NULL, '2024-05-16', '2024-05-19', '2024-04-17 00:00:00', '2024-04-17 00:00:00'),
(312, 252, 'Crasm-2024-0251', 'Elena Dela Cruz', 'South Cotabato', 'Banga', NULL, 'New', 'Pentecostal', 'Female', 'Banga', '09891218382', 'Deaconess', '2024-06-23', '2024-06-23', '2024-06-27', '2024-07-03', NULL, NULL, NULL, NULL, '2024-07-20', '2024-07-22', '2024-06-23 00:00:00', '2024-06-23 00:00:00'),
(313, 253, 'Crasm-2024-0252', 'Antonio Reyes', 'Sarangani', 'Glan', NULL, 'New', 'Latter-day Saints', 'Male', 'Glan', '09177721109', 'Elder', '2024-04-10', '2024-04-10', '2024-04-12', '2024-04-16', NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-10 00:00:00', '2024-04-10 00:00:00'),
(314, 254, 'Crasm-2024-0253', 'Eduardo Fernandez', 'Cotabato', 'Midsayap', NULL, 'Renewal', 'Aglipayan', 'Male', 'Midsayap', '09748998028', 'Elder', '2024-04-17', '2024-04-17', '2024-04-22', '2024-04-26', NULL, NULL, NULL, NULL, '2024-05-12', '2024-05-15', '2024-04-17 00:00:00', '2024-04-17 00:00:00'),
(315, 255, 'Crasm-2024-0254', 'Rosa Mendoza', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Aglipayan', 'Female', 'Kidapawan City', '09952839238', 'Missionary', '2024-04-07', '2024-04-07', '2024-04-09', '2024-04-12', NULL, NULL, NULL, NULL, '2024-04-27', '2024-04-29', '2024-04-07 00:00:00', '2024-04-07 00:00:00'),
(316, 256, 'Crasm-2024-0255', 'Eduardo Torres', 'Sarangani', 'Glan', NULL, 'New', 'Jehovah\'s Witnesses', 'Male', 'Glan', '09469085574', 'Elder', '2024-05-30', '2024-05-30', '2024-06-02', '2024-06-05', NULL, NULL, NULL, NULL, '2024-06-23', '2024-06-25', '2024-05-30 00:00:00', '2024-05-30 00:00:00'),
(317, 257, 'Crasm-2024-0256', 'Pedro Bautista', 'Cotabato', 'Matalam', NULL, 'New', 'Born Again Christian', 'Male', 'Matalam', '09691453189', 'Deaconess', '2024-06-01', '2024-06-01', '2024-06-04', '2024-06-10', NULL, NULL, NULL, NULL, '2024-06-22', '2024-06-27', '2024-06-01 00:00:00', '2024-06-01 00:00:00'),
(318, 258, 'Crasm-2024-0257', 'Luz Mendoza', 'Sultan Kudarat', 'Lebak', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Lebak', '09488587604', 'Elder', '2024-07-28', '2024-07-28', '2024-07-30', '2024-08-06', NULL, NULL, NULL, NULL, '2024-08-16', '2024-08-20', '2024-07-28 00:00:00', '2024-07-28 00:00:00'),
(319, 259, 'Crasm-2024-0258', 'Josefa Dela Cruz', 'Cotabato', 'Kidapawan City', NULL, 'New', 'Roman Catholic', 'Female', 'Kidapawan City', '09256019028', 'Minister', '2024-07-24', '2024-07-24', '2024-07-26', '2024-08-02', NULL, NULL, NULL, NULL, NULL, NULL, '2024-07-24 00:00:00', '2024-07-24 00:00:00'),
(320, 260, 'Crasm-2024-0259', 'Felipe Fernandez', 'Sarangani', 'Maasim', NULL, 'New', 'Latter-day Saints', 'Male', 'Maasim', '09620139320', 'Pastor', '2024-09-23', '2024-09-23', '2024-09-27', '2024-10-02', '2024-10-10', NULL, NULL, NULL, NULL, NULL, '2024-09-23 00:00:00', '2024-09-23 00:00:00'),
(321, 261, 'Crasm-2024-0260', 'Grace Villanueva', 'Sarangani', 'Kiamba', NULL, 'Renewal', 'Aglipayan', 'Female', 'Kiamba', '09977308348', 'Deacon', '2024-07-10', '2024-07-10', '2024-07-12', '2024-07-18', NULL, NULL, NULL, NULL, '2024-08-06', '2024-08-08', '2024-07-10 00:00:00', '2024-07-10 00:00:00'),
(322, 262, 'Crasm-2024-0261', 'Eduardo Gomez', 'Cotabato', 'Matalam', NULL, 'New', 'Baptist', 'Male', 'Matalam', '09432298393', 'Pastor', '2024-08-17', '2024-08-17', '2024-08-21', '2024-08-25', NULL, NULL, NULL, NULL, '2024-09-12', '2024-09-16', '2024-08-17 00:00:00', '2024-08-17 00:00:00'),
(323, 263, 'Crasm-2024-0262', 'Ana Ramirez', 'Cotabato', 'Matalam', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Matalam', '09967163846', 'Pastor', '2024-09-09', '2024-09-09', '2024-09-12', '2024-09-17', '2024-09-23', NULL, NULL, NULL, NULL, NULL, '2024-09-09 00:00:00', '2024-09-09 00:00:00'),
(324, 264, 'Crasm-2024-0263', 'Josefa Torres', 'South Cotabato', 'General Santos City', NULL, 'Renewal', 'Roman Catholic', 'Female', 'General Santos City', '09851057736', 'Pastor', '2024-08-24', '2024-08-24', '2024-08-28', '2024-08-31', NULL, NULL, NULL, NULL, NULL, NULL, '2024-08-24 00:00:00', '2024-08-24 00:00:00'),
(325, 265, 'Crasm-2024-0264', 'Carlos Dela Cruz', 'Sarangani', 'Kiamba', NULL, 'Renewal', 'Methodist', 'Male', 'Kiamba', '09290123666', 'Pastor', '2024-07-10', '2024-07-10', '2024-07-13', '2024-07-20', NULL, NULL, NULL, NULL, NULL, NULL, '2024-07-10 00:00:00', '2024-07-10 00:00:00'),
(326, 266, 'Crasm-2024-0265', 'Rodrigo Ramirez', 'South Cotabato', 'Banga', NULL, 'New', 'Pentecostal', 'Male', 'Banga', '09212836793', 'Deacon', '2024-08-15', '2024-08-15', '2024-08-18', '2024-08-26', '2024-08-30', NULL, NULL, NULL, NULL, NULL, '2024-08-15 00:00:00', '2024-08-15 00:00:00'),
(327, 267, 'Crasm-2024-0266', 'Ricardo Ramirez', 'Sultan Kudarat', 'Esperanza', NULL, 'New', 'Jehovah\'s Witnesses', 'Male', 'Esperanza', '09362587044', 'Pastor', '2024-11-12', '2024-11-12', '2024-11-17', '2024-11-25', NULL, NULL, NULL, NULL, '2024-12-08', '2024-12-12', '2024-11-12 00:00:00', '2024-11-12 00:00:00'),
(328, 268, 'Crasm-2024-0267', 'Maria Torres', 'Sarangani', 'Alabel', NULL, 'New', 'Seventh-day Adventist', 'Female', 'Alabel', '09664246837', 'Missionary', '2024-11-14', '2024-11-14', '2024-11-18', '2024-11-22', '2024-11-28', NULL, NULL, NULL, NULL, NULL, '2024-11-14 00:00:00', '2024-11-14 00:00:00'),
(329, 269, 'Crasm-2024-0268', 'Ana Bautista', 'Sultan Kudarat', 'Lebak', NULL, 'Renewal', 'Baptist', 'Female', 'Lebak', '09716970178', 'Evangelist', '2024-11-21', '2024-11-21', '2024-11-25', '2024-11-28', NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-21 00:00:00', '2024-11-21 00:00:00'),
(330, 270, 'Crasm-2024-0269', 'Maria Lopez', 'South Cotabato', 'General Santos City', NULL, 'Renewal', 'Baptist', 'Female', 'General Santos City', '09506808455', 'Evangelist', '2024-11-25', '2024-11-25', '2024-11-27', '2024-12-03', NULL, NULL, NULL, NULL, '2024-12-16', '2024-12-20', '2024-11-25 00:00:00', '2024-11-25 00:00:00'),
(331, 271, 'Crasm-2024-0270', 'Eduardo Bautista', 'Cotabato', 'Makilala', NULL, 'New', 'Baptist', 'Male', 'Makilala', '09245133802', 'Deaconess', '2024-12-25', '2024-12-25', '2024-12-29', '2025-01-05', '2025-01-09', '2025-01-18', '2025-01-22', NULL, NULL, NULL, '2024-12-25 00:00:00', '2024-12-25 00:00:00'),
(332, 272, 'Crasm-2024-0271', 'Luz Dela Cruz', 'Sultan Kudarat', 'Lebak', NULL, 'New', 'Seventh-day Adventist', 'Female', 'Lebak', '09883962536', 'Deaconess', '2024-11-18', '2024-11-18', '2024-11-21', '2024-11-28', NULL, NULL, NULL, NULL, '2024-12-14', '2024-12-20', '2024-11-18 00:00:00', '2024-11-18 00:00:00'),
(333, 273, 'Crasm-2024-0272', 'Elena Mendoza', 'Cotabato', 'Midsayap', NULL, 'Renewal', 'Aglipayan', 'Female', 'Midsayap', '09644765466', 'Deaconess', '2024-12-26', '2024-12-26', '2024-12-29', '2025-01-05', NULL, NULL, NULL, NULL, '2025-01-17', '2025-01-19', '2024-12-26 00:00:00', '2024-12-26 00:00:00'),
(334, 274, 'Crasm-2024-0273', 'Ana Bautista', 'South Cotabato', 'Banga', NULL, 'New', 'Pentecostal', 'Female', 'Banga', '09383814138', 'Elder', '2024-10-19', '2024-10-19', '2024-10-21', '2024-10-24', '2024-11-03', NULL, NULL, NULL, NULL, NULL, '2024-10-19 00:00:00', '2024-10-19 00:00:00'),
(335, 275, 'Crasm-2024-0274', 'Beatriz Fernandez', 'Cotabato', 'Makilala', NULL, 'Renewal', 'Aglipayan', 'Female', 'Makilala', '09431976301', 'Deaconess', '2024-10-19', '2024-10-19', '2024-10-21', '2024-10-24', NULL, NULL, NULL, NULL, '2024-11-06', '2024-11-09', '2024-10-19 00:00:00', '2024-10-19 00:00:00'),
(336, 276, 'Crasm-2024-0275', 'Pedro Torres', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Aglipayan', 'Male', 'Isulan', '09740298515', 'Evangelist', '2024-12-11', '2024-12-11', '2024-12-15', '2024-12-21', NULL, NULL, NULL, NULL, '2025-01-08', '2025-01-13', '2024-12-11 00:00:00', '2024-12-11 00:00:00'),
(337, 277, 'Crasm-2024-0276', 'Teresa Gomez', 'Sultan Kudarat', 'Tacurong City', NULL, 'New', 'Pentecostal', 'Female', 'Tacurong City', '09729741412', 'Missionary', '2024-12-02', '2024-12-02', '2024-12-05', '2024-12-10', NULL, NULL, NULL, NULL, '2024-12-24', '2024-12-27', '2024-12-02 00:00:00', '2024-12-02 00:00:00'),
(338, 278, 'Crasm-2025-0277', 'Jose Ramirez', 'South Cotabato', 'Polomolok', NULL, 'New', 'Jehovah\'s Witnesses', 'Male', 'Polomolok', '09334073400', 'Evangelist', '2025-01-28', '2025-01-28', '2025-01-30', '2025-02-05', NULL, NULL, NULL, NULL, '2025-02-23', '2025-02-28', '2025-01-28 00:00:00', '2025-01-28 00:00:00'),
(339, 279, 'Crasm-2025-0278', 'Rodrigo Mendoza', 'Sultan Kudarat', 'Isulan', NULL, 'New', 'Latter-day Saints', 'Male', 'Isulan', '09682153089', 'Deaconess', '2025-01-01', '2025-01-01', '2025-01-05', '2025-01-10', NULL, NULL, NULL, NULL, '2025-01-26', '2025-02-01', '2025-01-01 00:00:00', '2025-01-01 00:00:00'),
(340, 280, 'Crasm-2025-0279', 'Manuel Mendoza', 'Sarangani', 'Maasim', NULL, 'Renewal', 'Roman Catholic', 'Male', 'Maasim', '09530916347', 'Deaconess', '2025-03-27', '2025-03-27', '2025-04-01', '2025-04-09', '2025-04-19', NULL, NULL, NULL, NULL, NULL, '2025-03-27 00:00:00', '2025-03-27 00:00:00'),
(341, 281, 'Crasm-2025-0280', 'Rodrigo Santos', 'Sarangani', 'Malapatan', NULL, 'New', 'Iglesia ni Cristo', 'Male', 'Malapatan', '09630219456', 'Evangelist', '2025-01-18', '2025-01-18', '2025-01-23', '2025-01-27', NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-18 00:00:00', '2025-01-18 00:00:00'),
(342, 282, 'Crasm-2025-0281', 'Carlos Garcia', 'Sultan Kudarat', 'Lebak', NULL, 'Renewal', 'Jehovah\'s Witnesses', 'Male', 'Lebak', '09977464398', 'Deacon', '2025-02-23', '2025-02-23', '2025-02-27', '2025-03-02', NULL, NULL, NULL, NULL, '2025-03-20', '2025-03-22', '2025-02-23 00:00:00', '2025-02-23 00:00:00'),
(343, 283, 'Crasm-2025-0282', 'Juan Bautista', 'South Cotabato', 'General Santos City', NULL, 'New', 'Seventh-day Adventist', 'Male', 'General Santos City', '09191882877', 'Elder', '2025-03-21', '2025-03-21', '2025-03-24', '2025-03-28', NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-21 00:00:00', '2025-03-21 00:00:00'),
(344, 284, 'Crasm-2025-0283', 'Carlos Aquino', 'Cotabato', 'Matalam', NULL, 'Renewal', 'Baptist', 'Male', 'Matalam', '09820572325', 'Elder', '2025-03-19', '2025-03-19', '2025-03-21', '2025-03-25', NULL, NULL, NULL, NULL, '2025-04-05', '2025-04-11', '2025-03-19 00:00:00', '2025-03-19 00:00:00'),
(345, 285, 'Crasm-2025-0284', 'Carmen Aquino', 'Cotabato', 'Midsayap', NULL, 'New', 'Iglesia ni Cristo', 'Female', 'Midsayap', '09911541582', 'Minister', '2025-03-22', '2025-03-22', '2025-03-25', '2025-03-28', NULL, NULL, NULL, NULL, '2025-04-11', '2025-04-17', '2025-03-22 00:00:00', '2025-03-22 00:00:00'),
(346, 286, 'Crasm-2025-0285', 'Antonio Dela Cruz', 'Cotabato', 'Matalam', NULL, 'Renewal', 'Baptist', 'Male', 'Matalam', '09713271261', 'Pastor', '2025-03-24', '2025-03-24', '2025-03-28', '2025-03-31', NULL, NULL, NULL, NULL, '2025-04-17', '2025-04-19', '2025-03-24 00:00:00', '2025-03-24 00:00:00'),
(347, 287, 'Crasm-2025-0286', 'Grace Mendoza', 'Sultan Kudarat', 'Lebak', NULL, 'New', 'Methodist', 'Female', 'Lebak', '09459998761', 'Evangelist', '2025-03-20', '2025-03-20', '2025-03-25', '2025-03-31', NULL, NULL, NULL, NULL, '2025-04-19', '2025-04-23', '2025-03-20 00:00:00', '2025-03-20 00:00:00'),
(348, 288, 'Crasm-2025-0287', 'Manuel Torres', 'South Cotabato', 'General Santos City', NULL, 'New', 'Aglipayan', 'Male', 'General Santos City', '09825338677', 'Minister', '2025-03-27', '2025-03-27', '2025-04-01', '2025-04-06', NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-27 00:00:00', '2025-03-27 00:00:00'),
(349, 289, 'Crasm-2025-0288', 'Antonio Bautista', 'Sarangani', 'Maasim', NULL, 'Renewal', 'Baptist', 'Male', 'Maasim', '09810116562', 'Deacon', '2025-05-06', '2025-05-06', '2025-05-08', '2025-05-15', NULL, NULL, NULL, NULL, '2025-05-26', '2025-05-29', '2025-05-06 00:00:00', '2025-05-06 00:00:00'),
(350, 290, 'Crasm-2025-0289', 'Carlos Villanueva', 'Sultan Kudarat', 'Lambayong', NULL, 'Renewal', 'Aglipayan', 'Male', 'Lambayong', '09188514813', 'Missionary', '2025-04-12', '2025-04-12', '2025-04-16', '2025-04-20', NULL, NULL, NULL, NULL, '2025-05-03', '2025-05-07', '2025-04-12 00:00:00', '2025-04-12 00:00:00'),
(351, 291, 'Crasm-2025-0290', 'Carmen Aquino', 'South Cotabato', 'Surallah', NULL, 'Renewal', 'Baptist', 'Female', 'Surallah', '09657205269', 'Evangelist', '2025-05-05', '2025-05-05', '2025-05-09', '2025-05-14', '2025-05-20', NULL, NULL, NULL, NULL, NULL, '2025-05-05 00:00:00', '2025-05-05 00:00:00'),
(352, 292, 'Crasm-2025-0291', 'Ricardo Fernandez', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Pentecostal', 'Male', 'Koronadal City', '09803064117', 'Evangelist', '2025-06-07', '2025-06-07', '2025-06-11', '2025-06-14', NULL, NULL, NULL, NULL, '2025-06-28', '2025-07-01', '2025-06-07 00:00:00', '2025-06-07 00:00:00'),
(353, 293, 'Crasm-2025-0292', 'Maria Aquino', 'South Cotabato', 'General Santos City', NULL, 'New', 'Pentecostal', 'Female', 'General Santos City', '09228546865', 'Pastor', '2025-06-10', '2025-06-10', '2025-06-14', '2025-06-22', NULL, NULL, NULL, NULL, '2025-07-12', '2025-07-17', '2025-06-10 00:00:00', '2025-06-10 00:00:00'),
(354, 294, 'Crasm-2025-0293', 'Teresa Torres', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Baptist', 'Female', 'Kidapawan City', '09225165334', 'Elder', '2025-05-03', '2025-05-03', '2025-05-08', '2025-05-11', NULL, NULL, NULL, NULL, '2025-05-27', '2025-06-01', '2025-05-03 00:00:00', '2025-05-03 00:00:00'),
(355, 295, 'Crasm-2025-0294', 'Jose Ramirez', 'Cotabato', 'Matalam', NULL, 'Renewal', 'Iglesia ni Cristo', 'Male', 'Matalam', '09297186396', 'Elder', '2025-06-11', '2025-06-11', '2025-06-16', '2025-06-23', NULL, NULL, NULL, NULL, '2025-07-12', '2025-07-15', '2025-06-11 00:00:00', '2025-06-11 00:00:00');
INSERT INTO `authority_records` (`id`, `no`, `crasm_no`, `name_of_so`, `provinces`, `municipality`, `status_type`, `type`, `religious_sect`, `sex`, `church_address`, `contact_number`, `position`, `filed`, `payment`, `received_in_rsso`, `processed`, `return_to_province_for_compliance`, `complied`, `received_in_rsso_after_compliance`, `complied_with_the_following`, `approved`, `transmitted_to_pso`, `created_at`, `updated_at`) VALUES
(356, 296, 'Crasm-2025-0295', 'Luz Santos', 'Sultan Kudarat', 'Lambayong', NULL, 'Renewal', 'Pentecostal', 'Female', 'Lambayong', '09836823010', 'Minister', '2025-04-08', '2025-04-08', '2025-04-10', '2025-04-14', NULL, NULL, NULL, NULL, '2025-04-28', '2025-04-30', '2025-04-08 00:00:00', '2025-04-08 00:00:00'),
(357, 297, 'Crasm-2025-0296', 'Eduardo Lopez', 'Sarangani', 'Glan', NULL, 'New', 'Roman Catholic', 'Male', 'Glan', '09653724666', 'Deaconess', '2025-06-28', '2025-06-28', '2025-07-03', '2025-07-08', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-28 00:00:00', '2025-06-28 00:00:00'),
(358, 298, 'Crasm-2025-0297', 'Ana Villanueva', 'South Cotabato', 'Polomolok', NULL, 'New', 'Pentecostal', 'Female', 'Polomolok', '09841150922', 'Missionary', '2025-06-15', '2025-06-15', '2025-06-18', '2025-06-24', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-15 00:00:00', '2025-06-15 00:00:00'),
(359, 299, 'Crasm-2025-0298', 'Grace Lopez', 'Sarangani', 'Glan', NULL, 'New', 'Born Again Christian', 'Female', 'Glan', '09500295664', 'Missionary', '2025-06-16', '2025-06-16', '2025-06-20', '2025-06-26', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-16 00:00:00', '2025-06-16 00:00:00'),
(360, 300, 'Crasm-2025-0299', 'Teresa Lopez', 'Sultan Kudarat', 'Lebak', NULL, 'New', 'Jehovah\'s Witnesses', 'Female', 'Lebak', '09516090161', 'Evangelist', '2025-09-16', '2025-09-16', '2025-09-20', '2025-09-23', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-16 00:00:00', '2025-09-16 00:00:00'),
(361, 301, 'Crasm-2025-0300', 'Jose Torres', 'Cotabato', 'Midsayap', NULL, 'Renewal', 'Pentecostal', 'Male', 'Midsayap', '09798492896', 'Elder', '2025-08-25', '2025-08-25', '2025-08-30', '2025-09-02', NULL, NULL, NULL, NULL, '2025-09-18', '2025-09-22', '2025-08-25 00:00:00', '2025-08-25 00:00:00'),
(362, 302, 'Crasm-2025-0301', 'Antonio Mendoza', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Pentecostal', 'Male', 'Koronadal City', '09599982328', 'Evangelist', '2025-09-09', '2025-09-09', '2025-09-11', '2025-09-17', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-09 00:00:00', '2025-09-09 00:00:00'),
(363, 303, 'Crasm-2025-0302', 'Rodrigo Dela Cruz', 'South Cotabato', 'Polomolok', NULL, 'New', 'Methodist', 'Male', 'Polomolok', '09613721204', 'Deaconess', '2025-07-07', '2025-07-07', '2025-07-10', '2025-07-17', '2025-07-27', '2025-08-11', '2025-08-16', NULL, NULL, NULL, '2025-07-07 00:00:00', '2025-07-07 00:00:00'),
(364, 304, 'Crasm-2025-0303', 'Josefa Ramirez', 'Cotabato', 'M\'lang', NULL, 'Renewal', 'Aglipayan', 'Female', 'M\'lang', '09690455331', 'Evangelist', '2025-07-16', '2025-07-16', '2025-07-18', '2025-07-26', NULL, NULL, NULL, NULL, '2025-08-08', '2025-08-11', '2025-07-16 00:00:00', '2025-07-16 00:00:00'),
(365, 305, 'Crasm-2025-0304', 'Eduardo Mendoza', 'South Cotabato', 'Banga', NULL, 'New', 'Seventh-day Adventist', 'Male', 'Banga', '09291827951', 'Missionary', '2025-08-29', '2025-08-29', '2025-08-31', '2025-09-08', NULL, NULL, NULL, NULL, '2025-09-25', '2025-09-29', '2025-08-29 00:00:00', '2025-08-29 00:00:00'),
(366, 306, 'Crasm-2025-0305', 'Teresa Fernandez', 'South Cotabato', 'Surallah', NULL, 'Renewal', 'Methodist', 'Female', 'Surallah', '09581872350', 'Elder', '2025-07-25', '2025-07-25', '2025-07-28', '2025-07-31', '2025-08-09', NULL, NULL, NULL, NULL, NULL, '2025-07-25 00:00:00', '2025-07-25 00:00:00'),
(367, 307, 'Crasm-2025-0306', 'Maria Gomez', 'South Cotabato', 'Banga', NULL, 'Renewal', 'Latter-day Saints', 'Female', 'Banga', '09878605988', 'Minister', '2025-09-01', '2025-09-01', '2025-09-04', '2025-09-10', NULL, NULL, NULL, NULL, '2025-09-25', '2025-09-29', '2025-09-01 00:00:00', '2025-09-01 00:00:00'),
(368, 308, 'Crasm-2025-0307', 'Elena Aquino', 'Sultan Kudarat', 'Lambayong', NULL, 'New', 'Latter-day Saints', 'Female', 'Lambayong', '09420785761', 'Deaconess', '2025-08-22', '2025-08-22', '2025-08-24', '2025-08-29', NULL, NULL, NULL, NULL, '2025-09-14', '2025-09-19', '2025-08-22 00:00:00', '2025-08-22 00:00:00'),
(369, 309, 'Crasm-2025-0308', 'Jose Dela Cruz', 'Sarangani', 'Maasim', NULL, 'Renewal', 'Iglesia ni Cristo', 'Male', 'Maasim', '09642775194', 'Missionary', '2025-07-13', '2025-07-13', '2025-07-18', '2025-07-21', NULL, NULL, NULL, NULL, '2025-08-06', '2025-08-09', '2025-07-13 00:00:00', '2025-07-13 00:00:00'),
(370, 310, 'Crasm-2025-0309', 'Rosa Santos', 'Cotabato', 'Makilala', NULL, 'Renewal', 'Iglesia ni Cristo', 'Female', 'Makilala', '09522794611', 'Missionary', '2025-09-25', '2025-09-25', '2025-09-30', '2025-10-05', NULL, NULL, NULL, NULL, '2025-10-22', '2025-10-28', '2025-09-25 00:00:00', '2025-09-25 00:00:00'),
(371, 311, 'Crasm-2025-0310', 'Elena Aquino', 'Cotabato', 'M\'lang', NULL, 'New', 'Jehovah\'s Witnesses', 'Female', 'M\'lang', '09986440738', 'Pastor', '2025-12-21', '2025-12-21', '2025-12-23', '2025-12-29', '2026-01-05', NULL, NULL, NULL, NULL, NULL, '2025-12-21 00:00:00', '2025-12-21 00:00:00'),
(372, 312, 'Crasm-2025-0311', 'Maria Gomez', 'Cotabato', 'Kidapawan City', NULL, 'Renewal', 'Baptist', 'Female', 'Kidapawan City', '09326304376', 'Deaconess', '2025-11-01', '2025-11-01', '2025-11-06', '2025-11-13', NULL, NULL, NULL, NULL, '2025-11-25', '2025-11-28', '2025-11-01 00:00:00', '2025-11-01 00:00:00'),
(373, 313, 'Crasm-2025-0312', 'Beatriz Villanueva', 'Sarangani', 'Alabel', NULL, 'New', 'Aglipayan', 'Female', 'Alabel', '09323671484', 'Minister', '2025-10-30', '2025-10-30', '2025-11-04', '2025-11-12', '2025-11-19', NULL, NULL, NULL, NULL, NULL, '2025-10-30 00:00:00', '2025-10-30 00:00:00'),
(374, 314, 'Crasm-2025-0313', 'Josefa Ramirez', 'Cotabato', 'Makilala', NULL, 'New', 'Aglipayan', 'Female', 'Makilala', '09800900313', 'Deacon', '2025-11-08', '2025-11-08', '2025-11-13', '2025-11-21', '2025-11-28', NULL, NULL, NULL, NULL, NULL, '2025-11-08 00:00:00', '2025-11-08 00:00:00'),
(375, 315, 'Crasm-2025-0314', 'Ana Fernandez', 'Sarangani', 'Maasim', NULL, 'Renewal', 'Latter-day Saints', 'Female', 'Maasim', '09787199615', 'Deacon', '2025-11-07', '2025-11-07', '2025-11-11', '2025-11-14', NULL, NULL, NULL, NULL, '2025-12-04', '2025-12-09', '2025-11-07 00:00:00', '2025-11-07 00:00:00'),
(376, 316, 'Crasm-2025-0315', 'Felipe Aquino', 'South Cotabato', 'Koronadal City', NULL, 'Renewal', 'Pentecostal', 'Male', 'Koronadal City', '09417087743', 'Missionary', '2025-12-17', '2025-12-17', '2025-12-21', '2025-12-25', NULL, NULL, NULL, NULL, '2026-01-13', '2026-01-17', '2025-12-17 00:00:00', '2025-12-17 00:00:00'),
(378, 318, 'Crasm-2025-0317', 'Josefa Dela Cruz', 'Sarangani', 'Malapatan', NULL, 'Renewal', 'Born Again Christian', 'Female', 'Malapatan', '09687380166', 'Deacon', '2025-11-07', '2025-11-07', '2025-11-11', '2025-11-14', NULL, NULL, NULL, NULL, '2025-11-25', '2025-11-28', '2025-11-07 00:00:00', '2025-11-07 00:00:00'),
(379, 319, 'Crasm-2025-0318', 'Ana Bautista', 'Sarangani', 'Maasim', NULL, 'Renewal', 'Roman Catholic', 'Female', 'Maasim', '09746140134', 'Deacon', '2025-10-16', '2025-10-16', '2025-10-21', '2025-10-26', NULL, NULL, NULL, NULL, '2025-11-15', '2025-11-19', '2025-10-16 00:00:00', '2025-10-16 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `created_at`, `updated_at`) VALUES
(3, 'emz', 'emz', '$2y$10$AD.J39wNvNRfgaCpRoKWz.957kOUBR60sGcq8x6iZR65Vgttvwpnu', '2026-09-29 03:41:27', '2026-09-29 03:41:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `authority_records`
--
ALTER TABLE `authority_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_crasm_no` (`crasm_no`),
  ADD KEY `idx_provinces` (`provinces`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_sex` (`sex`),
  ADD KEY `idx_approved` (`approved`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `authority_records`
--
ALTER TABLE `authority_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=380;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;