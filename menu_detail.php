<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
  $id_menu = (int)$_GET['id'];
} else {
  $id_menu = 0;
}

if ($id_menu == 0) {
  header('Location: index.php');
  exit;
}

$sql = "SELECT m.*, u.id_umkm, u.nama_stand, u.nama_pemilik, u.foto AS foto_umkm, u.status_halal, k.jenis_kategori
        FROM MENU m
        JOIN UMKM u ON m.id_umkm = u.id_umkm
        LEFT JOIN KATEGORI k ON u.id_kategori = k.id_kategori
        WHERE m.id_menu = $id_menu";
$result = mysqli_query($koneksi, $sql);
$menu = mysqli_fetch_assoc($result);
if (!$menu) {
  header('Location: index.php');
  exit;
}

$sql_rasa = "SELECT kr.nama_rasa
            FROM MENU_RASA mr
            JOIN KATEGORI_RASA kr ON mr.id_rasa = kr.id_rasa
            WHERE mr.id_menu = $id_menu";
$result_rasa = mysqli_query($koneksi, $sql_rasa);
$rasa_list = [];
while ($row = mysqli_fetch_assoc($result_rasa)) {
  $rasa_list[] = $row['nama_rasa'];
}

$sql_ekstra_menu = "SELECT * FROM EKSTRA_MENU
                    WHERE id_menu = $id_menu
                    ORDER BY harga_ekstra ASC";
$result_ekstra_menu = mysqli_query($koneksi, $sql_ekstra_menu);
$ekstra_menu = [];
while ($row = mysqli_fetch_assoc($result_ekstra_menu)) {
  $ekstra_menu[] = $row;
}

$id_umkm = $menu['id_umkm'];
$sql_ekstra_umkm = "SELECT * FROM EKSTRA_UMKM
                    WHERE id_umkm = $id_umkm
                    ORDER BY harga_ekstra ASC";
$result_ekstra_umkm = mysqli_query($koneksi, $sql_ekstra_umkm);
$ekstra_umkm = [];
while ($row = mysqli_fetch_assoc($result_ekstra_umkm)) {
  $ekstra_umkm[] = $row;
}

$sql_other = "SELECT m.id_menu, m.nama_menu, m.harga_menu, m.satuan, m.foto_menu,
                GROUP_CONCAT(kr.nama_rasa SEPARATOR ', ') AS rasa_list
              FROM MENU m
              LEFT JOIN MENU_RASA mr ON m.id_menu = mr.id_menu
              LEFT JOIN KATEGORI_RASA kr ON mr.id_rasa = kr.id_rasa
              WHERE m.id_umkm = $id_umkm
              AND m.id_menu != $id_menu
              GROUP BY m.id_menu
              ORDER BY m.harga_menu
              LIMIT 6";
$result_other = mysqli_query($koneksi, $sql_other);
$other_menus = [];
while ($row = mysqli_fetch_assoc($result_other)) {
  $other_menus[] = $row;
}

