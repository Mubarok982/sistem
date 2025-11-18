<?php
session_start();
include "db.php";
include "../config_fonnte.php";
include "../kirim_fonnte.php";

$npm = $_SESSION['npm'] ?? '';
$bab = $_POST['bab'] ?? '';

$fileField = 'file_bab' . $bab;
$file = $_FILES[$fileField] ?? null;

if (!$npm || !$bab || !$file) {
    echo "Data tidak lengkap.";
    exit();
}

$namaFile = basename($file['name']);
$ekstensi = pathinfo($namaFile, PATHINFO_EXTENSION);
$targetDir = "uploads/";
$mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM biodata_mahasiswa WHERE npm = '$npm'"));
$nama_mhs = str_replace(' ', '_', $mhs['nama']);
$namaBaru = "Progres_" . $nama_mhs . "_BAB" . $bab . "_" . time() . "." . $ekstensi;
$targetFile = $targetDir . $namaBaru;

if (strtolower($ekstensi) != 'pdf') {
    header("Location: progres_skripsi.php?error=type");
    exit();
}

if (move_uploaded_file($file["tmp_name"], $targetFile)) {
    $stmt = $conn->prepare("INSERT INTO progres_skripsi (npm, bab, file) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $npm, $bab, $namaBaru);
    $stmt->execute();
    $mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM biodata_mahasiswa WHERE npm = '$npm'"));
    $dosen1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM biodata_dosen WHERE nip = '{$mhs['nip_pembimbing1']}'"));
    $dosen2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM biodata_dosen WHERE nip = '{$mhs['nip_pembimbing2']}'"));

    $namaMhs = $mhs['nama'] ?? 'Mahasiswa';
    $judul = $mhs['judul_skripsi'] ?? '-';
    $pesan = "*Notifikasi Progres Skripsi UNIMMA*\n"
           . "👨‍🎓 Nama: $namaMhs\n"
           . "🆔 NPM: $npm\n"
           . "📚 Judul: $judul\n"
           . "📄 BAB: $bab\n"
           . "✅ Status: Mahasiswa telah mengunggah file skripsi.";
    if (!empty($dosen1['no_hp'])) {
        kirimWaFonnte($dosen1['no_hp'], $pesan);
        sleep(1); 
    }

    if (!empty($dosen2['no_hp'])) {
        kirimWaFonnte($dosen2['no_hp'], $pesan);
    }

    header("Location: progres_skripsi.php?upload=success");
exit();
} else {
    echo "Gagal upload file.";
}
