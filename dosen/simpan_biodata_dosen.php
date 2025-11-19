<?php
session_start();
include "../admin/db.php";

// 1. Cek Validitas Data
if (!isset($_POST['id_dosen'])) {
    echo "<script>alert('❌ Data tidak valid.'); window.history.back();</script>";
    exit();
}

$id_dosen = $_POST['id_dosen'];
$nama     = mysqli_real_escape_string($conn, $_POST['nama']);
$prodi    = mysqli_real_escape_string($conn, $_POST['prodi']);

// --- [FIX] CONFIG UPLOAD MENGGUNAKAN ABSOLUTE PATH ---
// dirname(__DIR__) akan mengambil path root project (C:/xampp/htdocs/Sistem)
// Jadi kita arahkan ke folder 'uploads' di root project
$upload_dir = dirname(__DIR__) . '/uploads/'; 

// Cek apakah folder ada, jika tidak buat baru
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Ambil data lama
$query_lama = mysqli_query($conn, "SELECT m.foto, d.ttd FROM mstr_akun m LEFT JOIN data_dosen d ON m.id=d.id WHERE m.id='$id_dosen'");
$data_lama  = mysqli_fetch_assoc($query_lama);
$nama_foto  = $data_lama['foto'] ?? ''; 
$nama_ttd   = $data_lama['ttd'] ?? '';

// --- 2. HANDLE FOTO ---
if (!empty($_FILES['foto']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        echo "<script>alert('❌ Format foto salah! Hanya JPG, JPEG, PNG.'); window.history.back();</script>";
        exit();
    }

    // Nama file unik
    $nama_foto_baru = 'dosen_' . $id_dosen . '_' . time() . '.' . $ext;
    $tujuan = $upload_dir . $nama_foto_baru;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        // Hapus foto lama jika ada
        if (!empty($nama_foto) && file_exists($upload_dir . $nama_foto)) {
            unlink($upload_dir . $nama_foto);
        }
        $nama_foto = $nama_foto_baru;
    } else {
        // Tampilkan error spesifik jika gagal
        echo "<script>alert('❌ Gagal upload. Pastikan folder uploads ada di C:/xampp/htdocs/Sistem/uploads/'); window.history.back();</script>";
        exit();
    }
}

// --- 3. HANDLE TANDA TANGAN ---
if (!empty($_FILES['ttd']['name'])) {
    $ext = strtolower(pathinfo($_FILES['ttd']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($ext, $allowed)) {
        $nama_ttd_baru = 'ttd_' . $id_dosen . '_' . time() . '.' . $ext;
        $tujuan_ttd = $upload_dir . $nama_ttd_baru;

        if (move_uploaded_file($_FILES['ttd']['tmp_name'], $tujuan_ttd)) {
            if (!empty($nama_ttd) && file_exists($upload_dir . $nama_ttd)) {
                unlink($upload_dir . $nama_ttd);
            }
            $nama_ttd = $nama_ttd_baru;
        }
    }
}

// --- 4. UPDATE DATABASE ---
$update_akun = mysqli_query($conn, "UPDATE mstr_akun SET nama='$nama', foto='$nama_foto' WHERE id='$id_dosen'");

$cek_dosen = mysqli_query($conn, "SELECT id FROM data_dosen WHERE id='$id_dosen'");
if (mysqli_num_rows($cek_dosen) > 0) {
    $update_detail = mysqli_query($conn, "UPDATE data_dosen SET prodi='$prodi', ttd='$nama_ttd' WHERE id='$id_dosen'");
} else {
    $nidk = $_SESSION['nip']; 
    $update_detail = mysqli_query($conn, "INSERT INTO data_dosen (id, nidk, prodi, ttd) VALUES ('$id_dosen', '$nidk', '$prodi', '$nama_ttd')");
}

if ($update_akun && $update_detail) {
    echo "<script>alert('✅ Biodata berhasil diperbarui!'); window.location.href='biodata_dosen.php';</script>";
} else {
    echo "<script>alert('❌ Gagal update database: " . mysqli_error($conn) . "'); window.history.back();</script>";
}
?>