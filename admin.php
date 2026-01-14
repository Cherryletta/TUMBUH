<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Cek apakah user admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Handle form submission untuk Tim dan Galeri
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // ==================== HANDLE TIM ACTIONS ====================
    if (in_array($action, ['add_tim', 'edit_tim', 'delete_tim'])) {
        if ($action == 'add_tim' || $action == 'edit_tim') {
            $nama_tim = clean($_POST['nama_tim']);
            $posisi_tim = clean($_POST['posisi_tim']);
            $bio_tim = clean($_POST['bio_tim']);
            $ig_tim = isset($_POST['ig_tim']) ? clean($_POST['ig_tim']) : '';
            $x_tim = isset($_POST['x_tim']) ? clean($_POST['x_tim']) : '';
            $fb_tim = isset($_POST['fb_tim']) ? clean($_POST['fb_tim']) : '';
            $photo_tim = '';
            
            // Handle file upload
            if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
                $file = $_FILES['photo'];
                $file_name = $file['name'];
                $file_size = $file['size'];
                $file_tmp = $file['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                if ($file_size > 5 * 1024 * 1024) {
                    $message = "❌ Ukuran file terlalu besar (max 5MB)";
                    $message_type = "error";
                } elseif (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $message = "❌ Format file tidak didukung (gunakan JPG, PNG, GIF, WEBP)";
                    $message_type = "error";
                } else {
                    $new_file_name = 'tim_' . uniqid() . '.' . $file_ext;
                    $upload_path = __DIR__ . '/assets/img/tim/' . $new_file_name;
                    
                    if (!is_dir(__DIR__ . '/assets/img/tim/')) {
                        mkdir(__DIR__ . '/assets/img/tim/', 0755, true);
                    }
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $photo_tim = $new_file_name;
                    } else {
                        $message = "❌ Gagal upload file";
                        $message_type = "error";
                    }
                }
            }
            
            if ($action == 'add_tim') {
                $query = "INSERT INTO tim (nama_tim, posisi_tim, bio_tim, ig_tim, x_tim, fb_tim" . ($photo_tim ? ", photo_tim" : "") . ") 
                          VALUES ('$nama_tim', '$posisi_tim', '$bio_tim', '$ig_tim', '$x_tim', '$fb_tim'" . ($photo_tim ? ", '$photo_tim'" : "") . ")";
                
                if (mysqli_query($conn, $query)) {
                    header("Location: admin.php?tab=tim&tim_added=1");
                    exit();
                } else {
                    $message = "❌ Gagal menambahkan tim: " . mysqli_error($conn);
                    $message_type = "error";
                }
            } elseif ($action == 'edit_tim') {
                $id_tim = (int)$_POST['id_tim'];
                $old_query = mysqli_query($conn, "SELECT photo_tim FROM tim WHERE id_tim = $id_tim");
                $old_data = mysqli_fetch_assoc($old_query);
                
                if ($photo_tim) {
                    if (!empty($old_data['photo_tim'])) {
                        $old_file = __DIR__ . '/assets/img/tim/' . $old_data['photo_tim'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    $query = "UPDATE tim SET nama_tim = '$nama_tim', posisi_tim = '$posisi_tim', 
                             bio_tim = '$bio_tim', ig_tim = '$ig_tim', x_tim = '$x_tim', 
                             fb_tim = '$fb_tim', photo_tim = '$photo_tim' WHERE id_tim = $id_tim";
                } else {
                    $query = "UPDATE tim SET nama_tim = '$nama_tim', posisi_tim = '$posisi_tim', 
                             bio_tim = '$bio_tim', ig_tim = '$ig_tim', x_tim = '$x_tim', 
                             fb_tim = '$fb_tim' WHERE id_tim = $id_tim";
                }
                
                if (mysqli_query($conn, $query)) {
                    header("Location: admin.php?tab=tim&tim_updated=1");
                    exit();
                } else {
                    $message = "❌ Gagal memperbarui tim: " . mysqli_error($conn);
                    $message_type = "error";
                }
            }
        } elseif ($action == 'delete_tim') {
            $id_tim = (int)$_POST['id_tim'];
            $photo_query = mysqli_query($conn, "SELECT photo_tim FROM tim WHERE id_tim = $id_tim");
            $photo_data = mysqli_fetch_assoc($photo_query);
            
            if (!empty($photo_data['photo_tim'])) {
                $photo_file = __DIR__ . '/assets/img/tim/' . $photo_data['photo_tim'];
                if (file_exists($photo_file)) {
                    unlink($photo_file);
                }
            }
            
            if (mysqli_query($conn, "DELETE FROM tim WHERE id_tim = $id_tim")) {
                header("Location: admin.php?tab=tim&tim_deleted=1");
                exit();
            } else {
                $message = "❌ Gagal menghapus tim: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    }
    
    // ==================== HANDLE GALERI ACTIONS ====================
    if ($action == 'upload_foto') {
        $id_kegiatan = intval($_POST['id_kegiatan'] ?? 0);
        $deskripsi_galeri = trim($_POST['deskripsi_galeri'] ?? '');
        
        if ($id_kegiatan <= 0) {
            $message = '❌ Silakan pilih kegiatan';
            $message_type = 'error';
        } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = basename($_FILES['foto']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_size = $_FILES['foto']['size'];
            
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($file_ext, $allowed_ext)) {
                $message = '❌ Hanya file JPG, PNG, GIF, atau WEBP yang diperbolehkan';
                $message_type = 'error';
            } elseif ($file_size > 5 * 1024 * 1024) {
                $message = '❌ Ukuran file terlalu besar (max 5MB)';
                $message_type = 'error';
            } else {
                $new_file_name = 'foto_' . $id_kegiatan . '_' . time() . '.' . $file_ext;
                $upload_dir = __DIR__ . '/assets/img/galeri/';
                $upload_path = $upload_dir . $new_file_name;
                $file_url = 'assets/img/galeri/' . $new_file_name;
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $query = "INSERT INTO galeri (id_kegiatan, foto_galeri, deskripsi_galeri) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 'iss', $id_kegiatan, $file_url, $deskripsi_galeri);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            header("Location: admin.php?tab=galeri&foto_uploaded=1");
                            exit();
                        } else {
                            $message = '❌ Error saat menyimpan ke database';
                            $message_type = 'error';
                            unlink($upload_path);
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }
    
    if ($action == 'delete_foto') {
        $id_foto = intval($_POST['id_foto'] ?? 0);
        $query = "SELECT foto_galeri FROM galeri WHERE id_galeri = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_foto);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $foto = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($foto) {
            $delete_query = "DELETE FROM galeri WHERE id_galeri = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, 'i', $id_foto);
            
            if (mysqli_stmt_execute($delete_stmt)) {
                $file_path = __DIR__ . '/' . $foto['foto_galeri'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                header("Location: admin.php?tab=galeri&foto_deleted=1");
                exit();
            }
            mysqli_stmt_close($delete_stmt);
        }
    }
}

// ==================== AMBIL DATA ====================
$users_query = mysqli_query($conn, "SELECT * FROM users WHERE role_user = 'user' ORDER BY tanggal_daftar_user DESC");
$users_list = [];
while ($row = mysqli_fetch_assoc($users_query)) {
    $users_list[] = $row;
}

$kegiatan_query = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY created_at_kegiatan DESC");
$kegiatan_list = [];
while ($row = mysqli_fetch_assoc($kegiatan_query)) {
    $kegiatan_list[] = $row;
}

$berita_query = mysqli_query($conn, "SELECT * FROM berita ORDER BY created_at_berita DESC");
$berita_list = [];
while ($row = mysqli_fetch_assoc($berita_query)) {
    $berita_list[] = $row;
}

$tim_query = mysqli_query($conn, "SELECT * FROM tim ORDER BY id_tim ASC");
$tim_list = [];
while ($row = mysqli_fetch_assoc($tim_query)) {
    $tim_list[] = $row;
}

$foto_query = "SELECT f.*, k.judul_kegiatan 
               FROM galeri f
               JOIN kegiatan k ON f.id_kegiatan = k.id_kegiatan
               ORDER BY f.tanggal_upload_galeri DESC";

$all_fotos = mysqli_query($conn, $foto_query);
$fotos_list = [];
while ($row = mysqli_fetch_assoc($all_fotos)) {
    $fotos_list[] = $row;
}

$stat_query = mysqli_query($conn, "SELECT * FROM statistik");
$statistik = [];
while ($row = mysqli_fetch_assoc($stat_query)) {
    $statistik[$row['nama_stat']] = $row['nilai'];
}

$edit_tim = null;
if (isset($_GET['edit_tim'])) {
    $id_tim = (int)$_GET['edit_tim'];
    $edit_query = mysqli_query($conn, "SELECT * FROM tim WHERE id_tim = $id_tim");
    $edit_tim = mysqli_fetch_assoc($edit_query);
}

// Ambil data pesan kontak
$pesan_query = mysqli_query($conn, "SELECT * FROM kontak_pesan ORDER BY tanggal_pesan DESC");
$pesan_list = [];
while ($row = mysqli_fetch_assoc($pesan_query)) {
    $pesan_list[] = $row;
}

// Hitung pesan belum dibaca
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
    <!-- ==================== NAVIGATION ==================== -->
    <nav class="admin-nav">
        <div class="nav-brand">
            <h1>🌱 TUMBUH Admin</h1>
        </div>
        <div class="nav-links">
            <a href="index.php">← Kembali ke Website</a>
            <span class="admin-user">👤 <?php echo $_SESSION['user_name']; ?></span>
            <a href="auth/logout.php" class="btn-logout">Keluar</a>
        </div>
    </nav>

    <div class="admin-container">
        <!-- ==================== SIDEBAR NAVIGATION ==================== -->
        <div class="admin-sidebar">
            <button class="admin-nav-btn <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'statistik') ? 'active' : ''; ?>" onclick="showTab('statistik', event)">📊 Dashboard</button>
            <button class="admin-nav-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'relawan') ? 'active' : ''; ?>" onclick="showTab('relawan', event)">👥 Kelola Relawan</button>
            <button class="admin-nav-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'kegiatan') ? 'active' : ''; ?>" onclick="showTab('kegiatan', event)">📅 Kelola Kegiatan</button>
            <button class="admin-nav-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'berita') ? 'active' : ''; ?>" onclick="showTab('berita', event)">📰 Kelola Berita</button>
            <button class="admin-nav-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'tim') ? 'active' : ''; ?>" onclick="showTab('tim', event)">👤 Kelola Tim</button>
            <button class="admin-nav-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'galeri') ? 'active' : ''; ?>" onclick="showTab('galeri', event)">📸 Kelola Galeri</button>
            <button class="admin-nav-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'pesan') ? 'active' : ''; ?>" onclick="showTab('pesan', event)">
                📧 Kelola Pesan 
                <?php if ($pesan_belum_dibaca > 0): ?>
                    <span style="background: #ff9800; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; margin-left: 5px;">
                        <?php echo $pesan_belum_dibaca; ?>
                    </span>
                <?php endif; ?>
            </button>
        </div>

        <!-- ==================== MAIN CONTENT ==================== -->
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
                            <h3><?php echo count($berita_list); ?></h3>
                            <p>Total Berita</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="card">
                        <h3>Statistik Lengkap</h3>
                        <div class="stat-item">
                            <span>Total Kegiatan</span>
                            <strong><?php echo count($kegiatan_list); ?></strong>
                        </div>
                        <div class="stat-item">
                            <span>Status Relawan Aktif</span>
                            <strong><?php echo count($users_list); ?></strong>
                        </div>
                        <div class="stat-item">
                            <span>Total Publikasi</span>
                            <strong><?php echo count($berita_list); ?></strong>
                        </div>
                        <div class="stat-item">
                            <span>Total Anggota Tim</span>
                            <strong><?php echo count($tim_list); ?></strong>
                        </div>
                        <div class="stat-item">
                            <span>Total Foto Galeri</span>
                            <strong><?php echo count($fotos_list); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB RELAWAN -->
            <div id="tab-relawan" class="admin-content <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'relawan') ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Relawan</h2>
                    <p class="subtitle">Daftar semua relawan yang terdaftar di TUMBUH</p>
                </div>

                <?php if (isset($_GET['user_deleted'])): ?>
                    <div class="alert success">✅ User berhasil dihapus!</div>
                <?php endif; ?>
                <?php if (isset($_GET['user_updated'])): ?>
                    <div class="alert success">✅ User berhasil diupdate!</div>
                <?php endif; ?>

                <div class="card">
                    <?php if (empty($users_list)): ?>
                        <div class="no-data">Belum ada relawan yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Bidang</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
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
            <div id="tab-kegiatan" class="admin-content <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'kegiatan') ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Kegiatan</h2>
                    <p class="subtitle">Mengelola semua kegiatan di TUMBUH</p>
                </div>

                <?php if (isset($_GET['kegiatan_added'])): ?>
                    <div class="alert success">✅ Kegiatan berhasil ditambahkan!</div>
                <?php endif; ?>
                <?php if (isset($_GET['kegiatan_deleted'])): ?>
                    <div class="alert success">✅ Kegiatan berhasil dihapus!</div>
                <?php endif; ?>

                <div class="card">
                    <button class="btn btn-add" onclick="showModal('add-kegiatan')" style="margin-bottom: 1.5rem;">➕ Tambah Kegiatan Baru</button>

                    <?php if (empty($kegiatan_list)): ?>
                        <div class="no-data">Belum ada kegiatan yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Tanggal</th>
                                        <th>Lokasi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
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

            <!-- TAB BERITA -->
            <div id="tab-berita" class="admin-content <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'berita') ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Berita</h2>
                    <p class="subtitle">Mengelola semua berita dan publikasi</p>
                </div>

                <?php if (isset($_GET['berita_added'])): ?>
                    <div class="alert success">✅ Berita berhasil ditambahkan!</div>
                <?php endif; ?>
                <?php if (isset($_GET['berita_deleted'])): ?>
                    <div class="alert success">✅ Berita berhasil dihapus!</div>
                <?php endif; ?>

                <div class="card">
                    <button class="btn btn-add" onclick="showModal('add-berita')" style="margin-bottom: 1.5rem;">➕ Tambah Berita Baru</button>

                    <?php if (empty($berita_list)): ?>
                        <div class="no-data">Belum ada berita yang terdaftar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Tanggal</th>
                                        <th>Sumber</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($berita_list as $berita): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $berita['judul_berita']; ?></td>
                                            <td><?php echo $berita['tanggal_berita']; ?></td>
                                            <td><?php echo $berita['sumber_berita']; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" onclick="deleteBerita(<?php echo $berita['id_berita']; ?>, '<?php echo addslashes($berita['judul_berita']); ?>')">Hapus</button>
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
            <div id="tab-tim" class="admin-content <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'tim') ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Tim</h2>
                    <p class="subtitle">Tambah, edit, atau hapus anggota tim</p>
                </div>

                <?php if (isset($_GET['tim_added'])): ?>
                    <div class="alert success">✅ Tim berhasil ditambahkan!</div>
                <?php endif; ?>
                <?php if (isset($_GET['tim_updated'])): ?>
                    <div class="alert success">✅ Tim berhasil diperbarui!</div>
                <?php endif; ?>
                <?php if (isset($_GET['tim_deleted'])): ?>
                    <div class="alert success">✅ Tim berhasil dihapus!</div>
                <?php endif; ?>
                <?php if ($message): ?>
                    <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Form Tambah/Edit Tim -->
                <div class="tim-form-section">
                    <h3><?php echo $edit_tim ? '✏️ Edit Tim' : '➕ Tambah Tim Baru'; ?></h3>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_tim ? 'edit_tim' : 'add_tim'; ?>">
                        <?php if ($edit_tim): ?>
                            <input type="hidden" name="id_tim" value="<?php echo $edit_tim['id_tim']; ?>">
                        <?php endif; ?>

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
                                <label>Instagram (username saja, tanpa @)</label>
                                <input type="text" name="ig_tim" placeholder="contoh: nama_akun" value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['ig_tim']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>X/Twitter (username saja, tanpa @)</label>
                                <input type="text" name="x_tim" placeholder="contoh: nama_akun" value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['x_tim']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Facebook (username saja)</label>
                            <input type="text" name="fb_tim" placeholder="contoh: nama.akun" value="<?php echo $edit_tim ? htmlspecialchars($edit_tim['fb_tim']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Foto Profil <?php echo !$edit_tim ? '*' : '(Biarkan kosong jika tidak ingin mengubah)'; ?></label>
                            <div class="file-input-wrapper">
                                <input type="file" id="photo" name="photo" accept="image/*" <?php echo !$edit_tim ? 'required' : ''; ?>>
                                <label for="photo" class="file-input-label">
                                    📸 Klik untuk memilih foto (JPG, PNG, GIF, WEBP - max 5MB)
                                </label>
                                <div class="file-name" id="file-name"></div>
                            </div>
                            <?php if ($edit_tim && !empty($edit_tim['photo_tim'])): ?>
                                <div style="margin-top: 1rem;">
                                    <p style="font-size: 0.9rem; color: #666;">Foto saat ini:</p>
                                    <img src="assets/img/tim/<?php echo htmlspecialchars($edit_tim['photo_tim']); ?>" 
                                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #4a7c29;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn btn-success">
                                <?php echo $edit_tim ? '💾 Perbarui Tim' : '➕ Tambah Tim'; ?>
                            </button>
                            <?php if ($edit_tim): ?>
                                <a href="admin.php?tab=tim" class="btn-cancel">← Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Tabel Daftar Tim -->
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem; color: #2d5016;">📋 Daftar Tim (<?php echo count($tim_list); ?> orang)</h3>
                    
                    <?php if (count($tim_list) > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Nama</th>
                                        <th>Posisi</th>
                                        <th>Biografi</th>
                                        <th>Aksi</th>
                                    </tr>
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
                                                        if (count($parts) >= 2) {
                                                            echo strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                                        } else {
                                                            echo strtoupper(substr($tim['nama_tim'], 0, 2));
                                                        }
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
                        <div class="no-data">📭 Belum ada data tim. Mulai dengan menambah anggota tim baru! 👆</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ==================== TAB GALERI ==================== -->
            <div id="tab-galeri" class="admin-content <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'galeri') ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Galeri Foto</h2>
                    <p class="subtitle">Upload dan kelola foto untuk setiap kegiatan</p>
                </div>

                <?php if (isset($_GET['foto_uploaded'])): ?>
                    <div class="alert success">✅ Foto berhasil diunggah!</div>
                <?php endif; ?>
                <?php if (isset($_GET['foto_deleted'])): ?>
                    <div class="alert success">✅ Foto berhasil dihapus!</div>
                <?php endif; ?>
                <?php if ($message && isset($_GET['tab']) && $_GET['tab'] == 'galeri'): ?>
                    <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="color: #2d5016; margin-bottom: 1.5rem; font-size: 1.3rem;">📤 Upload Foto Baru</h3>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_foto">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <!-- Pilih Kegiatan -->
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">
                                    Pilih Kegiatan <span style="color: #dc3545;">*</span>
                                </label>
                                <select name="id_kegiatan" required style="width: 100%; padding: 0.7rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                    <option value="">-- Pilih Kegiatan --</option>
                                    <?php foreach ($kegiatan_list as $k): ?>
                                        <option value="<?php echo $k['id_kegiatan']; ?>">
                                            <?php echo $k['judul_kegiatan']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Pilih Foto -->
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">
                                    Pilih Foto <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" required 
                                       style="padding: 0.7rem; border: 1px solid #ddd; border-radius: 4px; width: 100%; font-size: 1rem;">
                            </div>
                        </div>
                        
                        <!-- Deskripsi -->
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">
                                Deskripsi (Opsional)
                            </label>
                            <textarea name="deskripsi_galeri" placeholder="Tulis deskripsi foto..." 
                                      style="width: 100%; padding: 0.7rem; border: 1px solid #ddd; border-radius: 4px; min-height: 80px; font-family: inherit; font-size: 1rem;"></textarea>
                        </div>
                        
                        <!-- Catatan -->
                        <div style="background: #e8f5e9; padding: 0.8rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.9rem; color: #2d5016;">
                            📋 <strong>Format:</strong> JPG, PNG, GIF, atau WEBP | <strong>Max:</strong> 5MB | <strong>Rekomendasi:</strong> 1200x800px atau lebih
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" style="width: 100%; padding: 0.9rem; background: linear-gradient(135deg, #2d5016 0%, #4a7c29 100%); color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: all 0.3s ease;">
                            📤 Upload Foto
                        </button>
                    </form>
                </div>

                <!-- Daftar Foto Existing -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #2d5016; margin-bottom: 1.5rem; font-size: 1.3rem;">📷 Daftar Foto (Total: <?php echo count($fotos_list); ?>)</h3>
                    
                    <?php if (count($fotos_list) > 0): ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                            <?php foreach ($fotos_list as $foto): ?>
                                <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #f9f9f9; transition: all 0.3s ease;"
                                     onmouseover="this.style.boxShadow='0 4px 15px rgba(0,0,0,0.15)'" 
                                     onmouseout="this.style.boxShadow='none'">
                                    
                                    <!-- Thumbnail -->
                                    <div style="width: 100%; height: 200px; overflow: hidden; background: #e0e0e0;">
                                        <img src="<?php echo $foto['foto_galeri']; ?>" alt="Foto" 
                                             style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                             onerror="this.src='assets/img/placeholder-galeri.jpg'">
                                    </div>
                                    
                                    <!-- Info -->
                                    <div style="padding: 1rem;">
                                        <h5 style="color: #2d5016; margin: 0 0 0.5rem 0; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo $foto['judul_kegiatan']; ?>
                                        </h5>
                                        <p style="color: #666; font-size: 0.85rem; margin: 0 0 0.5rem 0; 
                                                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            📝 <?php echo $foto['deskripsi_galeri'] ?: 'Tidak ada deskripsi'; ?>
                                        </p>
                                        <p style="color: #999; font-size: 0.8rem; margin: 0 0 0.8rem 0;">
                                            📅 <?php echo date('d M Y H:i', strtotime($foto['tanggal_upload_galeri'])); ?>
                                        </p>
                                        
                                        <!-- Delete Button -->
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="delete_foto">
                                            <input type="hidden" name="id_foto" value="<?php echo $foto['id_galeri']; ?>">
                                            <button type="submit" onclick="return confirm('Hapus foto ini?');" 
                                                    style="width: 100%; padding: 0.6rem; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #999;">
                            <p style="font-size: 1rem;">📸 Belum ada foto yang diunggah</p>
                            <p style="font-size: 0.9rem;">Upload foto pertama Anda di atas!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
            <!-- Tab Pesan -->
            <div id="tab-pesan" class="admin-content <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'pesan') ? 'active' : ''; ?>">
                <div class="content-header">
                    <h2>Kelola Pesan Kontak</h2>
                    <p class="subtitle">Pesan yang masuk dari formulir kontak website</p>
                </div>

                <?php if (isset($_GET['pesan_deleted'])): ?>
                    <div class="alert success">✅ Pesan berhasil dihapus!</div>
                <?php endif; ?>
                
                <?php if (isset($_GET['pesan_read'])): ?>
                    <div class="alert success">✅ Pesan ditandai sudah dibaca!</div>
                <?php endif; ?>

                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0; color: #2d5016;">
                            📬 Total Pesan: <?php echo count($pesan_list); ?> 
                            <?php if ($pesan_belum_dibaca > 0): ?>
                                <span style="color: #ff9800;">(<?php echo $pesan_belum_dibaca; ?> belum dibaca)</span>
                            <?php endif; ?>
                        </h3>
                        <?php if (count($pesan_list) > 0): ?>
                            <button class="btn btn-sm" onclick="markAllRead()"
                                    style="background: #4a7c29;">
                                ✓ Tandai Semua Dibaca
                            </button>
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
                                        <tr style="<?php echo $pesan['status_pesan'] == 'belum_dibaca' ? 'background: #fff9e6;' : ''; ?>">
                                            <td style="text-align: center;">
                                                <?php if ($pesan['status_pesan'] == 'belum_dibaca'): ?>
                                                    <span style="color: #ff9800; font-size: 1.3rem;" title="Belum Dibaca">●</span>
                                                <?php else: ?>
                                                    <span style="color: #ccc; font-size: 1.3rem;" title="Sudah Dibaca">○</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pesan['tanggal_pesan'])); ?></td>
                                            <td><strong><?php echo htmlspecialchars($pesan['nama_pesan']); ?></strong></td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($pesan['email_pesan']); ?>" 
                                                style="color: #4a7c29; text-decoration: none;">
                                                    <?php echo htmlspecialchars($pesan['email_pesan']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if (!empty($pesan['telepon_pesan'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($pesan['telepon_pesan']); ?>" 
                                                    style="color: #4a7c29; text-decoration: none;">
                                                        <?php echo htmlspecialchars($pesan['telepon_pesan']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color: #999;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($pesan['subjek_pesan']); ?></td>
                                            <td>
                                                <button class="btn btn-sm" onclick="viewPesan(<?php echo $pesan['id_pesan']; ?>)" 
                                                        style="background: #4a7c29; margin-right: 5px;">
                                                    Lihat
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="deletePesan(<?php echo $pesan['id_pesan']; ?>, '<?php echo addslashes($pesan['nama_pesan']); ?>')">
                                                    Hapus
                                                </button>
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
            <h2>Edit Data User</h2>
            <form method="POST" action="admin_actions.php">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit-user-id">
                
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama_user" id="edit-nama" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email_user" id="edit-email" required>
                </div>
                
                <div class="form-group">
                    <label>Telepon *</label>
                    <input type="tel" name="telepon_user" id="edit-telepon" required>
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat_user" id="edit-alamat" rows="3"></textarea>
                </div>
                
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

    <!-- Modal Tambah Kegiatan -->
    <div id="modal-add-kegiatan" class="modal">
        <div class="modal-content">
            <h2>Tambah Kegiatan Baru</h2>
            <form method="POST" action="admin_actions.php">
                <input type="hidden" name="action" value="add_kegiatan">
                
                <div class="form-group">
                    <label>Judul Kegiatan *</label>
                    <input type="text" name="judul_kegiatan" placeholder="Contoh: Penanaman 500 Pohon" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal *</label>
                    <input type="text" name="tanggal_kegiatan" placeholder="Contoh: 25 Desember 2024" required>
                </div>
                
                <div class="form-group">
                    <label>Lokasi *</label>
                    <input type="text" name="lokasi_kegiatan" placeholder="Contoh: Gunung Merapi, Yogyakarta" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi *</label>
                    <textarea name="deskripsi_kegiatan" rows="4" placeholder="Deskripsi kegiatan..." required></textarea>
                </div>
                
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

    <!-- Modal Tambah Berita -->
    <div id="modal-add-berita" class="modal">
        <div class="modal-content">
            <h2>Tambah Berita Baru</h2>
            <form method="POST" action="admin_actions.php">
                <input type="hidden" name="action" value="add_berita">
                
                <div class="form-group">
                    <label>Judul Berita *</label>
                    <input type="text" name="judul_berita" placeholder="Judul berita..." required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal *</label>
                    <input type="text" name="tanggal_berita" placeholder="Contoh: 18 Desember 2024" required>
                </div>
                
                <div class="form-group">
                    <label>Sumber *</label>
                    <input type="text" name="sumber_berita" placeholder="Contoh: Kompas.com" required>
                </div>
                
                <div class="form-group">
                    <label>Isi Berita *</label>
                    <textarea name="isi_berita" rows="5" placeholder="Isi berita lengkap..." required></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">💾 Simpan Berita</button>
                <button type="button" class="btn" onclick="closeModal('add-berita')" style="background: #666; color: white;">✖️ Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Detail Pesan -->
    <div id="modal-view-pesan" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="modal-close" onclick="closeModal('view-pesan')">&times;</button>
            <h2>Detail Pesan</h2>
            
            <div id="pesan-detail-content">
                <div class="loading">Memuat data...</div>
            </div>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT ==================== -->
    <script>
    /* ==================== HELPER POST ACTION ==================== */
    function postAction(action, data = {}) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin_actions.php';

        let html = `<input type="hidden" name="action" value="${action}">`;
        for (const key in data) {
            html += `<input type="hidden" name="${key}" value="${data[key]}">`;
        }

        form.innerHTML = html;
        document.body.appendChild(form);
        form.submit();
    }

    /* ==================== TAB HANDLING ==================== */
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

    /* ==================== MODAL ==================== */
    function showModal(name) {
        document.getElementById('modal-' + name).classList.add('active');
    }
    function closeModal(name) {
        document.getElementById('modal-' + name).classList.remove('active');
    }
    window.onclick = function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    };

    /* ==================== USER ==================== */
    function editUser(userId) {
        fetch('get_user.php?id=' + userId)
            .then(r => r.json())
            .then(d => {
                if (d.error) return alert(d.error);
                editUserId.value = d.id_user;
                editNama.value = d.nama_user;
                editEmail.value = d.email_user;
                editTelepon.value = d.telepon_user;
                editAlamat.value = d.alamat_user || '';
                editBidang.value = d.bidang_user;
                showModal('edit-user');
            });
    }

    function deleteUser(id, name) {
        if (confirm(`Yakin hapus user: ${name}?`)) {
            postAction('delete_user', { id });
        }
    }

    /* ==================== KEGIATAN ==================== */
    function deleteKegiatan(id, judul) {
        if (confirm(`Hapus kegiatan: ${judul}?`)) {
            postAction('delete_kegiatan', { id });
        }
    }

    /* ==================== BERITA ==================== */
    function deleteBerita(id, judul) {
        if (confirm(`Hapus berita: ${judul}?`)) {
            postAction('delete_berita', { id });
        }
    }

    /* ==================== TIM ==================== */
    function editTim(id) {
        window.location.href = 'admin.php?tab=tim&edit_tim=' + id;
    }

    function deleteTim(id, nama) {
        if (confirm(`Hapus tim: ${nama}?`)) {
            postAction('delete_tim', { id_tim: id });
        }
    }

    /* ==================== PESAN ==================== */
    function deletePesan(id, nama) {
        if (confirm(`Hapus pesan dari: ${nama}?`)) {
            postAction('delete_pesan', { id });
        }
    }

    function markAsRead(id) {
        if (confirm('Tandai pesan sebagai sudah dibaca?')) {
            postAction('mark_read_pesan', { id });
        }
    }

    function markAllRead() {
        if (confirm('Tandai semua pesan sudah dibaca?')) {
            postAction('mark_all_read_pesan');
        }
    }

    /* ==================== VIEW PESAN ==================== */
    function viewPesan(id) {
        showModal('view-pesan');
        fetch('get_pesan.php?id=' + id)
            .then(r => r.json())
            .then(d => {
                if (d.error) {
                    pesanDetailContent.innerHTML = `<div class="alert error">${d.error}</div>`;
                    return;
                }

                pesanDetailContent.innerHTML = `
                    <div class="card">
                        <p><strong>Nama:</strong> ${d.nama_pesan}</p>
                        <p><strong>Email:</strong> ${d.email_pesan}</p>
                        <p><strong>Subjek:</strong> ${d.subjek_pesan}</p>
                        <hr>
                        <p>${d.isi_pesan}</p>
                        <br>
                        ${d.status_pesan === 'belum_dibaca'
                            ? `<button class="btn btn-success" onclick="markAsRead(${d.id_pesan})">✓ Tandai Dibaca</button>`
                            : ''
                        }
                    </div>
                `;
            });
    }

    /* ==================== FILE INPUT ==================== */
    document.getElementById('photo')?.addEventListener('change', e => {
        document.getElementById('file-name').textContent =
            e.target.files[0] ? '✓ ' + e.target.files[0].name : '';
    });
    </script>

</body>
</html>