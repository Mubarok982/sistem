<?php
session_start();
include "db.php";

if (!isset($_SESSION['nip'])) {
    header("Location: login_dosen.php");
    exit();
}

$nip = $_SESSION['nip'];
$dosen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM biodata_dosen WHERE nip = '$nip'"));

$foto_path = !empty($dosen['foto']) && file_exists("../dosen/uploads/" . $dosen['foto']) 
    ? "../dosen/uploads/" . $dosen['foto']
    : '';

$npm = $_GET['npm'] ?? '';
$mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM biodata_mahasiswa WHERE npm = '$npm'"));

$progres_per_bab = [];
$query = mysqli_query($conn, "SELECT * FROM progres_skripsi WHERE npm = '$npm' ORDER BY bab, created_at DESC");
while ($row = mysqli_fetch_assoc($query)) {
    $bab = $row['bab'];
    $progres_per_bab[$bab][] = $row;
}
$totalBab = 5;
$totalACC = 0;

for ($bab = 1; $bab <= $totalBab; $bab++) {
    $acc_dosen1 = false;
    $acc_dosen2 = false;

    if (isset($progres_per_bab[$bab])) {
        foreach ($progres_per_bab[$bab] as $versi) {
            if (!$acc_dosen1 && $versi['nilai_dosen1'] === 'ACC') {
                $acc_dosen1 = true;
            }
            if (!$acc_dosen2 && $versi['nilai_dosen2'] === 'ACC') {
                $acc_dosen2 = true;
            }

            if ($acc_dosen1 && $acc_dosen2) break;
        }
    }

    if ($acc_dosen1) $totalACC++;
    if ($acc_dosen2) $totalACC++;
}

$maxProgress = $totalBab * 2; 
$persentase = round(($totalACC / $maxProgress) * 100);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Progres Mahasiswa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
  .table-fixed th:nth-child(6) { width: 15%; }  
</style>
</head>
<body>
<div class="header">
  <div class="logo">
    <img src="unimma.png" alt="Logo" style="height: 40px;" />
  </div>
  <div class="title">
    <h1>WEBSITE MONITORING SKRIPSI UNIMMA</h1>
  </div>
  <div class="profile">
    <a href="biodata_dosen.php">
      <?php if (!empty($dosen['foto']) && file_exists("uploads/" . $dosen['foto'])): ?>
        <img src="uploads/<?= htmlspecialchars($dosen['foto']) ?>?t=<?= time() ?>" width="50" height="50"
     style="object-fit: cover; border-radius: 50%; border: 2px solid white;" />
      <?php else: ?>
        <div style="width: 50px; height: 50px; border-radius: 50%; background: #eee;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 25px;">👤</div>
      <?php endif; ?>
    </a>
  </div>
</div>

