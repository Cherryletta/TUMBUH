<?php
require_once 'config.php';

// ==================== SECURITY CHECK ====================
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ==================== ACTION HANDLER ====================
switch ($action) {

    // ==================== USER ====================
    case 'delete_user':
        $user_id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id_user = ? AND role_user = 'user'");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=relawan&user_deleted=1");
        exit();

    case 'update_user':
        $user_id = (int) $_POST['user_id'];
        $nama    = clean($_POST['nama_user']);
        $email   = clean($_POST['email_user']);
        $telepon = clean($_POST['telepon_user']);
        $alamat  = clean($_POST['alamat_user']);
        $bidang  = clean($_POST['bidang_user']);

        $stmt = mysqli_prepare($conn, "
            UPDATE users SET 
                nama_user = ?, 
                email_user = ?, 
                telepon_user = ?, 
                alamat_user = ?, 
                bidang_user = ?
            WHERE id_user = ?
        ");
        mysqli_stmt_bind_param($stmt, "sssssi", $nama, $email, $telepon, $alamat, $bidang, $user_id);
        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=relawan&user_updated=1");
        exit();

    // ==================== KEGIATAN ====================
    case 'add_kegiatan':
        $judul     = clean($_POST['judul_kegiatan']);
        $tanggal   = clean($_POST['tanggal_kegiatan']);
        $lokasi    = clean($_POST['lokasi_kegiatan']);
        $deskripsi = clean($_POST['deskripsi_kegiatan']);
        $status    = clean($_POST['status_kegiatan']);

        if (!$judul || !$tanggal || !$lokasi) {
            header("Location: admin.php?tab=kegiatan&error=invalid_input");
            exit();
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO kegiatan 
            (judul_kegiatan, tanggal_kegiatan, lokasi_kegiatan, deskripsi_kegiatan, status_kegiatan)
            VALUES (?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "sssss", $judul, $tanggal, $lokasi, $deskripsi, $status);
        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=kegiatan&kegiatan_added=1");
        exit();

    case 'update_kegiatan':
        $id        = (int) $_POST['id_kegiatan'];
        $judul     = clean($_POST['judul_kegiatan']);
        $tanggal   = clean($_POST['tanggal_kegiatan']);
        $lokasi    = clean($_POST['lokasi_kegiatan']);
        $deskripsi = clean($_POST['deskripsi_kegiatan']);
        $status    = clean($_POST['status_kegiatan']);

        if (!$id || !$judul || !$tanggal || !$lokasi || !$status) {
            header("Location: admin.php?tab=kegiatan&error=invalid_input");
            exit();
        }

        $stmt = mysqli_prepare($conn, "
            UPDATE kegiatan SET
                judul_kegiatan = ?,
                tanggal_kegiatan = ?,
                lokasi_kegiatan = ?,
                deskripsi_kegiatan = ?,
                status_kegiatan = ?
            WHERE id_kegiatan = ?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "sssssi",
            $judul,
            $tanggal,
            $lokasi,
            $deskripsi,
            $status,
            $id
        );

        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=kegiatan&kegiatan_updated=1");
        exit();
        
    case 'delete_kegiatan':
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM kegiatan WHERE id_kegiatan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=kegiatan&kegiatan_deleted=1");
        exit();

    // ==================== ARTIKEL ====================
    case 'add_artikel':

        $kategori = $_POST['kategori_artikel'];
        $judul    = mysqli_real_escape_string($conn, $_POST['judul_artikel']);
        $tanggal  = $_POST['tanggal_artikel'];
        $sumber   = mysqli_real_escape_string($conn, $_POST['sumber_artikel']);
        $gambar   = mysqli_real_escape_string($conn, $_POST['gambar_artikel']);
        $isi      = mysqli_real_escape_string($conn, $_POST['isi_artikel']);

        if (!$kategori || !$judul || !$tanggal || !$sumber || !$isi) {
            header("Location: admin.php?tab=artikel&error=invalid_input");
            exit();
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO artikel
            (kategori_artikel, judul_artikel, tanggal_artikel, sumber_artikel, gambar_artikel, isi_artikel)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssssss",
            $kategori,
            $judul,
            $tanggal,
            $sumber,
            $gambar,
            $isi
        );

        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=artikel&artikel_added=1");
        exit();

    case 'update_artikel':

        $id       = (int) $_POST['id_artikel'];
        $kategori = clean($_POST['kategori_artikel']);
        $judul    = clean($_POST['judul_artikel']);
        $tanggal  = $_POST['tanggal_artikel'];
        $sumber   = clean($_POST['sumber_artikel']);
        $gambar   = clean($_POST['gambar_artikel'] ?? '');
        $isi      = clean($_POST['isi_artikel']);

        if (!$id || !$kategori || !$judul || !$tanggal || !$sumber || !$isi) {
            header("Location: admin.php?tab=artikel&error=invalid_input");
            exit();
        }

        $stmt = mysqli_prepare($conn, "
            UPDATE artikel SET
                kategori_artikel = ?,
                judul_artikel    = ?,
                tanggal_artikel  = ?,
                sumber_artikel   = ?,
                gambar_artikel   = ?,
                isi_artikel      = ?
            WHERE id_artikel = ?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssi",
            $kategori,
            $judul,
            $tanggal,
            $sumber,
            $gambar,
            $isi,
            $id
        );

        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=artikel&artikel_updated=1");
        exit();

    case 'delete_artikel':
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM artikel WHERE id_artikel = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=artikel&artikel_deleted=1");
        exit();

    // ==================== PESAN ====================
    case 'mark_read_pesan':
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "
            UPDATE kontak_pesan 
            SET status_pesan = 'sudah_dibaca' 
            WHERE id_pesan = ?
        ");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=pesan&pesan_read=1");
        exit();

    case 'mark_all_read_pesan':
        mysqli_query($conn, "
            UPDATE kontak_pesan 
            SET status_pesan = 'sudah_dibaca' 
            WHERE status_pesan = 'belum_dibaca'
        ");

        header("Location: admin.php?tab=pesan&pesan_read=1");
        exit();

    case 'delete_pesan':
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM kontak_pesan WHERE id_pesan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: admin.php?tab=pesan&pesan_deleted=1");
        exit();

    // ==================== DEFAULT ====================
    default:
        header("Location: admin.php");
        exit();
}
