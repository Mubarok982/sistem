<?php
include "db.php";

$nip = $_GET['nip'] ?? '';
if (!$nip) {
    echo "<script>alert('NIP tidak ditemukan!'); window.location='data_dosen.php';</script>";
    exit();
}

$query = mysqli_query($conn, "SELECT * FROM biodata_dosen WHERE nip = '$nip'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data dosen tidak ditemukan!'); window.location='data_dosen.php';</script>";
    exit();
}

$foto = $data['foto'];
$path = "../dosen/uploads/$foto";

if (!empty($foto) && file_exists($path)) {
    unlink($path); 
}


$delete = mysqli_query($conn, "DELETE FROM biodata_dosen WHERE nip = '$nip'");

if ($delete) {
    echo "<script>alert('✅ Data dosen berhasil dihapus.'); window.location='data_dosen.php';</script>";
} else {
    echo "<script>alert('❌ Gagal menghapus data dosen.'); window.location='data_dosen.php';</script>";
}
