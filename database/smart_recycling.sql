-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2026 at 11:24 PM
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
-- Database: `smart_recycling`
--

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `Branch_id` varchar(20) NOT NULL,
  `Branch_name` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `C_ID` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`Branch_id`, `Branch_name`, `address`, `C_ID`) VALUES
('BR001', 'Aarong Dhanmondi', 'Road 27, Dhanmondi, Dhaka', 'COM001'),
('BR002', 'Shwapno Gulshan', 'Gulshan Avenue, Dhaka', 'COM002'),
('BR003', 'Meena Bazar Mohammadpur', 'Town Hall, Mohammadpur, Dhaka', 'COM003'),
('BR004', 'PRAN-RFL Badda', 'Badda Main Road, Dhaka', 'COM004'),
('BR005', 'Walton Gazipur', 'Chowrasta, Gazipur', 'COM005'),
('BR006', 'Bashundhara City', 'Panthapath, Dhaka', 'COM006'),
('BR007', 'Square Mohakhali', 'Mohakhali, Dhaka', 'COM007'),
('BR008', 'Akij Tejgaon', 'Tejgaon Industrial Area, Dhaka', 'COM008'),
('BR009', 'ACI Tejgaon', 'Tejgaon, Dhaka', 'COM009'),
('BR010', 'bKash Gulshan', 'Gulshan 1, Dhaka', 'COM010'),
('BR011', 'Daraz Banani', 'Banani, Dhaka', 'COM011'),
('BR012', 'Robi Gulshan', 'Gulshan 2, Dhaka', 'COM012'),
('BR013', 'GP Baridhara', 'Baridhara, Dhaka', 'COM013'),
('BR014', 'City Bank Gulshan', 'Gulshan Avenue, Dhaka', 'COM014'),
('BR015', 'BRAC Mohakhali', 'Mohakhali, Dhaka', 'COM015'),
('BR016', 'IDLC Gulshan', 'Gulshan Avenue, Dhaka', 'COM016'),
('BR017', 'Bata Motijheel', 'Motijheel, Dhaka', 'COM017'),
('BR018', 'Transcom Gulshan', 'Gulshan, Dhaka', 'COM018'),
('BR019', 'Kazi Farms Dhaka', 'Mirpur Road, Dhaka', 'COM019'),
('BR020', 'Olympic Tejgaon', 'Tejgaon, Dhaka', 'COM020');

-- --------------------------------------------------------

--
-- Table structure for table `branch_telephone`
--

