<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nama = clean($_POST['nama_user']);
    $email = clean($_POST['email_user']);
    $password = $_POST['password_user'];
    $confirm_password = $_POST['confirm_password'];
    $telepon = clean($_POST['telepon_user']);
    $alamat = clean($_POST['alamat_user']);
    $motivasi = clean($_POST['motivasi_user']);

    // Validasi
    $errors = [];
    
    if (empty($nama) || empty($email) || empty($password) || empty($telepon)) {
        $errors[] = "Semua field wajib harus diisi!";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter!";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak cocok!";
    }
    
    // Cek apakah email sudah terdaftar
    $stmt = mysqli_prepare($conn, "SELECT id_user FROM users WHERE email_user = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email sudah terdaftar!";
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert ke database
        $query = "INSERT INTO users (nama_user, email_user, password_user, telepon_user, alamat_user, motivasi_user, role_user) 
                  VALUES ('$nama', '$email', '$hashed_password', '$telepon', '$alamat', '$motivasi', 'user')";
        
        if (mysqli_query($conn, $query)) {     
            $_SESSION['success'] = "Pendaftaran berhasil! Silakan login dengan akun Anda.";
            header("Location: ../index.php?success=1");
            exit();
        } else {
            $errors[] = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
    
    // Jika ada error, kembali ke halaman register dengan pesan error
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
        header("Location: ../gabung.php");
        exit();
    }
}

// Jika diakses langsung tanpa POST
header("Location: ../index.php");
exit();
?>
