<?php
session_start();
include "db.php";

$npm = $_SESSION['npm'] ?? '';
$bab = $_GET['bab'] ?? '';

if (!$npm || !$bab) {
    echo "Parameter tidak valid.";
    exit();
}

$sql = "SELECT * FROM progres_skripsi WHERE npm = ? AND bab = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $npm, $bab);
$stmt->execute();
$result = $stmt->get_result();
?>

<h3>Histori BAB <?= $bab ?></h3>
<table border="1" cellpadding="8" cellspacing="0" style="color: white;">
    <thead>
        <tr>
            <th>No</th>
            <th>File</th>
            <th>Tanggal Upload</th>
            <th>Komentar Dosen 1</th>
            <th>Komentar Dosen 2</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><a href="<?= $row['file'] ?>" target="_blank">Lihat File</a></td>
            <td><?= $row['created_at'] ?></td>
            <td><?= $row['komentar_dosen1'] ?: 'Belum ada' ?></td>
            <td><?= $row['komentar_dosen2'] ?: 'Belum ada' ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
