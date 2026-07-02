--
-- Database: `teacher_leave_system`
--

SET NAMES utf8mb4;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

CREATE DATABASE IF NOT EXISTS `teacher_leave_system`;
USE `teacher_leave_system`;

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

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `leave_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_credits`
--
ALTER TABLE `leave_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locator_slips`
--
ALTER TABLE `locator_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
