<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$password_lama = $_POST['password_lama'];
$password_baru = $_POST['password_baru'];
$konfirmasi = $_POST['konfirmasi_password'];

if ($password_baru !== $konfirmasi) {
    $_SESSION['profile_errors'][] = 'Konfirmasi password tidak cocok';
    header('Location: ../dashboard.php');
    exit;
}

// Ambil password lama dari DB
$sql = "SELECT password_user FROM users WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Cek password lama
if (!password_verify($password_lama, $user['password_user'])) {
    $_SESSION['profile_errors'][] = 'Password lama salah';
    header('Location: ../dashboard.php');
    exit;
}

// Hash password baru
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// Update password
$sql = "UPDATE users SET password_user = ? WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $password_hash, $user_id);
mysqli_stmt_execute($stmt);

$_SESSION['profile_success'] = 'Password berhasil diperbarui';
header('Location: ../dashboard.php');
exit;
