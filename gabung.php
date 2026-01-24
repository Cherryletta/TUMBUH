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

<form method="POST" action="proses/register.php">
    <div class="form-group">
        <label>Nama Lengkap *</label>
        <input type="text" name="nama_user" required>
    </div>

    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email_user" required>
    </div>

    <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password_user" required minlength="6">
    </div>

    <div class="form-group">
        <label>Konfirmasi Password *</label>
        <input type="password" name="confirm_password" required>
    </div>

    <div class="form-group">
        <label>No. Telepon *</label>
        <input type="tel" name="telepon_user" required>
    </div>

    <div class="form-group">
        <label>Alamat</label>
        <textarea name="alamat_user"></textarea>
    </div>

    <div class="form-group">
        <label>Motivasi Bergabung</label>
        <textarea name="motivasi_user"></textarea>
    </div>

    <button type="submit">Daftar Sekarang</button>
</form>

<?php include __DIR__ . '/inc/footer.php'; ?>