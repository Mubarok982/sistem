<?php
session_start();
include "db.php";

$err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nip = $_POST["nip"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $conn->prepare("SELECT * FROM akun_dosen WHERE nip = ? AND password = ?");
    $stmt->bind_param("ss", $nip, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['nip'] = $user['nip'];
        header("Location: home_dosen.php");
        exit();
    } else {
        $err = "NIP atau Password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Dosen</title>
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
    <h3>Login Dosen</h3>

    <?php if ($err): ?>
      <div class="message"><p><?php echo $err; ?></p></div>
    <?php endif; ?>

    <div class="input-field">
      <input type="text" name="nip" placeholder="NIP" required />
    </div>
    <div class="input-field">
      <input type="password" name="password" placeholder="Password" required />
    </div>
    <button type="submit" class="login-button">Login</button>
  </form>

  <script src="validate.js"></script>
</body>
</html>
