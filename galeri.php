<?php include __DIR__ . '/inc/header.php'; ?>

<div class="container">
    <h2 class="title-underline">Galeri Kegiatan</h2>
    <p class="subtitle">Saksikan momen-momen indah dari kegiatan TUMBUH</p>

    <?php
    // Ambil data kegiatan dari database dengan foto-nya
    $kegiatan_query = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY created_at_kegiatan DESC");
    $kegiatan_list = [];
    
    while ($row = mysqli_fetch_assoc($kegiatan_query)) {
        // Ambil foto untuk kegiatan ini (limit 3)
        $id_k = $row['id_kegiatan'];
        $foto_query = mysqli_query($conn, "SELECT * FROM galeri WHERE id_kegiatan = $id_k ORDER BY tanggal_upload_galeri DESC LIMIT 3");
        $fotos = mysqli_fetch_all($foto_query, MYSQLI_ASSOC);
        
        // Jika kurang dari 3 foto, tambahi dengan placeholder
        while (count($fotos) < 3) {
            $fotos[] = ['foto_galeri' => 'assets/img/placeholder-galeri.jpg', 'deskripsi_galeri' => 'Tidak ada foto'];
        }
        
        $row['fotos'] = $fotos;
        $kegiatan_list[] = $row;
    }

    // Pagination
    $items_per_page = 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $total_items = count($kegiatan_list);
    $total_pages = ceil($total_items / $items_per_page);

    // Validasi halaman
    if ($current_page < 1) $current_page = 1;
    if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

    // Hitung offset
    $offset = ($current_page - 1) * $items_per_page;
    $paginated_items = array_slice($kegiatan_list, $offset, $items_per_page);
    ?>

    <?php if (count($kegiatan_list) > 0): ?>
        <!-- Carousel Galeri per Kegiatan -->
        <div class="kegiatan-gallery-list">
            <?php foreach ($paginated_items as $kegiatan): ?>
                <div class="kegiatan-gallery-card">
                    <div class="kegiatan-info-box">
                        <h3 style="text-align: justify; font-weight: bold; font-size: 25px;"><?php echo $kegiatan['judul_kegiatan']; ?></h3>
                        <p class="kegiatan-meta">
                            <strong>📍 Lokasi:</strong> <?php echo $kegiatan['lokasi_kegiatan']; ?><br>
                            <strong>📅 Tanggal:</strong> <?php echo date('d F Y', strtotime($kegiatan['tanggal_kegiatan'])); ?>
                        </p>
                    </div>

                    <!-- Carousel -->
                    <div class="carousel-container" data-carousel-id="<?php echo $kegiatan['id_kegiatan']; ?>">
                        <div class="carousel-wrapper">
                            <div class="carousel-track">
                                <?php 
                                foreach ($kegiatan['fotos'] as $foto): 
                                ?>
                                    <div class="carousel-slide">
                                        <img src="<?php echo $foto['foto_galeri']; ?>" alt="Foto Kegiatan" onerror="this.src='assets/img/placeholder-galeri.jpg'">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Arrow Navigation -->
                            <button class="carousel-btn prev" onclick="moveCarousel(this, -1)">❮</button>
                            <button class="carousel-btn next" onclick="moveCarousel(this, 1)">❯</button>
                        </div>

                        <!-- Indicators -->
                        <div class="carousel-indicators">
                            <?php for ($i = 0; $i < count($kegiatan['fotos']); $i++): ?>
                                <span class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" onclick="currentSlide(this, <?php echo $i; ?>)"></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="galeri-pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-btn prev">← Sebelumnya</a>
            <?php endif; ?>

            <div class="pagination-numbers">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="pagination-num <?php echo $i == $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-btn next">Selanjutnya →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?><br>

        <!-- Info -->
        <div class="galeri-info-text">
            <p>Menampilkan <?php echo count($paginated_items); ?> dari <?php echo $total_items; ?> kegiatan</p>
        </div>

    <?php else: ?>
        <div class="no-data" style="text-align: center; padding: 3rem; color: #666;">
            <p style="font-size: 1.1rem;">Belum ada kegiatan yang terdaftar.</p>
        </div>
    <?php endif; ?>

</div>

<!-- JavaScript untuk Carousel -->
<script>
function moveCarousel(button, direction) {
    const carouselContainer = button.closest('.carousel-container');
    const track = carouselContainer.querySelector('.carousel-track');
    const slides = carouselContainer.querySelectorAll('.carousel-slide');
    const indicators = carouselContainer.querySelectorAll('.indicator');
    
    // Hitung current index dari scroll position
    const slideWidth = slides[0].offsetWidth;
    let currentIndex = Math.round(track.scrollLeft / slideWidth);
    
    // Hitung next index
    let nextIndex = currentIndex + direction;
    
    // Loop jika sudah di awal atau akhir
    if (nextIndex < 0) {
        nextIndex = slides.length - 1;
    } else if (nextIndex >= slides.length) {
        nextIndex = 0;
    }
    
    // Scroll ke slide berikutnya
    track.scrollLeft = nextIndex * slideWidth;
    
    // Update indicators
    indicators.forEach(ind => ind.classList.remove('active'));
    indicators[nextIndex].classList.add('active');
}

function currentSlide(indicator, index) {
    const indicators = indicator.closest('.carousel-indicators').querySelectorAll('.indicator');
    const carouselContainer = indicator.closest('.carousel-container');
    const track = carouselContainer.querySelector('.carousel-track');
    const slides = carouselContainer.querySelectorAll('.carousel-slide');
    
    // Update scroll
    track.scrollLeft = index * slides[0].offsetWidth;
    
    // Update indicators
    indicators.forEach(ind => ind.classList.remove('active'));
    indicator.classList.add('active');
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
        document.querySelectorAll('.carousel-btn.prev')[0]?.click();
    }
    if (e.key === 'ArrowRight') {
        document.querySelectorAll('.carousel-btn.next')[0]?.click();
    }
});
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>