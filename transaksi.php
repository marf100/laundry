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

// Tambah transaksi dengan metode pembayaran
if (isset($_POST['tambah'])) {
    $id_pelanggan = $_POST['id_pelanggan'];
    $id_layanan = $_POST['id_layanan'];
    $berat = $_POST['berat'];
    $tanggal_masuk = date('Y-m-d');
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $status = 'proses';

    // Ambil harga layanan
    $getHarga = $koneksi->query("SELECT harga_per_kg FROM layanan WHERE id_layanan = '$id_layanan'");
    $hargaData = $getHarga->fetch_assoc();
    $harga_per_kg = $hargaData['harga_per_kg'];
    $subtotal = $harga_per_kg * $berat;

    // Insert ke tabel transaksi
    $sql = "INSERT INTO transaksi (id_user, id_pelanggan, tanggal_masuk, tanggal_selesai, STATUS, total_harga, metode_pembayaran)
            VALUES ('$id_user', '$id_pelanggan', '$tanggal_masuk', '$tanggal_selesai', '$status', '$subtotal', '$metode_pembayaran')";
    
    if ($koneksi->query($sql)) {
        $id_transaksi = $koneksi->insert_id;
        
        // Insert ke tabel detail_transaksi
        $sqlDetail = "INSERT INTO detail_transaksi (id_transaksi, id_layanan, berat_kg, subtotal)
                      VALUES ('$id_transaksi', '$id_layanan', '$berat', '$subtotal')";
        $koneksi->query($sqlDetail);

        echo "<script>alert('Transaksi berhasil ditambahkan!'); window.location='transaksi.php';</script>";
    }
}

// Hapus transaksi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Hapus detail transaksi dulu (foreign key constraint)
    $koneksi->query("DELETE FROM detail_transaksi WHERE id_transaksi = $id");
    
    // Kemudian hapus transaksi
    $koneksi->query("DELETE FROM transaksi WHERE id_transaksi = $id");
    
    header("Location: transaksi.php");
}

// Selesaikan transaksi dan kirim nota ke WhatsApp
if (isset($_GET['selesai'])) {
    $id = $_GET['selesai'];
    
    // Ambil data untuk nota SEBELUM update status
    $query = "SELECT t.*, p.nama_pelanggan, p.no_hp, 
              GROUP_CONCAT(l.nama_layanan SEPARATOR ', ') AS layanan,
              SUM(dt.berat_kg) AS total_berat
              FROM transaksi t 
              JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
              LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
              LEFT JOIN layanan l ON dt.id_layanan = l.id_layanan
              WHERE t.id_transaksi = $id
              GROUP BY t.id_transaksi";
    $result = $koneksi->query($query);
    $data = $result->fetch_assoc();
    
    if ($data) {
        // Update status menjadi 'selesai'
        $koneksi->query("UPDATE transaksi SET STATUS = 'selesai' WHERE id_transaksi = $id");
        
        // Cek apakah ada nomor HP
        if (!empty($data['no_hp'])) {
            $no_hp = preg_replace('/[^0-9]/', '', $data['no_hp']);
            
            // Format nomor HP untuk WhatsApp (tambahkan 62 jika diawali 0)
            if (substr($no_hp, 0, 1) === '0') {
                $no_hp = '62' . substr($no_hp, 1);
            } elseif (substr($no_hp, 0, 2) !== '62') {
                $no_hp = '62' . $no_hp;
            }
            
            // Buat nota dengan format yang lebih baik
            $nota = "🧺 *Cuci.in* 🧺%0A";
            $nota .= "═══════════════════════════%0A%0A";
            $nota .= "✅ *Cucian Anda Sudah Selesai!*%0A%0A";
            $nota .= "📋 *Detail Transaksi*%0A";
            $nota .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━%0A";
            $nota .= "No. Invoice: *#" . str_pad($data['id_transaksi'], 5, '0', STR_PAD_LEFT) . "*%0A";
            $nota .= "Nama: *" . $data['nama_pelanggan'] . "*%0A";
            $nota .= "Tgl Masuk: " . date('d/m/Y', strtotime($data['tanggal_masuk'])) . "%0A";
            $nota .= "Tgl Selesai: " . date('d/m/Y', strtotime($data['tanggal_selesai'])) . "%0A%0A";
            $nota .= "📦 *Layanan*%0A";
            $nota .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━%0A";
            $nota .= "• " . $data['layanan'] . "%0A";
            $nota .= "• Berat: *" . $data['total_berat'] . " kg*%0A%0A";
            $nota .= "💰 *Pembayaran*%0A";
            $nota .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━%0A";
            $nota .= "Metode: *" . strtoupper($data['metode_pembayaran']) . "*%0A";
            $nota .= "Total: *Rp " . number_format($data['total_harga'], 0, ',', '.') . "*%0A%0A";
            $nota .= "═══════════════════════════%0A";
            $nota .= "Terima kasih sudah menggunakan%0A";
            $nota .= "layanan *Cuci.in*! 🙏✨%0A%0A";
            $nota .= "Silakan ambil cucian Anda 🎉";
            
            // URL WhatsApp dengan nota
            $wa_url = "https://wa.me/$no_hp?text=$nota";
            
            // Simpan URL ke session untuk redirect
            $_SESSION['wa_url'] = $wa_url;
            $_SESSION['wa_message'] = "Transaksi #{$data['id_transaksi']} untuk {$data['nama_pelanggan']} telah diselesaikan!";
            
            // Redirect ke halaman dengan JavaScript yang akan membuka WhatsApp
            header("Location: transaksi.php?wa_redirect=1");
            exit();
        } else {
            echo "<script>
                alert('Transaksi berhasil diselesaikan, tetapi pelanggan tidak memiliki nomor WhatsApp.');
                window.location='transaksi.php';
            </script>";
            exit();
        }
    }
    
    header("Location: transaksi.php");
    exit();
}

