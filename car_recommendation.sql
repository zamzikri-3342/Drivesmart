-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2026 at 11:05 PM
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
-- Database: `car_recommendation`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `created_at`) VALUES
(1, 'zamzikri', '1234', '2026-06-02 06:59:50');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `body_type` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `fuel` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `link` text DEFAULT NULL,
  `last_modified_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `brand`, `model`, `body_type`, `image_path`, `fuel`, `price`, `link`, `last_modified_by`) VALUES
(1, 'Perodua', 'Bezza', 'Sedan', 'Images/Perodua/Bezza.webp', 'Petrol', 34580.00, 'perodua.com.my/our-models/sedan/bezza', 1),
(2, 'Perodua', 'Bezza', 'Sedan', 'Images/Perodua/Bezza.webp', 'Petrol', 43980.00, 'perodua.com.my/our-models/sedan/bezza', 1),
(3, 'Perodua', 'Axia (Manual)', 'Hatchback', 'Images/Perodua/Axia Manual.jpg', 'Petrol', 22000.00, 'perodua.com.my/our-models/hatchback/axia', 1),
(4, 'Perodua', 'Axia', 'Hatchback', 'Images/Perodua/Axia.webp', 'Petrol', 38600.00, 'perodua.com.my/our-models/hatchback/axia', 1),
(5, 'Perodua', 'Myvi', 'Hatchback', 'Images/Perodua/Myvi.webp', 'Petrol', 46500.00, 'perodua.com.my/our-models/hatchback/myvi', 1),
(6, 'Perodua', 'Myvi', 'Hatchback', 'Images/Perodua/Myvi.webp', 'Petrol', 50900.00, 'perodua.com.my/our-models/hatchback/myvi', 1),
(7, 'Perodua', 'Ativa', 'SUV', 'Images/Perodua/Ativa.webp', 'Petrol', 62500.00, 'perodua.com.my/our-models/suv/ativa', 1),
(8, 'Perodua', 'Alza', 'MPV', 'Images/Perodua/Alza.webp', 'Petrol', 62500.00, 'perodua.com.my/our-models/mpv/new-alza', 1),
(9, 'Perodua', 'Traz', 'SUV', 'Images/Perodua/Traz.jpg', 'Petrol', 76100.00, 'perodua.com.my/our-models/mpv/new-alza', 1),
(10, 'Perodua', 'QV-E', 'SUV', 'Images/Perodua/QV-E.png', 'EV', 93999.00, 'ev.perodua.com.my/', 1),
(11, 'Proton', 'Saga', 'Sedan', 'Images/Proton/Saga.webp', 'Petrol', 38990.00, 'proton.com/models/all-new-saga', 1),
(12, 'Proton', 'Persona', 'Sedan', 'Images/Proton/Persona.webp', 'Petrol', 47800.00, 'proton.com/models/persona', 1),
(13, 'Proton', 'S70', 'Sedan', 'Images/Proton/S70.webp', 'Petrol', 68800.00, 'proton.com/models/s70', 1),
(14, 'Proton', 'X50', 'SUV', 'Images/Proton/X50-1.png', 'Petrol', 89800.00, 'proton.com/models/all-new-x50', 1),
(15, 'Proton', 'X70', 'SUV', 'Images/Proton/X70.webp', 'Petrol', 99800.00, 'proton.com/models/x70', 1),
(16, 'Proton', 'X90', 'SUV', 'Images/Proton/X90.webp', 'Hybrid', 99800.00, 'proton.com/models/x90', 1),
(17, 'Proton', 'Emas7 PHEV', 'SUV', 'Images/Proton/Emas 7 PHEV.png', 'Hybrid', 105800.00, 'emas.proton.com/e-mas-7-phev/', 1),
(18, 'Proton', 'Emas 5', 'SUV', 'Images/Proton/Emas 5.png', 'EV', 56800.00, 'emas.proton.com/e-mas-5/', 1),
(19, 'Proton', 'Emas 7', 'SUV', 'Images/Proton/Emas 7.jpg', 'EV', 99800.00, 'emas.proton.com/e-mas-7/', 1),
(20, 'Toyota', 'Vios', 'Sedan', 'Images/Toyota/Vios.png', 'Petrol', 90600.00, 'toyota.com.my/en/models/vios.html', 1),
(21, 'Toyota', 'Corolla', 'Sedan', 'Images/Toyota/Corolla.png', 'Petrol', 144800.00, 'toyota.com.my/en/models/corolla.html', 1),
(22, 'Toyota', 'Camry', 'Sedan', 'Images/Toyota/Camry.png', 'Petrol', 221800.00, 'toyota.com.my/en/models/camry-hybrid-electric/camry.html', 1),
(23, 'Toyota', 'Yaris', 'Hatchback', 'Images/Toyota/Yaris.png', 'Petrol', 88000.00, 'toyota.com.my/en/models/yaris', 1),
(24, 'Toyota', 'Corolla Cross', 'SUV', 'Images/Toyota/Corolla Cross.png', 'Hybrid', 140800.00, 'toyota.com.my/en/models/corolla-cross-hybrid-electric.html', 1),
(25, 'Toyota', 'Veloz', 'MPV', 'Images/Toyota/Veloz.png', 'Petrol', 95000.00, 'toyota.com.my/en/models/veloz.html', 1),
(26, 'Toyota', 'Vellfire', 'MPV', 'Images/Toyota/Vellfire.png', 'Petrol', 448000.00, 'toyota.com.my/en/models/vellfire.html', 1),
(27, 'Toyota', 'Alphard', 'MPV', 'Images/Toyota/Alphard.png', 'Petrol', 548000.00, 'toyota.com.my/en/models/alphard.html', 1),
(28, 'Toyota', 'Hilux 4WD', 'Pickup', 'Images/Toyota/Hilux.png', 'Diesel', 104880.00, 'toyota.com.my/en/models/hilux.html', 1),
(29, 'Toyota', 'Hilux 4WD', 'Pickup', 'Images/Toyota/Hilux.png', 'Diesel', 163080.00, 'toyota.com.my/en/models/hilux.html', 1),
(30, 'Toyota', 'Hilux 4WD', 'Pickup', 'Images/Toyota/Hilux EV.png', 'EV', 226300.00, 'toyota.com.my/en/models/hilux/hilux-bev.html', 1),
(31, 'Honda', 'City', 'Sedan', 'Images/Honda/City.png', 'Petrol', 84900.00, 'honda.com.my/model/city', 1),
(32, 'Honda', 'City Hatchback', 'Hatchback', 'Images/Honda/City HB.png', 'Petrol', 85900.00, 'honda.com.my/model/city-hatchback', 1),
(33, 'Honda', 'Civic', 'Sedan', 'Images/Honda/Civic.webp', 'Petrol', 133900.00, 'honda.com.my/model/civic', 1),
(34, 'Honda', 'Civic e:Hev RS', 'Sedan', 'Images/Honda/Civic.webp', 'Hybrid', 167900.00, 'honda.com.my/model/civic', 1),
(35, 'Honda', 'WR-V', 'SUV', 'Images/Honda/WR-V.png', 'Petrol', 89900.00, 'honda.com.my/model/wr-v', 1),
(36, 'Honda', 'HR-V', 'SUV', 'Images/Honda/HR-V.png', 'Petrol', 115900.00, 'honda.com.my/model/hrv', 1),
(37, 'Honda', 'CR-V', 'SUV', 'Images/Honda/CR-V.png', 'Petrol', 181900.00, 'honda.com.my/model/crv', 1),
(38, 'Honda', 'CR-V e:Hev E', 'SUV', 'Images/Honda/CR-V.png', 'Hybrid', 178200.00, 'honda.com.my/model/crv', 1),
(39, 'Nissan', 'Almera', 'Sedan', 'Images/Nissan/Almera-39.jpg', 'Petrol', 83888.00, 'nissan.com.my/v2/nissan-almera/specifications/', 1),
(40, 'Nissan', 'Leaf', 'Hatchback', 'Images/Nissan/Leaf.jpg', 'EV', 168888.00, 'nissan.com.my/v2/nissan-leaf/specifications/', 1),
(41, 'Nissan', 'X-Trail 2WD', 'SUV', 'Images/Nissan/X-trail 2WD.jpg', 'Petrol', 138888.00, 'nissan.com.my/v2/nissan-x-trail/specifications/', 1),
(42, 'Nissan', 'X-Trail 4WD', 'SUV', 'Images/Nissan/X-Trail.jpg', 'Petrol', 163888.00, 'nissan.com.my/v2/nissan-x-trail/specifications/', 1),
(43, 'Nissan', 'Serena', 'MPV', 'Images/Nissan/Serena.webp', 'Hybrid', 149888.00, 'nissan.com.my/v2/nissan-serena/specifications/', 1),
(44, 'Nissan', 'Navara Single CAB', 'Pickup', 'Images/Nissan/Navara SC.png', 'Diesel', 98600.00, 'nissan.com.my/v2/nissan-navara-sc/specifications/', 1),
(45, 'Mazda', 'Mazda 3 Sedan', 'Sedan', 'Images/Mazda/3 Sedan.png', 'Petrol', 118900.00, 'mazda.com.my/vehicles/mazda3', 1),
(46, 'Mazda', 'Mazda 3 Sedan', 'Sedan', 'Images/Mazda/3 Sedan.png', 'Petrol', 165000.00, 'mazda.com.my/vehicles/mazda3', 1),
(47, 'Mazda', 'Mazda 3 Liftback', 'Hatchback', 'Images/Mazda/3 Hatchback.webp', 'Petrol', 118900.00, 'mazda.com.my/vehicles/mazda3', 1),
(48, 'Mazda', 'Mazda 3 Liftback', 'Hatchback', 'Images/Mazda/3 Hatchback.webp', 'Petrol', 165000.00, 'mazda.com.my/vehicles/mazda3', 1),
(49, 'Mazda', 'CX-5', 'SUV', 'Images/Mazda/CX5.avif', 'Petrol', 134000.00, 'mazda.com.my/vehicles/mazda-cx-5', 1),
(50, 'Mazda', 'CX-5 2.2D High', 'SUV', 'Images/Mazda/CX5.avif', 'Diesel', 169000.00, 'mazda.com.my/vehicles/mazda-cx-5', 1),
(51, 'Mazda', 'CX-5 2.5 High AWD', 'SUV', 'Images/Mazda/CX5.avif', 'Petrol', 177200.00, 'mazda.com.my/vehicles/mazda-cx-5', 1),
(52, 'Mazda', 'Mazda 6', 'Sedan', 'Images/Mazda/6.png', 'Petrol', 240000.00, 'mazda.com.my/vehicles/mazda6', 1),
(53, 'Mitsubishi', 'Xforce', 'SUV', 'Images/Mitsubishi/Xforce.webp', 'Petrol', 109980.00, 'mitsubishi-motors.com.my/model/xforce/', 1),
(54, 'Mitsubishi', 'Xpander', 'MPV', 'Images/Mitsubishi/Xpander.webp', 'Petrol', 99980.00, 'mitsubishi-motors.com.my/model/xpander/', 1),
(55, 'Mitsubishi', 'Triton', 'Pickup', 'Images/Mitsubishi/Triton.webp', 'Diesel', 116980.00, 'mitsubishi-motors.com.my/model/triton/', 1),
(56, 'BMW', '320i', 'Sedan', 'Images/BMW/320i.webp', 'Petrol', 265800.00, 'bmw.com.my/en-my/sl/vehiclefinder/details/01945e09-ce6c-7948-aeaf-d4bfe79bb7fd?filters=%257B%2522MARKETING_SERIES%2522%253A%255B%25223%2522%255D%252C%2522MARKETING_MODEL_RANGE%2522%253A%255B%25223_G20%2522%255D%257D&sorting=PRODUCTION_DATE_ASC&modelCode=38FV&paint=P0475&fabric=FMAH7&modelRangeCode=G20&options=S08WD,S06C3,S05DM,S06NX,S01A1,S01HY,S0710,S05AC,S08KM,S0715,S08WM,S0430,S0431,S02VL,S06AK,S06VB,S0544,S08S3,S0548,S0428,S0825,S0704,S05DA,S0465,S0491,S0Z38,S0258,S0775,S0534,S0853,S0337,S06AC,S0459,S06AE,S0493,S04UR,S05AS,S07EW,S08TG,S02TB,S06CP,S02PA,S04LN,S0925,S0481,S0880,S0760,S0322,S06U3', 1),
(57, 'BMW', '330i', 'Sedan', 'Images/BMW/330i.webp', 'Petrol', 313800.00, 'bmw.com.my/en-my/sl/vehiclefinder/details/01945e17-519f-7319-a68e-64952688cbe7?filters=%257B%2522MARKETING_SERIES%2522%253A%255B%25223%2522%255D%252C%2522MARKETING_MODEL_RANGE%2522%253A%255B%25223_G20%2522%255D%257D&sorting=PRODUCTION_DATE_ASC&modelCode=58FV&paint=P0475&fabric=FMAMU&modelRangeCode=G20&options=S08WD,S06C3,S0Z58,S05DM,S06NX,S01A1,S01HY,S0676,S0710,S05AC,S08KM,S0715,S08WM,S0430,S0431,S02VL,S02NH,S06AK,S02VF,S06VB,S0544,S08S3,S0548,S0428,S0825,S05DA,S0465,S0491,S0258,S0775,S0534,S0853,S0337,S06AC,S0459,S06AE,S0493,S04UR,S05AS,S07EW,S08TG,S02TB,S06CP,S02PA,S04LN,S0925,S0481,S0880,S0760,S0322,S06U3', 1),
(58, 'BMW', '520i', 'Sedan', 'Images/BMW/520i.webp', 'Petrol', 357800.00, 'bmw.com.my/en-my/sl/vehiclefinder/details/01945dfc-8704-7853-b5aa-58618f1f0ca0?filters=%257B%2522MARKETING_SERIES%2522%253A%255B%25225%2522%255D%252C%2522MARKETING_MODEL_RANGE%2522%253A%255B%2522i5_G60%2522%255D%252C%2522ENGINE_TYPE%2522%253A%255B%2522GASOLINE%2522%255D%257D&sorting=PRODUCTION_DATE_ASC&modelCode=18FJA7&paint=P0475&fabric=FKSJX&modelRangeCode=G60&options=S06C3%2CS03DP%2CS05DN%2CS06NX%2CS0Z19%2CS0710%2CS0316%2CS08KM%2CS0552%2CS0674%2CS04NB%2CS02VB%2CS02VC%2CS06AK%2CS0225%2CS0302%2CS04NR%2CS0548%2CS0428%2CS04FL%2CS0825%2CS09C4%2CS03G8%2CS0775%2CS0853%2CS09T1%2CS0337%2CS09T2%2CS06AC%2CS0459%2CS0416%2CS06AE%2CS04UR%2CS0212%2CS05AS%2CS05AT%2CS07EW%2CS08TF%2CS08TG%2CS06CP%2CS02TE%2CS02PA%2CS06U7%2CS0925%2CS0880%2CS0760%2CS043R%2CS0322%2CS0488%2CS06U3', 1),
(59, 'BMW', '530i', 'Sedan', 'Images/BMW/530i.webp', 'Petrol', 399800.00, 'bmw.com.my/en-my/sl/vehiclefinder/details/01953774-fa67-7e09-b932-75761bd0b4ce?filters=%257B%2522MARKETING_SERIES%2522%253A%255B%25225%2522%255D%252C%2522MARKETING_MODEL_RANGE%2522%253A%255B%2522i5_G60%2522%255D%252C%2522ENGINE_TYPE%2522%253A%255B%2522GASOLINE%2522%255D%257D&sorting=PRODUCTION_DATE_ASC&modelCode=48FJA7&paint=P0A96&fabric=FVCJL&modelRangeCode=G60&options=S06C3%2CS03MB%2CS03DP%2CS05DN%2CS06NX%2CS0710%2CS0316%2CS08KM%2CS0552%2CS09TA%2CS04NB%2CS02VB%2CS0Z48%2CS02VC%2CS06AK%2CS06F4%2CS0302%2CS04NR%2CS0548%2CS0428%2CS04FL%2CS0825%2CS01DB%2CS02VV%2CS09C4%2CS0775%2CS0853%2CS09T1%2CS0337%2CS09T2%2CS06AC%2CS0459%2CS0416%2CS06AE%2CS03GN%2CS04UR%2CS0212%2CS01CE%2CS05AS%2CS05AT%2CS07EW%2CS08TF%2CS08TG%2CS06CP%2CS02TE%2CS02PA%2CS06U7%2CS0925%2CS0880%2CS043R%2CS0322%2CS0488%2CS06U3', 1),
(60, 'BMW', 'i4 eDrive35', 'Sedan', 'Images/BMW/i4 edrive35.webp', 'EV', 294800.00, 'bmw.com.my/en-my/sl/vehiclefinder/details/0197cf99-afc7-7a5e-a94b-b66ba0bae9a8?filters=%257B%2522MARKETING_MODEL_RANGE%2522%253A%255B%2522i4_G26%2522%255D%252C%2522ENGINE_TYPE%2522%253A%255B%2522ELECTRIC%2522%255D%257D&sorting=PRODUCTION_DATE_ASC&modelCode=12HD&paint=P0C31&fabric=FEPMI&modelRangeCode=G26&options=S08WD%2CS06C3%2CS05YM%2CS05DN%2CS06NX%2CS02BQ%2CS08WC%2CS0710%2CS05AC%2CS0715%2CS08WN%2CS0430%2CS0552%2CS0431%2CS04T3%2CS02NH%2CS02VB%2CS02VC%2CS08R9%2CS06AK%2CS06VB%2CS03GX%2CS05DF%2CS0548%2CS0428%2CS0825%2CS05DA%2CS0491%2CS0775%2CS0534%2CS0853%2CS0337%2CS06AC%2CS0459%2CS06AE%2CS0493%2CS04V1%2CS04UR%2CS05AS%2CS06PA%2CS08TF%2CS08TG%2CS06CP%2CS02PA%2CS04LN%2CS04U9%2CS0925%2CS0880%2CS0760%2CS0322%2CS06U3', 1),
(61, 'BMW', 'i5 eDrive40', 'Sedan', 'Images/BMW/i5 eDrive40.webp', 'EV', 400800.00, 'bmw.com.my/en-my/sl/vehiclefinder/details/018f4bf7-bc93-7b1c-b3b7-8b1c9e4e14d4?filters=%257B%2522MARKETING_SERIES%2522%253A%255B%25225%2522%255D%252C%2522MARKETING_MODEL_RANGE%2522%253A%255B%2522i5_G60%2522%255D%252C%2522ENGINE_TYPE%2522%253A%255B%2522ELECTRIC%2522%255D%257D&sorting=PRODUCTION_DATE_ASC&modelCode=32FK&paint=P0475&fabric=FVCJL&modelRangeCode=G60&options=S06C3%2CS03DP%2CS05DN%2CS06NX%2CS0710%2CS0316%2CS08WN%2CS0552%2CS04T2%2CS04T3%2CS04NB%2CS03GQ%2CS02VB%2CS02VC%2CS06AK%2CS06F4%2CS0302%2CS03M1%2CS04NR%2CS0548%2CS0428%2CS04FL%2CS0825%2CS02VV%2CS0775%2CS0853%2CS09T1%2CS0337%2CS09T2%2CS06AC%2CS0459%2CS0416%2CS06AE%2CS04V1%2CS04UR%2CS0212%2CS07EW%2CS05AU%2CS08TF%2CS08TG%2CS06CP%2CS02PA%2CS04U6%2CS04U9%2CS06U7%2CS0925%2CS0880%2CS0760%2CS043R%2CS0322%2CS0488%2CS06U3', 1),
(62, 'BMW', 'i7 xDrive60', 'Sedan', 'Images/BMW/i7 xDrive60.webp', 'EV', 714800.00, 'bmw.com.my/en-my/sl/vehiclefinder/details/018e9113-c767-7926-8b2b-e54c41fb6ce1?filters=%257B%2522MARKETING_SERIES%2522%253A%255B%25227%2522%255D%252C%2522MARKETING_MODEL_RANGE%2522%253A%255B%2522i7_G70%2522%255D%252C%2522ENGINE_TYPE%2522%253A%255B%2522ELECTRIC%2522%255D%257D&sorting=PRODUCTION_DATE_ASC&modelCode=52EJ&paint=P0A96&fabric=FVCF2&modelRangeCode=G70&options=S06C3%2CS03DN%2CS046A%2CS05DN%2CS06NX%2CS07M7%2CS04T6%2CS0710%2CS08WN%2CS04T3%2CS04T4%2CS02VB%2CS02VC%2CS06AK%2CS04F4%2CS04F5%2CS02VH%2CS0302%2CS06FH%2CS0548%2CS0428%2CS04FL%2CS0825%2CS04FM%2CS03DM%2CS04A2%2CS0775%2CS0853%2CS09T1%2CS0337%2CS09T2%2CS06AC%2CS0416%2CS06AE%2CS04V1%2CS0453%2CS0454%2CS0212%2CS03CD%2CS06PA%2CS05AU%2CS08TF%2CS08TG%2CS06CP%2CS02PA%2CS043C%2CS04U9%2CS0407%2CS0925%2CS04HA%2CS01FD%2CS0880%2CS0760%2CS06U3', 1),
(63, 'BMW', 'X3 20 xDrive', 'SUV', 'Images/BMW/x3 20 xDrive.webp', 'Petrol', 325800.00, 'bmw.com.my/en/all-models/x-series/x3/bmw-x3.html', 1),
(65, 'BMW', 'X7 xDrive40i', 'SUV', 'Images/BMW/x7 xDrive40i.webp', 'Hybrid', 630800.00, 'bmw.com.my/en/all-models/x-series/x7/bmw-x7.html', 1),
(69, 'Mercedes-Benz', 'A-200', 'Sedan', 'Images/Mercedes-Benz/A-Class.avif', 'Petrol', 241888.00, 'mercedes-benz.com.my/passengercars/mercedes-benz-cars/car-configurator.html/motorization/CCci/MY/en/bm/17714762_MY1,17715162_MY2,17718762_MY4', 1),
(70, 'Mercedes-Benz', 'C-Class', 'Sedan', 'Images/Mercedes-Benz/C-Class.avif', 'Petrol', 249888.00, 'mercedes-benz.com.my/passengercars/models/saloon/c-class/overview.html', 1),
(71, 'Mercedes-Benz', 'EQS', 'Sedan', 'Images/Mercedes-Benz/EQS.avif', 'EV', 379888.00, 'mercedes-benz.com.my/passengercars/models/saloon/eqe/overview.htmlhttps://www.mercedes-benz.com.my/passengercars/models/saloon/eqs/overview.html', 1),
(72, 'Mercedes-Benz', 'S-Class', 'Sedan', 'Images/Mercedes-Benz/S-Class.avif', 'Hybrid', 738888.00, 'mercedes-benz.com.my/passengercars/models/saloon-long/s-class/overview.html', 1),
(73, 'Mercedes-Benz', 'E-Class', 'Sedan', 'Images/Mercedes-Benz/E-Class.avif', 'Hybrid', 367888.00, 'mercedes-benz.com.my/passengercars/models/saloon/e-class/overview.html', 1),
(74, 'Mercedes-Benz', 'AMG-A-Class Hatchback', 'Hatchback', 'Images/Mercedes-Benz/AMG-A-Class Hatchback.avif', 'Petrol', 530888.00, 'mercedes-benz.com.my/passengercars/models/hatchback/a-class/amg.html', 1),
(75, 'Mercedes-Benz', 'GLC', 'SUV', 'Images/Mercedes-Benz/GLC.avif', 'Hybrid', 336888.00, 'mercedes-benz.com.my/passengercars/models/suv/glc/overview.html', 1),
(76, 'Mercedes-Benz', 'EQA', 'SUV', 'Images/Mercedes-Benz/EQA.avif', 'EV', 296888.00, 'mercedes-benz.com.my/passengercars/models/suv/eqa/overview.html', 1),
(77, 'Mercedes-Benz', 'EQE 350+', 'SUV', 'Images/Mercedes-Benz/EQE.avif', 'EV', 398888.00, 'mercedes-benz.com.my/passengercars/mercedes-benz-cars/car-configurator.html/motorization/CCci/MY/en/bm/29462122_MY2,29462122_MY4,29462222_MY4', 1),
(78, 'Mercedes-Benz', 'GLA 200', 'SUV', 'Images/Mercedes-Benz/GLA.avif', 'Petrol', 258888.00, 'mercedes-benz.com.my/passengercars/models/suv/gla/overview.html', 1),
(128, 'BYD', 'Seal 6', 'Sedan', 'Images/BYD/Seal 6.png', 'EV', 100000.00, 'byd.simemotors.my/models/byd-seal-6.html', 1),
(129, 'BYD', 'M6', 'MPV', NULL, 'EV', 109800.00, 'byd.simemotors.my/models/byd-m6.html', 1),
(130, 'Tesla', 'Model 3', 'Sedan', NULL, 'EV', 147600.00, 'tesla.com/en_my/model3/design#overview', 1);

--
-- Triggers `cars`
--
DELIMITER $$
CREATE TRIGGER `trg_insert_ev_car` AFTER INSERT ON `cars` FOR EACH ROW BEGIN
    IF NEW.fuel = 'EV' THEN
        INSERT INTO ev_cars (id, power_output_kw)
        VALUES (NEW.id, 0);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `car_fuel_efficiency`
--

CREATE TABLE `car_fuel_efficiency` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `km_per_liter` decimal(5,2) DEFAULT NULL,
  `full_tank_liters` decimal(10,2) DEFAULT NULL,
  `engine_cc` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_fuel_efficiency`
