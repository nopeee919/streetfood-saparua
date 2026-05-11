<?php
include 'koneksi.php';

if (isset($_GET['filter'])) {
  $filter_type = $_GET['filter'];
} else {
  $filter_type = '';
}
if (isset($_GET['jam_hari'])) {
  $jam_hari = $_GET['jam_hari'];
} else {
  $jam_hari = '';
}
if (isset($_GET['jam'])) {
  $jam_input = $_GET['jam'];
} else {
  $jam_input = '';
}
if (isset($_GET['harga_min'])) {
  $harga_min = $_GET['harga_min'];
} else {
  $harga_min = '';
}
if (isset($_GET['harga_max'])) {
  $harga_max = $_GET['harga_max'];
} else {
  $harga_max = '';
}
if (isset($_GET['rasa'])) {
  $rasa_id = $_GET['rasa'];
} else {
  $rasa_id = '';
}

$hari_ini   = hariIniID();
$semua_hari = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

$halal_data = [];
$sql_halal = "SELECT status_halal, COUNT(*) as jumlah
              FROM umkm
              GROUP BY status_halal";
$halal_result = mysqli_query($koneksi, $sql_halal);
while ($row = mysqli_fetch_assoc($halal_result)) {
  $halal_data[] = $row;
}

$mitra_data = [];
$sql_mitra = "SELECT m.nama_mitra, COUNT(um.id_umkm) as jumlah
              FROM MITRA m
              LEFT JOIN UMKM_MITRA um ON m.id_mitra = um.id_mitra
              GROUP BY m.id_mitra
              ORDER BY jumlah DESC";
$mitra_result = mysqli_query($koneksi, $sql_mitra);
while ($row = mysqli_fetch_assoc($mitra_result)) {
  $mitra_data[] = $row;
}

$bayar_data = [];
$sql_bayar = "SELECT mp.metode_pembayaran, COUNT(up.id_umkm) as jumlah
              FROM METODE_PEMBAYARAN mp
              LEFT JOIN UMKM_PEMBAYARAN up ON mp.id_metode = up.id_metode
              GROUP BY mp.id_metode
              ORDER BY jumlah DESC";
$bayar_result = mysqli_query($koneksi, $sql_bayar);
while ($row = mysqli_fetch_assoc($bayar_result)) {
  $bayar_data[] = $row;
}

$rasa_all = [];
$sql_rasa = "SELECT kr.id_rasa, kr.nama_rasa,
              COUNT(DISTINCT m.id_umkm) as jumlah_umkm
            FROM KATEGORI_RASA kr
            LEFT JOIN MENU_RASA mr ON kr.id_rasa = mr.id_rasa
            LEFT JOIN MENU m ON mr.id_menu = m.id_menu
            GROUP BY kr.id_rasa
            ORDER BY jumlah_umkm DESC";
$rasa_result = mysqli_query($koneksi, $sql_rasa);
while ($row = mysqli_fetch_assoc($rasa_result)) {
  $rasa_all[] = $row;
}

$umkm_list    = [];
$filter_label = '';
$active_filter = false;

