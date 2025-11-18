<?php
session_start();
include "db.php";

// --- 1. CEK APAKAH ADA POST DATA MASUK? ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "<h3>Error: Akses Ditolak!</h3>";
    echo "Anda mengakses file ini secara langsung, bukan dari tombol Simpan.<br>";
    echo "<a href='data_mahasiswa.php'>Kembali</a>";
    exit();
}

// --- 2. TAMPILKAN DATA YANG DITERIMA (DEBUG MODE) ---
// Jika Anda masih bingung, uncomment (hapus tanda //) 3 baris di bawah ini untuk melihat data mentah
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

// --- 3. VALIDASI SATU PER SATU (CARI YANG KOSONG) ---
$errors = [];

if (empty($_POST['id_mhs'])) {
    $errors[] = "ID Mahasiswa (Hidden Input) tidak terkirim. Cek file edit_mahasiswa.php baris <input type='hidden' name='id_mhs'...>";
}

if (empty($_POST['npm'])) {
    $errors[] = "NPM Kosong. Pastikan di form edit ada atribut name='npm'";
}

if (empty($_POST['nama'])) {
    $errors[] = "Nama Kosong. Pastikan di form edit ada atribut name='nama'";
}

if (empty($_POST['prodi'])) {
    $errors[] = "Prodi Kosong. Pastikan di form edit ada atribut name='prodi'";
}

// Jika ada error, Stop dan Tampilkan
if (!empty($errors)) {
    echo "<div style='background: #ffcccc; padding: 20px; border: 1px solid red; font-family: sans-serif;'>";
    echo "<h3 style='margin-top:0;'>❌ Gagal Memproses Data!</h3>";
    echo "<ul>";
    foreach ($errors as $err) {
        echo "<li>$err</li>";
    }
    echo "</ul>";
    echo "<br><button onclick='history.back()'>Kembali ke Form Edit</button>";
    echo "</div>";
    exit(); // Matikan script di sini
}

// --- 4. JIKA LOLOS VALIDASI, LANJUT PROSES UPDATE ---

$id_mhs   = $_POST['id_mhs'];
$npm_baru = mysqli_real_escape_string($conn, $_POST['npm']);
$nama     = mysqli_real_escape_string($conn, $_POST['nama']);
$prodi    = mysqli_real_escape_string($conn, $_POST['prodi']);
$telepon  = isset($_POST['telepon']) ? mysqli_real_escape_string($conn, $_POST['telepon']) : ''; // Telepon boleh kosong
$judul    = mysqli_real_escape_string($conn, $_POST['judul_skripsi']);

// Handle Pembimbing (Null Handling)
$p1 = !empty($_POST['pembimbing1']) ? "'" . $_POST['pembimbing1'] . "'" : "NULL";
$p2 = !empty($_POST['pembimbing2']) ? "'" . $_POST['pembimbing2'] . "'" : "NULL";

// --- PROSES A: AMBIL NPM LAMA ---
$cek_npm = mysqli_query($conn, "SELECT npm FROM data_mahasiswa WHERE id = '$id_mhs'");
$row_lama = mysqli_fetch_assoc($cek_npm);
$npm_lama = $row_lama ? $row_lama['npm'] : '';

// --- PROSES B: UPDATE ---

// 1. Update MSTR_AKUN
$update_akun = mysqli_query($conn, "UPDATE mstr_akun SET nama='$nama', username='$npm_baru' WHERE id='$id_mhs'");
if (!$update_akun) { die("Gagal Update Akun: " . mysqli_error($conn)); }

// 2. Update DATA_MAHASISWA
$update_biodata = mysqli_query($conn, "UPDATE data_mahasiswa SET npm='$npm_baru', prodi='$prodi', telepon='$telepon' WHERE id='$id_mhs'");
if (!$update_biodata) { die("Gagal Update Biodata: " . mysqli_error($conn)); }

// 3. Update SKRIPSI
$cek_skripsi = mysqli_query($conn, "SELECT id FROM skripsi WHERE id_mahasiswa = '$id_mhs'");
if (mysqli_num_rows($cek_skripsi) > 0) {
    $update_skripsi = mysqli_query($conn, "UPDATE skripsi SET judul='$judul', pembimbing1=$p1, pembimbing2=$p2 WHERE id_mahasiswa='$id_mhs'");
} else {
    if (!empty($judul)) {
        $update_skripsi = mysqli_query($conn, "INSERT INTO skripsi (id_mahasiswa, judul, pembimbing1, pembimbing2, tgl_pengajuan_judul, tema, skema) VALUES ('$id_mhs', '$judul', $p1, $p2, CURDATE(), 'Software Engineering', 'Reguler')");
    }
}
if (isset($update_skripsi) && !$update_skripsi) { die("Gagal Update Skripsi: " . mysqli_error($conn)); }


// --- PROSES C: SINKRONISASI PROGRES (Jika NPM Berubah) ---
if ($npm_lama && $npm_lama != $npm_baru) {
    $cek_progres = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
    if (mysqli_num_rows($cek_progres) > 0) {
        mysqli_query($conn, "UPDATE progres_skripsi SET npm = '$npm_baru' WHERE npm = '$npm_lama'");
    }
}

// --- FINISH ---
echo "<script>alert('✅ Data Berhasil Diperbarui!'); window.location='data_mahasiswa.php';</script>";
?>