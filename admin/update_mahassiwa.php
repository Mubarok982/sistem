<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_mhs   = $_POST['id_mhs'];
    
    // Tangkap data baru dari form
    $npm_baru = mysqli_real_escape_string($conn, $_POST['npm']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi    = mysqli_real_escape_string($conn, $_POST['prodi']);
    $telepon  = mysqli_real_escape_string($conn, $_POST['telepon']);
    $judul    = mysqli_real_escape_string($conn, $_POST['judul_skripsi']);
    $p1       = !empty($_POST['pembimbing1']) ? $_POST['pembimbing1'] : 'NULL';
    $p2       = !empty($_POST['pembimbing2']) ? $_POST['pembimbing2'] : 'NULL';

    // --- 1. AMBIL NPM LAMA DARI TABLE DATA_MAHASISWA (SEBELUM UPDATE) ---
    // Ini vital untuk menyambungkan ke progres skripsi nanti
    $cek_npm_lama = mysqli_query($conn, "SELECT npm FROM data_mahasiswa WHERE id = '$id_mhs'");
    $row_lama = mysqli_fetch_assoc($cek_npm_lama);
    $npm_lama = $row_lama['npm'];

    // --- 2. UPDATE TABEL UTAMA ---
    
    // Update nama di mstr_akun (username juga disamakan dgn npm biar login tetep jalan)
    $update_akun = mysqli_query($conn, "UPDATE mstr_akun SET nama='$nama', username='$npm_baru' WHERE id='$id_mhs'");

    // Update data_mahasiswa (NPM BARU DISIMPAN DISINI)
    $update_biodata = mysqli_query($conn, "UPDATE data_mahasiswa SET 
                                            npm = '$npm_baru', 
                                            prodi = '$prodi', 
                                            telepon = '$telepon' 
                                           WHERE id = '$id_mhs'");

    // Update Skripsi
    $cek_skripsi = mysqli_query($conn, "SELECT id FROM skripsi WHERE id_mahasiswa = '$id_mhs'");
    if (mysqli_num_rows($cek_skripsi) > 0) {
        $update_skripsi = mysqli_query($conn, "UPDATE skripsi SET judul='$judul', pembimbing1=$p1, pembimbing2=$p2 WHERE id_mahasiswa='$id_mhs'");
    } else {
        if (!empty($judul)) {
            $update_skripsi = mysqli_query($conn, "INSERT INTO skripsi (id_mahasiswa, judul, pembimbing1, pembimbing2, tgl_pengajuan_judul, tema, skema) VALUES ('$id_mhs', '$judul', $p1, $p2, CURDATE(), 'Software Engineering', 'Reguler')");
        }
    }

    // --- 3. SINKRONISASI KE TABEL PROGRES_SKRIPSI ---
    // Logika: Ubah semua data di progres_skripsi yang punya npm lama menjadi npm baru
    if ($npm_lama != $npm_baru) {
        $cek_progres = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
        if (mysqli_num_rows($cek_progres) > 0) {
            // Query Update Relasi
            $sync_progres = mysqli_query($conn, "UPDATE progres_skripsi SET npm = '$npm_baru' WHERE npm = '$npm_lama'");
        }
    }

    // --- 4. FINISH ---
    if ($update_biodata) {
        echo "<script>alert('✅ Data Berhasil Diperbarui & Sinkronisasi NPM Sukses!'); window.location='data_mahasiswa.php';</script>";
    } else {
        echo "Gagal Update: " . mysqli_error($conn);
    }

} else {
    header("Location: data_mahasiswa.php");
}
?>