if ($filter_type === 'jam' && $jam_input) {
  $active_filter = true;
  if ($jam_hari) {
    $hari_filter = $jam_hari;
  } else {
    $hari_filter = $hari_ini;
  }
  $jam = $jam_input . ':00';
  $filter_label  = "Buka hari $hari_filter pukul $jam_input";

  $sql = "SELECT DISTINCT u.id_umkm, u.nama_stand, u.nama_pemilik, u.foto, u.status_halal,
                k.jenis_kategori, l.deskripsi as alamat,
                j.hari, j.jam_buka, j.jam_tutup
          FROM UMKM u
          LEFT JOIN KATEGORI k ON u.id_kategori = k.id_kategori
          LEFT JOIN LOKASI l ON u.id_lokasi = l.id_lokasi
          INNER JOIN JADWAL j ON u.id_umkm = j.id_umkm
          WHERE j.hari = '$hari_filter'
          AND j.jam_buka <= '$jam'
          AND j.jam_tutup >= '$jam'
          ORDER BY u.nama_stand";
  $result = mysqli_query($koneksi, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $umkm_list[] = $row;
  }

} elseif ($filter_type === 'harga' && ($harga_min !== '' || $harga_max !== '')) {
  $active_filter = true;
  if ($harga_min != '') {
    $min = (float)$harga_min;
  } else {
    $min = 0;
  }
  if ($harga_max != '') {
    $max = (float)$harga_max;
  } else {
    $max = PHP_INT_MAX;
  }
  $filter_label = "Harga Rp" . number_format($min, 0, ',', '.') . " - Rp";
  if ($harga_max != '') {
    $filter_label .= number_format($max, 0, ',', '.');
  } else {
    $filter_label .= '∞';
  }

  $sql = "SELECT DISTINCT u.id_umkm, u.nama_stand, u.nama_pemilik, u.foto, u.status_halal,
                k.jenis_kategori, l.deskripsi as alamat
          FROM UMKM u
          LEFT JOIN KATEGORI k ON u.id_kategori=k.id_kategori
          LEFT JOIN LOKASI l ON u.id_lokasi=l.id_lokasi
          INNER JOIN MENU m ON u.id_umkm=m.id_umkm
          WHERE m.harga_menu BETWEEN $min AND $max
          ORDER BY u.nama_stand";
  $result = mysqli_query($koneksi, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $umkm_list[] = $row;
  }

} elseif ($filter_type === 'rasa' && $rasa_id) {
  $active_filter = true;
  $sql_rasa_name = "SELECT nama_rasa FROM KATEGORI_RASA WHERE id_rasa = $rasa_id";
  $rasa_name_result = mysqli_query($koneksi, $sql_rasa_name);
  $rasa_row = mysqli_fetch_assoc($rasa_name_result);
  if (isset($rasa_row['nama_rasa'])) {
    $rasa_name = $rasa_row['nama_rasa'];
  } else {
    $rasa_name = 'Rasa';
  }
  $filter_label = "Rasa: $rasa_name";

  $sql = "SELECT DISTINCT u.id_umkm, u.nama_stand, u.nama_pemilik, u.foto, u.status_halal,
                k.jenis_kategori, l.deskripsi as alamat
          FROM UMKM u
          LEFT JOIN KATEGORI k ON u.id_kategori=k.id_kategori
          LEFT JOIN LOKASI l ON u.id_lokasi=l.id_lokasi
          INNER JOIN MENU m ON u.id_umkm=m.id_umkm
          INNER JOIN MENU_RASA mr ON m.id_menu=mr.id_menu
          WHERE mr.id_rasa = $rasa_id
          ORDER BY u.nama_stand";
  $result = mysqli_query($koneksi, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $umkm_list[] = $row;
  }

} else {
  $sql = "SELECT u.id_umkm, u.nama_stand, u.nama_pemilik, u.foto, u.status_halal,
                k.jenis_kategori, l.deskripsi as alamat
          FROM UMKM u
          LEFT JOIN KATEGORI k ON u.id_kategori=k.id_kategori
          LEFT JOIN LOKASI l ON u.id_lokasi=l.id_lokasi
          ORDER BY u.nama_stand";
  $result = mysqli_query($koneksi, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $umkm_list[] = $row;
  }
}

$filter_count = count($umkm_list);

function getFilteredMenu($koneksi, $umkm_id, $filter_type, $harga_min, $harga_max, $rasa_id) {
  $menu = [];
  if ($filter_type === 'harga') {
    if ($harga_min != '') {
      $min = (float)$harga_min;
    } else {
      $min = 0;
    }
    if ($harga_max != '') {
      $max = (float)$harga_max;
    } else {
      $max = PHP_INT_MAX;
    }
    $sql = "SELECT id_menu, nama_menu, harga_menu, satuan
            FROM MENU
            WHERE id_umkm = $umkm_id
            AND harga_menu BETWEEN $min AND $max";
  } elseif ($filter_type === 'rasa') {
    $sql = "SELECT m.id_menu, m.nama_menu, m.harga_menu, m.satuan
            FROM MENU m
            INNER JOIN MENU_RASA mr ON m.id_menu = mr.id_menu
            WHERE m.id_umkm = $umkm_id
            AND mr.id_rasa = $rasa_id";
  } else {
    return [];
  }
  $result = mysqli_query($koneksi, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $menu[] = $row;
  }
  return $menu;
}

