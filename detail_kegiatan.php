<?php 
include __DIR__ . '/inc/header.php'; 

// Get kegiatan ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header('Location: kegiatan.php');
    exit;
}

// Fetch kegiatan detail dengan JOIN ke detail_kegiatan dan hitung jumlah pendaftar
$sql = "
    SELECT 
        k.*,
        dk.manfaat_kegiatan,
        dk.syarat_kegiatan,
        COUNT(DISTINCT pk.id_pendaftaran) as jumlah_pendaftar
    FROM kegiatan k
    LEFT JOIN detail_kegiatan dk ON k.id_kegiatan = dk.id_kegiatan
    LEFT JOIN pendaftaran_kegiatan pk ON k.id_kegiatan = pk.id_kegiatan
    WHERE k.id_kegiatan = ?
    GROUP BY k.id_kegiatan
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$kegiatan = mysqli_fetch_assoc($result);

if (!$kegiatan) {
    header('Location: kegiatan.php');
    exit;
}

// Hitung sisa kuota
$sisa_kuota = $kegiatan['kuota_relawan'] - $kegiatan['jumlah_pendaftar'];

// Check if user is already registered
$is_registered = false;

if (isset($_SESSION['user_id'])) {
    $sql = "SELECT id_pendaftaran FROM pendaftaran_kegiatan WHERE id_kegiatan = ? AND id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $is_registered = mysqli_num_rows($result) > 0;
}

// Fetch related activities (same category, not finished)
$sql = "
    SELECT * FROM kegiatan 
    WHERE id_kegiatan != ? 
    AND jenis_kegiatan = ?
    AND status_kegiatan != 'selesai'
    ORDER BY tanggal_kegiatan DESC
    LIMIT 3
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $id, $kegiatan['jenis_kegiatan']);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$related_kegiatan = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>

<!-- Detail Hero Section -->
<div class="detail-hero">
    <?php if (isset($kegiatan['gambar_kegiatan']) && !empty($kegiatan['gambar_kegiatan'])): ?>
        <img src="assets/img/kegiatan/<?php echo htmlspecialchars($kegiatan['gambar_kegiatan']); ?>" 
             alt="<?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>" 
             class="detail-hero-bg">
    <?php else: ?>
        <div class="detail-hero-bg" style="background: linear-gradient(135deg, #4a7c29, #6ba544);"></div>
    <?php endif; ?>
    <div class="detail-hero-overlay"></div>
    
    <div class="detail-hero-content">
        <div class="breadcrumb-detail">
            <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
            <span>/</span>
            <a href="kegiatan.php">Kegiatan</a>
            <span>/</span>
            <span style="color: white;">Detail</span>
        </div>
        
        <h1 class="detail-hero-title"><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></h1>
        
        <div class="detail-hero-meta">
            <div class="hero-meta-item">
                <span>📅 <?php echo date('d F Y', strtotime($kegiatan['tanggal_kegiatan'])); ?></span>
            </div>
            
            <div class="hero-meta-item">
                <span>⏰ <?php echo htmlspecialchars($kegiatan['waktu_kegiatan'] ?? 'Akan diumumkan'); ?></span>
            </div>
            
            <div class="hero-meta-item">
                <span>📍 <?php echo htmlspecialchars($kegiatan['lokasi_kegiatan']); ?></span>
            </div>
            
            <div class="hero-meta-item">
                <span>👥 <?php echo $kegiatan['kuota_relawan']; ?> Peserta</span>
            </div>
        </div>
    </div>
</div>

