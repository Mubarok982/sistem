<?php
session_start();
// Sesuaikan path ke db.php
include "db.php";

// Cek session login admin/operator
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nama_admin = $_SESSION['admin_username'] ?? 'Admin'; // Menggunakan username untuk Header
$page = 'tata_usaha'; // Penanda halaman aktif

// --- QUERY: Mengambil data user dengan role 'operator' atau 'tata_usaha' ---
$query_sql = "SELECT id, username, password, nama, role 
              FROM mstr_akun 
              WHERE role = 'operator' OR role = 'tata_usaha'
              ORDER BY username ASC";

$akun = mysqli_query($conn, $query_sql);

// Cek error query
if (!$akun) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Akun Tata Usaha & Operator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="ccsprogres.css"> 
    <style>
        /* --- LAYOUT CONSISTENCY --- */
        body { background-color: #f8f9fe; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #fff; z-index: 1050; padding: 0 25px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        /* --- STYLING --- */
        .card-custom { border: 0; box-shadow: 0 0 1.5rem 0 rgba(136, 152, 170, .1); border-radius: .75rem; background: #fff; }
        .table thead th { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; color: #8898aa; background-color: #f6f9fc; padding: 1rem 0.75rem; border-top: none; }
        .table td { padding: 0.75rem; vertical-align: middle; font-size: 0.85rem; }
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
            <small class="text-muted d-block">Admin</small>
            <span class="fw-bold"><?= htmlspecialchars($nama_admin) ?></span>
        </div>
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border"
            style="width: 40px; height: 40px;">👤</div>
    </div>
</div>

<?php include "../templates/sidebar_admin.php"; ?>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="h2 text-dark d-inline-block mb-0">Kelola Akun Tata Usaha & Operator</h6>
    </div>

    <div class="card card-custom">
        <div class="card-header border-0">
            <h5 class="mb-0">Daftar Akun</h5>
        </div>
        
        <div class="table-responsive py-4 px-4">
            <table class="table align-items-center table-flush table-hover" id="datatable-akun">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th width="15%">Username</th>
                        <th width="30%">Nama</th>
                        <th width="15%">Role</th>
                        <th width="25%">Password (Hash)</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($akun)): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            
                            <td class="fw-bold"><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td>
                                <span class="badge bg-primary"><?= strtoupper(htmlspecialchars($row['role'])) ?></span>
                            </td>
                            
                            <td style="word-break: break-all; font-size: 11px;"><?= substr(htmlspecialchars($row['password']), 0, 25) ?>...</td>
                            
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="reset_password_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning text-white" title="Reset Password" onclick="return confirm('Reset password akun ini menjadi 123?')">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    <a href="hapus_akun_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Hapus Akun" 
                                       onclick="return confirm('Yakin ingin menghapus akun <?= $row['username'] ?>? Tindakan ini tidak bisa dibatalkan.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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
        $('#datatable-akun').DataTable({
            dom: 'lfrtip', 
            language: { search: "Cari:", paginate: { previous: "Kembali", next: "Lanjut" } },
            columnDefs: [{ orderable: false, targets: 5 }]
        });
    });
</script>

</body>
</html>