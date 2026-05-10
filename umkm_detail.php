<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
} else {
  $id = 0;
}
if ($id == 0) {
  header("Location: index.php");
  exit;
}

$sql_umkm = "SELECT u.*, k.jenis_kategori, l.deskripsi AS alamat, l.koordinat
            FROM UMKM u
            LEFT JOIN KATEGORI k ON u.id_kategori = k.id_kategori
            LEFT JOIN LOKASI l ON u.id_lokasi = l.id_lokasi
            WHERE u.id_umkm = $id";
$result = mysqli_query($koneksi, $sql_umkm);
$umkm = mysqli_fetch_assoc($result);
if (!$umkm) {
  header("Location: index.php");
  exit;
}

$jadwal_list = [];
$sql = "SELECT jam_buka, jam_tutup FROM JADWAL WHERE id_umkm = $id";
$result = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $jadwal_list[] = $row;
}

$bayar_list = [];
$sql = "SELECT mp.metode_pembayaran
        FROM UMKM_PEMBAYARAN up
        JOIN METODE_PEMBAYARAN mp ON up.id_metode = mp.id_metode
        WHERE up.id_umkm = $id";
$result = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $bayar_list[] = $row;
}

$mitra_list = [];
$sql = "SELECT mt.nama_mitra, mt.jenis_mitra, um.link_mitra
        FROM UMKM_MITRA um
        JOIN MITRA mt ON um.id_mitra = mt.id_mitra
        WHERE um.id_umkm = $id";
$result = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $mitra_list[] = $row;
}

$foto_booth = [];
$sql = "SELECT * 
        FROM FOTO_BOOTH 
        WHERE id_umkm = $id
        ORDER BY urutan ASC, id_foto ASC";
$result = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $foto_booth[] = $row;
}

$menu_list = [];
$sql = "SELECT m.id_menu, m.nama_menu, m.harga_menu, m.satuan, m.foto_menu,
          GROUP_CONCAT(kr.nama_rasa SEPARATOR ', ') AS rasa_list,
          (SELECT COUNT(*) FROM EKSTRA_MENU em WHERE em.id_menu = m.id_menu) AS jumlah_ekstra_menu
        FROM MENU m
        LEFT JOIN MENU_RASA mr ON m.id_menu = mr.id_menu
        LEFT JOIN KATEGORI_RASA kr ON mr.id_rasa = kr.id_rasa
        WHERE m.id_umkm = $id
        GROUP BY m.id_menu
        ORDER BY m.harga_menu";
$result = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $menu_list[] = $row;
}

$ekstra_umkm = [];
$sql = "SELECT * 
        FROM EKSTRA_UMKM 
        WHERE id_umkm = $id 
        ORDER BY harga_ekstra ASC";
$result = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $ekstra_umkm[] = $row;
}

$now = date('H:i');
$is_open = false;
foreach ($jadwal_list as $j) {
  $jam_buka = substr($j['jam_buka'], 0, 5);
  $jam_tutup = substr($j['jam_tutup'], 0, 5);
  if ($now >= $jam_buka && $now <= $jam_tutup) {
    $is_open = true;
    break;
  }
}

