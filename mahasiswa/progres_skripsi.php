<?php
session_start();
include "db.php";

$npm = $_SESSION['npm'] ?? '';
if (!$npm) {
    header("Location: login_mahasiswa.php");
    exit();
}

$sql = "SELECT m.*, d1.nama AS nama_dosen1, d2.nama AS nama_dosen2, m.foto
        FROM biodata_mahasiswa m
        LEFT JOIN biodata_dosen d1 ON m.nip_pembimbing1 = d1.nip
        LEFT JOIN biodata_dosen d2 ON m.nip_pembimbing2 = d2.nip
        WHERE m.npm = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $npm);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

function getJudulBab($bab) {
    $judul = [
        1 => "PENDAHULUAN",
        2 => "TINJAUAN PUSTAKA",
        3 => "METODOLOGI PENELITIAN",
        4 => "HASIL DAN PEMBAHASAN",
        5 => "KESIMPULAN DAN SARAN"
    ];
    return $judul[$bab] ?? "BAB $bab";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Progres Skripsi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="csspg1.css"> 
    <link rel="stylesheet" href="ccsprogres.css">
     <style>
          .table-fixed {
            table-layout: fixed;
            width: 100%;
          }
          .table-fixed th:nth-child(1) { width: 5%; }  
          .table-fixed th:nth-child(2) { width: 30%; }  
          .table-fixed th:nth-child(3) { width: 15%; }  
          .table-fixed th:nth-child(4) { width: 10%; }  
          .table-fixed th:nth-child(5) { width: 10%; }  
    </style>
</head>
<body>


<!-- Header -->
<div class="header">
    <div class="logo">
        <img src="unimma.png" alt="Logo">
    </div>
    <div class="title">
        <h1>WEBSITE MONITORING SKRIPSI UNIMMA</h1>
    </div>
    <div class="profile">
        <a href="update_biodata_mahasiswa.php">
            <?php if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])): ?>
                <img src="uploads/<?= $data['foto'] ?>" />
            <?php else: ?>
                <div style="width: 50px; height: 50px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center;">👤</div>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Layout -->
<div class="container-fluid">
    <!-- Sidebar -->
    <div class="sidebar">
       <h4 class="text-center">Panel Mahasiswa</h4>
        <a href="home_mahasiswa.php">Dashboard</a>
        <a href="progres_skripsi.php">Progres Skripsi</a>
        <a href="logout.php">Logout</a>
    </div>

    
    <div class="main-content">
        <h2 style="color: black;">Progres Skripsi Mahasiswa</h2>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'type'): ?>
            <div style="background-color: #ffc107; color: black; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                ❌ File harus berupa PDF.
            </div>
    <?php endif; ?>

    <?php if (isset($_GET['upload']) && $_GET['upload'] == 'success'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
            ✅ File berhasil diupload.
        </div>
    <?php endif; ?>


    <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="card-bab">
            <h3>BAB <?= $i ?> - <?= getJudulBab($i) ?></h3>

            <form method="post" action="simpan_progres.php" enctype="multipart/form-data" class="mb-3">
                <input type="hidden" name="bab" value="<?= $i ?>">
                <div class="input-group">
                    <input type="file" name="file_bab<?= $i ?>" class="form-control" accept="application/pdf" required>
                    <button class="btn btn-primary" type="submit">Upload</button>
                </div>
            </form>

            <table class="tabel-riwayat">
                <thead >
                    <tr>
                        <th>File</th>
                        <th>Tanggal Upload</th>
                        <th>Status Pembimbing 1</th>
                        <th>Status Pembimbing 2</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM progres_skripsi WHERE npm = ? AND bab = ? ORDER BY created_at DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $npm, $i);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><a href="uploads/<?= $row['file'] ?>" target="_blank"><?= $row['file'] ?></a></td>
                        <td><?= $row['created_at'] ?></td>
                       <td>
                        <?php
                        if ($row['nilai_dosen1']) {
                            echo '<span class="badge '.($row['nilai_dosen1'] == 'ACC' ? 'bg-success' : 'bg-warning').'">'.$row['nilai_dosen1'].'</span>';
                        } else {
                           
                            $substmt = $conn->prepare("SELECT nilai_dosen1 FROM progres_skripsi WHERE npm = ? AND bab = ? AND id < ? ORDER BY id DESC");
                            $substmt->bind_param("sii", $npm, $i, $row['id']);
                            $substmt->execute();
                            $subresult = $substmt->get_result();
                            $accFound = false;
                            while ($prev = $subresult->fetch_assoc()) {
                                if ($prev['nilai_dosen1'] === 'ACC') {
                                    echo '<span class="badge bg-success">ACC</span>';
                                    $accFound = true;
                                    break;
                                }
                            }
                            if (!$accFound) {
                                echo '<span class="text-muted">-</span>';
                            }
                        }
                        ?>
                        </td>

                       <td>
                        <?php
                        if ($row['nilai_dosen2']) {
                            echo '<span class="badge '.($row['nilai_dosen2'] == 'ACC' ? 'bg-success' : 'bg-warning').'">'.$row['nilai_dosen2'].'</span>';
                        } else {
                            $substmt = $conn->prepare("SELECT nilai_dosen2 FROM progres_skripsi WHERE npm = ? AND bab = ? AND id < ? ORDER BY id DESC");
                            $substmt->bind_param("sii", $npm, $i, $row['id']);
                            $substmt->execute();
                            $subresult = $substmt->get_result();
                            $accFound = false;
                            while ($prev = $subresult->fetch_assoc()) {
                                if ($prev['nilai_dosen2'] === 'ACC') {
                                    echo '<span class="badge bg-success">ACC </span>';
                                    $accFound = true;
                                    break;
                                }
                            }
                            if (!$accFound) {
                                echo '<span class="text-muted">-</span>';
                            }
                        }
                        ?>
                        </td>
                        <td>
                             <button class='btn btn-sm btn-info mb-1'
                                onclick='toggleKomentar(<?= $row['id'] ?>,
                                        <?= json_encode($row['komentar_dosen1']) ?>,
                                        <?= json_encode($row['komentar_dosen2']) ?>)'>
                            📄 Riwayat Komentar
                        </button>
                        </td>
                    </tr>
                    <tr id="komentar_row_<?= $row['id'] ?>" style="display:none;">
                        <td colspan="5">
                            <div class="riwayat-komentar">
                                <b>Komentar Pembimbing 1:</b><br><div id="komentar1_<?= $row['id'] ?>"></div><hr>
                                <b>Komentar Pembimbing 2:</b><br><div id="komentar2_<?= $row['id'] ?>"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endfor; ?>
</div>

</div>

<script>
    if (window.history.replaceState) {
        const url = new URL(window.location);
        url.searchParams.delete('upload');
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url.pathname);
    }
function toggleKomentar(id, komentar1, komentar2) {
    const row = document.getElementById("komentar_row_" + id);
    if (row.style.display === "none") {
        document.getElementById("komentar1_" + id).innerHTML = komentar1 || "Belum ada komentar";
        document.getElementById("komentar2_" + id).innerHTML = komentar2 || "Belum ada komentar";
        row.style.display = "table-row";
    } else {
        row.style.display = "none";
    }
}
</script>
</body>
</html>
