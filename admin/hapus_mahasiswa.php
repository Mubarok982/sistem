<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$npm = $_GET['npm'] ?? '';

if (!$npm) {
    echo "<script>alert('NPM tidak ditemukan!'); location.href='data_mahasiswa.php';</script>";
    exit();
}

$query = "DELETE FROM data_mahasiswa WHERE npm = '$npm'";
if (mysqli_query($conn, $query)) {
    echo "<script>alert('✅ Data mahasiswa berhasil dihapus'); location.href='data_mahasiswa.php';</script>";
} else {
    echo "<script>alert('❌ Gagal menghapus data'); location.href='data_mahasiswa.php';</script>";
}
?>
