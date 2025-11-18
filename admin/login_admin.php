<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link rel="stylesheet" href="style1.css">
</head>
<body>

<div class="judul">
  <h2>LOGIN ADMIN</h2>
</div>

<div class="login-box">
  <h2>Silakan Login</h2>

  <?php if (isset($_SESSION['error'])): ?>
    <div style="background: rgba(255,0,0,0.5); padding: 10px; border-radius: 8px; margin-bottom: 15px;">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <form action="proses_login_admin.php" method="POST">
    <div class="input-field">
      <input type="text" name="username" placeholder="Username" required>
    </div>
    <div class="input-field">
      <input type="password" name="password" placeholder="Password" required>
    </div>
    <button type="submit" class="login-button">Login</button>
  </form>

  <div class="register-link">
    Kembali ke <a href="index.php">Beranda</a>
  </div>
</div>

</body>
</html>
