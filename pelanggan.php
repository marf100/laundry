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


// Logika Tambah, Edit, dan Hapus Pelanggan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $nama = $_POST['nama_pelanggan'];
        $sql = "INSERT INTO pelanggan (nama_pelanggan) VALUES ('$nama')";
        $koneksi->query($sql);
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id_pelanggan'];
        $nama = $_POST['nama_pelanggan'];
        $sql = "UPDATE pelanggan SET nama_pelanggan='$nama' WHERE id_pelanggan='$id'";
        $koneksi->query($sql);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id_pelanggan'];

        // Cek apakah pelanggan sedang digunakan dalam transaksi
        $cek = $koneksi->query("SELECT * FROM transaksi WHERE id_pelanggan = $id");
        if ($cek->num_rows > 0) {
            echo "<script>alert('Pelanggan tidak bisa dihapus karena masih digunakan dalam transaksi.');</script>";
        } else {
            $sql = "DELETE FROM pelanggan WHERE id_pelanggan='$id'";
            $koneksi->query($sql);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pelanggan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        h2 {
            color: #6a11cb;
        }
        .btn-primary {
            background-color: #6a11cb;
            border: none;
        }
        .btn-primary:hover {
            background-color: #5a0fb0;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        th {
            background-color: #6a11cb;
            color: white;
        }
        .modal .form-control {
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users"></i> Pelanggan</h2>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <!-- Form Tambah -->
    <form method="POST" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="nama_pelanggan" placeholder="Nama Pelanggan" required>
            <button type="submit" name="add" class="btn btn-primary">Tambah</button>
        </div>
    </form>

    <!-- Tabel Pelanggan -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
<?php
$sql = "SELECT * FROM pelanggan";
$result = $koneksi->query($sql);
$no = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$no}</td>
        <td>{$row['nama_pelanggan']}</td>
        <td>
            <button type='button' class='btn btn-warning btn-sm edit-btn' 
                data-id='{$row['id_pelanggan']}' 
                data-nama='{$row['nama_pelanggan']}' 
                data-bs-toggle='modal' data-bs-target='#editModal'>
                <i class='fas fa-edit'></i> Edit
            </button>

            <form method='POST' class='d-inline' onsubmit=\"return confirm('Yakin ingin menghapus pelanggan ini?');\">
                <input type='hidden' name='id_pelanggan' value='{$row['id_pelanggan']}'>
                <button type='submit' name='delete' class='btn btn-danger btn-sm'>
                    <i class='fas fa-trash'></i> Hapus
                </button>
            </form>
        </td>
    </tr>";
    $no++;
}
?>
        </tbody>
    </table>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Pelanggan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_pelanggan" id="edit-id">
        <div class="mb-3">
          <label for="edit-nama" class="form-label">Nama Pelanggan</label>
          <input type="text" class="form-control" name="nama_pelanggan" id="edit-nama" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const nama = this.dataset.nama;
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-nama').value = nama;
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
