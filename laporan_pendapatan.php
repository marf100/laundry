<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo "<script>alert('Silakan login terlebih dahulu'); window.location='login.php';</script>";
    exit();
}

$koneksi = new mysqli("localhost", "root", "", "laundry_db");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Hitung statistik pendapatan
$total_result = $koneksi->query("SELECT SUM(total_harga) AS grand_total FROM transaksi WHERE STATUS = 'selesai'");
$total_data = $total_result->fetch_assoc();
$grand_total = $total_data['grand_total'] ?? 0;

// Pendapatan hari ini
$today_result = $koneksi->query("SELECT SUM(total_harga) AS total FROM transaksi WHERE STATUS = 'selesai' AND DATE(tanggal_selesai) = CURDATE()");
$today_data = $today_result->fetch_assoc();
$pendapatan_hari_ini = $today_data['total'] ?? 0;

// Pendapatan minggu ini
$week_result = $koneksi->query("SELECT SUM(total_harga) AS total FROM transaksi WHERE STATUS = 'selesai' AND YEARWEEK(tanggal_selesai) = YEARWEEK(NOW())");
$week_data = $week_result->fetch_assoc();
$pendapatan_minggu_ini = $week_data['total'] ?? 0;

// Pendapatan bulan ini
$month_result = $koneksi->query("SELECT SUM(total_harga) AS total FROM transaksi WHERE STATUS = 'selesai' AND MONTH(tanggal_selesai) = MONTH(NOW()) AND YEAR(tanggal_selesai) = YEAR(NOW())");
$month_data = $month_result->fetch_assoc();
$pendapatan_bulan_ini = $month_data['total'] ?? 0;

// Jumlah transaksi selesai
$count_result = $koneksi->query("SELECT COUNT(*) AS total FROM transaksi WHERE STATUS = 'selesai'");
$count_data = $count_result->fetch_assoc();
$jumlah_transaksi = $count_data['total'] ?? 0;

// Rata-rata transaksi
$rata_rata = $jumlah_transaksi > 0 ? $grand_total / $jumlah_transaksi : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan - Cuci.in</title>
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
            padding: 2rem;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(139, 92, 246, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(14, 165, 233, 0.2) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes backgroundMove {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.6s ease;
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

        .header h1 {
            font-size: 2rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-family: 'Playfair Display', serif;
        }

        .header .icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-family: 'DM Sans', sans-serif;
        }

        .btn-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .btn-secondary:hover {
            background: rgba(100, 116, 139, 0.2);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
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
        .stat-card.green .stat-icon { background: linear-gradient(135deg, #10b981, #34d399); color: white; }
        .stat-card.purple .stat-icon { background: linear-gradient(135deg, #8b5cf6, #a78bfa); color: white; }
        .stat-card.orange .stat-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); color: white; }

        .stat-value {
            font-size: 2rem;
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

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
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

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        tfoot {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            font-weight: 700;
        }

        tfoot td {
            padding: 1.5rem 1rem;
            font-size: 1.1rem;
            color: var(--dark);
        }

        #revenueChart {
            height: 350px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                Laporan Pendapatan
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-value">Rp <?php echo number_format($pendapatan_hari_ini/1000, 0); ?>K</div>
                <div class="stat-label">Pendapatan Hari Ini</div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-value">Rp <?php echo number_format($pendapatan_minggu_ini/1000, 0); ?>K</div>
                <div class="stat-label">Pendapatan Minggu Ini</div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value">Rp <?php echo number_format($pendapatan_bulan_ini/1000, 0); ?>K</div>
                <div class="stat-label">Pendapatan Bulan Ini</div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-value">Rp <?php echo number_format($rata_rata/1000, 0); ?>K</div>
                <div class="stat-label">Rata-rata per Transaksi</div>
            </div>
        </div>

        <!-- Total Pendapatan Card -->
        <div class="card" style="background: linear-gradient(135deg, rgba(255, 255, 255, 1), rgba(118, 75, 162, 0.1)); border: 3px solid #667eea;">
            <div style="text-align: center;">
                <div style="font-size: 1.2rem; color: var(--white); margin-bottom: 1rem; font-weight: 600;">
                    💰 TOTAL KESELURUHAN PENDAPATAN
                </div>
                <div style="font-size: 3rem; font-weight: 900; font-family: 'Playfair Display', serif; background: linear-gradient(135deg, #000000ff, #fcfcfdff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.5rem;">
                    Rp <?php echo number_format($grand_total, 0, ',', '.'); ?>
                </div>
                <div style="font-size: 0.95rem; color: var(--white);">
                    Dari <strong><?php echo $jumlah_transaksi; ?></strong> transaksi selesai
                </div>
            </div>
        </div>

        <!-- Detail Transaksi Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📊 Detail Transaksi Selesai</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Selesai</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th style="text-align: right;">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT t.tanggal_selesai, t.total_harga, p.nama_pelanggan,
                                  GROUP_CONCAT(l.nama_layanan SEPARATOR ', ') AS layanan
                                  FROM transaksi t 
                                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                  LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                                  LEFT JOIN layanan l ON dt.id_layanan = l.id_layanan
                                  WHERE t.STATUS = 'selesai'
                                  GROUP BY t.id_transaksi
                                  ORDER BY t.tanggal_selesai DESC";
                        $result = $koneksi->query($query);
                        $no = 1;
                        
                        while ($row = $result->fetch_assoc()) {
                            $tanggal = date('d M Y', strtotime($row['tanggal_selesai']));
                            $total = $row['total_harga'];

                            echo "<tr>
                                    <td>{$no}</td>
                                    <td><strong>{$tanggal}</strong></td>
                                    <td>{$row['nama_pelanggan']}</td>
                                    <td>{$row['layanan']}</td>
                                    <td style='text-align: right;'><strong style='color: #10b981;'>Rp " . number_format($total, 0, ',', '.') . "</strong></td>
                                  </tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;">
                                <i class="fas fa-calculator"></i> TOTAL KESELURUHAN
                            </td>
                            <td style="text-align: right; font-size: 1.25rem; color: #667eea;">
                                Rp <?php echo number_format($grand_total, 0, ',', '.'); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const revenueData = <?php echo json_encode($dataGrafik); ?>;
        
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
        gradient.addColorStop(1, 'rgba(118, 75, 162, 0.05)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueData.map(d => d.bulan),
                datasets: [{
                    label: 'Pendapatan',
                    data: revenueData.map(d => d.total),
                    borderColor: '#667eea',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
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
                        borderColor: '#667eea',
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
    </script>
</body>
</html>