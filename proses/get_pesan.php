<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID pesan tidak ditemukan']);
    exit;
}

$id_pesan = (int)$_GET['id'];

$query = mysqli_query($conn, "SELECT * FROM kontak_pesan WHERE id_pesan = $id_pesan");

if (!$query || mysqli_num_rows($query) == 0) {
    echo json_encode(['error' => 'Pesan tidak ditemukan']);
    exit;
}

$pesan = mysqli_fetch_assoc($query);

// Format tanggal
$pesan['tanggal_pesan_formatted'] = date('d F Y, H:i', strtotime($pesan['tanggal_pesan']));

echo json_encode($pesan);