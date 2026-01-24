<?php include __DIR__ . '/inc/header.php'; ?>

<?php
// ==================== PAGINATION KEGIATAN ====================
$limit = 6; // jumlah kegiatan per halaman
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// hitung total kegiatan
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kegiatan");
$total_data  = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// ambil kegiatan per halaman
$kegiatan_list = [];
$query = "
    SELECT *
    FROM kegiatan
    ORDER BY tanggal_kegiatan DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $kegiatan_list[] = $row;
}
?>

<div class="container kegiatan-page-container">
    <div class="container" style="padding: 2rem 1rem;">
        <h2 class="title-underline">Program & Aktivitas TUMBUH</h2>
        <div class="intro-text">Bergabunglah dalam berbagai kegiatan untuk pelestarian lingkungan dan penanaman pohon</div>
    </div>

    <!-- Filter Buttons -->
    <div class="kegiatan-filter-section">
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
                    <?php 
                    // Hitung jumlah pendaftar untuk kegiatan ini
                    $kegiatan_id = $kegiatan['id_kegiatan'];
                    $sql_count = "SELECT COUNT(*) as jumlah FROM pendaftaran_kegiatan WHERE id_kegiatan = ?";
                    $stmt_count = mysqli_prepare($conn, $sql_count);
                    mysqli_stmt_bind_param($stmt_count, "i", $kegiatan_id);
                    mysqli_stmt_execute($stmt_count);
                    $result_count = mysqli_stmt_get_result($stmt_count);
                    $count_data = mysqli_fetch_assoc($result_count);
                    $jumlah_pendaftar = $count_data['jumlah'] ?? 0;
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
        </div>
    </section>
<?php if ($total_pages > 1): ?>
<div class="all-pagination">

    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>" class="page-btn">« Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>"
           class="page-btn <?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>" class="page-btn">Next »</a>
    <?php endif; ?>

</div>
<?php endif; ?>

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

/* Kegiatan Card Link */
.kegiatan-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.kegiatan-card-link:hover {
    text-decoration: none;
}

/* Modern Quota Info */
.quota-info-modern {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.8rem 1rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    border-left: 4px solid #4caf50;
    transition: all 0.3s ease;
}

.quota-info-modern.quota-low {
    background: linear-gradient(135deg, #fff3e0, #ffe0b2);
    border-left-color: #ff9800;
}

.quota-info-modern.quota-full {
    background: linear-gradient(135deg, #ffebee, #ffcdd2);
    border-left-color: #f44336;
}

.quota-icon {
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    flex-shrink: 0;
}

.quota-text {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.quota-numbers {
    font-size: 1rem;
    font-weight: 700;
    color: #2e7d32;
    line-height: 1;
}

.quota-info-modern.quota-low .quota-numbers {
    color: #e65100;
}

.quota-info-modern.quota-full .quota-numbers {
    color: #c62828;
}

.quota-label {
    font-size: 0.75rem;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
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
            
            // Filter cards (filter parent link, not card itself)
            document.querySelectorAll('.kegiatan-card-link').forEach(link => {
                const card = link.querySelector('.kegiatan-card-modern');
                if (filter === 'semua') {
                    link.style.display = 'block';
                    link.style.animation = 'fadeInUp 0.5s ease';
                } else {
                    const status = card.getAttribute('data-status');
                    if (status === filter) {
                        link.style.display = 'block';
                        link.style.animation = 'fadeInUp 0.5s ease';
                    } else {
                        link.style.display = 'none';
                    }
                }
            });
        });
    });
});
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>