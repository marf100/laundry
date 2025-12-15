-- Buat database dan gunakan
CREATE DATABASE IF NOT EXISTS laundry_db;
USE laundry_db;

-- Tabel user (login)
CREATE TABLE IF NOT EXISTS USER (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    PASSWORD VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    ROLE ENUM('admin', 'kasir') DEFAULT 'kasir'
);

-- Tabel pelanggan
CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    alamat VARCHAR(255),
    no_hp VARCHAR(15),
    email VARCHAR(100)
);

-- Tabel layanan
CREATE TABLE IF NOT EXISTS layanan (
    id_layanan INT AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    durasi_hari INT NOT NULL
);

-- Tabel transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_pelanggan INT,
    tanggal_masuk DATE NOT NULL,
    tanggal_selesai DATE,
    STATUS ENUM('proses', 'selesai', 'diambil') DEFAULT 'proses',
    total_harga DECIMAL(10,2),
    FOREIGN KEY (id_user) REFERENCES USER(id_user),
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
);

-- Tabel detail_transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT,
    id_layanan INT,
    berat_kg DECIMAL(5,2) NOT NULL,
    subtotal DECIMAL(10,2),
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi),
    FOREIGN KEY (id_layanan) REFERENCES layanan(id_layanan)
);

-- Trigger: update total_harga otomatis setelah insert detail_transaksi
DELIMITER //

CREATE TRIGGER update_total_transaksi
AFTER INSERT ON detail_transaksi
FOR EACH ROW
BEGIN
  UPDATE transaksi
  SET total_harga = (
    SELECT SUM(subtotal)
    FROM detail_transaksi
    WHERE id_transaksi = NEW.id_transaksi
  )
  WHERE id_transaksi = NEW.id_transaksi;
END;
//

DELIMITER ;

-- DATA AWAL
-- User (admin & kasir)
INSERT INTO USER (username, PASSWORD, nama_lengkap, ROLE) VALUES
('admin1', 'admin123', 'Admin Utama', 'admin'),
('kasir1', 'kasir123', 'Kasir Satu', 'kasir');

-- Pelanggan
INSERT INTO pelanggan (nama_pelanggan, alamat, no_hp, email) VALUES
('Budi Santoso', 'Jl. Merdeka No. 1', '08123456789', 'budi@mail.com'),
('Siti Aminah', 'Jl. Sudirman No. 99', '08987654321', 'siti@mail.com');

-- Layanan
INSERT INTO layanan (nama_layanan, harga_per_kg, durasi_hari) VALUES
('Cuci Kering', 6000.00, 2),
('Cuci Setrika', 8000.00, 2),
('Setrika Saja', 5000.00, 1),
('Laundry Kilat', 10000.00, 1);

-- Transaksi (Budi, 20 Mei 2025)
INSERT INTO transaksi (id_user, id_pelanggan, tanggal_masuk, tanggal_selesai, STATUS, total_harga)
VALUES (2, 1, '2025-05-20', '2025-05-22', 'proses', 0.00);

-- Detail Transaksi (Budi pilih Cuci Setrika 2kg)
INSERT INTO detail_transaksi (id_transaksi, id_layanan, berat_kg, subtotal)
VALUES (1, 2, 2.00, 16000.00);
