<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$npm  = $_POST['npm'] ?? '';
$nama = $_POST['nama'] ?? '';
$prodi = $_POST['prodi'] ?? '';
$semester = $_POST['semester'] ?? '';
$periode  = $_POST['periode'] ?? '';

$nama = ucwords(strtolower(trim($nama)));
$prodi = ucwords(strtolower(trim($prodi)));
$semester = trim($semester); 
$periode  = trim($periode);
if (!$npm || !$nama || !$prodi || !$semester || !$periode) {
    echo "❌ Semua field wajib diisi.";
    exit();
}

$cek = mysqli_query($conn, "SELECT * FROM mahasiswa_skripsi WHERE npm = '$npm'");
if (mysqli_num_rows($cek) > 0) {
    echo "❌ NPM sudah terdaftar.";
    exit();
}

$query = mysqli_query($conn, "INSERT INTO mahasiswa_skripsi (npm, nama, prodi, semester, periode) VALUES ('$npm', '$nama', '$prodi', '$semester', '$periode')");

if ($query) {
    header("Location: mahasiswa_skripsi.php?msg=sukses");
    exit();
} else {
    echo "❌ Gagal menyimpan data.";
}
?>
