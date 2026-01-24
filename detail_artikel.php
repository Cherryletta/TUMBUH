<?php
include __DIR__ . '/inc/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo "<div class='container'><p>Artikel tidak valid.</p></div>";
    include __DIR__ . '/inc/footer.php';
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM artikel WHERE id_artikel = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$artikel = mysqli_fetch_assoc($result);

if (!$artikel) {
    echo "<div class='container'><p>Artikel tidak ditemukan.</p></div>";
    include __DIR__ . '/inc/footer.php';
    exit;
}

$sql_terbaru = "SELECT * FROM artikel WHERE id_artikel != ? ORDER BY created_at_artikel DESC LIMIT 3";
$stmt_terbaru = mysqli_prepare($conn, $sql_terbaru);
mysqli_stmt_bind_param($stmt_terbaru, "i", $id);
mysqli_stmt_execute($stmt_terbaru);
$result_terbaru = mysqli_stmt_get_result($stmt_terbaru);

$sql_count = "SELECT kategori_artikel, COUNT(*) as jumlah FROM artikel GROUP BY kategori_artikel";
$result_count = mysqli_query($conn, $sql_count);

$sql_related = "SELECT * FROM artikel WHERE kategori_artikel = ? AND id_artikel != ? ORDER BY created_at_artikel DESC LIMIT 3";
$stmt_related = mysqli_prepare($conn, $sql_related);
mysqli_stmt_bind_param($stmt_related, "si", $artikel['kategori_artikel'], $id);
mysqli_stmt_execute($stmt_related);
$result_related = mysqli_stmt_get_result($stmt_related);
?>

