<?php include __DIR__ . '/inc/header.php'; ?>

<div class="container">
<h2 class="title-underline">Berita Lingkungan Terkini</h2>
<p class="subtitle">Inilah sejumlah kabar terbaru seputar lingkungan.</p>

    <div class="berita-list">
        <?php foreach ($berita_list as $berita): ?>
            <div class="berita-item2">
        <div class="berita-image2">
            <?php if (!empty($berita['gambar_berita'])): ?>
                <img src="assets/img/berita/<?php echo htmlspecialchars($berita['gambar_berita']); ?>" 
                    alt="<?php echo htmlspecialchars($berita['judul_berita']); ?>">
            <?php else: ?>
                <div class="berita-placeholder">📰</div>
            <?php endif; ?>

            <div class="berita-overlay">
                <span class="berita-category">
                    <i>🏷️</i> Lingkungan
                </span>
            </div>
        </div>

        <div class="berita-text">
            <h3><?php echo $berita['judul_berita']; ?></h3>
            <p class="meta"><?php echo $berita['tanggal_berita']; ?> | <?php echo $berita['sumber_berita']; ?></p>
            <p class="berita-ringkas">
                <?php echo substr(strip_tags($berita['isi_berita']), 0, 250); ?>...
            </p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

</div>

<?php include __DIR__ . '/inc/footer.php'; ?>