<?php
// File: auth/fix_pass.php
include "../admin/db.php";

// Ganti username sesuai akun yang mau dibenerin
$username_target = 'dosen2'; // Contoh: NIP Dosen
$password_baru   = '123';    // Password yang dimau

// 1. Enkripsi Password
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// 2. Update ke Database
$query = "UPDATE mstr_akun SET password = '$password_hash' WHERE username = '$username_target'";

if (mysqli_query($conn, $query)) {
    echo "<h1>BERHASIL!</h1>";
    echo "Password untuk user <b>$username_target</b> sudah diubah jadi: <b>$password_baru</b><br>";
    echo "Hash baru di database: $password_hash";
    echo "<br><br><a href='login.php'>Coba Login Sekarang</a>";
} else {
    echo "Gagal update: " . mysqli_error($conn);
}
?>