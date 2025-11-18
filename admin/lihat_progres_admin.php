<?php
session_start();
// Sesuaikan path db.php
include "db.php";

// Cek login
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';
$npm = $_GET['npm'] ?? '';

if (!$npm) {
    echo "<script>alert('NPM tidak ditemukan!'); window.location='home_admin.php';</script>";
    exit();
}

// --- QUERY 1: AMBIL DATA MAHASISWA ---
// Mengambil Nama, Prodi, Judul Skripsi
$query_mhs = "SELECT 
                m.nama, 
                dm.prodi, 
                s.judul AS judul_skripsi 
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
              WHERE m.username = '$npm' AND m.role = 'mahasiswa'";

$result_mhs = mysqli_query($conn, $query_mhs);
$data_mhs = mysqli_fetch_assoc($result_mhs);

if (!$data_mhs) {
    echo "<script>alert('Data mahasiswa tidak ditemukan!'); window.location='home_admin.php';</script>";
    exit();
}

$nama  = $data_mhs['nama'];
$prodi = $data_mhs['prodi'];
$judul = !empty($data_mhs['judul_skripsi']) ? $data_mhs['judul_skripsi'] : '- Belum Mengajukan Judul -';

// --- QUERY 2: AMBIL DATA PROGRES ---
// Cek dulu apakah tabel progres_skripsi ada
$cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
$progres = false;

if (mysqli_num_rows($cek_tabel) > 0) {
    $progres = mysqli_query($conn, "SELECT * FROM progres_skripsi WHERE npm = '$npm' ORDER BY bab ASC, id DESC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Progres - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    /* --- LAYOUT FIXED (Konsisten) --- */
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
    
    /* Header */
    .header {
        position: fixed; top: 0; left: 0; width: 100%; height: 70px;
        background-color: #ffffff; border-bottom: 1px solid #dee2e6;
        z-index: 1050; display: flex; align-items: center; justify-content: space-between;
        padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }

    /* Sidebar */
    .sidebar {
        position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px);
        background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040;
    }
    .sidebar a {
        color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px;
        border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s;
    }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; padding-left: 30px; }
    
    /* Content */
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
  </style>
</head>
<body>

<div class="header">
  <div class="d-flex align-items-center">
    <img src="unimma.png" alt="Logo" style="height: 50px;">
    <h4 class="m-0 d-none d-md-block">MONITORING SKRIPSI</h4>
  </div>
  <div class="profile d-flex align-items-center gap-2">
    <div class="text-end d-none d-md-block" style="line-height: 1.2;">
        <small class="text-muted" style="display:block; font-size: 11px;">Login Sebagai</small>
        <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($nama_admin) ?></span>
    </div>
    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef;
                display: flex; align-items: center; justify-content: center; border: 1px solid #ced4da; font-size: 20px;">
        👤
    </div>
  </div>
</div>

<div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px; letter-spacing: 1px;">Menu Utama</h6>
    <a href="home_admin.php" class="active" style="background-color: #0d6efd;">Dashboard</a>
    <a href="laporan_sidang.php">Laporan Sidang</a>
    <a href="data_mahasiswa.php">Data Mahasiswa</a>
    <a href="data_dosen.php">Data Dosen</a>
    <h6 class="text-uppercase text-secondary ms-3 mb-3 mt-4" style="font-size: 12px; letter-spacing: 1px;">Manajemen Akun</h6>
    <a href="akun_mahasiswa.php">Akun Mahasiswa</a>
    <a href="akun_dosen.php">Akun Dosen</a>
    <a href="mahasiswa_skripsi.php">Data Skripsi</a>
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a> 
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">&copy; 2025 UNIMMA</div>
</div>

<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <h4 class="mb-4 text-primary border-bottom pb-2">Detail Progres Skripsi Mahasiswa</h4>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><td width="150"><strong>NPM</strong></td><td>: <?= htmlspecialchars($npm) ?></td></tr>
                    <tr><td><strong>Nama</strong></td><td>: <?= htmlspecialchars($nama) ?></td></tr>
                    <tr><td><strong>Prodi</strong></td><td>: <?= htmlspecialchars($prodi) ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><td><strong>Judul Skripsi</strong></td><td>:</td></tr>
                    <tr><td colspan="2" class="fst-italic bg-light p-2 rounded"><?= htmlspecialchars($judul) ?></td></tr>
                </table>
            </div>
        </div>
        
        <a href="home_admin.php" class="btn btn-secondary btn-sm mb-3">← Kembali ke Dashboard</a>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-2">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>BAB</th>
                        <th>File Dokumen</th>
                        <th>Tanggal Upload</th>
                        <th>Status Pembimbing 1</th>
                        <th>Status Pembimbing 2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($progres && mysqli_num_rows($progres) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($progres)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-bold">BAB <?= htmlspecialchars($row['bab']) ?></td>
                            <td>
                                <a href="../mahasiswa/uploads/<?= htmlspecialchars($row['file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    📄 Lihat File
                                </a>
                            </td>
                            <td><?= htmlspecialchars(date('d-m-Y H:i', strtotime($row['created_at']))) ?></td>
                            <td>
                                <?php 
                                    $status1 = $row['nilai_dosen1'] ?? '-';
                                    $badge1 = ($status1 == 'ACC') ? 'success' : (($status1 == 'Revisi') ? 'danger' : 'secondary');
                                    echo "<span class='badge bg-$badge1'>$status1</span>";
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $status2 = $row['nilai_dosen2'] ?? '-';
                                    $badge2 = ($status2 == 'ACC') ? 'success' : (($status2 == 'Revisi') ? 'danger' : 'secondary');
                                    echo "<span class='badge bg-$badge2'>$status2</span>";
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <em>Belum ada data progres yang diunggah oleh mahasiswa ini.</em>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>