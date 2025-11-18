<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$prodiQuery = mysqli_query($conn, "SELECT DISTINCT prodi FROM mahasiswa_skripsi ORDER BY prodi ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mahasiswa Skripsi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="ccsprogres.css">
    <style>
  .table-fixed {
    table-layout: fixed;
    width: 100%;
  }

    .table-fixed th:nth-child(1),
    .table-fixed td:nth-child(1) { width: 5%; }    
    .table-fixed th:nth-child(2),
    .table-fixed td:nth-child(2) { width: 15%; }   
    .table-fixed th:nth-child(3),
    .table-fixed td:nth-child(3) { width: 25%; }  
    .table-fixed th:nth-child(4),
    .table-fixed td:nth-child(4) { width: 10%; }
    .table-fixed th:nth-child(5),
    .table-fixed td:nth-child(5) { width: 15%; }
    .table-fixed th:nth-child(6),
    .table-fixed td:nth-child(6) { width: 30%; }
  
</style>
</head>
<body>


<div class="header">
    <div class="logo">
        <img src="unimma.png" alt="Logo" style="height: 40px;">
    </div>
    <div class="title">
        <h1>WEBSITE MONITORING SKRIPSI UNIMMA</h1>
    </div>
    <div class="profile">
        <div style="width:50px; height:50px; border-radius:50%; background:#eee; display:flex; align-items:center; justify-content:center; font-size:25px;">👤</div>
    </div>
</div>


<div class="container-fluid">
    <div class="sidebar">
      <h4 class="text-center">Panel Admin</h4>
      <a href="home_admin.php">Dashboard</a>
      <a href="data_mahasiswa.php">Data Mahasiswa</a>
      <a href="data_dosen.php">Data Dosen</a>
      <a href="akun_mahasiswa.php">Akun Mahasiswa</a>
      <a href="akun_dosen.php">Akun Dosen</a>
      <a href="mahasiswa_skripsi.php" class="active">Mahasiswa Skripsi</a>
      <a href="logout.php">Logout</a>
      <div class="text-center mt-4" style="font-size: 13px; color: #aaa;">
      &copy; ikhbal.khasodiq18@gmail.com
      </div>
    </div>
    
    
    <div class="col-md-10 main-content">
      <div class="card-box w-100 text-start">
        <h3>Data Mahasiswa Skripsi</h3>
        <a href="tambah_mahasiswa_skripsi.php" class="btn btn-success mb-3">+ Tambah Mahasiswa Skripsi</a>

        <?php
        
        while ($rowProdi = mysqli_fetch_assoc($prodiQuery)) {
            $prodi = $rowProdi['prodi'];
            echo "<h4>" . htmlspecialchars($prodi) . "</h4>";

            
            $queryMhs = mysqli_query($conn, "SELECT * FROM mahasiswa_skripsi WHERE prodi='$prodi' ORDER BY npm ASC");
            if (mysqli_num_rows($queryMhs) > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-striped table-fixed">';
                echo '<thead class="table-dark"><tr><th>No</th><th>NPM</th><th>Nama</th><th>Semester</th><th>Periode</th><th>Aksi</th></tr></thead><tbody>';
                $no = 1;
                while ($mhs = mysqli_fetch_assoc($queryMhs)) {
                    echo "<tr>
                            <td>" . $no++ . "</td>
                            <td>" . htmlspecialchars($mhs['npm']) . "</td>
                            <td>" . htmlspecialchars($mhs['nama']) . "</td>
                            <td>" . htmlspecialchars($mhs['semester']) . "</td>
                            <td>" . htmlspecialchars($mhs['periode']) . "</td>
                            <td>
                               <a href='edit_mahasiswa_skripsi.php?npm=" . $mhs['npm'] . "' class='btn btn-sm btn-warning'>✏️ Edit</a>
                               <a href='hapus_mahasiswa_skripsi.php?npm=" . $mhs['npm'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin ingin menghapus data ini?')\">🗑️ Hapus</a>
                            </td>
                          </tr>";
                }
                echo '</tbody></table></div>';
            } else {
                echo "<p>Tidak ada data mahasiswa untuk prodi " . htmlspecialchars($prodi) . ".</p>";
            }
        }
        ?>

      </div>
    </div>
    
  </div>
</div>

</body>
</html>
