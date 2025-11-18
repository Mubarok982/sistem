<?php
session_start(); 
include "db.php";

$npm = $_POST['npm'] ?? '';
$pesan = $_POST['pesan'] ?? '';
$nip_dosen = $_POST['nip'] ?? ($_SESSION['nip'] ?? '');

if (!$npm || !$pesan || !$nip_dosen) {
    echo "<script>
        alert('❌ Data tidak lengkap.');
        window.location.href = 'home_dosen.php';
    </script>";
    exit;
}

$query_dosen = mysqli_query($conn, "SELECT nama FROM biodata_dosen WHERE nip = '$nip_dosen'");
$dosen = mysqli_fetch_assoc($query_dosen);
$nama_dosen = $dosen['nama'] ?? 'Dosen';

$query_mhs = mysqli_query($conn, "SELECT no_hp FROM biodata_mahasiswa WHERE npm = '$npm'");
$mhs = mysqli_fetch_assoc($query_mhs);
$no_hp = $mhs['no_hp'] ?? '';

if (!$no_hp) {
    echo "<script>
        alert('❌ Nomor HP mahasiswa tidak ditemukan.');
        window.location.href = 'home_dosen.php';
    </script>";
    exit;
}

if (substr($no_hp, 0, 1) === "0") {
    $no_hp = "62" . substr($no_hp, 1);
}

$pesan_wa = "
📢 *Notifikasi Chat Dari Dosen*

🧑‍🏫 Nama: $nama_dosen

✉️ Pesan:
$pesan
";

$token = "T9zUnaLpo54RVoFL8C3j"; 
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.fonnte.com/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => array(
        'target' => $no_hp,
        'message' => $pesan_wa,
        'countryCode' => '62',
    ),
    CURLOPT_HTTPHEADER => array(
        "Authorization: $token"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "<script>
        alert('❌ Gagal mengirim pesan: $err');
        window.location.href = 'home_dosen.php';
    </script>";
} else {
    echo "<script>
        alert('✅ Pesan berhasil dikirim ke mahasiswa.');
        window.location.href = 'home_dosen.php';
    </script>";
}
?>
