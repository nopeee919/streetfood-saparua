<?php
include 'koneksi.php';

header('Content-Type: application/json');

if (isset($_POST['q'])) {
  $q = trim($_POST['q']);
} else {
  $q = '';
}
if (strlen($q) < 2) {
  echo json_encode([]);
  exit;
}

$q = mysqli_real_escape_string($koneksi, $q);
$keyword = '%' . $q . '%';
$results = [];

$sql_umkm = "SELECT u.id_umkm, u.nama_stand, k.jenis_kategori
            FROM UMKM u
            LEFT JOIN KATEGORI k ON u.id_kategori = k.id_kategori
            WHERE u.nama_stand LIKE '$keyword'
            LIMIT 4";
$result_umkm = mysqli_query($koneksi, $sql_umkm);
while ($row = mysqli_fetch_assoc($result_umkm)) {
  $results[] = [
    'type'  => 'umkm',
    'id'    => $row['id_umkm'],
    'label' => $row['nama_stand'],
    'sub'   => $row['jenis_kategori'] ?? 'UMKM',
    'url'   => 'umkm_detail.php?id=' . $row['id_umkm']
  ];
}

$sql_menu = "SELECT m.id_menu, m.nama_menu, m.harga_menu, u.nama_stand
            FROM MENU m
            JOIN UMKM u ON m.id_umkm = u.id_umkm
            WHERE m.nama_menu LIKE '$keyword'
            LIMIT 5";
$result_menu = mysqli_query($koneksi, $sql_menu);
while ($row = mysqli_fetch_assoc($result_menu)) {
  $results[] = [
    'type'  => 'menu',
    'id'    => $row['id_menu'],
    'label' => $row['nama_menu'],
    'sub'   => $row['nama_stand'] . ' · Rp' . number_format($row['harga_menu'], 0, ',', '.'),
    'url'   => 'menu_detail.php?id=' . $row['id_menu']
  ];
}

echo json_encode($results);