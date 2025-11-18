<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$npm = $_GET['npm'] ?? '';
if (!$npm) {
    echo "❌ NPM tidak ditemukan!";
    exit();
}

$cek = mysqli_query($conn, "SELECT * FROM data_mahasiswa WHERE npm = '$npm'");
if (mysqli_num_rows($cek) == 0) {
    echo "❌ Data tidak ditemukan!";
    exit();
}


$hapus = mysqli_query($conn, "DELETE FROM data_mahasiswa WHERE npm = '$npm'");

if ($hapus) {
    header("Location: mahasiswa_skripsi.php?msg=deleted");
    exit();
} else {
    echo "❌ Gagal menghapus data!";
}
?>
