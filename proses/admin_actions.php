<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ==================== ACTION HANDLER ====================
switch ($action) {

    // ==================== USER ====================
    case 'delete_user':
        $user_id = (int) ($_POST['id'] ?? 0);
        
        // Cegah admin menghapus diri sendiri
        if ($user_id == $_SESSION['user_id']) {
            header("Location: ../admin.php?tab=relawan&error=cannot_delete_self");
            exit();
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        header("Location: ../admin.php?tab=relawan&user_deleted=1");
        exit();

    case 'update_user':
        $user_id = (int) $_POST['user_id'];
        $nama    = clean($_POST['nama_user']);
        $email   = clean($_POST['email_user']);
        $telepon = clean($_POST['telepon_user'] ?? '');
        $alamat  = clean($_POST['alamat_user'] ?? '');
        $role    = clean($_POST['role_user'] ?? 'user');

        // Validasi role
        if (!in_array($role, ['user', 'admin'])) {
            $role = 'user';
        }

        $stmt = mysqli_prepare($conn, "
            UPDATE users SET 
                nama_user = ?, 
                email_user = ?, 
                telepon_user = ?, 
                alamat_user = ?,
                role_user = ?
            WHERE id_user = ?
        ");
        mysqli_stmt_bind_param($stmt, "sssssi", $nama, $email, $telepon, $alamat, $role, $user_id);
        mysqli_stmt_execute($stmt);

        header("Location: ../admin.php?tab=relawan&user_updated=1");
        exit();

    // ==================== KEGIATAN ====================
    case 'add_kegiatan':
        $jenis_kegiatan = clean($_POST['jenis_kegiatan']);
        $judul          = clean($_POST['judul_kegiatan']);
        $tanggal        = clean($_POST['tanggal_kegiatan']);
        $waktu          = clean($_POST['waktu_kegiatan']);
        $lokasi         = clean($_POST['lokasi_kegiatan']);
        $deskripsi      = clean($_POST['deskripsi_kegiatan']);
        $status         = clean($_POST['status_kegiatan']);
        $kuota_relawan  = intval($_POST['kuota_relawan'] ?? 0);
        $link_grup      = clean($_POST['link_grup'] ?? '');
        $manfaat        = clean($_POST['manfaat_kegiatan'] ?? '');
        $syarat         = clean($_POST['syarat_kegiatan'] ?? '');
        
        $gambar_kegiatan = '';
        
        // Upload gambar jika ada
        if (isset($_FILES['gambar_kegiatan']) && $_FILES['gambar_kegiatan']['size'] > 0) {
            $file = $_FILES['gambar_kegiatan'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if ($file['size'] <= 5 * 1024 * 1024 && in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_file_name = 'kegiatan_' . uniqid() . '.' . $file_ext;
                $upload_path = __DIR__ . '/../assets/img/kegiatan/' . $new_file_name;
                
                if (!is_dir(__DIR__ . '/../assets/img/kegiatan/')) {
                    mkdir(__DIR__ . '/../assets/img/kegiatan/', 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $gambar_kegiatan = 'assets/img/kegiatan/' . $new_file_name;
                }
            }
        }

        if (!$jenis_kegiatan || !$judul || !$tanggal || !$waktu || !$lokasi) {
            header("Location: ../admin.php?tab=kegiatan&error=invalid_input");
            exit();
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO kegiatan 
            (jenis_kegiatan, judul_kegiatan, gambar_kegiatan, tanggal_kegiatan, waktu_kegiatan, lokasi_kegiatan, deskripsi_kegiatan, status_kegiatan, kuota_relawan, link_grup)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "ssssssssis", $jenis_kegiatan, $judul, $gambar_kegiatan, $tanggal, $waktu, $lokasi, $deskripsi, $status, $kuota_relawan, $link_grup);
        
        if (mysqli_stmt_execute($stmt)) {
            $id_kegiatan = mysqli_insert_id($conn);
            
            // Insert detail kegiatan jika ada
            if ($manfaat || $syarat) {
                $stmt2 = mysqli_prepare($conn, "
                    INSERT INTO detail_kegiatan (id_kegiatan, manfaat_kegiatan, syarat_kegiatan)
                    VALUES (?, ?, ?)
                ");
                mysqli_stmt_bind_param($stmt2, "iss", $id_kegiatan, $manfaat, $syarat);
                mysqli_stmt_execute($stmt2);
            }
        }

        header("Location: ../admin.php?tab=kegiatan&kegiatan_added=1");
        exit();

    case 'update_kegiatan':
        $id             = (int) $_POST['id_kegiatan'];
        $jenis_kegiatan = clean($_POST['jenis_kegiatan']);
        $judul          = clean($_POST['judul_kegiatan']);
        $tanggal        = clean($_POST['tanggal_kegiatan']);
        $waktu          = clean($_POST['waktu_kegiatan']);
        $lokasi         = clean($_POST['lokasi_kegiatan']);
        $deskripsi      = clean($_POST['deskripsi_kegiatan']);
        $status         = clean($_POST['status_kegiatan']);
        $kuota_relawan  = intval($_POST['kuota_relawan'] ?? 0);
        $link_grup      = clean($_POST['link_grup'] ?? '');
        $manfaat        = clean($_POST['manfaat_kegiatan'] ?? '');
        $syarat         = clean($_POST['syarat_kegiatan'] ?? '');
        
        $gambar_kegiatan = '';
        
        // Upload gambar baru jika ada
        if (isset($_FILES['gambar_kegiatan']) && $_FILES['gambar_kegiatan']['size'] > 0) {
            $file = $_FILES['gambar_kegiatan'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if ($file['size'] <= 5 * 1024 * 1024 && in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                // Hapus gambar lama
                $old_query = mysqli_query($conn, "SELECT gambar_kegiatan FROM kegiatan WHERE id_kegiatan = $id");
                $old_data = mysqli_fetch_assoc($old_query);
                
                if (!empty($old_data['gambar_kegiatan'])) {
                    $old_file = __DIR__ . '/../' . $old_data['gambar_kegiatan'];
                    if (file_exists($old_file)) unlink($old_file);
                }
                
                $new_file_name = 'kegiatan_' . uniqid() . '.' . $file_ext;
                $upload_path = __DIR__ . '/../assets/img/kegiatan/' . $new_file_name;
                
                if (!is_dir(__DIR__ . '/../assets/img/kegiatan/')) {
                    mkdir(__DIR__ . '/../assets/img/kegiatan/', 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $gambar_kegiatan = 'assets/img/kegiatan/' . $new_file_name;
                }
            }
        }

        if (!$id || !$jenis_kegiatan || !$judul || !$tanggal || !$waktu || !$lokasi || !$status) {
            header("Location: ../admin.php?tab=kegiatan&error=invalid_input");
            exit();
        }

        $query = "
            UPDATE kegiatan SET
                jenis_kegiatan = ?,
                judul_kegiatan = ?,
                tanggal_kegiatan = ?,
                waktu_kegiatan = ?,
                lokasi_kegiatan = ?,
                deskripsi_kegiatan = ?,
                status_kegiatan = ?,
                kuota_relawan = ?,
                link_grup = ?";
        
        if ($gambar_kegiatan) {
            $query .= ", gambar_kegiatan = ?";
        }
        
        $query .= " WHERE id_kegiatan = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        
        if ($gambar_kegiatan) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssissi",
                $jenis_kegiatan,
                $judul,
                $tanggal,
                $waktu,
                $lokasi,
                $deskripsi,
                $status,
                $kuota_relawan,
                $link_grup,
                $gambar_kegiatan,
                $id
            );
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssisi",
                $jenis_kegiatan,
                $judul,
                $tanggal,
                $waktu,
                $lokasi,
                $deskripsi,
                $status,
                $kuota_relawan,
                $link_grup,
                $id
            );
        }

        mysqli_stmt_execute($stmt);
        
        // Update detail kegiatan
        $check_detail = mysqli_query($conn, "SELECT id_detail FROM detail_kegiatan WHERE id_kegiatan = $id");
        
        if (mysqli_num_rows($check_detail) > 0) {
            // Update existing
            $stmt2 = mysqli_prepare($conn, "
                UPDATE detail_kegiatan 
                SET manfaat_kegiatan = ?, syarat_kegiatan = ?
                WHERE id_kegiatan = ?
            ");
            mysqli_stmt_bind_param($stmt2, "ssi", $manfaat, $syarat, $id);
            mysqli_stmt_execute($stmt2);
        } else if ($manfaat || $syarat) {
            // Insert new
            $stmt2 = mysqli_prepare($conn, "
                INSERT INTO detail_kegiatan (id_kegiatan, manfaat_kegiatan, syarat_kegiatan)
                VALUES (?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt2, "iss", $id, $manfaat, $syarat);
            mysqli_stmt_execute($stmt2);
        }

        header("Location: ../admin.php?tab=kegiatan&kegiatan_updated=1");
        exit();
        
    case 'delete_kegiatan':
        $id = (int) ($_POST['id'] ?? 0);
        
        // Hapus gambar jika ada
        $img_query = mysqli_query($conn, "SELECT gambar_kegiatan FROM kegiatan WHERE id_kegiatan = $id");
        $img_data = mysqli_fetch_assoc($img_query);
        
        if (!empty($img_data['gambar_kegiatan'])) {
            $img_file = __DIR__ . '/../' . $img_data['gambar_kegiatan'];
            if (file_exists($img_file)) unlink($img_file);
        }
        
        // Hapus detail kegiatan
        mysqli_query($conn, "DELETE FROM detail_kegiatan WHERE id_kegiatan = $id");
        
        // Hapus kegiatan
        $stmt = mysqli_prepare($conn, "DELETE FROM kegiatan WHERE id_kegiatan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: ../admin.php?tab=kegiatan&kegiatan_deleted=1");
        exit();

    // ==================== PENDAFTARAN KEGIATAN ====================
    case 'update_pendaftaran':
        $id     = (int) $_POST['id_pendaftaran'];
        $status = clean($_POST['status_kehadiran']);

        $stmt = mysqli_prepare($conn, "
            UPDATE pendaftaran_kegiatan 
            SET status_kehadiran = ?
            WHERE id_pendaftaran = ?
        ");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);

        header("Location: ../admin.php?tab=pendaftaran&pendaftaran_updated=1");
        exit();

    case 'delete_pendaftaran':
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM pendaftaran_kegiatan WHERE id_pendaftaran = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: ../admin.php?tab=pendaftaran&pendaftaran_deleted=1");
        exit();

// ==================== GALERI ====================

case 'update_galeri':
    $id = (int) $_POST['id_galeri'];
    $deskripsi = clean($_POST['deskripsi_galeri'] ?? '');

    $q = mysqli_prepare($conn, "SELECT foto_galeri FROM galeri WHERE id_galeri = ?");
    mysqli_stmt_bind_param($q, "i", $id);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    $old = mysqli_fetch_assoc($res);

    if (!$old) {
        header("Location: ../admin.php?tab=galeri");
        exit();
    }

    $path = $old['foto_galeri'];

    if (!empty($_FILES['foto_baru']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto_baru']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $new = 'galeri_' . uniqid() . '.' . $ext;
            $target = __DIR__ . '/../assets/img/galeri/' . $new;
            move_uploaded_file($_FILES['foto_baru']['tmp_name'], $target);

            $old_file = __DIR__ . '/../' . $old['foto_galeri'];
            if (file_exists($old_file)) unlink($old_file);

            $path = 'assets/img/galeri/' . $new;
        }
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE galeri SET foto_galeri = ?, deskripsi_galeri = ? WHERE id_galeri = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssi", $path, $deskripsi, $id);
    mysqli_stmt_execute($stmt);

    header("Location: ../admin.php?tab=galeri");
    exit();


case 'delete_foto':
    $id_foto = (int) ($_POST['id_foto'] ?? 0);

    if ($id_foto <= 0) {
        header("Location: ../admin.php?tab=galeri&error=invalid_id");
        exit();
    }

    // Ambil data foto
    $stmt = mysqli_prepare($conn, "SELECT foto_galeri FROM galeri WHERE id_galeri = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_foto);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $foto = mysqli_fetch_assoc($result);

    if (!$foto) {
        header("Location: ../admin.php?tab=galeri&error=not_found");
        exit();
    }

    // Hapus file fisik
    $file_path = __DIR__ . '/../' . $foto['foto_galeri'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Hapus data DB
    $stmt2 = mysqli_prepare($conn, "DELETE FROM galeri WHERE id_galeri = ?");
    mysqli_stmt_bind_param($stmt2, "i", $id_foto);
    mysqli_stmt_execute($stmt2);

    header("Location: ../admin.php?tab=galeri&foto_deleted=1");
    exit();


    // ==================== ARTIKEL ====================
    case 'add_artikel':

        $kategori = $_POST['kategori_artikel'];
        $judul    = clean($_POST['judul_artikel']);
        $tanggal  = $_POST['tanggal_artikel'];
        $sumber   = clean($_POST['sumber_artikel']);
        $isi      = clean($_POST['isi_artikel']);

        $gambar = '';

        if (!empty($_FILES['gambar_artikel']['name'])) {
            $ext = strtolower(pathinfo($_FILES['gambar_artikel']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];

            if (in_array($ext, $allowed) && $_FILES['gambar_artikel']['size'] <= 5 * 1024 * 1024) {

                $newName = 'artikel_' . uniqid() . '.' . $ext;
                $uploadDir = __DIR__ . '/../assets/img/artikel/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                move_uploaded_file(
                    $_FILES['gambar_artikel']['tmp_name'],
                    $uploadDir . $newName
                );

                $gambar = $newName;
            }
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

        header("Location: ../admin.php?tab=artikel&artikel_added=1");
        exit();

    case 'update_artikel':
        $id       = (int) $_POST['id_artikel'];
        $kategori = clean($_POST['kategori_artikel']);
        $judul    = clean($_POST['judul_artikel']);
        $tanggal  = $_POST['tanggal_artikel'];
        $sumber   = clean($_POST['sumber_artikel']);
        $q = mysqli_query($conn, "SELECT gambar_artikel FROM artikel WHERE id_artikel = $id");
        $old = mysqli_fetch_assoc($q);

        $gambar = $old['gambar_artikel'] ?? '';

        if (!empty($_FILES['gambar_artikel']['name'])) {
            $ext = strtolower(pathinfo($_FILES['gambar_artikel']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];

            if (in_array($ext, $allowed) && $_FILES['gambar_artikel']['size'] <= 5 * 1024 * 1024) {

                if (!empty($old['gambar_artikel'])) {
                    $oldFile = __DIR__ . '/../assets/img/artikel/' . $old['gambar_artikel'];
                    if (file_exists($oldFile)) unlink($oldFile);
                }

                $newName = 'artikel_' . uniqid() . '.' . $ext;
                $uploadDir = __DIR__ . '/../assets/img/artikel/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                move_uploaded_file(
                    $_FILES['gambar_artikel']['tmp_name'],
                    $uploadDir . $newName
                );

                $gambar = $newName;
            }
        }
        $isi      = clean($_POST['isi_artikel']);

        if (!$id || !$kategori || !$judul || !$tanggal || !$sumber || !$isi) {
            header("Location: ../admin.php?tab=artikel&error=invalid_input");
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

        header("Location: ../admin.php?tab=artikel&artikel_updated=1");
        exit();

    case 'delete_artikel':
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM artikel WHERE id_artikel = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: ../admin.php?tab=artikel&artikel_deleted=1");
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

        header("Location: ../admin.php?tab=pesan&pesan_read=1");
        exit();

    case 'mark_all_read_pesan':
        mysqli_query($conn, "
            UPDATE kontak_pesan 
            SET status_pesan = 'sudah_dibaca' 
            WHERE status_pesan = 'belum_dibaca'
        ");

        header("Location: ../admin.php?tab=pesan&pesan_read=1");
        exit();

    case 'delete_pesan':
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM kontak_pesan WHERE id_pesan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        header("Location: ../admin.php?tab=pesan&pesan_deleted=1");
        exit();

    default:
        header("Location: ../admin.php");
        exit();
}