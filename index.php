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

// transaksi proses
$transaksiProses = 0;
$result = $koneksi->query("SELECT COUNT(*) AS total FROM transaksi WHERE STATUS = 'proses'");
if ($result) {
    $row = $result->fetch_assoc();
    $transaksiProses = $row['total'] ?? 0;
}

// transaksi selesai
$transaksiSelesai = 0;
$result = $koneksi->query("SELECT COUNT(*) AS total FROM transaksi WHERE STATUS = 'selesai'");
if ($result) {
    $row = $result->fetch_assoc();
    $transaksiSelesai = $row['total'] ?? 0;
}

// total pendapatan (hanya transaksi selesai)
$totalPendapatan = 0;
$result = $koneksi->query("SELECT COALESCE(SUM(total_harga),0) AS total FROM transaksi WHERE STATUS = 'selesai'");
if ($result) {
    $row = $result->fetch_assoc();
    $totalPendapatan = $row['total'] ?? 0;
}

// Pendapatan minggu ini
$pendapatanMingguIni = 0;
$result = $koneksi->query("SELECT COALESCE(SUM(total_harga),0) AS total FROM transaksi WHERE STATUS = 'selesai' AND YEARWEEK(tanggal_selesai) = YEARWEEK(NOW())");
if ($result) {
    $row = $result->fetch_assoc();
    $pendapatanMingguIni = $row['total'] ?? 0;
}

// Pendapatan bulan ini
$pendapatanBulanIni = 0;
$result = $koneksi->query("SELECT COALESCE(SUM(total_harga),0) AS total FROM transaksi WHERE STATUS = 'selesai' AND MONTH(tanggal_selesai) = MONTH(NOW()) AND YEAR(tanggal_selesai) = YEAR(NOW())");
if ($result) {
    $row = $result->fetch_assoc();
    $pendapatanBulanIni = $row['total'] ?? 0;
}

// Data untuk grafik - 7 hari terakhir
$dataGrafik = [];
for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $result = $koneksi->query("SELECT COALESCE(SUM(total_harga),0) AS total FROM transaksi WHERE STATUS = 'selesai' AND DATE(tanggal_selesai) = '$tanggal'");
    $row = $result->fetch_assoc();
    $dataGrafik[] = [
        'tanggal' => date('d M', strtotime($tanggal)),
        'tanggal_penuh' => date('l, d F Y', strtotime($tanggal)),
        'total' => $row['total'] ?? 0
    ];
}


// Data bulanan untuk 6 bulan terakhir
$dataBulanan = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $result = $koneksi->query("SELECT COALESCE(SUM(total_harga),0) AS total FROM transaksi WHERE STATUS = 'selesai' AND DATE_FORMAT(tanggal_selesai, '%Y-%m') = '$bulan'");
    $row = $result->fetch_assoc();
    $dataBulanan[] = [
        'bulan' => date('M Y', strtotime($bulan . '-01')),
        'total' => $row['total'] ?? 0
    ];
}

