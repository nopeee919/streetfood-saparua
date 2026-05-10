<?php
include 'koneksi.php';

if (isset($_GET['q'])) {
  $q = trim($_GET['q']);
} else {
  $q = '';
}
if ($q == '') {
  header('Location: index.php');
  exit;
}

$keyword = '%' . $q . '%';
$umkm_combined = [];
$umkm_ids = [];
$umkm_by_name_ids = [];

$sql1 = "SELECT u.id_umkm, u.nama_stand, u.nama_pemilik, u.foto, u.status_halal, k.jenis_kategori
        FROM UMKM u
        LEFT JOIN KATEGORI k ON u.id_kategori = k.id_kategori
        WHERE u.nama_stand LIKE '$keyword'
        ORDER BY u.nama_stand";
$result1 = mysqli_query($koneksi, $sql1);
while ($row = mysqli_fetch_assoc($result1)) {
  $umkm_combined[] = $row;
  $umkm_by_name_ids[] = $row['id_umkm'];
  $umkm_ids[] = $row['id_umkm'];
}

$sql2 = "SELECT DISTINCT u.id_umkm, u.nama_stand, u.nama_pemilik, u.foto, u.status_halal, k.jenis_kategori
        FROM UMKM u
        LEFT JOIN KATEGORI k ON u.id_kategori = k.id_kategori
        INNER JOIN MENU m ON u.id_umkm = m.id_umkm
        WHERE m.nama_menu LIKE '$keyword'
        ORDER BY u.nama_stand";
$result2 = mysqli_query($koneksi, $sql2);
while ($row = mysqli_fetch_assoc($result2)) {
  if (!in_array($row['id_umkm'], $umkm_ids)) {
    $umkm_combined[] = $row;
    $umkm_ids[] = $row['id_umkm'];
  }
}

function getMatchingMenus($koneksi, $umkm_id, $keyword){
  $menu = [];
  $sql = "SELECT id_menu, nama_menu, harga_menu, satuan, foto_menu
          FROM MENU
          WHERE id_umkm = $umkm_id
          AND nama_menu LIKE '$keyword'";
  $result = mysqli_query($koneksi, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $menu[] = $row;
  }
  return $menu;
}

$total = count($umkm_combined);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hasil: "<?= htmlspecialchars($q) ?>" — StreetFood Saparua</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <a href="index.php" class="logo">
        <span class="logo-icon">🍜</span>
        <span class="logo-text">StreetFood<em>Saparua</em></span>
      </a>
      <div class="search-wrapper">
        <form class="search-bar" id="searchForm" action="search.php" method="GET">
          <input type="text" name="q" id="searchInput" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama UMKM atau menu..." autocomplete="off">
          <button type="submit">
            <span>🔍</span>
          </button>
        </form>
        <div id="searchDropdown" class="search-dropdown"></div>
      </div>
    </div>
  </header>

  <div class="search-result-bar">
    <div class="search-result-inner">
      <div class="search-meta">
        <span class="search-keyword">Hasil: "<strong><?= htmlspecialchars($q) ?></strong>"</span>
        <span class="search-count"><?= $total ?> UMKM ditemukan</span>
      </div>
      <a href="index.php" class="link-back">← Kembali ke Beranda</a>
    </div>
  </div>

  <div class="search-layout">
    <?php if (empty($umkm_combined)): ?>
      <div class="empty-state solo">
        <span>😶</span>
        <p>Tidak ada UMKM atau menu yang cocok dengan "<strong><?= htmlspecialchars($q) ?></strong>"</p>
        <a href="index.php">← Kembali ke beranda</a>
      </div>
    <?php else: ?>
      <?php foreach ($umkm_combined as $u): ?>
        <?php
          if (!in_array($u['id_umkm'], $umkm_by_name_ids)) {
            $matching_menus = getMatchingMenus($koneksi, $u['id_umkm'], $keyword);
          } else {
            $matching_menus = [];
          }
        ?>
        <div class="search-result-item">
          <div class="search-umkm-card" onclick="window.location='umkm_detail.php?id=<?= $u['id_umkm'] ?>'">
            <div class="search-card-img">
              <?php if (!empty($u['foto']) && file_exists("images/" . $u['foto'])): ?>
                <img src="images/<?= htmlspecialchars($u['foto']) ?>" alt="<?= htmlspecialchars($u['nama_stand']) ?>">
              <?php else: ?>
                <div class="search-img-placeholder">🍽️</div>
              <?php endif; ?>
            </div>
            <div class="search-card-info">
              <div class="search-card-top">
                <h3><?= htmlspecialchars($u['nama_stand']) ?></h3>
                <?php if (in_array($u['id_umkm'],$umkm_by_name_ids)): ?>
                  <span class="match-badge match-name">nama cocok</span>
                <?php else: ?>
                  <span class="match-badge match-menu">via menu</span>
                <?php endif; ?>
              </div>
              <p class="search-owner">
                <span>👤</span>
                <?= htmlspecialchars($u['nama_pemilik']) ?>
              </p>
              <?php if ($u['jenis_kategori']): ?>
                <span class="search-kategori"><?= htmlspecialchars($u['jenis_kategori']) ?></span>
              <?php endif; ?>
              <a href="umkm_detail.php?id=<?= $u['id_umkm'] ?>" class="search-detail-btn" onclick="event.stopPropagation()">Lihat Detail →</a>
            </div>
          </div>
          <?php if (!empty($matching_menus)): ?>
            <div class="search-menu-results">
              <p class="menu-match-label">Menu yang cocok dengan "<?= htmlspecialchars($q) ?>":</p>
              <div class="search-menu-grid">
                <?php foreach ($matching_menus as $mn): ?>
                  <a href="menu_detail.php?id=<?= $mn['id_menu'] ?>" class="search-menu-item">
                    <?php if (!empty($mn['foto_menu']) && file_exists("images/" . $mn['foto_menu'])): ?>
                      <img src="images/<?= htmlspecialchars($mn['foto_menu']) ?>" alt="<?= htmlspecialchars($mn['nama_menu']) ?>">
                    <?php else: ?>
                      <div class="menu-img-ph">🍴</div>
                    <?php endif; ?>
                    <div>
                      <span class="search-menu-name"><?= htmlspecialchars($mn['nama_menu']) ?></span>
                      <span class="search-menu-price">Rp<?= number_format($mn['harga_menu'],0,',','.') ?><?= $mn['satuan']?'/'.$mn['satuan']:'' ?></span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <footer class="site-footer">
    <p>© 2026 StreetFood Saparua — Sistem Manajemen Basis Data</p>
  </footer>

  <script src="appSearch.js"></script>
</body>
</html>