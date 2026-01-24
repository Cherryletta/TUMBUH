<?php
$page_css = 'home';
include __DIR__ . '/inc/header.php';
// ==================== STATISTIK ====================
function getCount($conn, $sql) {
    $q = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($q)['total'] ?? 0;
}

$kegiatan_selesai = getCount(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM kegiatan 
     WHERE status_kegiatan = 'selesai'"
);

$total_relawan = getCount(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM users 
     WHERE role_user = 'user'"
);

$total_artikel = getCount(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM artikel"
);
?>

<!-- ==================== 1. HERO SECTION ==================== -->
<header class="hero">
    <div class="hero-content-wrapper">
        <h1>TUMBUH</h1>
        <p>Tanam Untuk Bumi Hijau</p>
        <p class="cta">🌱 ⊹₊˚‧ Menanam Kepedulian, Menjaga Masa Depan Bumi ‧˚₊⊹🌱</p>
        
        <div class="hero-buttons">
            <a href="#focus" class="btn-hero primary-pulse">
                <span>Selengkapnya</span>
                <i>→</i>
            </a>
        </div>
    </div>
</header>

<!-- ==================== MAIN CONTAINER ==================== -->
<div class="container">
    
    <!-- Success Alert -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert success">
            ✓ Pendaftaran berhasil! Silakan login dengan akun Anda.
        </div>
    <?php endif; ?>

    <h1 class="subtitle" id="focus">⋅˚₊‧ 🌱 ‧₊˚ ⋅</h1><br>

<!-- ==================== 2. FOKUS UTAMA TUMBUH ==================== -->
<section class="focus-section" id="focus">
    <div class="section-header">
        <h2>Selamat datang di TUMBUH</h2>
    </div>
    
    <div class="intro-box-bordered">
        <p>
            TUMBUH adalah komunitas lingkungan yang berfokus pada aksi pelestarian ekosistem darat dengan mengacu pada Sustainable Development Goals (SDGs) Tujuan 15: Menjaga Ekosistem Daratan. 
            Melalui kegiatan penanaman, edukasi, dan kolaborasi lintas pihak, TUMBUH hadir sebagai ruang partisipatif bagi masyarakat untuk berkontribusi langsung dalam menjaga keanekaragaman hayati serta menciptakan lingkungan yang lestari dan berkelanjutan.
        </p>
    </div>
</section>
</div>

<!-- ==================== 3. KENALI LAYANAN TUMBUH SECTION (FULL WIDTH GREEN) ==================== -->
<section class="layanan-section">
    <div class="layanan-container">
        <h2 class="layanan-title">˗ˏˋ ꒰ Kenali Layanan TUMBUH ꒱ ˎˊ˗</h2>
        
        <div class="layanan-cards-wrapper">
            <!-- Card 1: Informasi Kegiatan -->
            <div class="layanan-card">
                <div class="layanan-card-image">
                    <img src="assets/img/home/layanan1.png" alt="Informasi Kegiatan Lingkungan">
                </div>
                <div class="layanan-card-content">
                    <h3>Jelajahi Kegiatan Lingkungan</h3>
                    <p>Ketahui berbagai kegiatan lingkungan yang dilakukan oleh TUMBUH, lengkap dengan deskripsi, waktu, dan lokasi pelaksanaan.</p>
                </div>
            </div>
            
            <!-- Card 2: Artikel Lingkungan -->
            <div class="layanan-card">
                <div class="layanan-card-image">
                    <img src="assets/img/home/layanan2.png" alt="Artikel Lingkungan">
                </div>
                <div class="layanan-card-content">
                    <h3>Baca Artikel Lingkungan</h3>
                    <p>Akses artikel dan informasi seputar isu lingkungan, pelestarian alam, serta upaya menjaga ekosistem darat.</p>
                </div>
            </div>
            
            <!-- Card 3: Gerakan Lingkungan -->
            <div class="layanan-card">
                <div class="layanan-card-image">
                    <img src="assets/img/home/layanan3.png" alt="Gerakan Lingkungan TUMBUH">
                </div>
                <div class="layanan-card-content">
                    <h3>Kenali Gerakan TUMBUH</h3>
                    <p>Pelajari gerakan dan inisiatif lingkungan yang diusung oleh TUMBUH sebagai bentuk kepedulian terhadap bumi.</p>
                </div>
            </div>
            
            <!-- Card 4: Informasi & Kontak -->
            <div class="layanan-card">
                <div class="layanan-card-image">
                    <img src="assets/img/home/layanan4.png" alt="Informasi & Kontak">
                </div>
                <div class="layanan-card-content">
                    <h3>Terhubung dengan TUMBUH</h3>
                    <p>Temukan informasi kontak dan media komunikasi TUMBUH untuk mengenal lebih dekat dan terhubung dengan kami.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">

