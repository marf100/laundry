<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo "<script>alert('Silakan login terlebih dahulu'); window.location='login.php';</script>";
    exit();
}

$id_user = $_SESSION['id_user'];
$koneksi = new mysqli("localhost", "root", "", "laundry_db");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Tambah transaksi
if (isset($_POST['tambah'])) {
    $id_pelanggan = $_POST['id_pelanggan'];
    $id_layanan = $_POST['id_layanan'];
    $berat = $_POST['berat'];
    $tanggal_masuk = date('Y-m-d');
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $status = 'proses';

    $getHarga = $koneksi->query("SELECT harga_per_kg FROM layanan WHERE id_layanan = '$id_layanan'");
    $hargaData = $getHarga->fetch_assoc();
    $harga_per_kg = $hargaData['harga_per_kg'];
    $total_harga = $harga_per_kg * $berat;

    $sql = "INSERT INTO transaksi (id_user, id_pelanggan, id_layanan, berat, tanggal_masuk, tanggal_selesai, STATUS, total_harga)
            VALUES ('$id_user', '$id_pelanggan', '$id_layanan', '$berat', '$tanggal_masuk', '$tanggal_selesai', '$status', '$total_harga')";
    $koneksi->query($sql);
    header("Location: transaksi.php");
}

// Hapus transaksi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $koneksi->query("DELETE FROM transaksi WHERE id_transaksi = $id");
    header("Location: transaksi.php");
}

// Selesaikan transaksi
if (isset($_GET['selesai'])) {
    $id = $_GET['selesai'];

    // Ambil data transaksi
    $result = $koneksi->query("SELECT * FROM transaksi WHERE id_transaksi = $id");
    $data = $result->fetch_assoc();

    // Simpan ke laporan
    $koneksi->query("INSERT INTO laporan (id_user, id_pelanggan, id_layanan, berat, tanggal_masuk, tanggal_selesai, total_harga)
                     VALUES (
                        '{$data['id_user']}', '{$data['id_pelanggan']}', '{$data['id_layanan']}',
                        '{$data['berat']}', '{$data['tanggal_masuk']}', '{$data['tanggal_selesai']}',
                        '{$data['total_harga']}')");

    // Hapus dari transaksi
    $koneksi->query("DELETE FROM transaksi WHERE id_transaksi = $id");

    header("Location: transaksi.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
        }
        .table thead {
            background-color: #343a40;
            color: white;
        }
        .btn-success, .btn-danger {
            width: 70px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold">Manajemen Transaksi</h2>
    <a href="index.php" class="btn btn-secondary mb-3">⬅ Kembali ke Dashboard</a>

    <form method="POST" class="card card-body shadow-sm mb-4">
        <div class="row">
            <div class="col-md-3 mb-2">
                <label>Pelanggan</label>
                <select name="id_pelanggan" class="form-control" required>
                    <?php
                    $pelanggan = $koneksi->query("SELECT * FROM pelanggan");
                    while ($row = $pelanggan->fetch_assoc()) {
                        echo "<option value='{$row['id_pelanggan']}'>{$row['nama_pelanggan']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label>Layanan</label>
                <select name="id_layanan" class="form-control" required>
                    <?php
                    $layanan = $koneksi->query("SELECT * FROM layanan");
                    while ($row = $layanan->fetch_assoc()) {
                        echo "<option value='{$row['id_layanan']}'>{$row['nama_layanan']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label>Berat (kg)</label>
                <input type="number" name="berat" step="0.1" class="form-control" required>
            </div>
            <div class="col-md-2 mb-2">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" required>
            </div>
            <div class="col-md-2 mb-2 d-grid">
                <label>&nbsp;</label>
                <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped shadow-sm">
        <thead>
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Layanan</th>
                <th>Berat</th>
                <th>Tanggal Masuk</th>
                <th>Tanggal Selesai</th>
                <th>Status</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT t.*, p.nama_pelanggan, l.nama_layanan FROM transaksi t 
                      JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                      JOIN layanan l ON t.id_layanan = l.id_layanan
                      ORDER BY t.tanggal_masuk DESC";
            $result = $koneksi->query($query);
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['nama_pelanggan']}</td>
                    <td>{$row['nama_layanan']}</td>
                    <td>{$row['berat']} kg</td>
                    <td>{$row['tanggal_masuk']}</td>
                    <td>{$row['tanggal_selesai']}</td>
                    <td>{$row['STATUS']}</td>
                    <td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>
                    <td>
                        <a href='?selesai={$row['id_transaksi']}' class='btn btn-success btn-sm mb-1' onclick=\"return confirm('Tandai transaksi ini sebagai selesai?');\">Selesai</a>
                        <a href='?hapus={$row['id_transaksi']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Hapus transaksi ini?');\">Hapus</a>
                    </td>
                </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>

