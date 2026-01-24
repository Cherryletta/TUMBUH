<?php include __DIR__ . '/inc/header.php'; ?>

<?php 
if (! $is_logged_in) { 
    header('Location: index.php'); 
    exit(); 
}

$user_id = $current_user['id_user'];
$query_stats = mysqli_query($conn, "
    SELECT 
        k.jenis_kegiatan,
        COUNT(*) as total
    FROM pendaftaran_kegiatan p
    INNER JOIN kegiatan k ON p.id_kegiatan = k.id_kegiatan
    WHERE p.id_user = $user_id
    GROUP BY k.jenis_kegiatan
");
$stats = [
    'penanaman' => 0,
    'edukasi' => 0,
    'kolaborasi' => 0,
    'kampanye' => 0,
    'lainnya' => 0
];

while ($row = mysqli_fetch_assoc($query_stats)) {
    $key = strtolower($row['jenis_kegiatan']);
    $stats[$key] = $row['total'];
}

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
                    </span>
                </div>
                <div class="profile-action-buttons">
                    <button class="btn-edit-profile" onclick="toggleEditMode()" id="btn-edit-trigger">Edit Profil</button>
                    <button class="btn-password" onclick="togglePasswordMode()" id="btn-password-trigger" style="background:#2e7d32;">Edit Password</button>
                </div>             
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
                        <label class="info-label">💡 Motivasi</label>
                        <div class="info-value"><?php echo $current_user['motivasi_user'] ?: '<em>Belum diisi</em>'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Edit Form Section (EDIT MODE) -->
            <form method="POST" action="proses/update_profile.php" id="profile-edit" class="profile-edit-form" style="display:none;">
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
                    <label for="motivasi">💡 Motivasi</label>
                    <textarea id="motivasi" name="motivasi" rows="3" placeholder="Bagikan motivasi Anda bergabung dengan TUMBUH..."><?php echo $current_user['motivasi_user']; ?></textarea>
                </div>

                <div class="form-actions-horizontal">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">❌ Batal</button>
                </div>
            </form>

            <form method="POST" action="proses/update_password.php" 
                id="password-edit" class="profile-edit-form" style="display:none;">

                <h3>🔒 Ubah Password</h3>

                <div class="form-row-full">
                    <label>Password Lama</label>
                    <input type="password" name="password_lama" required>
                </div>

                <div class="form-row-full">
                    <label>Password Baru</label>
                    <input type="password" name="password_baru" required minlength="6">
                </div>

                <div class="form-row-full">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" required>
                </div>

                <div class="form-actions-horizontal">
                    <button type="submit" class="btn btn-primary">💾 Simpan Password</button>
                    <button type="button" class="btn btn-secondary" onclick="togglePasswordMode()">❌ Batal</button>
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
                    <div class="stat-number-modern"><?php echo $stats['penanaman']; ?></div>
                    <div class="stat-label-modern">Penanaman</div>
                </div>
            </div>

            <div class="stat-box-modern stat-edukasi">
                <div class="stat-icon-modern">📚</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $stats['edukasi']; ?></div>
                    <div class="stat-label-modern">Edukasi</div>
                </div>
            </div>

            <div class="stat-box-modern stat-kolaborasi">
                <div class="stat-icon-modern">🤝</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $stats['kolaborasi']; ?></div>
                    <div class="stat-label-modern">Kolaborasi</div>
                </div>
            </div>

            <div class="stat-box-modern stat-kampanye">
                <div class="stat-icon-modern">📢</div>
                <div class="stat-content-modern">
                    <div class="stat-number-modern"><?php echo $stats['kampanye']; ?></div>
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
        profileView.style.display = 'none';
        profileEdit.style.display = 'block';
        btnEdit.textContent = '❌ Batalkan Edit';
        btnEdit.style.background = '#dc3545';
    } else {
        profileView.style.display = 'block';
        profileEdit.style.display = 'none';
        btnEdit.textContent = '✏️ Edit Profil';
        btnEdit.style.background = '#ff9800';
    }
}

function togglePasswordMode() {
    const profileView = document.getElementById('profile-view');
    const profileEdit = document.getElementById('profile-edit');
    const passwordEdit = document.getElementById('password-edit');

    profileView.style.display = 'none';
    profileEdit.style.display = 'none';

    if (passwordEdit.style.display === 'none') {
        passwordEdit.style.display = 'block';
    } else {
        passwordEdit.style.display = 'none';
        profileView.style.display = 'block';
    }
}
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>