<?php include __DIR__ . '/inc/header.php'; ?>

<div class="gabung-container">
<h2 class="title-underline">Gabung Sekarang</h2><br>

<?php if (isset($_SESSION['errors'])): ?>
    <div class="alert error">
        <?php foreach ($_SESSION['errors'] as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<form method="POST" action="auth/register_process.php">
    <div class="form-group">
        <label>Nama Lengkap *</label>
        <input type="text" name="nama" required>
    </div>
    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required>
    </div>
    <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" required minlength="6">
        <small>Minimal 6 karakter</small>
    </div>
    <div class="form-group">
        <label>Konfirmasi Password *</label>
        <input type="password" name="confirm_password" required>
    </div>
    <div class="form-group">
        <label>No. Telepon *</label>
        <input type="tel" name="telepon" required>
    </div>
    <div class="form-group">
        <label>Alamat</label>
        <textarea name="alamat" rows="3"></textarea>
    </div>
    <div class="form-group">
        <label>Bidang Minat *</label>
        <select name="bidang" required>
            <option value="">-- Pilih Bidang --</option>
            <option value="penanaman">Penanaman Pohon</option>
            <option value="edukasi">Edukasi Lingkungan</option>
            <option value="publikasi">Publikasi & Media</option>
            <option value="dokumentasi">Dokumentasi</option>
        </select>
    </div>
    <div class="form-group">
        <label>Motivasi Bergabung</label>
        <textarea name="motivasi" rows="4"></textarea>
    </div>
    <button type="submit">Daftar Sekarang</button>
</form>

<?php include __DIR__ . '/inc/footer.php'; ?>