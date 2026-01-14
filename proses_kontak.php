<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pesan = clean($_POST['nama']);
    $email_pesan = clean($_POST['email']);
    $telepon_pesan = isset($_POST['telepon']) ? clean($_POST['telepon']) : '';
    $subjek_pesan = clean($_POST['subjek']);
    $isi_pesan = clean($_POST['pesan']);
    $tanggal_pesan = date('Y-m-d H:i:s');
    
    try {
        // Simpan ke database
        $query = "INSERT INTO kontak_pesan (nama_pesan, email_pesan, telepon_pesan, subjek_pesan, isi_pesan, tanggal_pesan, status_pesan) 
                  VALUES ('$nama_pesan', '$email_pesan', '$telepon_pesan', '$subjek_pesan', '$isi_pesan', '$tanggal_pesan', 'belum_dibaca')";
        
        if (mysqli_query($conn, $query)) {
            // (OPSIONAL) Kirim email notifikasi
            $to = "info@tumbuh.org"; // Ganti dengan email Anda
            $email_subject = "Pesan Kontak Website: " . $subjek_pesan;
            $email_body = "
Pesan Baru dari Website TUMBUH

Nama: $nama_pesan
Email: $email_pesan
Telepon: $telepon_pesan
Subjek: $subjek_pesan

Pesan:
$isi_pesan

---
Dikirim pada: " . date('d-m-Y H:i:s') . "
";
            
            $headers = "From: noreply@tumbuh.org\r\n";
            $headers .= "Reply-To: $email_pesan\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            @mail($to, $email_subject, $email_body, $headers);
            
            $_SESSION['success'] = "Pesan Anda berhasil dikirim! Kami akan merespons segera.";
        } else {
            $_SESSION['error'] = "Gagal mengirim pesan. Silakan coba lagi.";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
    }
    
    header('Location: kontak.php');
    exit;
    
} else {
    header('Location: kontak.php');
    exit;
}