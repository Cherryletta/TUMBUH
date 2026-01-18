<?php
// PANGGIL HEADER (INI PENTING)
include __DIR__ . '/inc/header.php';

// AMBIL ID DARI URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo "<div class='container'><p>Artikel tidak valid.</p></div>";
    include __DIR__ . '/inc/footer.php';
    exit;
}

// AMBIL DATA ARTIKEL
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
?>

<div class="container artikel-detail">

    <h1 class="artikel-title">
        <?php echo htmlspecialchars($artikel['judul_artikel']); ?>
    </h1>

    <div class="artikel-meta">
        <span><?php echo date('d F Y', strtotime($artikel['tanggal_artikel'])); ?></span>
        <span> | </span>
        <span><?php echo htmlspecialchars($artikel['sumber_artikel']); ?></span>
    </div>

    <?php if (!empty($artikel['gambar_artikel'])): ?>
        <div class="artikel-image">
            <img 
                src="assets/img/artikel/<?php echo htmlspecialchars($artikel['gambar_artikel']); ?>" 
                alt="<?php echo htmlspecialchars($artikel['judul_artikel']); ?>"
            >
        </div>
    <?php endif; ?>

    <div class="artikel-content">
        <?php
        $paragraf = explode("\n\n", $artikel['isi_artikel']);
        foreach ($paragraf as $p) {
            echo "<p>" . nl2br(htmlspecialchars($p)) . "</p>";
        }
        ?>
    </div>

    <a href="artikel.php" class="btn-back-artikel">← Kembali ke Artikel</a>

</div>

<?php
// PANGGIL FOOTER
include __DIR__ . '/inc/footer.php';
