<?php include __DIR__ . '/inc/header.php'; ?>

<?php
// ==================== PAGINATION KEGIATAN ====================
$limit = 6;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kegiatan");
$total_data  = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

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
    <div class="container kegiatan-page-intro">
        <h2 class="title-underline">Program & Aktivitas TUMBUH</h2>
        <div class="intro-text">Berbagai kegiatan dan program lingkungan yang dirancang sebagai bentuk aksi nyata TUMBUH dalam menjaga dan merawat lingkungan bersama masyarakat.</div>
    </div>

    <!-- Filter Buttons -->
    <div class="kegiatan-filter-section">
        <div class="filter-container kegiatan-filter-pills">
            <button class="filter-pill active" data-filter="semua">
                <span>🌿 Semua Kegiatan</span>
            </button>
            <button class="filter-pill" data-filter="berlangsung">
                <span>🔴 Berlangsung</span>
            </button>
            <button class="filter-pill" data-filter="mendatang">
                <span>📅 Akan Datang</span>
            </button>
            <button class="filter-pill" data-filter="selesai">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-pill');
    const kegiatanCards = document.querySelectorAll('.kegiatan-card-modern');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active to clicked button
            this.classList.add('active');
            
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