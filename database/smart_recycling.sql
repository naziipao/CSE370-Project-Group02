-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2026 at 06:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
-- Table structure for table `collection_center`
--

CREATE TABLE `collection_center` (
  `Center_ID` varchar(20) NOT NULL,
  `Center_name` varchar(100) DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL,
  `Address` varchar(100) DEFAULT NULL,
  `Status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection_center`
--

INSERT INTO `collection_center` (`Center_ID`, `Center_name`, `max_capacity`, `Address`, `Status`) VALUES
('C001', 'Dhanmondi Recycling Center', 5000, 'Road 27, Dhanmondi, Dhaka', 'Open'),
('C002', 'Uttara Recycling Center', 4500, 'Sector 7, Uttara, Dhaka', 'Open'),
('C003', 'Mirpur Recycling Center', 4000, 'Section 10, Mirpur, Dhaka', 'Open'),
('C004', 'Nasirabad Recycling Center', 3500, 'Nasirabad, Chattogram', 'Open'),
('C005', 'GEC Recycling Center', 3000, 'GEC Circle, Chattogram', 'Open'),
('C006', 'Amberkhana Recycling Center', 2800, 'Amberkhana, Sylhet', 'Open'),
('C007', 'Shaheb Bazar Recycling Center', 3200, 'Shaheb Bazar, Rajshahi', 'Open'),
('C008', 'KDA Recycling Center', 3600, 'KDA Avenue, Khulna', 'Open'),
('C009', 'Sadar Recycling Center', 2500, 'Sadar Road, Barishal', 'Open'),
('C010', 'Station Road Recycling Center', 3000, 'Station Road, Rangpur', 'Open'),
('C011', 'Town Hall Recycling Center', 2700, 'Town Hall, Mymensingh', 'Open'),
('C012', 'Kandirpar Recycling Center', 4000, 'Kandirpar, Cumilla', 'Open'),
('C013', 'Joydebpur Recycling Center', 4500, 'Joydebpur, Gazipur', 'Open'),
('C014', 'Chashara Recycling Center', 3500, 'Chashara, Narayanganj', 'Open'),
('C015', 'Kolatoli Recycling Center', 3000, 'Kolatoli, Cox\'s Bazar', 'Open'),
('C016', 'Satmatha Recycling Center', 3300, 'Satmatha, Bogura', 'Open'),
('C017', 'Mujib Sarak Recycling Center', 2800, 'Mujib Sarak, Jashore', 'Open'),
('C018', 'College Road Recycling Center', 2500, 'College Road, Tangail', 'Open'),
('C019', 'Edward College Recycling Center', 3000, 'Edward College Road, Pabna', 'Open'),
('C020', 'Maijdee Recycling Center', 3500, 'Maijdee, Noakhali', 'Open');

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
  `center_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deposit`
--

INSERT INTO `deposit` (`deposit_id`, `deposit_date`, `earned_points`, `waste_type`, `weight`, `User_id`, `center_id`) VALUES
('DEP001', '2026-07-01', 120, 'Plastic', 4.50, 231806499, 'C001'),
('DEP002', '2026-07-02', 180, 'Paper', 6.00, 208595783, 'C002'),
('DEP003', '2026-07-03', 250, 'Glass', 5.50, 511192176, 'C006'),
('DEP004', '2026-07-04', 150, 'Plastic', 5.00, 48962133, 'C007'),
('DEP005', '2026-07-05', 300, 'Metal', 4.00, 30776855, 'C008'),
('DEP006', '2026-07-06', 100, 'Paper', 3.50, 134305862, 'C009'),
('DEP007', '2026-07-07', 220, 'Plastic', 7.00, 965064893, 'C010'),
('DEP008', '2026-07-08', 280, 'Metal', 5.00, 37147258, 'C011'),
('DEP009', '2026-07-09', 190, 'Glass', 4.00, 836588097, 'C012'),
('DEP010', '2026-07-10', 350, 'Electronic', 3.00, 320310141, 'C013'),
('DEP011', '2026-07-11', 130, 'Paper', 4.50, 451831903, 'C014'),
('DEP012', '2026-07-12', 240, 'Plastic', 8.00, 356966335, 'C015'),
('DEP013', '2026-07-13', 175, 'Glass', 3.50, 285969411, 'C016'),
('DEP014', '2026-07-14', 320, 'Metal', 6.00, 381123432, 'C017'),
('DEP015', '2026-07-15', 140, 'Paper', 4.00, 163551872, 'C018'),
('DEP016', '2026-07-16', 400, 'Electronic', 2.50, 767516284, 'C003'),
('DEP017', '2026-07-17', 210, 'Plastic', 6.50, 220629670, 'C019'),
('DEP018', '2026-07-18', 260, 'Metal', 5.50, 822539989, 'C020'),
('DEP019', '2026-07-19', 160, 'Paper', 5.00, 950660445, 'C004'),
('DEP020', '2026-07-20', 290, 'Plastic', 7.50, 46937022, 'C005');

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
('100008', 'Sylhet Government College', 14500),
('100009', 'Rajshahi College', 23100),
('100010', 'Government Azizul Haque College', 17400),
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
(46937022, '198654321020', 'Pharmacist'),
(48962133, '197654321404', 'Banker'),
(134305862, '196543210606', 'Homemaker'),
(163551872, '200876543515', 'Consultant'),
(208595783, '199876543202', 'Businessperson'),
(220629670, '199765432717', 'Manager'),
(231806499, '198765432101', 'Teacher'),
(285969411, '199876543313', 'Journalist'),
(320310141, '199765432010', 'Shopkeeper'),
(356966335, '197876543212', 'Driver'),
(381123432, '198987654414', 'Entrepreneur'),
(451831903, '198876543111', 'Lawyer'),
(511192176, '200987654303', 'Engineer'),
(643110575, NULL, NULL),
(767516284, '196876543616', 'Retired'),
(822539989, '200765432818', 'Artist'),
(836588097, '198765432909', 'Accountant'),
(950660445, '199654321919', 'Chef'),
(965064893, '199543210707', 'Freelancer');

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
  `Recycler_ID` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pickup_request`
--

INSERT INTO `pickup_request` (`User_id`, `Pickup_ID`, `Pickup_type`, `status`, `Center_ID`, `Recycler_ID`) VALUES
(231806499, 'PU001', 'Home', 'Completed', 'C001', 'REC001'),
(208595783, 'PU002', 'Home', 'Pending', 'C002', 'REC002'),
(511192176, 'PU003', 'Home', 'Completed', 'C006', 'REC003'),
(48962133, 'PU004', 'Center', 'Completed', 'C007', 'REC004'),
(30776855, 'PU005', 'Home', 'Scheduled', 'C008', 'REC005'),
(134305862, 'PU006', 'Home', 'Completed', 'C009', 'REC006'),
(965064893, 'PU007', 'Center', 'Pending', 'C010', 'REC007'),
(37147258, 'PU008', 'Home', 'Completed', 'C011', 'REC008'),
(836588097, 'PU009', 'Home', 'Scheduled', 'C012', 'REC009'),
(320310141, 'PU010', 'Center', 'Completed', 'C013', 'REC010'),
(451831903, 'PU011', 'Home', 'Pending', 'C014', 'REC011'),
(356966335, 'PU012', 'Home', 'Completed', 'C015', 'REC012'),
(285969411, 'PU013', 'Center', 'Scheduled', 'C016', 'REC013'),
(381123432, 'PU014', 'Home', 'Completed', 'C017', 'REC014'),
(163551872, 'PU015', 'Home', 'Pending', 'C018', 'REC015'),
(767516284, 'PU016', 'Center', 'Completed', 'C003', 'REC016'),
(220629670, 'PU017', 'Home', 'Scheduled', 'C019', 'REC017'),
(822539989, 'PU018', 'Home', 'Completed', 'C020', 'REC018'),
(950660445, 'PU019', 'Center', 'Pending', 'C004', 'REC019'),
(46937022, 'PU020', 'Home', 'Completed', 'C005', 'REC020');

-- --------------------------------------------------------

--
-- Table structure for table `recycler`
--

CREATE TABLE `recycler` (
  `Recycler_ID` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `vehicle_no` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recycler`
--

INSERT INTO `recycler` (`Recycler_ID`, `name`, `phone`, `vehicle_no`) VALUES
('REC001', 'Abdur Rahim', '01712000001', 'DHAKA-METRO-11-1001'),
('REC002', 'Jamal Uddin', '01812000002', 'CTG-METRO-12-1002'),
('REC003', 'Rashed Mia', '01912000003', 'SYL-METRO-13-1003'),
('REC004', 'Kamal Hossain', '01612000004', 'RAJ-METRO-14-1004'),
('REC005', 'Sohel Rana', '01712000005', 'KHL-METRO-15-1005'),
('REC006', 'Mizanur Rahman', '01812000006', 'BAR-METRO-16-1006'),
('REC007', 'Nasir Ahmed', '01912000007', 'RAN-METRO-17-1007'),
('REC008', 'Rubel Islam', '01612000008', 'MYM-METRO-18-1008'),
('REC009', 'Shakil Khan', '01712000009', 'COM-METRO-19-1009'),
('REC010', 'Al Amin', '01812000010', 'GAZ-METRO-20-1010'),
('REC011', 'Foysal Ahmed', '01912000011', 'NAR-METRO-21-1011'),
('REC012', 'Ruhul Amin', '01612000012', 'COX-METRO-22-1012'),
('REC013', 'Babul Hossain', '01712000013', 'BOG-METRO-23-1013'),
('REC014', 'Sajjad Karim', '01812000014', 'JAS-METRO-24-1014'),
('REC015', 'Nayeem Hasan', '01912000015', 'TAN-METRO-25-1015'),
('REC016', 'Harun Or Rashid', '01612000016', 'NAR-METRO-26-1016'),
('REC017', 'Riyad Hossain', '01712000017', 'PAB-METRO-27-1017'),
('REC018', 'Masud Rana', '01812000018', 'DIN-METRO-28-1018'),
('REC019', 'Tareq Aziz', '01912000019', 'FEN-METRO-29-1019'),
('REC020', 'Shahadat Hossain', '01612000020', 'NOA-METRO-30-1020');

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
(30776855, 'STU005', '100005'),
(37147258, 'STU008', '100008'),
(46937022, 'STU020', '100010'),
(48962133, 'STU004', '100004'),
(134305862, 'STU006', '100006'),
(163551872, 'STU015', '100005'),
(208595783, 'STU002', '100002'),
(220629670, 'STU017', '100007'),
(231806499, 'STU001', '100001'),
(232178925, NULL, NULL),
(285969411, 'STU013', '100003'),
(320310141, 'STU010', '100010'),
(356966335, 'STU012', '100002'),
(381123432, 'STU014', '100004'),
(451831903, 'STU011', '100001'),
(511192176, 'STU003', '100003'),
(767516284, 'STU016', '100006'),
(822539989, 'STU018', '100008'),
(836588097, 'STU009', '100009'),
(950660445, 'STU019', '100009'),
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
(37147258, 'tF8\\@te621L4W?', '2010-01-08', 'Town Hall Road', 'Mymensingh', 'Raihan', 'Ahmed', 'Male', 'raihan.ahmed@gmail.com', 'Silver User', 450, 33.1),
(46937022, 'nL7#LIdR.eC', '2002-06-21', 'Maijdee Main Road', 'Noakhali', 'Tasmia', 'Akter', 'Female', 'tasmia.akter@gmail.com', 'Gold User', 750, 22.5),
(48962133, 'qM4+!pj?Qjh', '1979-10-20', 'Shaheb Bazar Road', 'Rajshahi', 'Farzana', 'Akter', 'Female', 'farzana.akter@gmail.com', 'Diamond User', 1200, 12.2),
(134305862, 'pA1HOykfUE7qV', '1951-09-23', 'Sadar Road', 'Barishal', 'Shamima', 'Begum', 'Female', 'shamima.begum@gmail.com', 'Bronze User', 55, 41.3),
(163551872, 'tP1\0cm,', '2001-04-16', 'College Road', 'Tangail', 'Jannatul', 'Ferdous', 'Female', 'jannatul.ferdous@gmail.com', 'Silver User', 310, 22),
(208595783, 'pR8,kRBcx\'m`B)1', '2004-04-06', 'Road 11, Nasirabad', 'Chattogram', 'Nusrat', 'Jahan', 'Female', 'nusrat.jahan@gmail.com', 'Gold User', 890, 34.8),
(220629670, 'qK8?BTkV%', '1997-05-21', 'Edward College Road', 'Pabna', 'Mehedi', 'Hasan', 'Male', 'mehedi.hasan@gmail.com', 'Diamond User', 1050, 6.4),
(231806499, 'zQ2_ge`>6upE', '1970-05-15', 'Road 7, Dhanmondi', 'Dhaka', 'Sabbir', 'Rahman', 'Male', 'sabbir.rahman@gmail.com', 'Bronze User', 299, 26.1),
(232178925, 'riponmia123', NULL, NULL, NULL, 'Ripon', 'Mia', NULL, 'riponmia2026@yahoo.com', 'Bronze User', 0, 12.1),
(285969411, 'iH6#eSRv', '1989-10-05', 'Sherpur Road', 'Bogura', 'Rumana', 'Yasmin', 'Female', 'rumana.yasmin@gmail.com', 'Gold User', 999, 30.5),
(320310141, 'dA7)P_Zm,l', '1998-07-08', 'Joydebpur Road', 'Gazipur', 'Tanvir', 'Hossain', 'Male', 'tanvir.hossain@gmail.com', 'Diamond User', 1500, 17.2),
(356966335, 'vY0{@bTRcoEp\'', '1992-12-26', 'Kolatoli Road', 'Cox\'s Bazar', 'Imran', 'Kabir', 'Male', 'imran.kabir@gmail.com', 'Bronze User', 250, 42.5),
(381123432, 'rD6_|PG.QV', '1993-01-07', 'Mujib Sarak', 'Jashore', 'Arif', 'Chowdhury', 'Male', 'arif.chowdhury@gmail.com', 'Silver User', 350, 12.7),
(451831903, 'iY4!jxLc', '1995-04-29', 'Chashara Main Road', 'Narayanganj', 'Maliha', 'Sultana', 'Female', 'maliha.sultana@gmail.com', 'Gold User', 650, 33),
(511192176, 'lF1*W#6_Q+\"+3(03', '2005-08-27', 'Amberkhana Main Road', 'Sylhet', 'Tahmid', 'Hasan', 'Male', 'tahmid.hasan@gmail.com', 'Diamond User', 1100, 27.9),
(643110575, 'harrypotter2026', NULL, NULL, NULL, 'Harry', 'Potter', NULL, 'harrypotter45@outlook.com', 'Bronze User', 0, 39.6),
(767516284, 'dB2=EeKn', '1969-09-17', 'Sadar Road', 'Narsingdi', 'Abdul', 'Karim', 'Male', 'abdul.karim@gmail.com', 'Silver User', 500, 15.1),
(822539989, 'fM6\"/JEx', '2003-02-24', 'Munshipara Road', 'Dinajpur', 'Sadia', 'Rahman', 'Female', 'sadia.rahman@gmail.com', 'Gold User', 720, 4.7),
(836588097, 'zQ2,K8IqJI', '2001-06-23', 'Kandirpar Main Road', 'Cumilla', 'Sumaiya', 'Islam', 'Female', 'sumaiya.islam@gmail.com', 'Diamond User', 1800, 26.3),
(950660445, 'qT0*m&jk8,F', '2009-03-26', 'Grand Trunk Road', 'Feni', 'Adnan', 'Haque', 'Male', 'adnan.haque@gmail.com', 'Bronze User', 150, 18.3),
(965064893, 'iR7\ZLUpMR.?>', '2009-05-05', 'Station Road', 'Rangpur', 'Afsana', 'Khatun', 'Female', 'afsana.khatun@gmail.com', 'Silver User', 400, 11.5);

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
('WAL002', 480, 208595783, '2026-07-02', 7),
('WAL003', 1350, 511192176, '2026-07-03', 8),
('WAL004', 620, 48962133, '2026-07-04', 8),
('WAL005', 2100, 30776855, '2026-07-05', 6),
('WAL006', 890, 134305862, '2026-07-06', 5),
('WAL007', 1350, 965064893, '2026-07-07', 7),
('WAL008', 2400, 37147258, '2026-07-08', 10),
('WAL009', 1160, 836588097, '2026-07-09', 10),
('WAL010', 3050, 320310141, '2026-07-10', 6),
('WAL011', 740, 451831903, '2026-07-11', 1),
('WAL012', 1980, 356966335, '2026-07-12', 7),
('WAL013', 1540, 285969411, '2026-07-13', 1),
('WAL014', 2700, 381123432, '2026-07-14', 5),
('WAL015', 950, 163551872, '2026-07-15', 10),
('WAL016', 3200, 767516284, '2026-07-16', 3),
('WAL017', 1420, 220629670, '2026-07-17', 7),
('WAL018', 2210, 822539989, '2026-07-18', 7),
('WAL019', 780, 950660445, '2026-07-19', 1),
('WAL020', 1860, 46937022, '2026-07-20', 3);

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
-- Indexes for table `collection_center`
--
ALTER TABLE `collection_center`
  ADD PRIMARY KEY (`Center_ID`);

--
-- Indexes for table `deposit`
--
ALTER TABLE `deposit`
  ADD PRIMARY KEY (`deposit_id`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `center_id` (`center_id`);

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
  ADD KEY `User_id` (`User_id`),
  ADD KEY `Center_ID` (`Center_ID`),
  ADD KEY `Recycler_ID` (`Recycler_ID`);

--
-- Indexes for table `recycler`
--
ALTER TABLE `recycler`
  ADD PRIMARY KEY (`Recycler_ID`);

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
-- Indexes for table `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`wallet_id`),
  ADD KEY `User_id` (`User_id`);

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
-- Constraints for table `deposit`
--
ALTER TABLE `deposit`
  ADD CONSTRAINT `deposit_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `deposit_ibfk_2` FOREIGN KEY (`center_id`) REFERENCES `collection_center` (`Center_ID`);

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
-- Constraints for table `wallet`
--
ALTER TABLE `wallet`
  ADD CONSTRAINT `wallet_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
