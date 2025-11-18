<?php
$conn = new mysqli("localhost", "root", "", "sistem_lab");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
