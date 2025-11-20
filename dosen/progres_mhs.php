<?php
session_start();
include "../admin/db.php"; 

// Cek Login Dosen
if (!isset($_SESSION['nip']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../auth/login.php");
    exit();
}

$nip_login = $_SESSION['nip'];
$current_page = basename($_SERVER['PHP_SELF']);
$error_msg = null;
$success_msg = null;

// Ambil NPM dari URL
$npm_target = filter_input(INPUT_GET, 'npm', FILTER_SANITIZE_STRING);

if (!$npm_target) {
    header("Location: home_dosen.php");
    exit();
}

// --- FUNGSI EMBEDDED DARI HOME_DOSEN ---
if (!function_exists('getJudulBab')) {
    function getJudulBab($bab) {
        $judul = [
            1 => "PENDAHULUAN", 2 => "TINJAUAN PUSTAKA", 3 => "METODOLOGI PENELITIAN", 
            4 => "HASIL DAN PEMBAHASAN", 5 => "KESIMPULAN DAN SARAN"
        ]; return $judul[$bab] ?? "BAB $bab";
    }
}
// Fungsi is_active diasumsikan ada di templates/sidebar_dosen.php

// --- 1. AMBIL DATA DOSEN & ID ---
$query_dosen = "SELECT m.id, m.nama FROM mstr_akun m WHERE m.username = ?";
$stmt_dosen = $conn->prepare($query_dosen);
$stmt_dosen->bind_param("s", $nip_login);
$stmt_dosen->execute();
$dosen = $stmt_dosen->get_result()->fetch_assoc();
$id_dosen = $dosen['id'];
$nama_dosen_login = $dosen['nama'];

// --- 2. AMBIL DATA MAHASISWA & PENENTUAN PERAN (Prioritas 2) ---
$query_mhs = "
SELECT 
    mhs_akun.nama AS nama_mahasiswa,
    mhs.npm,
    s.judul AS judul_skripsi,
    s.pembimbing1, s.pembimbing2
FROM data_mahasiswa dm
JOIN mstr_akun mhs_akun ON dm.id = mhs_akun.id
LEFT JOIN skripsi s ON dm.id = s.id_mahasiswa
WHERE dm.npm = ?";

$stmt_mhs = $conn->prepare($query_mhs);
$stmt_mhs->bind_param("s", $npm_target);
$stmt_mhs->execute();
$data_mhs = $stmt_mhs->get_result()->fetch_assoc();

if (!$data_mhs) {
    die("Data Mahasiswa tidak ditemukan.");
}

$nama_mahasiswa = $data_mhs['nama_mahasiswa'];
$judul_skripsi = $data_mhs['judul_skripsi'];
$is_pembimbing1 = $data_mhs['pembimbing1'] == $id_dosen;
$is_pembimbing2 = $data_mhs['pembimbing2'] == $id_dosen;

if (!$is_pembimbing1 && !$is_pembimbing2) {
    die("Akses ditolak. Anda bukan Pembimbing Mahasiswa ini.");
}

$role_pembimbing = $is_pembimbing1 ? 'nilai_dosen1' : 'nilai_dosen2';
$role_komentar = $is_pembimbing1 ? 'komentar_dosen1' : 'komentar_dosen2';
$label_pembimbing = $is_pembimbing1 ? 'Pembimbing 1' : 'Pembimbing 2';

// --- 3. LOGIKA PROSES ACC/REVISI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['progres_id'])) {
    $progres_id = filter_input(INPUT_POST, 'progres_id', FILTER_VALIDATE_INT);
    $action = $_POST['action']; // 'ACC' atau 'Revisi'
    $komentar = filter_input(INPUT_POST, 'komentar', FILTER_SANITIZE_STRING) ?? null;
    
    // Perbarui nilai progres_dosen1/2 (asumsi 50 jika ACC, 0 jika Revisi)
    $progres_score = ($action === 'ACC') ? 50 : 0;
    $progres_column = $is_pembimbing1 ? 'progres_dosen1' : 'progres_dosen2';

    $update_sql = "UPDATE progres_skripsi SET {$role_pembimbing} = ?, {$role_komentar} = ?, {$progres_column} = ? WHERE id = ? AND npm = ?";
    
    $stmt_update = $conn->prepare($update_sql);
    
    if ($stmt_update === FALSE) {
         $error_msg = "Database Error [Update]: " . $conn->error;
    } else {
        $stmt_update->bind_param("ssiis", $action, $komentar, $progres_score, $progres_id, $npm_target);
        
        if ($stmt_update->execute()) {
            $success_msg = "Persetujuan berhasil disimpan sebagai **{$action}**.";
        } else {
            $error_msg = "Gagal menyimpan persetujuan: " . $stmt_update->error;
        }
    }
}


