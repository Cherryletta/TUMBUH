<?php
require_once __DIR__ . '/../config.php';

/* ================= DATA ================= */

// Statistik
$stat_query = mysqli_query($conn, "SELECT * FROM statistik");
$statistik = [];
while ($row = mysqli_fetch_assoc($stat_query)) {
    $statistik[$row['nama_stat']] = $row['nilai'];
}

// Kegiatan
$kegiatan_query = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY created_at_kegiatan DESC");
$kegiatan_list = [];
while ($row = mysqli_fetch_assoc($kegiatan_query)) {
    $kegiatan_list[] = $row;
}

// Berita
$berita_query = mysqli_query($conn, "SELECT * FROM berita ORDER BY created_at_berita DESC LIMIT 5");
$berita_list = [];
while ($row = mysqli_fetch_assoc($berita_query)) {
    $berita_list[] = $row;
}

// Tim
$tim_query = mysqli_query($conn, "SELECT * FROM tim ORDER BY id_tim ASC");
$tim_list = [];
while ($row = mysqli_fetch_assoc($tim_query)) {
    $tim_list[] = $row;
}

/* ================= LOGIN ================= */

$is_logged_in = isset($_SESSION['user_id']);
$current_user = null;

if ($is_logged_in) {
    $user_id = (int) $_SESSION['user_id'];
    $user_query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $user_id");
    $current_user = mysqli_fetch_assoc($user_query);
}

