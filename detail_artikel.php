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

<!-- ==================== ARTIKEL PAGE WRAPPER ==================== -->
<div class="artikel-page-wrapper" style="padding-top: 2rem;">
    
    <!-- ==================== BREADCRUMB ==================== -->
    <div style="max-width: 1300px; margin: 0 auto; padding: 1.5rem 2rem 0.5rem;">
        <div class="breadcrumb" style="background: white; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: var(--shadow-sm); display: inline-flex;">
            <a href="index.php">
                <i class="fas fa-home"></i> Beranda
            </a>
            <span>/</span>
            <a href="artikel.php">
                <i class="fas fa-newspaper"></i> Artikel
            </a>
            <span>/</span>
            <span style="color: var(--text-dark); font-weight: 600;"><?php echo htmlspecialchars(substr($artikel['judul_artikel'], 0, 40)) . (strlen($artikel['judul_artikel']) > 40 ? '...' : ''); ?></span>
        </div>
    </div>

    <!-- ==================== MAIN CONTENT WITH SIDEBAR ==================== -->
    <div style="max-width: 1300px; margin: 0 auto; padding: 0 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 3rem; align-items: start;">
            
            <!-- LEFT CONTENT - ARTIKEL DETAIL -->
            <div>
                <!-- ARTIKEL CARD -->
                <article style="background: white; border-radius: 15px; box-shadow: var(--shadow-md); overflow: hidden; border: 1px solid var(--pale-green);">
                    
                    <!-- HEADER ARTIKEL -->
                    <div style="padding: 2.5rem 3rem 1.5rem;">
                        <!-- JUDUL -->
                        <h1 style="font-size: 2.5rem; color: var(--text-dark); margin-bottom: 1.5rem; line-height: 1.3; font-weight: 800;">
                            <?php echo htmlspecialchars($artikel['judul_artikel']); ?>
                        </h1>

                        <!-- META INFO -->
                        <div class="artikel-meta-bar" style="margin-bottom: 0;">
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
                    <div style="padding: 0 3rem 2rem;">
                        <div style="width: 100%; max-height: 500px; overflow: hidden; border-radius: 12px; box-shadow: var(--shadow-md);">
                            <img src="assets/img/artikel/<?php echo htmlspecialchars($artikel['gambar_artikel']); ?>" 
                                 alt="<?php echo htmlspecialchars($artikel['judul_artikel']); ?>"
                                 style="width: 100%; height: auto; display: block; object-fit: cover;">
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- KONTEN ARTIKEL -->
                    <div style="padding: 3rem;">
                        <div class="artikel-content-detail">
                        <?php
                        $isi = $artikel['isi_artikel'];
                        $isi = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $isi);
                        echo nl2br(htmlspecialchars($isi));
                        ?>
                        </div>
                    </div>

                    <!-- FOOTER ARTIKEL -->
                    <div style="padding: 2rem 3rem; background: #f9f9f9; border-top: 1px solid var(--border-light);">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <a href="artikel.php" class="btn-back" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-arrow-left"></i> Kembali ke Artikel
                            </a>
                            
                            <div style="display: flex; gap: 1rem;">
                                <span style="color: var(--text-gray); font-size: 0.9rem;">
                                    <i class="fas fa-share-alt"></i> Bagikan:
                                </span>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   style="color: #1877F2; font-size: 1.2rem; transition: var(--transition-base);"
                                   onmouseover="this.style.transform='scale(1.2)'"
                                   onmouseout="this.style.transform='scale(1)'">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($artikel['judul_artikel']); ?>" 
                                   target="_blank" 
                                   style="color: #000; font-size: 1.2rem; transition: var(--transition-base);"
                                   onmouseover="this.style.transform='scale(1.2)'"
                                   onmouseout="this.style.transform='scale(1)'">
                                    <i class="fab fa-x-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode($artikel['judul_artikel'].' - http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   style="color: #25D366; font-size: 1.2rem; transition: var(--transition-base);"
                                   onmouseover="this.style.transform='scale(1.2)'"
                                   onmouseout="this.style.transform='scale(1)'">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </article>

                <!-- ==================== RELATED POSTS ==================== -->
                <?php if (mysqli_num_rows($result_related) > 0): ?>
                <div style="margin-top: 4rem;">
                    <div style="text-align: center; margin-bottom: 2.5rem;">
                        <h2 style="color: var(--primary-green); font-size: 2rem; margin-bottom: 0.5rem; font-weight: 700;">
                            <i class="fas fa-newspaper" style="color: var(--primary-orange); margin-right: 0.5rem;"></i>
                            Artikel Terkait
                        </h2>
                        <p style="color: var(--text-gray); font-size: 1rem;">
                            Baca juga artikel lain dalam kategori <?php echo ucfirst($artikel['kategori_artikel']); ?>
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
                        <?php while($related = mysqli_fetch_assoc($result_related)): ?>
                        <a href="detail_artikel.php?id=<?php echo $related['id_artikel']; ?>" 
                           style="text-decoration: none; display: block; background: white; border-radius: 15px; overflow: hidden; box-shadow: var(--shadow-md); border: 1px solid var(--pale-green); transition: var(--transition-base);"
                           onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='var(--shadow-lg)'"
                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-md)'">
                            
                            <!-- IMAGE -->
                            <div style="height: 180px; overflow: hidden; background: var(--gradient-pale); position: relative;">
                                <?php if(!empty($related['gambar_artikel']) && file_exists("assets/img/artikel/".$related['gambar_artikel'])): ?>
                                    <img src="assets/img/artikel/<?php echo htmlspecialchars($related['gambar_artikel']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['judul_artikel']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                                         onmouseover="this.style.transform='scale(1.1)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: rgba(45, 80, 22, 0.3);">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- BADGE -->
                                <div class="artikel-kategori-badge badge-<?php echo $related['kategori_artikel']; ?>" 
                                     style="position: absolute; top: 1rem; left: 1rem; font-size: 0.75rem; padding: 0.4rem 0.8rem;">
                                    <?php 
                                    $icon = isset($icons[$related['kategori_artikel']]) ? $icons[$related['kategori_artikel']] : 'fa-file-alt';
                                    echo '<i class="fas '.$icon.'"></i> '.strtoupper($related['kategori_artikel']);
                                    ?>
                                </div>
                            </div>
                            
                            <!-- CONTENT -->
                            <div style="padding: 1.5rem;">
                                <div class="artikel-meta-bar" style="margin-bottom: 0.8rem;">
                                    <span class="meta-date" style="font-size: 0.8rem;">
                                        <i class="far fa-calendar"></i> 
                                        <?php echo date('d M Y', strtotime($related['tanggal_artikel'])); ?>
                                    </span>
                                </div>
                                
                                <h3 style="color: var(--primary-green); font-size: 1.1rem; margin-bottom: 0.8rem; font-weight: 700; line-height: 1.4;">
                                    <?php echo htmlspecialchars($related['judul_artikel']); ?>
                                </h3>
                                
                                <p style="color: var(--text-gray); font-size: 0.9rem; line-height: 1.5; margin: 0;">
                                    <?php 
                                    $excerpt = strip_tags($related['isi_artikel']);
                                    echo htmlspecialchars(substr($excerpt, 0, 100)) . '...';
                                    ?>
                                </p>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- RIGHT SIDEBAR -->
            <aside class="artikel-sidebar" style="position: sticky; top: 100px;">
                
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
                            <p style="text-align: center; color: var(--text-light); padding: 1rem; font-size: 0.9rem;">
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