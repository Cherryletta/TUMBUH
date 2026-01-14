<?php 
session_start();
include __DIR__ . '/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle upload foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_foto') {
    $id_kegiatan = intval($_POST['id_kegiatan'] ?? 0);
    $deskripsi = trim($_POST['deskripsi_galeri'] ?? '');
    
    if ($id_kegiatan <= 0) {
        $message = '❌ Silakan pilih kegiatan';
        $message_type = 'error';
    } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_name = basename($_FILES['foto']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['foto']['size'];
        
        // Validasi tipe file
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_ext, $allowed_ext)) {
            $message = '❌ Hanya file JPG, PNG, GIF, atau WEBP yang diperbolehkan';
            $message_type = 'error';
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB
            $message = '❌ Ukuran file terlalu besar (max 5MB)';
            $message_type = 'error';
        } else {
            // Generate nama file unik
            $new_file_name = 'foto_' . $id_kegiatan . '_' . time() . '.' . $file_ext;
            $upload_dir = __DIR__ . '/assets/img/galeri/';
            $upload_path = $upload_dir . $new_file_name;
            $file_url = 'assets/img/galeri/' . $new_file_name;
            
            // Buat folder jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Upload file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Insert ke database
                $query = "INSERT INTO foto_galeri (id_kegiatan, foto_galeri, deskripsi_galeri) 
                         VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'iss', $id_kegiatan, $file_url, $deskripsi);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = '✅ Foto berhasil diunggah!';
                        $message_type = 'success';
                    } else {
                        $message = '❌ Error saat menyimpan ke database: ' . mysqli_stmt_error($stmt);
                        $message_type = 'error';
                        unlink($upload_path); // Hapus file jika gagal insert
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = '❌ Error prepare statement';
                    $message_type = 'error';
                    unlink($upload_path);
                }
            } else {
                $message = '❌ Error saat mengupload file';
                $message_type = 'error';
            }
        }
    } else {
        $message = '❌ Silakan pilih file';
        $message_type = 'error';
    }
}

// Handle hapus foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_foto') {
    $id_foto = intval($_POST['id_foto'] ?? 0);
    
    // Ambil path file
    $query = "SELECT foto_url FROM foto_galeri WHERE id_foto = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_foto);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $foto = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($foto) {
        // Hapus dari database
        $delete_query = "DELETE FROM foto_galeri WHERE id_foto = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'i', $id_foto);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            // Hapus file
            $file_path = __DIR__ . '/' . $foto['foto_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $message = '✅ Foto berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = '❌ Error saat menghapus foto';
            $message_type = 'error';
        }
        mysqli_stmt_close($delete_stmt);
    }
}

// Ambil daftar kegiatan
$kegiatan_query = mysqli_query($conn, "SELECT id_kegiatan, judul FROM kegiatan ORDER BY created_at DESC");
$kegiatan_list = mysqli_fetch_all($kegiatan_query, MYSQLI_ASSOC);

// Ambil semua foto galeri dengan info kegiatan
$foto_query = "SELECT f.*, k.judul FROM foto_galeri f 
              JOIN kegiatan k ON f.id_kegiatan = k.id_kegiatan 
              ORDER BY f.tanggal_upload DESC";
$all_fotos = mysqli_query($conn, $foto_query);
$fotos_list = mysqli_fetch_all($all_fotos, MYSQLI_ASSOC);

include __DIR__ . '/inc/header.php';
?>

<div class="container">
    <!-- Heading -->
    <div style="margin-top: 2rem; margin-bottom: 2rem;">
        <h2 style="color: #2d5016; font-size: 2rem; margin-bottom: 0.5rem;">📸 Kelola Galeri Foto</h2>
        <p style="color: #666; font-size: 0.95rem;">Upload dan kelola foto untuk setiap kegiatan</p>
    </div>

    <!-- Message Alert -->
    <?php if ($message): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; 
                    background: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>;
                    color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;
                    border-left: 4px solid <?php echo $message_type === 'success' ? '#28a745' : '#dc3545'; ?>;">
            <?php echo $message; ?>
        </div>
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
                                <?php echo $k['judul']; ?>
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
                <textarea name="deskripsi" placeholder="Tulis deskripsi foto..." 
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
                            <img src="<?php echo $foto['foto_url']; ?>" alt="Foto" 
                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                 onerror="this.src='assets/img/placeholder-galeri.jpg'">
                        </div>
                        
                        <!-- Info -->
                        <div style="padding: 1rem;">
                            <h5 style="color: #2d5016; margin: 0 0 0.5rem 0; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo $foto['judul']; ?>
                            </h5>
                            <p style="color: #666; font-size: 0.85rem; margin: 0 0 0.5rem 0; 
                                      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                📝 <?php echo $foto['deskripsi'] ?: 'Tidak ada deskripsi'; ?>
                            </p>
                            <p style="color: #999; font-size: 0.8rem; margin: 0 0 0.8rem 0;">
                                📅 <?php echo date('d M Y H:i', strtotime($foto['tanggal_upload'])); ?>
                            </p>
                            
                            <!-- Delete Button -->
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="delete_foto">
                                <input type="hidden" name="id_foto" value="<?php echo $foto['id_foto']; ?>">
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

<?php include __DIR__ . '/inc/footer.php'; ?>
