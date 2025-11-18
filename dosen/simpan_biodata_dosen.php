<?php
session_start();
include "db.php";

if (!isset($_POST['nip'])) {
    echo "❌ Data tidak valid.";
    exit();
}

$nip   = trim($_POST['nip']);
$nama  = ucwords(strtolower(trim($_POST['nama'])));
$prodi = ucwords(strtolower(trim($_POST['prodi'])));
$no_hp = trim($_POST['no_hp']);

$foto = '';
$upload_folder = 'uploads/';

if (!file_exists($upload_folder)) {
    mkdir($upload_folder, 0777, true);
}

if (!empty($_FILES['foto']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        echo "<script>alert('❌ Format file tidak diperbolehkan. Hanya JPG, JPEG, PNG'); history.back();</script>";
        exit();
    }

    $foto = 'dosen_' . $nip . '.' . $ext;
    $tujuan = $upload_folder . $foto;

    
    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM biodata_dosen WHERE nip = '$nip'"));
    if (!empty($old['foto']) && file_exists($upload_folder . $old['foto'])) {
        unlink($upload_folder . $old['foto']);
    }

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        echo "<script>alert('❌ Upload foto gagal.'); history.back();</script>";
        exit();
    }
}


$cek = mysqli_query($conn, "SELECT nip FROM biodata_dosen WHERE nip = '$nip'");
if (mysqli_num_rows($cek) > 0) {
    
    if (!empty($foto)) {
        $stmt = $conn->prepare("UPDATE biodata_dosen SET nama=?, prodi=?, no_hp=?, foto=? WHERE nip=?");
        $stmt->bind_param("sssss", $nama, $prodi, $no_hp, $foto, $nip);
    } else {
        $stmt = $conn->prepare("UPDATE biodata_dosen SET nama=?, prodi=?, no_hp=? WHERE nip=?");
        $stmt->bind_param("ssss", $nama, $prodi, $no_hp, $nip);
    }
} else {
    
    $stmt = $conn->prepare("INSERT INTO biodata_dosen (nip, nama, prodi, no_hp, foto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nip, $nama, $prodi, $no_hp, $foto);
}

if ($stmt->execute()) {
    echo "<script>alert('✅ Biodata dosen berhasil disimpan.'); window.location.href='home_dosen.php';</script>";
} else {
    echo "<script>alert('❌ Gagal menyimpan biodata.'); history.back();</script>";
}
?>
