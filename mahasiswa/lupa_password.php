<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lupa Password</title>
  <link rel="stylesheet" href="style1.css">
  <style>
    .message { text-align: center; margin-bottom: 15px; }
    .message p { color: red; }
    .message.success p { color: green; }
  </style>
</head>
<body>

  <div class="judul">
    SISTEM MONITORING SKRIPSI<br>UNIVERSITAS MUHAMMADIYAH MAGELANG
  </div>

  <form method="POST" class="login-box">
    <h3>Lupa Password</h3>

    <?php
    include "db.php";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $npm = $_POST["npm"] ?? '';
        if (!empty($npm)) {
            $result = $conn->prepare("SELECT password FROM akun_mahasiswa WHERE npm = ?");
            $result->bind_param("s", $npm);
            $result->execute();
            $hasil = $result->get_result();

            if ($hasil->num_rows > 0) {
                $row = $hasil->fetch_assoc();
                echo "<div class='message success'><p>Password Anda: <strong>" . htmlspecialchars($row['password']) . "</strong></p></div>";
            } else {
                echo "<div class='message'><p>NPM tidak ditemukan.</p></div>";
            }
        }
    }
    ?>

    <div class="input-field">
      <input type="text" name="npm" placeholder="Masukkan NPM Anda" required />
    </div>

    <button type="submit" class="login-button">Lihat Password</button>

    <div class="register-link">
      Kembali ke <a href="login_mahasiswa.php">Login</a>
    </div>
  </form>

</body>
</html>