// Transaksi terbaru
$transaksiTerbaru = [];
$result = $koneksi->query("SELECT t.*, p.nama_pelanggan FROM transaksi t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan ORDER BY t.tanggal_masuk DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $transaksiTerbaru[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Laundry SMBD</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(139, 92, 246, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(14, 165, 233, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(236, 72, 153, 0.2) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes backgroundMove {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            animation: slideDown 0.8s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .logo-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 900;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo-text p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.95);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: var(--danger);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease backwards;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-card.blue .stat-icon { background: linear-gradient(135deg, #0ea5e9, #06b6d4); color: white; }
        .stat-card.purple .stat-icon { background: linear-gradient(135deg, #8b5cf6, #a78bfa); color: white; }
        .stat-card.pink .stat-icon { background: linear-gradient(135deg, #ec4899, #f472b6); color: white; }
        .stat-card.green .stat-icon { background: linear-gradient(135deg, #10b981, #34d399); color: white; }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
            background: linear-gradient(135deg, var(--dark), var(--gray));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .stat-link {
            margin-top: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .stat-link:hover {
            gap: 0.75rem;
            color: var(--primary-dark);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Card */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.6s ease backwards;
            animation-delay: 0.5s;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(100, 116, 139, 0.1);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
            color: var(--dark);
        }

        /* Chart Container */
        #revenueChart {
            height: 350px;
        }

        .chart-tab {
            padding: 0.5rem 1rem;
            border: 2px solid rgba(14, 165, 233, 0.2);
            background: transparent;
            color: var(--gray);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .chart-tab:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .chart-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
            color: white;
        }

        /* Transaction List */
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            background: rgba(248, 250, 252, 0.5);
            transition: all 0.3s ease;
        }

        .transaction-item:hover {
            background: rgba(248, 250, 252, 1);
            transform: translateX(5px);
        }

        .transaction-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .transaction-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .transaction-amount {
            font-weight: 700;
            color: var(--success);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .status-badge.proses {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-badge.selesai {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        /* Navigation Pills */
        .nav-pills {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .nav-pill {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.95);
            color: var(--dark);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .nav-pill:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            background: white;
        }

        .nav-pill i {
            font-size: 1.1rem;
        }

        /* Footer */
        .dashboard-footer {
            text-align: center;
            padding: 2rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .quick-stat {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(139, 92, 246, 0.1));
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
        }

        .quick-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .quick-stat-label {
            font-size: 0.8rem;
            color: var(--gray);
            font-weight: 500;
        }

        /* Revenue Summary Grid - Optimized */
        .revenue-summary-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .revenue-summary-card {
            animation-delay: 0.6s;
        }

        .status-chart-card {
            animation-delay: 0.65s;
        }

        .revenue-summary-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
        }

        .revenue-stat-item {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(139, 92, 246, 0.05));
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .revenue-stat-item:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(139, 92, 246, 0.1));
            transform: translateY(-3px);
        }

        .revenue-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            flex-shrink: 0;
        }

        .revenue-stat-content {
            flex: 1;
        }

        .revenue-stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
            font-family: 'DM Sans', sans-serif;
        }

        .revenue-stat-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 500;
        }

        .revenue-stat-separator {
            width: 2px;
            height: 60px;
            background: linear-gradient(to bottom, transparent, rgba(100, 116, 139, 0.2), transparent);
        }

        /* Status Chart Container - Optimized */
        .status-chart-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        #statusChart {
            flex-shrink: 0;
            width: 180px;
            height: 180px;
        }

        .status-summary {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(248, 250, 252, 0.5);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .status-item:hover {
            background: rgba(248, 250, 252, 1);
            transform: translateX(5px);
        }

        .status-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-dot.proses {
            background: #f59e0b;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
        }

        .status-dot.selesai {
            background: #10b981;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }

        .status-count {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            font-family: 'Playfair Display', serif;
        }

        .status-text {
            font-size: 0.9rem;
            color: var(--gray);
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .logo-text h1 {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 2rem;
            }

            .revenue-summary-grid {
                grid-template-columns: 1fr;
            }

            .revenue-summary-stats {
                flex-direction: column;
            }

            .revenue-stat-separator {
                width: 100%;
                height: 2px;
                background: linear-gradient(to right, transparent, rgba(100, 116, 139, 0.2), transparent);
            }

            .status-chart-container {
                flex-direction: column;
            }

            #statusChart {
                width: 200px;
                height: 200px;
            }

            .status-summary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-soap"></i>
                </div>
                <div class="logo-text">
                    <h1>Laundry SMBD</h1>
                    <p>Sistem Manajemen Laundry Modern</p>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: var(--dark);">Admin</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">Administrator</div>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Navigation Pills -->
        <div class="nav-pills">
            <a href="index.php" class="nav-pill" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="pelanggan.php" class="nav-pill">
                <i class="fas fa-users"></i> Pelanggan
            </a>
            <a href="layanan.php" class="nav-pill">
                <i class="fas fa-concierge-bell"></i> Layanan
            </a>
            <a href="transaksi.php" class="nav-pill">
                <i class="fas fa-shopping-cart"></i> Transaksi
            </a>
            <a href="laporan_pendapatan.php" class="nav-pill">
                <i class="fas fa-chart-line"></i> Laporan
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $totalPelanggan; ?></div>
                <div class="stat-label">Total Pelanggan</div>
                <a href="pelanggan.php" class="stat-link">
                    Lihat Detail <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <div class="stat-value"><?php echo $totalLayanan; ?></div>
                <div class="stat-label">Jenis Layanan</div>
                <a href="layanan.php" class="stat-link">
                    Kelola Layanan <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="stat-card pink">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo $totalTransaksi; ?></div>
                <div class="stat-label">Total Transaksi</div>
                <a href="transaksi.php" class="stat-link">
                    Lihat Transaksi <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">Rp <?php echo number_format($totalPendapatan/1000, 0); ?>K</div>
                <div class="stat-label">Total Pendapatan</div>
                <a href="laporan_pendapatan.php" class="stat-link">
                    Lihat Laporan <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Revenue Chart -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📈 Grafik Pendapatan Harian</h2>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="chart-tab active" data-chart="daily">7 Hari</button>
                        <button class="chart-tab" data-chart="monthly">6 Bulan</button>
                    </div>
                </div>
                <div style="position: relative;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔔 Transaksi Terbaru</h2>
                </div>

                <div class="quick-stats">
                    <div class="quick-stat">
                        <div class="quick-stat-value"><?php echo $transaksiProses; ?></div>
                        <div class="quick-stat-label">Sedang Proses</div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-value"><?php echo $transaksiSelesai; ?></div>
                        <div class="quick-stat-label">Selesai</div>
                    </div>
                </div>

                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($transaksiTerbaru as $trans): ?>
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <h4><?php echo $trans['nama_pelanggan']; ?></h4>
                            <p><?php echo date('d M Y', strtotime($trans['tanggal_masuk'])); ?></p>
                            <span class="status-badge <?php echo $trans['STATUS']; ?>">
                                <?php echo ucfirst($trans['STATUS']); ?>
                            </span>
                        </div>
                        <div class="transaction-amount">
                            Rp <?php echo number_format($trans['total_harga'], 0, ',', '.'); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Additional Stats - Optimized Single Row -->
        <div class="revenue-summary-grid">
            <div class="card revenue-summary-card">
                <div class="card-header">
                    <h2 class="card-title">💰 Ringkasan Pendapatan</h2>
                </div>
                <div class="revenue-summary-stats">
                    <div class="revenue-stat-item">
                        <div class="revenue-stat-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="revenue-stat-content">
                            <div class="revenue-stat-value">Rp <?php echo number_format($pendapatanMingguIni, 0, ',', '.'); ?></div>
                            <div class="revenue-stat-label">Minggu Ini</div>
                        </div>
                    </div>
                    <div class="revenue-stat-separator"></div>
                    <div class="revenue-stat-item">
                        <div class="revenue-stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="revenue-stat-content">
                            <div class="revenue-stat-value">Rp <?php echo number_format($pendapatanBulanIni, 0, ',', '.'); ?></div>
                            <div class="revenue-stat-label">Bulan Ini</div>
                        </div>
                    </div>
                    <div class="revenue-stat-separator"></div>
                    <div class="revenue-stat-item">
                        <div class="revenue-stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="revenue-stat-content">
                            <div class="revenue-stat-value">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></div>
                            <div class="revenue-stat-label">Total Keseluruhan</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card status-chart-card">
                <div class="card-header">
                    <h2 class="card-title">📊 Status Transaksi</h2>
                </div>
                <div class="status-chart-container">
                    <canvas id="statusChart"></canvas>
                    <div class="status-summary">
                        <div class="status-item">
                            <div class="status-dot proses"></div>
                            <div>
                                <div class="status-count"><?php echo $transaksiProses; ?></div>
                                <div class="status-text">Proses</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-dot selesai"></div>
                            <div>
                                <div class="status-count"><?php echo $transaksiSelesai; ?></div>
                                <div class="status-text">Selesai</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <div class="dashboard-footer">
        <strong>&copy; 2025 Laundry SMBD.</strong> All rights reserved. | Version 2.0.0
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ==============================================
        // DATA DARI PHP
        // ==============================================
        const revenueDataDaily = <?php echo json_encode($dataGrafik); ?>;
        const revenueDataMonthly = <?php echo json_encode($dataBulanan); ?>;
        
        // ==============================================
        // REVENUE CHART - DAILY & MONTHLY
        // ==============================================
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Gradient untuk area
        const gradientDaily = ctx.createLinearGradient(0, 0, 0, 400);
        gradientDaily.addColorStop(0, 'rgba(14, 165, 233, 0.3)');
        gradientDaily.addColorStop(1, 'rgba(14, 165, 233, 0.01)');

        let revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueDataDaily.map(d => d.tanggal),
                datasets: [{
                    label: 'Pendapatan',
                    data: revenueDataDaily.map(d => d.total),
                    borderColor: '#0ea5e9',
                    backgroundColor: gradientDaily,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0ea5e9',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#8b5cf6',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.98)',
                        padding: 16,
                        borderColor: '#0ea5e9',
                        borderWidth: 2,
                        titleColor: '#fff',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyColor: '#fff',
                        bodyFont: {
                            size: 13
                        },
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return revenueDataDaily[index].tanggal_penuh || context[0].label;
                            },
                            label: function(context) {
                                return 'Pendapatan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(100, 116, 139, 0.1)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            padding: 10,
                            font: {
                                size: 11,
                                weight: '600'
                            },
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value/1000000).toFixed(1) + 'Jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value/1000) + 'K';
                                }
                                return 'Rp ' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            padding: 10,
                            font: {
                                size: 11,
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });

        // Chart Tab Switching
        const chartTabs = document.querySelectorAll('.chart-tab');
        let currentChartData = revenueDataDaily;

        chartTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                chartTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const chartType = this.dataset.chart;
                
                if (chartType === 'daily') {
                    currentChartData = revenueDataDaily;
                    revenueChart.data.labels = revenueDataDaily.map(d => d.tanggal);
                    revenueChart.data.datasets[0].data = revenueDataDaily.map(d => d.total);
                } else if (chartType === 'monthly') {
                    currentChartData = revenueDataMonthly;
                    revenueChart.data.labels = revenueDataMonthly.map(d => d.bulan);
                    revenueChart.data.datasets[0].data = revenueDataMonthly.map(d => d.total);
                }
                
                revenueChart.update('active');
            });
        });

        // ==============================================
        // STATUS CHART - DOUGHNUT (COMPACT VERSION)
        // ==============================================
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Proses', 'Selesai'],
                datasets: [{
                    data: [<?php echo $transaksiProses; ?>, <?php echo $transaksiSelesai; ?>],
                    backgroundColor: [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(16, 185, 129, 0.8)'
                    ],
                    borderColor: [
                        '#f59e0b',
                        '#10b981'
                    ],
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(30, 41, 59, 0.98)',
                        padding: 12,
                        borderWidth: 2,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $transaksiProses + $transaksiSelesai; ?>;
                                const value = context.parsed;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });


    </script>
</body>
</html>