CREATE TABLE `branch_telephone` (
  `Branch_id` varchar(20) NOT NULL,
  `Telephones` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_telephone`
--

INSERT INTO `branch_telephone` (`Branch_id`, `Telephones`) VALUES
('BR001', '0255000001'),
('BR002', '0255000002'),
('BR003', '0255000003'),
('BR004', '0255000004'),
('BR005', '0255000005'),
('BR006', '0255000006'),
('BR007', '0255000007'),
('BR008', '0255000008'),
('BR009', '0255000009'),
('BR010', '0255000010'),
('BR011', '0255000011'),
('BR012', '0255000012'),
('BR013', '0255000013'),
('BR014', '0255000014'),
('BR015', '0255000015'),
('BR016', '0255000016'),
('BR017', '0255000017'),
('BR018', '0255000018'),
('BR019', '0255000019'),
('BR020', '0255000020');

-- --------------------------------------------------------

--
-- Table structure for table `center_manager`
--

CREATE TABLE `center_manager` (
  `Manager_ID` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `Center_ID` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `center_manager`
--

INSERT INTO `center_manager` (`Manager_ID`, `name`, `email`, `password`, `Center_ID`, `phone`) VALUES
('MGR001', 'Karim Uddin', 'mgr001@recycle.com', 'Mgr@123', 'C001', '01911000001'),
('MGR002', 'Salma Khatun', 'mgr002@recycle.com', 'Mgr@123', 'C002', '01911000002'),
('MGR003', 'Rafiq Hasan', 'mgr003@recycle.com', 'Mgr@123', 'C003', '01911000003'),
('MGR004', 'Jashim Uddin', 'mgr004@recycle.com', 'Mgr@123', 'C004', '01911000004'),
('MGR005', 'Farida Yasmin', 'mgr005@recycle.com', 'Mgr@123', 'C005', '01911000005'),
('MGR006', 'Abdul Kalam', 'mgr006@recycle.com', 'Mgr@123', 'C006', '01911000006'),
('MGR007', 'Nasrin Akter', 'mgr007@recycle.com', 'Mgr@123', 'C007', '01911000007'),
('MGR008', 'Mizanur Rahman', 'mgr008@recycle.com', 'Mgr@123', 'C008', '01911000008'),
('MGR009', 'Ruma Begum', 'mgr009@recycle.com', 'Mgr@123', 'C009', '01911000009'),
('MGR010', 'Shahidul Islam', 'mgr010@recycle.com', 'Mgr@123', 'C010', '01911000010'),
('MGR011', 'Parvin Sultana', 'mgr011@recycle.com', 'Mgr@123', 'C011', '01911000011'),
('MGR012', 'Anwar Hossain', 'mgr012@recycle.com', 'Mgr@123', 'C012', '01911000012'),
('MGR013', 'Shirin Aktar', 'mgr013@recycle.com', 'Mgr@123', 'C013', '01911000013'),
('MGR014', 'Delwar Hossain', 'mgr014@recycle.com', 'Mgr@123', 'C014', '01911000014'),
('MGR015', 'Momtaz Begum', 'mgr015@recycle.com', 'Mgr@123', 'C015', '01911000015'),
('MGR016', 'Golam Mostofa', 'mgr016@recycle.com', 'Mgr@123', 'C016', '01911000016'),
('MGR017', 'Rehana Parvin', 'mgr017@recycle.com', 'Mgr@123', 'C017', '01911000017'),
('MGR018', 'Habibur Rahman', 'mgr018@recycle.com', 'Mgr@123', 'C018', '01911000018'),
('MGR019', 'Sultana Razia', 'mgr019@recycle.com', 'Mgr@123', 'C019', '01911000019'),
('MGR020', 'Kamrul Hasan', 'mgr020@recycle.com', 'Mgr@123', 'C020', '01911000020');

-- --------------------------------------------------------

--
-- Table structure for table `collection_center`
--

CREATE TABLE `collection_center` (
  `Center_ID` varchar(20) NOT NULL,
  `Center_name` varchar(100) DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL,
  `current_capacity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Address` varchar(100) DEFAULT NULL,
  `Status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection_center`
--

INSERT INTO `collection_center` (`Center_ID`, `Center_name`, `max_capacity`, `current_capacity`, `Address`, `Status`) VALUES
('C001', 'Dhanmondi Recycling Center', 5000, 4.50, 'Road 27, Dhanmondi, Dhaka', 'Open'),
('C002', 'Uttara Recycling Center', 4500, 6.00, 'Sector 7, Uttara, Dhaka', 'Open'),
('C003', 'Mirpur Recycling Center', 4000, 2.50, 'Section 10, Mirpur, Dhaka', 'Open'),
('C004', 'Nasirabad Recycling Center', 3500, 5.00, 'Nasirabad, Chattogram', 'Open'),
('C005', 'GEC Recycling Center', 3000, 7.50, 'GEC Circle, Chattogram', 'Open'),
('C006', 'Amberkhana Recycling Center', 2800, 5.50, 'Amberkhana, Sylhet', 'Open'),
('C007', 'Shaheb Bazar Recycling Center', 3200, 5.00, 'Shaheb Bazar, Rajshahi', 'Open'),
('C008', 'KDA Recycling Center', 3600, 4.00, 'KDA Avenue, Khulna', 'Open'),
('C009', 'Sadar Recycling Center', 2500, 3.50, 'Sadar Road, Barishal', 'Open'),
('C010', 'Station Road Recycling Center', 3000, 7.00, 'Station Road, Rangpur', 'Open'),
('C011', 'Town Hall Recycling Center', 2700, 5.00, 'Town Hall, Mymensingh', 'Open'),
('C012', 'Kandirpar Recycling Center', 4000, 4.00, 'Kandirpar, Cumilla', 'Open'),
('C013', 'Joydebpur Recycling Center', 4500, 3.00, 'Joydebpur, Gazipur', 'Open'),
('C014', 'Chashara Recycling Center', 3500, 4.50, 'Chashara, Narayanganj', 'Open'),
('C015', 'Kolatoli Recycling Center', 3000, 8.00, 'Kolatoli, Cox\'s Bazar', 'Open'),
('C016', 'Satmatha Recycling Center', 3300, 3.50, 'Satmatha, Bogura', 'Open'),
('C017', 'Mujib Sarak Recycling Center', 2800, 6.00, 'Mujib Sarak, Jashore', 'Open'),
('C018', 'College Road Recycling Center', 2500, 4.00, 'College Road, Tangail', 'Open'),
('C019', 'Edward College Recycling Center', 3000, 6.50, 'Edward College Road, Pabna', 'Open'),
('C020', 'Maijdee Recycling Center', 3500, 12.50, 'Maijdee, Noakhali', 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `deposit`
--

CREATE TABLE `deposit` (
  `deposit_id` varchar(20) NOT NULL,
  `deposit_date` date DEFAULT NULL,
  `earned_points` int(11) DEFAULT NULL,
  `waste_type` varchar(50) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `User_id` int(11) DEFAULT NULL,
  `center_id` varchar(20) DEFAULT NULL,
  `pickup_id` varchar(20) DEFAULT NULL,
  `recycler_id` varchar(20) DEFAULT NULL,
  `deposit_seq` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deposit`
--

INSERT INTO `deposit` (`deposit_id`, `deposit_date`, `earned_points`, `waste_type`, `weight`, `User_id`, `center_id`, `pickup_id`, `recycler_id`, `deposit_seq`) VALUES
('DEP001', '2026-07-01', 120, 'Plastic', 4.50, 231806499, 'C001', NULL, NULL, 1),
('DEP002', '2026-07-02', 180, 'Paper', 6.00, 208595783, 'C002', NULL, NULL, 2),
('DEP003', '2026-07-03', 250, 'Glass', 5.50, 511192176, 'C006', NULL, NULL, 3),
('DEP004', '2026-07-04', 150, 'Plastic', 5.00, 48962133, 'C007', NULL, NULL, 4),
('DEP005', '2026-07-05', 300, 'Metal', 4.00, 30776855, 'C008', NULL, NULL, 5),
('DEP006', '2026-07-06', 100, 'Paper', 3.50, 134305862, 'C009', NULL, NULL, 6),
('DEP007', '2026-07-07', 220, 'Plastic', 7.00, 965064893, 'C010', NULL, NULL, 7),
('DEP008', '2026-07-08', 280, 'Metal', 5.00, 37147258, 'C011', NULL, NULL, 8),
('DEP009', '2026-07-09', 190, 'Glass', 4.00, 836588097, 'C012', NULL, NULL, 9),
('DEP010', '2026-07-10', 350, 'Electronic', 3.00, 320310141, 'C013', NULL, NULL, 10),
('DEP011', '2026-07-11', 130, 'Paper', 4.50, 451831903, 'C014', NULL, NULL, 11),
('DEP012', '2026-07-12', 240, 'Plastic', 8.00, 356966335, 'C015', NULL, NULL, 12),
('DEP013', '2026-07-13', 175, 'Glass', 3.50, 285969411, 'C016', NULL, NULL, 13),
('DEP014', '2026-07-14', 320, 'Metal', 6.00, 381123432, 'C017', NULL, NULL, 14),
('DEP015', '2026-07-15', 140, 'Paper', 4.00, 163551872, 'C018', NULL, NULL, 15),
('DEP016', '2026-07-16', 400, 'Electronic', 2.50, 767516284, 'C003', NULL, NULL, 16),
('DEP017', '2026-07-17', 210, 'Plastic', 6.50, 220629670, 'C019', NULL, NULL, 17),
('DEP018', '2026-07-18', 260, 'Metal', 5.50, 822539989, 'C020', NULL, NULL, 18),
('DEP019', '2026-07-19', 160, 'Paper', 5.00, 950660445, 'C004', NULL, NULL, 19),
('DEP020', '2026-07-20', 290, 'Plastic', 7.50, 46937022, 'C005', NULL, NULL, 20),
('DEP021', '2026-08-24', 100, 'Household (Mixed)', 10.00, 37147258, NULL, 'PU027', 'REC008', 21),
('DEP022', '2026-08-24', 100, 'Household (Mixed)', 10.00, 37147258, NULL, 'PU028', 'REC008', 22),
('DEP023', '2026-08-25', 150, 'Household (Mixed)', 15.00, 37147258, NULL, 'PU029', 'REC008', 23),
('DEP24', '2026-08-28', 100, 'Household (Mixed)', 10.00, 37147258, NULL, 'PU29', 'REC008', 24),
('DEP25', '2026-08-31', 70, 'Metal', 7.00, 46937022, 'C020', NULL, NULL, 25);

-- --------------------------------------------------------

--
-- Table structure for table `deposit_request`
--

CREATE TABLE `deposit_request` (
  `request_id` varchar(20) NOT NULL,
  `User_id` int(11) DEFAULT NULL,
  `center_id` varchar(20) DEFAULT NULL,
  `waste_type` varchar(50) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `request_date` datetime DEFAULT NULL,
  `handled_by` varchar(20) DEFAULT NULL,
  `handled_at` datetime DEFAULT NULL,
  `req_seq` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deposit_request`
--

INSERT INTO `deposit_request` (`request_id`, `User_id`, `center_id`, `waste_type`, `weight`, `status`, `request_date`, `handled_by`, `handled_at`, `req_seq`) VALUES
('DR01', 37147258, 'C011', 'E-waste', 7.00, 'Cancelled', '2026-08-31 20:13:43', NULL, NULL, 1),
('DR02', 46937022, 'C020', 'Metal', 7.00, 'Accepted', '2026-08-31 21:26:08', 'MGR020', '2026-08-31 21:26:54', 2);

-- --------------------------------------------------------

--
-- Table structure for table `edu_institute_stats`
--

CREATE TABLE `edu_institute_stats` (
  `institute_EIIN` varchar(20) NOT NULL,
  `Institute_name` varchar(100) DEFAULT NULL,
  `CumulativeEarnedPoints` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `edu_institute_stats`
--

INSERT INTO `edu_institute_stats` (`institute_EIIN`, `Institute_name`, `CumulativeEarnedPoints`) VALUES
('100001', 'Dhaka Residential Model College', 12500),
('100002', 'Notre Dame College', 18300),
('100003', 'Rajuk Uttara Model College', 15700),
('100004', 'Dhaka College', 21400),
('100005', 'Eden Mohila College', 13200),
('100006', 'Chittagong College', 19600),
('100007', 'Government City College Chattogram', 10800),
('100008', 'Sylhet Government College', 14950),
('100009', 'Rajshahi College', 23100),
('100010', 'Government Azizul Haque College', 17470),
('100011', 'Carmichael College', 12100),
('100012', 'Mymensingh Government College', 18900),
('100013', 'Government Edward College', 11600),
('100014', 'Govt. Brajalal College', 15300),
('100015', 'Comilla Victoria Government College', 20700),
('100016', 'Govt. Michael Madhusudan College', 13900),
('100017', 'Tangail Government College', 11200),
('100018', 'Narsingdi Government College', 9800),
('100019', 'Feni Government College', 16400),
('100020', 'Noakhali Government College', 12800);

-- --------------------------------------------------------

--
-- Table structure for table `non_student`
--

CREATE TABLE `non_student` (
  `User_id` int(11) NOT NULL,
  `NID` varchar(20) DEFAULT NULL,
  `Occupation` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `non_student`
--

INSERT INTO `non_student` (`User_id`, `NID`, `Occupation`) VALUES
(30776855, '198543210505', 'Doctor'),
(37147258, '200543210808', 'Designer'),
(134305862, '196543210606', 'Homemaker'),
(208595783, '199876543202', 'Businessperson'),
(220629670, '199765432717', 'Manager'),
(285969411, '199876543313', 'Journalist'),
(381123432, '198987654414', 'Entrepreneur'),
(767516284, '196876543616', 'Retired'),
(822539989, '200765432818', 'Artist'),
(950660445, '199654321919', 'Chef');

-- --------------------------------------------------------

--
-- Table structure for table `partner_company`
--

CREATE TABLE `partner_company` (
  `company_id` varchar(20) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner_company`
--

INSERT INTO `partner_company` (`company_id`, `company_name`, `address`, `contact_no`) VALUES
('COM001', 'Aarong', 'Dhanmondi, Dhaka', '01713000001'),
('COM002', 'Shwapno', 'Gulshan, Dhaka', '01713000002'),
('COM003', 'Meena Bazar', 'Mohammadpur, Dhaka', '01713000003'),
('COM004', 'PRAN-RFL Group', 'Badda, Dhaka', '01713000004'),
('COM005', 'Walton', 'Gazipur, Bangladesh', '01713000005'),
('COM006', 'Bashundhara Group', 'Bashundhara, Dhaka', '01713000006'),
('COM007', 'Square Group', 'Mohakhali, Dhaka', '01713000007'),
('COM008', 'Akij Group', 'Tejgaon, Dhaka', '01713000008'),
('COM009', 'ACI Limited', 'Tejgaon, Dhaka', '01713000009'),
('COM010', 'bKash', 'Gulshan, Dhaka', '01713000010'),
('COM011', 'Daraz Bangladesh', 'Banani, Dhaka', '01713000011'),
('COM012', 'Robi Axiata', 'Gulshan, Dhaka', '01713000012'),
('COM013', 'Grameenphone', 'Baridhara, Dhaka', '01713000013'),
('COM014', 'City Bank', 'Gulshan, Dhaka', '01713000014'),
('COM015', 'BRAC', 'Mohakhali, Dhaka', '01713000015'),
('COM016', 'IDLC Finance', 'Gulshan, Dhaka', '01713000016'),
('COM017', 'Bata Bangladesh', 'Motijheel, Dhaka', '01713000017'),
('COM018', 'Transcom Group', 'Gulshan, Dhaka', '01713000018'),
('COM019', 'Kazi Farms Group', 'Dhaka', '01713000019'),
('COM020', 'Olympic Industries', 'Tejgaon, Dhaka', '01713000020');

-- --------------------------------------------------------

--
-- Table structure for table `phone`
--

CREATE TABLE `phone` (
  `User_id` int(11) NOT NULL,
  `Phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phone`
--

INSERT INTO `phone` (`User_id`, `Phone`) VALUES
(30776855, '01711000005'),
(37147258, '01711000008'),
(46937022, '01711000020'),
(48962133, '01711000004'),
(134305862, '01711000006'),
(163551872, '01711000015'),
(208595783, '01711000002'),
(220629670, '01711000017'),
(231806499, '01711000001'),
(232178925, '01721234567'),
(285969411, '01711000013'),
(320310141, '01711000010'),
(356966335, '01711000012'),
(381123432, '01711000014'),
(451831903, '01711000011'),
(511192176, '01711000003'),
(643110575, '01671234567'),
(767516284, '01711000016'),
(822539989, '01711000018'),
(836588097, '01711000009'),
(950660445, '01711000019'),
(965064893, '01711000007');

-- --------------------------------------------------------

--
-- Table structure for table `pickup_request`
--

CREATE TABLE `pickup_request` (
  `User_id` int(11) DEFAULT NULL,
  `Pickup_ID` varchar(20) NOT NULL,
  `Pickup_type` varchar(30) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `Center_ID` varchar(20) DEFAULT NULL,
  `Recycler_ID` varchar(20) DEFAULT NULL,
  `pickup_address` varchar(150) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `request_date` datetime DEFAULT NULL,
  `arrived_at` datetime DEFAULT NULL,
  `weight_kg` decimal(8,2) DEFAULT NULL,
  `user_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `recycler_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `pickup_seq` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pickup_request`
--

INSERT INTO `pickup_request` (`User_id`, `Pickup_ID`, `Pickup_type`, `status`, `Center_ID`, `Recycler_ID`, `pickup_address`, `city`, `notes`, `request_date`, `arrived_at`, `weight_kg`, `user_confirmed`, `recycler_confirmed`, `pickup_seq`) VALUES
(511192176, '1', 'Home', 'Completed', 'C006', 'REC003', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 1),
(356966335, '10', 'Home', 'Completed', 'C015', 'REC012', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 2),
(285969411, '11', 'Center', 'Scheduled', 'C016', 'REC013', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 3),
(381123432, '12', 'Home', 'Completed', 'C017', 'REC014', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 4),
(163551872, '13', 'Home', 'Pending', 'C018', 'REC015', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 5),
(767516284, '14', 'Center', 'Completed', 'C003', 'REC016', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 6),
(220629670, '15', 'Home', 'Scheduled', 'C019', 'REC017', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 7),
(822539989, '16', 'Home', 'Completed', 'C020', 'REC018', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 8),
(46937022, '17', 'Home', 'Completed', 'C005', 'REC020', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 9),
(37147258, '18', 'Home', 'Cancelled', NULL, NULL, 'Town Hall Road', 'Mymensingh', '', '2026-08-24 18:35:31', NULL, 10.00, 0, 0, 10),
(37147258, '19', 'Home', 'Cancelled', NULL, NULL, 'Town Hall Road', 'Mymensingh', '', '2026-08-24 18:44:05', NULL, 10.00, 0, 0, 11),
(48962133, '2', 'Center', 'Completed', 'C007', 'REC004', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 12),
(37147258, '20', 'Home', 'Cancelled', NULL, NULL, 'Town Hall Road', 'Mymensingh', '', '2026-08-24 20:36:28', NULL, 10.00, 0, 0, 13),
(37147258, '21', 'Home', 'Completed', NULL, 'REC008', 'Town Hall Road', 'Mymensingh', 'I will leave the waste to the gatekeeper of my house, collect from there.', '2026-08-24 22:04:33', '2026-08-24 22:06:11', 10.00, 0, 0, 14),
(37147258, '22', 'Home', 'Completed', NULL, 'REC008', 'Central Road', 'Mymensingh', 'I will leave the waste to the gatekeeper of my house, collect from there.', '2026-08-24 22:13:34', '2026-08-24 22:16:58', 10.00, 0, 0, 15),
(37147258, '23', 'Plastic', 'Completed', 'C011', 'REC008', 'Town Hall Road', 'Mymensingh', '', '2026-08-24 22:27:39', '2026-08-24 22:29:50', 10.00, 0, 0, 16),
(37147258, '24', 'Home', 'Collected', NULL, 'REC008', 'Town Hall Road', 'Mymensingh', '', '2026-08-24 23:25:37', '2026-08-24 23:26:12', 10.00, 1, 1, 17),
(37147258, '25', 'Home', 'Collected', NULL, 'REC008', 'Town Hall Road', 'Mymensingh', '', '2026-08-24 23:30:33', '2026-08-24 23:30:53', 10.00, 1, 1, 18),
(37147258, '26', 'Home', 'Collected', NULL, 'REC008', 'Town Hall Road', 'Mymensingh', '', '2026-08-25 01:36:26', '2026-08-25 01:36:45', 15.00, 1, 1, 19),
(30776855, '3', 'Home', 'Scheduled', 'C008', 'REC005', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 20),
(134305862, '4', 'Home', 'Completed', 'C009', 'REC006', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 21),
(965064893, '5', 'Center', 'Pending', 'C010', 'REC007', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 22),
(37147258, '6', 'Home', 'Completed', 'C011', 'REC008', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 23),
(836588097, '7', 'Home', 'Scheduled', 'C012', 'REC009', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 24),
(320310141, '8', 'Center', 'Completed', 'C013', 'REC010', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 25),
(451831903, '9', 'Home', 'Pending', 'C014', 'REC011', NULL, NULL, NULL, '2026-08-24 18:19:34', NULL, 10.00, 0, 0, 26),
(37147258, 'PU27', 'Home', 'Cancelled', NULL, NULL, 'Town Hall Road', 'Mymensingh', '', '2026-08-25 12:06:09', NULL, 10.00, 0, 0, 27),
(37147258, 'PU28', 'Home', 'Cancelled', NULL, NULL, 'Town Hall Road', 'Mymensingh', '', '2026-08-25 18:02:46', NULL, 10.00, 0, 0, 28),
(37147258, 'PU29', 'Home', 'Collected', NULL, 'REC008', 'Town Hall Road', 'Mymensingh', 'I will leave the waste to the gatekeeper of my house, collect from there.', '2026-08-25 23:20:03', '2026-08-25 23:22:45', 10.00, 1, 1, 29);

-- --------------------------------------------------------

--
-- Table structure for table `recycler`
--

CREATE TABLE `recycler` (
  `Recycler_ID` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `vehicle_no` varchar(30) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recycler`
--

INSERT INTO `recycler` (`Recycler_ID`, `name`, `phone`, `vehicle_no`, `city`, `is_available`, `email`, `password`) VALUES
('REC001', 'Abdur Rahim', '01712000001', 'DHAKA-METRO-11-1001', 'Dhaka', 1, 'rec001@recycle.com', 'Rec@123'),
('REC002', 'Jamal Uddin', '01812000002', 'CTG-METRO-12-1002', 'Chattogram', 1, 'rec002@recycle.com', 'Rec@1345'),
('REC003', 'Rashed Mia', '01912000003', 'SYL-METRO-13-1003', 'Sylhet', 1, 'rec003@recycle.com', '03kdpi3ei9-0nkdd\r\n'),
('REC004', 'Kamal Hossain', '01612000004', 'RAJ-METRO-14-1004', 'Rajshahi', 1, 'rec004@recycle.com', 'p0k2i-09812'),
('REC005', 'Sohel Rana', '01712000005', 'KHL-METRO-15-1005', 'Khulna', 1, 'rec005@recycle.com', 'Rec@123'),
('REC006', 'Mizanur Rahman', '01812000006', 'BAR-METRO-16-1006', 'Barishal', 1, 'rec006@recycle.com', 'Rec@123'),
('REC007', 'Nasir Ahmed', '01912000007', 'RAN-METRO-17-1007', 'Rangpur', 1, 'rec007@recycle.com', 'Rec@123'),
('REC008', 'Rubel Islam', '01612000008', 'MYM-METRO-18-1008', 'Mymensingh', 1, 'rec008@recycle.com', 'Rec@123'),
('REC009', 'Shakil Khan', '01712000009', 'COM-METRO-19-1009', 'Cumilla', 1, 'rec009@recycle.com', 'Rec@123'),
('REC010', 'Al Amin', '01812000010', 'GAZ-METRO-20-1010', 'Gazipur', 1, 'rec010@recycle.com', 'Rec@123'),
('REC011', 'Foysal Ahmed', '01912000011', 'NAR-METRO-21-1011', 'Narayanganj', 1, 'rec011@recycle.com', 'Rec@123'),
('REC012', 'Ruhul Amin', '01612000012', 'COX-METRO-22-1012', 'Coxs Bazar', 1, 'rec012@recycle.com', 'Rec@123'),
('REC013', 'Babul Hossain', '01712000013', 'BOG-METRO-23-1013', 'Bogura', 1, 'rec013@recycle.com', 'Rec@123'),
('REC014', 'Sajjad Karim', '01812000014', 'JAS-METRO-24-1014', 'Jashore', 1, 'rec014@recycle.com', 'Rec@123'),
('REC015', 'Nayeem Hasan', '01912000015', 'TAN-METRO-25-1015', 'Tangail', 1, 'rec015@recycle.com', 'Rec@123'),
('REC016', 'Harun Or Rashid', '01612000016', 'NAR-METRO-26-1016', 'Narayanganj', 1, 'rec016@recycle.com', 'Rec@123'),
('REC017', 'Riyad Hossain', '01712000017', 'PAB-METRO-27-1017', 'Pabna', 1, 'rec017@recycle.com', 'Rec@123'),
('REC018', 'Masud Rana', '01812000018', 'DIN-METRO-28-1018', 'Dinajpur', 1, 'rec018@recycle.com', 'Rec@123'),
('REC019', 'Tareq Aziz', '01912000019', 'FEN-METRO-29-1019', 'Feni', 1, 'rec019@recycle.com', 'Rec@123'),
('REC020', 'Shahadat Hossain', '01612000020', 'NOA-METRO-30-1020', 'Noakhali', 1, 'rec020@recycle.com', 'Rec@123'),
('REC021', 'Imran Sarker', '01712000021', 'DHAKA-METRO-31-1021', 'Dhaka', 1, 'rec021@recycle.com', 'Rec@123'),
('REC022', 'Sabbir Alam', '01812000022', 'DHAKA-METRO-32-1022', 'Dhaka', 1, 'rec022@recycle.com', 'Rec@123'),
('REC023', 'Anisul Haque', '01912000023', 'DHAKA-METRO-33-1023', 'Dhaka', 1, 'rec023@recycle.com', 'Rec@123');

-- --------------------------------------------------------

--
-- Table structure for table `redeem`
--

CREATE TABLE `redeem` (
  `User_id` int(11) NOT NULL,
  `company_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `redeem`
--

INSERT INTO `redeem` (`User_id`, `company_id`) VALUES
(30776855, 'COM005'),
(37147258, 'COM008'),
(46937022, 'COM020'),
(48962133, 'COM004'),
(134305862, 'COM006'),
(163551872, 'COM015'),
(208595783, 'COM002'),
(220629670, 'COM017'),
(231806499, 'COM001'),
(285969411, 'COM013'),
(320310141, 'COM010'),
(356966335, 'COM012'),
(381123432, 'COM014'),
(451831903, 'COM011'),
(511192176, 'COM003'),
(767516284, 'COM016'),
(822539989, 'COM018'),
(836588097, 'COM009'),
(950660445, 'COM019'),
(965064893, 'COM007');

-- --------------------------------------------------------

--
-- Table structure for table `reward`
--

CREATE TABLE `reward` (
  `reward_id` varchar(20) NOT NULL,
  `reward_name` varchar(100) DEFAULT NULL,
  `required_points` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `company_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward`
--

INSERT INTO `reward` (`reward_id`, `reward_name`, `required_points`, `expiry_date`, `company_id`) VALUES
('REW001', 'Aarong Gift Voucher', 500, '2027-01-01', 'COM001'),
('REW002', 'Shwapno Discount Coupon', 600, '2027-01-05', 'COM002'),
('REW003', 'Meena Bazar Voucher', 700, '2027-01-10', 'COM003'),
('REW004', 'PRAN Product Pack', 800, '2027-01-15', 'COM004'),
('REW005', 'Walton Accessories Voucher', 1200, '2027-01-20', 'COM005'),
('REW006', 'Bashundhara Shopping Voucher', 900, '2027-01-25', 'COM006'),
('REW007', 'Square Product Pack', 650, '2027-02-01', 'COM007'),
('REW008', 'Akij Product Voucher', 750, '2027-02-05', 'COM008'),
('REW009', 'ACI Household Pack', 850, '2027-02-10', 'COM009'),
('REW010', 'bKash Cashback', 1000, '2027-02-15', 'COM010'),
('REW011', 'Daraz Discount Voucher', 1500, '2027-02-20', 'COM011'),
('REW012', 'Robi Internet Pack', 400, '2027-02-25', 'COM012'),
('REW013', 'Grameenphone Internet Pack', 450, '2027-03-01', 'COM013'),
('REW014', 'City Bank Shopping Voucher', 2000, '2027-03-05', 'COM014'),
('REW015', 'BRAC Training Voucher', 1800, '2027-03-10', 'COM015'),
('REW016', 'IDLC Gift Voucher', 2200, '2027-03-15', 'COM016'),
('REW017', 'Bata Shoe Discount', 1300, '2027-03-20', 'COM017'),
('REW018', 'Transcom Gift Voucher', 1600, '2027-03-25', 'COM018'),
('REW019', 'Kazi Farms Food Pack', 1100, '2027-04-01', 'COM019'),
('REW020', 'Olympic Product Pack', 900, '2027-04-05', 'COM020');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `User_id` int(11) NOT NULL,
  `Student_id` varchar(20) DEFAULT NULL,
  `institute_EIIN` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`User_id`, `Student_id`, `institute_EIIN`) VALUES
(46937022, 'STU020', '100010'),
(48962133, 'STU004', '100004'),
(163551872, 'STU015', '100005'),
(231806499, 'STU001', '100001'),
(320310141, 'STU010', '100010'),
(356966335, 'STU012', '100002'),
(451831903, 'STU011', '100001'),
(511192176, 'STU003', '100003'),
(836588097, 'STU009', '100009'),
(965064893, 'STU007', '100007');

-- --------------------------------------------------------

--
-- Table structure for table `student_edu_level`
--

CREATE TABLE `student_edu_level` (
  `User_id` int(11) NOT NULL,
  `Education_Level` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_edu_level`
--

INSERT INTO `student_edu_level` (`User_id`, `Education_Level`) VALUES
(30776855, 'Undergraduate'),
(37147258, 'Higher Secondary'),
(46937022, 'Undergraduate'),
(48962133, 'Undergraduate'),
(134305862, 'Undergraduate'),
(163551872, 'Undergraduate'),
(208595783, 'Undergraduate'),
(220629670, 'Undergraduate'),
(231806499, 'Undergraduate'),
(285969411, 'Undergraduate'),
(320310141, 'Secondary'),
(356966335, 'Undergraduate'),
(381123432, 'Undergraduate'),
(451831903, 'Undergraduate'),
(511192176, 'Undergraduate'),
(767516284, 'Undergraduate'),
(822539989, 'Higher Secondary'),
(836588097, 'Undergraduate'),
(950660445, 'Undergraduate'),
(965064893, 'Secondary');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_id` int(11) NOT NULL,
  `Pin` varchar(255) NOT NULL,
  `DOB` date DEFAULT NULL,
  `StreetAddress` varchar(255) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Gender` varchar(20) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Badge_name` varchar(50) DEFAULT 'Bronze User',
  `current_badge_points` int(11) DEFAULT 0,
  `total_recycled` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_id`, `Pin`, `DOB`, `StreetAddress`, `City`, `FirstName`, `LastName`, `Gender`, `Email`, `Badge_name`, `current_badge_points`, `total_recycled`) VALUES
(30776855, 'iO9`<n~qI{2v*x', '1990-01-10', 'KDA Avenue', 'Khulna', 'Mahmudul', 'Islam', 'Male', 'mahmudul.islam@gmail.com', 'Bronze User', 120, 14.7),
(37147258, 'tF8\\@te621L4W?', '2010-01-08', 'Town Hall Road', 'Mymensingh', 'Raihan', 'Ahmed', 'Male', 'raihan.ahmed@gmail.com', 'Diamond', 1240, 78.1),
(46937022, 'nL7#LIdR.eC', '2002-06-21', 'Maijdee Main Road', 'Noakhali', 'Tasmia', 'Akter', 'Female', 'tasmia.akter@gmail.com', 'Gold', 820, 29.5),
(48962133, 'qM4+!pj?Qjh', '1979-10-20', 'Shaheb Bazar Road', 'Rajshahi', 'Farzana', 'Akter', 'Female', 'farzana.akter@gmail.com', 'Diamond User', 1200, 12.2),
(134305862, 'pA1HOykfUE7qV', '1998-08-25', 'Sadar Road', 'Barishal', 'Shamima', 'Begum', 'Female', 'shamima.begum@gmail.com', 'Bronze User', 55, 41.3),
(163551872, 'tP1\0cm,', '2001-04-16', 'College Road', 'Tangail', 'Jannatul', 'Ferdous', 'Female', 'jannatul.ferdous@gmail.com', 'Diamond', 1160, 22),
(208595783, 'nusrat123', '2004-04-06', 'Road 11, Nasirabad', 'Chattogram', 'Nusrat', 'Jahan', 'Female', 'nusrat.jahan@gmail.com', 'Diamond', 1330, 34.8),
(220629670, 'qK8?BTkV%', '1997-05-21', 'Edward College Road', 'Pabna', 'Mehedi', 'Hasan', 'Male', 'mehedi.hasan@gmail.com', 'Diamond User', 1050, 6.4),
(231806499, 'zQ2_ge`>6upE', '1970-05-15', 'Road 7, Dhanmondi', 'Dhaka', 'Sabbir', 'Rahman', 'Male', 'sabbir.rahman@gmail.com', 'Bronze User', 299, 26.1),
(232178925, 'riponmia123', NULL, NULL, NULL, 'Ripon', 'Mia', NULL, 'riponmia2026@yahoo.com', 'Bronze User', 0, 12.1),
(285969411, 'iH6#eSRv', '1989-10-05', 'Sherpur Road', 'Bogura', 'Rumana', 'Yasmin', 'Female', 'rumana.yasmin@gmail.com', 'Gold User', 999, 30.5),
(320310141, 'dA7)P_Zm,l', '1998-07-08', 'Joydebpur Road', 'Gazipur', 'Tanvir', 'Hossain', 'Male', 'tanvir.hossain@gmail.com', 'Diamond User', 1500, 17.2),
(356966335, 'vY0{@bTRcoEp\'', '1992-12-26', 'Kolatoli Road', 'Cox\'s Bazar', 'Imran', 'Kabir', 'Male', 'imran.kabir@gmail.com', 'Bronze User', 250, 42.5),
(381123432, 'test123', '1993-01-07', 'Mujib Sarak', 'Jashore', 'Arif', 'Chowdhury', 'Male', 'arif.chowdhury@gmail.com', 'Silver User', 350, 12.7),
(451831903, 'iY4!jxLc', '1995-04-29', 'Chashara Main Road', 'Narayanganj', 'Maliha', 'Sultana', 'Female', 'maliha.sultana@gmail.com', 'Gold User', 650, 33),
(511192176, 'lF1*W#6_Q+\"+3(03', '2005-08-27', 'Amberkhana Main Road', 'Sylhet', 'Tahmid', 'Hasan', 'Male', 'tahmid.hasan@gmail.com', 'Diamond', 1400, 27.9),
(643110575, 'harrypotter2026', NULL, NULL, NULL, 'Harry', 'Potter', NULL, 'harrypotter45@outlook.com', 'Bronze User', 0, 39.6),
(767516284, 'dB2=EeKn', '1969-09-17', 'Sadar Road', 'Narsingdi', 'Abdul', 'Karim', 'Male', 'abdul.karim@gmail.com', 'Silver User', 500, 15.1),
(822539989, 'fM6\"/JEx', '2003-02-24', 'Munshipara Road', 'Dinajpur', 'Sadia', 'Rahman', 'Female', 'sadia.rahman@gmail.com', 'Gold User', 720, 4.7),
(836588097, 'zQ2,K8IqJI', '2001-06-23', 'Kandirpar Main Road', 'Cumilla', 'Sumaiya', 'Islam', 'Female', 'sumaiya.islam@gmail.com', 'Diamond User', 1800, 26.3),
(950660445, 'qT0*m&jk8,F', '2009-03-26', 'Grand Trunk Road', 'Feni', 'Adnan', 'Haque', 'Male', 'adnan.haque@gmail.com', 'Bronze User', 150, 18.3),
(965064893, 'iR7\ZLUpMR.?>', '2009-05-05', 'Station Road', 'Rangpur', 'Afsana', 'Khatun', 'Female', 'afsana.khatun@gmail.com', 'Silver User', 400, 11.5);

-- --------------------------------------------------------

--
-- Table structure for table `voucher_transaction_history`
--

CREATE TABLE `voucher_transaction_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` varchar(20) NOT NULL,
  `purchase_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher_transaction_history`
--

INSERT INTO `voucher_transaction_history` (`id`, `user_id`, `reward_id`, `purchase_date`) VALUES
(1, 208595783, 'REW001', '2026-08-31 18:54:28'),
(2, 208595783, 'REW003', '2026-08-31 18:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `wallet`
--

CREATE TABLE `wallet` (
  `wallet_id` varchar(20) NOT NULL,
  `current_points` int(11) DEFAULT NULL,
  `User_id` int(11) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `voucher` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet`
--

INSERT INTO `wallet` (`wallet_id`, `current_points`, `User_id`, `transaction_date`, `voucher`) VALUES
('WAL001', 1250, 231806499, '2026-07-01', 9),
('WAL002', 300, 208595783, '2026-07-02', 9),
('WAL003', 1400, 511192176, '2026-07-03', 8),
('WAL004', 620, 48962133, '2026-07-04', 8),
('WAL005', 2100, 30776855, '2026-07-05', 6),
('WAL006', 890, 134305862, '2026-07-06', 5),
('WAL007', 1350, 965064893, '2026-07-07', 7),
('WAL008', 1240, 37147258, '2026-07-08', 11),
('WAL009', 1160, 836588097, '2026-07-09', 10),
('WAL010', 3050, 320310141, '2026-07-10', 6),
('WAL011', 740, 451831903, '2026-07-11', 1),
('WAL012', 1980, 356966335, '2026-07-12', 7),
('WAL013', 1540, 285969411, '2026-07-13', 1),
('WAL014', 2700, 381123432, '2026-07-14', 5),
('WAL015', 1160, 163551872, '2026-07-15', 10),
('WAL016', 3200, 767516284, '2026-07-16', 3),
('WAL017', 1420, 220629670, '2026-07-17', 7),
('WAL018', 2210, 822539989, '2026-07-18', 7),
('WAL019', 780, 950660445, '2026-07-19', 1),
('WAL020', 1930, 46937022, '2026-07-20', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`Branch_id`),
  ADD KEY `C_ID` (`C_ID`);

--
-- Indexes for table `branch_telephone`
--
ALTER TABLE `branch_telephone`
  ADD PRIMARY KEY (`Branch_id`,`Telephones`);

--
-- Indexes for table `center_manager`
--
ALTER TABLE `center_manager`
  ADD PRIMARY KEY (`Manager_ID`),
  ADD UNIQUE KEY `manager_email` (`email`),
  ADD KEY `Center_ID` (`Center_ID`);

--
-- Indexes for table `collection_center`
--
ALTER TABLE `collection_center`
  ADD PRIMARY KEY (`Center_ID`);

--
-- Indexes for table `deposit`
--
ALTER TABLE `deposit`
  ADD PRIMARY KEY (`deposit_id`),
  ADD UNIQUE KEY `deposit_seq` (`deposit_seq`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `center_id` (`center_id`);

--
-- Indexes for table `deposit_request`
--
ALTER TABLE `deposit_request`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `req_seq` (`req_seq`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `center_id` (`center_id`),
  ADD KEY `handled_by` (`handled_by`);

--
-- Indexes for table `edu_institute_stats`
--
ALTER TABLE `edu_institute_stats`
  ADD PRIMARY KEY (`institute_EIIN`);

--
-- Indexes for table `non_student`
--
ALTER TABLE `non_student`
  ADD PRIMARY KEY (`User_id`);

--
-- Indexes for table `partner_company`
--
ALTER TABLE `partner_company`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `phone`
--
ALTER TABLE `phone`
  ADD PRIMARY KEY (`User_id`,`Phone`);

--
-- Indexes for table `pickup_request`
--
ALTER TABLE `pickup_request`
  ADD PRIMARY KEY (`Pickup_ID`),
  ADD UNIQUE KEY `pickup_seq` (`pickup_seq`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `Center_ID` (`Center_ID`),
  ADD KEY `Recycler_ID` (`Recycler_ID`);

--
-- Indexes for table `recycler`
--
ALTER TABLE `recycler`
  ADD PRIMARY KEY (`Recycler_ID`),
  ADD UNIQUE KEY `recycler_email` (`email`);

--
-- Indexes for table `redeem`
--
ALTER TABLE `redeem`
  ADD PRIMARY KEY (`User_id`,`company_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `reward`
--
ALTER TABLE `reward`
  ADD PRIMARY KEY (`reward_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`User_id`),
  ADD KEY `institute_EIIN` (`institute_EIIN`);

--
-- Indexes for table `student_edu_level`
--
ALTER TABLE `student_edu_level`
  ADD PRIMARY KEY (`User_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_id`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `voucher_transaction_history`
--
ALTER TABLE `voucher_transaction_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_id` (`reward_id`);

--
-- Indexes for table `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`wallet_id`),
  ADD KEY `User_id` (`User_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deposit`
--
ALTER TABLE `deposit`
  MODIFY `deposit_seq` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `deposit_request`
--
ALTER TABLE `deposit_request`
  MODIFY `req_seq` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pickup_request`
--
ALTER TABLE `pickup_request`
  MODIFY `pickup_seq` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `voucher_transaction_history`
--
ALTER TABLE `voucher_transaction_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `branch`
--
ALTER TABLE `branch`
  ADD CONSTRAINT `branch_ibfk_1` FOREIGN KEY (`C_ID`) REFERENCES `partner_company` (`company_id`);

--
-- Constraints for table `branch_telephone`
--
ALTER TABLE `branch_telephone`
  ADD CONSTRAINT `branch_telephone_ibfk_1` FOREIGN KEY (`Branch_id`) REFERENCES `branch` (`Branch_id`);

--
-- Constraints for table `center_manager`
--
ALTER TABLE `center_manager`
  ADD CONSTRAINT `center_manager_ibfk_1` FOREIGN KEY (`Center_ID`) REFERENCES `collection_center` (`Center_ID`);

--
-- Constraints for table `deposit`
--
ALTER TABLE `deposit`
  ADD CONSTRAINT `deposit_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `deposit_ibfk_2` FOREIGN KEY (`center_id`) REFERENCES `collection_center` (`Center_ID`);

--
-- Constraints for table `deposit_request`
--
ALTER TABLE `deposit_request`
  ADD CONSTRAINT `deposit_request_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`),
  ADD CONSTRAINT `deposit_request_ibfk_2` FOREIGN KEY (`center_id`) REFERENCES `collection_center` (`Center_ID`),
  ADD CONSTRAINT `deposit_request_ibfk_3` FOREIGN KEY (`handled_by`) REFERENCES `center_manager` (`Manager_ID`);

--
-- Constraints for table `non_student`
--
ALTER TABLE `non_student`
  ADD CONSTRAINT `non_student_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`);

--
-- Constraints for table `phone`
--
ALTER TABLE `phone`
  ADD CONSTRAINT `phone_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`);

--
-- Constraints for table `pickup_request`
--
ALTER TABLE `pickup_request`
  ADD CONSTRAINT `pickup_request_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`),
  ADD CONSTRAINT `pickup_request_ibfk_2` FOREIGN KEY (`Center_ID`) REFERENCES `collection_center` (`Center_ID`),
  ADD CONSTRAINT `pickup_request_ibfk_3` FOREIGN KEY (`Recycler_ID`) REFERENCES `recycler` (`Recycler_ID`);

--
-- Constraints for table `redeem`
--
ALTER TABLE `redeem`
  ADD CONSTRAINT `redeem_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`),
  ADD CONSTRAINT `redeem_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `partner_company` (`company_id`);

--
-- Constraints for table `reward`
--
ALTER TABLE `reward`
  ADD CONSTRAINT `reward_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `partner_company` (`company_id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`),
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`institute_EIIN`) REFERENCES `edu_institute_stats` (`institute_EIIN`);

--
-- Constraints for table `student_edu_level`
--
ALTER TABLE `student_edu_level`
  ADD CONSTRAINT `student_edu_level_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`);

--
-- Constraints for table `voucher_transaction_history`
--
ALTER TABLE `voucher_transaction_history`
  ADD CONSTRAINT `voucher_transaction_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`User_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voucher_transaction_history_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `reward` (`reward_id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet`
--
ALTER TABLE `wallet`
  ADD CONSTRAINT `wallet_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
