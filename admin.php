<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/proses/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// ==================== AJAX HANDLER ====================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax'] == 'get_user' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($result) ?: ['error' => 'Not found']);
        exit();
    }
    
    if ($_GET['ajax'] == 'get_kegiatan' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT k.*, d.manfaat_kegiatan, d.syarat_kegiatan FROM kegiatan k LEFT JOIN detail_kegiatan d ON k.id_kegiatan = d.id_kegiatan WHERE k.id_kegiatan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($result) ?: ['error' => 'Not found']);
        exit();
    }
   
    if ($_GET['ajax'] == 'get_galeri' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn,
            "SELECT id_galeri, foto_galeri, deskripsi_galeri
            FROM galeri
            WHERE id_galeri = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($res) ?: ['error' => 'Not found']);
        exit();
    }

    if ($_GET['ajax'] == 'get_artikel' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM artikel WHERE id_artikel = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($result) ?: ['error' => 'Not found']);
        exit();
    }
    
    if ($_GET['ajax'] == 'get_pesan' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM kontak_pesan WHERE id_pesan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($result) ?: ['error' => 'Not found']);
        exit();
    }

    if ($_GET['ajax'] == 'get_tim' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM tim WHERE id_tim = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($result) ?: ['error' => 'Not found']);
        exit();
    }
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if (in_array($action, ['add_tim', 'edit_tim', 'delete_tim'])) {
        if ($action == 'add_tim' || $action == 'edit_tim') {
            $nama_tim = clean($_POST['nama_tim']);
            $posisi_tim = clean($_POST['posisi_tim']);
            $bio_tim = clean($_POST['bio_tim']);
            $ig_tim = clean($_POST['ig_tim'] ?? '');
            $x_tim = clean($_POST['x_tim'] ?? '');
            $fb_tim = clean($_POST['fb_tim'] ?? '');
            $photo_tim = '';
            
            if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
                $file = $_FILES['photo'];
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if ($file['size'] > 5 * 1024 * 1024) {
                    $message = "❌ Ukuran file terlalu besar (max 5MB)";
                    $message_type = "error";
                } elseif (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $message = "❌ Format file tidak didukung";
                    $message_type = "error";
                } else {
                    $new_file_name = 'tim_' . uniqid() . '.' . $file_ext;
                    $upload_path = __DIR__ . '/assets/img/tim/' . $new_file_name;
                    
                    if (!is_dir(__DIR__ . '/assets/img/tim/')) {
                        mkdir(__DIR__ . '/assets/img/tim/', 0755, true);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $photo_tim = $new_file_name;
                    }
                }
            }
            
            if ($action == 'add_tim') {
                $query = "INSERT INTO tim (nama_tim, posisi_tim, bio_tim, ig_tim, x_tim, fb_tim" . ($photo_tim ? ", photo_tim" : "") . ") 
                          VALUES ('$nama_tim', '$posisi_tim', '$bio_tim', '$ig_tim', '$x_tim', '$fb_tim'" . ($photo_tim ? ", '$photo_tim'" : "") . ")";
                
                if (mysqli_query($conn, $query)) {
                    header("Location: admin.php?tab=tim&tim_added=1");
                    exit();
                }
            } elseif ($action == 'edit_tim') {
                $id_tim = (int)$_POST['id_tim'];
                $old_query = mysqli_query($conn, "SELECT photo_tim FROM tim WHERE id_tim = $id_tim");
                $old_data = mysqli_fetch_assoc($old_query);
                
                if ($photo_tim && !empty($old_data['photo_tim'])) {
                    $old_file = __DIR__ . '/assets/img/tim/' . $old_data['photo_tim'];
                    if (file_exists($old_file)) unlink($old_file);
                }
                
                $query = "UPDATE tim SET nama_tim = '$nama_tim', posisi_tim = '$posisi_tim', 
                         bio_tim = '$bio_tim', ig_tim = '$ig_tim', x_tim = '$x_tim', fb_tim = '$fb_tim'";
                if ($photo_tim) $query .= ", photo_tim = '$photo_tim'";
                $query .= " WHERE id_tim = $id_tim";
                
                if (mysqli_query($conn, $query)) {
                    header("Location: admin.php?tab=tim&tim_updated=1");
                    exit();
                }
            }
        } elseif ($action == 'delete_tim') {
            $id_tim = (int)$_POST['id_tim'];
            $photo_query = mysqli_query($conn, "SELECT photo_tim FROM tim WHERE id_tim = $id_tim");
            $photo_data = mysqli_fetch_assoc($photo_query);
            
            if (!empty($photo_data['photo_tim'])) {
                $photo_file = __DIR__ . '/assets/img/tim/' . $photo_data['photo_tim'];
                if (file_exists($photo_file)) unlink($photo_file);
            }
            
            if (mysqli_query($conn, "DELETE FROM tim WHERE id_tim = $id_tim")) {
                header("Location: admin.php?tab=tim&tim_deleted=1");
                exit();
            }
        }
    }
    
    if ($action == 'upload_foto') {
        $id_kegiatan = intval($_POST['id_kegiatan'] ?? 0);
        $deskripsi_galeri = trim($_POST['deskripsi_galeri'] ?? '');
        
        if ($id_kegiatan > 0 && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed) && $_FILES['foto']['size'] <= 5 * 1024 * 1024) {
                $original_name = basename($_FILES['foto']['name']);
                $upload_dir = __DIR__ . '/assets/img/galeri/';
                $target_path = $upload_dir . $original_name;
                
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                    $file_url = 'assets/img/galeri/' . $original_name;

                    $stmt = mysqli_prepare($conn,
                        "INSERT INTO galeri (id_kegiatan, foto_galeri, deskripsi_galeri)
                        VALUES (?, ?, ?)"
                    );
                    mysqli_stmt_bind_param($stmt, 'iss', $id_kegiatan, $file_url, $deskripsi_galeri);

                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: admin.php?tab=galeri&foto_uploaded=1");
                        exit();
                    }
                }
            }
        }
    }
    
    if ($action == 'delete_foto') {
        $id_foto = intval($_POST['id_foto'] ?? 0);
        $stmt = mysqli_prepare($conn, "SELECT foto_galeri FROM galeri WHERE id_galeri = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id_foto);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $foto = mysqli_fetch_assoc($result);
        
        if ($foto) {
            $stmt2 = mysqli_prepare($conn, "DELETE FROM galeri WHERE id_galeri = ?");
            mysqli_stmt_bind_param($stmt2, 'i', $id_foto);
            
            if (mysqli_stmt_execute($stmt2)) {
                $file_path = __DIR__ . '/' . $foto['foto_galeri'];
                if (file_exists($file_path)) unlink($file_path);
                header("Location: admin.php?tab=galeri&foto_deleted=1");
                exit();
            }
        }
    }
}

$users_list = [];
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY role_user DESC, tanggal_daftar_user ASC");
while ($row = mysqli_fetch_assoc($result)) $users_list[] = $row;

$kegiatan_list = [];
$result = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY created_at_kegiatan DESC");
while ($row = mysqli_fetch_assoc($result)) $kegiatan_list[] = $row;

$artikel_list = [];
$result = mysqli_query($conn, "SELECT * FROM artikel ORDER BY created_at_artikel DESC");
while ($row = mysqli_fetch_assoc($result)) $artikel_list[] = $row;

$tim_list = [];
$result = mysqli_query($conn, "SELECT * FROM tim ORDER BY id_tim ASC");
while ($row = mysqli_fetch_assoc($result)) $tim_list[] = $row;

