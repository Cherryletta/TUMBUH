<?php include __DIR__ . '/inc/header.php'; ?>

<?php 
// Jika tidak login, redirect ke beranda
if (! $is_logged_in) { 
    header('Location: index.php'); 
    exit(); 
}

// ========== HITUNG STATISTIK RELAWAN BERDASARKAN JENIS KEGIATAN ==========
$user_id = $current_user['id_user'];

// Query untuk menghitung berdasarkan jenis kegiatan
$query_stats = mysqli_query($conn, "
    SELECT 
        k.jenis_kegiatan,
        COUNT(*) as total
    FROM pendaftaran_kegiatan p
    INNER JOIN kegiatan k ON p.id_kegiatan = k.id_kegiatan
    WHERE p.id_user = $user_id
    GROUP BY k.jenis_kegiatan
");

// Inisialisasi array statistik
$stats = [
    'Penanaman' => 0,
    'Edukasi' => 0,
    'Kolaborasi' => 0,
    'Kampanye' => 0,
    'Lainnya' => 0
];

// Isi data dari database
while ($row = mysqli_fetch_assoc($query_stats)) {
    $stats[$row['jenis_kegiatan']] = $row['total'];
}

// Hitung total kegiatan
$total_kegiatan = array_sum($stats);

?>

<div class="container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="dashboard-header-content">
            <h1>👤 Dashboard Profil</h1>
            <p class="dashboard-subtitle">Kelola data dan informasi relawan Anda</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['profile_success'])): ?>
        <div class="alert alert-success" role="alert">
            <span class="alert-icon">✅</span>
            <span><?php echo $_SESSION['profile_success']; ?></span>
        </div>
        <?php unset($_SESSION['profile_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['profile_errors'])): ?>
        <div class="alert alert-error" role="alert">
            <span class="alert-icon">⚠️</span>
            <div>
                <?php foreach ($_SESSION['profile_errors'] as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        </div>
        <?php unset($_SESSION['profile_errors']); ?>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Profile Header with Avatar -->
            <div class="profile-header-box">
                <div class="avatar-container">
                    <div class="avatar">
                        <?php echo strtoupper(substr($current_user['nama_user'], 0, 2)); ?>
                    </div>
                </div>
                <div class="profile-header-info">
                    <h4><?php echo $current_user['nama_user']; ?></h4>
                    <p class="user-email"><?php echo $current_user['email_user']; ?></p>
                    <span class="bidang-badge">
                        <?php 
                        $bidang_labels = [
                            'penanaman' => '🌱 Penanaman Pohon', 
                            'edukasi' => '📚 Edukasi Lingkungan', 
                            'publikasi' => '📢 Publikasi & Media', 
                            'dokumentasi' => '📷 Dokumentasi'
                        ];
                        echo $bidang_labels[$current_user['bidang_user']] ?? '🌿 Relawan';
                        ?>
                    </span>
                </div>
                <button class="btn-edit-profile" onclick="toggleEditMode()" id="btn-edit-trigger">
                    ✏️ Edit Profil
                </button>
            </div>

            <!-- Profile Info Section (VIEW MODE) -->
            <div class="profile-info-section" id="profile-view">
                <h3>📋 Informasi Pribadi</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <label class="info-label">👤 Nama Lengkap</label>
                        <div class="info-value"><?php echo $current_user['nama_user']; ?></div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">📧 Email</label>
                        <div class="info-value"><?php echo $current_user['email_user']; ?></div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">📱 Telepon</label>
                        <div class="info-value"><?php echo $current_user['telepon_user']; ?></div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">📍 Alamat</label>
                        <div class="info-value"><?php echo $current_user['alamat_user'] ?: '<em>Belum diisi</em>'; ?></div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">🎯 Bidang Minat</label>
                        <div class="info-value">
                            <?php 
                            $bidang_labels_simple = [
                                'penanaman' => 'Penanaman Pohon', 
                                'edukasi' => 'Edukasi Lingkungan', 
                                'publikasi' => 'Publikasi & Media', 
                                'dokumentasi' => 'Dokumentasi'
                            ];
                            echo $bidang_labels_simple[$current_user['bidang_user']] ?? 'Relawan Umum';
                            ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">💡 Motivasi</label>
                        <div class="info-value"><?php echo $current_user['motivasi_user'] ?: '<em>Belum diisi</em>'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Edit Form Section (EDIT MODE) -->
            <form method="POST" action="auth/update_profile.php" id="profile-edit" class="profile-edit-form" style="display:none;">
                <h3>✏️ Edit Profil</h3>

                <div class="form-row-full">
                    <label for="nama">👤 Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?php echo $current_user['nama_user']; ?>" required>
                </div>

                <div class="form-row-full">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $current_user['email_user']; ?>" required>
                </div>

                <div class="form-row-full">
                    <label for="telepon">📱 Telepon</label>
                    <input type="tel" id="telepon" name="telepon" value="<?php echo $current_user['telepon_user']; ?>" required>
                </div>

                <div class="form-row-full">
                    <label for="alamat">📍 Alamat</label>
                    <textarea id="alamat" name="alamat" rows="2" placeholder="Masukkan alamat Anda"><?php echo $current_user['alamat_user']; ?></textarea>
                </div>

                <div class="form-row-full">
                    <label for="bidang">🎯 Bidang Minat</label>
                    <select id="bidang" name="bidang" required>
                        <option value="penanaman" <?php echo $current_user['bidang_user'] == 'penanaman' ? 'selected' : ''; ?>>🌱 Penanaman Pohon</option>
                        <option value="edukasi" <?php echo $current_user['bidang_user'] == 'edukasi' ? 'selected' : ''; ?>>📚 Edukasi Lingkungan</option>
                        <option value="publikasi" <?php echo $current_user['bidang_user'] == 'publikasi' ? 'selected' : ''; ?>>📢 Publikasi & Media</option>
                        <option value="dokumentasi" <?php echo $current_user['bidang_user'] == 'dokumentasi' ? 'selected' : ''; ?>>📷 Dokumentasi</option>
                    </select>
                </div>

                <div class="form-row-full">
                    <label for="motivasi">💡 Motivasi</label>
                    <textarea id="motivasi" name="motivasi" rows="3" placeholder="Bagikan motivasi Anda bergabung dengan TUMBUH..."><?php echo $current_user['motivasi_user']; ?></textarea>
                </div>

                <div class="form-actions-horizontal">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">❌ Batal</button>
                </div>
            </form>
        </div>

        <!-- Stats Sidebar -->
        <div class="profile-stats-sidebar">
            <h3>📊 Statistik Kontribusi</h3>
            
            <!-- Total Kegiatan -->
            <div class="stat-box-modern stat-total">
                <div class="stat-icon-modern">🌟</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $total_kegiatan; ?></div>
                    <div class="stat-label-modern">Total Kegiatan</div>
                </div>
            </div>

            <!-- Kegiatan per Jenis -->
            <div class="stat-box-modern stat-penanaman">
                <div class="stat-icon-modern">🌱</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $stats['Penanaman']; ?></div>
                    <div class="stat-label-modern">Penanaman</div>
                </div>
            </div>

            <div class="stat-box-modern stat-edukasi">
                <div class="stat-icon-modern">📚</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $stats['Edukasi']; ?></div>
                    <div class="stat-label-modern">Edukasi</div>
                </div>
            </div>

            <div class="stat-box-modern stat-kolaborasi">
                <div class="stat-icon-modern">🤝</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $stats['Kolaborasi']; ?></div>
                    <div class="stat-label-modern">Kolaborasi</div>
                </div>
            </div>

            <div class="stat-box-modern stat-kampanye">
                <div class="stat-icon-modern">📢</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $stats['Kampanye']; ?></div>
                    <div class="stat-label-modern">Kampanye</div>
                </div>
            </div>

            <!-- Tombol Riwayat -->
            <a href="riwayat_kegiatan.php" class="btn-riwayat-modern">
                <span class="btn-icon">📋</span>
                <span>Lihat Riwayat Kegiatan</span>
                <span class="btn-arrow">→</span>
            </a>
        </div>
    </div>

</div>

<script>
function toggleEditMode() {
    const profileView = document.getElementById('profile-view');
    const profileEdit = document.getElementById('profile-edit');
    const btnEdit = document.getElementById('btn-edit-trigger');

    if (profileEdit.style.display === 'none') {
        // Masuk mode edit
        profileView.style.display = 'none';
        profileEdit.style.display = 'block';
        btnEdit.textContent = '❌ Batalkan Edit';
        btnEdit.style.background = '#dc3545';
    } else {
        // Kembali ke mode view
        profileView.style.display = 'block';
        profileEdit.style.display = 'none';
        btnEdit.textContent = '✏️ Edit Profil';
        btnEdit.style.background = '#ff9800';
    }
}
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>