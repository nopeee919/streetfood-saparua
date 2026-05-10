<?php
// Parameter koneksi
$host = "localhost";
$user = "root";       // sesuaikan dengan user phpmyadmin
$pass = "";           // kosongkan jika pakai XAMPP default
$db   = "db_streetfood_saparua2";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
