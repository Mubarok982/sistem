<?php
// Pastikan path ini benar mengarah ke db.php Anda
include "../admin/db.php"; 

// Kita set password "123" menggunakan PHP di komputer Anda sendiri
$password_baru = "123";
$hash_valid = password_hash($password_baru, PASSWORD_DEFAULT);

// Daftar user dummy yang akan di-reset
$usernames = "'admin_test', 'tu_test', 'dosen_test', 'mhs_test'";

// Update database
$query = "UPDATE mstr_akun SET password = '$hash_valid' WHERE username IN ($usernames)";

echo "<h3>Perbaikan Password</h3>";
echo "Password yang akan diset: <b>$password_baru</b><br>";
echo "Hash yang di-generate server Anda: <small>$hash_valid</small><br><br>";

if (mysqli_query($conn, $query)) {
    $affected = mysqli_affected_rows($conn);
    if ($affected > 0) {
        echo "<div style='color:green; font-weight:bold;'>BERHASIL!</div>";
        echo "$affected akun telah diupdate passwordnya menjadi '123'.";
        echo "<br><br><a href='login.php'>Coba Login Sekarang &raquo;</a>";
    } else {
        echo "<div style='color:orange;'>Tidak ada data yang berubah.</div>";
        echo "Mungkin username tidak ditemukan atau password sudah sama.";
        echo "<br>Coba cek tabel mstr_akun di phpMyAdmin, apakah username <b>admin_test</b> ada?";
    }
} else {
    echo "<div style='color:red;'>Error SQL:</div>";
    echo mysqli_error($conn);
}
?>