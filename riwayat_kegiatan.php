<?php include __DIR__ . '/inc/header.php'; ?>

<?php 
if (! $is_logged_in) { 
    header('Location: index.php'); 
    exit(); 
}

$user_id = $current_user['id_user'];
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';
$where_clause = "WHERE p.id_user = $user_id";
if ($filter_jenis != 'semua') {
    $where_clause .= " AND k.jenis_kegiatan = '" . mysqli_real_escape_string($conn, $filter_jenis) . "'";
}

$query_riwayat = mysqli_query($conn, "
    SELECT 
        k.id_kegiatan,
        k.jenis_kegiatan,
        k.judul_kegiatan,
        k.tanggal_kegiatan,
        k.waktu_kegiatan,
        k.lokasi_kegiatan,
        k.status_kegiatan,
        p.tanggal_daftar,
        p.status_kehadiran,
        p.catatan
    FROM pendaftaran_kegiatan p
    INNER JOIN kegiatan k ON p.id_kegiatan = k.id_kegiatan
    $where_clause
    ORDER BY p.tanggal_daftar DESC
");

$total_riwayat = mysqli_num_rows($query_riwayat);
?>

<div class="container">
    <!-- Page Header -->
    <div class="riwayat-page-header">
        <div class="breadcrumb">
            <a href="dashboard.php">👤 Profil</a>
            <span>›</span>
            <span>📋 Riwayat Kegiatan</span>
        </div>
        
        <h1>📋 Riwayat Kegiatan Saya</h1>
        <p class="riwayat-subtitle">Semua kegiatan yang pernah Anda ikuti di TUMBUH</p>
    </div>

    <!-- Filter Section -->
    <div class="riwayat-filter-section">
        <div class="filter-label">🔍 Filter Berdasarkan:</div>
        <div class="filter-buttons-row">
            <a href="?jenis=semua" class="filter-btn-riwayat <?php echo $filter_jenis == 'semua' ? 'active' : ''; ?>">
                <span class="filter-icon">📊</span>
                <span>Semua</span>
            </a>
            <a href="?jenis=Penanaman" class="filter-btn-riwayat <?php echo $filter_jenis == 'Penanaman' ? 'active' : ''; ?>">
                <span class="filter-icon">🌱</span>
                <span>Penanaman</span>
            </a>
            <a href="?jenis=Edukasi" class="filter-btn-riwayat <?php echo $filter_jenis == 'Edukasi' ? 'active' : ''; ?>">
                <span class="filter-icon">📚</span>
                <span>Edukasi</span>
            </a>
            <a href="?jenis=Kolaborasi" class="filter-btn-riwayat <?php echo $filter_jenis == 'Kolaborasi' ? 'active' : ''; ?>">
                <span class="filter-icon">🤝</span>
                <span>Kolaborasi</span>
            </a>
            <a href="?jenis=Kampanye" class="filter-btn-riwayat <?php echo $filter_jenis == 'Kampanye' ? 'active' : ''; ?>">
                <span class="filter-icon">📢</span>
                <span>Kampanye</span>
            </a>
            <a href="?jenis=Lainnya" class="filter-btn-riwayat <?php echo $filter_jenis == 'Lainnya' ? 'active' : ''; ?>">
                <span class="filter-icon">📦</span>
                <span>Lainnya</span>
            </a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="riwayat-info-banner">
        <div class="info-icon">ℹ️</div>
        <div class="info-text">
            Menampilkan <strong><?php echo $total_riwayat; ?></strong> kegiatan 
            <?php if ($filter_jenis != 'semua'): ?>
                dari kategori <strong><?php echo $filter_jenis; ?></strong>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat List -->
    <?php if ($total_riwayat > 0): ?>
        <div class="riwayat-timeline">
            <?php while ($row = mysqli_fetch_assoc($query_riwayat)): ?>
                <?php
                // Icon dan warna berdasarkan jenis
                $jenis_config = [
                    'Penanaman' => ['icon' => '🌱', 'color' => 'penanaman'],
                    'Edukasi' => ['icon' => '📚', 'color' => 'edukasi'],
                    'Kolaborasi' => ['icon' => '🤝', 'color' => 'kolaborasi'],
                    'Kampanye' => ['icon' => '📢', 'color' => 'kampanye'],
                    'Lainnya' => ['icon' => '📦', 'color' => 'lainnya']
                ];
                $config = $jenis_config[$row['jenis_kegiatan']] ?? ['icon' => '📦', 'color' => 'lainnya'];

                // Format tanggal
                $tgl_daftar = date('d M Y, H:i', strtotime($row['tanggal_daftar']));
                ?>

                <div class="riwayat-timeline-item">
                    <div class="timeline-marker timeline-marker-<?php echo $config['color']; ?>">
                        <?php echo $config['icon']; ?>
                    </div>
                    
                    <div class="riwayat-card">
                        <div class="riwayat-card-header">
                            <div class="riwayat-jenis-badge badge-<?php echo $config['color']; ?>">
                                <?php echo $config['icon']; ?> <?php echo $row['jenis_kegiatan']; ?>
                            </div>
                            
                            <div class="riwayat-status-badge status-<?php echo $row['status_kegiatan']; ?>">
                                <?php 
                                $status_text = [
                                    'berlangsung' => '🟢 Berlangsung',
                                    'mendatang' => '🔵 Mendatang',
                                    'selesai' => '⚪ Selesai'
                                ];
                                echo $status_text[$row['status_kegiatan']] ?? $row['status_kegiatan'];
                                ?>
                            </div>
                        </div>

                        <h3 class="riwayat-card-title"><?php echo $row['judul_kegiatan']; ?></h3>

                        <div class="riwayat-card-details">
                            <div class="detail-item">
                                <span class="detail-icon">📅</span>
                                <span class="detail-label">Tanggal Kegiatan:</span>
                                <span class="detail-value"><?php echo $row['tanggal_kegiatan']; ?></span>
                            </div>

                            <div class="detail-item">
                                <span class="detail-icon">🕐</span>
                                <span class="detail-label">Waktu:</span>
                                <span class="detail-value"><?php echo $row['waktu_kegiatan'] ?: '-'; ?></span>
                            </div>

                            <div class="detail-item">
                                <span class="detail-icon">📍</span>
                                <span class="detail-label">Lokasi:</span>
                                <span class="detail-value"><?php echo $row['lokasi_kegiatan']; ?></span>
                            </div>

                            <div class="detail-item">
                                <span class="detail-icon">📝</span>
                                <span class="detail-label">Tanggal Daftar:</span>
                                <span class="detail-value"><?php echo $tgl_daftar; ?></span>
                            </div>

                            <div class="detail-item">
                                <span class="detail-icon">✅</span>
                                <span class="detail-label">Status Kehadiran:</span>
                                <span class="detail-value status-kehadiran-<?php echo $row['status_kehadiran']; ?>">
                                    <?php 
                                    $kehadiran_text = [
                                        'terdaftar' => '📌 Terdaftar',
                                        'hadir' => '✅ Hadir',
                                        'tidak_hadir' => '❌ Tidak Hadir'
                                    ];
                                    echo $kehadiran_text[$row['status_kehadiran']] ?? $row['status_kehadiran'];
                                    ?>
                                </span>
                            </div>

                            <?php if (!empty($row['catatan'])): ?>
                            <div class="detail-item detail-catatan">
                                <span class="detail-icon">💬</span>
                                <span class="detail-label">Catatan:</span>
                                <span class="detail-value"><?php echo $row['catatan']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="riwayat-card-footer">
                            <a href="detail_kegiatan.php?id=<?php echo $row['id_kegiatan']; ?>" class="btn-detail-riwayat">
                                <span>Lihat Detail</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="riwayat-empty-state">
            <div class="empty-icon">📭</div>
            <h3>Belum Ada Riwayat Kegiatan</h3>
            <p>Anda belum pernah mendaftar kegiatan <?php echo $filter_jenis != 'semua' ? 'jenis ' . $filter_jenis : 'apapun'; ?>.</p>
            <a href="kegiatan.php" class="btn-cta-empty">
                <span>🌿</span>
                <span>Jelajahi Kegiatan</span>
            </a>
        </div>
    <?php endif; ?>

    <!-- Back to Profile Button -->
    <div class="back-to-profile">
        <a href="profil.php" class="btn-back">
            <span>←</span>
            <span>Kembali ke Profil</span>
        </a>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>