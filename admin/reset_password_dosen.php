<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$nip = $_GET['nip'] ?? '';

if (!$nip) {
    echo "❌ NIP tidak ditemukan.";
    exit();
}

function generatePassword($length = 6) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

$passwordBaru = generatePassword();

$update = mysqli_query($conn, "UPDATE akun_dosen SET password='$passwordBaru' WHERE nip='$nip'");

if ($update) {
    echo "<script>
        alert('✅ Password berhasil direset! Password baru: $passwordBaru');
        window.location.href = 'akun_dosen.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Gagal mereset password.');
        window.location.href = 'akun_dosen.php';
    </script>";
}
?>
