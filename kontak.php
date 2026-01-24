<?php include __DIR__ . '/inc/header.php'; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert success">
        <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert error">
        <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container">
    <h2 class="title-underline">Hubungi Kami</h2>
    
    <div class="intro-text">
        Punya pertanyaan, saran, atau ingin berkolaborasi? Kami senang mendengar dari Anda!<br>
        Isi formulir di bawah ini atau hubungi kami melalui kontak yang tersedia.
    </div>

    <div class="kontak-wrapper">
        
        <!-- FORM KONTAK -->
        <div class="kontak-form-section">
            <h3>Kirim Pesan</h3>
            <form action="proses/proses_kontak.php" method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span style="color: #ff9800;">*</span></label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color: #ff9800;">*</span></label>
                    <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
                </div>

                <div class="form-group">
                    <label for="telepon">Nomor Telepon</label>
                    <input type="tel" id="telepon" name="telepon" placeholder="08xxxxxxxxxx">
                </div>

                <div class="form-group">
                    <label for="subjek">Subjek Pesan <span style="color: #ff9800;">*</span></label>
                    <input type="text" id="subjek" name="subjek" placeholder="Topik atau judul pesan Anda" required>
                </div>

                <div class="form-group">
                    <label for="pesan">Isi Pesan <span style="color: #ff9800;">*</span></label>
                    <textarea id="pesan" name="pesan" rows="6" placeholder="Tulis pesan Anda di sini..." required></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Kirim Pesan
                </button>
            </form>
        </div>

        <!-- INFO KONTAK -->
        <aside class="kontak-info-box">
            <h3>Informasi Kontak</h3>
            
            <div class="kontak-item">
                <div class="kontak-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="kontak-detail">
                    <h4>Alamat</h4>
                    <p>Jl. Basuki Rahmat No. 05<br>Palembang, Sumatera Selatan<br>Indonesia</p>
                </div>
            </div>

            <div class="kontak-item">
                <div class="kontak-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="kontak-detail">
                    <h4>Telepon</h4>
                    <p><a href="tel:+6281234567890">+62 895-6043-39518</a></p>
                </div>
            </div>

            <div class="kontak-item">
                <div class="kontak-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="kontak-detail">
                    <h4>Email</h4>
                    <p><a href="mailto:info@tumbuh.org">info@tumbuh.org</a></p>
                </div>
            </div>

            <div class="kontak-item">
                <div class="kontak-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="kontak-detail">
                    <h4>Jam Operasional</h4>
                    <p>Senin - Jumat: 09.00 - 16.00 WIB<br>Sabtu: 09.00 - 14.00 WIB<br>Minggu & Libur: Tutup</p>
                </div>
            </div>

            <div class="kontak-sosmed">
                <h4>Ikuti Kami</h4>
                <div class="social-links">
                    <a href="#" class="instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="facebook" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </aside>

    </div>

    <!-- PETA LOKASI (OPSIONAL) -->
    <div class="kontak-map-section">
        <h3>Lokasi Kami</h3>
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31876.118516652015!2d104.71015287431642!3d-2.9545456999999993!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3b7606b6ea9f07%3A0x83305e6d548171d!2sInstitut%20Teknologi%20dan%20Bisnis%20PalComTech!5e0!3m2!1sen!2sid!4v1769262500126!5m2!1sen!2sid" 
                width="100%" 
                height="400" 
                style="border:0; border-radius: 12px;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>

</div>

<?php include __DIR__ . '/inc/footer.php'; ?>