function halalBadge($status){
  if ($status == "Sertifikasi Halal") {
    return "<span class='halal-badge halal-green'>✅ Sertifikasi Halal</span>";
  } elseif ($status == "Halal Belum Sertifikasi") {
    return "<span class='halal-badge halal-yellow'>⚠️ Halal (Belum Sertifikasi)</span>";
  } elseif ($status == "Non-Halal") {
    return "<span class='halal-badge halal-red'>🚫 Non-Halal</span>";
  } else {
    return "<span class='halal-badge halal-gray'>".$status."</span>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
      <?= htmlspecialchars($umkm['nama_stand']) ?> — StreetFood Saparua
    </title>
    <link rel="stylesheet" href="style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
      href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
      rel="stylesheet"
    />
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
          <form
            class="search-bar"
            id="searchForm"
            action="search.php"
            method="GET"
          >
            <input
              type="text"
              name="q"
              id="searchInput"
              placeholder="Cari nama UMKM atau menu..."
              autocomplete="off"
            />
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
        <span>›</span>
        <span><?= htmlspecialchars($umkm['nama_stand']) ?></span>
      </div>
    </div>

    <div class="detail-hero">
      <div class="detail-hero-inner">
        <div class="detail-hero-img">
          <?php if (!empty($umkm['foto']) && file_exists("images/" . $umkm['foto'])): ?>
          <img src="images/<?= htmlspecialchars($umkm['foto']) ?>" alt="Logo <?= htmlspecialchars($umkm['nama_stand']) ?>">
          <?php else: ?>
          <div class="detail-img-ph">🍽️</div>
          <?php endif; ?>
          <?php
          $status_class = 'closed';
          $status_text = '● Tutup';
          if ($is_open) {
            $status_class = 'open';
            $status_text = '● Buka';
          }
          ?>
          <div class="detail-status-dot <?= $status_class ?>">
            <?= $status_text ?>
          </div>
        </div>
        <div class="detail-hero-info">
          <div class="detail-tags">
            <?php if ($umkm['jenis_kategori']): ?>
              <span class="detail-tag-kategori"><?= htmlspecialchars($umkm['jenis_kategori']) ?></span>
            <?php endif; ?>
            <?= halalBadge($umkm['status_halal']) ?>
          </div>
          <h1 class="detail-title"><?= htmlspecialchars($umkm['nama_stand']) ?></h1>
          <p class="detail-owner">
            <span>👤</span>
            Pemilik: <strong><?= htmlspecialchars($umkm['nama_pemilik']) ?></strong>
          </p>
          <?php if (!empty($umkm['deskripsi'])): ?>
          <p class="detail-desc"><?= nl2br(htmlspecialchars($umkm['deskripsi'])) ?></p>
          <?php endif; ?>
          <?php if ($umkm['alamat']): ?>
          <p class="detail-location">
            <span>📍</span>
            <?= htmlspecialchars($umkm['alamat']) ?>
            <?php if ($umkm['koordinat']): ?>
              <a href="https://maps.google.com/?q=<?= urlencode($umkm['koordinat']) ?>" target="_blank" class="maps-link">🗺️ Buka Maps</a>
            <?php endif; ?>
          </p>
          <?php endif; ?>
          <?php if (!empty($jadwal_list)): ?>
          <div class="detail-jadwal">
            <span>🕗</span>
            <strong>Jam Operasional:</strong>
            <?php foreach ($jadwal_list as $j): ?>
              <span class="jadwal-pill"><?= substr($j['jam_buka'],0,5) ?> - <?= substr($j['jam_tutup'],0,5) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if (!empty($bayar_list)): ?>
          <div class="detail-bayar">
            <span>💳</span>
            <strong>Pembayaran:</strong>
            <?php foreach ($bayar_list as $b): ?>
              <span class="bayar-pill"><?= htmlspecialchars($b['metode_pembayaran']) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if (!empty($mitra_list)): ?>
          <div class="detail-mitra">
            <span>🌐</span>
            <strong>Pesan Online:</strong>
            <?php foreach ($mitra_list as $m): ?>
              <?php if ($m['link_mitra']): ?>
                <a href="<?= htmlspecialchars($m['link_mitra']) ?>" target="_blank" class="mitra-pill-link"><?= htmlspecialchars($m['nama_mitra']) ?> ↗</a>
              <?php else: ?>
                <span class="mitra-pill"><?= htmlspecialchars($m['nama_mitra']) ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!empty($foto_booth)): ?>
    <div class="booth-gallery-section">
      <div class="booth-gallery-inner">
        <h2 class="gallery-title">
          <span>📸</span> Foto Booth
        </h2>
        <div class="booth-gallery-grid booth-count-<?= min(count($foto_booth), 5) ?>">
          <?php foreach (array_slice($foto_booth, 0, 5) as $i => $f): ?>
          <div class="booth-thumb <?php if ($i == 0) { echo 'booth-thumb-main'; } ?>" onclick="openLightbox(<?= $i ?>)">
            <img src="<?= htmlspecialchars($f['url_foto']) ?>" alt="<?= htmlspecialchars($f['keterangan']) ?>">
            <?php if (!empty($f['keterangan'])): ?>
              <div class="booth-thumb-caption"><?= htmlspecialchars($f['keterangan']) ?></div>
            <?php endif; ?>
            <?php if ($i === 4 && count($foto_booth) > 5): ?>
              <div class="booth-more-overlay">+<?= count($foto_booth) - 4 ?> lagi</div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div id="lightbox" class="lightbox" style="display:none">
      <div class="lightbox-overlay" onclick="closeLightbox()"></div>
      <div class="lightbox-content">
        <button class="lb-close" onclick="closeLightbox()">✕</button>
        <button class="lb-prev" id="lbPrev"><</button>
        <div class="lb-img-wrap">
          <img id="lbImg" src="" alt="">
          <p id="lbCaption" class="lb-caption"></p>
        </div>
        <button class="lb-next" id="lbNext">></button>
        <p class="lb-counter"><span id="lbCurrent">1</span> / <?= count($foto_booth) ?></p>
      </div>
    </div>
    <?php endif; ?>
    <div class="detail-menu-section">
      <div class="detail-menu-inner">
        <h2 class="menu-section-title">
          Menu & Produk <span class="count-badge"><?= count($menu_list) ?></span>
        </h2>
        <?php if (empty($menu_list)): ?>
        <div class="empty-state">
          <span>📋</span>
          <p>Belum ada menu terdaftar.</p>
        </div>
        <?php else: ?>
        <div class="menu-grid">
          <?php foreach ($menu_list as $mn): ?>
          <a href="menu_detail.php?id=<?= $mn['id_menu'] ?>" class="menu-card menu-card-link">
            <div class="menu-card-img">
              <?php if (!empty($mn['foto_menu']) && file_exists("images/" . $mn['foto_menu'])): ?>
              <img src="images/<?= htmlspecialchars($mn['foto_menu']) ?>" alt="<?= htmlspecialchars($mn['nama_menu']) ?>">
              <?php else: ?>
              <div class="menu-img-ph-big">🍴</div>
              <?php endif; ?>
              <?php if ($mn['jumlah_ekstra_menu'] > 0): ?>
              <span class="menu-ekstra-badge">✚ Ekstra</span>
              <?php endif; ?>
            </div>
            <div class="menu-card-body">
              <h4 class="menu-card-name"><?= htmlspecialchars($mn['nama_menu']) ?></h4>
              <?php if ($mn['rasa_list']): ?>
              <p class="menu-card-rasa">
                <?php foreach (explode(', ', $mn['rasa_list']) as $r): ?>
                  <span class="rasa-chip"><?= htmlspecialchars(trim($r)) ?></span>
                <?php endforeach; ?>
              </p>
              <?php endif; ?>
              <p class="menu-card-price">
                Rp<?= number_format($mn['harga_menu'], 0, ',', '.') ?>
                <?php if (!empty($mn['satuan'])) { ?>
                  <span class="menu-satuan">/ <?= htmlspecialchars($mn['satuan']) ?></span>
                <?php } ?>
              </p>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($ekstra_umkm)): ?>
    <div class="ekstra-section-wrap">
      <div class="ekstra-section-inner">
        <h2 class="ekstra-section-title">
          <span>+</span> Ekstra Berlaku untuk Semua Menu
        </h2>
        <p class="ekstra-section-sub">Tambahan berikut bisa dipesan bersama menu apapun di <?= htmlspecialchars($umkm['nama_stand']) ?></p>
        <div class="ekstra-cols">
          <div class="ekstra-col ekstra-col-full">
            <div class="ekstra-card">
              <div class="ekstra-card-header ekstra-header-umkm">
                <span class="ekstra-icon">🏪</span>
                <div>
                  <h3>Ekstra Umum</h3>
                  <p>Berlaku untuk semua menu</p>
                </div>
              </div>
              <div class="ekstra-list">
                <?php foreach ($ekstra_umkm as $e): ?>
                <div class="ekstra-row">
                  <div class="ekstra-row-info">
                    <span class="ekstra-nama"><?= htmlspecialchars($e['nama_ekstra']) ?></span>
                    <?php if ($e['keterangan']): ?>
                      <span class="ekstra-ket"><?= htmlspecialchars($e['keterangan']) ?></span>
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
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="back-bar-umkm">
      <a href="index.php" class="btn-back">← Kembali ke Daftar UMKM</a>
    </div>

    <footer class="site-footer">
      <p>© 2026 StreetFood Saparua — Sistem Manajemen Basis Data</p>
    </footer>

    <script>
      window.boothPhotos = <?= json_encode($foto_booth) ?>;
    </script>
    <script src="script.js"></script>
    <script src="appUmkm.js"></script>
  </body>
</html>