// ==================== PAGINATION GALERI ====================
$limit = 9; // 3 x 3
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM galeri");
$total_data  = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);
$fotos_list = [];
$result = mysqli_query($conn, "
    SELECT f.*, k.judul_kegiatan
    FROM galeri f
    JOIN kegiatan k ON f.id_kegiatan = k.id_kegiatan
    ORDER BY f.tanggal_upload_galeri DESC
    LIMIT $limit OFFSET $offset
");
while ($row = mysqli_fetch_assoc($result)) {
    $fotos_list[] = $row;
}

$q2 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kegiatan WHERE status_kegiatan = 'selesai'");
$kegiatan_selesai = mysqli_fetch_assoc($q2)['total'] ?? 0;

$q3 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role_user = 'user'");
$total_relawan = mysqli_fetch_assoc($q3)['total'] ?? 0;

$q4 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM artikel");
$total_artikel = mysqli_fetch_assoc($q4)['total'] ?? 0;

$pesan_list = [];
$result = mysqli_query($conn, "SELECT * FROM kontak_pesan ORDER BY tanggal_pesan DESC");
while ($row = mysqli_fetch_assoc($result)) $pesan_list[] = $row;

$pesan_belum_dibaca = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kontak_pesan WHERE status_pesan = 'belum_dibaca'"));

$pendaftaran_list = [];
$result = mysqli_query($conn, "
    SELECT p.*, k.judul_kegiatan, k.tanggal_kegiatan, u.nama_user, u.email_user 
    FROM pendaftaran_kegiatan p 
    JOIN kegiatan k ON p.id_kegiatan = k.id_kegiatan 
    JOIN users u ON p.id_user = u.id_user 
    ORDER BY p.tanggal_daftar DESC
");
while ($row = mysqli_fetch_assoc($result)) $pendaftaran_list[] = $row;

$kegiatanAktif = $conn->query("
    SELECT judul_kegiatan, status_kegiatan, tanggal_kegiatan
    FROM kegiatan
    WHERE status_kegiatan IN ('berlangsung','mendatang')
    ORDER BY tanggal_kegiatan ASC
    LIMIT 5
");

$pendaftaranTerbaru = $conn->query("
    SELECT u.nama_user, k.judul_kegiatan, p.tanggal_daftar
    FROM pendaftaran_kegiatan p
    JOIN users u ON u.id_user = p.id_user
    JOIN kegiatan k ON k.id_kegiatan = p.id_kegiatan
    ORDER BY p.tanggal_daftar DESC
    LIMIT 8
");

$statusResult = $conn->query("
    SELECT status_kegiatan, COUNT(*) total
    FROM kegiatan
    GROUP BY status_kegiatan
");

$statusData = ['selesai'=>0,'berlangsung'=>0,'mendatang'=>0];
while ($r = $statusResult->fetch_assoc()) {
    $statusData[$r['status_kegiatan']] = (int)$r['total'];
}

$kategoriKegiatanResult = $conn->query("
    SELECT jenis_kegiatan, COUNT(*) total
    FROM kegiatan
    GROUP BY jenis_kegiatan
");
$kategoriKegiatanData = [];
while ($r = $kategoriKegiatanResult->fetch_assoc()) {
    $kategoriKegiatanData[$r['jenis_kegiatan']] = (int)$r['total'];
}

$kategoriArtikelResult = $conn->query("
    SELECT kategori_artikel, COUNT(*) total
    FROM artikel
    GROUP BY kategori_artikel
");
$kategoriArtikelData = [];
while ($r = $kategoriArtikelResult->fetch_assoc()) {
    $kategoriArtikelData[$r['kategori_artikel']] = (int)$r['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TUMBUH</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <nav class="admin-nav">
        <div class="nav-brand"><h1>🌱 TUMBUH Admin</h1></div>
        <div class="nav-links">
            <a href="index.php">← Kembali ke Website</a>
            <span class="admin-user">👤 <?php echo $_SESSION['user_name']; ?></span>
            <a href="proses/logout.php" class="btn-logout">Keluar</a>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-sidebar">
            <button class="admin-nav-btn <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'statistik') ? 'active' : ''; ?>" onclick="showTab('statistik', event)">📊 Dashboard</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'relawan' ? 'active' : ''; ?>" onclick="showTab('relawan', event)">👥 Kelola Relawan</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'kegiatan' ? 'active' : ''; ?>" onclick="showTab('kegiatan', event)">📅 Kelola Kegiatan</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'pendaftaran' ? 'active' : ''; ?>" onclick="showTab('pendaftaran', event)">📝 Kelola Pendaftaran</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'artikel' ? 'active' : ''; ?>" onclick="showTab('artikel', event)">📰 Kelola Artikel</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'tim' ? 'active' : ''; ?>" onclick="showTab('tim', event)">👤 Kelola Tim</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'galeri' ? 'active' : ''; ?>" onclick="showTab('galeri', event)">📸 Kelola Galeri</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'pesan' ? 'active' : ''; ?>" onclick="showTab('pesan', event)">
                📧 Kelola Pesan 
                <?php if ($pesan_belum_dibaca > 0): ?>
                    <span class="admin-badge-notification"><?php echo $pesan_belum_dibaca; ?></span>
                <?php endif; ?>
            </button>
        </div>

        <div class="admin-main">
            <!-- TAB STATISTIK -->
            <div id="tab-statistik" class="admin-content <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'statistik') ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Dashboard</h2>
                    <p class="subtitle">Ringkasan statistik website TUMBUH</p>
                </div>

                <div class="dashboard-cards">
                    <div class="dashboard-card">
                        <div class="card-icon">👥</div>
                        <div class="card-content">
                            <h3><?php echo $total_relawan; ?></h3>
                            <p>Relawan Terlibat</p>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">🌳</div>
                        <div class="card-content">
                            <h3><?php echo $kegiatan_selesai; ?></h3>
                            <p>Kegiatan Terlaksana</p>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">📍</div>
                        <div class="card-content">
                            <h3><?php echo $total_artikel; ?></h3>
                            <p>Informasi Dibagikan</p>
                        </div>
                    </div>
                </div>

            <div class="dashboard-lower-grid">

                <!-- KIRI -->
                <div class="dashboard-left-stack">

                    <!-- KEGIATAN AKTIF -->
                    <div class="dashboard-panel">
                        <h3 class="dashboard-panel-title">Kegiatan Aktif</h3>

                        <ul class="activity-simple-list">
                            <?php if ($kegiatanAktif->num_rows > 0): ?>
                                <?php while ($k = $kegiatanAktif->fetch_assoc()): ?>
                                    <li>
                                        <span class="activity-dot <?= $k['status_kegiatan']==='berlangsung'?'active':'upcoming' ?>"></span>
                                        <div>
                                            <strong><?= htmlspecialchars($k['judul_kegiatan']) ?></strong>
                                            <small>
                                                <?= ucfirst($k['status_kegiatan']) ?> ·
                                                <?= date('d M Y', strtotime($k['tanggal_kegiatan'])) ?>
                                            </small>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li><small>Tidak ada kegiatan aktif</small></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- PENDAFTARAN TERBARU -->
                    <div class="dashboard-panel">
                        <h3 class="dashboard-panel-title">Pendaftaran Terbaru</h3>

                        <ul class="registration-simple-list">
                            <?php if ($pendaftaranTerbaru->num_rows > 0): ?>
                                <?php while ($p = $pendaftaranTerbaru->fetch_assoc()): ?>
                                    <li>
                                        <div>
                                            <strong><?= htmlspecialchars($p['nama_user']) ?></strong><br>
                                            <small><?= date('d M Y H:i', strtotime($p['tanggal_daftar'])) ?></small>
                                        </div>
                                        <span><?= htmlspecialchars($p['judul_kegiatan']) ?></span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li><small>Belum ada pendaftaran</small></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- KANAN -->
                <div class="dashboard-right-stack">

                    <div class="dashboard-panel chart-box">
                        <h3 class="dashboard-panel-title">Status Kegiatan</h3>
                        <canvas id="chartStatusKegiatan"></canvas>
                    </div>

                    <div class="dashboard-panel chart-box">
                        <h3 class="dashboard-panel-title">Kategori Kegiatan</h3>
                        <canvas id="chartKategoriKegiatan"></canvas>
                    </div>

                    <div class="dashboard-panel chart-box">
                        <h3 class="dashboard-panel-title">Kategori Artikel</h3>
                        <canvas id="chartKategoriArtikel"></canvas>
                    </div>

                </div>

            </div>
        </div>

            <!-- TAB RELAWAN -->
            <div id="tab-relawan" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'relawan' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Relawan</h2>
                    <p class="subtitle">Daftar semua relawan yang terdaftar di TUMBUH</p>
                </div>

                <?php if (isset($_GET['user_deleted'])): ?><div class="alert success">✅ User berhasil dihapus!</div><?php endif; ?>
                <?php if (isset($_GET['user_updated'])): ?><div class="alert success">✅ User berhasil diupdate!</div><?php endif; ?>

                <div class="card">
                    <?php if (empty($users_list)): ?>
                        <div class="no-data">Belum ada relawan yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                    <div class="admin-list-header">
                        <h3 class="admin-list-title">
                            👥 Daftar Relawan
                            <span class="admin-list-count">(<?= count($users_list); ?> orang)</span>
                        </h3>
                    </div>                            
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Role</th><th>Tanggal Daftar</th><th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($users_list as $user): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $user['nama_user']; ?></td>
                                        <td><?php echo $user['email_user']; ?></td>
                                        <td><?php echo $user['telepon_user'] ?: '-'; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $user['role_user'] == 'admin' ? 'status-berlangsung' : 'status-mendatang'; ?>">
                                                <?php echo $user['role_user'] == 'admin' ? '👑 Admin' : '👤 User'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['tanggal_daftar_user'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-edit" onclick="editUser(<?php echo $user['id_user']; ?>)">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id_user']; ?>, '<?php echo addslashes($user['nama_user']); ?>')">Hapus</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB KEGIATAN -->
            <div id="tab-kegiatan" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'kegiatan' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Kegiatan</h2>
                    <p class="subtitle">Mengelola semua kegiatan di TUMBUH</p>
                </div>

                <?php if (isset($_GET['kegiatan_added'])): ?><div class="alert success">✅ Kegiatan berhasil ditambahkan!</div><?php endif; ?>
                <?php if (isset($_GET['kegiatan_updated'])): ?><div class="alert success">✅ Kegiatan berhasil diperbarui!</div><?php endif; ?>
                <?php if (isset($_GET['kegiatan_deleted'])): ?><div class="alert success">✅ Kegiatan berhasil dihapus!</div><?php endif; ?>

                <button class="btn btn-add" onclick="showModal('add-kegiatan')" style="margin-bottom: 1.5rem;">➕ Tambah Kegiatan Baru</button>
                <div class="card">
                    <?php if (empty($kegiatan_list)): ?>
                        <div class="no-data">Belum ada kegiatan yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                        <div class="admin-list-header">
                            <h3 class="admin-list-title">
                                📅 Daftar Kegiatan
                                <span class="admin-list-count">(<?= count($kegiatan_list); ?> kegiatan)</span>
                            </h3>
                        </div>
                            <table>
                                <thead>
                                    <tr><th>No</th><th>Gambar</th><th>Judul</th><th>Jenis</th><th>Tanggal</th><th>Lokasi</th><th>Status</th><th>Kuota</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($kegiatan_list as $kegiatan): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <?php if (!empty($kegiatan['gambar_kegiatan'])): ?>
                                                    <img src="assets/img/kegiatan/<?php echo htmlspecialchars($kegiatan['gambar_kegiatan']); ?>" class="admin-table-photo" alt="Gambar">
                                                <?php else: ?>
                                                    <div class="admin-avatar-placeholder">📷</div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $kegiatan['judul_kegiatan']; ?></td>
                                            <td>
                                                <?php 
                                                $jenis_labels = ['penanaman' => 'Penanaman', 'edukasi' => 'Edukasi', 'kampanye' => 'Kampanye', 'kolaborasi' => 'Kolaborasi'];
                                                echo $jenis_labels[$kegiatan['jenis_kegiatan']] ?? $kegiatan['jenis_kegiatan'];
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($kegiatan['tanggal_kegiatan'])); ?></td>
                                            <td><?php echo $kegiatan['lokasi_kegiatan']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $kegiatan['status_kegiatan']; ?>">
                                                    <?php 
                                                    $status_labels = ['berlangsung' => 'Berlangsung', 'mendatang' => 'Mendatang', 'selesai' => 'Selesai'];
                                                    echo $status_labels[$kegiatan['status_kegiatan']] ?? $kegiatan['status_kegiatan'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo $kegiatan['kuota_relawan'] ?? 0; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-edit" onclick="editKegiatan(<?php echo $kegiatan['id_kegiatan']; ?>)">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteKegiatan(<?php echo $kegiatan['id_kegiatan']; ?>, '<?php echo addslashes($kegiatan['judul_kegiatan']); ?>')">Hapus</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB PENDAFTARAN -->
            <div id="tab-pendaftaran" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'pendaftaran' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Pendaftaran Kegiatan</h2>
                    <p class="subtitle">Daftar relawan yang mendaftar kegiatan, dikelompokkan per kegiatan</p>
                </div>

                <?php if (isset($_GET['pendaftaran_updated'])): ?><div class="alert success">✅ Status kehadiran berhasil diperbarui!</div><?php endif; ?>
                <?php if (isset($_GET['pendaftaran_deleted'])): ?><div class="alert success">✅ Pendaftaran berhasil dihapus!</div><?php endif; ?>

                <div class="card">
                    <?php if (empty($pendaftaran_list)): ?>
                        <div class="no-data">Belum ada pendaftaran kegiatan.</div>
                    <?php else: ?>
                        <?php
                        // Group pendaftaran by kegiatan
                        $grouped_pendaftaran = [];
                        foreach ($pendaftaran_list as $daftar) {
                            $id_kegiatan = $daftar['id_kegiatan'];
                            if (!isset($grouped_pendaftaran[$id_kegiatan])) {
                                $grouped_pendaftaran[$id_kegiatan] = [
                                    'judul' => $daftar['judul_kegiatan'],
                                    'tanggal' => $daftar['tanggal_kegiatan'],
                                    'pendaftar' => []
                                ];
                            }
                            $grouped_pendaftaran[$id_kegiatan]['pendaftar'][] = $daftar;
                        }
                        ?>

                        <div class="pendaftaran-summary">
                            <h3>📊 Ringkasan</h3>
                            <div class="summary-stats">
                                <div class="summary-item">
                                    <span class="summary-label">Total Kegiatan:</span>
                                    <strong><?php echo count($grouped_pendaftaran); ?></strong>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Total Pendaftaran:</span>
                                    <strong><?php echo count($pendaftaran_list); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="pendaftaran-grouped-container">
                            <?php $kegiatan_no = 1; foreach ($grouped_pendaftaran as $id_kegiatan => $data_kegiatan): ?>
                                <div class="pendaftaran-kegiatan-card">
                                    <div class="kegiatan-card-header" onclick="toggleKegiatanCard(<?php echo $id_kegiatan; ?>)">
                                        <div class="kegiatan-info">
                                            <h3>
                                                <span class="kegiatan-number">#<?php echo $kegiatan_no++; ?></span>
                                                <?php echo htmlspecialchars($data_kegiatan['judul']); ?>
                                            </h3>
                                            <p class="kegiatan-meta">
                                                <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($data_kegiatan['tanggal'])); ?>
                                                <span class="pendaftar-count">
                                                    <i class="fas fa-users"></i> <?php echo count($data_kegiatan['pendaftar']); ?> Pendaftar
                                                </span>
                                            </p>
                                        </div>
                                        <div class="toggle-icon">
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                    </div>

                                    <div class="kegiatan-card-body" id="kegiatan-body-<?php echo $id_kegiatan; ?>">
                                        <div class="table-responsive">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Nama Relawan</th>
                                                        <th>Email</th>
                                                        <th>Status</th>
                                                        <th>Tanggal Daftar</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1; foreach ($data_kegiatan['pendaftar'] as $daftar): ?>
                                                        <tr>
                                                            <td><?php echo $no++; ?></td>
                                                            <td><strong><?php echo htmlspecialchars($daftar['nama_user']); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($daftar['email_user']); ?></td>
                                                            <td>
                                                                <span class="status-badge status-<?php echo $daftar['status_kehadiran']; ?>">
                                                                    <?php 
                                                                    $kehadiran_labels = ['terdaftar' => 'Terdaftar', 'hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];
                                                                    echo $kehadiran_labels[$daftar['status_kehadiran']] ?? $daftar['status_kehadiran'];
                                                                    ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($daftar['tanggal_daftar'])); ?></td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-edit" onclick="editPendaftaran(<?php echo $daftar['id_pendaftaran']; ?>, event)">Edit</button>
                                                                <button class="btn btn-sm btn-danger" onclick="deletePendaftaran(<?php echo $daftar['id_pendaftaran']; ?>, '<?php echo addslashes($daftar['nama_user']); ?>')">Hapus</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <?php
                                        $terdaftar = 0;
                                        $hadir = 0;
                                        $tidak_hadir = 0;
                                        foreach ($data_kegiatan['pendaftar'] as $p) {
                                            if ($p['status_kehadiran'] == 'terdaftar') $terdaftar++;
                                            elseif ($p['status_kehadiran'] == 'hadir') $hadir++;
                                            elseif ($p['status_kehadiran'] == 'tidak_hadir') $tidak_hadir++;
                                        }
                                        ?>

                                        <div class="kegiatan-stats">
                                            <div class="stat-box-mini stat-terdaftar">
                                                <i class="fas fa-user-clock"></i>
                                                <div>
                                                    <strong><?php echo $terdaftar; ?></strong>
                                                    <span>Terdaftar</span>
                                                </div>
                                            </div>
                                            <div class="stat-box-mini stat-hadir">
                                                <i class="fas fa-user-check"></i>
                                                <div>
                                                    <strong><?php echo $hadir; ?></strong>
                                                    <span>Hadir</span>
                                                </div>
                                            </div>
                                            <div class="stat-box-mini stat-tidak-hadir">
                                                <i class="fas fa-user-times"></i>
                                                <div>
                                                    <strong><?php echo $tidak_hadir; ?></strong>
                                                    <span>Tidak Hadir</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB ARTIKEL -->
            <div id="tab-artikel" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'artikel' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Artikel</h2>
                    <p class="subtitle">Mengelola semua artikel dan publikasi</p>
                </div>

                <?php if (isset($_GET['artikel_added'])): ?><div class="alert success">✅ Artikel berhasil ditambahkan!</div><?php endif; ?>
                <?php if (isset($_GET['artikel_updated'])): ?><div class="alert success">✅ Artikel berhasil diperbarui!</div><?php endif; ?>
                <?php if (isset($_GET['artikel_deleted'])): ?><div class="alert success">✅ Artikel berhasil dihapus!</div><?php endif; ?>

                <button class="btn btn-add" onclick="showModal('add-artikel')" style="margin-bottom: 1.5rem;">➕ Tambah Artikel Baru</button>
                <div class="card">
                    <?php if (empty($artikel_list)): ?>
                        <div class="no-data">Belum ada Artikel yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                        <div class="admin-list-header">
                            <h3 class="admin-list-title">
                                📰 Daftar Artikel
                                <span class="admin-list-count">(<?= count($artikel_list); ?> artikel)</span>
                            </h3>
                        </div>                            
                            <table>
                                <thead>
                                    <tr><th>No</th><th>Gambar</th><th>Kategori</th><th>Judul</th><th>Tanggal</th><th>Sumber</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($artikel_list as $artikel): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <?php if (!empty($artikel['gambar_artikel'])): ?>
                                                    <img src="assets/img/artikel/<?php echo htmlspecialchars($artikel['gambar_artikel']); ?>" class="admin-table-photo" alt="Gambar">
                                                <?php else: ?>
                                                    <div class="admin-avatar-placeholder">📰</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $kategori_labels = ['edukasi' => 'Edukasi', 'pandangan' => 'Pandangan', 'tips' => 'Tips', 'cerita' => 'Cerita'];
                                                echo $kategori_labels[$artikel['kategori_artikel']] ?? $artikel['kategori_artikel'];
                                                ?>
                                            </td>
                                            <td><?php echo $artikel['judul_artikel']; ?></td>
                                            <td><?php echo $artikel['tanggal_artikel']; ?></td>
                                            <td><?php echo $artikel['sumber_artikel']; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-edit" onclick="editArtikel(<?php echo $artikel['id_artikel']; ?>)">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteartikel(<?php echo $artikel['id_artikel']; ?>, '<?php echo addslashes($artikel['judul_artikel']); ?>')">Hapus</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB TIM -->
            <div id="tab-tim" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'tim' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Tim</h2>
                    <p class="subtitle">Tambah, edit, atau hapus anggota tim</p>
                </div>

                <?php if (isset($_GET['tim_added'])): ?><div class="alert success">✅ Tim berhasil ditambahkan!</div><?php endif; ?>
                <?php if (isset($_GET['tim_updated'])): ?><div class="alert success">✅ Tim berhasil diperbarui!</div><?php endif; ?>
                <?php if (isset($_GET['tim_deleted'])): ?><div class="alert success">✅ Tim berhasil dihapus!</div><?php endif; ?>
                <?php if ($message): ?><div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div><?php endif; ?>

                <button class="btn btn-add" onclick="showModal('add-tim')" style="margin-bottom: 1.5rem;">
                    ➕ Tambah Tim Baru
                </button>

                <div class="card">
                    <div class="admin-list-header">
                        <h3 class="admin-list-title">📋 Daftar Tim <span class="admin-list-count">(<?php echo count($tim_list); ?> orang)</span></h3>
                    </div>
                    
                    <?php if (count($tim_list) > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr><th>Foto</th><th>Nama</th><th>Posisi</th><th>Biografi</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tim_list as $tim): ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <?php if (!empty($tim['photo_tim'])): ?>
                                                    <img src="assets/img/tim/<?php echo htmlspecialchars($tim['photo_tim']); ?>" 
                                                         class="tim-photo-thumb" alt="<?php echo htmlspecialchars($tim['nama_tim']); ?>">
                                                <?php else: ?>
                                                    <div class="tim-avatar-placeholder">
                                                        <?php 
                                                        $parts = explode(' ', $tim['nama_tim']);
                                                        echo strtoupper(count($parts) >= 2 ? substr($parts[0], 0, 1) . substr($parts[1], 0, 1) : substr($tim['nama_tim'], 0, 2));
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($tim['nama_tim']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($tim['posisi_tim']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($tim['bio_tim'], 0, 50)) . (strlen($tim['bio_tim']) > 50 ? '...' : ''); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-edit" onclick="editTim(<?php echo $tim['id_tim']; ?>)">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteTim(<?php echo $tim['id_tim']; ?>, '<?php echo addslashes($tim['nama_tim']); ?>')">Hapus</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">📭 Belum ada data tim</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB GALERI -->
            <div id="tab-galeri" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'galeri' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Galeri Foto</h2>
                    <p class="subtitle">Upload dan kelola foto untuk setiap kegiatan</p>
                </div>

                <?php if (isset($_GET['foto_uploaded'])): ?><div class="alert success">✅ Foto berhasil diunggah!</div><?php endif; ?>
                <?php if (isset($_GET['foto_deleted'])): ?><div class="alert success">✅ Foto berhasil dihapus!</div><?php endif; ?>

                <button class="btn btn-add" onclick="showModal('upload-galeri')" style="margin-bottom: 1.5rem;">
                     📤 Upload Foto Baru
                </button>
                <div class="admin-galeri-display-section">
                    <div class="admin-list-header">
                    <h3 class="admin-list-header-title">📷 Daftar Foto <span class="admin-list-count">(<?php echo count($fotos_list); ?> foto)</span></h3>
                    </div>
                    
                    <?php if (count($fotos_list) > 0): ?>
                        <div class="admin-galeri-grid">
                            <?php foreach ($fotos_list as $foto): ?>
                                <div class="admin-galeri-item">
                                    <div class="admin-galeri-image-wrapper">
                                        <img src="<?php echo $foto['foto_galeri']; ?>" alt="Foto" class="admin-galeri-image">
                                    </div>
                                    
                                    <div class="admin-galeri-item-content">
                                        <h5 class="admin-galeri-item-title"><?php echo $foto['judul_kegiatan']; ?></h5>
                                        <p class="admin-galeri-item-description">📝 <?php echo $foto['deskripsi_galeri'] ?: 'Tidak ada deskripsi'; ?></p>
                                        <p class="admin-galeri-item-date">📅 <?php echo date('d M Y H:i', strtotime($foto['tanggal_upload_galeri'])); ?></p>
                                        
                                    <div class="admin-galeri-actions">

                                        <button type="button" class="btn btn-sm btn-edit" onclick="editGaleri(<?= $foto['id_galeri']; ?>)">Edit</button>

                                        <form method="POST"
                                            action="proses/admin_actions.php"
                                            class="admin-galeri-action-form">

                                            <input type="hidden" name="action" value="delete_foto">
                                            <input type="hidden" name="id_foto" value="<?= $foto['id_galeri']; ?>">

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Hapus foto ini?')">
                                                Hapus
                                            </button>
                                        </form>

                                    </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <div class="admin-pagination">
                            <?php if ($page > 1): ?>
                                <a href="?tab=galeri&page=<?php echo $page - 1; ?>" class="page-btn">« Prev</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?tab=galeri&page=<?php echo $i; ?>"
                                class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?tab=galeri&page=<?php echo $page + 1; ?>" class="page-btn">Next »</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="admin-galeri-empty">
                            <p>📸 Belum ada foto yang diunggah</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- TAB PESAN -->
            <div id="tab-pesan" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'pesan' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Pesan Kontak</h2>
                    <p class="subtitle">Pesan yang masuk dari formulir kontak website</p>
                </div>

                <?php if (isset($_GET['pesan_deleted'])): ?><div class="alert success">✅ Pesan berhasil dihapus!</div><?php endif; ?>
                <?php if (isset($_GET['pesan_read'])): ?><div class="alert success">✅ Pesan ditandai sudah dibaca!</div><?php endif; ?>

                <div class="card">
                    <div class="admin-pesan-header">
                        <h3>
                            📬 Total Pesan: <?php echo count($pesan_list); ?> 
                            <?php if ($pesan_belum_dibaca > 0): ?>
                                <span class="admin-pesan-count-badge">(<?php echo $pesan_belum_dibaca; ?> belum dibaca)</span>
                            <?php endif; ?>
                        </h3>
                        <?php if (count($pesan_list) > 0): ?>
                            <button class="btn btn-sm admin-pesan-mark-all-btn" onclick="markAllRead()">✓ Tandai Semua Dibaca</button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($pesan_list)): ?>
                        <div class="no-data">📭 Belum ada pesan yang masuk.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Status</th>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Subjek</th>
                                        <th style="width: 180px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pesan_list as $pesan): ?>
                                        <tr class="<?php echo $pesan['status_pesan'] == 'belum_dibaca' ? 'pesan-row-unread' : ''; ?>">
                                            <td style="text-align: center;">
                                                <span class="pesan-status-indicator <?php echo $pesan['status_pesan'] == 'belum_dibaca' ? 'pesan-status-unread' : 'pesan-status-read'; ?>" 
                                                    title="<?php echo $pesan['status_pesan'] == 'belum_dibaca' ? 'Belum Dibaca' : 'Sudah Dibaca'; ?>">
                                                    <?php echo $pesan['status_pesan'] == 'belum_dibaca' ? '●' : '○'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pesan['tanggal_pesan'])); ?></td>
                                            <td><strong><?php echo htmlspecialchars($pesan['nama_pesan']); ?></strong></td>
                                            <td><a href="mailto:<?php echo htmlspecialchars($pesan['email_pesan']); ?>" class="pesan-email-link"><?php echo htmlspecialchars($pesan['email_pesan']); ?></a></td>
                                            <td><?php echo !empty($pesan['telepon_pesan']) ? htmlspecialchars($pesan['telepon_pesan']) : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($pesan['subjek_pesan']); ?></td>
                                            <td>
                                                <button class="btn btn-sm pesan-action-btn" onclick="viewPesan(<?php echo $pesan['id_pesan']; ?>)">Lihat</button>
                                                <button class="btn btn-sm btn-danger" onclick="deletePesan(<?php echo $pesan['id_pesan']; ?>, '<?php echo addslashes($pesan['nama_pesan']); ?>')">Hapus</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODALS ==================== -->
    
    <!-- Modal Edit User -->
    <div id="modal-edit-user" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('edit-user')">&times;</button>
            <h2>Edit Data User</h2>
            <form method="POST" action="proses/admin_actions.php">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit-user-id">
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama_user" id="edit-nama" required></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email_user" id="edit-email" required></div>
                <div class="form-group"><label>Telepon</label><input type="tel" name="telepon_user" id="edit-telepon"></div>
                <div class="form-group"><label>Alamat</label><textarea name="alamat_user" id="edit-alamat" rows="3"></textarea></div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role_user" id="edit-role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" class="btn" onclick="closeModal('edit-user')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Kegiatan -->
    <div id="modal-add-kegiatan" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="modal-close" onclick="closeModal('add-kegiatan')">&times;</button>
            <h2>Tambah Kegiatan Baru</h2>
            <form method="POST" action="proses/admin_actions.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_kegiatan">
                <div class="form-group">
                    <label>Jenis Kegiatan *</label>
                    <select name="jenis_kegiatan" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="penanaman">Penanaman</option>
                        <option value="edukasi">Edukasi</option>
                        <option value="kampanye">Kampanye</option>
                        <option value="kolaborasi">Kolaborasi</option>
                    </select>
                </div>
                <div class="form-group"><label>Judul Kegiatan *</label><input type="text" name="judul_kegiatan" required></div>
                <div class="form-group"><label>Gambar Kegiatan</label><input type="file" name="gambar_kegiatan" accept="image/*"></div>
                <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal_kegiatan" required></div>
                <div class="form-group"><label>Waktu *</label><input type="text" name="waktu_kegiatan" placeholder="Contoh: 08:00 - 12:00 WIB" required></div>
                <div class="form-group"><label>Lokasi *</label><input type="text" name="lokasi_kegiatan" required></div>
                <div class="form-group"><label>Deskripsi *</label><textarea name="deskripsi_kegiatan" rows="4" required></textarea></div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status_kegiatan" required>
                        <option value="mendatang">Mendatang</option>
                        <option value="berlangsung">Sedang Berlangsung</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="form-group"><label>Kuota Relawan</label><input type="number" name="kuota_relawan" min="0" value="0"></div>
                <div class="form-group"><label>Link Grup WhatsApp</label><input type="url" name="link_grup" placeholder="https://chat.whatsapp.com/..."></div>
                <div class="form-group"><label>Manfaat Kegiatan</label><textarea name="manfaat_kegiatan" rows="3" placeholder="Pisahkan dengan enter untuk setiap manfaat"></textarea></div>
                <div class="form-group"><label>Syarat Kegiatan</label><textarea name="syarat_kegiatan" rows="3" placeholder="Pisahkan dengan enter untuk setiap syarat"></textarea></div>
                <button type="submit" class="btn btn-success">💾 Simpan Kegiatan</button>
                <button type="button" class="btn" onclick="closeModal('add-kegiatan')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Kegiatan -->
    <div id="modal-edit-kegiatan" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="modal-close" onclick="closeModal('edit-kegiatan')">&times;</button>
            <h2>Edit Kegiatan</h2>
            <form method="POST" action="proses/admin_actions.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_kegiatan">
                <input type="hidden" name="id_kegiatan" id="edit-kegiatan-id">
                <div class="form-group">
                    <label>Jenis Kegiatan *</label>
                    <select name="jenis_kegiatan" id="edit-kegiatan-jenis" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="penanaman">Penanaman</option>
                        <option value="edukasi">Edukasi</option>
                        <option value="kampanye">Kampanye</option>
                        <option value="kolaborasi">Kolaborasi</option>
                    </select>
                </div>
                <div class="form-group"><label>Judul Kegiatan *</label><input type="text" name="judul_kegiatan" id="edit-kegiatan-judul" required></div>
                <div class="form-group">
                    <label>Gambar Kegiatan (Opsional - kosongkan jika tidak ingin mengubah)</label>
                    <input type="file" name="gambar_kegiatan" accept="image/*">
                    <small id="edit-kegiatan-current-image" style="display: block; margin-top: 5px; color: #666;"></small>
                </div>
                <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal_kegiatan" id="edit-kegiatan-tanggal" required></div>
                <div class="form-group"><label>Waktu *</label><input type="text" name="waktu_kegiatan" id="edit-kegiatan-waktu" required></div>
                <div class="form-group"><label>Lokasi *</label><input type="text" name="lokasi_kegiatan" id="edit-kegiatan-lokasi" required></div>
                <div class="form-group"><label>Deskripsi *</label><textarea name="deskripsi_kegiatan" id="edit-kegiatan-deskripsi" rows="4" required></textarea></div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status_kegiatan" id="edit-kegiatan-status" required>
                        <option value="mendatang">Mendatang</option>
                        <option value="berlangsung">Sedang Berlangsung</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="form-group"><label>Kuota Relawan</label><input type="number" name="kuota_relawan" id="edit-kegiatan-kuota" min="0"></div>
                <div class="form-group"><label>Link Grup WhatsApp</label><input type="url" name="link_grup" id="edit-kegiatan-link"></div>
                <div class="form-group"><label>Manfaat Kegiatan</label><textarea name="manfaat_kegiatan" id="edit-kegiatan-manfaat" rows="3"></textarea></div>
                <div class="form-group"><label>Syarat Kegiatan</label><textarea name="syarat_kegiatan" id="edit-kegiatan-syarat" rows="3"></textarea></div>
                <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" class="btn" onclick="closeModal('edit-kegiatan')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pendaftaran -->
    <div id="modal-edit-pendaftaran" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('edit-pendaftaran')">&times;</button>
            <h2>Edit Status Kehadiran</h2>
            <form method="POST" action="proses/admin_actions.php">
                <input type="hidden" name="action" value="update_pendaftaran">
                <input type="hidden" name="id_pendaftaran" id="edit-pendaftaran-id">
                <div class="form-group">
                    <label>Status Kehadiran *</label>
                    <select name="status_kehadiran" id="edit-pendaftaran-status" required>
                        <option value="terdaftar">Terdaftar</option>
                        <option value="hadir">Hadir</option>
                        <option value="tidak_hadir">Tidak Hadir</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" class="btn" onclick="closeModal('edit-pendaftaran')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Upload Galeri -->
    <div id="modal-upload-galeri" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('upload-galeri')">&times;</button>
            <h2>📤 Upload Foto Baru</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_foto">
                
                <div class="form-group">
                    <label>Pilih Kegiatan *</label>
                    <select name="id_kegiatan" required>
                        <option value="">-- Pilih Kegiatan --</option>
                        <?php foreach ($kegiatan_list as $k): ?>
                            <option value="<?php echo $k['id_kegiatan']; ?>">
                                <?php echo htmlspecialchars($k['judul_kegiatan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pilih Foto *</label>
                    <input type="file" name="foto" accept="image/*" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi (Opsional)</label>
                    <textarea name="deskripsi_galeri" 
                            placeholder="Tulis deskripsi foto..." 
                            rows="4"></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">📤 Upload Foto</button>
                <button type="button" class="btn" onclick="closeModal('upload-galeri')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Galeri -->
    <div id="modal-edit-galeri" class="modal">
        <div class="modal-content" style="max-width:600px">
            <button class="modal-close" onclick="closeModal('edit-galeri')">&times;</button>
            <h2>Edit Foto Galeri</h2>

            <form method="POST"
                action="proses/admin_actions.php"
                enctype="multipart/form-data">

                <input type="hidden" name="action" value="update_galeri">
                <input type="hidden" name="id_galeri" id="edit-galeri-id">

                <div class="form-group">
                    <label>Foto Saat Ini</label>
                    <img id="edit-galeri-preview"
                        src=""
                        style="width:100%;border-radius:8px">
                </div>

                <div class="form-group">
                    <label>Ganti Foto (Opsional)</label>
                    <input type="file" name="foto_baru" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi_galeri"
                            id="edit-galeri-deskripsi"
                            rows="4"></textarea>
                </div>

                <button type="submit" class="btn btn-success">💾 Simpan</button>
                <button type="button"
                        class="btn"
                        onclick="closeModal('edit-galeri')"
                        style="background:#666;color:white">
                    ✖️ Batal
                </button>
            </form>
        </div>
    </div>
    
    <!-- Modal Add Artikel -->
    <div id="modal-add-artikel" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('add-artikel')">&times;</button>
            <h2>Tambah Artikel Baru</h2>
            <form method="POST" action="proses/admin_actions.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_artikel">
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="kategori_artikel" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="edukasi">Edukasi Lingkungan</option>
                        <option value="pandangan">Pandangan</option>
                        <option value="tips">Tips Menanam</option>
                        <option value="cerita">Cerita Lapangan</option>
                    </select>
                </div>
                <div class="form-group"><label>Judul Artikel *</label><input type="text" name="judul_artikel" required></div>
                <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal_artikel" required></div>
                <div class="form-group"><label>Sumber *</label><input type="text" name="sumber_artikel" required></div>
                <div class="form-group">
                    <label>Gambar Artikel</label>
                    <input type="file" name="gambar_artikel" accept="image/*">
                    <small>Format: jpg, jpeg, png, webp (max 5MB)</small>
                </div>
                <div class="form-group"><label>Isi Artikel *</label><textarea name="isi_artikel" rows="12" required></textarea></div>
                <button type="submit" class="btn btn-success">💾 Simpan Artikel</button>
                <button type="button" class="btn" onclick="closeModal('add-artikel')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Artikel -->
    <div id="modal-edit-artikel" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('edit-artikel')">&times;</button>
            <h2>Edit Artikel</h2>
            <form method="POST" action="proses/admin_actions.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_artikel">
                <input type="hidden" name="id_artikel" id="edit-artikel-id">
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="kategori_artikel" id="edit-artikel-kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="edukasi">Edukasi Lingkungan</option>
                        <option value="pandangan">Pandangan</option>
                        <option value="tips">Tips Menanam</option>
                        <option value="cerita">Cerita Lapangan</option>
                    </select>
                </div>
                <div class="form-group"><label>Judul Artikel *</label><input type="text" name="judul_artikel" id="edit-artikel-judul" required></div>
                <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal_artikel" id="edit-artikel-tanggal" required></div>
                <div class="form-group"><label>Sumber *</label><input type="text" name="sumber_artikel" id="edit-artikel-sumber" required></div>
                <div class="form-group">
                    <label>Gambar Artikel (Opsional)</label>
                    <input type="file" name="gambar_artikel" accept="image/*">
                    <small id="edit-artikel-current-image"></small>
                </div>
                <div class="form-group"><label>Isi Artikel *</label><textarea name="isi_artikel" id="edit-artikel-isi" rows="12" required></textarea></div>
                <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" class="btn" onclick="closeModal('edit-artikel')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal View Pesan -->
    <div id="modal-view-pesan" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="modal-close" onclick="closeModal('view-pesan')">&times;</button>
            <h2>Detail Pesan</h2>
            <div id="pesan-detail-content"><div class="loading">Memuat data...</div></div>
        </div>
    </div>

    <!-- Modal Add Tim -->
    <div id="modal-add-tim" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="modal-close" onclick="closeModal('add-tim')">&times;</button>
            <h2>➕ Tambah Tim Baru</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_tim">

                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama_tim" required>
                    </div>
                    <div class="form-group">
                        <label>Posisi/Jabatan *</label>
                        <input type="text" name="posisi_tim" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Biografi/Deskripsi *</label>
                    <textarea name="bio_tim" required rows="4"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Instagram (username saja)</label>
                        <input type="text" name="ig_tim">
                    </div>
                    <div class="form-group">
                        <label>X/Twitter (username saja)</label>
                        <input type="text" name="x_tim">
                    </div>
                </div>

                <div class="form-group">
                    <label>Facebook (username saja)</label>
                    <input type="text" name="fb_tim">
                </div>

                <div class="form-group">
                    <label>Foto Profil *</label>
                    <input type="file" name="photo" accept="image/*" required>
                </div>

                <button type="submit" class="btn btn-success">➕ Tambah Tim</button>
                <button type="button" class="btn" onclick="closeModal('add-tim')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Tim -->
    <div id="modal-edit-tim" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="modal-close" onclick="closeModal('edit-tim')">&times;</button>
            <h2>✏️ Edit Tim</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_tim">
                <input type="hidden" name="id_tim" id="edit-tim-id">

                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama_tim" id="edit-tim-nama" required>
                    </div>
                    <div class="form-group">
                        <label>Posisi/Jabatan *</label>
                        <input type="text" name="posisi_tim" id="edit-tim-posisi" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Biografi/Deskripsi *</label>
                    <textarea name="bio_tim" id="edit-tim-bio" required rows="4"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Instagram (username saja)</label>
                        <input type="text" name="ig_tim" id="edit-tim-ig">
                    </div>
                    <div class="form-group">
                        <label>X/Twitter (username saja)</label>
                        <input type="text" name="x_tim" id="edit-tim-x">
                    </div>
                </div>

                <div class="form-group">
                    <label>Facebook (username saja)</label>
                    <input type="text" name="fb_tim" id="edit-tim-fb">
                </div>

                <div class="form-group">
                    <label>Foto Profil (Opsional - kosongkan jika tidak ingin mengubah)</label>
                    <input type="file" name="photo" accept="image/*">
                    <small id="edit-tim-current-photo" style="display: block; margin-top: 5px; color: #666;"></small>
                </div>

                <button type="submit" class="btn btn-success">💾 Perbarui Tim</button>
                <button type="button" class="btn" onclick="closeModal('edit-tim')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT ==================== -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    function postAction(action, data = {}) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'proses/admin_actions.php';
        let html = `<input type="hidden" name="action" value="${action}">`;
        for (const key in data) html += `<input type="hidden" name="${key}" value="${data[key]}">`;
        form.innerHTML = html;
        document.body.appendChild(form);
        form.submit();
    }

    function showTab(tabName, event) {
        event.preventDefault();

        document.querySelectorAll('.admin-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.admin-nav-btn').forEach(b => b.classList.remove('active'));

        document.getElementById('tab-' + tabName).classList.add('active');
        event.target.classList.add('active');

        if (tabName === 'statistik') {
            setTimeout(initCharts, 50);
        }
    }


    function showModal(name) { document.getElementById('modal-' + name).classList.add('active'); }
    function closeModal(name) { document.getElementById('modal-' + name).classList.remove('active'); }
    window.onclick = function(e) { if (e.target.classList.contains('modal')) e.target.classList.remove('active'); };

    function editUser(userId) {
        fetch('admin.php?ajax=get_user&id=' + userId).then(r => r.json()).then(d => {
            if (d.error) return alert(d.error);
            document.getElementById('edit-user-id').value = d.id_user;
            document.getElementById('edit-nama').value = d.nama_user;
            document.getElementById('edit-email').value = d.email_user;
            document.getElementById('edit-telepon').value = d.telepon_user || '';
            document.getElementById('edit-alamat').value = d.alamat_user || '';
            document.getElementById('edit-role').value = d.role_user || 'user';
            showModal('edit-user');
        });
    }

    function deleteUser(id, name) { if (confirm(`Yakin hapus user: ${name}?`)) postAction('delete_user', { id }); }

    function editKegiatan(id) {
        fetch('admin.php?ajax=get_kegiatan&id=' + id).then(r => r.json()).then(d => {
            if (d.error) return alert(d.error);
            document.getElementById('edit-kegiatan-id').value = d.id_kegiatan;
            document.getElementById('edit-kegiatan-jenis').value = d.jenis_kegiatan;
            document.getElementById('edit-kegiatan-judul').value = d.judul_kegiatan;
            document.getElementById('edit-kegiatan-tanggal').value = d.tanggal_kegiatan;
            document.getElementById('edit-kegiatan-waktu').value = d.waktu_kegiatan;
            document.getElementById('edit-kegiatan-lokasi').value = d.lokasi_kegiatan;
            document.getElementById('edit-kegiatan-deskripsi').value = d.deskripsi_kegiatan;
            document.getElementById('edit-kegiatan-status').value = d.status_kegiatan;
            document.getElementById('edit-kegiatan-kuota').value = d.kuota_relawan || 0;
            document.getElementById('edit-kegiatan-link').value = d.link_grup || '';
            document.getElementById('edit-kegiatan-manfaat').value = d.manfaat_kegiatan || '';
            document.getElementById('edit-kegiatan-syarat').value = d.syarat_kegiatan || '';
            
            const currentImg = document.getElementById('edit-kegiatan-current-image');
            if (d.gambar_kegiatan) {
                currentImg.textContent = `Gambar saat ini: ${d.gambar_kegiatan}`;
                currentImg.style.display = 'block';
            } else {
                currentImg.style.display = 'none';
            }
            
            showModal('edit-kegiatan');
        });
    }

    function deleteKegiatan(id, judul) { if (confirm(`Hapus kegiatan: ${judul}?`)) postAction('delete_kegiatan', { id }); }

    function editPendaftaran(id) {
        const row = event.target.closest('tr');
        const statusCell = row.cells[5].querySelector('.status-badge');
        const statusClass = statusCell.className;
        
        let status = 'terdaftar';
        if (statusClass.includes('status-hadir')) status = 'hadir';
        else if (statusClass.includes('status-tidak_hadir')) status = 'tidak_hadir';
        
        document.getElementById('edit-pendaftaran-id').value = id;
        document.getElementById('edit-pendaftaran-status').value = status;
        
        showModal('edit-pendaftaran');
    }

    function deletePendaftaran(id, nama) { if (confirm(`Hapus pendaftaran: ${nama}?`)) postAction('delete_pendaftaran', { id }); }

    function editArtikel(id) {
        fetch('admin.php?ajax=get_artikel&id=' + id)
            .then(r => r.json())
            .then(d => {
                if (d.error) return alert(d.error);

                document.getElementById('edit-artikel-id').value = d.id_artikel;
                document.getElementById('edit-artikel-kategori').value = d.kategori_artikel;
                document.getElementById('edit-artikel-judul').value = d.judul_artikel;
                document.getElementById('edit-artikel-tanggal').value = d.tanggal_artikel;
                document.getElementById('edit-artikel-sumber').value = d.sumber_artikel;
                document.getElementById('edit-artikel-isi').value = d.isi_artikel;

                document.getElementById('edit-artikel-current-image').textContent =
                    d.gambar_artikel ? 'Gambar saat ini: ' + d.gambar_artikel : '';

                showModal('edit-artikel');
            });
    }

    function deleteartikel(id, judul) { if (confirm(`Hapus artikel: ${judul}?`)) postAction('delete_artikel', { id }); }

    function editTim(id) {
        // Ambil data dari row tabel
        const row = event.target.closest('tr');
        const cells = row.cells;
        
        // Set ID
        document.getElementById('edit-tim-id').value = id;
        
        // Set data dari tabel
        document.getElementById('edit-tim-nama').value = cells[1].querySelector('strong').textContent;
        document.getElementById('edit-tim-posisi').value = cells[2].textContent;
        document.getElementById('edit-tim-bio').value = cells[3].textContent.replace('...', '');
        
        // Ambil detail lengkap via AJAX
        fetch('admin.php?ajax=get_tim&id=' + id)
            .then(r => r.json())
            .then(d => {
                if (d.error) return alert(d.error);
                
                document.getElementById('edit-tim-nama').value = d.nama_tim;
                document.getElementById('edit-tim-posisi').value = d.posisi_tim;
                document.getElementById('edit-tim-bio').value = d.bio_tim;
                document.getElementById('edit-tim-ig').value = d.ig_tim || '';
                document.getElementById('edit-tim-x').value = d.x_tim || '';
                document.getElementById('edit-tim-fb').value = d.fb_tim || '';
                
                const currentPhoto = document.getElementById('edit-tim-current-photo');
                if (d.photo_tim) {
                    currentPhoto.textContent = `Foto saat ini: ${d.photo_tim}`;
                    currentPhoto.style.display = 'block';
                } else {
                    currentPhoto.style.display = 'none';
                }
                
                showModal('edit-tim');
            });
    }
    function deleteTim(id, nama) { if (confirm(`Hapus tim: ${nama}?`)) postAction('delete_tim', { id_tim: id }); }

    function deletePesan(id, nama) { if (confirm(`Hapus pesan dari: ${nama}?`)) postAction('delete_pesan', { id }); }
    function markAsRead(id) { if (confirm('Tandai pesan sebagai sudah dibaca?')) postAction('mark_read_pesan', { id }); }
    function markAllRead() { if (confirm('Tandai semua pesan sudah dibaca?')) postAction('mark_all_read_pesan'); }

    function viewPesan(id) {
        showModal('view-pesan');
        fetch('admin.php?ajax=get_pesan&id=' + id).then(r => r.json()).then(d => {
            if (d.error) {
                document.getElementById('pesan-detail-content').innerHTML = `<div class="alert error">${d.error}</div>`;
                return;
            }
            document.getElementById('pesan-detail-content').innerHTML = `
                <div class="pesan-detail-card">
                    <p><strong>Nama:</strong> ${d.nama_pesan}</p>
                    <p><strong>Email:</strong> <a href="mailto:${d.email_pesan}" class="pesan-email-link">${d.email_pesan}</a></p>
                    <p><strong>Telepon:</strong> ${d.telepon_pesan || '-'}</p>
                    <p><strong>Tanggal:</strong> ${new Date(d.tanggal_pesan).toLocaleString('id-ID')}</p>
                    <p><strong>Subjek:</strong> ${d.subjek_pesan}</p>
                    <hr class="admin-hr">
                    <div class="pesan-message-content">
                        <p>${d.isi_pesan}</p>
                    </div>
                    <div class="pesan-action-buttons">
                        <button class="btn pesan-reply-btn" onclick="balasPesan('${d.email_pesan}', '${d.subjek_pesan}', '${d.nama_pesan}')">
                            📧 Balas via Email
                        </button>
                        ${d.status_pesan === 'belum_dibaca' ? `<button class="btn pesan-mark-read-btn" onclick="markAsRead(${d.id_pesan})">✓ Tandai Dibaca</button>` : ''}
                    </div>
                </div>
            `;
        });
    }

    function balasPesan(email, subjek, nama) {
        const subject = encodeURIComponent('Re: ' + subjek);
        const body = encodeURIComponent(`\n\n\n---\nBalasan untuk: ${nama} (${email})`);
        window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
    }

    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', e => {
            const fileName = document.getElementById('file-name');
            if (fileName) fileName.textContent = e.target.files[0] ? '✓ ' + e.target.files[0].name : '';
        });
    }

    function toggleKegiatanCard(id) {
        const card = event.currentTarget.closest('.pendaftaran-kegiatan-card');
        card.classList.toggle('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const firstCard = document.querySelector('.pendaftaran-kegiatan-card');
        if (firstCard) {
            firstCard.classList.add('active');
        }
    });

    function editGaleri(id) {
        fetch('admin.php?ajax=get_galeri&id=' + id)
            .then(r => r.json())
            .then(d => {
                if (d.error) return alert(d.error);

                document.getElementById('edit-galeri-id').value = d.id_galeri;
                document.getElementById('edit-galeri-deskripsi').value = d.deskripsi_galeri || '';
                document.getElementById('edit-galeri-preview').src = d.foto_galeri;

                showModal('edit-galeri');
            });
    }

let chartStatus = null;
let chartKategoriKegiatan = null;
let chartKategoriArtikel = null;

function initCharts() {
    const ctxStatus = document.getElementById('chartStatusKegiatan');
    const ctxKegiatan = document.getElementById('chartKategoriKegiatan');
    const ctxArtikel = document.getElementById('chartKategoriArtikel');

    if (!ctxStatus || !ctxKegiatan || !ctxArtikel) return;

    if (chartStatus) chartStatus.destroy();
    if (chartKategoriKegiatan) chartKategoriKegiatan.destroy();
    if (chartKategoriArtikel) chartKategoriArtikel.destroy();

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 1,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 10,
                    font: {
                        size: 11
                    }
                }
            }
        }
    };

    // Chart 1: Status Kegiatan
    chartStatus = new Chart(ctxStatus, {
        type: 'pie',
        data: {
            labels: ['Selesai', 'Berlangsung', 'Mendatang'],
            datasets: [{
                data: [
                    <?= $statusData['selesai'] ?>,
                    <?= $statusData['berlangsung'] ?>,
                    <?= $statusData['mendatang'] ?>
                ],
                backgroundColor: ['#9e9e9e', '#4caf50', '#2196f3'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: chartOptions
    });

    // Chart 2: Kategori Kegiatan
    chartKategoriKegiatan = new Chart(ctxKegiatan, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_keys($kategoriKegiatanData)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($kategoriKegiatanData)) ?>,
                backgroundColor: ['#4caf50', '#2196f3', '#ffc107', '#9c27b0'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: chartOptions
    });

    // Chart 3: Kategori Artikel
    chartKategoriArtikel = new Chart(ctxArtikel, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_keys($kategoriArtikelData)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($kategoriArtikelData)) ?>,
                backgroundColor: ['#2196f3', '#ff9800', '#4caf50', '#9c27b0'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: chartOptions
    });

    console.log('✅ Charts initialized successfully');
}

document.addEventListener('DOMContentLoaded', function () {
    const tab = document.getElementById('tab-statistik');
    if (tab && tab.classList.contains('active')) {
        setTimeout(initCharts, 100);
    }
});
    </script>
</body>
</html>