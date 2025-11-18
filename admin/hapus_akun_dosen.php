<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$nip = $_GET['nip'] ?? '';

if (!$nip) {
    echo "<script>
        alert('❌ NIP tidak ditemukan!');
        window.location.href = 'akun_dosen.php';
    </script>";
    exit();
}

$hapus = mysqli_query($conn, "DELETE FROM akun_dosen WHERE nip='$nip'");

if ($hapus) {
    echo "<script>
        alert('✅ Akun dosen berhasil dihapus.');
        window.location.href = 'akun_dosen.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Gagal menghapus akun dosen.');
        window.location.href = 'akun_dosen.php';
    </script>";
}
?>
