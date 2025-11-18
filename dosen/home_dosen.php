<?php
session_start();
include "db.php";

if (!isset($_SESSION['nip'])) {
    header("Location: login_dosen.php");
    exit();
}

$nip = $_SESSION['nip'];
$dosenQuery = mysqli_query($conn, "SELECT * FROM biodata_dosen WHERE nip = '$nip'");
$dosen = mysqli_fetch_assoc($dosenQuery);
$query = mysqli_query($conn, "
    SELECT m.*, ms.semester, ms.periode
    FROM biodata_mahasiswa m
    LEFT JOIN mahasiswa_skripsi ms ON m.npm = ms.npm
    WHERE m.nip_pembimbing1 = '$nip' OR m.nip_pembimbing2 = '$nip'
");
$foto_path = !empty($dosen['foto']) && file_exists("uploads/" . $dosen['foto']) 
    ? "uploads/" . $dosen['foto']
    : '';

function hitungProgres($conn, $npm) {
    $sql = "SELECT progres_dosen1, progres_dosen2 FROM progres_skripsi WHERE npm = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $npm);
    $stmt->execute();
    $result = $stmt->get_result();

    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += (int)$row['progres_dosen1'] + (int)$row['progres_dosen2'];
    }

    return min(100, round($total)); 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Dosen</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  
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
      <div class="text-center mt-4" style="font-size: 13px; color: #aaa;">
      &copy; ikhbal.khasodiq18@gmail.com
      </div>
    </div>

   <div class="main-content">
      <div class="card-box">
        <h3>Selamat Datang, <?= htmlspecialchars($dosen['nama']) ?></h3>
        <p class="text-muted">Dashboard Dosen Pembimbing</p>
        <div class="mt-4">
          <h5>Daftar Mahasiswa Bimbingan</h5>
          <div class="mb-3">
  <input type="text" id="searchInput" class="form-control w-25" placeholder="🔍 Cari Mahasiswa...">
</div>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="table-dark">
                <tr>
                  <th>No</th>
                  <th>Nama Mahasiswa</th>
                  <th>NPM</th>
                  <th>Semester</th>
                  <th>Periode</th>
                  <th>Judul Skripsi</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
              while ($mhs = mysqli_fetch_assoc($query)) {
                    $npm = $mhs['npm'];
                    $progres = hitungProgres($conn, $npm);

                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . htmlspecialchars($mhs['nama']) . "</td>";
                    echo "<td>" . htmlspecialchars($npm) . "</td>";
                    echo "<td>" . htmlspecialchars($mhs['semester'] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($mhs['periode'] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($mhs['judul_skripsi']) . "</td>";
                    echo "<td>
                            <a href='progres_mahasiswa.php?npm=$npm' class='btn btn-sm btn-primary mb-1'>📄 Lihat Progres</a>
                            <a href='komentar.php?npm=$npm' class='btn btn-sm btn-warning mb-1'>📝 Komentar</a>
                            <button class='btn btn-sm btn-success mb-1' data-npm='$npm' data-nama='{$mhs['nama']}' onclick='openChatModal(this)'>💬 Chat</button>
                            <button class='btn btn-sm btn-info mb-1' onclick='toggleProgres(\"bar_$npm\")'>📊 Bar Progres</button>
                          </td>";
                    echo "</tr>";
                    echo "<tr id='bar_$npm' style='display: none;'>
                            <td colspan='7'>
                              <div style='padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px;'>
                                <label><strong>Total Progres Skripsi:</strong></label>
                                <div style='background: #e0e0e0; border-radius: 8px; overflow: hidden; height: 24px; width: 100%; margin-bottom: 5px;'>
                                    <div style='width: {$progres}%; background: #4caf50; height: 100%; color: white; text-align: center; font-weight: bold;'>
                                        {$progres}%
                                    </div>
                                </div>
                                <small style='color: #555;'>Total poin akumulasi: {$progres} / 100</small>
                              </div>
                            </td>
                          </tr>";
                }

                ?>
                    <div id="chatModal" class="modal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);">
                      <div style="background:white; max-width:500px; margin:5% auto; padding:20px; border-radius:10px; position:relative;">
                        <h5>Kirim Chat ke <span id="chatNama"></span></h5>
                        <form action="kirim_chat.php" method="post">
                          <input type="hidden" name="npm" id="chatNpm">
                          <div class="mb-3">
                            <label>Pesan:</label>
                            <textarea name="pesan" class="form-control" rows="4" required></textarea>
                          </div>
                          <div class="d-flex justify-content-end">
                            <button type="button" onclick="closeChatModal()" class="btn btn-secondary me-2">Batal</button>
                            <button type="submit" class="btn btn-success">Kirim</button>
                          </div>
                        </form>
                      </div>
                    </div>

                    <script>
                    function openChatModal(btn) {
                        document.getElementById("chatNama").textContent = btn.dataset.nama;
                        document.getElementById("chatNpm").value = btn.dataset.npm;
                        document.getElementById("chatModal").style.display = 'block';
                    }
                    function closeChatModal() {
                        document.getElementById("chatModal").style.display = 'none';
                    }
                    </script>


              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  
</div>
<script>
  const searchInput = document.getElementById("searchInput");
  searchInput.addEventListener("keyup", function () {
    const filter = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });
function toggleProgres(id) {
    const row = document.getElementById(id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
</body>
</html>
