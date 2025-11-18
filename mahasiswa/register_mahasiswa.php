<?php
include "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $npm = trim($_POST["npm"] ?? '');
    $nama = trim($_POST["nama"] ?? '');
    $password = $_POST["password"] ?? '';

    if (empty($npm) || empty($nama) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } else {
        $cek = $conn->prepare("SELECT * FROM mahasiswa_skripsi WHERE npm = ? AND nama = ?");
        $cek->bind_param("ss", $npm, $nama);
        $cek->execute();
        $hasil = $cek->get_result();

        if ($hasil->num_rows == 0) {
            $error = "Data tidak ditemukan. Anda belum mengambil skripsi atau data salah.";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO akun_mahasiswa (npm, nama, password, status_skripsi) VALUES (?, ?, ?, 'sudah')");
                $stmt->bind_param("sss", $npm, $nama, $password);
                $stmt->execute();

                $success = "Aktivasi berhasil! Anda bisa langsung login.";
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    $error = "Akun dengan NPM ini sudah pernah diaktivasi.";
                } else {
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aktivasi Akun Mahasiswa</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        .message { text-align: center; margin-bottom: 15px; }
        .message p { color: yellow; }
        .message.success p { color: #00ff00; }
    </style>
</head>
<body>
    <div class="judul">
        SISTEM MONITORING SKRIPSI<br>UNIVERSITAS MUHAMMADIYAH MAGELANG
    </div>

    <form method="POST" class="login-box">
        <h3>Aktivasi Akun Mahasiswa</h3>

        <?php if ($error): ?>
            <div class="message"><p><?= $error ?></p></div>
        <?php elseif ($success): ?>
            <div class="message success"><p><?= $success ?></p></div>
        <?php endif; ?>

        <div class="input-field">
            <input type="text" name="npm" placeholder="NPM *1805040035*" required value="<?= htmlspecialchars($_POST['npm'] ?? '') ?>">
        </div>

        <div class="input-field">
            <input type="text" name="nama" placeholder="Nama *Ikhbal Khasodiq*" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
        </div>

        <div class="input-field">
            <input type="password" name="password" placeholder="Buat Password" required>
        </div>

        <button type="submit" class="login-button">Aktivasi</button>

        <div class="register-link">
            Sudah aktivasi? <a href="login_mahasiswa.php">Login</a>
        </div>
    </form>
</body>
</html>
