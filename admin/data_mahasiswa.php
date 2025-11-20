<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$page = 'data_mahasiswa';
$nama_admin = $_SESSION['admin_username'] ?? 'Admin';

// --- QUERY DATA MAHASISWA LENGKAP ---
$query_mhs = "SELECT 
                m.id,
                m.nama,
                dm.npm, 
                dm.angkatan,
                dm.prodi,
                dm.telepon,
                dm.email,
                dm.status_mahasiswa,
                dm.status_beasiswa,
                dm.jenis_kelamin
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              WHERE m.role = 'mahasiswa'
              ORDER BY dm.angkatan DESC, m.nama ASC";

$result = mysqli_query($conn, $query_mhs);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Data Mahasiswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    /* --- LAYOUT UTAMA (Fixed Position) --- */
    body { background-color: #f8f9fe; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #fff; z-index: 1050; padding: 0 25px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .sidebar-area { width: 250px; background: #343a40; color: white; position: fixed; height: calc(100vh - 70px); overflow-y: auto; z-index: 1000; top: 70px; }
    .main-content { margin-left: 250px; padding: 30px; width: auto; }

    /* --- CARD & TABLE STYLE (CLEAN) --- */
    .card-custom { border: 0; box-shadow: 0 0 1rem 0 rgba(136, 152, 170, .1); border-radius: .75rem; background: #fff; }
    .card-header { background-color: #fff; border-bottom: 1px solid #e9ecef; padding: 1.5rem; border-radius: .75rem .75rem 0 0; }
    
    .table thead th { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; color: #8898aa; background-color: #f6f9fc; border-bottom: 1px solid #e9ecef; padding: 1rem 0.75rem; border-top: none; }
    .table td { padding: 0.75rem 0.75rem; vertical-align: middle; font-size: 0.85rem; color: #525f7f; border-bottom: 1px solid #f0f0f0; }

    /* --- AKSI DROPDOWN STYLE (KUNCI ESTETIKA) --- */

    /* 1. Tombol Plus Biru (Button) */
    .btn-plus-action {
        width: 30px; height: 30px; 
        border-radius: 50%; 
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%); /* Gradien Biru */
        color: white; border: none;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 6px rgba(50,50,93,.11), 0 1px 3px rgba(0,0,0,.08);
        transition: all 0.2s ease;
    }
    .btn-plus-action:hover { background-color: #324cdd; transform: translateY(-1px); }
    .dropdown-toggle::after { content: none; } /* Hilangkan panah default */
    
    /* 2. Kotak Ikon di Dalam Dropdown Item */
    .icon-shape {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        width: 32px;
        height: 32px;
        margin-right: 10px;
        box-shadow: 0 4px 6px rgba(50,50,93,.11), 0 1px 3px rgba(0,0,0,.08);
    }
    
    /* 3. Item Dropdown Hover/Active */
    .dropdown-menu {
        border: 0;
        box-shadow: 0 0 1rem rgba(0,0,0,.15);
        border-radius: 0.5rem;
        min-width: 220px;
    }
    .dropdown-item {
        transition: all 0.2s;
        font-weight: 500;
        color: #525f7f;
    }
    .dropdown-item:hover {
        background-color: #f6f9fc;
        transform: translateX(3px); /* Efek geser dikit */
    }
    .dropdown-item.text-danger:hover { background-color: #fde5e5; }

    /* DataTables Fixes */
    div.dataTables_filter input { border-radius: 0.5rem; padding: 0.5rem 1rem; border: 1px solid #dee2ee; box-shadow: none; }
    div.dataTables_wrapper div.row:nth-child(3) { border-top: 1px solid #e9ecef; margin-top: 1rem; padding-top: 1rem; }
  </style>
</head>

<body>

  <div class="header">
    <div class="d-flex align-items-center">
      <img src="unimma.png" alt="Logo" style="height: 40px;">
      <h5 class="m-0 ms-3 fw-bold text-dark">FAKULTAS TEKNIK</h5>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="text-end lh-sm">
        <small class="text-muted d-block">Operator</small>
        <span class="fw-bold"><?= htmlspecialchars($nama_admin) ?></span>
      </div>
      <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border"
        style="width: 40px; height: 40px;">
        👤
      </div>
    </div>
  </div>

  <div class="sidebar-area">
    <?php 
        // Menggunakan class .sidebar-area dan .sidebar di sidebar_admin.php
        // untuk fixed position layout
        include "../templates/sidebar_admin.php"; 
    ?>
  </div>

  <div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h6 class="h2 text-dark d-inline-block mb-0">Data Mahasiswa</h6>
    </div>

    <div class="card card-custom">
      <div class="card-header border-0">
        <div class="row align-items-center">
          <div class="col-md-12 d-flex flex-wrap gap-2">
            <a href="tambah_mahasiswa.php" class="btn btn-primary-custom shadow-sm py-2 px-3">
              <i class="fas fa-plus me-1"></i> Tambah Mahasiswa
            </a>

            <form action="proses_import.php" method="POST" enctype="multipart/form-data" class="d-flex gap-1 align-items-center">
              <input type="file" name="file_excel" class="form-control form-control-sm" style="width: 200px;"
                required accept=".csv">
              <button type="submit" name="import" class="btn btn-success-custom btn-sm shadow-sm py-2 px-3">
                <i class="fas fa-file-csv me-1"></i> Import CSV
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="table-responsive py-4 px-4">
        <table class="table align-items-center table-flush table-hover" id="datatable-basic">
          <thead>
            <tr>
              <th class="text-center" width="5%">No</th>
              <th class="text-center" width="5%">Aksi</th>
              <th>NPM</th>
              <th>Nama</th>
              <th>Angkatan</th>
              <th>Prodi</th>
              <th>Telepon</th>
              <th>Email</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn-plus-action" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
                      <i class="fas fa-plus"></i>
                    </button>
                    
                    <div class="dropdown-menu dropdown-menu-end shadow-lg">
                      <h6 class="dropdown-header text-muted small text-uppercase">Kelola Data</h6>
                      
                      <a class="dropdown-item d-flex align-items-center" href="edit_mahasiswa.php?id=<?= $row['id'] ?>">
                          <div class="icon-shape bg-warning text-white">
                              <i class="fas fa-pen fa-xs"></i>
                          </div>
                          <span>Edit Data</span>
                      </a>

                      <div class="dropdown-divider my-1"></div>

                      <a class="dropdown-item d-flex align-items-center text-danger" href="hapus_mahasiswa.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin menghapus data ini?')">
                          <div class="icon-shape bg-danger text-white">
                              <i class="fas fa-trash fa-xs"></i>
                          </div>
                          <span>Hapus Data</span>
                      </a>
                    </div>
                  </div>
                </td>

                <td class="fw-bold"><?= htmlspecialchars($row['npm']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['angkatan']) ?></td>
                <td><?= htmlspecialchars($row['prodi']) ?></td>
                <td><?= htmlspecialchars($row['telepon'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                <td>
                  <span class="badge bg-secondary"><?= htmlspecialchars($row['status_mahasiswa']) ?></span>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#datatable-basic').DataTable({
        dom: 'lfrtip', 
        "pageLength": 10,
        language: {
          search: "Cari Data:",
          paginate: { previous: "Kembali", next: "Lanjut" }
        },
        columnDefs: [
          { orderable: false, targets: 1 }
        ]
      });
    });
  </script>

</body>
</html>