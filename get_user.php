<?php
require_once 'config.php';

// Cek apakah user admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = (int)$_GET['id'];

$query = "SELECT * FROM users WHERE id_user = $user_id";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'User not found']);
}
?>
