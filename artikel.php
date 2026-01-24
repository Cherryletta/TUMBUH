<?php
include __DIR__ . '/inc/header.php'; 

$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';
$sql_terbaru = "SELECT * FROM artikel ORDER BY created_at_artikel DESC LIMIT 3";
$result_terbaru = mysqli_query($conn, $sql_terbaru);

if ($filter_kategori == 'semua') {
    $sql_artikel = "SELECT * FROM artikel ORDER BY created_at_artikel DESC";
} else {
    $sql_artikel = "SELECT * FROM artikel WHERE kategori_artikel = ? ORDER BY created_at_artikel DESC";
}

$stmt = mysqli_prepare($conn, $sql_artikel);
if ($filter_kategori != 'semua') {
    mysqli_stmt_bind_param($stmt, "s", $filter_kategori);
}
mysqli_stmt_execute($stmt);
$result_artikel = mysqli_stmt_get_result($stmt);
?>

<!-- HEADER ARTIKEL -->
<div class="container">
    <h2 class="title-underline">Artikel TUMBUH</h2>
    <div class="intro-text">Kumpulan artikel yang berisi edukasi, pandangan, tips, dan cerita seputar kepedulian serta aksi lingkungan.</div>
</div>

<!-- CONTAINER ARTIKEL -->
<div class="container" style="padding-top: 0;">
    <div class="artikel-page-container">
        <!-- MAIN CONTENT -->
        <div class="artikel-main-content">
            <!-- FILTER -->
            <div class="artikel-filter-bar">
                <div class="filter-label">
                    <i class="fas fa-filter"></i> Filter Kategori:
                </div>
                <div class="artikel-filter-pills">
                    <a href="artikel.php?kategori=semua" class="filter-pill-artikel <?php echo $filter_kategori == 'semua' ? 'active' : ''; ?>">
                        <i class="fas fa-th"></i> Semua
                    </a>
                    <a href="artikel.php?kategori=edukasi" class="filter-pill-artikel <?php echo $filter_kategori == 'edukasi' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap"></i> Edukasi
                    </a>
                    <a href="artikel.php?kategori=pandangan" class="filter-pill-artikel <?php echo $filter_kategori == 'pandangan' ? 'active' : ''; ?>">
                        <i class="fas fa-eye"></i> Pandangan
                    </a>
                    <a href="artikel.php?kategori=tips" class="filter-pill-artikel <?php echo $filter_kategori == 'tips' ? 'active' : ''; ?>">
                        <i class="fas fa-lightbulb"></i> Tips
                    </a>
                    <a href="artikel.php?kategori=cerita" class="filter-pill-artikel <?php echo $filter_kategori == 'cerita' ? 'active' : ''; ?>">
                        <i class="fas fa-book-open"></i> Cerita
                    </a>
                </div>
            </div>

            <!-- ARTIKEL LIST -->
            <div class="artikel-list-container">
                <?php if(mysqli_num_rows($result_artikel) > 0): ?>
                    <?php while($artikel = mysqli_fetch_assoc($result_artikel)): ?>
                        <a href="detail_artikel.php?id=<?php echo $artikel['id_artikel']; ?>" class="artikel-card-link">
                            <div class="artikel-item-horizontal">
                                <!-- GAMBAR -->
                                <div class="artikel-thumb">
                                    <?php if(!empty($artikel['gambar_artikel']) && file_exists("assets/img/artikel/".$artikel['gambar_artikel'])): ?>
                                        <img src="assets/img/artikel/<?php echo htmlspecialchars($artikel['gambar_artikel']); ?>" alt="<?php echo htmlspecialchars($artikel['judul_artikel']); ?>">
                                    <?php else: ?>
                                        <div class="artikel-thumb-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- BADGE KATEGORI -->
                                    <div class="artikel-kategori-badge badge-<?php echo $artikel['kategori_artikel']; ?>">
                                        <?php 
                                        $icons = [
                                            'edukasi' => 'fa-graduation-cap',
                                            'pandangan' => 'fa-eye',
                                            'tips' => 'fa-lightbulb',
                                            'cerita' => 'fa-book-open'
                                        ];
                                        echo '<i class="fas '.$icons[$artikel['kategori_artikel']].'"></i> '.strtoupper($artikel['kategori_artikel']);
                                        ?>
                                    </div>
                                </div>

                                <!-- KONTEN -->
                                <div class="artikel-text-content">
                                    <div class="artikel-meta-bar">
                                        <span class="meta-date">
                                            <i class="far fa-calendar"></i> 
                                            <?php echo date('d F Y', strtotime($artikel['tanggal_artikel'])); ?>
                                        </span>
                                        <span class="meta-divider">|</span>
                                        <span class="meta-source">
                                            <i class="fas fa-building"></i> 
                                            <?php echo htmlspecialchars($artikel['sumber_artikel']); ?>
                                        </span>
                                    </div>

                                    <h3 class="artikel-title-horizontal">
                                        <?php echo htmlspecialchars($artikel['judul_artikel']); ?>
                                    </h3>

                                    <p class="artikel-excerpt-horizontal">
                                        <?php 
                                        $isi = strip_tags($artikel['isi_artikel']);
                                        echo htmlspecialchars(substr($isi, 0, 130)) . '...'; 
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state-artikel">
                        <i class="fas fa-inbox"></i>
                        <h3>Belum Ada Artikel</h3>
                        <p>Saat ini belum ada artikel yang tersedia di kategori ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SIDEBAR -->
        <aside class="artikel-sidebar">
            <!-- POSTINGAN TERBARU -->
            <div class="sidebar-widget">
                <h3 class="widget-title">
                    <i class="fas fa-fire"></i> Postingan Terbaru
                </h3>
                <div class="widget-content">
                    <?php 
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
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- KATEGORI -->
            <div class="sidebar-widget">
                <h3 class="widget-title">
                    <i class="fas fa-tags"></i> Kategori
                </h3>
                <div class="widget-content">
                    <?php
                    $sql_count = "SELECT kategori_artikel, COUNT(*) as jumlah FROM artikel GROUP BY kategori_artikel";
                    $result_count = mysqli_query($conn, $sql_count);
                    ?>
                    <ul class="kategori-list">
                        <?php while($count = mysqli_fetch_assoc($result_count)): ?>
                            <li>
                                <a href="artikel.php?kategori=<?php echo $count['kategori_artikel']; ?>" class="kategori-link">
                                    <span class="kategori-name">
                                        <?php 
                                        $icons = [
                                            'edukasi' => 'fa-graduation-cap',
                                            'pandangan' => 'fa-eye',
                                            'tips' => 'fa-lightbulb',
                                            'cerita' => 'fa-book-open'
                                        ];
                                        echo '<i class="fas '.$icons[$count['kategori_artikel']].'"></i> ';
                                        echo ucfirst($count['kategori_artikel']); 
                                        ?>
                                    </span>
                                    <span class="kategori-count"><?php echo $count['jumlah']; ?></span>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>