<!-- ARTIKEL PAGE WRAPPER -->
<div class="artikel-page-wrapper detail-artikel-wrapper">
    
    <!-- BREADCRUMB -->
    <div class="detail-breadcrumb-container">
        <div class="breadcrumb detail-breadcrumb">
            <a href="index.php">
                <i class="fas fa-home"></i> Beranda
            </a>
            <span>/</span>
            <a href="artikel.php">
                <i class="fas fa-newspaper"></i> Artikel
            </a>
            <span>/</span>
            <span class="breadcrumb-current">
                <?php echo htmlspecialchars(substr($artikel['judul_artikel'], 0, 40)) . (strlen($artikel['judul_artikel']) > 40 ? '...' : ''); ?>
            </span>
        </div>
    </div>

    <!-- MAIN CONTENT WITH SIDEBAR -->
    <div class="detail-artikel-main-container">
        <div class="detail-artikel-grid">
            
            <!-- LEFT CONTENT - ARTIKEL DETAIL -->
            <div class="detail-artikel-content">
                <!-- ARTIKEL CARD -->
                <article class="detail-artikel-card">
                    
                    <!-- HEADER ARTIKEL -->
                    <div class="detail-artikel-header">
                        <!-- JUDUL -->
                        <h1 class="detail-artikel-title">
                            <?php echo htmlspecialchars($artikel['judul_artikel']); ?>
                        </h1>

                        <!-- META INFO -->
                        <div class="artikel-meta-bar">
                            <span class="meta-date">
                                <i class="far fa-calendar"></i> 
                                <?php echo date('d F Y', strtotime($artikel['tanggal_artikel'])); ?>
                            </span>
                            <?php if (!empty($artikel['sumber_artikel'])): ?>
                            <span class="meta-divider">|</span>
                            <span class="meta-source">
                                <i class="fas fa-building"></i> 
                                <?php echo htmlspecialchars($artikel['sumber_artikel']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- GAMBAR ARTIKEL (FEATURED IMAGE) -->
                    <?php if (!empty($artikel['gambar_artikel']) && file_exists("assets/img/artikel/".$artikel['gambar_artikel'])): ?>
                    <div class="detail-artikel-image-container">
                        <div class="detail-artikel-image-wrapper">
                            <img src="assets/img/artikel/<?php echo htmlspecialchars($artikel['gambar_artikel']); ?>" 
                                 alt="<?php echo htmlspecialchars($artikel['judul_artikel']); ?>"
                                 class="detail-artikel-image">
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- KONTEN ARTIKEL -->
                    <div class="detail-artikel-body">
                        <div class="artikel-content-detail">
                        <?php
                        $isi = $artikel['isi_artikel'];
                        $isi = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $isi);
                        echo nl2br(htmlspecialchars($isi));
                        ?>
                        </div>
                    </div>

                    <!-- FOOTER ARTIKEL -->
                    <div class="detail-artikel-footer">
                        <div class="detail-artikel-footer-content">
                            <a href="artikel.php" class="btn-back">
                                <i class="fas fa-arrow-left"></i> Kembali ke Artikel
                            </a>
                            
                            <div class="detail-artikel-share">
                                <span class="share-label">
                                    <i class="fas fa-share-alt"></i> Bagikan:
                                </span>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="share-btn share-fb">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($artikel['judul_artikel']); ?>" 
                                   target="_blank" 
                                   class="share-btn share-twitter">
                                    <i class="fab fa-x-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode($artikel['judul_artikel'].' - http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="share-btn share-wa">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </article>

                <!-- RELATED POSTS -->
                <?php if (mysqli_num_rows($result_related) > 0): ?>
                <div class="detail-related-section">
                    <div class="detail-related-header">
                        <h2 class="detail-related-title">
                            <i class="fas fa-newspaper"></i>
                            Artikel Terkait
                        </h2>
                        <p class="detail-related-subtitle">
                            Baca juga artikel lain dalam kategori <?php echo ucfirst($artikel['kategori_artikel']); ?>
                        </p>
                    </div>

                    <div class="detail-related-grid">
                        <?php while($related = mysqli_fetch_assoc($result_related)): ?>
                        <a href="detail_artikel.php?id=<?php echo $related['id_artikel']; ?>" 
                           class="detail-related-card-link">
                            <div class="detail-related-card">
                                <!-- IMAGE -->
                                <div class="detail-related-image">
                                    <?php if(!empty($related['gambar_artikel']) && file_exists("assets/img/artikel/".$related['gambar_artikel'])): ?>
                                        <img src="assets/img/artikel/<?php echo htmlspecialchars($related['gambar_artikel']); ?>" 
                                             alt="<?php echo htmlspecialchars($related['judul_artikel']); ?>"
                                             class="detail-related-img">
                                    <?php else: ?>
                                        <div class="detail-related-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- BADGE -->
                                    <div class="artikel-kategori-badge badge-<?php echo $related['kategori_artikel']; ?>">
                                        <?php 
                                        $icon = isset($icons[$related['kategori_artikel']]) ? $icons[$related['kategori_artikel']] : 'fa-file-alt';
                                        echo '<i class="fas '.$icon.'"></i> '.strtoupper($related['kategori_artikel']);
                                        ?>
                                    </div>
                                </div>
                                
                                <!-- CONTENT -->
                                <div class="detail-related-content">
                                    <div class="artikel-meta-bar detail-related-meta">
                                        <span class="meta-date">
                                            <i class="far fa-calendar"></i> 
                                            <?php echo date('d M Y', strtotime($related['tanggal_artikel'])); ?>
                                        </span>
                                    </div>
                                    
                                    <h3 class="detail-related-card-title">
                                        <?php echo htmlspecialchars($related['judul_artikel']); ?>
                                    </h3>
                                    
                                    <p class="detail-related-excerpt">
                                        <?php 
                                        $excerpt = strip_tags($related['isi_artikel']);
                                        echo htmlspecialchars(substr($excerpt, 0, 100)) . '...';
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- RIGHT SIDEBAR -->
            <aside class="artikel-sidebar detail-artikel-sidebar">
                
                <!-- POSTINGAN TERBARU -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i class="fas fa-fire"></i> Postingan Terbaru
                    </h3>
                    <div class="widget-content">
                        <?php 
                        if (mysqli_num_rows($result_terbaru) > 0):
                            mysqli_data_seek($result_terbaru, 0);
                            while($terbaru = mysqli_fetch_assoc($result_terbaru)): 
                        ?>
                            <a href="detail_artikel.php?id=<?php echo $terbaru['id_artikel']; ?>" class="sidebar-artikel-item">
                                <div class="sidebar-artikel-thumb">
                                    <?php if(!empty($terbaru['gambar_artikel']) && file_exists("assets/img/artikel/".$terbaru['gambar_artikel'])): ?>
                                        <img src="assets/img/artikel/<?php echo htmlspecialchars($terbaru['gambar_artikel']); ?>" alt="">
                                    <?php else: ?>
                                        <div class="sidebar-thumb-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="sidebar-artikel-info">
                                    <h4><?php echo htmlspecialchars($terbaru['judul_artikel']); ?></h4>
                                    <span class="sidebar-date">
                                        <i class="far fa-clock"></i> 
                                        <?php echo date('d M Y', strtotime($terbaru['tanggal_artikel'])); ?>
                                    </span>
                                </div>
                            </a>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <p class="sidebar-empty-text">
                                Tidak ada artikel lain
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KATEGORI -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i class="fas fa-tags"></i> Kategori
                    </h3>
                    <ul class="kategori-list">
                        <?php 
                        mysqli_data_seek($result_count, 0);
                        while($count = mysqli_fetch_assoc($result_count)): 
                        ?>
                            <li>
                                <a href="artikel.php?kategori=<?php echo $count['kategori_artikel']; ?>" class="kategori-link">
                                    <span class="kategori-name">
                                        <?php 
                                        $icon = isset($icons[$count['kategori_artikel']]) ? $icons[$count['kategori_artikel']] : 'fa-file-alt';
                                        echo '<i class="fas '.$icon.'"></i> ';
                                        echo ucfirst($count['kategori_artikel']); 
                                        ?>
                                    </span>
                                    <span class="kategori-count"><?php echo $count['jumlah']; ?></span>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

            </aside>

        </div>
    </div>
</div>

<?php
include __DIR__ . '/inc/footer.php';
?>