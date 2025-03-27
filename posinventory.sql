-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 12:56 AM
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
-- Database: `posinventory`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `addbasket` (IN `pid` INT(11), IN `uname` VARCHAR(75))   BEGIN

INSERT INTO basket (productID, username) 
VALUES (pid,uname);

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `addcheckout` (IN `uname` VARCHAR(75), IN `pid` INT(75), IN `pquantity` INT(75))   BEGIN
INSERT INTO checkout (username, productID, quantity)
VALUES (uname,pid,pquantity);

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `addemployee` (IN `p_firstname` VARCHAR(75), IN `p_lastname` VARCHAR(75), IN `p_position` VARCHAR(75), IN `p_username` VARCHAR(75), IN `p_password` VARCHAR(75))   BEGIN
    DECLARE getposition INT(11);

    SET getposition = (SELECT positions.positionID FROM positions WHERE positions.position = p_position);

    INSERT INTO employees (firstName, lastName, positionID, username, password)
    VALUES (p_firstname, p_lastname, getposition, p_username, p_password);

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `addproduct` (IN `pcode` INT(11), IN `pimg` VARCHAR(75), IN `bname` VARCHAR(75), IN `gname` VARCHAR(75), IN `stocks` INT(75), IN `price` INT(75), IN `mdate` DATE, IN `edate` DATE, IN `prescription` VARCHAR(20))   BEGIN
    INSERT INTO products (productcode, productImg, brandName, genericName, prescription, stocks, price, manufactureDate, expiryDate) 
    VALUES (pcode, pimg, bname, gname, prescription, stocks, price, mdate, edate);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `checkout` (IN `p_username` VARCHAR(75), IN `p_productID` INT, IN `p_quantity` INT)   begin
declare newstock int;
declare stocks int;
declare productprice int;

set productprice =(select products.price FROM products where products.productID=p_productID);
set stocks =(select products.stocks from products where products.productID=p_productID);

IF p_quantity > stocks THEN
SELECT 'Out of Stock' as message;
ELSE
INSERT INTO orders (username,productID,quantity,price) values (p_username, p_productID,p_quantity,productprice);
delete from checkout;
set newstock = stocks - p_quantity;
call updatestock (p_productID, newstock);

END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `MoveExpiredProducts` ()   BEGIN

    INSERT INTO expiredproducts (productID, productcode, brandName, genericName, stocks, price, manufactureDate, expiryDate)
    SELECT productID, productcode, brandName, genericName, stocks, price, manufactureDate, expiryDate
    FROM products
    WHERE expiryDate < CURDATE();


    DELETE FROM products
    WHERE expiryDate < CURDATE();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updatestock` (IN `p_productID` INT, IN `p_newstock` INT)   update products SET products.stocks = p_newstock where products.productID=p_productID$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `basket`
--

CREATE TABLE `basket` (
  `id` int(11) NOT NULL,
  `username` varchar(75) NOT NULL,
  `productID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `basket_details`
-- (See below for the actual view)
--
CREATE TABLE `basket_details` (
`username` varchar(75)
,`productID` int(11)
,`brandName` varchar(75)
,`genericName` varchar(75)
,`stocks` int(75)
,`price` int(75)
);

-- --------------------------------------------------------

--
-- Table structure for table `changes`
--

CREATE TABLE `changes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `oldStock` int(75) NOT NULL,
  `stock` int(75) NOT NULL,
  `action` varchar(75) NOT NULL,
  `made_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `changes`
--

