<?php
session_start();
include "../admin/db.php"; 

// Aktifkan Error Reporting biar kelihatan salahnya dimana
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>--- MODE DEBUGGING ---</h3>";

// 1. Cek Login
if (!isset($_SESSION['nip'])) {
    die("❌ Error: Sesi login habis. Silakan login ulang.");
}

// 2. Tangkap Input
$npm = $_POST['npm'] ?? '';
$pesan = $_POST['pesan'] ?? '';
$nip_dosen = $_SESSION['nip']; 

echo "NPM Tujuan: " . $npm . "<br>";
echo "Pengirim: " . $nip_dosen . "<br>";

// --- 3. CEK NOMOR HP (PERBAIKAN NAMA KOLOM) ---
// Coba ambil kolom 'telepon' (sesuai perbaikan biodata sebelumnya)
$query_mhs = "SELECT nama, telepon FROM data_mahasiswa WHERE npm = '$npm'";
$res_mhs = mysqli_query($conn, $query_mhs);
$data_mhs = mysqli_fetch_assoc($res_mhs);

if (!$data_mhs) {
    die("❌ Error Database: Data mahasiswa dengan NPM $npm tidak ditemukan di tabel data_mahasiswa.");
}

// Cek apakah nomor HP ada?
// Kita tampung ke variabel $no_hp, entah nama kolomnya 'telepon' atau 'no_hp'
$no_hp = $data_mhs['telepon'] ?? $data_mhs['no_hp'] ?? '';

echo "Nama Mahasiswa: " . $data_mhs['nama'] . "<br>";
echo "Nomor HP dari DB: <strong>" . ($no_hp ? $no_hp : "KOSONG") . "</strong><br>";

if (empty($no_hp)) {
    die("❌ GAGAL: Mahasiswa ini belum mengisi Nomor HP di biodata. Tidak bisa kirim WA.");
}

// 4. Format Nomor HP (08 -> 628)
$no_hp = preg_replace('/[^0-9]/', '', $no_hp);
if (substr($no_hp, 0, 1) === "0") {
    $no_hp = "62" . substr($no_hp, 1);
}
echo "Nomor HP Terformat: $no_hp <br><hr>";

// 5. Ambil Nama Dosen
$q_dosen = mysqli_query($conn, "SELECT nama FROM mstr_akun WHERE username = '$nip_dosen'");
$d_dosen = mysqli_fetch_assoc($q_dosen);
$nama_dosen = $d_dosen['nama'] ?? 'Dosen Pembimbing';

// 6. Kirim ke Fonnte
$pesan_wa = "*📢 PESAN DARI DOSEN PEMBIMBING*\n\n"
          . "🧑‍🏫 *Dari:* $nama_dosen\n"
          . "✉️ *Pesan:*\n"
          . "$pesan";

$token = "T9zUnaLpo54RVoFL8C3j"; // Pastikan token ini benar

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.fonnte.com/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30, // Tambah timeout
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => array(
        'target' => $no_hp,
        'message' => $pesan_wa,
        'countryCode' => '62', 
    ),
    CURLOPT_HTTPHEADER => array(
        "Authorization: $token"
    ),
    // BYPASS SSL (Wajib di Localhost)
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
));

echo "Sedang mengirim ke Fonnte...<br>";
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "<h3>❌ CURL Error:</h3>" . $err;
} else {
    echo "<h3>✅ Respon dari Fonnte:</h3>";
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
    
    $res = json_decode($response, true);
    if (isset($res['status']) && $res['status'] == true) {
        echo "<h2 style='color:green'>SUKSES TERKIRIM!</h2>";
        echo "<a href='home_dosen.php'>Kembali ke Dashboard</a>";
    } else {
        echo "<h2 style='color:red'>GAGAL TERKIRIM!</h2>";
        echo "Cek alasan di atas (misal: device disconnected, invalid token, atau nomor salah).";
    }
}
?>