function halalBadge($status) {
  if ($status == 'Sertifikasi Halal') {
    return '<span class="halal-badge halal-green">✅ Sertifikasi Halal</span>';
  } elseif ($status == 'Halal Belum Sertifikasi') {
    return '<span class="halal-badge halal-yellow">⚠️ Halal (Belum Sertifikasi)</span>';
  } elseif ($status == 'Non-Halal') {
    return '<span class="halal-badge halal-red">🚫 Non-Halal</span>';
  } else {
    return '<span class="halal-badge halal-gray">'.$status.'</span>';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $menu['nama_menu'] ?> — StreetFood Saparua</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
  <body class="detail-page">
    <header class="site-header">
      <div class="header-inner">
        <a href="index.php" class="logo">
          <span class="logo-icon">🍜</span>
          <span class="logo-text">StreetFood<em>Saparua</em></span>
        </a>
        <div class="search-wrapper">
          <form class="search-bar" id="searchForm" action="search.php" method="GET">
            <input type="text" name="q" id="searchInput" placeholder="Cari nama UMKM atau menu..." autocomplete="off">
            <button type="submit">
              <span>🔍</span>
            </button>
          </form>
          <div id="searchDropdown" class="search-dropdown"></div>
        </div>
      </div>
    </header>

    <div class="breadcrumb-bar">
      <div class="breadcrumb-inner">
        <a href="index.php">Beranda</a>
        <span>></span>
        <a href="umkm_detail.php?id=<?= $menu['id_umkm'] ?>"><?= $menu['nama_stand'] ?></a>
        <span>></span>
        <span><?= ($menu['nama_menu']) ?></span>
      </div>
    </div>

    <div class="menu-detail-hero">
      <div class="menu-detail-inner">

        <div class="menu-detail-img">
          <?php if (!empty($menu['foto_menu']) && file_exists("images/" . $menu['foto_menu'])): ?>
            <img src="images/<?= $menu['foto_menu'] ?>" alt="<?= $menu['nama_menu'] ?>">
          <?php else: ?>
            <div class="menu-detail-img-ph">🍴</div>
          <?php endif; ?>
        </div>

        <div class="menu-detail-info">

          <a href="umkm_detail.php?id=<?= $menu['id_umkm'] ?>" class="menu-from-umkm">
            <?php if (!empty($menu['foto_umkm']) && file_exists("images/" . $menu['foto_umkm'])): ?>
              <img src="images/<?= $menu['foto_umkm'] ?>" alt="logo" class="umkm-mini-logo">
            <?php endif; ?>
            <span><?= $menu['nama_stand'] ?></span>
            <span class="umkm-mini-arrow">↗</span>
          </a>

          <div class="menu-detail-tags">
            <?php if ($menu['jenis_kategori']): ?>
              <span class="detail-tag-kategori"><?= $menu['jenis_kategori'] ?></span>
            <?php endif; ?>
            <?= halalBadge($menu['status_halal']) ?>
          </div>

          <h1 class="menu-detail-title"><?= $menu['nama_menu'] ?></h1>

          <div class="menu-detail-price">
            <span class="price-main">Rp<?= number_format($menu['harga_menu'],0,',','.') ?></span>
            <?php if ($menu['satuan']): ?>
              <span class="price-unit">/ <?= $menu['satuan'] ?></span>
            <?php endif; ?>
          </div>

          <?php if (!empty($rasa_list)): ?>
          <div class="menu-detail-rasa">
            <span class="detail-sub-label">Kategori Rasa:</span>
            <?php foreach ($rasa_list as $r): ?>
              <span class="rasa-chip"><?= $r ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <a href="umkm_detail.php?id=<?= $menu['id_umkm'] ?>" class="btn-visit-umkm">
            Lihat Profil UMKM Lengkap →
          </a>
        </div>
      </div>
    </div>

    <?php $ada_ekstra = !empty($ekstra_menu) || !empty($ekstra_umkm); ?>

    <?php if ($ada_ekstra): ?>
    <div class="ekstra-section-wrap">
      <div class="ekstra-section-inner">
        <h2 class="ekstra-section-title">
          <span>✚</span> Tambahan & Ekstra
        </h2>
        <p class="ekstra-section-sub">Pilih ekstra yang tersedia untuk menu ini</p>

        <div class="ekstra-cols">

          <!-- Ekstra Khusus Menu -->
          <?php if (!empty($ekstra_menu)): ?>
          <div class="ekstra-col">
            <div class="ekstra-card">
              <div class="ekstra-card-header ekstra-header-menu">
                <span class="ekstra-icon">🍴</span>
                <div>
                  <h3>Ekstra Khusus Menu Ini</h3>
                  <p>Tambahan yang hanya berlaku untuk <strong><?= $menu['nama_menu'] ?></strong></p>
                </div>
              </div>
              <div class="ekstra-list">
                <?php foreach ($ekstra_menu as $e): ?>
                <div class="ekstra-row">
                  <div class="ekstra-row-info">
                    <span class="ekstra-nama"><?= $e['nama_ekstra'] ?></span>
                    <?php if ($e['keterangan']): ?>
                      <span class="ekstra-ket"><?= $e['keterangan'] ?></span>
                    <?php endif; ?>
                  </div>
                  <span class="ekstra-harga">
                    <?php if ($e['harga_ekstra'] > 0) { ?>
                      +Rp<?= number_format($e['harga_ekstra'], 0, ',', '.') ?>
                    <?php } else { ?>
                      <span class="free-tag">GRATIS</span>
                    <?php } ?>
                  </span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <!-- Ekstra Semua Menu (UMKM) -->
          <?php if (!empty($ekstra_umkm)): ?>
          <div class="ekstra-col">
            <div class="ekstra-card">
              <div class="ekstra-card-header ekstra-header-umkm">
                <span class="ekstra-icon">🏪</span>
                <div>
                  <h3>Ekstra dari <?= $menu['nama_stand'] ?></h3>
                  <p>Tambahan yang berlaku untuk semua menu di UMKM ini</p>
                </div>
              </div>
              <div class="ekstra-list">
                <?php foreach ($ekstra_umkm as $e): ?>
                <div class="ekstra-row">
                  <div class="ekstra-row-info">
                    <span class="ekstra-nama"><?= $e['nama_ekstra'] ?></span>
                    <?php if ($e['keterangan']): ?>
                      <span class="ekstra-ket"><?= $e['keterangan'] ?></span>
                    <?php endif; ?>
                  </div>
                  <span class="ekstra-harga">
                    <?php if ($e['harga_ekstra'] > 0) { ?>
                      +Rp<?= number_format($e['harga_ekstra'], 0, ',', '.') ?>
                    <?php } else { ?>
                      <span class="free-tag">GRATIS</span>
                    <?php } ?>
                  </span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($other_menus)): ?>
    <div class="other-menu-section">
      <div class="other-menu-inner">
        <h2 class="menu-section-title">
          Menu Lain dari <?= $menu['nama_stand'] ?>
        </h2>
        <div class="menu-grid">
          <?php foreach ($other_menus as $mn): ?>
          <div class="menu-card" onclick="window.location='menu_detail.php?id=<?= $mn['id_menu'] ?>'">
            <div class="menu-card-img">
              <?php if (!empty($mn['foto_menu']) && file_exists("images/" . $mn['foto_menu'])): ?>
                <img src="images/<?= $mn['foto_menu'] ?>" alt="<?= $mn['nama_menu'] ?>">
              <?php else: ?>
                <div class="menu-img-ph-big">🍴</div>
              <?php endif; ?>
            </div>
            <div class="menu-card-body">
              <h4 class="menu-card-name"><?= $mn['nama_menu'] ?></h4>
              <?php if ($mn['rasa_list']): ?>
              <p class="menu-card-rasa">
                <?php foreach (explode(', ', $mn['rasa_list']) as $r): ?>
                  <span class="rasa-chip"><?= trim($r) ?></span>
                <?php endforeach; ?>
              </p>
              <?php endif; ?>
              <p class="menu-card-price">
                Rp<?= number_format($mn['harga_menu'], 0, ',', '.') ?>
                <?php if (!empty($mn['satuan'])) { ?>
                  <span class="menu-satuan">
                    / <?= $mn['satuan'] ?>
                  </span>
                <?php } ?>
              </p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="back-bar-menu">
      <a href="umkm_detail.php?id=<?= $menu['id_umkm'] ?>" class="btn-back">← Kembali ke <?= $menu['nama_stand'] ?></a>
    </div>

    <footer class="site-footer">
      <p>© 2026 StreetFood Saparua — Sistem Manajemen Basis Data</p>
    </footer>

    <script src="app.js"></script>
  </body>
</html>