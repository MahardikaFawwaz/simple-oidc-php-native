<?php
session_name("OIDC_CLIENT_SESSION"); // <-- Nama beda untuk klien
session_start();
require_once '../db.php';

if (isset($_SESSION['client_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $client_secret = $_POST['client_secret'];

    // 1. Validasi Kredensial dulu
    $stmt = $conn->prepare("SELECT * FROM clients WHERE client_id = ? AND client_secret = ?");
    $stmt->bind_param("ss", $client_id, $client_secret);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // 2. Kredensial Benar, Masukkan ke antrean Login Requests
        $stmt_req = $conn->prepare("INSERT INTO login_requests (client_id, status) VALUES (?, 'pending')");
        $stmt_req->bind_param("s", $client_id);
        
        if ($stmt_req->execute()) {
            // Simpan ID request dan Nama Client di session sementara untuk halaman waiting
            $_SESSION['pending_request_id'] = $stmt_req->insert_id;
            $_SESSION['pending_client_name'] = $row['client_name'];
            
            // Lempar ke Ruang Tunggu
            header("Location: wait.php");
            exit;
        } else {
            $error = "Gagal membuat permintaan login.";
        }
    } else {
        $error = "Client ID atau Secret tidak valid!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Client Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center vh-100">
    <div class="card bg-secondary text-white shadow p-4" style="width: 450px;">
        <h3 class="text-center mb-3">Login Aplikasi Klien</h3>
        <?php if($error): ?><div class="alert alert-danger text-dark"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Client ID</label>
                <input type="text" name="client_id" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Client Secret</label>
                <input type="password" name="client_secret" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-warning w-100 fw-bold">Request Access</button>
            <a href="../index.php" class="d-block text-center mt-2 text-white">Kembali</a>
        </form>
    </div>
</body>
</html>