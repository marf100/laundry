<?php
// Koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "laundry_db");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

session_start();
// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// ======================
// AMBIL DATA UNTUK DASHBOARD
// ======================

// total pelanggan
$totalPelanggan = 0;
$result = $koneksi->query("SELECT COUNT(*) AS total FROM pelanggan");
if ($result) {
    $row = $result->fetch_assoc();
    $totalPelanggan = $row['total'] ?? 0;
}

// total layanan
$totalLayanan = 0;
$result = $koneksi->query("SELECT COUNT(*) AS total FROM layanan");
if ($result) {
    $row = $result->fetch_assoc();
    $totalLayanan = $row['total'] ?? 0;
}

// total transaksi
$totalTransaksi = 0;
$result = $koneksi->query("SELECT COUNT(*) AS total FROM transaksi");
if ($result) {
    $row = $result->fetch_assoc();
    $totalTransaksi = $row['total'] ?? 0;
}

// total pendapatan (semua waktu)
$totalPendapatan = 0;
$result = $koneksi->query("SELECT COALESCE(SUM(total_bayar),0) AS total FROM transaksi");
if ($result) {
    $row = $result->fetch_assoc();
    $totalPendapatan = $row['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/css/adminlte.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-image: url('gambar/cucibajuya.jpg');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }

        .content-wrapper {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 20px;
        }

        .sidebar {
            background-color: #343a40;
        }

        .sidebar a {
            color: #fff;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .card {
            margin-bottom: 20px;
        }

        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light">Laundry SMBD</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column">
                    <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> <p>Dashboard</p></a></li>
                    <li class="nav-item"><a href="pelanggan.php" class="nav-link"><i class="fas fa-users"></i> <p> Pelanggan</p></a></li>
                    <li class="nav-item"><a href="layanan.php" class="nav-link"><i class="fas fa-concierge-bell"></i> <p> Layanan</p></a></li>
                    <li class="nav-item"><a href="transaksi.php" class="nav-link"><i class="fas fa-shopping-cart"></i> <p> Transaksi</p></a></li>
                    <li class="nav-item"><a href="laporan_pendapatan.php" class="nav-link"><i class="fas fa-file-alt"></i> <p>Laporan Pendapatan</p></a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> <p>Logout</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Konten Utama -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Dashboard</h1>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">

                <!-- ====== KOTAK STATISTIK (SMALL BOX) ====== -->
                <div class="row">
                    <!-- Pelanggan -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $totalPelanggan; ?></h3>
                                <p>Pelanggan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="pelanggan.php" class="small-box-footer">
                                Info lebih lanjut <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Layanan -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $totalLayanan; ?></h3>
                                <p>Layanan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <a href="layanan.php" class="small-box-footer">
                                Info lebih lanjut <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Transaksi -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $totalTransaksi; ?></h3>
                                <p>Transaksi</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <a href="transaksi.php" class="small-box-footer">
                                Info lebih lanjut <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pendapatan -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h4>Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></h4>
                                <p>Total Pendapatan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <a href="laporan_pendapatan.php" class="small-box-footer">
                                Lihat laporan <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ====== GRAFIK ====== -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Grafik Pemasukan Laundry Kiloan</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="chartKiloan"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Grafik Pemasukan Laundry Satuan</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="chartSatuan"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- kalau masih mau text tentang aplikasi, bisa bikin card kecil terpisah di bawah sini -->

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer text-center">
        <strong>&copy; 2023 Laundry SMBD.</strong> All rights reserved.
        <div class="float-right d-none d-sm-inline"><b>Version</b> 1.0.0</div>
    </footer>
</div>

<!-- ======================
     SCRIPT JS
====================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/js/adminlte.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// ==============================
// DATA CONTOH UNTUK GRAFIK
// (Silakan nanti dihubungkan ke database kalau mau dinamis)
// ==============================
const labelsHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// contoh data pemasukan (kamu bisa ubah sendiri)
const dataKiloan = [10, 12, 9, 14, 8, 11, 7];
const dataSatuan = [5, 3, 4, 6, 2, 5, 3];

// Grafik Kiloan
const ctxKiloan = document.getElementById('chartKiloan').getContext('2d');
new Chart(ctxKiloan, {
    type: 'bar',
    data: {
        labels: labelsHari,
        datasets: [{
            label: 'Pemasukan Kiloan (contoh)',
            data: dataKiloan
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Grafik Satuan
const ctxSatuan = document.getElementById('chartSatuan').getContext('2d');
new Chart(ctxSatuan, {
    type: 'bar',
    data: {
        labels: labelsHari,
        datasets: [{
            label: 'Pemasukan Satuan (contoh)',
            data: dataSatuan
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>