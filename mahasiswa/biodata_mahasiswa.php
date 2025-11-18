<?php
session_start();
include "db.php";

$npm = $_SESSION['npm'] ?? '';
if (!$npm) {
    header("Location: login_mahasiswa.php");
    exit();
}

$dosen = [];
$result = $conn->query("SELECT * FROM biodata_dosen");
while ($row = $result->fetch_assoc()) {
    $dosen[] = $row;
}

$stmt = $conn->prepare("SELECT * FROM biodata_mahasiswa WHERE npm = ?");
$stmt->bind_param("s", $npm);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    $stmt = $conn->prepare("SELECT nama FROM akun_mahasiswa WHERE npm = ?");
    $stmt->bind_param("s", $npm);
    $stmt->execute();
    $result = $stmt->get_result();
    $akun = $result->fetch_assoc();

    $data = [
        'nama' => $akun['nama'] ?? '',
        'prodi' => '',
        'judul_skripsi' => '',
        'nip_pembimbing1' => '',
        'nip_pembimbing2' => '',
        'foto' => '',
        'no_hp' => ''
    ];
    
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Biodata Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4 text-center">Form Biodata Mahasiswa</h2>
    <div class="card">
        <form method="POST" action="simpan_biodata_mahasiswa.php" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <?php if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])): ?>
                        <img src="uploads/<?= $data['foto'] ?>" class="profile-pic mb-3" alt="Foto Profil">
                    <?php else: ?>
                        <div class="emoji-pic mb-3">👤</div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Ganti Foto:</label>
                        <input class="form-control" type="file" name="foto">
                    </div>
                    <p><strong>NPM:</strong> <?= htmlspecialchars($npm) ?></p>
                </div>

                <div class="col-md-8">
                    <input type="hidden" name="npm" value="<?= htmlspecialchars($npm) ?>">

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

                    <div class="mb-3">
                        <label class="form-label"><strong>Judul Skripsi</strong></label>
                        <textarea name="judul_skripsi" class="form-control" rows="3" required><?= htmlspecialchars($data['judul_skripsi']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Dosen Pembimbing 1</strong></label>
                        <select name="nip_pembimbing1" class="form-select" required>
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($dosen as $d): ?>
                                <option value="<?= $d['nip'] ?>" <?= ($data['nip_pembimbing1'] ?? '') == $d['nip'] ? 'selected' : '' ?>>
                                    <?= $d['nama'] ?> (<?= $d['nip'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Dosen Pembimbing 2</strong></label>
                        <select name="nip_pembimbing2" class="form-select" required>
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($dosen as $d): ?>
                                <option value="<?= $d['nip'] ?>" <?= ($data['nip_pembimbing2'] ?? '') == $d['nip'] ? 'selected' : '' ?>>
                                    <?= $d['nama'] ?> (<?= $d['nip'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="text-end">
                        <?php if (!empty($data['prodi'])): ?>
                            <a href="home_mahasiswa.php" class="btn btn-secondary me-2">Batal</a>
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
