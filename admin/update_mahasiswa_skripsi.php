<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$npm_lama = $_POST['npm_lama'] ?? '';
$npm_baru = $_POST['npm_baru'] ?? '';
$nama     = $_POST['nama'] ?? '';
$prodi    = $_POST['prodi'] ?? '';
$semester = $_POST['semester'] ?? '';
$periode  = $_POST['periode'] ?? '';

$nama = ucwords(strtolower(trim($nama)));
$prodi = ucwords(strtolower(trim($prodi)));
$semester = trim($semester); 
$periode  = trim($periode);

if (!$npm_lama || !$npm_baru || !$nama || !$prodi || !$semester || !$periode) {
    echo "❌ Semua data wajib diisi!";
    exit();
}

$update = mysqli_query($conn, "
    UPDATE mahasiswa_skripsi 
    SET npm = '$npm_baru', nama = '$nama', prodi = '$prodi', semester = '$semester', periode = '$periode'
    WHERE npm = '$npm_lama'
");

if ($update) {
    header("Location: mahasiswa_skripsi.php?msg=updated");
    exit();
} else {
    echo "❌ Gagal mengupdate data!";
}
?>
