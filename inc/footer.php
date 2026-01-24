</div>

    <!-- Modal Login -->
    <div id="modal-login" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('login')">&times;</button>
            <h2 style="color: #2d5016;">Masuk ke Akun</h2>
            
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert error"><?php echo $_SESSION['login_error']; ?></div>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>
            
            <form method="POST" action="proses/login.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email_user" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password_user" required>
                </div>
                <button type="submit">Masuk</button>
            </form>
        </div>
    </div>

    <!-- Footer Modern -->
    <footer class="footer-modern">
        <div class="footer-container">
            <!-- Footer Top Section -->
            <div class="footer-top">
                <!-- Kolom 1: Logo & Deskripsi -->
                <div class="footer-column footer-about">
                    <div class="footer-logo">
                        <img src="assets/img/home/logo_tumbuh.png" alt="TUMBUH Logo">
                    </div>
                    <p class="footer-tagline">Tanam Untuk Bumi Hijau</p>
                    <p class="footer-description">
                        Gerakan hijau Indonesia untuk masa depan yang berkelanjutan. 
                        Bersama kita tanam pohon, bersama kita jaga bumi.
                    </p>
                    <div class="footer-social">
                        <a href="https://facebook.com/" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com/" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://x.com/" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://youtube.com/" class="social-link" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://wa.me/62895604339518?text=Halo%20Admin%20TUMBUH,%20saya%20ingin%20bertanya." class="social-link" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Kolom 2: Menu Cepat -->
                <div class="footer-column">
                    <h4 class="footer-title">Menu Cepat</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Beranda</a></li>
                        <li><a href="tentang.php"><i class="fas fa-chevron-right"></i> Tentang Kami</a></li>
                        <li><a href="tim.php"><i class="fas fa-chevron-right"></i> Tim Kami</a></li>
                        <li><a href="galeri.php"><i class="fas fa-chevron-right"></i> Galeri</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Program -->
                <div class="footer-column">
                    <h4 class="footer-title">Program</h4>
                    <ul class="footer-links">
                        <li><a href="kegiatan.php"><i class="fas fa-chevron-right"></i> Kegiatan</a></li>
                        <li><a href="artikel.php"><i class="fas fa-chevron-right"></i> Artikel</a></li>
                        <li><a href="gabung.php"><i class="fas fa-chevron-right"></i> Gabung Relawan</a></li>
                        <li><a href="kontak.php"><i class="fas fa-chevron-right"></i> Kontak</a></li>
                    </ul>
                </div>

                <!-- Kolom 4: Kontak Info -->
                <div class="footer-column">
                    <h4 class="footer-title">Hubungi Kami</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Palembang, Indonesia</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:info@tumbuh.org">info@tumbuh.org</a>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:+62895604339518">0895-6043-39518</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom Section -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="footer-copyright">
                        &copy; <?php echo date('Y'); ?> TUMBUH - Tanam Untuk Bumi Hijau. All Rights Reserved.
                    </p>
                    <div class="footer-credits">
                        <span>Made with <i class="fas fa-heart"></i> for a greener future</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        function showModal(id) {
            document.getElementById('modal-' + id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById('modal-' + id).classList.remove('active');
        }

        function toggleEditMode() {
            const viewMode = document.getElementById('profile-view');
            const editMode = document.getElementById('profile-edit');
            
            if (!viewMode || !editMode) return;

            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'grid';
                editMode.style.display = 'none';
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
            }
        }

        window.addEventListener('load', function() {
            <?php if (isset($_SESSION['login_error'])): ?>
                showModal('login');
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>
        });

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        };

        (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="zUAYJUaV3xWh6Plqcuswq";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    </script>
</body>
</html>