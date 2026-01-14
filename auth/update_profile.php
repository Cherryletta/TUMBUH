<?php
require_once __DIR__ . '/../config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = (int) $_SESSION['user_id'];
    $nama     = clean($_POST['nama_user']);
    $email    = clean($_POST['email_user']);
    $telepon  = clean($_POST['telepon_user']);
    $alamat   = clean($_POST['alamat_user']);
    $bidang   = clean($_POST['bidang_user']);
    $motivasi = clean($_POST['motivasi_user']);

    // Validasi
    $errors = [];

    if (empty($nama) || empty($email) || empty($telepon) || empty($bidang)) {
        $errors[] = "Semua field wajib harus diisi!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }

    // Cek apakah email sudah digunakan user lain
    $check_email = mysqli_query(
        $conn,
        "SELECT id_user FROM users 
         WHERE email_user = '$email' 
         AND id_user != $user_id"
    );

    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email sudah digunakan oleh user lain!";
    }

    // Jika tidak ada error, update database
    if (empty($errors)) {
        $query = "UPDATE users SET 
                    nama_user     = '$nama',
                    email_user    = '$email',
                    telepon_user  = '$telepon',
                    alamat_user   = '$alamat',
                    bidang_user   = '$bidang',
                    motivasi_user = '$motivasi'
                  WHERE id_user = $user_id";

        if (mysqli_query($conn, $query)) {
            $_SESSION['user_name']  = $nama;
            $_SESSION['user_email'] = $email;
            $_SESSION['profile_success'] = "Profil berhasil diupdate!";

            header("Location: ../dashboard.php");
            exit();

        } else {
            $errors[] = "Gagal mengupdate profil: " . mysqli_error($conn);
        }
    }

    // Jika ada error
    if (!empty($errors)) {
        $_SESSION['profile_errors'] = $errors;
        header("Location: ../dashboard.php");
        exit();
    }
}

// Jika diakses langsung tanpa POST
header("Location: ../index.php");
exit();
?>
