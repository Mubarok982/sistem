<?php
session_start();
include "db.php"; 


$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';


$query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username' LIMIT 1");
$data = mysqli_fetch_assoc($query);

if ($data && $password === $data['password']) { 
    $_SESSION['admin_username'] = $data['username'];
    $_SESSION['nama_admin'] = $data['nama_admin']; 
    header("Location: home_admin.php");
    exit();
} else {
    echo "<script>alert('Username atau password salah'); location.href='login_admin.php';</script>";
    exit();
}
?>
