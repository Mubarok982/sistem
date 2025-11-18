<?php
include "db.php";

$nip   = $_POST['nip'] ?? '';
$nama  = $_POST['nama'] ?? '';
$prodi = $_POST['prodi'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';
$foto_baru = $_FILES['foto']['name'] ?? '';

if (!$nip) {
    echo "<script>alert('NIP tidak ditemukan!'); window.location='data_dosen.php';</script>";
    exit();
}

$get = mysqli_query($conn, "SELECT * FROM biodata_dosen WHERE nip='$nip'");
$data = mysqli_fetch_assoc($get);
$foto_lama = $data['foto'] ?? '';

$nama_file = $foto_lama;

if (!empty($foto_baru)) {
    $target_dir = "../dosen/uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $nama_file = uniqid() . "_" . basename($foto_baru);
    $target_file = $target_dir . $nama_file;

    $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed)) {
        echo "<script>alert('❌ Format foto hanya JPG, JPEG, atau PNG!'); window.history.back();</script>";
        exit();
    }

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        // Hapus foto lama jika ada
        if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
            unlink($target_dir . $foto_lama);
        }
    } else {
        echo "<script>alert('❌ Gagal upload foto baru!'); window.history.back();</script>";
        exit();
    }
}


$query = "UPDATE biodata_dosen 
          SET nama='$nama', prodi='$prodi', no_hp='$no_hp', foto='$nama_file' 
          WHERE nip='$nip'";

if (mysqli_query($conn, $query)) {
    echo "<script>alert('✅ Data dosen berhasil diperbarui!'); window.location='data_dosen.php';</script>";
} else {
    echo "<script>alert('❌ Gagal memperbarui data dosen!'); window.history.back();</script>";
}