// Handle WhatsApp redirect
$show_wa_modal = false;
$wa_url = '';
$wa_message = '';
if (isset($_GET['wa_redirect']) && isset($_SESSION['wa_url'])) {
    $show_wa_modal = true;
    $wa_url = $_SESSION['wa_url'];
    $wa_message = $_SESSION['wa_message'];
    unset($_SESSION['wa_url']);
    unset($_SESSION['wa_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi - Cuci.in</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
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
        }

        .header h1 {
            font-size: 2rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header .icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
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
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .btn-secondary:hover {
            background: rgba(100, 116, 139, 0.2);
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #475569;
            font-size: 0.9rem;
        }

        .form-control {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .payment-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .payment-option label i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #667eea;
        }

        .payment-option label span {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
        }

        .qris-section {
            margin-top: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border-radius: 12px;
            border: 2px dashed #667eea;
        }

        .qris-section h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qris-upload {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .qris-preview {
            width: 150px;
            height: 150px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: white;
        }

        .qris-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .qris-preview i {
            font-size: 3rem;
            color: #cbd5e1;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
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

        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .badge-info {
            background: rgba(14, 165, 233, 0.1);
            color: #0ea5e9;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }

            .qris-upload {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                Manajemen Transaksi
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Form Tambah Transaksi -->
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: #1e293b;">
                <i class="fas fa-plus-circle" style="color: #667eea;"></i> Tambah Transaksi Baru
            </h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Pelanggan</label>
                        <select name="id_pelanggan" class="form-control" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php
                            $pelanggan = $koneksi->query("SELECT * FROM pelanggan");
                            while ($row = $pelanggan->fetch_assoc()) {
                                echo "<option value='{$row['id_pelanggan']}'>{$row['nama_pelanggan']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Layanan</label>
                        <select name="id_layanan" class="form-control" required>
                            <option value="">-- Pilih Layanan --</option>
                            <?php
                            $layanan = $koneksi->query("SELECT * FROM layanan");
                            while ($row = $layanan->fetch_assoc()) {
                                echo "<option value='{$row['id_layanan']}'>{$row['nama_layanan']} (Rp " . number_format($row['harga_per_kg'], 0, ',', '.') . "/kg)</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Berat (kg)</label>
                        <input type="number" name="berat" step="0.1" class="form-control" placeholder="Contoh: 2.5" required>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" required>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <div class="payment-methods">
                        <div class="payment-option">
                            <input type="radio" name="metode_pembayaran" id="cash" value="cash" required>
                            <label for="cash">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>CASH</span>
                            </label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="metode_pembayaran" id="transfer" value="transfer" required>
                            <label for="transfer">
                                <i class="fas fa-university"></i>
                                <span>TRANSFER</span>
                            </label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="metode_pembayaran" id="qris" value="qris" required>
                            <label for="qris">
                                <i class="fas fa-qrcode"></i>
                                <span>QRIS</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- QRIS Section -->
                <div class="qris-section">
                    <h3>
                        <i class="fas fa-qrcode"></i> Kode QRIS Laundry
                    </h3>
                    <div class="qris-upload">
                        <div class="qris-preview" id="qrisPreview">
                            <?php
                            // Cek apakah file QRIS ada
                            $qris_path = 'uploads/qris.png';
                            if (file_exists($qris_path)) {
                                echo "<img src='$qris_path' alt='QRIS'>";
                            } else {
                                echo "<i class='fas fa-qrcode'></i>";
                            }
                            ?>
                        </div>
                        <div>
                            <p style="color: #64748b; margin-bottom: 1rem; font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> Upload atau perbarui kode QRIS untuk pembayaran digital
                            </p>
                            <div class="file-input-wrapper">
                                <label for="qrisFile" class="file-input-label">
                                    <i class="fas fa-upload"></i>
                                    Upload QRIS
                                </label>
                                <input type="file" id="qrisFile" name="qris" accept="image/*" onchange="previewQRIS(this)">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="tambah" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; padding: 1rem; font-size: 1rem;">
                    <i class="fas fa-plus-circle"></i> Tambah Transaksi
                </button>
            </form>
        </div>

        <!-- Tabel Transaksi -->
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: #1e293b;">
                <i class="fas fa-list"></i> Daftar Transaksi
            </h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Berat</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Selesai</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT t.*, p.nama_pelanggan, 
                                  GROUP_CONCAT(l.nama_layanan SEPARATOR ', ') AS layanan,
                                  SUM(dt.berat_kg) AS total_berat
                                  FROM transaksi t 
                                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                                  LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                                  LEFT JOIN layanan l ON dt.id_layanan = l.id_layanan
                                  GROUP BY t.id_transaksi
                                  ORDER BY t.tanggal_masuk DESC";
                        $result = $koneksi->query($query);
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = $row['STATUS'] == 'selesai' ? 'badge-success' : 'badge-warning';
                            $metodeBadge = '';
                            switch($row['metode_pembayaran']) {
                                case 'cash':
                                    $metodeBadge = '<span class="badge badge-success"><i class="fas fa-money-bill-wave"></i> CASH</span>';
                                    break;
                                case 'transfer':
                                    $metodeBadge = '<span class="badge badge-info"><i class="fas fa-university"></i> TRANSFER</span>';
                                    break;
                                case 'qris':
                                    $metodeBadge = '<span class="badge badge-info"><i class="fas fa-qrcode"></i> QRIS</span>';
                                    break;
                                default:
                                    $metodeBadge = '<span class="badge badge-warning">N/A</span>';
                            }
                            
                            echo "<tr>
                                <td>{$no}</td>
                                <td><strong>{$row['nama_pelanggan']}</strong></td>
                                <td>{$row['layanan']}</td>
                                <td>{$row['total_berat']} kg</td>
                                <td>" . date('d M Y', strtotime($row['tanggal_masuk'])) . "</td>
                                <td>" . date('d M Y', strtotime($row['tanggal_selesai'])) . "</td>
                                <td>$metodeBadge</td>
                                <td><span class='badge {$statusClass}'>" . strtoupper($row['STATUS']) . "</span></td>
                                <td><strong>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</strong></td>
                                <td>
                                    <button onclick=\"selesaikanTransaksi({$row['id_transaksi']})\" class='btn-action btn-success' style='margin-bottom: 0.5rem;'>
                                        <i class='fas fa-check'></i> Selesai
                                    </button>
                                    <button onclick=\"hapusTransaksi({$row['id_transaksi']})\" class='btn-action btn-danger'>
                                        <i class='fas fa-trash'></i> Hapus
                                    </button>
                                </td>
                            </tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Preview QRIS
        function previewQRIS(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('qrisPreview').innerHTML = `<img src="${e.target.result}" alt="QRIS Preview">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Konfirmasi selesai transaksi
        function selesaikanTransaksi(id) {
            if (confirm('Tandai transaksi ini sebagai selesai dan kirim nota ke WhatsApp pelanggan?')) {
                window.location.href = '?selesai=' + id;
            }
        }

        // Konfirmasi hapus transaksi
        function hapusTransaksi(id) {
            if (confirm('Yakin ingin menghapus transaksi ini?')) {
                window.location.href = '?hapus=' + id;
            }
        }

        // Set minimum date untuk tanggal selesai (hari ini)
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="tanggal_selesai"]').setAttribute('min', today);

        <?php if ($show_wa_modal): ?>
        // Auto-open WhatsApp
        window.onload = function() {
            // Show success message
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeIn 0.3s ease;
            `;
            
            modal.innerHTML = `
                <div style="
                    background: white;
                    padding: 3rem;
                    border-radius: 20px;
                    text-align: center;
                    max-width: 500px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    animation: slideUp 0.4s ease;
                ">
                    <div style="
                        width: 80px;
                        height: 80px;
                        background: linear-gradient(135deg, #25D366, #128C7E);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 1.5rem;
                        animation: pulse 1.5s ease infinite;
                    ">
                        <i class="fab fa-whatsapp" style="font-size: 3rem; color: white;"></i>
                    </div>
                    <h2 style="color: #1e293b; margin-bottom: 1rem; font-size: 1.5rem;">
                        ✅ Transaksi Selesai!
                    </h2>
                    <p style="color: #64748b; margin-bottom: 2rem; font-size: 1rem;">
                        <?php echo $wa_message; ?>
                    </p>
                    <div style="
                        background: linear-gradient(135deg, rgba(37, 211, 102, 0.1), rgba(18, 140, 126, 0.1));
                        padding: 1rem;
                        border-radius: 12px;
                        margin-bottom: 2rem;
                        border: 2px dashed #25D366;
                    ">
                        <p style="color: #25D366; font-weight: 600; margin-bottom: 0.5rem;">
                            <i class="fas fa-info-circle"></i> Nota akan dikirim otomatis
                        </p>
                        <p style="color: #64748b; font-size: 0.9rem;">
                            WhatsApp akan terbuka dengan nota yang sudah terisi
                        </p>
                    </div>
                    <button onclick="sendWhatsApp()" style="
                        background: linear-gradient(135deg, #25D366, #128C7E);
                        color: white;
                        padding: 1rem 2rem;
                        border: none;
                        border-radius: 12px;
                        font-weight: 600;
                        font-size: 1rem;
                        cursor: pointer;
                        width: 100%;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(37, 211, 102, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i>
                        Buka WhatsApp & Kirim Nota
                    </button>
                    <button onclick="closeModal()" style="
                        background: transparent;
                        color: #64748b;
                        padding: 0.75rem;
                        border: none;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        width: 100%;
                        margin-top: 1rem;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='rgba(100, 116, 139, 0.1)'" onmouseout="this.style.style.background='transparent'">
                        Tutup
                    </button>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Auto open after 1 second
            setTimeout(() => {
                sendWhatsApp();
            }, 1500);
        };

        function sendWhatsApp() {
            const waUrl = <?php echo json_encode($wa_url); ?>;
            window.open(waUrl, '_blank');
            setTimeout(() => {
                closeModal();
            }, 500);
        }

        function closeModal() {
            const modal = document.querySelector('div[style*="z-index: 10000"]');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }
        <?php endif; ?>
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(37, 211, 102, 0);
            }
        }
    </style>
</body>
</html>