<!-- Detail Content -->
<div class="detail-main-wrapper">
    <div class="detail-container">
        <div class="detail-main-content">
            <!-- Left Content -->
            <div class="detail-content-left">
                <!-- Description Section -->
                <div class="detail-section">
                    <h2 class="detail-section-title">
                        <i class="fas fa-info-circle"></i>
                        Tentang Kegiatan
                    </h2>
                    <div class="detail-description">
                        <?php echo nl2br(htmlspecialchars($kegiatan['deskripsi_kegiatan'])); ?>
                    </div>
                </div>
                
                <!-- Information Grid -->
                <div class="detail-section">
                    <h2 class="detail-section-title">
                        <i class="fas fa-clipboard-list"></i>
                        Informasi Detail
                    </h2>
                    <div class="detail-info-grid">
                        <div class="info-item">
                            <div class="info-item-label">Tanggal Kegiatan</div>
                            <div class="info-item-value">
                                <?php echo date('d F Y', strtotime($kegiatan['tanggal_kegiatan'])); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-item-label">Waktu</div>
                            <div class="info-item-value">
                                <?php echo htmlspecialchars($kegiatan['waktu_kegiatan'] ?? 'Akan diumumkan'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-item-label">Lokasi</div>
                            <div class="info-item-value">
                                <?php echo htmlspecialchars($kegiatan['lokasi_kegiatan']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-item-label">Kategori</div>
                            <div class="info-item-value">
                                <?php 
                                $kategori_label = [
                                    'Penanaman' => '🌱 Penanaman',
                                    'Edukasi' => '📚 Edukasi',
                                    'Kolaborasi' => '🤝 Kolaborasi',
                                    'Kampanye' => '📢 Kampanye',
                                    'Lainnya' => '📦 Lainnya'
                                ];
                                echo $kategori_label[$kegiatan['jenis_kegiatan']] ?? ucfirst($kegiatan['jenis_kegiatan']);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Benefits Section -->
                <div class="detail-section">
                    <h2 class="detail-section-title">
                        <i class="fas fa-gift"></i>
                        Manfaat Bergabung
                    </h2>
                    <ul class="benefits-list">
                        <?php 
                        $manfaat_default = [
                            'Berkontribusi langsung untuk kelestarian lingkungan',
                            'Bertemu dan networking dengan relawan lain yang peduli lingkungan',
                            'Mendapatkan pengalaman berharga dalam aksi sosial',
                            'Sertifikat keikutsertaan dari TUMBUH',
                            'Konsumsi dan dokumentasi kegiatan'
                        ];
                        
                        if (!empty($kegiatan['manfaat_kegiatan'])) {
                            $manfaat_list = explode('|', $kegiatan['manfaat_kegiatan']);
                        } else {
                            $manfaat_list = $manfaat_default;
                        }
                        
                        foreach ($manfaat_list as $manfaat):
                            if (!empty(trim($manfaat))):
                        ?>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>🍀༄ <?php echo htmlspecialchars(trim($manfaat)); ?></span>
                        </li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
                
                <!-- Requirements Section -->
                <div class="detail-section">
                    <h2 class="detail-section-title">
                        <i class="fas fa-tasks"></i>
                        Persyaratan
                    </h2>
                    <ul class="requirements-list">
                        <?php 
                        $persyaratan_default = [
                            'Usia minimal 17 tahun',
                            'Sehat jasmani dan rohani',
                            'Berkomitmen mengikuti kegiatan dari awal hingga akhir',
                            'Membawa perlengkapan pribadi (topi, sarung tangan, dll)'
                        ];
                        
                        if (!empty($kegiatan['syarat_kegiatan'])) {
                            $persyaratan_list = explode('|', $kegiatan['syarat_kegiatan']);
                        } else {
                            $persyaratan_list = $persyaratan_default;
                        }
                        
                        foreach ($persyaratan_list as $persyaratan):
                            if (!empty(trim($persyaratan))):
                        ?>
                        <li>
                            <i class="fas fa-chevron-right"></i>
                            <span>🍃༄ <?php echo htmlspecialchars(trim($persyaratan)); ?></span>
                        </li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
            </div>
            
            <!-- Right Sidebar -->
            <div class="detail-sidebar">
                <div class="sidebar-card">
                    <!-- Status Badge -->
                    <div class="sidebar-status <?php echo strtolower($kegiatan['status_kegiatan']); ?>">
                        <?php 
                        $status_text = [
                            'berlangsung' => '🔴 SEDANG BERLANGSUNG',
                            'mendatang' => '📅 AKAN DATANG',
                            'selesai' => '✅ SELESAI'
                        ];
                        echo $status_text[strtolower($kegiatan['status_kegiatan'])] ?? strtoupper($kegiatan['status_kegiatan']);
                        ?>
                    </div>
                    
                    <!-- Stats -->
                    <div class="sidebar-stats">
                        <div class="stat-box">
                            <div class="stat-number" id="sisa-kuota-display"><?php echo $sisa_kuota; ?></div>
                            <div class="stat-label">Kuota Tersisa</div>
                            <div class="quota-progress">
                                <?php 
                                $progress_percentage = $kegiatan['kuota_relawan'] > 0 
                                    ? ($kegiatan['jumlah_pendaftar'] / $kegiatan['kuota_relawan']) * 100 
                                    : 0;
                                ?>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" id="quota-progress-bar" style="width: <?php echo min($progress_percentage, 100); ?>%"></div>
                                </div>
                                <div class="quota-text" id="quota-text">
                                    <span id="jumlah-pendaftar-display"><?php echo $kegiatan['jumlah_pendaftar']; ?></span> dari <?php echo $kegiatan['kuota_relawan']; ?> peserta terdaftar
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CTA Buttons -->
                    <div class="sidebar-cta">
                        <?php if ($is_registered): ?>
                            <div class="alert-message success">
                                <i class="fas fa-check-circle"></i>
                                <span>Anda sudah terdaftar di kegiatan ini</span>
                            </div>
                            <button class="btn-register-large registered" disabled>
                                <i class="fas fa-check"></i>
                                <span>Sudah Terdaftar</span>
                            </button>
                        <?php elseif (strtolower($kegiatan['status_kegiatan']) === 'selesai'): ?>
                            <div class="alert-message info">
                                <i class="fas fa-info-circle"></i>
                                <span>Kegiatan ini sudah selesai</span>
                            </div>
                            <button class="btn-register-large" disabled>
                                <i class="fas fa-times"></i>
                                <span>Pendaftaran Ditutup</span>
                            </button>
                        <?php elseif ($sisa_kuota <= 0): ?>
                            <div class="alert-message warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Kuota peserta sudah penuh</span>
                            </div>
                            <button class="btn-register-large" disabled>
                                <i class="fas fa-user-times"></i>
                                <span>Kuota Penuh</span>
                            </button>
                        <?php else: ?>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <div class="alert-message info">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Silakan login terlebih dahulu</span>
                                </div>
                                <button onclick="showModal('login')" class="btn-register-large">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span>Login untuk Daftar</span>
                                </button>
                            <?php else: ?>
                                <button onclick="openDaftarModal()" class="btn-register-large" id="btn-daftar-kegiatan">
                                    <i class="fas fa-hand-paper"></i>
                                    <span>Daftar Sekarang</span>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <button class="btn-share" onclick="shareActivity()">
                            <i class="fas fa-share-alt"></i>
                            <span>Bagikan Kegiatan</span>
                        </button>
                    </div>
                </div>
                
                <!-- Organizer Card -->
                <div class="sidebar-card">
                    <div class="organizer-card">
                        <div class="organizer-avatar">
                            🌱
                        </div>
                        <div class="organizer-info">
                            <h4>TUMBUH</h4>
                            <p>Penyelenggara Kegiatan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Activities Section -->
        <?php if (count($related_kegiatan) > 0): ?>
        <div class="related-section">
            <h2>Kegiatan Serupa</h2>
            <div class="related-grid">
                <?php foreach ($related_kegiatan as $related): ?>
                <div class="related-card">
                    <div class="related-image">
                        <?php if (isset($related['gambar_kegiatan']) && !empty($related['gambar_kegiatan'])): ?>
                            <img src="assets/img/kegiatan/<?php echo htmlspecialchars($related['gambar_kegiatan']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['judul_kegiatan']); ?>">
                        <?php else: ?>
                            🌱
                        <?php endif; ?>
                    </div>
                    <div class="related-content">
                        <h3><?php echo htmlspecialchars($related['judul_kegiatan']); ?></h3>
                        <div class="related-meta">
                            <span>📅 <?php echo date('d F Y', strtotime($related['tanggal_kegiatan'])); ?></span>
                            <span>📍 <?php echo htmlspecialchars($related['lokasi_kegiatan']); ?></span>
                        </div>
                        <a href="detail_kegiatan.php?id=<?php echo $related['id_kegiatan']; ?>" class="btn-related">
                            Lihat Detail <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Pendaftaran -->
<div class="modal-daftar-overlay" id="modalDaftar">
    <div class="modal-daftar-content">
        <div class="modal-daftar-header">
            <button class="modal-daftar-close" onclick="closeDaftarModal()">×</button>
            <div class="modal-daftar-icon">✋</div>
            <h2>Konfirmasi Pendaftaran</h2>
            <p class="modal-daftar-subtitle">Pastikan data Anda sudah benar</p>
        </div>
        
        <div class="modal-daftar-body">
            <div class="kegiatan-info-box-modal">
                <h3><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></h3>
                <div class="kegiatan-info-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('d F Y', strtotime($kegiatan['tanggal_kegiatan'])); ?></span>
                </div>
                <div class="kegiatan-info-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo htmlspecialchars($kegiatan['waktu_kegiatan'] ?? 'Akan diumumkan'); ?></span>
                </div>
                <div class="kegiatan-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($kegiatan['lokasi_kegiatan']); ?></span>
                </div>
            </div>
            
            <form id="formDaftarKegiatan">
                <input type="hidden" name="kegiatan_id" value="<?php echo $id; ?>">
                
                <div class="form-group-modal">
                    <label for="catatan">Catatan (Opsional)</label>
                    <textarea 
                        id="catatan" 
                        name="catatan" 
                        placeholder="Tuliskan catatan atau pertanyaan Anda di sini..."
                    ></textarea>
                </div>
                
                <div class="modal-daftar-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeDaftarModal()">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn-modal-submit" id="btnSubmitDaftar">
                        <i class="fas fa-check"></i>
                        Ya, Daftar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Success -->
<div class="modal-daftar-overlay" id="modalSuccess">
    <div class="modal-daftar-content">
        <div class="modal-daftar-body modal-success-content">
            <div class="modal-success-icon">
                ✓
            </div>
            <h2 class="modal-success-title">Pendaftaran Berhasil!</h2>
            <p class="modal-success-message">
                Selamat! Anda telah berhasil terdaftar dalam kegiatan ini.
            </p>
            
            <div class="wa-group-box">
                <h4>
                    <i class="fab fa-whatsapp"></i>
                    Bergabung dengan Grup WhatsApp
                </h4>
                <p style="margin: 0 0 1rem; font-size: 0.9rem;">
                    Dapatkan informasi terkini dan koordinasi kegiatan
                </p>
                <a href="#" id="linkWAGroup" target="_blank" class="btn-wa-group">
                    <i class="fab fa-whatsapp"></i>
                    Gabung Grup WA
                </a>
            </div>
            
            <button onclick="closeSuccessModal()" class="btn-close-success">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
const kegiatanId = <?php echo $id; ?>;

function openDaftarModal() {
    document.getElementById('modalDaftar').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDaftarModal() {
    document.getElementById('modalDaftar').classList.remove('active');
    document.body.style.overflow = '';
}

function closeSuccessModal() {
    document.getElementById('modalSuccess').classList.remove('active');
    document.body.style.overflow = '';
    location.reload();
}

// Handle form submission
document.getElementById('formDaftarKegiatan').addEventListener('submit', function(e) {
    e.preventDefault();

    const btnSubmit = document.getElementById('btnSubmitDaftar');
    const originalText = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.classList.add('btn-loading');
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';

    // 🔥 FIX: kirim data manual (jangan pakai FormData(this))
    const formData = new FormData();
    formData.append('kegiatan_id', kegiatanId);
    formData.append('catatan', document.getElementById('catatan').value);

    fetch('proses/proses_daftar_kegiatan.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeDaftarModal();
            document.getElementById('linkWAGroup').href = data.data.wa_group;
            document.getElementById('modalSuccess').classList.add('active');
            updateUIAfterRegistration(data.data);
        } else {
            alert(data.message);
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('btn-loading');
            btnSubmit.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan. Silakan coba lagi.');
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('btn-loading');
        btnSubmit.innerHTML = originalText;
    });
});

function updateUIAfterRegistration(data) {
    // Update sisa kuota
    const sisaKuota = data.sisa_kuota;
    document.getElementById('sisa-kuota-display').textContent = sisaKuota;
    
    // Update jumlah pendaftar
    const currentCount = parseInt(document.getElementById('jumlah-pendaftar-display').textContent);
    document.getElementById('jumlah-pendaftar-display').textContent = currentCount + 1;
    
    // Update progress bar
    const totalKuota = <?php echo $kegiatan['kuota_relawan']; ?>;
    const newPercentage = ((currentCount + 1) / totalKuota) * 100;
    document.getElementById('quota-progress-bar').style.width = Math.min(newPercentage, 100) + '%';
}

function shareActivity() {
    const url = window.location.href;
    const title = "<?php echo addslashes($kegiatan['judul_kegiatan']); ?>";
    const text = "Yuk ikutan kegiatan: " + title;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        }).catch(err => console.log('Error sharing:', err));
    } else {
        navigator.clipboard.writeText(url).then(() => {
            alert('Link kegiatan telah disalin! Silakan bagikan kepada teman-teman Anda.');
        });
    }
}

// Close modal when clicking outside
document.getElementById('modalDaftar').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDaftarModal();
    }
});

document.getElementById('modalSuccess').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSuccessModal();
    }
});
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>