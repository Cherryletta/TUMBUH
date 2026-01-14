<?php include __DIR__ . '/inc/header.php'; ?>

<div class="container kegiatan-page-container">
    <div class="container" style="padding: 2rem 1rem;">
        <h2 class="title-underline">Program & Aktivitas TUMBUH</h2>
        <p class="subtitle">Bergabunglah dalam berbagai kegiatan untuk pelestarian lingkungan dan penanaman pohon</p>
    </div>

    <!-- Filter Buttons -->
    <div class="kegiatan-filter-section" style="margin: 1rem 0; padding: 1rem 0;">
        <div class="filter-container" style="display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center;">
            <button class="filter-pill active" data-filter="semua" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.5rem; background: linear-gradient(135deg, #4a7c29, #6ba544); color: white; border: 2px solid transparent; border-radius: 25px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                <span>🌿 Semua Kegiatan</span>
            </button>
            <button class="filter-pill" data-filter="berlangsung" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.5rem; background: white; color: #555; border: 2px solid #e0e0e0; border-radius: 25px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                <span>🔴 Berlangsung</span>
            </button>
            <button class="filter-pill" data-filter="mendatang" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.5rem; background: white; color: #555; border: 2px solid #e0e0e0; border-radius: 25px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                <span>📅 Akan Datang</span>
            </button>
            <button class="filter-pill" data-filter="selesai" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.5rem; background: white; color: #555; border: 2px solid #e0e0e0; border-radius: 25px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                <span>✅ Selesai</span>
            </button>
        </div>
    </div>

    <!-- Kegiatan Grid -->
    <section class="kegiatan-section modern">
        <div class="kegiatan-grid-modern">
            <?php if (count($kegiatan_list) > 0): ?>
                <?php foreach ($kegiatan_list as $kegiatan): ?>
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
    </section>
</div>

<style>
/* Inline CSS untuk filter pills */
.filter-pill:hover {
    border-color: #4a7c29 !important;
    background: rgba(74, 124, 41, 0.05) !important;
    transform: translateY(-2px);
}

.filter-pill.active {
    background: linear-gradient(135deg, #4a7c29, #6ba544) !important;
    color: white !important;
    border-color: transparent !important;
    box-shadow: 0 4px 12px rgba(74, 124, 41, 0.3);
}
</style>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-pill');
    const kegiatanCards = document.querySelectorAll('.kegiatan-card-modern');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.background = 'white';
                btn.style.color = '#555';
                btn.style.borderColor = '#e0e0e0';
            });
            
            // Add active to clicked button
            this.classList.add('active');
            this.style.background = 'linear-gradient(135deg, #4a7c29, #6ba544)';
            this.style.color = 'white';
            this.style.borderColor = 'transparent';
            
            const filter = this.getAttribute('data-filter');
            
            // Filter cards
            kegiatanCards.forEach(card => {
                if (filter === 'semua') {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.5s ease';
                } else {
                    const status = card.getAttribute('data-status');
                    if (status === filter) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeInUp 0.5s ease';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    });
});
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>