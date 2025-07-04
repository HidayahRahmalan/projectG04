-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 06:32 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maintenancedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CategoryID` varchar(10) NOT NULL,
  `CategoryType` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`CategoryID`, `CategoryType`) VALUES
('C001', 'Electrical'),
('C002', 'Plumbing'),
('C003', 'HVAC'),
('C004', 'Carpentry'),
('C005', 'Cleaning'),
('C006', 'Painting'),
('C007', 'Structural Damage'),
('C008', 'Safety Hazard'),
('C009', 'Equipment Malfunction'),
('C010', 'IT / Network'),
('C011', 'Grounds / Landscaping'),
('C012', 'Pest Control'),
('C013', 'Security System'),
('C014', 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `maintenancestatuslog`
--

CREATE TABLE `maintenancestatuslog` (
  `LogID` varchar(10) NOT NULL,
  `ReportID` varchar(10) NOT NULL,
  `StaffID` varchar(10) NOT NULL,
  `PreviousStatus` enum('Reported','Awaiting Repair','In Progress','Pending Approval','Completed') NOT NULL,
  `CurrentStatus` enum('Reported','Awaiting Repair','In Progress','Pending Approval','Completed') NOT NULL,
  `DateTime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenancestatuslog`
--

INSERT INTO `maintenancestatuslog` (`LogID`, `ReportID`, `StaffID`, `PreviousStatus`, `CurrentStatus`, `DateTime`) VALUES
('LOG0005', 'R001', 'S021', 'Awaiting Repair', 'In Progress', '2025-06-03 12:23:50'),
('LOG0007', 'R005', 'S011', 'Awaiting Repair', 'In Progress', '2025-06-03 16:21:38'),
('LOG0009', 'R006', 'S006', 'Awaiting Repair', 'In Progress', '2025-07-03 23:41:33'),
('LOG001', 'R002', 'S011', 'Reported', 'Awaiting Repair', '2025-06-03 00:30:08'),
('LOG002', 'R003', 'S004', 'Reported', 'Awaiting Repair', '2025-06-03 00:55:37'),
('LOG003', 'R004', 'S012', 'Reported', 'Awaiting Repair', '2025-06-03 12:00:16'),
('LOG004', 'R001', 'S021', 'Reported', 'Awaiting Repair', '2025-06-03 12:23:23'),
('LOG005', 'R001', 'S021', 'In Progress', 'Pending Approval', '2025-06-03 12:31:21'),
('LOG006', 'R005', 'S011', 'Reported', 'Awaiting Repair', '2025-06-03 16:20:05'),
('LOG007', 'R005', 'S011', 'In Progress', 'Pending Approval', '2025-06-03 16:23:26'),
('LOG008', 'R006', 'S006', 'Reported', 'Awaiting Repair', '2025-07-03 23:39:34'),
('LOG009', 'R006', 'S006', 'In Progress', 'Pending Approval', '2025-07-03 23:53:27'),
('LOG010', 'R007', 'S003', 'Reported', 'Awaiting Repair', '2025-07-04 00:13:59'),
('LOG011', 'R006', 'S001', 'Pending Approval', 'Completed', '2025-07-04 00:14:43');

-- --------------------------------------------------------

--
-- Table structure for table `mediafile`
--

CREATE TABLE `mediafile` (
  `MediaID` varchar(10) NOT NULL,
  `MediaType` enum('Image','Video') NOT NULL,
  `FilePath` varchar(255) NOT NULL,
  `ReportID` varchar(10) NOT NULL,
  `UploadDateTime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mediafile`
--

INSERT INTO `mediafile` (`MediaID`, `MediaType`, `FilePath`, `ReportID`, `UploadDateTime`) VALUES
('M001', 'Image', 'uploads/R001_1748853674_0.png', 'R001', '2025-06-02 17:13:09'),
('M002', 'Video', 'uploads/R002_1748875914_0.mp4', 'R002', '2025-06-02 22:51:54'),
('M003', 'Image', 'uploads/R003_1748883100_0.png', 'R003', '2025-06-03 00:51:40'),
('M004', 'Video', 'uploads/R003_1748883100_1.mp4', 'R003', '2025-06-03 00:51:40'),
('M005', 'Image', 'uploads/R004_1748923207_0.jpg', 'R004', '2025-06-03 12:00:07'),
('M006', 'Image', 'uploads/R001_1748925665_0.jpg', 'R001', '2025-06-03 12:41:05'),
('M007', 'Image', 'uploads/R001_1748926026_0.jpg', 'R001', '2025-06-03 12:47:06'),
('M008', 'Image', 'uploads/R005_1748938737_0.jpg', 'R005', '2025-06-03 16:18:57'),
('M009', 'Image', 'uploads/R005_1748939006_0.png', 'R005', '2025-06-03 16:23:26'),
('M010', 'Video', 'uploads/R005_1748939006_1.mp4', 'R005', '2025-06-03 16:23:26'),
('M011', 'Image', 'uploads/R006_1751557150_0.png', 'R006', '2025-07-03 23:39:10'),
('M012', 'Image', 'uploads/R006_1751558007_0.jpg', 'R006', '2025-07-03 23:53:27'),
('M013', 'Image', 'uploads/R007_1751559229_0.jpg', 'R007', '2025-07-04 00:13:49');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `ReportID` varchar(10) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `UrgencyLevel` enum('Low','Medium','High','Critical') NOT NULL,
  `Status` enum('Reported','Awaiting Repair','In Progress','Pending Approval','Completed') NOT NULL DEFAULT 'Reported',
  `StaffID` varchar(10) NOT NULL,
  `CategoryID` varchar(10) NOT NULL,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedDate` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `AISuggestion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`ReportID`, `Title`, `Description`, `UrgencyLevel`, `Status`, `StaffID`, `CategoryID`, `CreatedDate`, `LastUpdatedDate`, `AISuggestion`) VALUES
('R001', 'Wifi hilang', 'tiba\" wifi hilang', 'Medium', 'Pending Approval', 'S001', 'C010', '2025-06-02 16:41:14', '2025-06-03 12:47:06', NULL),
('R002', 'Toilet Blok Aman Aras 3', 'Tandas sumbat', 'Critical', 'Awaiting Repair', 'S001', 'C005', '2025-06-02 22:51:54', '2025-06-03 00:30:08', NULL),
('R003', 'Lampu Blok Kasturi Aras 3', 'Lampu depan lift rosak', 'High', 'Awaiting Repair', 'S001', 'C001', '2025-06-03 00:51:40', '2025-07-03 23:30:09', 'Here is an analysis of the provided image:\n\n1. **Problem Identification:** The fluorescent light fixture appears to be malfunctioning, with only partial illumination visible.\n\n2. **Possible Causes:**\n    * One or more fluorescent tubes within the fixture are burned out or failing.\n    * A faulty ballast is preventing the tubes from operating correctly.\n    * A loose connection in the wiring to the fixture is limiting power to the tubes.\n\n\n3. **Recommended Actions:**\n    * Visually inspect each fluorescent tube for signs of damage or darkening.\n    * Check the ballast for any visible damage or signs of overheating.  \n    * Turn off the power to the fixture at the breaker before conducting further inspections or repairs.  If the problem is not obvious, further investigation might be needed by a qualified electrician.\n'),
('R004', 'Tandas Blok Jebat Aras 9', 'Tandas rumah 9-12 flush rosak', 'High', 'Awaiting Repair', 'S001', 'C005', '2025-06-03 12:00:07', '2025-06-03 12:00:16', NULL),
('R005', 'Tandas Blok Kasturi Level 5', 'Bilik 12 tandas kotor', 'High', 'Pending Approval', 'S001', 'C005', '2025-06-03 16:18:57', '2025-06-03 16:23:26', NULL),
('R006', 'Toilet clog', 'Toilet clog at block A floor 1', 'High', 'Completed', 'S001', 'C002', '2025-07-03 23:39:10', '2025-07-04 00:14:43', 'Here is an analysis of the provided image:\n\n1. **Problem Identification:** The toilet bowl contains stagnant, discolored water, indicating a potential problem with the toilet\'s flushing mechanism or water supply.\n\n2. **Possible Causes:**\n    * **Faulty fill valve:** The fill valve may be malfunctioning, preventing the toilet tank from filling with water properly.\n    * **Clogged fill valve:**  Debris or mineral deposits might be obstructing the fill valve, restricting water flow.\n    * **Broken or malfunctioning flapper:** The flapper valve at the bottom of the tank may not be sealing properly, allowing water to continuously leak into the bowl.\n\n3. **Recommended Actions:**\n    * **Visually inspect the fill valve:** Check for any obvious obstructions or damage to the fill valve mechanism.\n    * **Check the flapper valve:** Inspect the flapper for proper seating and any signs of wear or damage.  Ensure a tight seal when the tank is full.\n    * **Manually flush the toilet:** Attempt to flush the toilet to observe the fill and flushing process. Listen for any unusual sounds or look for leaks.  If the problem persists after this, further investigation is necessary.\n'),
('R007', 'Wall plug burning', 'Wall blug blok B floor 2', 'Critical', 'Awaiting Repair', 'S001', 'C001', '2025-07-04 00:13:49', '2025-07-04 00:18:44', '1. **Problem Identification:** The electrical outlet shows signs of significant burning and damage, indicating a potential electrical fire hazard.\n\n2. **Possible Causes:**\n* Overloaded circuit: Too many appliances plugged into the outlet or circuit.\n* Faulty wiring: Damaged or improperly installed wiring within the wall or outlet.\n* Malfunctioning appliance: A defective appliance drawing excessive current.\n\n3. **Recommended Actions:**\n* Immediately switch off the power to the outlet at the circuit breaker.\n* Do not attempt to use the outlet until it is inspected and repaired by a qualified electrician.\n* Report the damage to the appropriate authority for safety inspection and repairs.');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` varchar(10) NOT NULL,
  `StaffName` varchar(100) NOT NULL,
  `PhoneNum` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Admin','Maintenance') NOT NULL DEFAULT 'Maintenance',
  `Position` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `StaffName`, `PhoneNum`, `Password`, `Role`, `Position`) VALUES
