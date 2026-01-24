<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = clean($_POST['email_user']);
    $password = $_POST['password_user'];

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email dan password harus diisi!";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit();
    }

    $email_lower = strtolower($email);
    $query = "SELECT * FROM users WHERE LOWER(email_user) = '$email_lower'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password_user'])) {

            $_SESSION['user_id']    = $user['id_user'];
            $_SESSION['user_name']  = $user['nama_user'];
            $_SESSION['user_email'] = $user['email_user'];
            $_SESSION['user_role']  = $user['role_user'];

            if ($user['role_user'] === 'admin') {
                header("Location: ../admin.php");
            } else {
                header("Location: ../index.php?login=success");
            }
            exit();

        } else {
            $_SESSION['login_error'] = "Password salah!";
        }

    } else {
        $_SESSION['login_error'] = "Email tidak ditemukan!";
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

header("Location: ../index.php");
exit();
?>
