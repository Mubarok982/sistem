<?php
$conn = new mysqli("localhost", "root", "", "bimbingan");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