/* ================= ACTIVE PAGE DETECTION ================= */
$current_page = basename($_SERVER['PHP_SELF']);
$tentang_pages = ['tentang.php', 'visi-misi.php', 'tim.php', 'kontak.php'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TUMBUH - Tanam Untuk Bumi Hijau</title>
    <link rel="icon" type="image/png" href="assets/img/icon_tumbuh.png">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<!-- MOBILE MENU OVERLAY -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

<!-- MOBILE SIDEBAR MENU -->
<div class="mobile-sidebar-menu" id="mobileSidebarMenu">
    <div class="mobile-menu-header">
        <div class="mobile-menu-logo">
            <img src="assets/img/logo_tumbuh.png" alt="TUMBUH Logo">
        </div>
        <button class="mobile-menu-close" id="mobileMenuClose">&times;</button>
    </div>

    <div class="mobile-menu-content">
        <ul class="mobile-menu-list">
            <li>
                <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <span class="menu-icon">🏠</span>
                    <span>Beranda</span>
                </a>
            </li>
            
            <li class="mobile-dropdown">
                <button class="mobile-dropdown-toggle <?php echo in_array($current_page, $tentang_pages) ? 'active' : ''; ?>">
                    <span style="display: flex; align-items: center; gap: 0.9rem;">
                        <span class="menu-icon">ℹ️</span>
                        <span>Tentang</span>
                    </span>
                    <span class="mobile-dropdown-arrow">›</span>
                </button>
                <ul class="mobile-dropdown-content">
                    <li><a href="tentang.php">Siapa Kami</a></li>
                    <li><a href="visi-misi.php">Visi & Misi</a></li>
                    <li><a href="tim.php">Tim Kami</a></li>
                    <li><a href="kontak.php">Kontak</a></li>
                </ul>
            </li>
            
            <li>
                <a href="kegiatan.php" class="<?php echo ($current_page == 'kegiatan.php') ? 'active' : ''; ?>">
                    <span class="menu-icon">📅</span>
                    <span>Kegiatan</span>
                </a>
            </li>
            
            <li>
                <a href="berita.php" class="<?php echo ($current_page == 'berita.php') ? 'active' : ''; ?>">
                    <span class="menu-icon">📰</span>
                    <span>Berita</span>
                </a>
            </li>
            
            <li>
                <a href="galeri.php" class="<?php echo ($current_page == 'galeri.php') ? 'active' : ''; ?>">
                    <span class="menu-icon">🖼️</span>
                    <span>Galeri</span>
                </a>
            </li>
        </ul>

        <div class="mobile-menu-auth">
            <?php if ($is_logged_in && $current_user): ?>
                <div class="mobile-user-info">
                    <div class="mobile-user-avatar">
                        <?php echo strtoupper(substr($current_user['nama_user'], 0, 1)); ?>
                    </div>
                    <div class="mobile-user-details">
                        <strong><?php echo explode(' ', $current_user['nama_user'])[0]; ?></strong>
                        <span><?php echo $current_user['email_user']; ?></span>
                    </div>
                </div>
                
                <?php if ($current_user['role_user'] === 'admin'): ?>
                    <a href="admin.php" class="mobile-auth-btn admin-btn">⚙️ Admin Panel</a>
                <?php endif; ?>
                
                <a href="dashboard.php" class="mobile-auth-btn">Dashboard</a>
                <a href="auth/logout.php" class="mobile-auth-btn logout-btn">Keluar</a>
            <?php else: ?>
                <button onclick="showModal('login')" class="mobile-auth-btn">Masuk</button>
                <button onclick="window.location.href='gabung.php'" class="mobile-auth-btn daftar-btn">Daftar</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<nav>
    <div class="nav-container">
        <!-- HAMBURGER MENU (MOBILE ONLY) -->
        <button class="mobile-hamburger" id="mobileHamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- LOGO -->
        <div class="nav-logo">
            <img src="assets/img/logo_tumbuh.png" alt="TUMBUH Logo">
        </div>

        <!-- DESKTOP MENU -->
        <ul class="desktop-menu">
            <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Beranda</a></li>
            
            <li class="nav-dropdown">
                <a href="#" class="<?php echo in_array($current_page, $tentang_pages) ? 'active' : ''; ?>">
                    Tentang <span class="dropdown-icon">▾</span>
                </a>
                <div class="nav-dropdown-content">
                    <a href="tentang.php">Siapa Kami</a>
                    <a href="visi-misi.php">Visi & Misi</a>
                    <a href="tim.php">Tim Kami</a>
                    <a href="kontak.php">Kontak</a>
                </div>
            </li>
            
            <li><a href="kegiatan.php" class="<?php echo ($current_page == 'kegiatan.php') ? 'active' : ''; ?>">Kegiatan</a></li>
            <li><a href="berita.php" class="<?php echo ($current_page == 'berita.php') ? 'active' : ''; ?>">Berita</a></li>
            <li><a href="galeri.php" class="<?php echo ($current_page == 'galeri.php') ? 'active' : ''; ?>">Galeri</a></li>
        </ul>

        <!-- DESKTOP AUTH -->
        <div class="nav-auth">
            <?php if ($is_logged_in && $current_user): ?>
                <div class="user-info">
                    <span>Halo, <?php echo explode(' ', $current_user['nama_user'])[0]; ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($current_user['nama_user'], 0, 1)); ?>
                    </div>
                </div>
                <?php if ($current_user['role_user'] === 'admin'): ?>
                    <a href="admin.php" style="background: #ff6b00; border-color: #ff6b00; font-weight: 600;">⚙️ Admin Panel</a>
                <?php endif; ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="auth/logout.php">Keluar</a>
            <?php else: ?>
                <button onclick="showModal('login')">Masuk</button>
                <button onclick="window.location.href='gabung.php'" style="background:#ff9800;border-color:#ff9800;">Daftar</button>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
// ==================== MOBILE MENU ====================
(function() {
    const hamburger = document.getElementById('mobileHamburger');
    const sidebar = document.getElementById('mobileSidebarMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    const closeBtn = document.getElementById('mobileMenuClose');

    function openMenu() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburger) hamburger.addEventListener('click', openMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    if (overlay) overlay.addEventListener('click', closeMenu);

    // Mobile dropdown
    const dropdownToggles = document.querySelectorAll('.mobile-dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            const wasActive = parent.classList.contains('active');
            
            document.querySelectorAll('.mobile-dropdown').forEach(d => d.classList.remove('active'));
            
            if (!wasActive) parent.classList.add('active');
        });
    });
})();

// ==================== DESKTOP DROPDOWN ====================
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.nav-dropdown');
    const dropdownToggle = document.querySelector('.nav-dropdown > a');
    
    if (dropdown && dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.classList.toggle('active');
        });
        
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    }
});
</script>