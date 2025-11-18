<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npm           = $_POST['npm'] ?? '';
    $nama          = $_POST['nama'] ?? '';
    $no_hp         = $_POST['no_hp'] ?? '';
    $prodi         = $_POST['prodi'] ?? '';
    $judul_skripsi = $_POST['judul_skripsi'] ?? '';
    $nip1          = $_POST['nip_pembimbing1'] ?? '';
    $nip2          = $_POST['nip_pembimbing2'] ?? '';
    $foto          = '';

    $cek = $conn->prepare("SELECT * FROM biodata_mahasiswa WHERE npm = ?");
    $cek->bind_param("s", $npm);
    $cek->execute();
    $hasil = $cek->get_result();
    $data_lama = $hasil->fetch_assoc();

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name      = $_FILES['foto']['tmp_name'];
        $original_name = basename($_FILES['foto']['name']);
        $ext           = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_name      = $npm . '_' . time() . '.' . $ext;
        $target_path   = 'uploads/' . $new_name;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $foto = $new_name;

            if (!empty($data_lama['foto']) && file_exists('uploads/' . $data_lama['foto'])) {
                unlink('uploads/' . $data_lama['foto']);
            }
        }
    } else {
        $foto = $data_lama['foto'] ?? '';
    }
    if ($nip1 === $nip2) {
    echo "<script>alert('Dosen pembimbing 1 dan 2 tidak boleh sama!'); window.history.back();</script>";
    exit();
}

    if ($data_lama) {
        $stmt = $conn->prepare("UPDATE biodata_mahasiswa SET nama=?, no_hp=?, prodi=?, judul_skripsi=?, nip_pembimbing1=?, nip_pembimbing2=?, foto=? WHERE npm=?");
        $stmt->bind_param("ssssssss", $nama, $no_hp, $prodi, $judul_skripsi, $nip1, $nip2, $foto, $npm);
    } else {
        $stmt = $conn->prepare("INSERT INTO biodata_mahasiswa (npm, nama, no_hp, prodi, judul_skripsi, nip_pembimbing1, nip_pembimbing2, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $npm, $nama, $no_hp, $prodi, $judul_skripsi, $nip1, $nip2, $foto);
    }

    if ($stmt->execute()) {
        header("Location: home_mahasiswa.php");
        exit();
    } else {
        echo "Gagal menyimpan data. " . $stmt->error;
    }
} else {
    header("Location: biodata_mahasiswa.php");
    exit();
}