<div class="container-fluid">
  <div class="sidebar">
    <h4 class="text-center">Panel Dosen</h4>
    <a href="home_dosen.php">Dashboard</a>
    <a href="biodata_dosen.php">Biodata</a>
    <a href="logout.php">Logout</a>
  </div>

  
  <div class="col-md-10 main-content">
    <div class="card-box ">
      <h4>Progres Mahasiswa</h4>
      <p><strong>Nama:</strong> <?= htmlspecialchars($mhs['nama']) ?></p>
      <p><strong>NPM:</strong> <?= htmlspecialchars($mhs['npm']) ?></p>
      <p><strong>Judul Skripsi:</strong> <?= htmlspecialchars($mhs['judul_skripsi']) ?></p>

      <?php if ($mhs['nip_pembimbing1'] == $nip): ?>
        <p><span class="badge-pembimbing">Anda adalah Pembimbing 1</span></p>
      <?php elseif ($mhs['nip_pembimbing2'] == $nip): ?>
        <p><span class="badge-pembimbing">Anda adalah Pembimbing 2</span></p>
      <?php else: ?>
        <p><span class="badge-pembimbing">Anda bukan pembimbing mahasiswa ini</span></p>
      <?php endif; ?>

      <?php for ($bab = 1; $bab <= 5; $bab++): ?>
        <h5 class="mt-4">BAB <?= $bab ?></h5>
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-fixed">
            <thead class="table-dark">
              <tr>
                <th>No</th>
                <th>File</th>
                <th>Tanggal Upload</th>
                <th>Status Dosen 1</th>
                <th>Status Dosen 2</th>
                <th>Komentar Pembimbing </th>
              </tr>
            </thead>
            <tbody>
              <?php if (isset($progres_per_bab[$bab])): ?>
                <?php $no = 1; foreach ($progres_per_bab[$bab] as $data): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><a href="../uploads/<?= htmlspecialchars($data['file']) ?>" target="_blank"><?= htmlspecialchars($data['file']) ?></a></td>
                    <td><?= $data['created_at'] ?></td>
                    <td>
                      <?php
                      if ($data['nilai_dosen1']) {
                          echo '<span class="badge-custom '.(strtolower($data['nilai_dosen1']) == 'acc' ? 'acc' : 'revisi').'">'
                               . htmlspecialchars($data['nilai_dosen1']) . '</span>';
                      } else {
                          
                          $cekAcc = mysqli_query($conn, "SELECT nilai_dosen1 FROM progres_skripsi 
                                                         WHERE npm = '$npm' AND bab = '$bab' AND id < '{$data['id']}' 
                                                         ORDER BY id DESC");
                          $found = false;
                          while ($cek = mysqli_fetch_assoc($cekAcc)) {
                              if ($cek['nilai_dosen1'] === 'ACC') {
                                  echo '<span class="badge-custom acc">ACC </span>';
                                  $found = true;
                                  break;
                              }
                          }
                          if (!$found) echo '<span class="text-muted">-</span>';
                      }
                      ?>
                    </td>
                    <td>
                      <?php
                        if ($data['nilai_dosen2']) {
                            echo '<span class="badge-custom '.(strtolower($data['nilai_dosen2']) == 'acc' ? 'acc' : 'revisi').'">'
                                 . htmlspecialchars($data['nilai_dosen2']) . '</span>';
                        } else {
                            $cekAcc = mysqli_query($conn, "SELECT nilai_dosen2 FROM progres_skripsi 
                                                           WHERE npm = '$npm' AND bab = '$bab' AND id < '{$data['id']}' 
                                                           ORDER BY id DESC");
                            $found = false;
                            while ($cek = mysqli_fetch_assoc($cekAcc)) {
                                if ($cek['nilai_dosen2'] === 'ACC') {
                                    echo '<span class="badge-custom acc">ACC </span>';
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) echo '<span class="text-muted">-</span>';
                        }
                        ?>
                    </td>
                    <td>
                          <button class='btn btn-sm btn-info mb-1'
                                  onclick='toggleKomentar(<?= $data['id'] ?>,
                                          <?= json_encode($data['komentar_dosen1']) ?>,
                                          <?= json_encode($data['komentar_dosen2']) ?>)'>
                              📄 Lihat 
                          </button>
                      </td>                                
                  </tr>
                 <tr id="komentar_row_<?= $data['id'] ?>" style="display:none;">
                    <td colspan="6">
                      <div class="riwayat-komentar">
                        <b>Komentar Pembimbing 1:</b><br>
                        <div id="komentar1_<?= $data['id'] ?>"></div>
                        <hr>
                        <b>Komentar Pembimbing 2:</b><br>
                        <div id="komentar2_<?= $data['id'] ?>"></div>
                      </div>
                    </td>
                  </tr>


                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">Belum ada progres</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php endfor; ?>

      <a href="home_dosen.php" class="btn btn-secondary mt-4">← Kembali ke Dashboard</a>
</tbody>
    </div>
  </div>
</div>
<script>
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
