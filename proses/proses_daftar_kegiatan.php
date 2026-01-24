<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu'
    ]);
    exit;
}

if (!isset($_POST['kegiatan_id']) || empty($_POST['kegiatan_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID kegiatan tidak valid'
    ]);
    exit;
}

$kegiatan_id = intval($_POST['kegiatan_id']);
$user_id = $_SESSION['user_id'];
$catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';

$sql = "SELECT k.*, COUNT(pk.id_pendaftaran) as jumlah_pendaftar 
        FROM kegiatan k 
        LEFT JOIN pendaftaran_kegiatan pk ON k.id_kegiatan = pk.id_kegiatan 
        WHERE k.id_kegiatan = ? 
        GROUP BY k.id_kegiatan";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $kegiatan_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kegiatan = mysqli_fetch_assoc($result);

if (!$kegiatan) {
    echo json_encode([
        'success' => false,
        'message' => 'Kegiatan tidak ditemukan'
    ]);
    exit;
}

if (strtolower($kegiatan['status_kegiatan']) === 'selesai') {
    echo json_encode([
        'success' => false,
        'message' => 'Kegiatan ini sudah selesai'
    ]);
    exit;
}

$sisa_kuota = $kegiatan['kuota_relawan'] - $kegiatan['jumlah_pendaftar'];
if ($sisa_kuota <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Kuota peserta sudah penuh'
    ]);
    exit;
}

$sql = "SELECT id_pendaftaran FROM pendaftaran_kegiatan 
        WHERE id_kegiatan = ? AND id_user = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $kegiatan_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda sudah terdaftar di kegiatan ini'
    ]);
    exit;
}

$sql = "INSERT INTO pendaftaran_kegiatan (id_kegiatan, id_user, status_kehadiran, catatan) 
        VALUES (?, ?, 'terdaftar', ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iis", $kegiatan_id, $user_id, $catatan);

if (mysqli_stmt_execute($stmt)) {
    $sql = "SELECT nama_user FROM users WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'message' => 'Pendaftaran berhasil!',
        'data' => [
            'kegiatan_nama' => $kegiatan['judul_kegiatan'],
            'kegiatan_tanggal' => date('d F Y', strtotime($kegiatan['tanggal_kegiatan'])),
            'kegiatan_lokasi' => $kegiatan['lokasi_kegiatan'],
            'user_nama' => $user['nama_user'],
            'wa_group' => $kegiatan['link_grup'] ?? 'https://chat.whatsapp.com/default-link',
            'sisa_kuota' => $sisa_kuota - 1
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mendaftar. Silakan coba lagi.'
    ]);
}

mysqli_close($conn);
?>