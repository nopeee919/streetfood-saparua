<?php
// Parameter koneksi
$host = "localhost";
$user = "root";       // sesuaikan dengan user phpmyadmin
$pass = "";           // kosongkan jika pakai XAMPP default
$db   = "db_streetfood_saparuafinal";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// ── Urutan hari ──────────────────────────────────────────────────
$HARI_ORDER = array(
  'Senin' => 1,
  'Selasa' => 2,
  'Rabu' => 3,
  'Kamis' => 4,
  'Jumat' => 5,
  'Sabtu' => 6,
  'Minggu' => 7
);

$HARI_ID_EN = array(
  'Senin' => 'Monday',
  'Selasa' => 'Tuesday',
  'Rabu' => 'Wednesday',
  'Kamis' => 'Thursday',
  'Jumat' => 'Friday',
  'Sabtu' => 'Saturday',
  'Minggu' => 'Sunday'
);

function hariIniID() {
  $hari_inggris = date('l');
  if ($hari_inggris == 'Monday') {
    return 'Senin';
  } elseif ($hari_inggris == 'Tuesday') {
    return 'Selasa';
  } elseif ($hari_inggris == 'Wednesday') {
    return 'Rabu';
  } elseif ($hari_inggris == 'Thursday') {
    return 'Kamis';
  } elseif ($hari_inggris == 'Friday') {
    return 'Jumat';
  } elseif ($hari_inggris == 'Saturday') {
    return 'Sabtu';
  } elseif ($hari_inggris == 'Sunday') {
    return 'Minggu';
  } else {
    return $hari_inggris;
  }
}

?>