<?php
session_start();
include "db.php";

// Cek Session Admin
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 1. Tangkap ID dari URL (Ini yang dikirim tombol reset)
$id_mhs = $_GET['id'] ?? '';

// Validasi ID
if (empty($id_mhs)) {
    echo "<script>alert('❌ ID Mahasiswa tidak ditemukan!'); window.location='akun_mahasiswa.php';</script>";
    exit();
}

// 2. Ambil NPM/Username untuk pesan Alert (Opsional)
$cek_user = mysqli_query($conn, "SELECT username FROM mstr_akun WHERE id = '$id_mhs'");
$data_user = mysqli_fetch_assoc($cek_user);
$npm_tampil = $data_user['username'] ?? "ID $id_mhs";

// Fungsi Generate Password Random
function generatePassword($length = 6) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

// 3. Buat Password Baru dan Hash
$passwordBaru = generatePassword();
$passwordHash = password_hash($passwordBaru, PASSWORD_DEFAULT);

// --- KOREKSI QUERY UTAMA ---
// Kita update tabel 'mstr_akun' langsung berdasarkan ID (Primary Key)
$query_update = "UPDATE mstr_akun SET password = '$passwordHash' WHERE id = '$id_mhs'";

$update = mysqli_query($conn, $query_update);

if ($update) {
    if (mysqli_affected_rows($conn) > 0) {
        echo "<script>
            alert('✅ Password untuk $npm_tampil berhasil direset!\\n\\nPassword Baru: $passwordBaru\\n\\n(Simpan password ini dan berikan ke mahasiswa)');
            window.location.href = 'akun_mahasiswa.php';
        </script>";
    } else {
        // ID ditemukan tapi baris tidak terpengaruh (mungkin sudah terupdate atau role salah)
        echo "<script>
            alert('⚠️ Gagal mereset password. Akun tidak ditemukan atau bukan role Mahasiswa.');
            window.location.href = 'akun_mahasiswa.php';
        </script>";
    }
} else {
    echo "<script>
        alert('❌ Gagal mereset password. Error Database: " . mysqli_error($conn) . "');
        window.location.href = 'akun_mahasiswa.php';
    </script>";
}
?>