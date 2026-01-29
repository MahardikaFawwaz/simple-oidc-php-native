<?php
session_name("OIDC_CLIENT_SESSION");
session_start();
require_once '../db.php';



$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    $client_id = $_POST['client_id'];
    $client_secret = $_POST['client_secret'];

    // Tambahkan kondisi 'is_approved = 1' dalam query
    $stmt = $conn->prepare("SELECT client_id, client_name, is_approved FROM clients WHERE client_id = ? AND client_secret = ?");
    $stmt->bind_param("ss", $client_id, $client_secret);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['is_approved'] == 1) {
            // Jika sudah disetujui admin
            $_SESSION['client_logged_in'] = true;
            $_SESSION['logged_client_id'] = $row['client_id'];
            header("Location: dashboard.php");
            exit;
        } else {
            // Jika akun masih pending
            $error = "Akses ditolak! Akun Anda sedang menunggu persetujuan admin.";
        }
    } else {
        $error = "Client ID atau Secret salah!";
    }

    $error_message = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'rejected') {
        $error_message = "Akses Ditolak! Akun Anda telah dinonaktifkan atau ditolak oleh Admin.";
    } elseif ($_GET['status'] == 'pending') {
        $error_message = "Akun Anda kembali ke status pending. Silakan hubungi Admin.";
    } elseif ($_GET['status'] == 'unauthorized') {
        $error_message = "Sesi Anda berakhir. Silakan login kembali.";
    }

    // ... kode login sebelumnya ...
if ($row = $result->fetch_assoc()) {
    if ($row['is_approved'] == 1) {
        // CATAT LOG: Login Berhasil
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (client_id, activity, status, ip_address) VALUES (?, 'Login ke Dashboard', 'Success', ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param("ss", $client_id, $ip);
        $log_stmt->execute();

        $_SESSION['client_logged_in'] = true;
        // ... sisa kode redirect ...
    } else {
        // CATAT LOG: Login Ditolak (Belum Approved)
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (client_id, activity, status, ip_address) VALUES (?, 'Login Ditolak (Pending/Rejected)', 'Denied', ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param("ss", $client_id, $ip);
        $log_stmt->execute();
        
        $error = "Akses ditolak! Akun Anda sedang menunggu persetujuan admin.";
    }
} else {
    // CATAT LOG: Gagal Login (ID/Secret Salah)
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (client_id, activity, status, ip_address) VALUES (?, 'Gagal Login (Salah Kredensial)', 'Failed', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt->bind_param("ss", $client_id, $ip);
    $log_stmt->execute();
    
    $error = "Client ID atau Secret salah!";
}
}
?>

<?php if ($error_message): ?>
    <div class="alert alert-danger shadow-sm border-0 mb-3" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        <?= $error_message ?>
    </div>
<?php endif; ?>

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Client Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1a1d20; color: white; }
        .login-box { background: #2b3035; border-radius: 10px; padding: 30px; width: 400px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="login-box shadow">
        <h3 class="text-center mb-1">Client Login</h3>
        <p class="text-center text-muted small mb-4">Gunakan Client ID & Secret dari Admin</p>
        
        <?php if($error): ?><div class="alert alert-danger py-2 small"><?= $error ?></div><?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small">Client ID</label>
                <input type="text" name="client_id" class="form-control" placeholder="keuangan-xxxxx" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Client Secret</label>
                <input type="password" name="client_secret" class="form-control" placeholder="••••••••••••" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Login ke App</button>
        </form>
    </div>
</body>
</html>