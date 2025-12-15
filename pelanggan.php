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
        $no_hp = $_POST['no_hp'];
        $alamat = $_POST['alamat'];
        $email = $_POST['email'];
        
        $sql = "INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat, email) VALUES ('$nama', '$no_hp', '$alamat', '$email')";
        $koneksi->query($sql);
        
        echo "<script>
            alert('✅ Pelanggan berhasil ditambahkan!');
            window.location='pelanggan.php';
        </script>";
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id_pelanggan'];
        $nama = $_POST['nama_pelanggan'];
        $no_hp = $_POST['no_hp'];
        $alamat = $_POST['alamat'];
        $email = $_POST['email'];
        
        $sql = "UPDATE pelanggan SET nama_pelanggan='$nama', no_hp='$no_hp', alamat='$alamat', email='$email' WHERE id_pelanggan='$id'";
        $koneksi->query($sql);
        
        echo "<script>
            alert('✅ Data pelanggan berhasil diupdate!');
            window.location='pelanggan.php';
        </script>";
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id_pelanggan'];

        // Cek apakah pelanggan sedang digunakan dalam transaksi
        $cek = $koneksi->query("SELECT * FROM transaksi WHERE id_pelanggan = $id");
        if ($cek->num_rows > 0) {
            echo "<script>alert('❌ Pelanggan tidak bisa dihapus karena masih memiliki riwayat transaksi.');</script>";
        } else {
            $sql = "DELETE FROM pelanggan WHERE id_pelanggan='$id'";
            $koneksi->query($sql);
            echo "<script>
                alert('✅ Pelanggan berhasil dihapus!');
                window.location='pelanggan.php';
            </script>";
        }
    }
}

// Ambil data pelanggan dengan statistik
$pelangganData = [];
$sql = "SELECT p.*, 
        COUNT(t.id_transaksi) as total_transaksi,
        COALESCE(SUM(t.total_harga), 0) as total_belanja
        FROM pelanggan p
        LEFT JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
        GROUP BY p.id_pelanggan
        ORDER BY p.id_pelanggan DESC";
$result = $koneksi->query($sql);
while ($row = $result->fetch_assoc()) {
    $pelangganData[] = $row;
}

$totalPelanggan = count($pelangganData);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - Laundry SMBD</title>
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .customers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .customer-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .customer-card::before {
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

        .customer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            border-color: #667eea;
        }

        .customer-card:hover::before {
            transform: scaleX(1);
        }

        .customer-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .customer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .customer-name {
            flex: 1;
        }

        .customer-name h3 {
            color: #1e293b;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .customer-name .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            color: #667eea;
        }

        .customer-info {
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .info-item i {
            width: 24px;
            color: #667eea;
        }

        .customer-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
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

        .customer-actions {
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
            animation: fadeIn 0.3s ease;
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

            .customers-grid {
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
                    <i class="fas fa-users"></i>
                </div>
                Data Pelanggan
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalPelanggan; ?></h3>
                    <p>Total Pelanggan</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <h3><?php 
                        $bulanIni = $koneksi->query("SELECT COUNT(*) as total FROM pelanggan WHERE MONTH(CURRENT_DATE) = MONTH(CURRENT_DATE)")->fetch_assoc();
                        echo $totalPelanggan; // Bisa disesuaikan dengan data bulan ini
                    ?></h3>
                    <p>Pelanggan Aktif</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?php 
                        $totalTransaksi = $koneksi->query("SELECT COUNT(*) as total FROM transaksi")->fetch_assoc();
                        echo $totalTransaksi['total'];
                    ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
        </div>

        <!-- Form Tambah -->
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: #1e293b;">
                <i class="fas fa-user-plus" style="color: #667eea;"></i> Tambah Pelanggan Baru
            </h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" class="form-control" placeholder="Contoh: John Doe" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Nomor HP (WhatsApp)</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: john@email.com">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                        <input type="text" name="alamat" class="form-control" placeholder="Contoh: Jl. Merdeka No. 123">
                    </div>
                </div>
                <button type="submit" name="add" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem;">
                    <i class="fas fa-plus-circle"></i> Tambah Pelanggan
                </button>
            </form>
        </div>

        <!-- Customer Grid -->
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: #1e293b;">
                <i class="fas fa-list"></i> Daftar Pelanggan
            </h2>
            
            <?php if (count($pelangganData) > 0): ?>
            <div class="customers-grid">
                <?php foreach ($pelangganData as $customer): ?>
                <div class="customer-card">
                    <div class="customer-header">
                        <div class="customer-avatar">
                            <?php echo strtoupper(substr($customer['nama_pelanggan'], 0, 1)); ?>
                        </div>
                        <div class="customer-name">
                            <h3><?php echo $customer['nama_pelanggan']; ?></h3>
                            <span class="badge">
                                <i class="fas fa-star"></i> Pelanggan
                            </span>
                        </div>
                    </div>
                    
                    <div class="customer-info">
                        <?php if ($customer['no_hp']): ?>
                        <div class="info-item">
                            <i class="fab fa-whatsapp"></i>
                            <span><?php echo $customer['no_hp']; ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($customer['email']): ?>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo $customer['email']; ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($customer['alamat']): ?>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $customer['alamat']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="customer-stats">
                        <div class="stat-item">
                            <strong><?php echo $customer['total_transaksi']; ?></strong>
                            <span>Transaksi</span>
                        </div>
                        <div class="stat-item">
                            <strong>Rp <?php echo number_format($customer['total_belanja']/1000, 0); ?>K</strong>
                            <span>Total Belanja</span>
                        </div>
                    </div>
                    
                    <div class="customer-actions">
                        <button class="btn-action btn-edit" onclick="editCustomer(<?php echo htmlspecialchars(json_encode($customer)); ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteCustomer(<?php echo $customer['id_pelanggan']; ?>)">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Belum Ada Pelanggan</h3>
                <p>Tambahkan pelanggan pertama Anda menggunakan form di atas</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Data Pelanggan</h2>
                <button class="close-modal" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="id_pelanggan" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Nomor HP</label>
                        <input type="text" name="no_hp" id="edit_hp" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                        <input type="text" name="alamat" id="edit_alamat" class="form-control">
                    </div>
                </div>
                <button type="submit" name="edit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <script>
        function editCustomer(data) {
            document.getElementById('edit_id').value = data.id_pelanggan;
            document.getElementById('edit_nama').value = data.nama_pelanggan;
            document.getElementById('edit_hp').value = data.no_hp || '';
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_alamat').value = data.alamat || '';
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function deleteCustomer(id) {
            if (confirm('Yakin ingin menghapus pelanggan ini? Data transaksi terkait akan tetap tersimpan.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="id_pelanggan" value="${id}">
                    <input type="hidden" name="delete" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
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