('S001', 'Azwarie', '0164475909', 'azwarie123', 'Admin', 'Supervisor'),
('S002', 'Fariz', '01161466216', 'fariz123', 'Admin', 'Supervisor'),
('S003', 'Ahmad Ismail', '0123456789', 'password123', 'Maintenance', 'Electrician'),
('S004', 'Zainal Abidin', '0123456790', 'password123', 'Maintenance', 'Electrician'),
('S005', 'Aminah Zulkifli', '0123456791', 'password123', 'Maintenance', 'Plumber'),
('S006', 'Nor Azman', '0123456792', 'password123', 'Maintenance', 'Plumber'),
('S007', 'Fadilah Kamsah', '0123456793', 'password123', 'Maintenance', 'HVAC Technician'),
('S008', 'Ibrahim Yaakob', '0123456794', 'password123', 'Maintenance', 'HVAC Technician'),
('S009', 'Kamarul Zahari', '0123456795', 'password123', 'Maintenance', 'Carpenter'),
('S010', 'Siti Mariam', '0123456796', 'password123', 'Maintenance', 'Carpenter'),
('S011', 'Noraini Mohamed', '0123456797', 'password123', 'Maintenance', 'Cleaner'),
('S012', 'Roslan Ismail', '0123456798', 'password123', 'Maintenance', 'Cleaner'),
('S013', 'Abdullah Ahmad', '0123456799', 'password123', 'Maintenance', 'Painter'),
('S014', 'Aiman Salim', '0123456800', 'password123', 'Maintenance', 'Painter'),
('S015', 'Faizul Nordin', '0123456801', 'password123', 'Maintenance', 'Structural Technician'),
('S016', 'Mardiana Mat', '0123456802', 'password123', 'Maintenance', 'Structural Technician'),
('S017', 'Jamilah Sabri', '0123456803', 'password123', 'Maintenance', 'Safety Officer'),
('S018', 'Tariq Hassan', '0123456804', 'password123', 'Maintenance', 'Safety Officer'),
('S019', 'Sharifah Aida', '0123456805', 'password123', 'Maintenance', 'Equipment Technician'),
('S020', 'Zuraidah Manaf', '0123456806', 'password123', 'Maintenance', 'Equipment Technician'),
('S021', 'Hafiz Fadil', '0123456807', 'password123', 'Maintenance', 'IT Technician'),
('S022', 'Sharul Azhar', '0123456808', 'password123', 'Maintenance', 'IT Technician'),
('S023', 'Khalid Anwar', '0123456809', 'password123', 'Maintenance', 'Groundskeeper'),
('S024', 'Salina Kamaruddin', '0123456810', 'password123', 'Maintenance', 'Groundskeeper'),
('S025', 'Rahman Razak', '0123456811', 'password123', 'Maintenance', 'Pest Control Technician'),
('S026', 'Siti Aisyah', '0123456812', 'password123', 'Maintenance', 'Pest Control Technician'),
('S027', 'Kamarul Nizam', '0123456813', 'password123', 'Maintenance', 'Security Officer'),
('S028', 'Nina Anwar', '0123456814', 'password123', 'Maintenance', 'Security Officer'),
('S029', 'Faisal Kamarul', '0123456815', 'password123', 'Maintenance', 'General Maintenance'),
('S030', 'Hamidi Ismail', '0123456816', 'password123', 'Maintenance', 'General Maintenance');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `maintenancestatuslog`
--
ALTER TABLE `maintenancestatuslog`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `ReportID` (`ReportID`),
  ADD KEY `StaffID` (`StaffID`);

--
-- Indexes for table `mediafile`
--
ALTER TABLE `mediafile`
  ADD PRIMARY KEY (`MediaID`),
  ADD KEY `ReportID` (`ReportID`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `StaffID` (`StaffID`),
  ADD KEY `CategoryID` (`CategoryID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `maintenancestatuslog`
--
ALTER TABLE `maintenancestatuslog`
  ADD CONSTRAINT `maintenancestatuslog_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `report` (`ReportID`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenancestatuslog_ibfk_2` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`);

--
-- Constraints for table `mediafile`
--
ALTER TABLE `mediafile`
  ADD CONSTRAINT `mediafile_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `report` (`ReportID`) ON DELETE CASCADE;

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`),
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`CategoryID`) REFERENCES `category` (`CategoryID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
