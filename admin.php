<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

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
        $stmt = mysqli_prepare($conn, "SELECT * FROM kegiatan WHERE id_kegiatan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($result) ?: ['error' => 'Not found']);
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
}

// Form submission handling
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
            $original_name = basename($_FILES['foto']['name']); // nama asli file
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

// Fetch data
$users_list = [];
$result = mysqli_query($conn, "SELECT * FROM users WHERE role_user = 'user' ORDER BY tanggal_daftar_user ASC");
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

$fotos_list = [];
$result = mysqli_query($conn, "SELECT f.*, k.judul_kegiatan FROM galeri f JOIN kegiatan k ON f.id_kegiatan = k.id_kegiatan ORDER BY f.tanggal_upload_galeri DESC");
while ($row = mysqli_fetch_assoc($result)) $fotos_list[] = $row;

$statistik = [];
$result = mysqli_query($conn, "SELECT * FROM statistik");
while ($row = mysqli_fetch_assoc($result)) $statistik[$row['nama_stat']] = $row['nilai'];

$edit_tim = null;
if (isset($_GET['edit_tim'])) {
    $id_tim = (int)$_GET['edit_tim'];
    $result = mysqli_query($conn, "SELECT * FROM tim WHERE id_tim = $id_tim");
    $edit_tim = mysqli_fetch_assoc($result);
}

$pesan_list = [];
$result = mysqli_query($conn, "SELECT * FROM kontak_pesan ORDER BY tanggal_pesan DESC");
while ($row = mysqli_fetch_assoc($result)) $pesan_list[] = $row;

