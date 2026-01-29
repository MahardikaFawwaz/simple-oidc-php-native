<?php
require_once '../db.php';
session_name("OIDC_CLIENT_SESSION");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stok_tambah = $_POST['stok'];
    $client_id = $_SESSION['logged_client_id'];

    // Simpan ke log aktivitas sebagai bukti pengerjaan tugas
    $stmt = $conn->prepare("INSERT INTO activity_logs (client_id, activity, status, ip_address) VALUES (?, 'Update Stok Barang', 'Success', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("ss", $client_id, $ip);
    $stmt->execute();

    header("Location: dashboard.php?status=success");
}
?>