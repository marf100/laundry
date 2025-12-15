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

// Tambah layanan
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_layanan'];
    $harga = $_POST['harga_per_kg'];
    $durasi = $_POST['durasi_hari'];
    $deskripsi = $_POST['deskripsi'];
    
    $koneksi->query("INSERT INTO layanan (nama_layanan, harga_per_kg, durasi_hari, deskripsi) 
                     VALUES ('$nama', '$harga', '$durasi', '$deskripsi')");
    
    echo "<script>
        alert('✅ Layanan berhasil ditambahkan!');
        window.location='layanan.php';
    </script>";
}

// Edit layanan
if (isset($_POST['edit'])) {
    $id = $_POST['id_layanan'];
    $nama = $_POST['nama_layanan'];
    $harga = $_POST['harga_per_kg'];
    $durasi = $_POST['durasi_hari'];
    $deskripsi = $_POST['deskripsi'];
    
    $koneksi->query("UPDATE layanan SET nama_layanan='$nama', harga_per_kg='$harga', 
                     durasi_hari='$durasi', deskripsi='$deskripsi' WHERE id_layanan=$id");
    
    echo "<script>
        alert('✅ Layanan berhasil diupdate!');
        window.location='layanan.php';
    </script>";
}

// Hapus layanan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Cek apakah layanan sedang digunakan
    $cek = $koneksi->query("SELECT * FROM detail_transaksi WHERE id_layanan = $id");
    if ($cek->num_rows > 0) {
        echo "<script>alert('❌ Layanan tidak bisa dihapus karena sedang/pernah digunakan dalam transaksi.');</script>";
    } else {
        $koneksi->query("DELETE FROM layanan WHERE id_layanan=$id");
        echo "<script>
            alert('✅ Layanan berhasil dihapus!');
            window.location='layanan.php';
        </script>";
    }
}

// Ambil data layanan dengan statistik
$layananData = [];
$sql = "SELECT l.*, 
        COUNT(dt.id_detail) as total_penggunaan,
        COALESCE(SUM(dt.subtotal), 0) as total_pendapatan
        FROM layanan l
        LEFT JOIN detail_transaksi dt ON l.id_layanan = dt.id_layanan
        LEFT JOIN transaksi t ON dt.id_transaksi = t.id_transaksi AND t.STATUS = 'selesai'
        GROUP BY l.id_layanan
        ORDER BY l.id_layanan DESC";
$result = $koneksi->query($sql);
while ($row = $result->fetch_assoc()) {
    $layananData[] = $row;
}