<!-- ==================== 4. KEGIATAN TERBARU ==================== -->
<section class="kegiatan-section modern">
    <div class="section-header">
        <h2 class="title-underline">Kegiatan Terbaru TUMBUH</h2>
        <p class="kegiatan-subtitle">Lihat beberapa kegiatan yang bisa kamu ikuti</p>
    </div>
        
    <div class="kegiatan-grid-modern">
        <?php if (count($kegiatan_list) > 0): ?>
            <?php 
            $shown_count = 0;
            foreach ($kegiatan_list as $kegiatan): 
                if ($shown_count >= 3) break;
                
                // Hitung jumlah pendaftar untuk kegiatan ini
                $kegiatan_id = $kegiatan['id_kegiatan'];
                $sql_count = "SELECT COUNT(*) as jumlah FROM pendaftaran_kegiatan WHERE id_kegiatan = ?";
                $stmt_count = mysqli_prepare($conn, $sql_count);
                mysqli_stmt_bind_param($stmt_count, "i", $kegiatan_id);
                mysqli_stmt_execute($stmt_count);
                $result_count = mysqli_stmt_get_result($stmt_count);
                $count_data = mysqli_fetch_assoc($result_count);
                $jumlah_pendaftar = $count_data['jumlah'] ?? 0;
                
                $shown_count++;
            ?>
                <a href="detail_kegiatan.php?id=<?php echo $kegiatan_id; ?>" class="kegiatan-card-link">
                    <div class="kegiatan-card-modern" data-status="<?php echo strtolower($kegiatan['status_kegiatan']); ?>">
                        <div class="kegiatan-card-header">
                            <?php if (isset($kegiatan['gambar_kegiatan']) && !empty($kegiatan['gambar_kegiatan'])): ?>
                                <img src="assets/img/kegiatan/<?php echo htmlspecialchars($kegiatan['gambar_kegiatan']); ?>" 
                                     alt="<?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>">
                            <?php else: ?>
                                <div class="kegiatan-gradient-bg">🌱</div>
                            <?php endif; ?>
                            
                            <div class="kegiatan-badge-container">
                                <span class="status-badge-modern status-<?php echo strtolower($kegiatan['status_kegiatan']); ?>">
                                    <?php 
                                    $status_icons = [
                                        'berlangsung' => '🔴',
                                        'mendatang' => '📅',
                                        'selesai' => '✅'
                                    ];
                                    echo $status_icons[strtolower($kegiatan['status_kegiatan'])] ?? '•';
                                    ?> <?php echo ucfirst($kegiatan['status_kegiatan']); ?>
                                </span>
                                
                                <!-- Category Badge -->
                                <?php if (isset($kegiatan['jenis_kegiatan'])): ?>
                                <span class="category-badge badge-<?php echo strtolower($kegiatan['jenis_kegiatan']); ?>">
                                    <?php
                                        $jenis = strtolower($kegiatan['jenis_kegiatan']);
                                        $icon  = 'fas fa-layer-group';
                                        $label = 'Lainnya';

                                        switch ($jenis) {
                                            case 'penanaman':
                                                $icon  = 'fas fa-seedling';
                                                $label = 'Penanaman';
                                                break;

                                            case 'edukasi':
                                                $icon  = 'fas fa-book';
                                                $label = 'Edukasi';
                                                break;

                                            case 'kolaborasi':
                                                $icon  = 'fas fa-handshake';
                                                $label = 'Kolaborasi';
                                                break;

                                            case 'kampanye':
                                                $icon  = 'fas fa-bullhorn';
                                                $label = 'Kampanye';
                                                break;

                                            default:
                                                $icon  = 'fas fa-layer-group';
                                                $label = 'Lainnya';
                                                break;
                                        }
                                    ?>
                                    <i class="<?php echo $icon; ?>"></i>
                                    <span><?php echo $label; ?></span>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="kegiatan-card-body">
                            <h3 class="kegiatan-title">
                                <?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>
                            </h3>
                            
                            <div class="kegiatan-meta-info">
                                <div class="meta-item">
                                    <i>📅</i>
                                    <span><?php echo date('d F Y', strtotime($kegiatan['tanggal_kegiatan'])); ?></span>
                                </div>
                                
                                <?php if (isset($kegiatan['lokasi_kegiatan'])): ?>
                                <div class="meta-item">
                                    <i>📍</i>
                                    <span><?php echo htmlspecialchars($kegiatan['lokasi_kegiatan']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($kegiatan['deskripsi_kegiatan'])): ?>
                            <p class="kegiatan-description">
                                <?php 
                                echo htmlspecialchars(substr($kegiatan['deskripsi_kegiatan'], 0, 120)); 
                                echo strlen($kegiatan['deskripsi_kegiatan']) > 120 ? '...' : ''; 
                                ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="kegiatan-card-footer">
                                <?php if (isset($kegiatan['kuota']) || isset($kegiatan['kuota_relawan'])): ?>
                                    <?php 
                                    $total_kuota = $kegiatan['kuota'] ?? $kegiatan['kuota_relawan'];
                                    $sisa_kuota = $total_kuota - $jumlah_pendaftar;
                                    $kuota_class = $sisa_kuota <= 0 ? 'quota-full' : ($sisa_kuota < 10 ? 'quota-low' : 'quota-available');
                                    ?>
                                    <div class="quota-info-modern <?php echo $kuota_class; ?>">
                                        <div class="quota-icon">👥</div>
                                        <div class="quota-text">
                                            <span class="quota-numbers"><?php echo $jumlah_pendaftar; ?>/<?php echo $total_kuota; ?></span>
                                            <span class="quota-label">Peserta</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-container">
                <div class="empty-state-icon">🌱</div>
                <h3>Belum Ada Kegiatan</h3>
                <p>Saat ini belum ada kegiatan yang tersedia. Pantau terus untuk update terbaru!</p>
            </div>
        <?php endif; ?>
    </div><br>

    <?php if (count($kegiatan_list) > 0): ?>
    <div class="section-cta">
        <a href="kegiatan.php" class="btn-cta-primary">
            Lihat Semua Kegiatan <i>→</i>
        </a>
    </div>
    <?php endif; ?>