$pesan_belum_dibaca = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kontak_pesan WHERE status_pesan = 'belum_dibaca'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TUMBUH</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="admin-page">
    <nav class="admin-nav">
        <div class="nav-brand"><h1>🌱 TUMBUH Admin</h1></div>
        <div class="nav-links">
            <a href="index.php">← Kembali ke Website</a>
            <span class="admin-user">👤 <?php echo $_SESSION['user_name']; ?></span>
            <a href="auth/logout.php" class="btn-logout">Keluar</a>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-sidebar">
            <button class="admin-nav-btn <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'statistik') ? 'active' : ''; ?>" onclick="showTab('statistik', event)">📊 Dashboard</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'relawan' ? 'active' : ''; ?>" onclick="showTab('relawan', event)">👥 Kelola Relawan</button>
            <button class="admin-nav-btn <?php echo ($_GET['tab'] ?? '') == 'kegiatan' ? 'active' : ''; ?>" onclick="showTab('kegiatan', event)">📅 Kelola Kegiatan</button>
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
                            <h3><?php echo count($users_list); ?></h3>
                            <p>Total Relawan</p>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">🌳</div>
                        <div class="card-content">
                            <h3><?php echo number_format($statistik['pohon_ditanam'] ?? 0); ?></h3>
                            <p>Pohon Ditanam</p>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">📍</div>
                        <div class="card-content">
                            <h3><?php echo $statistik['lokasi_penanaman'] ?? 0; ?></h3>
                            <p>Lokasi Penanaman</p>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">📰</div>
                        <div class="card-content">
                            <h3><?php echo count($artikel_list); ?></h3>
                            <p>Total Artikel</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="card">
                        <h3>Statistik Lengkap</h3>
                        <div class="stat-item"><span>Total Kegiatan</span><strong><?php echo count($kegiatan_list); ?></strong></div>
                        <div class="stat-item"><span>Status Relawan Aktif</span><strong><?php echo count($users_list); ?></strong></div>
                        <div class="stat-item"><span>Total Publikasi</span><strong><?php echo count($artikel_list); ?></strong></div>
                        <div class="stat-item"><span>Total Anggota Tim</span><strong><?php echo count($tim_list); ?></strong></div>
                        <div class="stat-item"><span>Total Foto Galeri</span><strong><?php echo count($fotos_list); ?></strong></div>
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
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Bidang</th><th>Tanggal Daftar</th><th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($users_list as $user): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $user['nama_user']; ?></td>
                                            <td><?php echo $user['email_user']; ?></td>
                                            <td><?php echo $user['telepon_user']; ?></td>
                                            <td><?php 
                                                $bidang_labels = ['penanaman' => 'Penanaman', 'edukasi' => 'Edukasi', 'publikasi' => 'Publikasi', 'dokumentasi' => 'Dokumentasi'];
                                                echo $bidang_labels[$user['bidang_user']] ?? $user['bidang_user'];
                                            ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($user['tanggal_daftar_user'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-edit" onclick="editUser(<?php echo $user['id_user']; ?>)">Edit</button>
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

                <div class="card">
                    <button class="btn btn-add" onclick="showModal('add-kegiatan')" style="margin-bottom: 1.5rem;">➕ Tambah Kegiatan Baru</button>

                    <?php if (empty($kegiatan_list)): ?>
                        <div class="no-data">Belum ada kegiatan yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr><th>No</th><th>Judul</th><th>Tanggal</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($kegiatan_list as $kegiatan): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $kegiatan['judul_kegiatan']; ?></td>
                                            <td><?php echo $kegiatan['tanggal_kegiatan']; ?></td>
                                            <td><?php echo $kegiatan['lokasi_kegiatan']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $kegiatan['status_kegiatan']; ?>">
                                                    <?php 
                                                    $status_labels = ['berlangsung' => 'Berlangsung', 'mendatang' => 'Mendatang', 'selesai' => 'Selesai'];
                                                    echo $status_labels[$kegiatan['status_kegiatan']] ?? $kegiatan['status_kegiatan'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-edit" onclick="editKegiatan(<?php echo $kegiatan['id_kegiatan']; ?>)">Edit</button>
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

            <!-- TAB ARTIKEL -->
            <div id="tab-artikel" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'artikel' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Artikel</h2>
                    <p class="subtitle">Mengelola semua artikel dan publikasi</p>
                </div>

                <?php if (isset($_GET['artikel_added'])): ?><div class="alert success">✅ Artikel berhasil ditambahkan!</div><?php endif; ?>
                <?php if (isset($_GET['artikel_updated'])): ?><div class="alert success">✅ Artikel berhasil diperbarui!</div><?php endif; ?>
                <?php if (isset($_GET['artikel_deleted'])): ?><div class="alert success">✅ Artikel berhasil dihapus!</div><?php endif; ?>

                <div class="card">
                    <button class="btn btn-add" onclick="showModal('add-artikel')" style="margin-bottom: 1.5rem;">➕ Tambah Artikel Baru</button>
                    <?php if (empty($artikel_list)): ?>
                        <div class="no-data">Belum ada Artikel yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr><th>No</th><th>Judul</th><th>Tanggal</th><th>Sumber</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($artikel_list as $artikel): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
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

            <!-- TAB TIM (dengan form inline) -->
            <div id="tab-tim" class="admin-content <?php echo ($_GET['tab'] ?? '') == 'tim' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Tim</h2>
                    <p class="subtitle">Tambah, edit, atau hapus anggota tim</p>
                </div>

                <?php if (isset($_GET['tim_added'])): ?><div class="alert success">✅ Tim berhasil ditambahkan!</div><?php endif; ?>
                <?php if (isset($_GET['tim_updated'])): ?><div class="alert success">✅ Tim berhasil diperbarui!</div><?php endif; ?>
                <?php if (isset($_GET['tim_deleted'])): ?><div class="alert success">✅ Tim berhasil dihapus!</div><?php endif; ?>
                <?php if ($message): ?><div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div><?php endif; ?>

                <div class="tim-form-section">
                    <h3><?php echo $edit_tim ? '✏️ Edit Tim' : '➕ Tambah Tim Baru'; ?></h3>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_tim ? 'edit_tim' : 'add_tim'; ?>">
                        <?php if ($edit_tim): ?><input type="hidden" name="id_tim" value="<?php echo $edit_tim['id_tim']; ?>"><?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="nama_tim" required value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['nama_tim']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Posisi/Jabatan *</label>
                                <input type="text" name="posisi_tim" required value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['posisi_tim']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Biografi/Deskripsi *</label>
                            <textarea name="bio_tim" required rows="4"><?php echo $edit_tim ? htmlspecialchars($edit_tim['bio_tim']) : ''; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Instagram (username saja)</label>
                                <input type="text" name="ig_tim" value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['ig_tim']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>X/Twitter (username saja)</label>
                                <input type="text" name="x_tim" value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['x_tim']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Facebook (username saja)</label>
                            <input type="text" name="fb_tim" value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['fb_tim']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Foto Profil <?php echo !$edit_tim ? '*' : '(Opsional)'; ?></label>
                            <input type="file" id="photo" name="photo" accept="image/*" <?php echo !$edit_tim ? 'required' : ''; ?>>
                            <?php if ($edit_tim && !empty($edit_tim['photo_tim'])): ?>
                                <div class="tim-form-photo-preview">
                                    <img src="assets/img/tim/<?php echo htmlspecialchars($edit_tim['photo_tim']); ?>" 
                                        class="admin-table-photo">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn btn-success"><?php echo $edit_tim ? '💾 Perbarui Tim' : '➕ Tambah Tim'; ?></button>
                            <?php if ($edit_tim): ?><a href="admin.php?tab=tim" class="btn-cancel">← Batal</a><?php endif; ?>
                        </div>
                    </form>
                </div>

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
                                                <button class="btn btn-sm btn-edit" onclick="editTim(<?php echo $tim['id_tim']; ?>)">Edit</button>
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

                <div class="admin-galeri-upload-section">
                    <h3>📤 Upload Foto Baru</h3>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_foto">
                        
                        <div class="admin-galeri-form-grid">
                            <div>
                                <label class="admin-galeri-form-label">Pilih Kegiatan *</label>
                                <select name="id_kegiatan" required class="admin-galeri-form-select">
                                    <option value="">-- Pilih Kegiatan --</option>
                                    <?php foreach ($kegiatan_list as $k): ?>
                                        <option value="<?php echo $k['id_kegiatan']; ?>"><?php echo $k['judul_kegiatan']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="admin-galeri-form-label">Pilih Foto *</label>
                                <input type="file" name="foto" accept="image/*" required class="admin-galeri-form-input">
                            </div>
                        </div>
                        
                        <div class="admin-galeri-form-full">
                            <label class="admin-galeri-form-label">Deskripsi (Opsional)</label>
                            <textarea name="deskripsi_galeri" placeholder="Tulis deskripsi foto..." class="admin-galeri-form-textarea"></textarea>
                        </div>
                        
                        <button type="submit" class="admin-galeri-submit-btn">
                            📤 Upload Foto
                        </button>
                    </form>
                </div>

                <div class="admin-galeri-display-section">
                    <h3>📷 Daftar Foto (Total: <?php echo count($fotos_list); ?>)</h3>
                    
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
                                        
                                        <form method="POST" class="admin-galeri-delete-form">
                                            <input type="hidden" name="action" value="delete_foto">
                                            <input type="hidden" name="id_foto" value="<?php echo $foto['id_galeri']; ?>">
                                            <button type="submit" onclick="return confirm('Hapus foto ini?');" class="admin-galeri-delete-btn">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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

    <!-- MODALS -->
    <div id="modal-edit-user" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('edit-user')">&times;</button>
            <h2>Edit Data User</h2>
            <form method="POST" action="admin_actions.php">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit-user-id">
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama_user" id="edit-nama" required></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email_user" id="edit-email" required></div>
                <div class="form-group"><label>Telepon *</label><input type="tel" name="telepon_user" id="edit-telepon" required></div>
                <div class="form-group"><label>Alamat</label><textarea name="alamat_user" id="edit-alamat" rows="3"></textarea></div>
                <div class="form-group">
                    <label>Bidang Minat *</label>
                    <select name="bidang_user" id="edit-bidang" required>
                        <option value="penanaman">Penanaman Pohon</option>
                        <option value="edukasi">Edukasi Lingkungan</option>
                        <option value="publikasi">Publikasi & Media</option>
                        <option value="dokumentasi">Dokumentasi</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" class="btn" onclick="closeModal('edit-user')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <div id="modal-add-kegiatan" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('add-kegiatan')">&times;</button>
            <h2>Tambah Kegiatan Baru</h2>
            <form method="POST" action="admin_actions.php">
                <input type="hidden" name="action" value="add_kegiatan">
                <div class="form-group"><label>Judul Kegiatan *</label><input type="text" name="judul_kegiatan" required></div>
                <div class="form-group"><label>Tanggal *</label><input type="text" name="tanggal_kegiatan" placeholder="Contoh: 25 Desember 2024" required></div>
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
                <button type="submit" class="btn btn-success">💾 Simpan Kegiatan</button>
                <button type="button" class="btn" onclick="closeModal('add-kegiatan')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <div id="modal-edit-kegiatan" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('edit-kegiatan')">&times;</button>
            <h2>Edit Kegiatan</h2>
            <form method="POST" action="admin_actions.php">
                <input type="hidden" name="action" value="update_kegiatan">
                <input type="hidden" name="id_kegiatan" id="edit-kegiatan-id">
                <div class="form-group"><label>Judul Kegiatan *</label><input type="text" name="judul_kegiatan" id="edit-kegiatan-judul" required></div>
                <div class="form-group"><label>Tanggal *</label><input type="text" name="tanggal_kegiatan" id="edit-kegiatan-tanggal" required></div>
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
                <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" class="btn" onclick="closeModal('edit-kegiatan')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <div id="modal-add-artikel" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('add-artikel')">&times;</button>
            <h2>Tambah Artikel Baru</h2>
            <form method="POST" action="admin_actions.php">
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
                <div class="form-group"><label>Gambar Artikel (nama file)</label><input type="text" name="gambar_artikel" placeholder="contoh: artikel1.jpg"></div>
                <div class="form-group"><label>Isi Artikel *</label><textarea name="isi_artikel" rows="12" required></textarea></div>
                <button type="submit" class="btn btn-success">💾 Simpan Artikel</button>
                <button type="button" class="btn" onclick="closeModal('add-artikel')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <div id="modal-edit-artikel" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('edit-artikel')">&times;</button>
            <h2>Edit Artikel</h2>
            <form method="POST" action="admin_actions.php">
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
                <div class="form-group"><label>Gambar Artikel (nama file)</label><input type="text" name="gambar_artikel" id="edit-artikel-gambar"></div>
                <div class="form-group"><label>Isi Artikel *</label><textarea name="isi_artikel" id="edit-artikel-isi" rows="12" required></textarea></div>
                <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" class="btn" onclick="closeModal('edit-artikel')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <div id="modal-view-pesan" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="modal-close" onclick="closeModal('view-pesan')">&times;</button>
            <h2>Detail Pesan</h2>
            <div id="pesan-detail-content"><div class="loading">Memuat data...</div></div>
        </div>
    </div>

    <script>
    function postAction(action, data = {}) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin_actions.php';
        let html = `<input type="hidden" name="action" value="${action}">`;
        for (const key in data) html += `<input type="hidden" name="${key}" value="${data[key]}">`;
        form.innerHTML = html;
        document.body.appendChild(form);
        form.submit();
    }

    function showTab(tabName, event) {
        event.preventDefault();
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
        document.querySelectorAll('.admin-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.admin-nav-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        event.target.classList.add('active');
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
            document.getElementById('edit-telepon').value = d.telepon_user;
            document.getElementById('edit-alamat').value = d.alamat_user || '';
            document.getElementById('edit-bidang').value = d.bidang_user;
            showModal('edit-user');
        });
    }

    function deleteUser(id, name) { if (confirm(`Yakin hapus user: ${name}?`)) postAction('delete_user', { id }); }

    function editKegiatan(id) {
        fetch('admin.php?ajax=get_kegiatan&id=' + id).then(r => r.json()).then(d => {
            if (d.error) return alert(d.error);
            document.getElementById('edit-kegiatan-id').value = d.id_kegiatan;
            document.getElementById('edit-kegiatan-judul').value = d.judul_kegiatan;
            document.getElementById('edit-kegiatan-tanggal').value = d.tanggal_kegiatan;
            document.getElementById('edit-kegiatan-lokasi').value = d.lokasi_kegiatan;
            document.getElementById('edit-kegiatan-deskripsi').value = d.deskripsi_kegiatan;
            document.getElementById('edit-kegiatan-status').value = d.status_kegiatan;
            showModal('edit-kegiatan');
        });
    }

    function deleteKegiatan(id, judul) { if (confirm(`Hapus kegiatan: ${judul}?`)) postAction('delete_kegiatan', { id }); }

    function editArtikel(id) {
        fetch('admin.php?ajax=get_artikel&id=' + id).then(r => r.json()).then(d => {
            if (d.error) return alert(d.error);
            document.getElementById('edit-artikel-id').value = d.id_artikel;
            document.getElementById('edit-artikel-kategori').value = d.kategori_artikel;
            document.getElementById('edit-artikel-judul').value = d.judul_artikel;
            document.getElementById('edit-artikel-tanggal').value = d.tanggal_artikel;
            document.getElementById('edit-artikel-sumber').value = d.sumber_artikel;
            document.getElementById('edit-artikel-gambar').value = d.gambar_artikel || '';
            document.getElementById('edit-artikel-isi').value = d.isi_artikel;
            showModal('edit-artikel');
        });
    }

    function deleteartikel(id, judul) { if (confirm(`Hapus artikel: ${judul}?`)) postAction('delete_artikel', { id }); }

    function editTim(id) { window.location.href = 'admin.php?tab=tim&edit_tim=' + id; }
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
    </script>
</body>
</html>