--

INSERT INTO `car_fuel_efficiency` (`id`, `car_id`, `km_per_liter`, `full_tank_liters`, `engine_cc`) VALUES
(1, 1, 22.70, 36.00, 998),
(2, 2, 21.30, 36.00, 1329),
(3, 3, 22.70, 33.00, 998),
(4, 4, 23.30, 36.00, 998),
(5, 5, 21.70, 36.00, 1329),
(6, 6, 21.30, 36.00, 1496),
(7, 7, 18.90, 36.00, 996),
(8, 8, 22.20, 43.00, 1496),
(9, 9, 15.60, 45.00, 1496),
(10, 11, 14.90, 40.00, 1499),
(11, 12, 15.20, 40.00, 1597),
(12, 13, 16.10, 50.00, 1477),
(13, 14, 15.40, 45.00, 1477),
(14, 15, 14.30, 60.00, 1477),
(15, 16, 17.20, 60.00, 1477),
(16, 17, 47.60, 60.00, 1499),
(17, 20, 19.20, 40.00, 1496),
(18, 21, 15.40, 50.00, 1798),
(19, 22, 14.70, 60.00, 2487),
(20, 23, 17.20, 42.00, 1496),
(21, 24, 23.30, 36.00, 1798),
(22, 25, 15.90, 43.00, 1496),
(23, 26, 11.60, 75.00, 2493),
(24, 27, 11.10, 75.00, 2393),
(25, 28, 14.50, 80.00, 2393),
(26, 29, 12.70, 80.00, 2755),
(27, 31, 17.90, 40.00, 1498),
(28, 32, 17.90, 40.00, 1498),
(29, 33, 16.70, 47.00, 1498),
(30, 34, 27.80, 40.00, 1993),
(31, 35, 16.70, 40.00, 1498),
(32, 36, 15.40, 40.00, 1498),
(33, 37, 15.20, 57.00, 1498),
(34, 38, 20.00, 40.00, 1993),
(35, 39, 18.50, 35.00, 999),
(36, 41, 13.30, 60.00, 1997),
(37, 42, 12.00, 60.00, 2488),
(38, 43, 14.30, 55.00, 1997),
(39, 44, 11.80, 80.00, 2488),
(40, 45, 18.20, 51.00, 1496),
(41, 46, 16.10, 51.00, 1998),
(42, 47, 18.20, 51.00, 1496),
(43, 48, 15.90, 51.00, 1998),
(44, 49, 14.50, 56.00, 1998),
(45, 50, 16.90, 58.00, 2191),
(46, 51, 13.90, 58.00, 2488),
(47, 52, 14.70, 62.00, 2488),
(48, 53, 16.10, 52.00, 1499),
(49, 54, 14.50, 45.00, 1499),
(50, 55, 13.30, 75.00, 2442),
(51, 56, 15.40, 59.00, 1998),
(52, 57, 15.20, 59.00, 1998),
(53, 58, 16.40, 60.00, 1998),
(54, 59, 14.90, 68.00, 1998),
(55, 63, 13.20, 65.00, 1998),
(57, 65, 12.20, 80.00, 2998),
(58, 69, 17.90, 43.00, 1332),
(59, 70, 15.20, 66.00, 1496),
(60, 72, 45.50, 76.00, 2999),
(61, 73, 62.50, 66.00, 1999),
(62, 74, 11.90, 51.00, 1991),
(63, 75, 13.30, 62.00, 1999),
(64, 78, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ev_cars`
--

CREATE TABLE `ev_cars` (
  `ev_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `power_output_kw` decimal(6,2) NOT NULL,
  `roadtax` decimal(10,2) GENERATED ALWAYS AS (case when `power_output_kw` <= 100.00 then if(`power_output_kw` <= 50.00,20.00,20.00 + ceiling((`power_output_kw` - 50.00) / 10.00) * 10.00) when `power_output_kw` <= 210.00 then if(`power_output_kw` <= 110.00,80.00,80.00 + ceiling((`power_output_kw` - 110.00) / 10.00) * 20.00) when `power_output_kw` <= 310.00 then if(`power_output_kw` <= 220.00,305.00,305.00 + ceiling((`power_output_kw` - 220.00) / 10.00) * 30.00) when `power_output_kw` <= 410.00 then if(`power_output_kw` <= 320.00,615.00,615.00 + ceiling((`power_output_kw` - 320.00) / 10.00) * 50.00) else if(`power_output_kw` <= 420.00,1140.00,1140.00 + ceiling((`power_output_kw` - 420.00) / 10.00) * 100.00) end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ev_cars`
--

INSERT INTO `ev_cars` (`ev_id`, `id`, `power_output_kw`) VALUES
(43, 10, 150.00),
(44, 18, 58.00),
(45, 19, 160.00),
(46, 30, 0.00),
(47, 40, 110.00),
(48, 60, 210.00),
(49, 61, 250.00),
(50, 62, 400.00),
(54, 71, 265.00),
(55, 76, 140.00),
(56, 77, 215.00),
(57, 128, 160.00),
(58, 129, 0.00),
(60, 130, 208.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_admin_modifier` (`last_modified_by`);

--
-- Indexes for table `car_fuel_efficiency`
--
ALTER TABLE `car_fuel_efficiency`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `car_id` (`car_id`);

--
-- Indexes for table `ev_cars`
--
ALTER TABLE `ev_cars`
  ADD PRIMARY KEY (`ev_id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `car_fuel_efficiency`
--
ALTER TABLE `car_fuel_efficiency`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `ev_cars`
--
ALTER TABLE `ev_cars`
  MODIFY `ev_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `fk_admin_modifier` FOREIGN KEY (`last_modified_by`) REFERENCES `admins` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `car_fuel_efficiency`
--
ALTER TABLE `car_fuel_efficiency`
  ADD CONSTRAINT `fk_car_fuel` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);

--
-- Constraints for table `ev_cars`
--
ALTER TABLE `ev_cars`
  ADD CONSTRAINT `fk_ev_car` FOREIGN KEY (`id`) REFERENCES `cars` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
