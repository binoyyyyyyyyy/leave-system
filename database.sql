-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 03:46 AM
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
-- Database: `teacher_leave_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `applicant_first_name` varchar(100) DEFAULT NULL,
  `applicant_middle_name` varchar(100) DEFAULT NULL,
  `applicant_last_name` varchar(100) DEFAULT NULL,
  `applicant_email` varchar(191) DEFAULT NULL,
  `applicant_employee_no` varchar(100) DEFAULT NULL,
  `applicant_department` varchar(150) DEFAULT NULL,
  `applicant_position` varchar(150) DEFAULT NULL,
  `applicant_salary` decimal(12,2) DEFAULT NULL,
  `leave_type_id` int(11) NOT NULL,
  `date_filed` date NOT NULL DEFAULT curdate(),
  `other_leave_type` varchar(150) DEFAULT NULL,
  `vacation_detail` enum('within_philippines','abroad') DEFAULT NULL,
  `abroad_specify` varchar(150) DEFAULT NULL,
  `sick_detail` enum('in_hospital','out_patient') DEFAULT NULL,
  `illness_details` varchar(255) DEFAULT NULL,
  `special_leave_women_details` varchar(255) DEFAULT NULL,
  `study_leave_detail` enum('completion_of_masters_degree','bar_board_examination_review') DEFAULT NULL,
  `working_days_applied` decimal(4,1) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `is_half_day` tinyint(1) NOT NULL DEFAULT 0,
  `commutation` enum('not_requested','requested') NOT NULL DEFAULT 'not_requested',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recommendation` enum('approved','disapproved') DEFAULT NULL,
  `recommendation_reason` text DEFAULT NULL,
  `credits_as_of` date DEFAULT NULL,
  `vacation_total_earned` decimal(10,2) DEFAULT NULL,
  `vacation_less_this_application` decimal(10,2) DEFAULT NULL,
  `vacation_balance` decimal(10,2) DEFAULT NULL,
  `sick_total_earned` decimal(10,2) DEFAULT NULL,
  `sick_less_this_application` decimal(10,2) DEFAULT NULL,
  `sick_balance` decimal(10,2) DEFAULT NULL,
  `certification_officer_name` varchar(150) DEFAULT NULL,
  `certification_officer_position` varchar(150) DEFAULT NULL,
  `recommendation_name` varchar(150) DEFAULT NULL,
  `recommendation_position` varchar(150) DEFAULT NULL,
  `days_with_pay` decimal(10,2) DEFAULT NULL,
  `days_without_pay` decimal(10,2) DEFAULT NULL,
  `others_specify` varchar(255) DEFAULT NULL,
  `disapproved_due_to` text DEFAULT NULL,
  `final_action_name` varchar(150) DEFAULT NULL,
  `final_action_position` varchar(150) DEFAULT NULL,
  `approval_name` varchar(255) DEFAULT NULL,
  `approval_position` varchar(255) DEFAULT NULL,
  `disapproval_name` varchar(255) DEFAULT NULL,
  `disapproval_position` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `teacher_id`, `applicant_first_name`, `applicant_middle_name`, `applicant_last_name`, `applicant_email`, `applicant_employee_no`, `applicant_department`, `applicant_position`, `applicant_salary`, `leave_type_id`, `date_filed`, `other_leave_type`, `vacation_detail`, `abroad_specify`, `sick_detail`, `illness_details`, `special_leave_women_details`, `study_leave_detail`, `working_days_applied`, `date_from`, `date_to`, `is_half_day`, `commutation`, `status`, `admin_remarks`, `rejected_reason`, `approved_by`, `approved_at`, `rejected_at`, `created_at`, `updated_at`, `recommendation`, `recommendation_reason`, `credits_as_of`, `vacation_total_earned`, `vacation_less_this_application`, `vacation_balance`, `sick_total_earned`, `sick_less_this_application`, `sick_balance`, `certification_officer_name`, `certification_officer_position`, `recommendation_name`, `recommendation_position`, `days_with_pay`, `days_without_pay`, `others_specify`, `disapproved_due_to`, `final_action_name`, `final_action_position`, `approval_name`, `approval_position`, `disapproval_name`, `disapproval_position`) VALUES
