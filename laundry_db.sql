-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 02:01 PM
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
-- Database: `laundry_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_layanan` int(11) DEFAULT NULL,
  `berat_kg` decimal(5,2) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_layanan`, `berat_kg`, `subtotal`) VALUES
(1, 1, 2, 2.00, 16000.00),
(3, 3, 4, 5.50, 55000.00),
(5, 5, 4, 100.00, 1000000.00),
(6, 6, 2, 30.00, 240000.00),
(7, 7, 2, 30.00, 240000.00),
(9, 9, 4, 2.00, 20000.00),
(10, 10, 2, 1.80, 14400.00),
(11, 11, 2, 3.00, 24000.00),
(12, 12, 3, 20.00, 100000.00),
(13, 13, 1, 2.00, 12000.00);

--
-- Triggers `detail_transaksi`
--
DELIMITER $$
CREATE TRIGGER `update_total_transaksi` AFTER INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
  UPDATE transaksi
  SET total_harga = (
    SELECT SUM(subtotal)
    FROM detail_transaksi
    WHERE id_transaksi = NEW.id_transaksi
  )
  WHERE id_transaksi = NEW.id_transaksi;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `id_layanan` int(11) DEFAULT NULL,
  `berat` decimal(5,2) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `total_harga` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga_per_kg` decimal(10,2) NOT NULL,
  `durasi_hari` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id_layanan`, `nama_layanan`, `harga_per_kg`, `durasi_hari`, `deskripsi`) VALUES
(1, 'Cuci Kering', 6000.00, 2, NULL),
(2, 'Cuci Setrika', 8000.00, 2, NULL),
(3, 'Setrika Saja', 5000.00, 1, NULL),
(4, 'Laundry Kilat', 10000.00, 1, NULL),
(6, 'cuci sepatu', 10.00, 3, NULL),
(7, 'cuci sepatu', 10000.00, 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `alamat`, `no_hp`, `email`) VALUES
(1, 'Budi Santoso', 'Jl. Merdeka No. 1', '082114952019', 'budi@mail.com'),
(2, 'Siti Aminah', 'Jl. Sudirman No. 99', '08987654321', 'siti@mail.com'),
(4, 'abyan', 'jalanin aja dulu', '082114952019', 'mairaloveabyan@gmail.com'),
(5, 'maung', 'jalan rusun nawa', '087884475203', 'maungski@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `total_bayar`
--

CREATE TABLE `total_bayar` (
  `id` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `total_bayar` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `STATUS` enum('proses','selesai','diambil') DEFAULT 'proses',
  `total_harga` decimal(10,2) DEFAULT NULL,
  `metode_pembayaran` enum('cash','transfer','qris') DEFAULT 'cash',
  `total_bayar` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_pelanggan`, `tanggal_masuk`, `tanggal_selesai`, `STATUS`, `total_harga`, `metode_pembayaran`, `total_bayar`) VALUES
(1, 2, 1, '2025-05-20', '2025-05-22', 'selesai', 16000.00, 'cash', 0),
(3, 2, 2, '2025-12-15', '2025-12-24', 'selesai', 55000.00, 'cash', 0),
(5, 2, 2, '2025-12-15', '2025-12-14', 'selesai', 1000000.00, 'cash', 0),
(6, 2, 1, '2025-12-15', '2025-12-19', 'selesai', 240000.00, 'cash', 0),
(7, 2, 2, '2025-12-15', '2025-12-28', 'selesai', 240000.00, 'qris', 0),
(9, 2, 1, '2025-12-15', '2025-12-23', 'selesai', 20000.00, 'qris', 0),
(10, 2, 1, '2025-12-15', '2025-12-18', 'selesai', 14400.00, 'qris', 0),
(11, 2, 1, '2025-12-15', '2025-12-28', 'selesai', 24000.00, 'qris', 0),
(12, 1, 4, '2025-12-15', '2025-12-30', 'selesai', 100000.00, 'qris', 0),
(13, 1, 5, '2025-12-15', '2025-12-24', 'selesai', 12000.00, 'qris', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `ROLE` enum('admin','kasir') DEFAULT 'kasir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `PASSWORD`, `nama_lengkap`, `ROLE`) VALUES
(1, 'admin1', 'admin123', 'Admin Utama', 'admin'),
(2, 'kasir1', 'admin123', 'Kasir Satu', 'kasir');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_layanan` (`id_layanan`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_layanan` (`id_layanan`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `total_bayar`
--
ALTER TABLE `total_bayar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id_layanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `total_bayar`
--
ALTER TABLE `total_bayar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`);

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `laporan_ibfk_3` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