// --- 4. AMBIL RIWAYAT PROGRES MAHASISWA (setelah update) ---
$query_progres = "SELECT * FROM progres_skripsi WHERE npm = ? ORDER BY bab ASC, created_at DESC";
$stmt_progres = $conn->prepare($query_progres);
$stmt_progres->bind_param("s", $npm_target);
$stmt_progres->execute();
$riwayat_progres = $stmt_progres->get_result()->fetch_all(MYSQLI_ASSOC);

$progres_grouped = [];
foreach ($riwayat_progres as $p) {
    $progres_grouped[$p['bab']][] = $p;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Bimbingan - <?= htmlspecialchars($npm_target) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* CSS Dosen Panel (Sama dengan home_dosen.php) */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #007bff; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        .profile-box { background-color: #e9ecef; border-radius: 50%; padding: 5px; }
        .sidebar a.active { background-color: #0056b3; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
        .card-progres { border-left: 5px solid #007bff; }
        .status-badge { font-size: 0.9em; padding: 0.3em 0.6em; }
        .form-komentar { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 8px; }
        .riwayat-table td, .riwayat-table th { font-size: 0.9rem; vertical-align: middle; }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="d-flex align-items-center">
        <img src="../admin/unimma.png" alt="Logo" style="height: 50px;">
        <h4 class="m-0 d-none d-md-block text-primary">MONITORING SKRIPSI (DOSEN)</h4>
    </div>
    <div class="profile d-flex align-items-center gap-2">
        <div class="text-end d-none d-md-block" style="line-height: 1.2;">
            <small class="text-muted" style="display:block; font-size: 11px;">Login Sebagai</small>
            <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($nama_dosen_login) ?></span>
        </div>
        <div class="profile-box d-flex align-items-center justify-content-center">
            <i class="bi bi-person-circle fs-3 text-secondary"></i>
        </div>
    </div>
</div>

<!-- SIDEBAR -->
<?php include "../templates/sidebar_dosen.php"; ?>

<!-- MAIN CONTENT -->
<div class="main-content">
    <a href="home_dosen.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Mahasiswa</a>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0 fw-bold">Kelola Bimbingan</h2>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php elseif ($error_msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <!-- DATA MAHASISWA -->
    <div class="card p-4 shadow-sm mb-4">
        <h5 class="mb-2 text-primary"><?= htmlspecialchars($nama_mahasiswa) ?> (<?= htmlspecialchars($npm_target) ?>)</h5>
        <p class="mb-0 small">Judul: <em><?= htmlspecialchars($judul_skripsi) ?></em></p>
        <p class="small">Peran Anda: <span class="badge bg-info"><?= $label_pembimbing ?></span></p>
    </div>

    <?php if (empty($progres_grouped)): ?>
        <div class="alert alert-warning text-center">Mahasiswa ini belum mengunggah progres bimbingan apapun.</div>
    <?php endif; ?>

    <!-- TAMPILAN RIWAYAT PROGRES PER BAB -->
    <?php foreach ($progres_grouped as $bab => $riwayat):
        $latest_progres = $riwayat[0]; // Ambil progres terbaru per bab

        $status_p1 = $latest_progres['nilai_dosen1'];
        $status_p2 = $latest_progres['nilai_dosen2'];

        $komentar_p1 = $latest_progres['komentar_dosen1'];
        $komentar_p2 = $latest_progres['komentar_dosen2'];
        
        $status_saya = $is_pembimbing1 ? $status_p1 : $status_p2;
        $komentar_saya = $is_pembimbing1 ? $komentar_p1 : $komentar_p2;
        $can_validate = ($status_saya !== 'ACC'); 
    ?>
    <div class="card p-4 shadow-sm mb-4 card-progres">
        <h5 class="mb-3">BAB <?= $bab ?>: <?= getJudulBab($bab) ?></h5>
        
        <table class="table table-sm riwayat-table">
            <thead>
                <tr>
                    <th width="15%">P1 Status</th>
                    <th width="15%">P2 Status</th>
                    <th width="30%">Tgl. Upload Terbaru</th>
                    <th width="20%">File</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="status-badge badge <?= $status_p1 == 'ACC' ? 'bg-success' : ($status_p1 == 'Revisi' ? 'bg-danger' : 'bg-secondary') ?>"><?= htmlspecialchars($status_p1 ?? 'Menunggu') ?></span></td>
                    <td><span class="status-badge badge <?= $status_p2 == 'ACC' ? 'bg-success' : ($status_p2 == 'Revisi' ? 'bg-danger' : 'bg-secondary') ?>"><?= htmlspecialchars($status_p2 ?? 'Menunggu') ?></span></td>
                    <td><?= date('d M Y H:i', strtotime($latest_progres['created_at'])) ?></td>
                    <td>
                        <a href="../uploads/progres_skripsi/<?= htmlspecialchars($latest_progres['file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> Unduh Naskah
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- RIWAYAT DETAIL (Tabel Lengkap) -->
        <h6 class="mt-4 mb-2 text-secondary">Riwayat Upload Bab <?= $bab ?></h6>
        <div style="max-height: 200px; overflow-y: auto;">
            <table class="table table-sm table-striped riwayat-table">
                <thead>
                    <tr>
                        <th width="20%">Tanggal</th>
                        <th width="30%">Komentar P1</th>
                        <th width="30%">Komentar P2</th>
                        <th width="20%">Status Final P1/P2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $p): ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                        <td><small class="text-muted"><?= nl2br(htmlspecialchars($p['komentar_dosen1'] ?? '-')) ?></small></td>
                        <td><small class="text-muted"><?= nl2br(htmlspecialchars($p['komentar_dosen2'] ?? '-')) ?></small></td>
                        <td>
                            <span class="status-badge badge <?= $p['nilai_dosen1'] == 'ACC' ? 'bg-success' : ($p['nilai_dosen1'] == 'Revisi' ? 'bg-danger' : 'bg-secondary') ?>"><?= htmlspecialchars($p['nilai_dosen1'] ?? '-') ?></span> / 
                            <span class="status-badge badge <?= $p['nilai_dosen2'] == 'ACC' ? 'bg-success' : ($p['nilai_dosen2'] == 'Revisi' ? 'bg-danger' : 'bg-secondary') ?>"><?= htmlspecialchars($p['nilai_dosen2'] ?? '-') ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        

        <!-- FORM VALIDASI HANYA UNTUK DOSEN INI -->
        <div class="form-komentar mt-4">
            <h6 class="text-info fw-bold">Validasi Progres oleh <?= htmlspecialchars($dosen['nama']) ?> (<?= $label_pembimbing ?>)</h6>
            
            <?php if ($can_validate): ?>
                <form method="POST" action="progres_mhs.php" class="mt-3">
                    <input type="hidden" name="progres_id" value="<?= $latest_progres['id'] ?>">
                    <input type="hidden" name="npm" value="<?= $npm_target ?>">

                    <div class="mb-3">
                        <label for="komentar_<?= $bab ?>" class="form-label">Komentar / Catatan Revisi:</label>
                        <textarea class="form-control" id="komentar_<?= $bab ?>" name="komentar" rows="3" required><?= htmlspecialchars($komentar_saya ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="ACC" class="btn btn-success">
                            <i class="bi bi-check-circle-fill"></i> ACC (Setuju)
                        </button>
                        <button type="submit" name="action" value="Revisi" class="btn btn-danger">
                            <i class="bi bi-x-circle-fill"></i> Revisi
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-success p-2 text-center">
                    <i class="bi bi-check-lg"></i> Anda sudah **ACC** bab ini. Persetujuan final sudah diberikan.
                </div>
            <?php endif; ?>
        </div>
        
    </div>
    <?php endforeach; ?>
    
    <!-- END PROGRES -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>