function getMitraUmkm($koneksi, $umkm_id) {
  $mitra = [];
  $sql = "SELECT mt.nama_mitra, um.link_mitra
          FROM UMKM_MITRA um
          JOIN MITRA mt ON um.id_mitra = mt.id_mitra
          WHERE um.id_umkm = $umkm_id";
  $result = mysqli_query($koneksi, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $mitra[] = $row;
  }
  return $mitra;
}

$buka_ids = [];
$now_time = date('H:i:s');
$sql_buka = "SELECT id_umkm
              FROM JADWAL
              WHERE hari = '$hari_ini'
              AND jam_buka <= '$now_time'
              AND jam_tutup >= '$now_time'";
$result_buka = mysqli_query($koneksi, $sql_buka);
while ($row = mysqli_fetch_assoc($result_buka)) {
  $buka_ids[] = $row['id_umkm'];
}
?>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StreetFood Saparua</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet" />
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
          <form action="search.php" class="search-bar" id="searchForm" method="GET">
            <input type="text" name="q" id="searchInput" placeholder="Cari nama UMKM atau menu..." autocomplete="off" />
            <button type="submit">
              <span>🔍</span>
            </button>
          </form>
          <div class="search-dropdown" id="searchDropdown"></div>
        </div>
      </div>
    </header>

    <div class="filter-bar">
      <div class="filter-bar-inner">
        <span class="filter-label-title">Filter:</span>
        <form class="filter-form" method="GET">
          <input type="hidden" name="filter" value="jam" />
          <div class="filter-group">
            <label>⏰ Jam Buka</label>
            <select name="jam_hari">
              <?php foreach ($semua_hari as $h): ?>
                <?php
                if ($jam_hari != '') {
                  $selected_hari = $jam_hari;
                } else {
                  $selected_hari = $hari_ini;
                }
                if ($selected_hari == $h) {
                  $selected = 'selected';
                } else {
                  $selected = '';
                }
                if ($h == $hari_ini) {
                  $label_hari = $h . ' (hari ini)';
                } else {
                  $label_hari = $h;
                }
                ?>
                <option value="<?= $h ?>" <?= $selected ?>>
                  <?= $label_hari ?>
                </option>
              <?php endforeach; ?>
            </select>
            <input type="time" name="jam" value="<?= htmlspecialchars($jam_input) ?>" required />
            <?php
            $class = '';
            if ($filter_type == 'jam') {
              $class = 'active';
            }
            ?>
            <button type="submit" class="btn-filter <?= $class ?>">Filter</button>
          </div>
        </form>
        <div class="filter-divider"></div>
        <form class="filter-form" method="GET">
          <input type="hidden" name="filter" value="harga" />
          <div class="filter-group">
            <label>💰 Range Harga</label>
            <div class="range-inputs">
              <input type="number" name="harga_min" placeholder="Min" value="<?= htmlspecialchars($harga_min) ?>" min="0" />
              <span>-</span>
              <input type="number" name="harga_max" placeholder="Max" value="<?= htmlspecialchars($harga_max) ?>" min="0" />
            </div>
            <?php
            $class = '';
            if ($filter_type == 'harga') {
              $class = 'active';
            }
            ?>
            <button type="submit" class="btn-filter <?= $class ?>">Filter</button>
          </div>
        </form>
        <div class="filter-divider"></div>
        <form class="filter-form" method="GET">
          <input type="hidden" name="filter" value="rasa" />
          <div class="filter-group">
            <label>👅 Rasa</label>
            <select name="rasa" required>
              <option value="">Pilih rasa...</option>
              <?php foreach ($rasa_all as $r): ?>
              <option value="<?= $r['id_rasa'] ?>" <?= $rasa_id==$r['id_rasa']?'selected':'' ?>>
                <?= htmlspecialchars($r['nama_rasa']) ?> (<?= $r['jumlah_umkm'] ?> UMKM)
              </option>
              <?php endforeach; ?>
            </select>
            <?php
            $class = '';
            if ($filter_type == 'rasa') {
              $class = 'active';
            }
            ?>
            <button type="submit" class="btn-filter <?= $class ?>">Filter</button>
          </div>
        </form>
        <?php if ($active_filter): ?>
        <a href="index.php" class="btn-reset">✕ Reset</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="page-layout">

      <aside class="sidebar sidebar-left">
        <div class="sidebar-card">
          <h3 class="sidebar-title">
            <span class="sidebar-icon">🕌</span> Sertifikat Halal
          </h3>
          <?php foreach ($halal_data as $h): ?>
            <?php
              $badge_color = 'yellow';
              if ($h['status_halal'] == 'Sertifikasi Halal') {
                $badge_color = 'green';
              } elseif ($h['status_halal'] == 'Non-Halal') {
                $badge_color = 'red';
              }
            ?>
            <div class="sidebar-row">
              <span class="sidebar-row-label"><?= htmlspecialchars($h['status_halal']) ?></span>
              <span class="badge badge-<?= $badge_color ?>"><?= $h['jumlah'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="sidebar-card">
          <h3 class="sidebar-title">
            <span class="sidebar-icon">💳</span> Metode Pembayaran
          </h3>
          <?php foreach ($bayar_data as $b): ?>
            <div class="sidebar-row">
              <span class="sidebar-row-label"><?= htmlspecialchars($b['metode_pembayaran']) ?></span>
              <span class="badge badge-blue"><?= $b['jumlah'] ?></span>
            </div>
          <?php endforeach; ?>
          <p class="sidebar-note">
            Selain cash:
            <?php
              $non_cash = [];
              foreach ($bayar_data as $b) {
                if (strtolower($b['metode_pembayaran']) != 'cash') {
                  $non_cash[] = $b['metode_pembayaran'];
                }
              }
              echo implode(', ', $non_cash) ?: '–';
            ?>
          </p>
        </div>
      </aside>

      <main class="main-content">
        <?php if ($active_filter): ?>
        <div class="filter-result-header">
          <div class="filter-result-info">
            <span class="filter-result-tag"><?= htmlspecialchars($filter_label) ?></span>
            <strong><?= $filter_count ?> UMKM</strong> ditemukan
          </div>
          <a href="index.php" class="link-reset">Lihat semua UMKM →</a>
        </div>
        <?php else: ?>
        <div class="section-heading">
          <h2>Semua UMKM <span class="count-badge"><?= count($umkm_list) ?></span></h2>
          <p>Klik kartu UMKM untuk lihat detail & menu lengkap</p>
        </div>
        <?php endif; ?>

        <div class="umkm-grid">
          <?php foreach ($umkm_list as $u):
            $is_buka = in_array($u['id_umkm'], $buka_ids);
            if ($filter_type === 'jam') {
              $mitra_links = getMitraUmkm($koneksi, $u['id_umkm']);
            } else {
              $mitra_links = [];
            }
            if ($filter_type === 'harga' || $filter_type === 'rasa') {
              $filtered_menu = getFilteredMenu($koneksi, $u['id_umkm'], $filter_type, $harga_min, $harga_max, $rasa_id);
            } else {
              $filtered_menu = [];
            }
          ?>
          <article class="umkm-card" onclick="window.location='umkm_detail.php?id=<?= $u['id_umkm'] ?>'">
            <div class="card-img-wrap">
              <?php if (!empty($u['foto']) && file_exists("images/" . $u['foto'])): ?>
                <img src="images/<?= htmlspecialchars($u['foto']) ?>" alt="<?= htmlspecialchars($u['nama_stand']) ?>" loading="lazy">
              <?php else: ?>
                <div class="card-img-placeholder">🍽️</div>
              <?php endif; ?>
              <span class="card-kategori"><?= htmlspecialchars($u['jenis_kategori'] ?? 'Umum') ?></span>
              <?php if (!$active_filter): ?>
                <?php
                if ($is_buka) {
                  $status_class = 'open';
                  $status_text  = '● Buka';
                } else {
                  $status_class = 'closed';
                  $status_text  = '● Tutup';
                }
                ?>
                <span class="card-status-dot <?= $status_class ?>">
                  <?= $status_text ?>
                </span>
              <?php endif; ?>
            </div>

            <div class="card-body">
              <h3 class="card-title"><?= htmlspecialchars($u['nama_stand']) ?></h3>
              <p class="card-owner">
                <span>👤</span>
                <?= htmlspecialchars($u['nama_pemilik']) ?>
              </p>

              <?php if ($filter_type === 'jam' && !empty($u['jam_buka'])): ?>
                <p class="card-jadwal">
                  <span>🕗</span>
                  <?= htmlspecialchars($u['hari']) ?>
                  <?= substr($u['jam_buka'], 0, 5) ?> - <?= substr($u['jam_tutup'], 0, 5) ?>
                </p>
                <?php if (!empty($mitra_links)): ?>
                  <div class="card-mitra">
                    <?php foreach ($mitra_links as $m): ?>
                      <?php if ($m['link_mitra']): ?>
                        <a href="<?= htmlspecialchars($m['link_mitra']) ?>" target="_blank" class="mitra-link" onclick="event.stopPropagation()">
                          <?= htmlspecialchars($m['nama_mitra']) ?> ↗
                        </a>
                      <?php else: ?>
                        <span class="mitra-tag"><?= htmlspecialchars($m['nama_mitra']) ?></span>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (($filter_type === 'harga' || $filter_type === 'rasa') && !empty($filtered_menu)): ?>
                <div class="card-menu-list">
                  <p class="menu-list-label">Menu yang sesuai:</p>
                  <?php foreach ($filtered_menu as $mn): ?>
                  <div class="menu-row">
                    <span class="menu-name"><?= htmlspecialchars($mn['nama_menu']) ?></span>
                    <span class="menu-price">Rp<?= number_format($mn['harga_menu'], 0, ',', '.') ?><?= $mn['satuan'] ? '/' . $mn['satuan'] : '' ?></span>
                  </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="card-footer">
              <span class="card-detail-link">Lihat Detail →</span>
            </div>
          </article>
          <?php endforeach; ?>
          <?php if (empty($umkm_list)): ?>
          <div class="empty-state">
            <span>🔍</span><p>Tidak ada UMKM yang sesuai filter.</p>
            <a href="index.php">Reset filter</a>
          </div>
          <?php endif; ?>
        </div>
      </main>

      <aside class="sidebar sidebar-right">
        <div class="sidebar-card">
          <h3 class="sidebar-title"><span class="sidebar-icon">🛵</span> Mitra Online</h3>
          <?php foreach ($mitra_data as $i => $m): ?>
            <?php
              $class = '';
              $icon = '';
              if ($i == 0) {
                $class = 'top-mitra';
                $icon = '🏆 ';
              }
            ?>
            <div class="sidebar-row <?= $class ?>">
              <span class="sidebar-row-label"><?= $icon ?><?= htmlspecialchars($m['nama_mitra']) ?></span>
              <span class="badge badge-orange"><?= $m['jumlah'] ?></span>
            </div>
          <?php endforeach; ?>
          <?php if (!empty($mitra_data)): ?>
            <p class="sidebar-note">Terbanyak: <strong><?= htmlspecialchars($mitra_data[0]['nama_mitra']) ?></strong></p>
          <?php endif; ?>
        </div>
        <div class="sidebar-card">
          <h3 class="sidebar-title"><span class="sidebar-icon">😋</span> Kategori Rasa</h3>
          <?php foreach ($rasa_all as $r): ?>
            <?php
              $active_class = '';
              if ($rasa_id == $r['id_rasa']) {
                $active_class = 'active-rasa';
              }
            ?>
            <div class="sidebar-row">
              <a href="?filter=rasa&rasa=<?= $r['id_rasa'] ?>" class="sidebar-rasa-link <?= $active_class ?>">
                <?= htmlspecialchars($r['nama_rasa']) ?>
              </a>
              <span class="badge badge-purple"><?= $r['jumlah_umkm'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </aside>
    </div>

    <footer class="site-footer">
      <p>© 2026 StreetFood Saparua — Sistem Manajemen Basis Data</p>
    </footer>
    <script src="app.js"></script>
  </body>
</html>