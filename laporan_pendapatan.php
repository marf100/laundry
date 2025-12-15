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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendapatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .highlight {
            background-color: #f1f1f1;
        }
        .table thead {
            background-color: #343a40;
            color: white;
        }
        .table tfoot {
            background-color: #dfe6e9;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold">📊 Laporan Pendapatan</h1>
        <p class="text-muted">Data total pemasukan berdasarkan tanggal penyelesaian transaksi</p>
    </div>

    <a href="index.php" class="btn btn-secondary mb-4">← Kembali ke Dashboard</a>

    <?php
    $query = "SELECT tanggal_selesai, SUM(total_harga) AS total 
              FROM laporan 
              GROUP BY tanggal_selesai 
              ORDER BY tanggal_selesai DESC";
    $result = $koneksi->query($query);
    $no = 1;
    $grand_total = 0;
    ?>

    <div class="card mb-4">
        <div class="card-body text-center">
            <h5 class="card-title">Total Keseluruhan Pendapatan</h5>
            <?php
            // Hitung total keseluruhan pendapatan
            $total_result = $koneksi->query("SELECT SUM(total_harga) AS grand_total FROM laporan");
            $total_data = $total_result->fetch_assoc();
            $grand_total = $total_data['grand_total'] ?? 0;
            ?>
            <h2 class="text-success">Rp <?= number_format($grand_total, 0, ',', '.') ?></h2>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="text-center">
        <tr>
            <th>No</th>
            <th>Tanggal Selesai</th>
            <th>Total Pendapatan</th>
        </tr>
        </thead>
        <tbody class="text-center">
        <?php
        // Jalankan ulang query
        $result = $koneksi->query($query);
        while ($row = $result->fetch_assoc()) {
            $tanggal = date('d M Y', strtotime($row['tanggal_selesai']));
            $total = $row['total'];

            echo "<tr>
                    <td>{$no}</td>
                    <td>{$tanggal}</td>
                    <td>Rp " . number_format($total, 0, ',', '.') . "</td>
                  </tr>";
            $no++;
        }
        ?>
        </tbody>
        <tfoot>
            <tr class="text-center">
                <td colspan="2">Total Keseluruhan</td>
                <td>Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
