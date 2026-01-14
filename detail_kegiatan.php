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

<style>
.detail-hero {
    position: relative;
    height: 470px;
    background: linear-gradient(135deg, rgba(45, 80, 22, 0.9) 0%, rgba(74, 124, 41, 0.8) 100%);
    overflow: hidden;
    margin-bottom: 0;
}

.detail-hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
}

.detail-hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(45, 80, 22, 0.88) 0%, rgba(74, 124, 41, 0.78) 100%);
    z-index: 1;
}

.detail-hero-content {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
    padding: 3rem 2rem;
    color: white;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.breadcrumb-detail {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 2rem;
    font-size: 0.9rem;
}

.breadcrumb-detail a {
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    transition: color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.breadcrumb-detail a:hover {
    color: white;
}

.breadcrumb-detail span {
    color: rgba(255, 255, 255, 0.6);
}

.detail-hero-title {
    font-size: 3.2rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 12px rgba(0, 0, 0, 0.4);
    line-height: 1.2;
}

.detail-hero-meta {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    margin-top: 1.5rem;
}

.hero-meta-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    background: rgba(255, 255, 255, 0.15);
    padding: 0.9rem 1.8rem;
    border-radius: 30px;
    backdrop-filter: blur(10px);
    font-weight: 500;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.hero-meta-item i {
    font-size: 1.4rem;
}

.detail-main-wrapper {
    background: white;
    margin-top: -80px;
    position: relative;
    z-index: 3;
}

.detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem 4rem;
}

.detail-main-content {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2.5rem;
    padding-top: 3rem;
}

.detail-content-left {
    background: white;
    border-radius: 15px;
    padding: 3rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f0f0f0;
}

.detail-section {
    margin-bottom: 3.5rem;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.detail-section-title {
    font-size: 1.9rem;
    color: #2d5016;
    margin-bottom: 1.8rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #e8f5e9;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 700;
}

.detail-section-title::after {
    display: none;
}

.detail-section-title i {
    color: #4a7c29;
    font-size: 1.6rem;
}

.detail-description {
    font-size: 1.1rem;
    line-height: 2;
    color: #555;
    text-align: justify;
}

.detail-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.info-item {
    background: linear-gradient(135deg, #f8f9fa 0%, #f0f2f5 100%);
    padding: 1.8rem;
    border-radius: 12px;
    border-left: 5px solid #4a7c29;
    transition: all 0.3s ease;
}

.info-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(74, 124, 41, 0.1);
}

.info-item-label {
    font-size: 0.85rem;
    color: #999;
    margin-bottom: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
}

.info-item-value {
    font-size: 1.3rem;
    color: #2d5016;
    font-weight: 700;
}

.benefits-list, .requirements-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.benefits-list li, .requirements-list li {
    padding: 1.2rem;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 10px;
    display: flex;
    align-items: flex-start;
    gap: 1.2rem;
    transition: all 0.3s ease;
    border: 1px solid #e8f5e9;
}

.benefits-list li:hover, .requirements-list li:hover {
    background: linear-gradient(135deg, #e8f5e9 0%, #f0f7f0 100%);
    transform: translateX(8px);
    box-shadow: 0 2px 10px rgba(74, 124, 41, 0.1);
}

.benefits-list li i, .requirements-list li i {
    color: #4a7c29;
    font-size: 1.3rem;
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.detail-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.sidebar-card {
    background: white;
    border-radius: 15px;
    padding: 2.2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 1.5rem;
    border: 1px solid #f0f0f0;
}

.sidebar-status {
    text-align: center;
    padding: 1.8rem;
    border-radius: 12px;
    margin-bottom: 1.8rem;
    font-weight: 700;
    font-size: 1.15rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.sidebar-status.berlangsung {
    background: linear-gradient(135deg, #28a745, #34ce57);
    color: white;
}

.sidebar-status.mendatang {
    background: linear-gradient(135deg, #007bff, #0069d9);
    color: white;
}

.sidebar-status.selesai {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.sidebar-stats {
    display: grid;
    gap: 1.2rem;
    margin-bottom: 2rem;
}

.stat-box {
    text-align: center;
    padding: 1.8rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e9 100%);
    border-radius: 12px;
    transition: all 0.3s ease;
    border: 2px solid #e8f5e9;
}

.stat-box:hover {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(74, 124, 41, 0.15);
}

.stat-number {
    font-size: 2.8rem;
    font-weight: 800;
    color: #ff9800;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #ff9800, #f57c00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-label {
    font-size: 0.95rem;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quota-progress {
    margin-top: 1.2rem;
}

.progress-bar-container {
    width: 100%;
    height: 12px;
    background: #e8f5e9;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 0.8rem;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #4a7c29, #6ba544);
    border-radius: 10px;
    transition: width 0.8s ease;
    box-shadow: 0 2px 8px rgba(74, 124, 41, 0.3);
}

.quota-text {
    font-size: 0.9rem;
    color: #666;
    text-align: center;
    font-weight: 500;
}

.sidebar-cta {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
}

.btn-register-large {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    color: white;
    padding: 1.4rem 2.2rem;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-register-large:hover {
    background: linear-gradient(135deg, #f57c00, #e65100);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.5);
}

.btn-register-large:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    background: linear-gradient(135deg, #ccc, #999);
}

.btn-register-large.registered {
    background: linear-gradient(135deg, #28a745, #34ce57);
}

.btn-register-large.registered:hover {
    background: linear-gradient(135deg, #218838, #28a745);
}

.btn-register-large i {
    font-size: 1.3rem;
}

.btn-share {
    background: white;
    color: #4a7c29;
    padding: 1.2rem;
    border: 2px solid #4a7c29;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-share:hover {
    background: #4a7c29;
    color: white;
    box-shadow: 0 4px 15px rgba(74, 124, 41, 0.3);
}

.organizer-card {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e9 100%);
    border-radius: 12px;
    border: 2px solid #e8f5e9;
}

.organizer-avatar {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #4a7c29, #6ba544);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    font-weight: bold;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(74, 124, 41, 0.3);
}

.organizer-info h4 {
    margin: 0 0 0.4rem 0;
    color: #2d5016;
    font-size: 1.15rem;
    font-weight: 700;
}

.organizer-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    font-weight: 500;
}

.related-section {
    margin-top: 4rem;
    padding-top: 3rem;
    border-top: 3px solid #e8f5e9;
}

.related-section h2 {
    text-align: center;
    margin-bottom: 2.5rem;
    font-size: 2.2rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.related-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

.related-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.related-image {
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #4a7c29, #6ba544);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3.5rem;
    position: relative;
}

.related-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.related-card:hover .related-image img {
    transform: scale(1.1);
}

.related-content {
    padding: 1.8rem;
}

.related-content h3 {
    font-size: 1.2rem;
    margin: 0 0 1rem 0;
    color: #2d5016;
    font-weight: 700;
    text-align: left;
}

.related-content h3::after {
    display: none;
}

.related-meta {
    font-size: 0.9rem;
    color: #999;
    margin-bottom: 1.2rem;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.btn-related {
    background: transparent;
    color: #4a7c29;
    padding: 0;
    border: none;
    font-weight: 600;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    transition: all 0.3s ease;
    text-decoration: none;
    cursor: pointer;
}

.btn-related:hover {
    color: #2d5016;
    gap: 1rem;
}

.btn-related i {
    transition: transform 0.3s ease;
}

.btn-related:hover i {
    transform: translateX(5px);
}

/* Alert Messages */
.alert-message {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 500;
}

.alert-message.success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-left: 5px solid #28a745;
}

.alert-message.info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    border-left: 5px solid #17a2b8;
}

.alert-message.warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border-left: 5px solid #ffc107;
}

/* Responsive */
@media (max-width: 968px) {
    .detail-main-content {
        grid-template-columns: 1fr;
    }
    
    .detail-sidebar {
        position: static;
    }
    
    .detail-info-grid {
        grid-template-columns: 1fr;
    }
    
    .related-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .detail-hero {
        height: 350px;
    }
    
    .detail-hero-title {
        font-size: 2.2rem;
    }
    
    .detail-hero-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .detail-content-left {
        padding: 2rem 1.5rem;
    }
    
    .detail-container {
        padding: 0 1rem 3rem;
    }
    
    .detail-section-title {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .detail-hero {
        height: 300px;
    }
    
    .detail-hero-title {
        font-size: 1.8rem;
    }
    
    .detail-hero-content {
        padding: 2rem 1rem;
    }
    
    .detail-content-left {
        padding: 1.5rem;
    }
    
    .sidebar-card {
        padding: 1.5rem;
    }
}
</style>

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
                        // Default manfaat jika tidak ada di database
                        $manfaat_default = [
                            'Berkontribusi langsung untuk kelestarian lingkungan',
                            'Bertemu dan networking dengan relawan lain yang peduli lingkungan',
                            'Mendapatkan pengalaman berharga dalam aksi sosial',
                            'Sertifikat keikutsertaan dari TUMBUH',
                            'Konsumsi dan dokumentasi kegiatan'
                        ];
                        
                        // Ambil manfaat dari database atau gunakan default
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
                        // Default persyaratan jika tidak ada di database
                        $persyaratan_default = [
                            'Usia minimal 17 tahun',
                            'Sehat jasmani dan rohani',
                            'Berkomitmen mengikuti kegiatan dari awal hingga akhir',
                            'Membawa perlengkapan pribadi (topi, sarung tangan, dll)'
                        ];
                        
                        // Ambil persyaratan dari database atau gunakan default
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
                            <div class="stat-number"><?php echo $sisa_kuota; ?></div>
                            <div class="stat-label">Kuota Tersisa</div>
                            <div class="quota-progress">
                                <?php 
                                $progress_percentage = $kegiatan['kuota_relawan'] > 0 
                                    ? ($kegiatan['jumlah_pendaftar'] / $kegiatan['kuota_relawan']) * 100 
                                    : 0;
                                ?>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: <?php echo min($progress_percentage, 100); ?>%"></div>
                                </div>
                                <div class="quota-text">
                                    <?php echo $kegiatan['jumlah_pendaftar']; ?> dari <?php echo $kegiatan['kuota_relawan']; ?> peserta terdaftar
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
                                <a href="gabung.php?kegiatan_id=<?php echo $id; ?>" class="btn-register-large">
                                    <i class="fas fa-hand-paper"></i>
                                    <span>Daftar Sekarang</span>
                                </a>
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

<script>
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
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('Link kegiatan telah disalin! Silakan bagikan kepada teman-teman Anda.');
        });
    }
}
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>