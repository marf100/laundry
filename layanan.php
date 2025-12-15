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
<?php
$koneksi = new mysqli("localhost", "root", "", "laundry_db");

// Tambah layanan
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_layanan'];
    $harga = $_POST['harga_per_kg'];
    $durasi = $_POST['durasi_hari'];
    $koneksi->query("INSERT INTO layanan (nama_layanan, harga_per_kg, durasi_hari) VALUES ('$nama', '$harga', '$durasi')");
    header("Location: layanan.php");
}

// Edit layanan
if (isset($_POST['edit'])) {
    $id = $_POST['id_layanan'];
    $nama = $_POST['nama_layanan'];
    $harga = $_POST['harga_per_kg'];
    $durasi = $_POST['durasi_hari'];
    $koneksi->query("UPDATE layanan SET nama_layanan='$nama', harga_per_kg='$harga', durasi_hari='$durasi' WHERE id_layanan=$id");
    header("Location: layanan.php");
}

// Hapus layanan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $koneksi->query("DELETE FROM layanan WHERE id_layanan=$id");
    header("Location: layanan.php");
}

// Ambil data untuk form edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit = $koneksi->query("SELECT * FROM layanan WHERE id_layanan=$id");
    $editData = $edit->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Layanan Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .icon-laundry {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .btn-warning {
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="text-center mb-4">
        <div class="icon-laundry">🧺</div>
        <h2 class="fw-bold">Data Layanan Laundry</h2>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id_layanan" value="<?= $editData['id_layanan'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="nama_layanan" class="form-control" required value="<?= $editData['nama_layanan'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga per Kg</label>
                    <input type="number" name="harga_per_kg" class="form-control" required value="<?= $editData['harga_per_kg'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Durasi (hari)</label>
                    <input type="number" name="durasi_hari" class="form-control" required value="<?= $editData['durasi_hari'] ?? '' ?>">
                </div>
                <button type="submit" name="<?= $editData ? 'edit' : 'tambah' ?>" class="btn btn-primary w-100">
                    <?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Layanan' ?>
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Nama Layanan</th>
                        <th>Harga per Kg</th>
                        <th>Durasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                <?php
                $data = $koneksi->query("SELECT * FROM layanan");
                while ($row = $data->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $row['id_layanan'] ?></td>
                        <td><?= $row['nama_layanan'] ?></td>
                        <td><?= "Rp " . number_format($row['harga_per_kg'], 0, ',', '.') ?></td>
                        <td><?= $row['durasi_hari'] ?> hari</td>
                        <td>
                            <a href="?edit=<?= $row['id_layanan'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                            <a href="?hapus=<?= $row['id_layanan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus layanan ini?')">🗑️ Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">🏠 Kembali ke Beranda</a>
    </div>
</div>

</body>
</html>
