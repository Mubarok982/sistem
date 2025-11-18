<?php
include "db.php";

$nip = $_POST['nip'];
$nama = $_POST['nama'];
$prodi = $_POST['prodi'];
$no_hp = $_POST['no_hp'];
$foto = $_FILES['foto']['name'];

if ($foto != '') {
    $target = "../dosen/uploads/" . basename($foto);
    move_uploaded_file($_FILES['foto']['tmp_name'], $target);
} else {
    $foto = '';
}

mysqli_query($conn, "INSERT INTO biodata_dosen (nip, nama, prodi, no_hp, foto)
                     VALUES ('$nip', '$nama', '$prodi', '$no_hp', '$foto')");

header("Location: data_dosen.php");
exit();