INSERT INTO `changes` (`id`, `product_id`, `oldStock`, `stock`, `action`, `made_at`) VALUES
(1, 16, 0, 12312, 'INSERT', '2023-12-01 20:54:39'),
(2, 16, 0, 12312, 'DELETE', '2023-12-01 21:05:12'),
(3, 9, 25, 500, 'UPDATE', '2023-12-01 21:09:07'),
(4, 7, 100, 97, 'UPDATE', '2023-12-01 21:17:36'),
(5, 7, 97, 92, 'UPDATE', '2023-12-01 21:38:33'),
(6, 9, 500, 499, 'UPDATE', '2023-12-01 21:47:21'),
(7, 17, 0, 2323, 'INSERT', '2023-12-01 21:48:13'),
(8, 9, 499, 10, 'UPDATE', '2023-12-01 21:48:44'),
(9, 17, 0, 2323, 'DELETE', '2023-12-01 21:49:01'),
(10, 9, 0, 10, 'DELETE', '2023-12-01 21:50:39'),
(11, 15, 0, 2313, 'DELETE', '2023-12-01 21:50:39'),
(12, 7, 92, 42, 'UPDATE', '2023-12-02 19:42:55'),
(13, 7, 42, 32, 'UPDATE', '2023-12-05 14:03:59'),
(14, 18, 0, 15, 'INSERT', '2023-12-05 14:04:42'),
(15, 18, 0, 15, 'DELETE', '2023-12-05 14:05:03'),
(16, 7, 32, 0, 'UPDATE', '2023-12-08 14:31:00'),
(17, 19, 0, 2313, 'INSERT', '2023-12-09 08:33:36'),
(18, 30, 1960, 1959, 'UPDATE', '2023-12-15 15:11:00'),
(19, 30, 1959, 1958, 'UPDATE', '2023-12-15 16:28:15'),
(20, 30, 1958, 1952, 'UPDATE', '2023-12-15 16:28:51'),
(21, 30, 1952, 1951, 'UPDATE', '2023-12-15 18:51:23'),
(22, 30, 1951, 1950, 'UPDATE', '2023-12-15 18:51:24'),
(23, 31, 0, 23232, 'INSERT', '2023-12-15 22:06:23'),
(24, 30, 1950, 1940, 'UPDATE', '2023-12-17 14:01:39'),
(25, 30, 1940, 1939, 'UPDATE', '2023-12-17 14:10:05'),
(26, 30, 1939, 1938, 'UPDATE', '2023-12-17 14:11:05'),
(27, 31, 23232, 23231, 'UPDATE', '2023-12-17 14:13:31'),
(28, 31, 23231, 23230, 'UPDATE', '2023-12-17 14:16:43'),
(29, 30, 1938, 1937, 'UPDATE', '2023-12-17 14:17:19'),
(30, 30, 1937, 1927, 'UPDATE', '2023-12-17 14:20:35'),
(31, 30, 1927, 1926, 'UPDATE', '2025-03-24 12:29:36'),
(32, 31, 0, 23230, 'DELETE', '2025-03-24 12:32:29'),
(33, 32, 0, 3000, 'INSERT', '2025-03-24 13:09:10'),
(34, 30, 1926, 1926, 'UPDATE', '2025-03-24 13:09:42'),
(35, 30, 1926, 1925, 'UPDATE', '2025-03-24 13:46:39'),
(36, 30, 1925, 1924, 'UPDATE', '2025-03-24 13:52:09');

-- --------------------------------------------------------

--
-- Table structure for table `checkout`
--

CREATE TABLE `checkout` (
  `username` varchar(75) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(75) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkout`
--

INSERT INTO `checkout` (`username`, `productID`, `quantity`) VALUES
('Pharmacist', 0, 1),
('Pharmacist', 32, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `checkoutpage`
-- (See below for the actual view)
--
CREATE TABLE `checkoutpage` (
`productID` int(11)
,`productImg` varchar(255)
,`brandName` varchar(75)
,`genericName` varchar(75)
,`stocks` int(75)
,`price` int(75)
,`quantity` int(75)
,`username` varchar(75)
);

-- --------------------------------------------------------

--
-- Table structure for table `deletedemployees`
--

CREATE TABLE `deletedemployees` (
  `employeeID` int(11) NOT NULL,
  `firstName` varchar(75) NOT NULL,
  `lastName` varchar(75) NOT NULL,
  `positionID` int(11) NOT NULL,
  `username` varchar(75) NOT NULL,
  `password` varchar(75) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deletedemployees`
--

INSERT INTO `deletedemployees` (`employeeID`, `firstName`, `lastName`, `positionID`, `username`, `password`, `deleted_at`) VALUES
(14, 'Clark', 'Kent', 1, 'clarkent', '$2y$10$AXJBtejYqiGCzNihfTDIAOV84Lw7udNxGdAu86mqsrOMAZq3xSFKO', '2023-12-15 17:43:28'),
(11, 'Clark', 'Kent', 1, 'clarkent', '$2y$10$EfWKCqOjM8EKtPDy9AZQrO4/4g/VdNF4x172T7LHR/Vk08p.L84LG', '2025-03-24 12:15:30'),
(12, 'Barry', 'Allen', 2, 'barryallen', '$2y$10$aawXRB2TFWTvgVeQgaPtAO3ZH9Kv4BK9xzxefb/NfEOlgTg5jI8Ii', '2025-03-24 12:15:32'),
(13, 'Vince Neill', 'Navales', 3, 'vinceneill', '$2y$10$8XA6Erj971./lDbDEWWQA.GOdrSNLWG.8ZllKuBET4YUmFRveUttC', '2025-03-24 12:15:33'),
(14, 'Diana', 'Prince', 3, 'dianaprince', 'wonderwoman123', '2025-03-24 12:15:35'),
(15, 'Roger', 'Malabanan', 3, 'Admin', '$2y$10$.DecFfxeVYT3KHvwJqeXhOfr4n6Z3QIbeX/wjn7xFWlAbAYhUkQiG', '2025-03-24 12:15:37'),
(14, 'Diana', 'Prince', 3, 'dianaprince', 'wonderwoman123', '2025-03-24 12:18:59');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employeeID` int(11) NOT NULL,
  `firstName` varchar(75) NOT NULL,
  `lastName` varchar(75) NOT NULL,
  `positionID` int(11) NOT NULL,
  `username` varchar(75) NOT NULL,
  `password` varchar(75) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employeeID`, `firstName`, `lastName`, `positionID`, `username`, `password`, `created_at`) VALUES
