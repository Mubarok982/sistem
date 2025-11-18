<?php
session_start();
include "db.php";

$nip = $_SESSION['nip'] ?? '';
if (!$nip) {
    header("Location: login_dosen.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM biodata_dosen WHERE nip = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    $stmt = $conn->prepare("SELECT nama FROM akun_dosen WHERE nip = ?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    $akun = $result->fetch_assoc();

    $data = [
        'nama' => $akun['nama'] ?? '',
        'prodi' => '',
        'foto' => '',
        'no_hp' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Biodata Dosen</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style2.css">
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Form Biodata Dosen</h2>
  <div class="card">
    <form method="POST" action="simpan_biodata_dosen.php" enctype="multipart/form-data">
      <div class="row g-4">
        <!-- Kolom Kiri -->
        <div class="col-md-4 text-center">
          <?php if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])): ?>
            <img src="uploads/<?= $data['foto'] ?>?t=<?= time()  ?>" class="profile-pic mb-3" alt="Foto Profil">
          <?php else: ?>
            <div class="emoji-pic mb-3">👨‍🏫</div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Ganti Foto:</label>
            <input class="form-control" type="file" name="foto">
          </div>
          <p><strong>NIP:</strong> <?= htmlspecialchars($nip) ?></p>
        </div>

        <div class="col-md-8">
          <input type="hidden" name="nip" value="<?= htmlspecialchars($nip) ?>">

          <div class="mb-3">
            <label class="form-label"><strong>Nama</strong></label>
            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label"><strong>No. HP</strong></label>
            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($data['no_hp']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label"><strong>Prodi</strong></label>
            <input type="text" name="prodi" class="form-control" value="<?= htmlspecialchars($data['prodi']) ?>" required>
          </div>

          <div class="text-end">
            <?php if (!empty($data['prodi'])): ?>
              <a href="home_dosen.php" class="btn btn-secondary me-2">Batal</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
</body>
</html>

	