</section>
</div>

<!-- ==================== 5. JEJAK AKSI SECTION ==================== -->
<section class="jejak-aksi-section">
    <div class="jejak-aksi-container">
        <h2 class="jejak-aksi-title">˗ˏˋ ꒰ Jejak Aksi TUMBUH ꒱ ˎˊ˗</h2>
        
        <div class="jejak-stats-wrapper">

            <div class="jejak-stat-card">
                <div class="jejak-stat-number">
                    <?php echo number_format($total_relawan); ?>
                </div>
                <div class="jejak-stat-label">Relawan Terlibat</div>
            </div>
            
            <div class="jejak-stat-card">
                <div class="jejak-stat-number">
                    <?php echo number_format($kegiatan_selesai); ?>
                </div>
                <div class="jejak-stat-label">Kegiatan Terlaksana</div>
            </div>
            
            <div class="jejak-stat-card">
                <div class="jejak-stat-number">
                    <?php echo number_format($total_artikel); ?>
                </div>
                <div class="jejak-stat-label">Informasi Dibagikan</div>
            </div>
        </div>
    </div>
</section>

<div class="container">

<!-- ==================== 6. ARTIKEL TERBARU ==================== -->
<section class="artikel-section-home">
    <div class="section-header">
        <h2 class="title-underline">Artikel TUMBUH</h2>
        <p class="artikel-subtitle">Sejumlah informasi yang bisa kamu baca</p>
    </div>
    
    <div class="artikel-list-container">
        <?php if (!empty($artikel_list)): ?>
            <?php 
            $shown_artikel = 0;
            foreach ($artikel_list as $artikel): 
                if ($shown_artikel >= 4) break;
                $shown_artikel++;
            ?>
                <a href="detail_artikel.php?id=<?php echo $artikel['id_artikel'] ?? ''; ?>" class="artikel-card-link">
                    <div class="artikel-item-horizontal">
                        <div class="artikel-thumb">
                            <?php if (!empty($artikel['gambar_artikel']) && file_exists("assets/img/artikel/".$artikel['gambar_artikel'])): ?>
                                <img src="assets/img/artikel/<?php echo htmlspecialchars($artikel['gambar_artikel']); ?>" 
                                     alt="<?php echo htmlspecialchars($artikel['judul_artikel']); ?>">
                            <?php else: ?>
                                <div class="artikel-thumb-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($artikel['kategori_artikel'])): ?>
                            <div class="artikel-kategori-badge badge-<?php echo $artikel['kategori_artikel']; ?>">
                                <?php 
                                $icons = [
                                    'edukasi' => 'fa-graduation-cap',
                                    'pandangan' => 'fa-eye',
                                    'tips' => 'fa-lightbulb',
                                    'cerita' => 'fa-book-open'
                                ];
                                $icon = isset($icons[$artikel['kategori_artikel']]) ? $icons[$artikel['kategori_artikel']] : 'fa-file-alt';
                                echo '<i class="fas '.$icon.'"></i> '.strtoupper($artikel['kategori_artikel']);
                                ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="artikel-text-content">
                            <div class="artikel-meta-bar">
                                <span class="meta-date">
                                    <i class="far fa-calendar"></i> 
                                    <?php echo date('d F Y', strtotime($artikel['tanggal_artikel'])); ?>
                                </span>
                                <?php if (isset($artikel['sumber_artikel']) && !empty($artikel['sumber_artikel'])): ?>
                                <span class="meta-divider">|</span>
                                <span class="meta-source">
                                    <i class="fas fa-building"></i> 
                                    <?php echo htmlspecialchars($artikel['sumber_artikel']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="artikel-title-horizontal">
                                <?php echo htmlspecialchars($artikel['judul_artikel']); ?>
                            </h3>

                            <p class="artikel-excerpt-horizontal">
                                <?php 
                                $isi = strip_tags($artikel['isi_artikel']);
                                echo htmlspecialchars(substr($isi, 0, 90)) . '...'; 
                                ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-artikel">
                <i class="fas fa-inbox"></i>
                <h3>Belum Ada Artikel</h3>
                <p>Saat ini belum ada artikel yang tersedia.</p>
            </div>
        <?php endif; ?>
    </div><br>

    <?php if (count($artikel_list) > 0): ?>
    <div class="section-cta" style="margin-top: 2rem;">
        <a href="artikel.php" class="btn-cta-primary">
            Lihat Semua Artikel <i>→</i>
        </a>
    </div>
    <?php endif; ?>
</section>
</div>

<!-- ==================== 7. PARTNER SECTION (FULL WIDTH GREEN) ==================== -->
<section class="partner-section">
    <div class="partner-container">
        <h2 class="partner-title">˗ˏˋ ꒰ Partner Kerjasama TUMBUH ꒱ ˎˊ˗</h2>
        
        <div class="partner-logos-wrapper">
            <div class="partner-logo">
                <img src="assets/img/home/partner1.png" alt="Partner 1">
            </div>
            <div class="partner-logo">
                <img src="assets/img/home/partner2.png" alt="Partner 2">
            </div>
            <div class="partner-logo">
                <img src="assets/img/home/partner3.jpg" alt="Partner 3">
            </div>
            <div class="partner-logo">
                <img src="assets/img/home/partner4.png" alt="Partner 4">
            </div>
            <div class="partner-logo">
                <img src="assets/img/home/partner5.webp" alt="Partner 5">
            </div>
        </div>
    </div>
</section>

<div class="container">
    <!-- ==================== 8. AJAKAN BERGABUNG (CTA) ==================== -->
    <section class="cta-section-modern">
        <div class="cta-decoration">
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            <div class="decoration-circle circle-3"></div>
        </div>
        
        <div class="cta-content">
            <div class="cta-icon">🌱</div>
            <h2>Mari Bertumbuh Bersama</h2>
            <p>
                Setiap langkah kecil yang kita ambil hari ini akan menjadi perubahan besar untuk masa depan. 
                Bergabunglah dengan ribuan relawan TUMBUH dan jadilah bagian dari gerakan hijau Indonesia. 
                Bersama kita tanam, bersama kita jaga, bersama kita tumbuh! 🌿
            </p>
            
            <div class="cta-buttons">
                <a href="gabung.php" class="btn-cta-primary">
                    <span>🚀 Gabung Bersama TUMBUH</span>
                </a>
                <a href="kontak.php" class="btn-cta-outline">
                    <span>📞 Hubungi TUMBUH</span>
                </a>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>