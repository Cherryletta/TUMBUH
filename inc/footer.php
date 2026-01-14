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
            
            <form method="POST" action="auth/login_process.php">
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

    <footer>
        <p>&copy; 2025 TUMBUH - Tanam Untuk Bumi Hijau</p>
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