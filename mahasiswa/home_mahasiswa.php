<?php
session_start();
include "db.php";

$npm = $_SESSION['npm'] ?? '';
if (!$npm) {
    header("Location: login_mahasiswa.php");
    exit();
}

$sql = "SELECT m.*, 
               d1.nama AS nama_dosen1, d1.nip AS nip1, 
               d2.nama AS nama_dosen2, d2.nip AS nip2 
        FROM biodata_mahasiswa m
        LEFT JOIN biodata_dosen d1 ON m.nip_pembimbing1 = d1.nip
        LEFT JOIN biodata_dosen d2 ON m.nip_pembimbing2 = d2.nip
        WHERE m.npm = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $npm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows < 1) {
    echo "Data biodata tidak ditemukan.";
    exit();
}

$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mahasiswa</title>
    <link rel="stylesheet" href="csshome1.css">
</head>
<body>
<div class="header">
    <div class="logo">
        <img src="unimma.png" alt="Logo">
    </div>
    <div class="title">
        <h1>WEBSITE MONITORING SKRIPSI UNIMMA</h1>
        <h3>Selamat datang, <?= htmlspecialchars($data['nama']) ?>!</h3>
    </div>
    <div class="profile">
        <a href="update_biodata_mahasiswa.php">
            <?php if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])): ?>
                <img src="uploads/<?= htmlspecialchars($data['foto']) ?>" alt="Foto">
            <?php else: ?>
                <div style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 25px;">👤</div>
            <?php endif; ?>
        </a>
    </div>
</div>

<div class="container-fluid">
    <div class="sidebar">
        <h4 class="text-center">Panel Mahasiswa</h4>
        <a href="home_mahasiswa.php">Dashboard</a>
        <a href="progres_skripsi.php">Progres Skripsi</a>
        <a href="../logout.php">Logout</a>
        <div class="text-center mt-4" style="font-size: 13px; color: #aaa;">
            &copy; ikhbal.khasodiq18@gmail.com
        </div>
    </div>

    <div class="main-content">
        <div class="card-box">
            <div class="biodata-row">
                <div>
                    <?php if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])): ?>
                        <img src="uploads/<?= htmlspecialchars($data['foto']) ?>" class="biodata-photo">
                    <?php else: ?>
                        <div class="biodata-photo" style="font-size: 60px; display: flex; align-items: center; justify-content: center; background: #eee;">👤</div>
                    <?php endif; ?>
                </div>

                <div class="biodata-info" style="text-align: left;">
                    <div class="biodata-field"><span class="label">Nama</span><span class="separator">:</span><span class="value"><?= htmlspecialchars($data['nama']) ?></span></div>
                    <div class="biodata-field"><span class="label">NPM</span><span class="separator">:</span><span class="value"><?= htmlspecialchars($data['npm']) ?></span></div>
                    <div class="biodata-field"><span class="label">No HP</span><span class="separator">:</span><span class="value"><?= htmlspecialchars($data['no_hp']) ?></span></div>
                    <div class="biodata-field"><span class="label">Prodi</span><span class="separator">:</span><span class="value"><?= htmlspecialchars($data['prodi']) ?></span></div>
                    <div class="biodata-field"><span class="label">Judul Skripsi</span><span class="separator">:</span><span class="value"><?= nl2br(htmlspecialchars($data['judul_skripsi'])) ?></span></div>

                    <div class="dosen-row">
                        <div class="dosen-box">
                            <strong>Dosen Pembimbing 1</strong><br>
                            <?= htmlspecialchars($data['nama_dosen1'] ?? '-') ?><br>
                            <?= htmlspecialchars($data['nip1'] ?? '-') ?>
                        </div>
                        <div class="dosen-box">
                            <strong>Dosen Pembimbing 2</strong><br>
                            <?= htmlspecialchars($data['nama_dosen2'] ?? '-') ?><br>
                            <?= htmlspecialchars($data['nip2'] ?? '-') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $total_progress = 0;
        $query = mysqli_query($conn, "SELECT progres_dosen1, progres_dosen2 FROM progres_skripsi WHERE npm = '$npm'");
        while ($row = mysqli_fetch_assoc($query)) {
            $total_progress += (int)$row['progres_dosen1'] + (int)$row['progres_dosen2'];
        }
        $persentase = min(100, round(($total_progress / 100) * 100));
        ?>

        <div class="card-box">
            <h4>Progres Bar Skripsi</h4>
            <div style="background: #e0e0e0; border-radius: 8px; overflow: hidden; height: 24px;">
                <div style="width: <?= $persentase ?>%; background: #4caf50; height: 100%; color: white; text-align: center; font-weight: bold;">
                    <?= $persentase ?>%
                </div>
            </div>
            <p style="margin-top: 8px;">Total poin progress: <?= $total_progress ?> / 100</p>
            <div class="card-box">
                <h5>Progress Per BAB (1–5)</h5>
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                <?php for ($bab = 1; $bab <= 3; $bab++): 
                   $sql = "SELECT MAX(progres_dosen1) AS progres_dosen1, MAX(progres_dosen2) AS progres_dosen2 
                         FROM progres_skripsi WHERE npm = ? AND bab = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $npm, $bab);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $p1 = ($row['progres_dosen1'] ?? 0) >= 1 ? 50 : 0;
                    $p2 = ($row['progres_dosen2'] ?? 0) >= 1 ? 50 : 0;
                ?>
                    <div style="flex: 1; min-width: 200px; background: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        <strong>BAB <?= $bab ?></strong><br>
                        <div style="background: #e0e0e0; border-radius: 6px; overflow: hidden; height: 18px; margin-top: 5px;">
                            <div style="width: <?= $p1 + $p2 ?>%; background: #03a9f4; height: 100%; text-align: center; color: white; font-size: 12px;">
                                <?= $p1 + $p2 ?>%
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
                </div>

                <h5>Progress Per BAB (4–5)</h5>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <?php for ($bab = 4; $bab <= 5; $bab++): 
                    $sql = "SELECT MAX(progres_dosen1) AS progres_dosen1, MAX(progres_dosen2) AS progres_dosen2 
                    FROM progres_skripsi WHERE npm = ? AND bab = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $npm, $bab);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $p1 = ($row['progres_dosen1'] ?? 0) >= 1 ? 50 : 0;
                    $p2 = ($row['progres_dosen2'] ?? 0) >= 1 ? 50 : 0;
                ?>
                    <div style="flex: 1; min-width: 200px; background: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        <strong>BAB <?= $bab ?></strong><br>
                        <div style="background: #e0e0e0; border-radius: 6px; overflow: hidden; height: 18px; margin-top: 5px;">
                            <div style="width: <?= $p1 + $p2 ?>%; background: #009688; height: 100%; text-align: center; color: white; font-size: 12px;">
                                <?= $p1 + $p2 ?>%
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
                </div>
        </div>
    </div>
</div>
</body>
</html>
