<?php
session_name("OIDC_CLIENT_SESSION"); // <-- Nama beda untuk klien
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['pending_request_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$req_id = $_SESSION['pending_request_id'];
$stmt = $conn->prepare("SELECT status, client_id FROM login_requests WHERE id = ?");
$stmt->bind_param("i", $req_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['status'] === 'approved') {
    // Jika approved, set session login RESMI di sini
    $_SESSION['client_logged_in'] = true;
    
    // Ambil nama client lagi untuk sesi
    $stmt_name = $conn->prepare("SELECT client_name FROM clients WHERE client_id = ?");
    $stmt_name->bind_param("s", $row['client_id']);
    $stmt_name->execute();
    $res_name = $stmt_name->get_result();
    $client_data = $res_name->fetch_assoc();
    
    $_SESSION['client_name'] = $client_data['client_name'];
    $_SESSION['access_token'] = "eyJhTokenApprovedByAdmin..." . bin2hex(random_bytes(10));
    
    // Hapus session pending
    unset($_SESSION['pending_request_id']);
    
    echo json_encode(['status' => 'approved']);

} elseif ($row['status'] === 'rejected') {
    unset($_SESSION['pending_request_id']);
    echo json_encode(['status' => 'rejected']);
} else {
    echo json_encode(['status' => 'pending']);
}
?>