(16, 'Jespher', 'Boreres', 1, 'Pharmacist', '$2y$10$QpG69ZoH7J/N9PwHRg29gORjzKoHZwbecB5hEX89akqV96SZlYwiK', '2025-03-24 12:16:03'),
(17, 'Roger', 'Malabanan', 3, 'Admin', '$2y$10$CaSIRDdf67mzPQcRh..R6OBxi.BO/LQ4q7i.f02AyivSQGrMtVJxS', '2025-03-24 12:18:16'),
(18, 'Vince', 'Navales', 2, 'Manager', '$2y$10$z0mExvuRylAbvNOKL/oP5ucOKt7LNsvKeVc4ChemnxiTfTZVc6uOy', '2025-03-24 12:19:20');

--
-- Triggers `employees`
--
DELIMITER $$
CREATE TRIGGER `employee_after_delete` AFTER DELETE ON `employees` FOR EACH ROW INSERT into deletedemployees (employeeID,firstName,lastName,positionID,username,password,deleted_at)
VALUES (old.employeeID, old.firstName,old.lastName,old.positionID, old.username, old.password, NOW())
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `expiredproducts`
--

CREATE TABLE `expiredproducts` (
  `productID` int(11) DEFAULT NULL,
  `productcode` varchar(255) DEFAULT NULL,
  `brandName` varchar(255) DEFAULT NULL,
  `genericName` varchar(255) DEFAULT NULL,
  `stocks` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `manufactureDate` date DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_sales`
--

CREATE TABLE `monthly_sales` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `sales` int(75) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monthly_sales`
--

INSERT INTO `monthly_sales` (`id`, `date`, `sales`) VALUES
(2, '2023-12-02', 250),
(3, '2023-12-02', 250),
(4, '2023-12-02', 250);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(75) NOT NULL,
  `price` int(75) NOT NULL,
  `ordered_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `username`, `productID`, `quantity`, `price`, `ordered_at`) VALUES
(56, 'vinceneill', 7, 50, 5, '2023-12-02'),
(57, 'vinceneill', 7, 10, 5, '2023-12-05'),
(58, 'vinceneill', 7, 32, 5, '2023-12-08'),
(60, 'clarkent', 30, 1, 10, '2023-12-15'),
(61, 'clarkent', 30, 1, 10, '2023-12-15'),
(62, 'clarkent', 30, 6, 10, '2023-12-15'),
(63, 'clarkent', 30, 1, 10, '2023-12-15'),
(64, 'clarkent', 30, 1, 10, '2023-12-15'),
(65, 'clarkent', 30, 10, 10, '2023-12-17'),
(66, 'clarkent', 30, 1, 10, '2023-12-17'),
(67, 'clarkent', 30, 1, 10, '2023-12-17'),
(68, 'clarkent', 31, 1, 3232323, '2023-12-17'),
(69, 'clarkent', 31, 1, 3232323, '2023-12-17'),
(70, 'clarkent', 30, 1, 10, '2023-12-17'),
(71, 'clarkent', 30, 10, 10, '2023-12-17'),
(72, 'Pharmacist', 30, 1, 10, '2025-03-24'),
(73, 'Pharmacist', 30, 1, 10, '2025-03-24'),
(74, 'Pharmacist', 30, 1, 10, '2025-03-24');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `positionID` int(11) NOT NULL,
  `position` varchar(75) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`positionID`, `position`) VALUES
(1, 'Pharmacist'),
(2, 'Manager'),
(3, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_details`
--

CREATE TABLE `prescription_details` (
  `prescription_id` int(11) NOT NULL,
  `basket_id` int(11) NOT NULL,
  `medical_center` varchar(255) NOT NULL,
  `prescriptor` varchar(255) NOT NULL,
  `prescription_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription_details`
--

INSERT INTO `prescription_details` (`prescription_id`, `basket_id`, `medical_center`, `prescriptor`, `prescription_date`, `created_at`) VALUES
(1, 171, 'Dr. Jose Fabella', 'Doc. Gerald Ogardo', '2025-03-24', '2025-03-24 05:37:11'),
(2, 172, 'Dr. Jose Fabella', 'Doc. Gerald Ogardo', '2025-03-13', '2025-03-24 05:51:54');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productID` int(11) NOT NULL,
  `productcode` int(75) NOT NULL,
  `productImg` varchar(255) NOT NULL,
  `brandName` varchar(75) NOT NULL,
  `genericName` varchar(75) NOT NULL,
  `prescription` varchar(20) NOT NULL,
  `stocks` int(75) NOT NULL,
  `price` int(75) NOT NULL,
  `manufactureDate` date NOT NULL,
  `expiryDate` date NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `productcode`, `productImg`, `brandName`, `genericName`, `prescription`, `stocks`, `price`, `manufactureDate`, `expiryDate`, `added_at`) VALUES
(30, 202838792, '657be8c8b6b64.jpg', 'Biogesic', 'Paracetamol', 'Required', 1924, 10, '2023-06-25', '2025-06-25', '2023-12-15 13:48:56'),
(32, 202838793, '67e0e8f62e479.jpg', 'Emergen-C', 'Ascorbic Acid', 'Optional', 3000, 25, '2025-03-24', '2026-03-24', '2025-03-24 13:09:10');

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `products_after_delete` AFTER DELETE ON `products` FOR EACH ROW insert into changes (product_id,stock,action,made_at) VALUES (old.productID,old.stocks,"DELETE", NOW())
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `products_after_insert` AFTER INSERT ON `products` FOR EACH ROW insert into changes (product_id,stock,action,made_at) values (new.productID,new.stocks,"INSERT", NOW())
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `products_after_update` AFTER UPDATE ON `products` FOR EACH ROW insert into changes (product_id,oldStock,stock,action,made_at) values (old.productID, old.stocks,new.stocks,"UPDATE", NOW())
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure for view `basket_details`
--
DROP TABLE IF EXISTS `basket_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `basket_details`  AS SELECT `basket`.`username` AS `username`, `basket`.`productID` AS `productID`, `products`.`brandName` AS `brandName`, `products`.`genericName` AS `genericName`, `products`.`stocks` AS `stocks`, `products`.`price` AS `price` FROM (`basket` join `products` on(`basket`.`productID` = `products`.`productID`)) ;

-- --------------------------------------------------------

--
-- Structure for view `checkoutpage`
--
DROP TABLE IF EXISTS `checkoutpage`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `checkoutpage`  AS SELECT `checkout`.`productID` AS `productID`, `products`.`productImg` AS `productImg`, `products`.`brandName` AS `brandName`, `products`.`genericName` AS `genericName`, `products`.`stocks` AS `stocks`, `products`.`price` AS `price`, `checkout`.`quantity` AS `quantity`, `checkout`.`username` AS `username` FROM (`products` join `checkout` on(`products`.`productID` = `checkout`.`productID`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `basket`
--
ALTER TABLE `basket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `changes`
--
ALTER TABLE `changes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `monthly_sales`
--
ALTER TABLE `monthly_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderID`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`positionID`);

--
-- Indexes for table `prescription_details`
--
ALTER TABLE `prescription_details`
  ADD PRIMARY KEY (`prescription_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `basket`
--
ALTER TABLE `basket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `changes`
--
ALTER TABLE `changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `monthly_sales`
--
ALTER TABLE `monthly_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `positionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `prescription_details`
--
ALTER TABLE `prescription_details`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `move_expired_products` ON SCHEDULE EVERY 1 DAY STARTS '2023-12-15 22:33:51' ON COMPLETION NOT PRESERVE ENABLE DO call MoveExpiredProducts()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
