<?php
session_start();
include "../admin/db.php"; 

// Cek File Konfigurasi WA
if (file_exists("../config_fonnte.php")) {
    include "../config_fonnte.php";
    include "../kirim_fonnte.php";
} else {
    function kirimWaFonnte($no, $msg) { return true; } 
}

// 1. Cek Login
if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}

$session_username = $_SESSION['npm']; 
$bab = $_POST['bab'] ?? '';
$fileField = 'file_bab' . $bab;
$file = $_FILES[$fileField] ?? null;

// 2. AMBIL DATA IDENTITAS (Nama & NPM Real)
// Mengambil NPM dari tabel data_mahasiswa
$query_ident = "SELECT 
                  m.nama, 
                  dm.npm AS npm_real 
                FROM mstr_akun m 
                JOIN data_mahasiswa dm ON m.id = dm.id 
                WHERE m.username = '$session_username'";

$res_ident  = mysqli_query($conn, $query_ident);
$data_ident = mysqli_fetch_assoc($res_ident);

// Validasi jika data biodata belum ada
if (!$data_ident || empty($data_ident['npm_real'])) {
    echo "<script>alert('Biodata NPM belum ditemukan! Silakan update profil Anda.'); window.location='update_biodata_mahasiswa.php';</script>";
    exit();
}

$nama_mhs = $data_ident['nama'];     
$npm_real = $data_ident['npm_real']; 

// 3. Validasi Input
if (empty($bab) || empty($file) || $file['error'] != 0) {
    echo "<script>alert('Data tidak lengkap atau file error.'); window.history.back();</script>";
    exit();
}

// 4. Validasi File
$namaFileAsli = basename($file['name']);
$ekstensi = strtolower(pathinfo($namaFileAsli, PATHINFO_EXTENSION));

if ($ekstensi != 'pdf') {
    echo "<script>alert('Format salah! Hanya PDF.'); window.history.back();</script>";
    exit();
}

if ($file['size'] > 5 * 1024 * 1024) { 
    echo "<script>alert('File terlalu besar (Max 5MB)'); window.history.back();</script>";
    exit();
}

// 5. Proses Upload
$targetDir = "uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Rename File
$nama_clean = str_replace(' ', '_', $nama_mhs);
$namaBaru   = "Progres_" . $nama_clean . "_" . $npm_real . "_BAB" . $bab . "_" . time() . "." . $ekstensi;
$targetFile = $targetDir . $namaBaru;

if (move_uploaded_file($file["tmp_name"], $targetFile)) {

    // 6. Insert ke Database (Tabel progres_skripsi punya kolom npm, jadi aman)
    $stmt = $conn->prepare("INSERT INTO progres_skripsi (npm, bab, file, created_at, nilai_dosen1, nilai_dosen2, progres_dosen1, progres_dosen2) VALUES (?, ?, ?, NOW(), NULL, NULL, 0, 0)");
    
    $stmt->bind_param("sis", $npm_real, $bab, $namaBaru);
    
    if ($stmt->execute()) {
        
        // --- NOTIFIKASI WA (QUERY FIX) ---
        
        // Kita cari data skripsi dengan menjoinkan data_mahasiswa
        // Karena tabel skripsi tidak punya npm, tapi punya id_mahasiswa
        $q_info = "SELECT 
                    s.judul, 
                    s.pembimbing1, 
                    s.pembimbing2 
                   FROM skripsi s 
                   JOIN data_mahasiswa dm ON s.id_mahasiswa = dm.id
                   WHERE dm.npm = '$npm_real'"; // Cari berdasarkan NPM di tabel data_mahasiswa
        
        $res_info = mysqli_query($conn, $q_info);

        if ($res_info && mysqli_num_rows($res_info) > 0) {
            $info = mysqli_fetch_assoc($res_info);
            $judul = $info['judul'];
            $id_d1 = $info['pembimbing1'];
            $id_d2 = $info['pembimbing2'];
            
            $pesan = "*Notifikasi Progres Skripsi UNIMMA*\n"
                   . "👨‍🎓 Nama: $nama_mhs\n"
                   . "🆔 NPM: $npm_real\n"
                   . "📚 Judul: $judul\n"
                   . "📄 BAB: $bab\n"
                   . "✅ Status: Mahasiswa telah mengunggah file skripsi baru.";

            // Fungsi Helper Cari HP Dosen
            // Cari di data_dosen dulu, kalau tidak ada coba mstr_akun (sesuaikan database Anda)
            // Asumsi: di tabel data_dosen belum ada kolom no_hp, jadi fitur ini skip dulu agar tidak error
            /*
            function getHpDosen($conn, $id) {
                // $q = "SELECT no_hp FROM data_dosen WHERE id='$id'";
                // $r = mysqli_query($conn, $q);
                // if($d = mysqli_fetch_assoc($r)) return $d['no_hp'];
                return null;
            }
            
            $hp1 = getHpDosen($conn, $id_d1);
            if($hp1) { kirimWaFonnte($hp1, $pesan); sleep(1); }

            $hp2 = getHpDosen($conn, $id_d2);
            if($hp2) { kirimWaFonnte($hp2, $pesan); }
            */
        }

        header("Location: progres_skripsi.php?upload=success");
        exit();
        
    } else {
        echo "Gagal menyimpan data: " . $stmt->error;
    }

} else {
    echo "Gagal upload file fisik.";
}
?>