$totalLayanan = count($layananData);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Layanan - Laundry SMBD</title>
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
            font-family: 'Poppins', sans-serif;
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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: fadeInUp 0.6s ease backwards;
        }

        .stat-box:nth-child(1) { animation-delay: 0.1s; }
        .stat-box:nth-child(2) { animation-delay: 0.2s; }
        .stat-box:nth-child(3) { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-box:nth-child(1) .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-box:nth-child(2) .stat-icon { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-box:nth-child(3) .stat-icon { background: linear-gradient(135deg, #4facfe, #00f2fe); }

        .stat-info h3 {
            font-size: 1.8rem;
            color: #1e293b;
            font-weight: 700;
        }

        .stat-info p {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease backwards;
            animation-delay: 0.4s;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
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

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .service-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            border-color: #667eea;
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 1rem;
        }

        .service-header h3 {
            color: #1e293b;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .service-price {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .service-duration {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 50px;
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .service-description {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .service-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
        }

        .stat-item {
            flex: 1;
            text-align: center;
        }

        .stat-item strong {
            display: block;
            font-size: 1.1rem;
            color: #667eea;
            margin-bottom: 0.25rem;
        }

        .stat-item span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .service-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            flex: 1;
            padding: 0.75rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-edit {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 172, 254, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            color: #1e293b;
            font-size: 1.5rem;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            color: #1e293b;
            transform: rotate(90deg);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr;
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
                    <i class="fas fa-concierge-bell"></i>
                </div>
                Data Layanan
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalLayanan; ?></h3>
                    <p>Total Layanan</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?php 
                        $totalPenggunaan = array_sum(array_column($layananData, 'total_penggunaan'));
                        echo $totalPenggunaan;
                    ?></h3>
                    <p>Total Penggunaan</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3>Rp <?php 
                        $totalPendapatan = array_sum(array_column($layananData, 'total_pendapatan'));
                        echo number_format($totalPendapatan/1000, 0);
                    ?>K</h3>
                    <p>Total Pendapatan</p>
                </div>
            </div>
        </div>

        <!-- Form Tambah -->
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: #1e293b;">
                <i class="fas fa-plus-circle" style="color: #667eea;"></i> Tambah Layanan Baru
            </h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nama Layanan</label>
                        <input type="text" name="nama_layanan" class="form-control" placeholder="Contoh: Cuci Setrika Express" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-money-bill"></i> Harga per Kg</label>
                        <input type="number" name="harga_per_kg" class="form-control" placeholder="Contoh: 10000" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Durasi (Hari)</label>
                        <input type="number" name="durasi_hari" class="form-control" placeholder="Contoh: 2" required>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Deskripsi Layanan</label>
                    <textarea name="deskripsi" class="form-control" placeholder="Jelaskan detail layanan ini..."></textarea>
                </div>
                <button type="submit" name="tambah" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem;">
                    <i class="fas fa-plus-circle"></i> Tambah Layanan
                </button>
            </form>
        </div>

        <!-- Services Grid -->
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: #1e293b;">
                <i class="fas fa-list"></i> Daftar Layanan
            </h2>
            
            <?php if (count($layananData) > 0): ?>
            <div class="services-grid">
                <?php foreach ($layananData as $service): ?>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="service-header">
                        <h3><?php echo $service['nama_layanan']; ?></h3>
                    </div>
                    <div class="service-price">
                        Rp <?php echo number_format($service['harga_per_kg'], 0, ',', '.'); ?>/kg
                    </div>
                    <div class="service-duration">
                        <i class="fas fa-clock"></i>
                        <?php echo $service['durasi_hari']; ?> Hari
                    </div>
                    
                    <?php if ($service['deskripsi']): ?>
                    <div class="service-description">
                        <?php echo $service['deskripsi']; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="service-stats">
                        <div class="stat-item">
                            <strong><?php echo $service['total_penggunaan']; ?>x</strong>
                            <span>Digunakan</span>
                        </div>
                        <div class="stat-item">
                            <strong>Rp <?php echo number_format($service['total_pendapatan']/1000, 0); ?>K</strong>
                            <span>Pendapatan</span>
                        </div>
                    </div>
                    
                    <div class="service-actions">
                        <button class="btn-action btn-edit" onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteService(<?php echo $service['id_layanan']; ?>)">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-concierge-bell"></i>
                <h3>Belum Ada Layanan</h3>
                <p>Tambahkan layanan pertama Anda menggunakan form di atas</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Layanan</h2>
                <button class="close-modal" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="id_layanan" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nama Layanan</label>
                        <input type="text" name="nama_layanan" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-money-bill"></i> Harga per Kg</label>
                        <input type="number" name="harga_per_kg" id="edit_harga" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Durasi (Hari)</label>
                        <input type="number" name="durasi_hari" id="edit_durasi" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" class="form-control"></textarea>
                </div>
                <button type="submit" name="edit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <script>
        function editService(data) {
            document.getElementById('edit_id').value = data.id_layanan;
            document.getElementById('edit_nama').value = data.nama_layanan;
            document.getElementById('edit_harga').value = data.harga_per_kg;
            document.getElementById('edit_durasi').value = data.durasi_hari;
            document.getElementById('edit_deskripsi').value = data.deskripsi || '';
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function deleteService(id) {
            if (confirm('Yakin ingin menghapus layanan ini?')) {
                window.location.href = '?hapus=' + id;
            }
        }

        // Close modal on outside click
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>