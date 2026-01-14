<?php
$page_css = 'home';
include __DIR__ . '/inc/header.php';
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

    <h1 class="subtitle" id="focus">‧₊˚ ⋅🌿🌱𓂃 ࣪ ִֶָ.</h1><br>

    <!-- ==================== 2. FOKUS UTAMA TUMBUH ==================== -->
    <section class="focus-section" id="focus">
        <div class="section-header">
            <h2 class="title-underline">Selamat datang di TUMBUH</h2>
        </div>
        
        <p class="intro-text">
            TUMBUH adalah komunitas lingkungan yang berfokus pada aksi pelestarian ekosistem darat dengan mengacu pada Sustainable Development Goals (SDGs) Tujuan 15: Menjaga Ekosistem Daratan. 
            Melalui kegiatan penanaman, edukasi, dan kolaborasi lintas pihak, TUMBUH hadir sebagai ruang partisipatif bagi masyarakat untuk berkontribusi langsung dalam menjaga keanekaragaman hayati serta menciptakan lingkungan yang lestari dan berkelanjutan.
        </p>
    </section>

    <h1 class="subtitle">‧₊˚ ⋅🌿🌱𓂃 ࣪ ִֶָ.</h1><br>
    

    <!-- ==================== 3. APA YANG KAMI LAKUKAN ==================== -->
    <section class="services-section">
        <div class="section-header">
            <h2 class="title-underline">Apa yang Kami Lakukan</h2>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon">🌳</div>
                <div class="stat-content">
                    <h3 style="font-size: 1.3rem; margin: 0 0 0.5rem 0;">Aksi Penanaman</h3>
                    <p style="text-align: center;">Mengadakan kegiatan penanaman pohon massal di berbagai lokasi untuk menghijaukan lingkungan</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3 style="font-size: 1.3rem; margin: 0 0 0.5rem 0;">Edukasi Lingkungan</h3>
                    <p style="text-align: center;">Memberikan workshop dan sosialisasi tentang pentingnya menjaga kelestarian alam</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🌿</div>
                <div class="stat-content">
                    <h3 style="font-size: 1.3rem; margin: 0 0 0.5rem 0;">Perawatan Lingkungan</h3>
                    <p style="text-align: center;">Melakukan monitoring dan perawatan pohon yang telah ditanam secara berkelanjutan</p>
                </div>
            </div>
        </div>
    </section>

    <h1 class="subtitle">‧₊˚ ⋅🌿🌱𓂃 ࣪ ִֶָ.</h1><br>


    <!-- ==================== 4. STATISTIK DAMPAK ==================== -->
    <section class="stats-section">
        <div class="section-header">
            <h2 class="title-underline">Statistik Terkini</h2>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-content">
                    <h3><?php echo number_format($statistik['total_relawan'] ?? 0); ?></h3>
                    <p>Relawan Terlibat</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-content">
                    <h3><?php echo number_format($statistik['pohon_ditanam'] ?? 0); ?></h3>
                    <p>Pohon Ditanam</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-content">
                    <h3><?php echo $statistik['lokasi_penanaman'] ?? 0; ?></h3>
                    <p>Lokasi Penanaman</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <h3><?php echo number_format($statistik['kegiatan_berhasil'] ?? 0); ?></h3>
                    <p>Kegiatan Berhasil</p>
                </div>
            </div>
        </div>
    </section>
    
    <h1 class="subtitle">‧₊˚ ⋅🌿🌱𓂃 ࣪ ִֶָ.</h1><br>

    <!-- ==================== 5. KEGIATAN TERBARU ==================== -->
    <section class="kegiatan-section modern">
        <div class="section-header">
            <h2 class="title-underline">Kegiatan Terbaru Kami</h2>
        </div>
        
        <div class="kegiatan-grid-modern">
            <?php if (count($kegiatan_list) > 0): ?>
                <?php 
                $shown_count = 0;
                foreach ($kegiatan_list as $kegiatan): 
                    if ($shown_count >= 3) break; // Tampilkan max 3 kegiatan
                    $shown_count++;
                ?>
                    <div class="kegiatan-card-modern">
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
                            </div>

                            <!-- Category Badge -->
                            <?php if (isset($kegiatan['jenis_kegiatan'])): ?>
                            <div class="category-badge badge-<?php echo strtolower($kegiatan['jenis_kegiatan']); ?>">
                                <?php
                                    $jenis = strtolower($kegiatan['jenis_kegiatan']);
                                    $icon  = 'fas fa-layer-group';
                                    $label = 'Lainnya';

                                    switch ($jenis) {
                                        case 'penanaman':
                                            $icon  = 'fas fa-seedling';
                                            $label = '#Penanaman';
                                            break;

                                        case 'edukasi':
                                            $icon  = 'fas fa-book';
                                            $label = '#Edukasi';
                                            break;

                                        case 'kolaborasi':
                                            $icon  = 'fas fa-handshake';
                                            $label = '#Kolaborasi';
                                            break;

                                        case 'kampanye':
                                            $icon  = 'fas fa-bullhorn';
                                            $label = '#Kampanye';
                                            break;

                                        default:
                                            $icon  = 'fas fa-layer-group';
                                            $label = '#Lainnya';
                                            break;
                                    }
                                ?>
                                <i class="<?php echo $icon; ?>"></i>
                                <span><?php echo $label; ?></span>
                            </div>
                            <?php endif; ?>
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
                                <span class="quota-info">
                                    <i>👥</i> Kuota: <?php echo $kegiatan['kuota'] ?? $kegiatan['kuota_relawan']; ?>
                                </span>
                                <?php endif; ?>
                                
                                <div class="kegiatan-action-buttons">
                                    <?php 
                                    $kegiatan_id = $kegiatan['id_kegiatan'];
                                    if ($kegiatan_id): 
                                    ?>
                                    <a href="detail_kegiatan.php?id=<?php echo $kegiatan_id; ?>" 
                                       class="btn-detail-modern">
                                        Lihat Detail <i>→</i>
                                    </a>
                                    
                                    <?php if (strtolower($kegiatan['status_kegiatan']) == 'mendatang' || strtolower($kegiatan['status_kegiatan']) == 'berlangsung'): ?>
                                    <a href="gabung.php" class="btn-daftar-modern">
                                        Daftar <i>✓</i>
                                    </a>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-container">
                    <div class="empty-state-icon">🌱</div>
                    <h3>Belum Ada Kegiatan</h3>
                    <p>Saat ini belum ada kegiatan yang tersedia. Pantau terus untuk update terbaru!</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (count($kegiatan_list) > 0): ?>
        <div class="section-cta">
            <a href="kegiatan.php" class="btn-cta-modern">
                Lihat Semua Kegiatan <i>→</i>
            </a>
        </div>
        <?php endif; ?>
    </section>

    <h1 class="subtitle">‧₊˚ ⋅🌿🌱𓂃 ࣪ ִֶָ.</h1><br>

    <!-- ==================== 6. DAMPAK YANG INGIN DICAPAI ==================== -->
    <section class="impact-section">
        <div class="section-header">
            <h2 class="title-underline">Dampak yang Ingin Kami Capai</h2>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon">💚</div>
                <div class="stat-content">
                    <h3 style="font-size: 1.5rem; margin: 0 0 0.5rem 0;">Kesadaran Lingkungan</h3>
                    <p>Menumbuhkan kesadaran masyarakat tentang pentingnya menjaga kelestarian alam</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🌱</div>
                <div class="stat-content">
                    <h3 style="font-size: 1.5rem; margin: 0 0 0.5rem 0;">Kebiasaan Menanam</h3>
                    <p>Mendorong kebiasaan menanam pohon sebagai gaya hidup berkelanjutan</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🤲</div>
                <div class="stat-content">
                    <h3 style="font-size: 1.5rem; margin: 0 0 0.5rem 0;">Kolaborasi Masyarakat</h3>
                    <p>Menguatkan kolaborasi antara masyarakat, pemerintah, dan swasta</p>
                </div>
            </div>
        </div>
    </section>

    <h1 class="subtitle">‧₊˚ ⋅🌿🌱𓂃 ࣪ ִֶָ.</h1><br>

    <!-- ==================== 7. BERITA TERBARU ==================== -->
    <section class="berita-section modern">
        <div class="section-header">
            <h2 class="title-underline">Berita Lingkungan Terkini</h2>
        </div>
        
        <div class="berita-grid">
            <?php if (!empty($berita_list)): ?>
                <?php 
                $shown_berita = 0;
                foreach ($berita_list as $berita): 
                    if ($shown_berita >= 3) break; // Tampilkan max 3 berita
                    $shown_berita++;
                ?>
                    <div class="berita-card">
                        <div class="berita-image">
                            <?php if (isset($berita['gambar_berita']) && !empty($berita['gambar_berita'])): ?>
                                <img src="assets/img/berita/<?php echo htmlspecialchars($berita['gambar_berita']); ?>" 
                                     alt="<?php echo htmlspecialchars($berita['judul_berita']); ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #4a7c29, #6ba544); display: flex; align-items: center; justify-content: center; font-size: 4rem; color: rgba(255,255,255,0.3);">
                                    📰
                                </div>
                            <?php endif; ?>
                            
                            <div class="berita-overlay">
                                <span class="berita-category">
                                    <i>🏷️</i> Lingkungan
                                </span>
                            </div>
                        </div>
                        
                        <div class="berita-content">
                            <div class="berita-date-badge">
                                <i>📅</i>
                                <?php echo date('d F Y', strtotime($berita['tanggal_berita'])); ?>
                            </div>
                            
                            <h3><?php echo htmlspecialchars($berita['judul_berita']); ?></h3>
                            
                            <p class="berita-excerpt">
                                <?php 
                                $excerpt = strip_tags($berita['isi_berita']);
                                echo htmlspecialchars(substr($excerpt, 0, 120));
                                echo strlen($excerpt) > 120 ? '...' : '';
                                ?>
                            </p>
                            
                            <div class="berita-footer">
                                <a href="<?php 
                                    if (isset($berita['id']) && !empty($berita['id'])) {
                                        echo 'detail-berita.php?id=' . $berita['id'];
                                    } else {
                                        echo 'berita.php';
                                    }
                                ?>" class="btn-read-more">
                                    Baca Selengkapnya <i>→</i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-container">
                    <div class="empty-state-icon">📰</div>
                    <h3>Belum Ada Berita</h3>
                    <p>Saat ini belum ada berita yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (count($berita_list) > 0): ?>
        <div class="section-cta">
            <a href="berita.php" class="btn-cta-secondary">
                Baca Artikel Lainnya <i>→</i>
            </a>
        </div>
        <?php endif; ?>
    </section>

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
                    <span>📞 Hubungi Kami</span>
                </a>
            </div>
        </div>
    </section>

</div>

<?php include __DIR__ . '/inc/footer.php'; ?>