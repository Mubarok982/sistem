<?php
session_start();
include "db.php";

$npm = $_SESSION['npm'] ?? '';
if (!$npm) {
    header("Location: login_mahasiswa.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM biodata_mahasiswa WHERE npm = ?");
$stmt->bind_param("s", $npm);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$dosen = [];
$resultDosen = $conn->query("SELECT * FROM biodata_dosen");
while ($row = $resultDosen->fetch_assoc()) {
    $dosen[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama         = $_POST['nama'];
    $prodi        = $_POST['prodi'];
    $no_hp        = $_POST['no_hp'];
    $judul        = $_POST['judul_skripsi'];
    $pembimbing1  = $_POST['nip_pembimbing1'];
    $pembimbing2  = $_POST['nip_pembimbing2'];
    $fotoBaru     = $_FILES['foto']['name'];
    
     if ($pembimbing1 === $pembimbing2) {
        echo "<script>alert('Dosen Pembimbing 1 dan 2 tidak boleh sama!'); window.history.back();</script>";
        exit();
    }

    if ($fotoBaru) {
        $ext = pathinfo($fotoBaru, PATHINFO_EXTENSION);
        $namafile = $npm . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $namafile);
    } else {
        $namafile = $data['foto'];
    }

    $stmt = $conn->prepare("UPDATE biodata_mahasiswa SET nama=?, prodi=?, no_hp=?, judul_skripsi=?, nip_pembimbing1=?, nip_pembimbing2=?, foto=? WHERE npm=?");
    $stmt->bind_param("ssssssss", $nama, $prodi, $no_hp, $judul, $pembimbing1, $pembimbing2, $namafile, $npm);
    $stmt->execute();

    header("Location: home_mahasiswa.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Biodata Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
</head>
<body style="background-image: url('1.jpg'); background-size: cover; background-position: center;">
<div class="container py-5">
    <h2 class="mb-4 text-center text-white">Update Biodata Mahasiswa</h2>
    <div class="card p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <?php if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])): ?>
                        <img src="uploads/<?= $data['foto'] ?>" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;border:3px solid #007bff;">
                    <?php else: ?>
                        <div class="mb-3" style="font-size:80px;">👤</div>
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
                                <option value="<?= $d['nip'] ?>" <?= $data['nip_pembimbing1'] == $d['nip'] ? 'selected' : '' ?>>
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
                                <option value="<?= $d['nip'] ?>" <?= $data['nip_pembimbing2'] == $d['nip'] ? 'selected' : '' ?>>
                                    <?= $d['nama'] ?> (<?= $d['nip'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="text-end">
                        <a href="home_mahasiswa.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