(50, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-07', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 4.0, '2026-04-07', '2026-04-10', 0, 'not_requested', 'rejected', NULL, NULL, NULL, '2026-04-07 11:26:39', '2026-04-20 09:04:59', '2026-04-07 03:26:24', '2026-04-20 01:04:59', 'disapproved', NULL, '2026-04-20', 120.00, 4.00, 120.00, 120.00, 0.00, 120.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-20', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 1.0, '2026-04-20', '2026-04-20', 0, 'not_requested', 'rejected', NULL, NULL, NULL, '2026-04-20 09:22:42', '2026-04-20 11:32:18', '2026-04-20 01:07:25', '2026-04-20 03:33:22', 'disapproved', 'not a good time', '2026-04-20', 120.00, 1.00, 120.00, 120.00, 0.00, 120.00, 'asd', 'qwe', 'asd', 'asd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-20', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 8.0, '2026-04-21', '2026-04-30', 0, 'not_requested', 'rejected', NULL, NULL, NULL, '2026-04-20 11:50:20', '2026-04-20 11:56:42', '2026-04-20 03:49:57', '2026-05-01 03:16:55', 'disapproved', NULL, '2026-04-30', 20.00, 8.00, 10.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-27', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 6.0, '2026-04-27', '2026-05-04', 0, 'not_requested', 'approved', NULL, NULL, 4, '2026-04-27 12:44:11', NULL, '2026-04-27 04:25:05', '2026-04-27 04:44:11', 'approved', 'ewan ko', '2026-04-27', 120.00, 6.00, 110.00, 120.00, 0.00, 120.00, 'Marvin Bermosa', 'Boss', 'Marvin Bermosa', 'Boss', NULL, NULL, NULL, NULL, 'Marvin Bermosa', 'Boss', NULL, NULL, NULL, NULL),
(54, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-27', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 4.0, '2026-04-28', '2026-05-01', 0, 'not_requested', 'approved', NULL, NULL, 4, '2026-04-27 12:38:46', NULL, '2026-04-27 04:38:27', '2026-04-27 04:38:46', 'approved', NULL, '2026-04-27', 120.00, 4.00, 110.00, 120.00, 0.00, 120.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-28', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 0.5, '2026-04-28', '2026-04-28', 1, 'not_requested', 'approved', NULL, NULL, 4, '2026-04-28 11:27:30', NULL, '2026-04-28 03:26:13', '2026-04-28 03:27:30', 'approved', NULL, '2026-04-28', 120.00, 0.50, 109.50, 120.00, 0.00, 120.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-28', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 4.0, '2026-04-28', '2026-05-01', 0, 'not_requested', 'approved', NULL, NULL, 4, '2026-04-28 11:45:13', NULL, '2026-04-28 03:45:00', '2026-04-28 03:45:13', 'approved', NULL, '2026-04-28', 15.00, 4.00, 11.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-28', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 3.0, '2026-04-28', '2026-04-30', 0, 'not_requested', 'approved', NULL, NULL, 4, '2026-04-28 11:52:28', NULL, '2026-04-28 03:52:18', '2026-04-28 03:52:28', 'approved', NULL, '2026-04-28', 20.00, 3.00, 17.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-04-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0, '2026-04-28', '2026-04-28', 0, 'not_requested', 'pending', NULL, NULL, NULL, NULL, NULL, '2026-04-28 04:07:36', '2026-04-28 04:07:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-30', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 7.0, '2026-05-20', '2026-05-28', 0, 'not_requested', 'approved', NULL, NULL, 4, '2026-04-30 11:47:46', NULL, '2026-04-30 03:38:23', '2026-04-30 03:47:46', 'approved', NULL, '2026-04-30', 20.00, 7.00, 10.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 4, 'asd', 'asd', 'asd', NULL, '123', 'asdasd', 'Principal232', NULL, 4, '2026-04-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.5, '2026-04-30', '2026-04-30', 1, 'not_requested', 'approved', NULL, NULL, 4, '2026-05-01 11:30:29', NULL, '2026-04-30 04:04:08', '2026-05-01 03:30:29', 'approved', NULL, '2026-04-30', 20.00, 0.00, 14.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 4, 'ds zxcx', 'zxczxx', 'zxcxcv', NULL, 'xxccxv', 'cxvbxcxv', 'cxvcx', NULL, 1, '2026-04-30', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 6.0, '2026-04-30', '2026-05-07', 0, 'not_requested', 'rejected', NULL, NULL, NULL, '2026-05-05 08:46:42', '2026-05-05 08:53:26', '2026-04-30 04:05:34', '2026-05-05 00:53:26', 'disapproved', NULL, '2026-05-05', 20.00, 6.00, 14.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 4, 'Admin', NULL, 'User', NULL, 'EMP-001', 'Administration', 'Principal', NULL, 1, '2026-04-30', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 6.0, '2026-04-30', '2026-05-07', 0, 'not_requested', 'approved', NULL, NULL, 4, '2026-04-30 12:10:50', NULL, '2026-04-30 04:10:33', '2026-04-30 04:10:50', 'approved', NULL, '2026-04-30', 20.00, 6.00, 14.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-05', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 3.0, '2026-05-12', '2026-05-14', 0, 'not_requested', 'rejected', NULL, NULL, NULL, '2026-05-05 09:18:31', '2026-05-05 09:21:01', '2026-05-05 01:18:18', '2026-05-05 01:21:01', 'disapproved', NULL, '2026-05-05', 20.00, 3.00, 10.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-13', NULL, 'within_philippines', NULL, NULL, NULL, NULL, NULL, 2.0, '2026-06-15', '2026-06-16', 0, 'not_requested', 'approved', NULL, NULL, 4, '2026-06-13 09:42:19', NULL, '2026-06-13 01:34:50', '2026-06-13 01:42:19', 'approved', NULL, '2026-06-13', 20.00, 2.00, 8.00, 20.00, 0.00, 20.00, 'Admin User', 'Principal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_credits`
--

CREATE TABLE `leave_credits` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `as_of_date` date NOT NULL,
  `vacation_total_earned` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vacation_less_this_application` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vacation_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sick_total_earned` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sick_less_this_application` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sick_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `certified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_credits`
--

INSERT INTO `leave_credits` (`id`, `teacher_id`, `as_of_date`, `vacation_total_earned`, `vacation_less_this_application`, `vacation_balance`, `sick_total_earned`, `sick_less_this_application`, `sick_balance`, `certified_by`, `created_at`, `updated_at`) VALUES
(4, 3, '2026-06-13', 20.00, 12.00, 8.00, 20.00, 0.00, 20.00, 4, '2026-04-28 03:52:02', '2026-06-13 01:42:19'),
(5, 4, '2026-05-05', 20.00, 6.00, 14.00, 20.00, 0.00, 20.00, 4, '2026-04-30 04:09:58', '2026-05-05 00:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `leave_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `leave_name`) VALUES
(9, '10-Day VAWC Leave'),
(13, 'Adoption Leave'),
(2, 'Mandatory/Forced Leave'),
(4, 'Maternity Leave'),
(16, 'Monetization of Leave Credits'),
(5, 'Paternity Leave'),
(10, 'Rehabilitation Privilege'),
(3, 'Sick Leave'),
(7, 'Solo Parent Leave'),
(12, 'Special Emergency (Calamity) Leave'),
(11, 'Special Leave Benefits for Women'),
(6, 'Special Privilege Leave'),
(8, 'Study Leave'),
(17, 'Terminal Leave'),
(1, 'Vacation Leave'),
(14, 'Wellness Leave');

-- --------------------------------------------------------

--
-- Table structure for table `locator_slips`
--

CREATE TABLE `locator_slips` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `permanent_station` varchar(150) NOT NULL,
  `purpose` text DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `check_type` enum('official_business','official_time') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locator_slips`
--

INSERT INTO `locator_slips` (`id`, `user_id`, `name`, `position`, `permanent_station`, `purpose`, `date_time`, `destination`, `check_type`, `status`, `admin_remarks`, `approved_by`, `approved_at`, `rejected_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'Teacher qwe User', 'Teacher I', 'Math Department', 'LBM', '2026-04-30 07:42:00', 'CR', 'official_time', 'approved', '', 4, '2026-04-30 13:53:07', NULL, '2026-04-30 05:49:16', '2026-04-30 05:53:07'),
(2, 3, 'Teacher qwe User', 'Teacher I', 'Math Department', 'qwe', '2026-04-30 07:49:00', 'qwe', 'official_business', 'approved', 'go ra', 4, '2026-04-30 14:01:45', NULL, '2026-04-30 05:59:52', '2026-04-30 06:01:45'),
(3, 4, 'Admin User', 'Principal', 'Administration', 'lbm', '2026-05-09 11:14:00', 'cr', 'official_business', 'rejected', 'qwe', NULL, NULL, '2026-05-01 11:15:13', '2026-05-01 03:15:00', '2026-05-01 03:15:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','teacher') NOT NULL DEFAULT 'teacher',
  `department` varchar(150) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_no`, `first_name`, `middle_name`, `last_name`, `email`, `username`, `password_hash`, `role`, `department`, `position`, `salary`, `status`, `created_at`, `updated_at`) VALUES
(3, 'EMP-002', 'Teacher', 'qwe', 'User', NULL, 'teacher1', '$2y$12$lFYPmmkSxTBe7/u7xxIKKORDHIbsMK1kmo8a2GZSXCZv3qkBNLlq.', 'teacher', 'Math Department', 'Teacher I', NULL, 'active', '2026-03-25 02:58:59', '2026-04-30 03:01:44'),
(4, 'EMP-001', 'Admin', NULL, 'User', NULL, 'admin1', '$2y$12$ZomSn5FH1kw/AMEazxTtAemUV.JJf.N.pPVRBlsB9ICHt9lb3ZLfq', 'admin', 'Administration', 'Principal', NULL, 'active', '2026-03-25 02:59:08', '2026-03-25 03:03:52'),
(5, '3', 'teacher2', 'teacher2', 'teacher2', 'qwe@example.com', 'teacher2', '$2y$10$mkaVW7De8SNaw4r8VmlUTOdRm1x0I9KxPPsnfMXixoGnOVOBxBD3S', 'teacher', 'san isidro', 'teacher1', 20000.00, 'active', '2026-04-07 01:58:00', '2026-04-07 01:58:00'),
(6, NULL, 'qwe', 'qwe', 'qwe', NULL, 'qwe', '$2y$10$1MKSIHl88PYW08xOETvZ2.HNA8AQcfNG7/5OEyeUPL0D5gcqJPGUG', 'teacher', 'qwe', 'qwe', 123.00, 'active', '2026-04-07 01:59:07', '2026-04-28 04:01:34'),
(7, NULL, 'marvin', NULL, 'bermosa', NULL, 'marvin', '$2y$10$B5SKmF15KT7u07Rtb0JR7eLIZypP46kObeRWj9Azg3rtIQxg24mjO', 'teacher', 'sto.domingo', 'teacher1', NULL, 'active', '2026-04-20 04:01:37', '2026-04-30 03:01:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_leave_teacher` (`teacher_id`),
  ADD KEY `fk_leave_type` (`leave_type_id`),
  ADD KEY `fk_leave_approved_by` (`approved_by`);

--
-- Indexes for table `leave_credits`
--
ALTER TABLE `leave_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_credit_teacher` (`teacher_id`),
  ADD KEY `fk_credit_certified_by` (`certified_by`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_name` (`leave_name`);

--
-- Indexes for table `locator_slips`
--
ALTER TABLE `locator_slips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `employee_no` (`employee_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `leave_credits`
--
ALTER TABLE `leave_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `locator_slips`
--
ALTER TABLE `locator_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `fk_leave_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `leave_credits`
--
ALTER TABLE `leave_credits`
  ADD CONSTRAINT `fk_credit_certified_by` FOREIGN KEY (`certified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_credit_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `locator_slips`
--
ALTER TABLE `locator_slips`
  ADD CONSTRAINT `locator_slips_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `locator_slips_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
