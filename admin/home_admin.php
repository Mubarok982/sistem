<?php
session_start();
// Sesuaikan path db.php jika perlu (misal: include "../admin/db.php")
include "db.php";

// Cek login
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- QUERY BARU SESUAI STRUKTUR DATABASE BARU ---
// Mengambil data dari mstr_akun, data_mahasiswa, dan skripsi
$query_sql = "SELECT 
                m.username AS npm,
                m.nama,
                dm.prodi,
                s.judul AS judul_skripsi,
                d1.nama AS nama_dosen1,
                d2.nama AS nama_dosen2
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
              LEFT JOIN mstr_akun d1 ON s.pembimbing1 = d1.id
              LEFT JOIN mstr_akun d2 ON s.pembimbing2 = d2.id
              WHERE m.role = 'mahasiswa'
              ORDER BY m.username ASC";

$query = mysqli_query($conn, $query_sql);

// Cek jika query error
if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

$perProdi = [];
while ($row = mysqli_fetch_assoc($query)) {
    // Jika judul kosong, isi strip
    if (empty($row['judul_skripsi'])) {
        $row['judul_skripsi'] = "- Belum Mengajukan -";
    }
    $perProdi[$row['prodi']][] = $row;
}

// Fungsi Hitung Progres (Disesuaikan agar aman)
function hitungProgres($conn, $npm) {
    // Cek dulu apakah tabel progres_skripsi ada (untuk menghindari error fatal)
    $cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
    if (mysqli_num_rows($cek_tabel) == 0) {
        return 0; // Kembalikan 0 jika tabel belum dibuat
    }

    $sql = "SELECT progres_dosen1, progres_dosen2 FROM progres_skripsi WHERE npm = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $npm);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = 0;
        while ($row = $result->fetch_assoc()) {
            $total += (int)$row['progres_dosen1'] + (int)$row['progres_dosen2'];
        }
        return min(100, round($total));
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    .search-container { margin-bottom: 15px; }
    .search-container input { width: 300px; display: inline-block; }
    .table { table-layout: fixed; width: 100%; }
    .table th, .table td { text-align: center; vertical-align: middle; word-wrap: break-word; }
    /* Style tambahan untuk sidebar/layout */
    body { display: flex; flex-direction: column; min-height: 100vh; }
    .container-fluid { flex: 1; display: flex; }
    .sidebar { min-width: 250px; background: #343a40; color: white; padding: 20px; min-height: 100vh; }
    .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; }
    .sidebar a:hover { background: #495057; }
    .main-content { flex: 1; padding: 20px; }
  </style>
</head>
<body>

<div class="header height-100px">
    <div class="logo">
      <img src="unimma.png" alt="Logo" style="height: 40px;" />
    </div>
    <div class="title">
      <h1>WEBSITE MONITORING SKRIPSI UNIMMA</h1>
    </div>
    <div class="profile">
      <a href="#">
        <div style="width: 50px; height: 50px; border-radius: 50%; background: #eee;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 25px;">👤</div>
      </a>
    </div>
  </div>

<div class="container-fluid p-0">
  
  <div class="sidebar">
      <h4 class="text-center mb-4">Panel Admin</h4>
      <a href="home_admin.php">Dashboard</a>
      <a href="laporan_sidang.php">Laporan Sidang</a>
      <a href="data_mahasiswa.php">Data Mahasiswa</a>
      <a href="data_dosen.php">Data Dosen</a>
      <a href="akun_mahasiswa.php">Akun Mahasiswa</a>
      <a href="akun_dosen.php">Akun Dosen</a>
      <a href="mahasiswa_skripsi.php">Mahasiswa Skripsi</a>
      <a href="../auth/login.php?action=logout">Logout</a> <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">
        &copy; ikhbal.khasodiq18@gmail.com
      </div>
  </div>

  <div class="main-content">
    <div class="card p-4 shadow-sm">
      <h3>Selamat Datang, <?= htmlspecialchars($nama_admin) ?></h3>
      <p class="text-muted">Dashboard Admin Sistem Monitoring Skripsi</p>

      <div class="mb-3">
        <label for="filterProdi" class="form-label">Filter Prodi:</label>
        <select id="filterProdi" class="form-select w-25 d-inline-block">
          <option value="all">-- Semua Prodi --</option>
          <?php foreach (array_keys($perProdi) as $prodi): ?>
            <option value="<?= htmlspecialchars($prodi) ?>"><?= htmlspecialchars($prodi) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <input type="text" id="search" class="form-control w-25" placeholder="🔍 Cari Mahasiswa...">
      </div>

      <?php if (empty($perProdi)): ?>
          <div class="alert alert-info">Belum ada data mahasiswa.</div>
      <?php else: ?>
          <?php foreach ($perProdi as $prodi => $mahasiswas): ?>
            <div class="prodi-section" data-prodi="<?= htmlspecialchars($prodi) ?>">
              <h5 class="mt-4 border-bottom pb-2"><?= htmlspecialchars($prodi) ?></h5>
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-skripsi">
                  <thead class="table-dark">
                    <tr>
                      <th style="width: 5%;">No</th>
                      <th style="width: 15%;">NPM</th>
                      <th style="width: 20%;">Nama</th>
                      <th style="width: 15%;">Pembimbing 1</th>
                      <th style="width: 15%;">Pembimbing 2</th>
                      <th style="width: 20%;">Judul Skripsi</th>
                      <th style="width: 10%;">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $no = 1; foreach ($mahasiswas as $mhs): ?>
                      <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($mhs['npm']) ?></td>
                        <td><?= htmlspecialchars($mhs['nama']) ?></td>
                        <td><?= !empty($mhs['nama_dosen1']) ? htmlspecialchars($mhs['nama_dosen1']) : '-' ?></td>
                        <td><?= !empty($mhs['nama_dosen2']) ? htmlspecialchars($mhs['nama_dosen2']) : '-' ?></td>
                        <td><?= htmlspecialchars($mhs['judul_skripsi']) ?></td>
                        <?php $progres = hitungProgres($conn, $mhs['npm']); ?>
                        <td>
                          <a href="lihat_progres_admin.php?npm=<?= $mhs['npm'] ?>" class="btn btn-sm btn-primary mb-1 w-100">📄 Progres</a>
                          <button class="btn btn-sm btn-info mb-1 w-100" onclick="toggleProgres('bar_<?= $mhs['npm'] ?>')">📊 Grafik</button>
                        </td>
                      </tr>
                      <tr id="bar_<?= $mhs['npm'] ?>" style="display: none;">
                        <td colspan="7">
                          <div style="padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px;">
                            <label><strong>Total Progres Skripsi:</strong></label>
                            <div style="background: #e0e0e0; border-radius: 8px; overflow: hidden; height: 24px; width: 100%; margin-bottom: 5px;">
                              <div style="width: <?= $progres ?>%; background: #4caf50; height: 100%; color: white; text-align: center; font-weight: bold; line-height: 24px;">
                                <?= $progres ?>%
                              </div>
                            </div>
                            <small style="color: #555;">Total poin akumulasi: <?= $progres ?> / 100</small>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  // Filter Prodi
  document.getElementById("filterProdi").addEventListener("change", function() {
    const selected = this.value;
    document.querySelectorAll(".prodi-section").forEach(section => {
      if (selected === "all" || section.dataset.prodi === selected) {
        section.style.display = "block";
      } else {
        section.style.display = "none";
      }
    });
  });

  // Search Mahasiswa
  const searchInput = document.getElementById("search");
  searchInput.addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll(".table-skripsi tbody tr");
    rows.forEach(row => {
      // Cek apakah baris ini adalah baris data (bukan baris grafik yang tersembunyi)
      if (!row.id.startsWith('bar_')) {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(filter) ? "" : "none";
      }
    });
  });

  // Toggle Progres Bar
  function toggleProgres(id) {
    const row = document.getElementById(id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
  }
</script>

</body>
</html>