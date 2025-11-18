<?php
session_start();
include "db.php";

$err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $npm = $_POST["npm"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $conn->prepare("SELECT * FROM akun_mahasiswa WHERE npm = ? AND password = ?");
    $stmt->bind_param("ss", $npm, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['npm'] = $user['npm'];
        $_SESSION['nama'] = $user['nama'];

        $cekBio = $conn->prepare("SELECT * FROM biodata_mahasiswa WHERE npm = ?");
        $cekBio->bind_param("s", $npm);
        $cekBio->execute();
        $bioResult = $cekBio->get_result();

        if ($bioResult->num_rows > 0) {
            header("Location: home_mahasiswa.php");
        } else {
            header("Location: biodata_mahasiswa.php");
        }
        exit();
    } else {
        $err = "NPM atau Password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Mahasiswa</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        .message { text-align: center; margin-bottom: 15px; }
        .message p { color: red; }
    </style>
</head>
<body>
    <div class="judul">
        SISTEM MONITORING SKRIPSI<br>UNIVERSITAS MUHAMMADIYAH MAGELANG
    </div>

    <form method="POST" class="login-box">
        <h3>Login Mahasiswa</h3>

        <?php if ($err): ?>
            <div class="message"><p><?= $err ?></p></div>
        <?php endif; ?>

        <div class="input-field">
            <input type="text" name="npm" placeholder="NPM" required />
        </div>
        <div class="input-field">
            <input type="password" name="password" placeholder="Password" required />
        </div>
        <div class="checkbox">
            <a href="lupa_password.php" class="forgot">Forgot Password?</a>
        </div>
        <button type="submit" class="login-button">Login</button>
        <div class="register-link">
            Belum punya akun? <a href="register_mahasiswa.php">Register</a>
        </div>
    </form>

    <script src="validate.js"></script>
</body>
</html>
