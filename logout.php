<?php
session_start();

// Default redirect
$redirect = 'mahasiswa/login_mahasiswa.php';

// Cek session siapa yang aktif
if (isset($_SESSION['admin_username'])) {
    $redirect = 'admin/login_admin.php';
} elseif (isset($_SESSION['nip'])) {
    $redirect = 'dosen/login_dosen.php';
} elseif (isset($_SESSION['npm'])) {
    $redirect = 'mahasiswa/login_mahasiswa.php';
}

session_unset();
session_destroy();

header("Location: $redirect");
exit();
